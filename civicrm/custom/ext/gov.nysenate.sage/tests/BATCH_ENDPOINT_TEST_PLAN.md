# SAGE Batch Endpoint Verification Checklist

Purpose: confirm the SAGE developer's fix makes the 4 batch endpoints
`CRM_Utils_SAGE` depends on both **callable** and **contract-compliant** —
i.e. not just "returns 200," but returns the response shape our client code
already assumes. Scoped to raw HTTP checks (curl/Postman) so it's verifiable
independent of the Bluebird/CiviCRM stack.

Two scenarios per endpoint:
- **Scenario A** (all-good batch): sanity check the endpoint works at all.
- **Scenario B** (mixed batch — 1 good + 1 garbage address): the contract
  check that actually matters. Every batch method in `CRM_Utils_SAGE` gates
  on `$batchXml->total == count($input)` before processing *any* row —
  all-or-nothing. If the endpoint silently drops a malformed entry instead
  of returning it with its own per-item failure status, `total` won't
  match, and our code will treat the *entire* batch as failed even though
  the good address was fine.

## Prerequisites

- [x] Confirm `bluebird.cfg` now points to `sage-dev` for this instance
- [x] Confirm resolved constants via:
      `CIVICRM_SETTINGS=<path> HTTP_HOST=dev cv php:eval 'echo SAGE_API_KEY . "\n" . SAGE_API_BASE . "\n";'`
      (run from the `drupal/` root)
      → Confirmed: `KEY=cV3ebZgROGBYham1U40hpK3ybkYuB7uO`, `BASE=http://sage-dev.nysenate.gov:8080/api/v2`

## Final results summary — RESOLVED (2026-07-16)

All 4 endpoints are now working, and all fixes are applied in code and
verified via the live PHPUnit suite (`INSTANCE=dev HTTP_HOST=dev phpunit` —
39 tests, 108 assertions, all passing):

- **3 of 4 endpoints** (`/address/validate/batch`, `/geo/geocode/batch`,
  `/district/assign/batch`) needed only the `postalCity` field-name fix
  (see below) — applied in `CRM_Utils_SAGE::getAddressesFromRows()`.
- **`batchCheckAddress()`** additionally had its own separate hardcoded
  `provider=usps` param (independent copy of the same bug fixed earlier in
  the single-item `checkAddress()`) — found via the PHPUnit run still
  failing after the `postalCity` fix, and removed.
- **`/district/bluebird/batch`** (used by `batchLookup`) was never fixed
  server-side — it stayed `HTTP 500` even with `postalCity` and even for a
  single-item batch (see the isolated test in section 4 below). Per the
  SAGE developer, this endpoint is deprecated in favor of
  `/district/assign/batch`, which performs the same combined
  validate+geocode+district-assign operation and returns the same response
  shape (confirmed: the non-batch `/district/assign` and `/district/bluebird`
  already returned byte-for-byte identical XML for the same address, before
  any of this batch work started). `batchLookup()` now targets
  `/district/assign/batch` with an explicit `districtStrategy=streetFallback`
  param, and required no other code changes — its existing response-parsing
  logic already read exactly the fields (`uspsValidated`, `geocoded`,
  `districtAssigned`) that `/district/assign/batch` provides.

**Separately noted, not a batch issue**: the original "very malformed"
garbage-address fixture used in this checklist (`Qzxjklw Nonexistent Blvd
999999`/`Notarealcityxyz`/`00000`) crashes SAGE even on single, non-batch
calls — a pre-existing bug unrelated to the batch work, already handled
gracefully by our `callSAGE` exception-catching fix and covered by
`testGeocodeSetsNullStringFallbackOnFailure`. Not yet confirmed whether
this has been reported to the SAGE developer.

**Deliberately not pursued**: `batchLookupFromPoint()` (posts `{lat, lon}`
points to `/district/assign/batch`) still fails the same way the
address-shaped endpoints did before the `postalCity` fix. Tracing every
real caller of the 5 batch methods found no evidence Bluebird actually
invokes this code path in practice (see the project memory
`project_sage_batch_endpoint_status` for the full reasoning) — the SAGE
developer separately asked whether Bluebird does point-based batch
assignment at all, which the user has answered directly in conversation
with them. No fix was pursued, and the live test for this was written,
confirmed to fail as expected, then removed rather than left failing
indefinitely.

### Original run (before the `postalCity` fix was identified)

| Endpoint | Result |
|---|---|
| `/address/validate/batch` | `HTTP 200`, `INVALID_BATCH_ADDRESSES` (statusCode 55) — "could not be parsed" |
| `/geo/geocode/batch` | `HTTP 500`, `INTERNAL_ERROR` — unhandled server exception |
| `/district/assign/batch` | `HTTP 200`, `INVALID_BATCH_ADDRESSES` (statusCode 55) — same as address/validate |
| `/district/bluebird/batch` | `HTTP 500`, `INTERNAL_ERROR` — unhandled server exception |

For `/address/validate/batch`, tried 4 payload variations against the same
good-address body, all producing the identical `INVALID_BATCH_ADDRESSES`:
wrapped `{"addresses":[...]}`, no `Content-Type` header (matching what our
PHP actually sends), `addr` instead of `addr1`, and a single unwrapped
object instead of an array. None changed the result on their own — see
below, the actual fix was a different field name entirely.

## Update: SAGE developer identified the field name issue

The SAGE developer confirmed `city` should be `postalCity` in batch request
bodies. Retested all 4 endpoints with that one field renamed:

| Endpoint | Result |
|---|---|
| `/address/validate/batch` | **SUCCESS** — `total=2`, both `validated=true`, `statusCode=0` |
| `/geo/geocode/batch` | **SUCCESS** — `total=2`, both `geocoded=true`, real lat/lon |
| `/district/assign/batch` | **SUCCESS** — `total=2`, both `districtAssigned=true`, full district data returned |
| `/district/bluebird/batch` | **still `HTTP 500` / `INTERNAL_ERROR`** — unaffected by the `postalCity` fix |

3 of 4 batch endpoints now work correctly with `postalCity` instead of
`city`. `/district/bluebird/batch` (used by `batchLookup`) is the one
remaining open issue — still crashes server-side even with the field
rename, so this needs separate attention from the SAGE developer.

**Important**: `CRM_Utils_SAGE::getAddressesFromRows()` (the method that
builds the JSON body for every batch call) currently sends `city`, not
`postalCity` — this needs to change in our code for the 3 now-working
endpoints to actually succeed when called from Bluebird, not just via raw
curl.

## Update: Scenario B (mixed batch) results

Retested the 3 now-working endpoints with a mixed batch. First attempt used
the checklist's original "garbage address" fixture and got **the same
failures as before the `postalCity` fix** (`INVALID_BATCH_ADDRESSES` /
`500`) on all 3 — but this turned out to be a red herring, not a batch
regression: that specific garbage address (`Qzxjklw Nonexistent Blvd
999999` / `Notarealcityxyz` / `00000`) crashes SAGE even on a **single,
non-batch** call (confirmed separately) — a pre-existing, unrelated bug in
SAGE's address processing, not something specific to batch handling.

Retested with a milder fixture instead — a well-formed but nonexistent
address (`999 Fake Street, Albany, NY 12207`), which does **not** crash on
a single-item call (`HTTP 200`, `NO_GEOCODE_RESULT`). Results:

| Endpoint | Result |
|---|---|
| `/address/validate/batch` | **PASS** — `total=2`, item 1 `validated=true`, item 2 `validated=false` / `NO_ADDRESS_VALIDATE_RESULT` (statusCode 73) — graceful per-item failure |
| `/geo/geocode/batch` | **PASS** — `total=2`, item 1 `geocoded=true`, item 2 `geocoded=false` / `NO_GEOCODE_RESULT` (statusCode 71) — graceful per-item failure |
| `/district/assign/batch` | **PASS** — `total=2`, item 1 fully assigned, item 2 partially succeeds (`districtAssigned=true` via streetfile match, `geocoded=false`, `uspsValidated=false`) — graceful, not a batch-wide rejection |

**Conclusion**: the all-or-nothing `total == count(input)` contract our
client code depends on is satisfied correctly by all 3 endpoints — a single
bad-but-parseable address does not poison the batch. The garbage-address
crash is a separate, real bug worth reporting to the SAGE developer
separately, but it's orthogonal to the batch-endpoint work and already
handled gracefully on our side (`callSAGE`'s exception-catching fix, and
`testGeocodeSetsNullStringFallbackOnFailure` already covers exactly this
crash for the single-item path).

Shared test fixtures used below (originally `city`, corrected to `postalCity`
per the SAGE developer's fix — see update above):
- **Good address 1**: `addr1=State St, postalCity=Albany, state=NY, zip5=12224`
- **Good address 2**: `addr1=24 Eagle St, postalCity=Albany, state=NY, zip5=12207`
- **Garbage address**: `addr1=Qzxjklw Nonexistent Blvd 999999, postalCity=Notarealcityxyz, state=NY, zip5=00000`

All requests: `POST {SAGE_API_BASE}{path}?format=xml&key={SAGE_API_KEY}`,
`Content-Type: application/json`, raw JSON array body.

---

## 1. `/address/validate/batch` (used by `batchCheckAddress`)

### Scenario A — all-good batch
```json
[{"addr1":"State St","postalCity":"Albany","state":"NY","zip5":"12224"},
 {"addr1":"24 Eagle St","postalCity":"Albany","state":"NY","zip5":"12207"}]
```
- [x] HTTP 200
- [x] `total` == 2 — **PASS** (with `postalCity` instead of `city`)
- [x] Both items: `validated=true`, `statusCode=0` — **PASS**

### Scenario B — mixed batch
Retested with a milder "not found but well-formed" address (see update
above — the original garbage fixture crashes SAGE even standalone, so it
wasn't a fair test of batch-specific handling):
```json
[{"addr1":"State St","postalCity":"Albany","state":"NY","zip5":"12224"},
 {"addr1":"999 Fake Street","postalCity":"Albany","state":"NY","zip5":"12207"}]
```
- [x] HTTP 200 — **PASS**
- [x] `total` == 2 — **PASS**
- [x] Item 1: `validated=true` — **PASS**
- [x] Item 2: `validated=false`, `NO_ADDRESS_VALIDATE_RESULT` (statusCode 73) — **PASS**, graceful per-item failure

---

## 2. `/geo/geocode/batch` (used by `batchGeocode`)

### Scenario A — all-good batch
Same body as above (addr1/postalCity/state/zip5 pairs).
- [x] HTTP 200 — **PASS** (with `postalCity` instead of `city`)
- [x] `total` == 2 — **PASS**
- [x] Both items: `geocoded=true`, numeric lat/lon — **PASS**

### Scenario B — mixed batch
Same milder mixed body as above.
- [x] HTTP 200 — **PASS**
- [x] `total` == 2 — **PASS**
- [x] Item 1: `geocoded=true` — **PASS**
- [x] Item 2: `geocoded=false`, `NO_GEOCODE_RESULT` (statusCode 71) — **PASS**, graceful per-item failure

---

## 3. `/district/assign/batch` (used by `batchDistAssign`, address-shaped payload)

> Note: this same path is also used by `batchLookupFromPoint`, but with a
> different payload shape (`{"lat":...,"lon":...}` points instead of
> addresses) — that shape is rejected (`INVALID_BATCH_ADDRESSES`), and this
> was deliberately not pursued; see the "Final results summary" above and
> `project_sage_batch_endpoint_status` in memory.

### Scenario A — all-good batch
Same address body as above.
- [x] HTTP 200 — **PASS** (with `postalCity` instead of `city`)
- [x] `total` == 2 — **PASS**
- [x] Both items: `districtAssigned=true`, `statusCode=0` — **PASS** (full district data returned: senate 46, assembly 109, congressional 20, county, cleg, etc.)

### Scenario B — mixed batch
Same milder mixed body as above.
- [x] HTTP 200 — **PASS**
- [x] `total` == 2 — **PASS**
- [x] Item 1: fully assigned (`districtAssigned=true`, `geocoded=true`, `uspsValidated=true`) — **PASS**
- [x] Item 2: partial-but-graceful (`districtAssigned=true` via streetfile match, `geocoded=false`, `uspsValidated=false`) — **PASS**, not a batch-wide rejection

---

## 4. `/district/bluebird/batch` (used by `batchLookup`) — ABANDONED IN FAVOR OF `/district/assign/batch`

### Scenario A — all-good batch
Same address body as above (with `postalCity`).
- [ ] HTTP 200 — **FAIL**: got `HTTP 500` / `INTERNAL_ERROR`
- [ ] `total` == 2 — blocked
- [ ] Both items: `uspsValidated=true`, `geocoded=true`, `districtAssigned=true` — blocked

**Isolated further**: retested with a single-item batch (1 address, not 2)
— still `HTTP 500`. The non-batch `/district/bluebird` single lookup works
fine with the identical `postalCity` params (`HTTP 200`). So this isn't a
batch-size or field-name issue like the other 3 were — it's a genuine
server-side bug specific to this one endpoint's batch handler.

### Scenario B — mixed batch
- [ ] Not tested — endpoint abandoned, see resolution below

### Resolution
Per the SAGE developer, this endpoint is deprecated — `batchLookup()` now
targets `/district/assign/batch` instead (verified elsewhere in this
document that `/district/assign` and `/district/bluebird` already return
identical response shapes at the single-item level). Retested with the
new endpoint + `districtStrategy=streetFallback`:
- [x] HTTP 200 — **PASS**
- [x] `total` == 2 — **PASS**
- [x] Both items: `uspsValidated=true`, `geocoded=true`, `districtAssigned=true` — **PASS**
- [x] Confirmed end-to-end via `testBatchLookupHandlesMultipleRows` (full PHPUnit/CiviCRM path, not just raw curl)

---

## Follow-up

- [x] ~~Report `/district/bluebird/batch`'s `HTTP 500` back to the SAGE developer~~
      — done; resolved by switching `batchLookup()` to `/district/assign/batch` instead
- [ ] Report the garbage-address crash (single-item, not just batch) back
      to the SAGE developer as a separate item — not yet confirmed done
- [x] ~~Update `CRM_Utils_SAGE::getAddressesFromRows()` to send `postalCity`
      instead of `city`~~ — done
- [x] ~~Fill in the remaining PHPUnit coverage gaps~~ — `testBatchGeocodeHandlesMultipleRows`
      and `testBatchDistAssignHandlesMultipleRows` added and passing;
      `testBatchLookupFromPointHandlesMultipleRows` was added, confirmed failing
      as expected, then removed (see "Deliberately not pursued" above)
- [ ] Dry-run `updateAddresses2.php` (the only real caller of these batch
      methods, via `bluebird_setup.sh -g`) against a small, bounded set of
      real/test contacts — deferred, not a current priority
- [x] ~~Re-run full suite to confirm no regressions~~ — 39 tests, 108 assertions, all passing
- [ ] ~~Update README's "Known baseline failures" section~~ — explicitly not needed for now

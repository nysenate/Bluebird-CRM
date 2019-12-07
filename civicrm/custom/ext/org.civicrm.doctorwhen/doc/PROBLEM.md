# Doctor When: Problem description

Databases often track key dates and times.  For example, if Alice modifies a `Contact`, the database should store a record of when this happened.

MySQL [supports several ways of tracking dates and times](https://dev.mysql.com/doc/refman/5.7/en/date-and-time-types.html), such as `DATETIME` and `TIMESTAMP` columns.  The differences are subtle.  For many organizations which work within one timezone, they're even indistinguishable.  But for other organizations spread across multiple timezones, confusing `DATETIME` and `TIMESTAMP` can lead to inaccurate or confusing data.

Unfortunately, some unknown person living in the antiquity of the early 2000's made a mistake: they flagged a column as `DATETIME` when the more correct and maintainable choice would have been `TIMESTAMP`.  (They may have had reasons -- such as a long-standing design-bug in `DB_DataObject` which had prevented correct handling of `TIMESTAMP`s.) Then other people saw the precedent and emulated it.  Eventually, a large number of fields were created as `DATETIME` when `TIMESTAMP` would have been more appropriate.

The aim of `DoctorWhen` is to facilitate the cleanup of temporal data.  This may include activities like (a) changing the schema of particular columns, (b) re-adjusting data for a timezone, and/or (c) other inter-related fallout.

## Q: How urgent is this issue?

In the near-term, this depends on your organization:

 * If the organization doesn't use or pay attention to any problematic fields, then it doesn't matter.
 * If the organization operates within one timezone (or maybe two adjacent timezones), then the discrepancies
   may be unnoticeable.
 * If the organization operates across many timezones, then the discrepancies could become quite confusing.
   (This is particularly true if the organization straddles the International Date Line.)

In the long-term -- even if you don't have an issue today -- it may become important to address this. `TIMESTAMP`s will gradually become the standard as new installations use them by default. Future extensions, fixes, and enhancements may come to depend on having `TIMESTAMP`s.

## Q: Can you explain an example where problems arise?

Yes, but it's not simple. (If it were simple, we wouldn't be in this situation!)

Suppose that:

 * Alice is a staffer in New York (US/Eastern).
 * Bob is a staffer in Los Angeles (US/Pacific).
 * Donna is a constituent.
 * The table `civicrm_log` has a column `modified_date` stored as `DATETIME`.

We can create a problematic scenario as follows:

 * Alice edits the contact record for Donna. It is currently 11:00 AM in Alice's timezone (US/Eastern), so a new log is created with `modified_date==2017-02-03 11:00:00`.
 * Five minutes later, Bob updates the contact record for Donna. It is currently 8:05 AM in Bob's timezone (US/Pacific), so a new log is created with `modified_date=2017-02-03 08:05:00`.
 * When anyone (Alice or Bob) looks at the change log for Donna, the records are misleading:
    * Alice sees that the timestamp for her change looks correct, but the timestamp
      for Bob's change appears incorrect -- it's almost 3 hours in the past.
    * Bob sees that the timestamp for his change looks correct, but the timestamp
      for Alice's change appears incorrect -- it's almost 3 hours in the future.

In a correctly functioning system, the `modified_date` field would be stored as `TIMESTAMP`.  MySQL would internally adjust these values to match the currently configured timezone.  So:

 * When Alice edits the contact, the `modified_date` would be internally stored as `2017-02-03 16:00:00 UTC`.
 * When Bob edits the contact, the `modified_date` would be internally stored as `2017-02-03 16:05:00 UTC`.
 * When Alice views the log, all logs would be displayed in her timezone (US/Eastern).
 * When Bob views the log, all logs would be displayed in his timezone (US/Pacific).

## Q: What are some general priciples for safely working with date/time data?

 * The application should use `TIMESTAMP` for storing *logs* or other data that reflects a concrete, objective point-in-time. (This means it is internally stored as UTC and may be converted based on user expectations.)
 * When a TIMESTAMP is displayed or inputted, the timezone should be clearly communicated.
 * The application should instruct the PHP/MySQL runtimes about the current users's preferred timezone. 
   * In MySQL, this is done via `SET time_zone = timezonename;`
   * In PHP, this is done via `date_default_timezone_set(timezonename);`

> Note: One *could* produce other cogent designs.  This design is most closely aligned with (a) how the upstream designers of the PHP and MySQL platforms expected time to work and (b) how Civi's design has worked in common usage.

## Q: Why can't you just fix the bug in `civicrm-core`? Why does this require a special tool or special process?

A few basic reasons:

 * Fixing this could reveal other problems.
 * The `civicrm-core` project is part of a bigger ecosystem (which also includes system-implementers, extension-developers, and CMS developers).  The old schema using `DATETIME` was quirky and misguided, but it's entirely possible that others in the ecosystem have (a) fashioned alternative work-arounds and/or (b) trained their users to expect the quirks and/or (c) built processes or customizations which subtly depend on them.
 * The problems in this space are subjective.  While we can test/validate individual configurations and screens, we cannot conceive of (let alone test) all possible configurations a-priori.

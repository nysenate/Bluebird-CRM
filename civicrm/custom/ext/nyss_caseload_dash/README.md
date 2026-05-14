# Bluebird CRM Caseload Dashboard

A CiviCRM extension that gives Case Coordinators a real-time, consolidated view of caseload health across their team. At a glance, coordinators can monitor workload distribution, track case aging, and ensure every open case has an assigned Case Manager.

---

## Requirements

- CiviCRM 6.9.2 or higher
- Node.js (v22) and npm (for CSS development only — see [Developer Notes](#developer-notes))

---

## Installation

1. Download or clone this repository into your CiviCRM extensions directory.
2. `cv ext:enable nyss_caseload_dash` or in Bluebird, `scripts/cv.sh [INSTANCE_NAME] ext:enable nyss_caseload_dash`
3. 

---

## Configuration

After installation, ensure the following:

- Users who should have access to the dashboard are assigned the **Case Coordinator** or **Administrator** role.

---

## Usage

Dashboard components can be accessed in two ways:
1. Users can pick and choose their dashlets on the main Bluebird CRM Dashboard, http://sd99.crm.nysenate.gov/civicrm
2. For the complete experience, users can navigate to the dedicated Caseload Dashboard page, http://sd99.crm.nysenate.gov/civicrm/nyss/caseload/dashboard

### Available Dashlets

1. Caseload - District Totals

---

## Implementation

The dashboard is built primarily with **SearchKit** and **Afform**, using CiviCRM's native extension framework to minimize custom code.

Additional styling is applied via **Tailwind CSS** using the `@apply` directive in a custom stylesheet. See [Developer Notes](#developer-notes) for details on the CSS build process.

---

## Developer Notes

### CSS

Additional Tailwind CSS utility classes have been injected using Tailwind's `@apply` directive. To update those styles, Node.js and npm are required.

1. Run `npm install` to install Tailwind CSS and the Tailwind CLI.
2. Run the Tailwind CLI in watch mode:
   ```
   npx @tailwindcss/cli -i ./css_src/app.css -o ./css/app.css --watch
   ```
3. Make your edits to `css_src/app.css`.
4. With `--watch` specified, changes are automatically compiled to `css/app.css`.
5. Commit **both** files to git — `css_src/app.css` and `css/app.css`. There is no server-side or CI build process in Bluebird.

---

## License

[AGPL-3.0](LICENSE)
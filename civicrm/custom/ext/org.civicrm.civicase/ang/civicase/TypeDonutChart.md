## civicase-type-donut-chart

"civicaseTypeDonutChart" displays a donut chart about case-types.  It
automatically fetches data using the `Case.gettypestats` API.

## Usage

```html
<div civicase-type-donut-chart="{...params for Case.gettypestats...}"></div>
```

## Example:

```html
<div civicase-type-donut-chart="{my_cases: true}"></div>
```
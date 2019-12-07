## civicase-type-duration-chart

"civicaseTypeDurationChart" displays a bar-chart about the average duration
of each case-type.  It automatically fetches data using the
`Case.gettypestats` API.

## Usage

```html
<div civicase-type-duration-chart="{...parmas for Case.gettypestats...}">
```

## Example

```html
<div civicase-type-duration-chart="{my_cases: myCasesOnly}">
```

## Addendum: Alternative sizing

The current implementation uses the `dc.js` default sizing logic, in which
you provide an overall width/height, and all the other elements adjust, e.g.

```js
chart
  .width(300)
  .height(125)
  .elasticX(true);
```

It *might* be better to scale the overall chart, but this has implications
for the general layout of the page, and it requires extra work to adapt
when/if there are multiple filters active.

If this becomes necessary, consider patching to use:

```js
var gap = 5,
    fontSize = parseFloat(getComputedStyle($el[0]).fontSize),
    size = countGroup.size()+1;
chart
  .fixedBarHeight(fontSize).gap(gap)
  .width(300)
  .height(((size+1) * fontSize) + (size * gap));
```

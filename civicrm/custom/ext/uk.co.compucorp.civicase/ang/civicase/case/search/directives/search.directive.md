# civicase-search

The `civicase-search` directive defines an embedded search form.

Note: This form includes an explicit search button.

## Usage

```html
  <div
    civicase-search="filtersExpr"
    hidden-filters="filtersExpr"
    expanded="boolExpr"
    on-search="actionExpr">
  </div>
```

## Example 1

```js
$scope.defaults = {case_type_id: []};
$scope.runSearch = function(selectedFilters) {
  // Issue AJAX request with "selectedFilters".
};
```

```html
  <div
    civicase-search="defaults"
    on-search="runSearch(selectedFilters)">
  </div>
```

## Example 2

```js
$scope.defaults = {case_type_id: []};
$scope.hidden = {contact_id: [123]};
$scope.runSearch = function(selectedFilters) {
  // Issue AJAX request with "selectedFilters".
};
```

```html
  <div
    civicase-search="defaults"
    hidden-filters="hidden"
    on-search="runSearch(selectedFilters)">
  </div>
```

## uibTabsetClass

This module provides a single directive, `uib-tabset-class`, which allows
extra styling on the [`uib-tabset`](https://angular-ui.github.io/bootstrap/)
directive.

For example, suppose you want a tab bar with this markup:

```html
<ul class="nav nav-pills nav-pills-horizontal nav-pills-horizontal-primary">
```

You can come close with this markup:

```html
<uib-tabset type="pills">
```

However, there's no way to include the final two CSS classes. This directive
allows them:

```html
<uib-tabset type="pills" uib-tabset-class="nav-pills-horizontal nav-pills-horizontal-primary">
```

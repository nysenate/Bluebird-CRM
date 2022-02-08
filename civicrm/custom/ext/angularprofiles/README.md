# org.civicrm.angularprofiles

This extension is a utility for allowing angular pages to load the backbone profile editor/selector widget.
It comes prebuilt with a service for loading backbone and necessary files as well as a directive to turn a standard input into the profile widget.

**Note:**
This module does no permission checking and if the user does not have sufficient privilege to use the widget an error message is generated (depending on context this can take the form of an alert box) once for each widget included on the page.

This module was built in conjunction with [CiviVolunteer](https://github.com/civicrm/org.civicrm.volunteer) and a working example can be seen in that project in the project edit view.

## Examples:

### Verifying that backbone is loaded:

```javascript
angular.module('myModule').config(function($routeProvider) {
  resolve: {
    profile_status: function(crmProfiles) {
      return crmProfiles.load();
    }
  }
}


angular.module('myModule').controller('myController', function($scope,profile_status) {
[...]
}

```

### Using the crm-profile-selector directive to include the widget:

```html
<input crm-profile-selector="{}" ng-model="profile.uf_group_id" />
```

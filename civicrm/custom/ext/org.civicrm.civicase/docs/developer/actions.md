Customizing the Actions Menu
============================

The case action menus are used for both single-case view and bulk case actions. They are defined as a javascript array which can be modified by a hook.

Each item in the array looks like:

    {
      title: ts('Print Case'),
      action: 'print(cases[0])',
      number: 1
    }

In the above example, this item will only show up on the single "view case" action menu because the `number` specified is 1. Actions that can handle any number of cases omit this number.

The `action` can be any valid javascript that Angular can parse. In-scope variables include `cases` (the array of selected cases) `refresh` (function to call the api and refresh the display) and the `CRM` object.

Appending a custom callback function to the `CRM` object will work fine.

The callback function will typically construct some api calls and pass them to the `refresh` function, or else return a url object like `{path: 'civicrm/case/activity', query: {action: 'add', reset: 1}}` which will be passed to `CRM.url` and opened as a popup.

Example Extension:
------------------

**mycustom.php**

    mycustom_civicrm_coreResourceList() {
      Civi::resources()
        ->addScriptFile('mycustom', 'js/duplicateCase.js')
        ->addSetting('civicase', array(
          'caseActions' => array(
            array(
              'title' => ts('Duplicate Case'),
              'action' => 'CRM.mycustom.duplicate(cases[0], refresh)',
              'number' => 1,
            )
          )
        );
    }
    
**js/duplicateCase.js**

    (function($, _) {
      CRM.mycustom = CRM.mycustom || {};
      CRM.mycustom.duplicate = function(selectedCase, refresh) {
        var apiCalls = [],
          newCase = _.cloneDeep(selectedCase);
        delete(newCase.id);
        apiCalls.push(['Case', 'create', newCase]);
        refresh(apiCalls);
      };
    })(CRM.$, CRM._);
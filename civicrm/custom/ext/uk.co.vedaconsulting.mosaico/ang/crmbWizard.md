# crmbWizard

The `crmb-wizard` directive displays a series of forms using a "wizard" idiom, i.e.
  * The user must progress through the forms in sequence.
  * Each form must be completed before going to the next one. ("Complete" meaning "passes Angular form validation.")
  * The user may jump back/forth among forms which are reachable/valid.

Some key features:
  * The layout is based on Bootstrap CSS.
  * The wizard provides a controller via `crmb-wizard-ctrl="myCtrl"` which can be used to check status and switch steps.
  * To add a step, include an `crmb-wizard-step` element.
    You can define optional/conditional steps using `ng-if`.
  * To add a button, include an item with `crmb-wizard-button-posiition`.
    You can define optional/conditional/contextual buttons using `ng-show`/`ng-hide`/`ng-if`.
  
## Annotated Example

```html
  <!--
    `crmb-wizard` defines the overall wizard flow. We'll need to define
      a few subforms and navigational buttons. We can use `myCtrl` to
      examine and manipulate the wizard's status.
  -->
  <div crmb-wizard crmb-wizard-ctrl="myCtrl">
    
    <!--
      `crmb-wizard-step` defines the form within a step. Notes:
        * Providing a weight (100, 200, etc) is a good precuation, and it's
          mandatory if you have any conditional steps.
        * Specify a title with `crm-title`.
        * Assign a unique name to the subform with `ng-form`.
        * Steps can be conditional. Use `ng-if` -- not `ng-hide` or `ng-show`.
    -->

    <div 
        crmb-wizard-step="100"
        crm-title="ts('Cat')"
        ng-form="catForm">
      Cat content.
    </div>

    <div 
        crmb-wizard-step="200"
        crm-title="ts('Dog')" 
        ng-form="dogForm">
      Dog content
    </div>

    <div
        crmb-wizard-step="300"
        crm-title="ts('Mouse')"
        ng-form="mouseForm"
        ng-if="your.opinion.mice == 'cute'">
      Mouse content
    </div>

    <!--
      `crmb-wizard-button-position` defines a navigational button. Notes:
        * Use the controller (myCtrl) to test the state of the
          wizard/form and to navigate among pages.
    -->

    <button
        class="btn btn-secondary-outline"
        crmb-wizard-button-position="left"
        ng-click="myCtrl.previous()"
        ng-show="!myCtrl.$first()">
      Previous
    </button>

    <button
        class="btn btn-primary"
        crmb-wizard-button-position="right"
        ng-click="myCtrl.next()"
        ng-show="!myCtrl.$last()"
        ng-disabled="!myCtrl.$validStep()">
      Next
    </button>

    <button
        class="btn btn-success"
        crmb-wizard-button-position="right"
        ng-show="myCtrl.$last()"
        ng-disabled="!myCtrl.$validStep()"
        ng-click="submit()">
      Finish
    </button>

  </div>
```

(function (angular) {
  var module = angular.module('civicase-base');

  module.config(onAppConfig);

  /**
   * Adds the translation service to the $rootScope's prototype object which in
   * turn will add the translation service to every scope that inherits from it.
   *
   * We are also adding the translation service as `civicaseTs` to avoid using `ts`
   * for translating strings that come from the DB. IE: we want to avoid
   * `ts(variable)`. We need to avoid this because `ts('string')` is automatically
   * picked up by CiviCRM's translation service and we want to avoid any side
   * effects.
   *
   * @param {object} $delegate delegated $rootScope reference.
   * @param {object} $injector injector service reference.
   * @returns {object} the delegated $rootScope.
   */
  function addCivicaseTsToRootScope ($delegate, $injector) {
    var ts = $injector.get('ts');
    var $rootScopePrototype = Object.getPrototypeOf($delegate);
    $rootScopePrototype.ts = ts;
    $rootScopePrototype.civicaseTs = ts;

    return $delegate;
  }

  /**
   * Decorates the $rootScope object by adding the translation service to it.
   *
   * @param {object} $provide provider service reference.
   */
  function onAppConfig ($provide) {
    $provide.decorator('$rootScope', addCivicaseTsToRootScope);
  }
})(angular);

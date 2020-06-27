(function (_, angular) {
  var module = angular.module('civicase-base');

  module.service('civicaseCrmApi', function ($q, crmApi) {
    return function (entity, action, params, message) {
      var deferred = $q.defer();

      crmApi(entity, action, params, message)
        .then(function (result) {
          var isError = _.isArray(result) && _.some(result, 'is_error');

          isError ? deferred.reject(result) : deferred.resolve(result);
        });

      return deferred.promise;
    };
  });
})(CRM._, angular);

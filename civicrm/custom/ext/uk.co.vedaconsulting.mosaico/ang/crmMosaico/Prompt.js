(function(angular, $, _) {
  angular.module('crmMosaico').factory('crmMosaicoPrompt', function($q) {
    // Display a quick question/prompt.
    // @return Promise<string>
    return function crmMosaicoPrompt(question, defaultName) {
      var dfr = $q.defer();
      var answer = window.prompt(question, defaultName);

      if (!_.isEmpty(answer)) dfr.resolve(answer);
      else dfr.reject();

      return dfr.promise;
    };
  });
})(angular, CRM.$, CRM._);

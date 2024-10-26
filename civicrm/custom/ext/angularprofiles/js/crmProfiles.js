(function(angular, $, _) {
  angular.module('crmProfileUtils', []);

  angular.module('crmProfileUtils').factory('crmProfiles', function($q, crmApi){

    //This was done as a recursive function because the scripts
    //Must execute in order.
    function loadNextScript(scripts, callback, fail) {
      var script = scripts.shift();
      var isAbsoluteUrl = /^(http|\/)/.test(script.url);
      var baseUrl = script.packages ? CRM.config.packagesBase : CRM.config.resourceBase;
      script.url = isAbsoluteUrl ? script.url : baseUrl + script.url;

      CRM.$.getScript(script.url)
        .done(function(scriptData, status) {
          if(scripts.length) {
            loadNextScript(scripts, callback, fail);
          } else {
            callback();
          }
        }).fail(function(jqxhr, settings, exception) {
          fail(exception);
        });
    }

    function loadStyleFile(url) {
      CRM.$("#backbone_resources").append('<link type="text/css" rel="stylesheet" href="'+url+'" />');
    }

    function loadBackbone() {
      var deferred = $q.defer();
      var scripts = [
        {url: 'jquery/plugins/jstree/jquery.jstree.js', weight: 0, packages: true},
        {url: 'backbone/json2.js', weight: 100, packages: true},
        {url: 'backbone/backbone.js', weight: 120, packages: true},
        {url: 'backbone/backbone.marionette.js', weight: 125, packages: true},
        {url: 'backbone/backbone.collectionsubset.js', weight: 125, packages: true},
        {url: 'backbone-forms/distribution/backbone-forms.js', weight: 130, packages: true},
        {url: 'backbone-forms/distribution/adapters/backbone.bootstrap-modal.min.js', weight: 140, packages: true},
        {url: 'backbone-forms/distribution/editors/list.min.js', weight: 140, packages: true},
        {url: CRM.crmProfileUtils.backboneInitUrl, weight: 145, packages: false},
        {url: 'js/crm.backbone.js', weight: 150, packages: false},
        {url: 'js/model/crm.schema-mapped.js', weight: 200, packages: false},
        {url: 'js/model/crm.uf.js', weight: 200, packages: false},
        {url: 'js/model/crm.designer.js', weight: 200, packages: false},
        {url: 'js/model/crm.profile-selector.js', weight: 200, packages: false},
        {url: 'js/view/crm.designer.js', weight: 200, packages: false},
        {url: 'js/view/crm.profile-selector.js', weight: 200, packages: false},
        {url: 'js/jquery/jquery.crmProfileSelector.js', weight: 250, packages: false},
        {url: 'js/crm.designerapp.js', weight: 250, packages: false}
      ];

      scripts.sort(function(a, b){
        return a.weight-b.weight;
      });


      //mess with the jQuery versions
      CRM.origJQuery = window.jQuery;
      window.jQuery = CRM.$;

      //We need to put underscore on the global scope or backbone fails to load
      window._ = CRM._;

      loadStyleFile(CRM.config.packagesBase + 'jquery/plugins/jstree/themes/default/style.css');
      loadStyleFile(CRM.config.packagesBase + 'backbone-forms/distribution/templates/default.css');
      loadStyleFile(CRM.config.resourceBase + 'css/crm.designer.css');


      //This is a recursive function that takes a list of scripts
      //and a pair of callbacks. It will load the scripts in order
      //from the list, and then call the callback. Errors will result in
      //Calling the error callback.
      loadNextScript(scripts, function () {
        window.jQuery = CRM.origJQuery;
        delete CRM.origJQuery;
        delete window._;
        deferred.resolve(true);
      }, function(status) {
        deferred.resolve(status);
      });

      return deferred.promise;
    }

    function loadSettings() {
      var deferred = $q.defer();
      //Fetch the settings from the api
      crmApi('profile', 'getangularsettings').then(function(result) {
        if(result.hasOwnProperty('values')) {
          CRM.$.extend(true, CRM, result.values);
        }
        if (!verifyBackbone()) {
          loadBackbone().then(function() {
            deferred.resolve(true);
          });
        } else {
          deferred.resolve(true);
        }
      }, function(status) {
        deferred.reject(status);
      });
      return deferred.promise;
    }

    function loadTemplate() {
      var deferred = $q.defer();

      //Load the template;
      CRM.$("body").append("<div id='backbone_templates'></div>");
      CRM.$("#backbone_templates").load(CRM.url("civicrm/angularprofiles/template", {snippet: 5}), function(response) {
        deferred.resolve(response);
      });

      return deferred.promise;
    }
    function verifyBackbone() {
      return !!CRM.Backbone;
    }
    function verifyTemplate() {
      return (angular.element("#designer_template").length > 0);
    }
    function verifySettings() {
      return (!!CRM.PseudoConstant && !!CRM.initialProfileList && !!CRM.contactSubTypes && !!CRM.profilePreviewKey);
    }

    return {
      verify: function() {
        return (verifyBackbone() && verifyTemplate() && verifySettings());
      },
      load: function() {
        var deferred = $q.defer();
        var promises = [];

        if (CRM.$("#backbone_resources").length < 1) {
          CRM.$("body").append("<div id='backbone_resources'></div>");
        }

        //The setting must be loaded before the libraries
        //Because the libraries depend on the settings.
        //loadSettings will once it is finished do it's own
        // check and spawn the loadBakcbone task when it is complete.
        if(!verifySettings()) {
          promises.push(loadSettings());
        } else if(!verifyBackbone()) {
          promises.push(loadBackbone());
        }

        if(!verifyTemplate()) {
          promises.push(loadTemplate());
        }

        $q.all(promises).then(
          function () {
            deferred.resolve(true);
          },
          function () {
            console.log("Failed to load all backbone resources");
            deferred.reject(ts("Failed to load all backbone resources"));
          }
        );

        return deferred.promise;
      }
    };
  })

    // Render a crmProfileSelector widget
    // Minimum usage: <input crm-profile-selector='{}' />
    // usage: <input crm-profile-selector='{dataGroupType: "Contact,Individual,Volunteer", dataEntities: [{"entity_name":"contact_1","entity_type":"IndividualModel"}], dataDefault: "", dataUsedfor: null}' />
    .directive('crmProfileSelector', function () {
      return {
        require: '?ngModel',
        scope: {
          crmProfileSelector: '='
        },
        link: function (scope, element, attrs, ngModel) {
          ngModel.$render = function () {
            if (!element.val() && !element.hasClass("rendered")) {
              element.val(ngModel.$modelValue);
              element.crmProfileSelector(scope.crmProfileSelector || {});
            }
          };
        }
      };
    });
})(angular, CRM.$, CRM._);

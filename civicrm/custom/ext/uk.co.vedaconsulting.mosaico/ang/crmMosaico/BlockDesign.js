(function(angular, $, _) {
  angular.module('crmMosaico').directive('crmMosaicoBlockDesign', function($q, crmUiHelp, dialogService, crmMosaicoTemplates, crmStatus, CrmMosaicoIframe, $timeout) {

    return {
      scope: {
        // crmMosaicoCtrl: '@',
        crmMailing: '@',
        crmMailingAttachments: '@',
        crmMailingVariant: '@',
      },
      templateUrl: '~/crmMosaico/BlockDesign.html',
      link: function (scope, elm, attr) {
        scope.$parent.$watch(attr.crmMailing, function(newValue){
          scope.mailing = newValue;
        });
        scope.$parent.$watch(attr.crmMailingAttachments, function(newValue){
          scope.attachments = newValue;
        });
        scope.$parent.$watch(attr.crmMailingVariant, function(newValue){
          scope.variantId = newValue;
        });
        scope.crmMailingConst = CRM.crmMailing;
        scope.ts = CRM.ts(null);
        scope.hs = crmUiHelp({file: 'CRM/Mailing/MailingUI'});

        function _object(prop) {
          const mailing = scope.mailing;
          mailing.template_options = mailing.template_options || {};

          if (scope.variantId !== null) return mailing.template_options.variants[scope.variantId];
          return prop.match(/^mosaico/) ? mailing.template_options : mailing;
        }
        function getProp(prop) { return _object(prop)[prop]; }
        function setProp(prop, value) { _object(prop)[prop] = value; }
        function deleteProp(prop) { delete _object(prop)[prop]; }

        const $scope = scope;
        var crmMosaicoIframe = null, activeDialogs = {};
        $scope.mosaicoCtrl = {
          templates: [],
          // Fill a given "mailing" which the chosen "template".
          select: function(template) {
            var promise = crmMosaicoTemplates.getFull(template).then(function(tplCtnt){
              setProp('mosaicoTemplate', template.id);
              setProp('mosaicoMetadata', tplCtnt.metadata);
              setProp('mosaicoContent', tplCtnt.content);
              setProp('body_html', tplCtnt.html);
              $scope.mosaicoCtrl.edit();
            });
            return crmStatus({start: ts('Loading...'), success: null}, promise);
          },
          hasSelection: function() {
            return !!getProp('mosaicoTemplate');
          },
          hasMarkup: function() {
            return !!getProp('body_html');
          },
          // Figure out which "template" was previously used with a "mailing."
          getTemplate: function() {
            const mailing = scope.mailing;
            if (!mailing || !getProp('mosaicoTemplate')) {
              return null;
            }
            var matches = _.where($scope.mosaicoCtrl.templates, {
              id: getProp('mosaicoTemplate')
            });
            return matches.length > 0 ? matches[0] : null;
          },
          // Reset all Mosaico data in a "mailing'.
          reset: function() {
            if (crmMosaicoIframe) crmMosaicoIframe.destroy();
            crmMosaicoIframe = null;
            deleteProp('mosaicoTemplate');
            deleteProp('mosaicoMetadata');
            deleteProp('mosaicoContent');
            setProp('body_html', '');
          },
          // Edit a mailing in Mosaico.
          edit: function() {
            if (crmMosaicoIframe) {
              crmMosaicoIframe.show();
              return;
            }

            function syncModel(viewModel) {
              setProp('body_html', viewModel.exportHTML());
              // Mosaico exports JSON. Keep their original encoding... or else the loader throws an error.
              setProp('mosaicoMetadata', viewModel.exportMetadata());
              setProp('mosaicoContent', viewModel.exportJSON());
            }

            crmMosaicoIframe = new CrmMosaicoIframe({
              model: {
                template: $scope.mosaicoCtrl.getTemplate().path,
                metadata: getProp('mosaicoMetadata'),
                content: getProp('mosaicoContent')
              },
              actions: {
                sync: function(ko, viewModel) {
                  syncModel(viewModel);
                },
                close: function(ko, viewModel) {
                  viewModel.metadata.changed = Date.now();
                  syncModel(viewModel);
                  // TODO: When autosave is better integrated, remove this.
                  $timeout(function(){
                    $scope.$parent.$apply(attr.onSave);
                  }, 100);
                  crmMosaicoIframe.hide('crmMosaicoEditorDialog');
                },
                test: function(ko, viewModel) {
                  syncModel(viewModel);

                  var model = {mailing: $scope.mailing, attachments: $scope.attachments, variantId: $scope.variantId};
                  var options = CRM.utils.adjustDialogDefaults(angular.extend(
                    {autoOpen: false, title: ts('Preview / Test'), width: 550},
                    options
                  ));
                  activeDialogs.crmMosaicoPreviewDialog = 1;
                  var pr = dialogService.open('crmMosaicoPreviewDialog', '~/crmMosaico/PreviewDialogCtrl.html', model, options)
                    .finally(function(){ delete activeDialogs.crmMosaicoPreviewDialog; });
                  return pr;
                }
              }
            });

            return crmStatus({start: ts('Loading...'), success: null}, crmMosaicoIframe.open());
          }
        };

        crmMosaicoTemplates.whenLoaded().then(function(){
          $scope.mosaicoCtrl.templates = crmMosaicoTemplates.getAll();
          $scope.mosaicoCtrl.categoryFilters = _.transform(crmMosaicoTemplates.getCategories(), function(filters, category) {
            filters.push({id: filters.length, text: category.label, filter: {category_id: category.value}});
          }, [{id: 0, text: ts('Base Template'), filter: {isBase: true}}]);
        });

        $scope.$on("$destroy", function() {
          angular.forEach(activeDialogs, function(v,name){
            dialogService.cancel(name);
          });
          if (crmMosaicoIframe) {
            crmMosaicoIframe.destroy();
            crmMosaicoIframe = null;
          }
        });


      }
    };
  });
})(angular, CRM.$, CRM._);

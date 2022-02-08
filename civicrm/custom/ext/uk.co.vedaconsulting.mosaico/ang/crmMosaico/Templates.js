(function (angular, $, _) {

  angular.module('crmMosaico').factory('crmMosaicoTemplates', function ($q, crmApi, $timeout, CrmMosaicoIframe, crmBlocker, crmStatus) {
    var ts = CRM.ts(null);
    var cache = {};
    var block = crmBlocker();

    // Track zero or one instances of CrmMosaicoIframe.
    var currentIframe, warmTplId;

    function filterBase(base) {
      return {
        id: 'base-' + base.name,
        baseDetails: base,
        title: ts('Empty Template'),
        type: base.title,
        thumbnail: base.thumbnail,
        path: base.path,
        isBase: true,
        category_id: null,
        category: ts('Base Template'),
        isHidden: base.is_hidden
      };
    }
    function filterTemplate(tpl) {
      var base = cache.basesByName[tpl.base];
      return {
        id: tpl.id,
        baseDetails: base,
        title: tpl.title,
        type: base.title,
        thumbnail: base.thumbnail,
        path: base.path,
        base: tpl.base,
        category_id: tpl.category_id,
        category: tpl.category_id && cache.categoriesByValue[tpl.category_id] ? cache.categoriesByValue[tpl.category_id].label : '',
        isBase: false
      };
    }

    crmApi({
      bases: ['MosaicoBaseTemplate', 'get', {sequential: 1, options: {sort: 'title ASC', limit: 0}}],
      templates: ['MosaicoTemplate', 'get', {sequential: 1, options: {sort: 'title ASC', limit: 0}, return: ['title', 'base', 'category_id']}],
      categories: ['OptionValue', 'get', {sequential: 1, is_active: 1, option_group_id: 'mailing_template_category', options: {sort: 'weight ASC', limit: 0}, return: ['value', 'label']}]
    }).then(function(r){
      cache.categories = r.categories.values;
      cache.categoriesByValue = _.indexBy(r.categories.values, 'value');
      cache.basesByName = _.indexBy(r.bases.values, 'name');
      cache.bases = _.map(r.bases.values, filterBase);
      cache.configured = _.map(r.templates.values, filterTemplate);
      cache.all = _.union(cache.bases, cache.configured);
    });

    function arrayDel(array, item) {
      var p = _.indexOf(array, item);
      if (p >= 0) array.splice(p, 1);
    }

    function register(tpl) {
      cache.configured.push(tpl);
      cache.all.push(tpl);
      return tpl;
    }

    function getFull(template) {
      if (!template.isBase) {
        return crmApi('MosaicoTemplate', 'getsingle', {id: template.id});
      }
      return $q(function(resolve){
        $timeout(function(){resolve({});}, 100);
      });
    }

    return {
      // Return Promise<void>
      whenLoaded: function whenLoaded() {
        return $q(function(resolve) {
          var poll = function() {
            if (cache.all !== undefined) resolve();
            else $timeout(poll, 100);
          };
          poll();
        });
      },
      // @return Promise<Template>
      create: function(params) {
        return crmApi('MosaicoTemplate', 'create', params).then(function(r){
          return register(filterTemplate(r.values[r.id]));
        });
      },
      // @return Promise<Template>
      clone: function(params) {
        return crmApi('MosaicoTemplate', 'clone', params).then(function(r){
          return register(filterTemplate(r.values[r.id]));
        });
      },
      // @return Promise<null>
      'delete': function(tpl) {
        if (tpl.isBase) throw "Cannot delete base template";
        return crmApi('MosaicoTemplate', 'delete', {id: tpl.id}).then(function(){
          arrayDel(cache.configured, tpl);
          arrayDel(cache.all, tpl);
        });
      },
      // Load the full content of a template (HTML, metadata, content -- as applicable).
      getFull: getFull,
      getBases: function() {
        return cache.bases.filter((template) => !template.isHidden);
      },
      getConfigured: function(){ return cache.configured; },
      getAll: function() {
        return cache.all.filter((template) => !template.isHidden);
      },
      getCategories: function() {
        return cache.categories;
      },
      save: function(tplId, viewModel) {
        viewModel.metadata.changed = Date.now();
        return crmApi('MosaicoTemplate', 'create', {
          id: tplId,
          html: viewModel.exportHTML(),
          metadata: viewModel.exportMetadata(),
          content: viewModel.exportJSON()
        });
      },
      edit: function(tpl) {
        function save(tplId, viewModel) {
          viewModel.metadata.changed = Date.now();
          return crmApi('MosaicoTemplate', 'create', {
            id: tplId,
            html: viewModel.exportHTML(),
            metadata: viewModel.exportMetadata(),
            content: viewModel.exportJSON()
          });
        }

        return $q(function(resolve, reject) {
          if (block.check()) {
            return reject({});
          }

          if (warmTplId === tpl.id) {
            currentIframe.show();
            return reject({});
          }

          warmTplId = tpl.id;
          var openPromise = getFull(tpl).then(function(fullTpl) {
            if (currentIframe) currentIframe.destroy();
            // FIXME: baseDetails seems like redundant data
            var base = tpl.baseDetails || cache.basesByName[tpl.base];
            currentIframe = new CrmMosaicoIframe({
              model: {
                template: base.path,
                metadata: fullTpl.metadata,
                content: fullTpl.content
              },
              actions: {
                sync: function(ko, viewModel) {
                  var savePromise = save(tpl.id, viewModel);
                  crmStatus({start: ts('Saving'), success: ts('Saved')}, savePromise);
                },
                save: function(ko, viewModel) {
                  var savePromise = save(tpl.id, viewModel).then(function(result) {
                    currentIframe.hide();
                    resolve(result);
                  });
                  crmStatus({start: ts('Saving'), success: ts('Saved')}, savePromise);
                }
              }
            });
            return currentIframe.open();
          });

          block(crmStatus({start: ts('Loading...'), success: null}, openPromise));
        });
      }
    };
  });

})(angular, CRM.$, CRM._);

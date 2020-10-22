(function (angular, $, _) {

  angular.module('crmMosaico').factory('crmMosaicoTemplates', function ($q, crmApi, $timeout) {
    var ts = CRM.ts(null);
    var cache = {};

    function filterBase(base) {
      return {
        id: 'base-' + base.name,
        baseDetails: base,
        title: ts('Empty Template'),
        type: base.title,
        thumbnail: base.thumbnail,
        path: base.path,
        isBase: true
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
        isBase: false
      };
    }

    crmApi({
      bases: ['MosaicoBaseTemplate', 'get', {sequential: 1, 'options': {'sort': 'title ASC', 'limit': 0}}],
      templates: ['MosaicoTemplate', 'get', {sequential: 1, 'options': {'sort': 'title ASC', 'limit': 0}, return:['title', 'base']}]
    }).then(function(r){
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
      getFull: function getFull(template) {
        if (!template.isBase) {
          return crmApi('MosaicoTemplate', 'getsingle', {id: template.id});
        }
        return $q(function(resolve){
          $timeout(function(){resolve({});}, 100);
        });
      },
      getBases: function(){ return cache.bases; },
      getConfigured: function(){ return cache.configured; },
      getAll: function(){ return cache.all; }
    };
  });

})(angular, CRM.$, CRM._);

(function(angular, $, _, undefined) {

  // Cache schema metadata
  var schema = [];
  // Cache fk schema data
  var links = [];
  // Cache list of entities
  var entities = [];
  // Cache list of actions
  var actions = [];

  angular.module('api4').config(function($routeProvider) {
      $routeProvider.when('/api4/:api4entity?/:api4action?', {
        controller: 'Api4Explorer',
        templateUrl: '~/api4/Explorer.html',
        reloadOnSearch: false
      });
    }
  );

  angular.module('api4').controller('Api4Explorer', function($scope, $routeParams, $location, $timeout, crmUiHelp, crmApi4) {
    var ts = $scope.ts = CRM.ts('api4');
    var hs = $scope.hs = crmUiHelp({file: 'CRM/Api4/Explorer'});
    $scope.entities = entities;
    $scope.operators = arrayToSelect2(CRM.vars.api4.operators);
    $scope.actions = actions;
    $scope.fields = [];
    $scope.availableParams = {};
    $scope.params = {};
    var richParams = {where: 'array', values: 'object', orderBy: 'object'};
    var getMetaParams = schema.length ? {} : {schema: ['Entity', 'getFields'], links: ['Entity', 'getLinks']};
    $scope.entity = $routeParams.api4entity;
    $scope.result = [];
    $scope.status = 'default';
    $scope.loading = false;
    $scope.controls = {};
    $scope.code = {
      php: '',
      javascript: ''
    };

    function ucfirst(str) {
      return str[0].toUpperCase() + str.slice(1);
    }

    function lcfirst(str) {
      return str[0].toLowerCase() + str.slice(1);
    }

    function pluralize(str) {
      switch (str[str.length-1]) {
        case 's':
          return str + 'es';
        case 'y':
          return str.slice(0, -1) + 'ies';
        default:
          return str + 's';
      }
    }

    // Turn a flat array into a select2 array
    function arrayToSelect2(array) {
      var out = [];
      _.each(array, function(item) {
        out.push({id: item, text: item});
      });
      return out;
    }

    // Reformat an existing array of objects for compatibility with select2
    function formatForSelect2(input, container, key, extra, prefix) {
      _.each(input, function(item) {
        var id = (prefix || '') + item[key];
        var formatted = {id: id, text: id};
        if (extra) {
          _.merge(formatted, _.pick(item, extra));
        }
        container.push(formatted);
      });
      return container;
    }

    function entityFields(entity) {
      return _.result(_.findWhere(schema, {entity: entity}), 'fields');
    }

    function getFieldList() {
      var fields = [],
        fks = _.findWhere(links, {entity: $scope.entity}) || {};
      formatForSelect2(entityFields($scope.entity), fields, 'name', ['description', 'required', 'default_value']);
      _.each(fks.links, function(link) {
        var linkFields = entityFields(link.entity);
        if (linkFields) {
          fields.push({
            text: link.alias,
            description: 'Join to ' + link.entity,
            children: formatForSelect2(linkFields, [], 'name', ['description'], link.alias + '.')
          });
        }
      });
      return fields;
    }

    $scope.valuesFields = function() {
      var fields = [];
      _.each(_.cloneDeep($scope.fields), function(field, index) {
        if (field.id === 'id' || field.children) {
          return;
        }
        if ($scope.params.values && typeof $scope.params.values[field.id] !== 'undefined') {
          field.disabled = true;
        }
        fields.push(field);
      });
      return fields;
    };

    $scope.formatSelect2Item = function(row) {
      return _.escape(row.text) +
        (isFieldRequiredForCreate(row) ? '<span class="crm-marker"> *</span>' : '') +
        (row.description ? '<div class="crm-select2-row-description"><p>' + _.escape(row.description) + '</p></div>' : '');
    };

    function isFieldRequiredForCreate(field) {
      return field.required && !field.default_value;
    }

    // Get all params that have been set
    function getParams() {
      var params = {};
      _.each($scope.params, function(param, key) {
        if (param != $scope.availableParams[key].default && !(typeof param === 'object' && _.isEmpty(param))) {
          params[key] = param;
        }
      });
      _.each(richParams, function(type, key) {
        if (params[key] && type === 'object') {
          var newParam = {};
          _.each(params[key], function(item) {
            newParam[item[0]] = item[1];
          });
          params[key] = newParam;
        }
      });
      return params;
    }

    function selectAction() {
      $scope.action = $routeParams.api4action;
      $scope.fields = getFieldList();
      if ($scope.action) {
        var actionInfo = _.findWhere(actions, {id: $scope.action});
        _.each(actionInfo.params, function (param, name) {
          var format, defaultVal;
          if (param.type) {
            switch (param.type[0]) {
              case 'int':
              case 'bool':
                format = param.type[0];
                break;

              case 'array':
              case 'object':
                format = 'json';
                break;

              default:
                format = 'raw';
            }
            if (name == 'limit') {
              defaultVal = 25;
            }
            if (name == 'checkPermissions') {
              defaultVal = true;
            }
            $scope.$bindToRoute({
              expr: 'params["' + name + '"]',
              param: name,
              format: format,
              default: defaultVal,
              deep: name === 'where'
            });
          }
          if (richParams[name]) {
            $scope.$watch('params.' + name, function(values) {
              // Remove empty values
              _.each(values, function(clause, index) {
                if (!clause[0]) {
                  $scope.params[name].splice(index, 1);
                }
              });
            }, true);
            $scope.$watch('controls.' + name, function(value) {
              var field = value;
              $timeout(function() {
                if (field) {
                  var defaultOp = {orderBy: 'ASC', where: '=', values: ''}[name];
                  if (_.isEmpty($scope.params[name])) {
                    $scope.params[name] = [[field, defaultOp]];
                  } else {
                    $scope.params[name].push([field, defaultOp]);
                  }
                  $scope.controls[name] = null;
                }
              });
            });
          }
        });
        $scope.availableParams = actionInfo.params;
      }
      writeCode();
    }

    function writeCode() {
      var code = {
        php: ts('Select an entity and action'),
        javascript: ''
      },
        entity = $scope.entity,
        action = $scope.action,
        params = getParams(),
        result = 'result';
      if ($scope.entity && $scope.action) {
        if (action.slice(0, 3) === 'get') {
          result = lcfirst(action.replace(/s$/, '').slice(3) || entity);
        }
        var results = lcfirst(pluralize(result)),
          paramCount = _.size(params),
          i = 0;
        code.javascript = "CRM.api4('" + entity + "', '" + action + "', {";
        _.each(params, function(param, key) {
          code.javascript += "\n  " + key + ': ' + JSON.stringify(param) +
            (++i < paramCount ? ',' : '');
          if (key === 'checkPermissions') {
            code.javascript += ' // IGNORED: permissions are always enforced from client-side requests';
          }
        });
        code.javascript += "\n}).done(function(" + results + ") {\n  // do something with " + results + " array\n});";
        code.php = '$' + results + " = \\Civi\\Api4\\" + entity + '::' + action + '()';
        _.each(params, function(param, key) {
          if (richParams[key]) {
            _.each(param, function(item, index) {
              var val = '';
              if (richParams[key] === 'array') {
                _.each(item, function (it) {
                  val += ((val.length ? ', ' : '') + JSON.stringify(it));
                });
              } else {
                val = JSON.stringify(index) + ', ' + JSON.stringify(item);
              }
              code.php += "\n  ->add" + ucfirst(key).replace(/s$/, '') + '(' + val + ')';
            });
          } else {
            code.php += "\n  ->set" + ucfirst(key) + '(' + JSON.stringify(param) + ')';
          }
        });
        code.php += "\n  ->execute();\nforeach ($" + results + ' as $' + result + ') {\n  // do something\n}';
      }
      $scope.code = code;
    }

    $scope.execute = function() {
      $scope.status = 'warning';
      $scope.loading = true;
      crmApi4($scope.entity, $scope.action, getParams())
        .then(function(data) {
          var meta = {length: data.length},
            result = JSON.stringify(data, null, 2);
          data.length = 0;
          _.assign(meta, data);
          $scope.loading = false;
          $scope.status = 'success';
          $scope.result = [JSON.stringify(meta).replace('{', '').replace(/}$/, ''), result];
        }, function(data) {
          $scope.loading = false;
          $scope.status = 'danger';
          $scope.result = [JSON.stringify(data, null, 2)];
        });
    };

    function fetchMeta() {
      crmApi4(getMetaParams)
        .then(function(data) {
          if (data.schema) {
            schema = data.schema;
            entities.length = 0;
            formatForSelect2(schema, entities, 'entity');
          }
          if (data.links) {
            links = data.links;
          }
          if (data.actions) {
            formatForSelect2(data.actions, actions, 'name', ['description', 'params']);
            selectAction();
          }
        });
    }

    if (!$scope.entity) {
      $scope.helpTitle = ts('Help');
      $scope.helpText = [ts('Welcome to the api explorer.'), ts('Select an entity to begin.')];
      if (getMetaParams.schema) {
        fetchMeta();
      }
    } else if (!actions.length) {
      if (getMetaParams.schema) {
        entities.push({id: $scope.entity, text: $scope.entity});
      }
      getMetaParams.actions = [$scope.entity, 'getActions'];
      fetchMeta();
    } else {
      selectAction();
    }

    if ($scope.entity) {
      $scope.helpTitle = $scope.entity;
      $scope.helpText = [ts('Select an action')];
    }

    // Update route when changing entity
    $scope.$watch('entity', function(newVal, oldVal) {
      if (oldVal !== newVal) {
        // Flush actions cache to re-fetch for new entity
        actions = [];
        $location.url('/api4/' + newVal);
      }
    });

    // Update route when changing actions
    $scope.$watch('action', function(newVal, oldVal) {
      if ($scope.entity && $routeParams.api4action !== newVal && !_.isUndefined(newVal)) {
        $location.url('/api4/' + $scope.entity + '/' + newVal);
      } else if (newVal) {
        $scope.helpTitle = $scope.entity + '::' + newVal;
        $scope.helpText = [_.findWhere(actions, {id: newVal}).description];
      }
    });

    $scope.$watch('params', writeCode, true);
    writeCode();

  });

  // Collapsible optgroups for select2
  $(function() {
    $('body')
      .on('select2-open', function(e) {
        if ($(e.target).hasClass('collapsible-optgroups')) {
          $('#select2-drop')
            .off('.collapseOptionGroup')
            .addClass('collapsible-optgroups-enabled')
            .on('click.collapseOptionGroup', '.select2-result-with-children > .select2-result-label', function() {
              $(this).parent().toggleClass('optgroup-expanded');
            });
        }
      })
     .on('select2-close', function() {
        $('#select2-drop').off('.collapseOptionGroup').removeClass('collapsible-optgroups-enabled');
      });
  });
})(angular, CRM.$, CRM._);

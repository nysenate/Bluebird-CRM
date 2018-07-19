(function(angular, $, _) {

  angular.module('contactlayout').config(function($routeProvider) {
      $routeProvider.when('/contact-summary-editor', {
        controller: 'Contactlayoutcontactlayout',
        templateUrl: '~/contactlayout/contactlayout.html',

        // If you need to look up data when opening the page, list it out
        // under 'resolve'.
        resolve: {
          profile_status: function(crmProfiles) {
            return crmProfiles.load();
          },
          data: function(crmApi4) {
            return crmApi4({
              layouts: ['ContactLayout', 'get', {orderBy: {weight: 'ASC'}}],
              blocks:  ['ContactLayout', 'getBlocks'],
              contactTypes: ['ContactType', 'get'],
              groups: ['Group', 'get', {
                select: ['name','title','description'],
                where: [['is_hidden','=','0'],['is_active','=','1'],['saved_search_id','IS NULL','']]
              }]
            });
          }
        }
      });
    }
  );

  // The controller uses *injection*. This default injects a few things:
  //   $scope -- This is the set of variables shared between JS and HTML.
  //   crmApi, crmStatus, crmUiHelp -- These are services provided by civicrm-core.
  //   data -- defined above in config().
  angular.module('contactlayout').controller('Contactlayoutcontactlayout', function($scope, $timeout, crmApi4, crmStatus, crmUiHelp, data) {
    var ts = $scope.ts = CRM.ts('contactlayout');
    var hs = $scope.hs = crmUiHelp({file: 'CRM/contactlayout/contactlayout'});
    $scope.paletteGroups = {};
    $scope.selectedLayout = null;
    $scope.changesSaved = 1;
    $scope.saving = false;
    $scope.contactTypes = data.contactTypes;
    $scope.layouts = data.layouts;
    var newLayoutCount = 0,
      profileEntities = [{entity_name: "contact_1", entity_type: "IndividualModel"}],
      allBlocks = loadBlocks(data.blocks);

    $scope.selectLayout = function(layout) {
      $scope.selectedLayout = layout;
    };

    function getLabels(name, data) {
      if (_.isArray(name)) {
        var ret = [];
        _.each(name, function(n) {
          ret.push(getLabels(n, data));
        });
        return ret;
      }
      var values = _.where(data, {name: name})[0];
      return values.label || values.title;
    }

    $scope.showContactTypes = function(layout) {
      if (layout.contact_sub_type && layout.contact_sub_type.length) {
        return getLabels(layout.contact_sub_type, data.contactTypes).join(', ');
      }
      if (layout.contact_type) {
        return getLabels(layout.contact_type, data.contactTypes);
      }
      return ts('All contact types');
    };

    $scope.clearSubType = function(layout) {
      layout.contact_sub_type = null;
    };

    $scope.showGroups = function(layout) {
      if (layout.groups && layout.groups.length) {
        return getLabels(layout.groups, data.groups).join(', ');
      }
      return ts('All users');
    };

    $scope.selectableSubTypes = function(contactType) {
      typeId = _.where(data.contactTypes, {name: contactType})[0].id;
      return _.where(data.contactTypes, {parent_id: typeId});
    };

    $scope.removeBlock = function(index, blocks) {
      blocks.splice(index, 1);
      loadLayout($scope.selectedLayout);
    };

    $scope.editBlock = function(block) {
      var edited;
      if (block.profile_id) {
        editProfile(block.profile_id);
      } else {
        CRM.loadForm(CRM.url(block.edit))
          .on('crmFormSuccess', function() {
            edited = true;
          })
          .on('dialogclose', function() {
            if (edited) {
              reloadBlocks();
            }
          });
      }
    };

    $scope.deleteBlock = function(block) {
      var message = [_.escape(ts('Delete the block "%1"?', {1: block.title}))];
      _.each($scope.layouts, function (layout) {
        if (_.where(layout.blocks[0].concat(layout.blocks[1]), {name: block.name}).length) {
          message.push(_.escape(ts('It is currently part of the "%1" layout.', {1: layout.label})));
        }
      });
      CRM.confirm({
        message: '<p>' + message.join('</p><p>') + '</p>',
        options: {no: ts('No'), yes: ts('Yes')}
      })
        .on('crmConfirm:yes', function() {
          // Remove block from all layouts
          _.each($scope.layouts, function (layout) {
            _.each(layout.blocks, function(blocks) {
              var idx = _.findIndex(blocks, {name: block.name});
              if (idx > -1) {
                blocks.splice(idx, 1);
              }
            });
          });
          reloadBlocks([['UFGroup', 'delete', {where: [['id', '=', block.profile_id]]}]]);
        });
    };

    $scope.enforceUnique = function(e, ui) {
      if (!ui.item.sortable.received &&
        $(ui.item.sortable.droptarget).is('#cse-palette'))
      {
        ui.item.sortable.cancel();
      }
    };

    $scope.newLayout = function() {
      var newLayout = {
        label: ts('Untitled %1', {1: ++newLayoutCount}),
        blocks: [[],[]],
        palette: _.cloneDeep(allBlocks)
      };
      $scope.deletedLayout = null;
      $scope.layouts.unshift(newLayout);
      $scope.selectedLayout = newLayout;
    };

    $scope.deleteLayout = function(index) {
      $scope.deletedLayout = $scope.layouts[index];
      if ($scope.selectedLayout === $scope.layouts[index]) {
        $scope.selectedLayout = null;
      }
      $scope.layouts.splice(index, 1);
    };

    $scope.restoreLayout = function() {
      $scope.layouts.unshift($scope.deletedLayout);
      $scope.selectedLayout = $scope.deletedLayout;
      $scope.deletedLayout = null;
    };

    $scope.newProfile = function() {
      var profileEditor = new CRM.Designer.DesignerDialog({
        findCreateUfGroupModel: function(options) {
          // Initialize new UF group
          var ufGroupModel = new CRM.UF.UFGroupModel();
          ufGroupModel.getRel('ufEntityCollection').reset(profileEntities);
          options.onLoad(ufGroupModel);
        }
      }).render();
      CRM.designerApp.vent.off('ufSaved', null, 'contactlayout');
      CRM.designerApp.vent.on('ufSaved', function() {
        var newId = profileEditor.model.get('id');
        // Save a record of this new profile as a contact summary block so this extension recognizes it.
        // Also save it as a profile form so that you can click to edit and it will render a form on the summary screen.
        reloadBlocks([
          ['UFJoin', 'create', {values: {module: "Profile", uf_group_id: newId}}],
          ['UFJoin', 'create', {values: {module: "Contact Summary", uf_group_id: newId}}]
        ]);
      }, 'contactlayout');
    };

    function editProfile(ufId) {
      var profileEditor = new CRM.Designer.DesignerDialog({
        // Copied from crm.profile-selector.js doEdit() method.
        findCreateUfGroupModel: function(options) {
          CRM.api('UFGroup', 'getsingle', {id: ufId, "api.UFField.get": 1}, {
            success: function(formData) {
              // Note: With chaining, API returns some extraneous keys that aren't part of UFGroupModel
              var ufGroupModel = new CRM.UF.UFGroupModel(_.pick(formData, _.keys(CRM.UF.UFGroupModel.prototype.schema)));
              ufGroupModel.setUFGroupModel(ufGroupModel.calculateContactEntityType(), profileEntities);
              ufGroupModel.getRel('ufFieldCollection').reset(_.values(formData["api.UFField.get"].values));
              options.onLoad(ufGroupModel);
            }
          });
        }
      }).render();
      CRM.designerApp.vent.off('ufSaved', null, 'contactlayout');
      CRM.designerApp.vent.on('ufSaved', function() {
        reloadBlocks();
      }, 'contactlayout');
    }

    // Called when pressing the save button
    $scope.save = function() {
      var data = [],
        layoutWeight = 0,
        emptyLayouts = [],
        noLabel = false;
      _.each($scope.layouts, function(layout) {
        var empty = true;
        var item = {
          label: layout.label,
          weight: ++layoutWeight,
          id: layout.id,
          contact_type: layout.contact_type || null,
          contact_sub_type: layout.contact_sub_type && layout.contact_sub_type.length ? layout.contact_sub_type : null,
          groups: layout.groups && layout.groups.length ? layout.groups : null,
          blocks: [[],[]]
        };
        _.each(layout.blocks, function(blocks, col) {
          _.each(blocks, function(block) {
            item.blocks[col].push(_.pick(block, 'name'));
            empty = false;
          });
        });
        if (!layout.label) {
          noLabel = true;
          alert(ts('Please give the layout a name.'));
          return;
        }
        if (empty) {
          emptyLayouts.push(layout.label);
        }
        data.push(item);
      });
      if (emptyLayouts.length) {
        alert(ts('The layout %1 is empty. Please add at least one block before saving.', {1: emptyLayouts.join(', ')}));
      } else if (!noLabel) {
        writeRecords(data);
      }
    };

    // Write layout data to the server
    function writeRecords(data) {
      $scope.saving = true;
      $scope.deletedLayout = null;
      // Replace records (or delete all if there are none)
      var apiCall = ['ContactLayout', 'delete', {where: [['id', 'IS NOT NULL']]}];
      if (data.length) {
        apiCall = ['ContactLayout', 'replace', {records: data}];
      }
      CRM.api4([apiCall])
        .done(function () {
          $scope.$apply(function () {
            $scope.saving = false;
            $scope.changesSaved = true;
          });
        });
    }

    function loadBlocks(blockData) {
      allBlocks = [];
      _.each(blockData, function(group) {
        $scope.paletteGroups[group.name] = {title: group.title, icon: group.icon};
        _.each(group.blocks, function(block) {
          block.group = group.name;
          block.icon = group.icon;
          allBlocks.push(block);
        });
      });
      return allBlocks;
    }

    function loadLayouts() {
      _.each($scope.layouts, loadLayout);
    }

    function loadLayout(layout) {
      layout.palette = _.cloneDeep(allBlocks);
      _.each(layout.blocks, function(column) {
        _.each(column, function(block) {
          $.extend(block, _.where(layout.palette, {name: block.name})[0]);
          _.remove(layout.palette, {name: block.name});
        });
      });
    }

    function reloadBlocks(apiCalls) {
      apiCalls = apiCalls || [];
      apiCalls.push(['ContactLayout', 'getBlocks']);
      $scope.deletedLayout = null;
      CRM.api4(apiCalls)
        .done(function(data) {
          $scope.$apply(function() {
            allBlocks = loadBlocks(_.last(data));
            loadLayouts($scope.layouts);
          });
        });
    }

    // Load schema for backbone-based profile editor
    CRM.civiSchema = {
      IndividualModel: null,
      OrganizationModel: null,
      HouseholdModel: null
    };
    CRM.Schema.reloadModels();

    $scope.$watch('layouts', function (a, b) {
      $scope.changesSaved = $scope.changesSaved === 1;
    }, true);

    // Initialize
    if ($scope.layouts.length) {
      loadLayouts();
    }
    else {
      $scope.newLayout();
    }

  });

})(angular, CRM.$, CRM._);

(function(angular, $, _) {

  angular.module('contactlayout').config(function($routeProvider) {
      $routeProvider.when('/contact-summary-editor', {
        controller: 'Contactlayoutcontactlayout',
        templateUrl: '~/contactlayout/contactlayout.html',
        resolve: {
          profile_status: function(crmProfiles) {
            return crmProfiles.load();
          },
          data: function(crmApi4) {
            return crmApi4({
              layouts: ['ContactLayout', 'get', {orderBy: {weight: 'ASC'}}],
              blocks:  ['ContactLayout', 'getBlocks'],
              tabs:  ['ContactLayout', 'getTabs'],
              contactTypes: ['ContactType', 'get', {
                where: [['is_active','=','1']],
                orderBy: {label: 'ASC'}
              }],
              groups: ['Group', 'get', {
                select: ['name','title','description'],
                where: [['is_hidden','=','0'], ['is_active','=','1'], ['saved_search_id','IS NULL','']]
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
    $scope.selectedLayout = null;
    $scope.changesSaved = 1;
    $scope.saving = false;
    $scope.contactTypes = data.contactTypes;
    $scope.layouts = data.layouts;
    $scope.tabs = _.indexBy(data.tabs, 'id');
    var newLayoutCount = 0,
      editingTabIcon,
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

    $scope.contactTypeLabel = function(contactType) {
      return getLabels(contactType, data.contactTypes);
    };

    $scope.changeContactType = function(layout) {
      layout.contact_sub_type = null;
      if (layout.contact_type) {
        _.each(layout.blocks, function(row) {
          _.each(row, function(col, i) {
            row[i] = _.filter(col, function(block) {
              return !block.contact_type || block.contact_type === layout.contact_type;
            });
          });
        });
        loadLayout(layout);
      }
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
          .on('crmLoad', function(e) {
            if ($(e.target).is('.ui-dialog-content')) {
              $(this).prepend('<div class="messages status"><i class="crm-i fa-exclamation-triangle"></i> ' +
                ts('You are editing global settings, which will affect more than just this layout.') +
                '</div>'
              );
            }
          })
          .on('dialogclose', function() {
            if (edited) {
              reloadBlocks();
              CRM.Schema.reloadModels();
            }
          });
      }
    };

    $scope.addRow = function() {
      $scope.selectedLayout.blocks.push([[], []]);
    };

    $scope.addCol = function(row) {
      row.push([]);
    };

    $scope.removeCol = function(row, col) {
      row.splice(col, 1);
      // When removing the last column in a row, delete the row
      _.each($scope.selectedLayout.blocks, function(row, num) {
        if (row && !row.length) {
          $scope.selectedLayout.blocks.splice(num, 1);
        }
      });
      // Place blocks from deleted col back in the palette
      loadLayout($scope.selectedLayout);
    };

    function getBlocksInLayout(layout) {
      var blocksInLayout = [];
      _.each(layout.blocks, function(row) {
        _.each(row, function(col) {
          blocksInLayout.push.apply(blocksInLayout, col);
        });
      });
      return blocksInLayout;
    }

    $scope.deleteBlock = function(block) {
      var message = [_.escape(ts('Delete the block "%1"?', {1: block.title}))];
      _.each($scope.layouts, function (layout) {
        if (_.where(getBlocksInLayout(layout), {name: block.name}).length) {
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
            _.each(layout.blocks, function(row) {
              _.each(row, function(col) {
                var idx = _.findIndex(col, {name: block.name});
                if (idx > -1) {
                  col.splice(idx, 1);
                }
              });
            });
          });
          reloadBlocks([['UFGroup', 'delete', {where: [['id', '=', block.profile_id]]}]]);
        });
    };

    // Cycles between the 4 possible collapsible/collapsed states
    $scope.toggleCollapsible = function(block) {
      if (!block.collapsible && !block.showTitle) {
        block.collapsible = true;
        block.collapsed = true;
      } else if (!block.collapsible && block.showTitle) {
        block.showTitle = false;
      } else if (block.collapsed) {
        block.collapsed = false;
      } else {
        block.collapsible = false;
        block.collapsed = false;
        block.showTitle = true;
      }
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
        blocks: [[[],[]]]
      };
      $scope.deletedLayout = null;
      loadLayout(newLayout);
      $scope.layouts.unshift(newLayout);
      $scope.selectLayout(newLayout);
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
      $scope.selectLayout($scope.deletedLayout);
      $scope.deletedLayout = null;
    };

    $scope.toggleTabActive = function(tab) {
      tab.is_active = !tab.is_active;
      if (!tab.is_active) {
        tab.title = $scope.tabs[tab.id].title;
      }
    };

    $scope.pickTabIcon = function(tab) {
      editingTabIcon = tab;
      $('#cse-icon-picker ~ .crm-icon-picker-button').click();
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
        var empty = true, tabs = [];
        var item = {
          label: layout.label,
          weight: ++layoutWeight,
          id: layout.id,
          contact_type: layout.contact_type || null,
          contact_sub_type: layout.contact_sub_type && layout.contact_sub_type.length ? layout.contact_sub_type : null,
          groups: layout.groups && layout.groups.length ? layout.groups : null,
          blocks: [],
          tabs: []
        };
        _.each(layout.blocks, function(row, rowNum) {
          item.blocks.push([]);
          _.each(row, function(col, colNum) {
            item.blocks[rowNum].push([]);
            _.each(col, function(block) {
              item.blocks[rowNum][colNum].push(getBlockProperties(block));
              empty = false;
            });
          });
        });
        _.each(layout.tabs, function(tab, pos) {
          var tabInfo = {id: tab.id, is_active: tab.is_active};
          if (tab.title !== $scope.tabs[tab.id].title) {
            tabInfo.title = tab.title;
          }
          if (tab.icon !== $scope.tabs[tab.id].icon) {
            tabInfo.icon = tab.icon;
          }
          item.tabs[pos] = tabInfo;
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

    // Return the editable properties of a block
    function getBlockProperties(block) {
      return _.pick(block, 'name', 'title', 'collapsible', 'collapsed', 'showTitle');
    }

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
        .then(function () {
          $scope.$apply(function () {
            $scope.saving = false;
            $scope.changesSaved = true;
          });
        });
    }

    function loadBlocks(blockData) {
      allBlocks = [];
      _.each(blockData, function(group) {
        _.each(group.blocks, function(block) {
          block.group = group.name;
          block.groupTitle = group.title;
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
      // Filter out tabs that no longer exist
      layout.tabs = _.filter(layout.tabs || _.cloneDeep(data.tabs), function(item) {
        return $scope.tabs[item.id];
      });
      // Set defaults for tabs
      _.each(data.tabs, function(defaultTab) {
        var layoutTab = _.where(layout.tabs, {id: defaultTab.id})[0];
        if (!layoutTab) {
          layout.tabs.push(defaultTab);
        } else {
          layoutTab.title = layoutTab.title || defaultTab.title;
          layoutTab.icon = layoutTab.icon || defaultTab.icon;
        }
      });
      _.each(layout.blocks, function(row) {
        _.each(row, function(col) {
          _.each(col, function(block, num) {
            col[num] = _.extend(_.where(layout.palette, {name: block.name})[0] || {}, getBlockProperties(block));
            _.remove(layout.palette, {name: block.name});
          });
        });
      });
    }

    // Reload all block data and refresh layouts
    // Optionally call the api first (e.g. to save a profile)
    function reloadBlocks(apiCalls) {
      apiCalls = apiCalls || [];
      apiCalls.push(['ContactLayout', 'getBlocks']);
      $scope.deletedLayout = null;
      CRM.api4(apiCalls)
        .then(function(data) {
          $scope.$apply(function() {
            allBlocks = loadBlocks(_.last(data));
            loadLayouts();
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

    // Set changesSaved to true on initial load, false thereafter whenever changes are made to the model
    $scope.$watch('layouts', function () {
      $scope.changesSaved = $scope.changesSaved === 1;
    }, true);

    // Initialize
    if ($scope.layouts.length) {
      loadLayouts();
      $scope.selectLayout($scope.layouts[0]);
    }
    else {
      $scope.newLayout();
    }

    CRM.loadScript(CRM.config.resourceBase + 'js/jquery/jquery.crmIconPicker.js').done(function() {
      $('#cse-icon-picker').crmIconPicker().change(function() {
        if (editingTabIcon) {
          $scope.$apply(function() {
            editingTabIcon.icon = 'crm-i ' + $('#cse-icon-picker').val();
          });
        }
      });
    });

  });

  // Editable titles using ngModel & html5 contenteditable
  angular.module('contactlayout').directive("contactLayoutEditable", function() {
    return {
      restrict: "A",
      require: "ngModel",
      link: function(scope, element, attrs, ngModel) {
        var ts = CRM.ts('contactlayout');

        function read() {
          var htmlVal = element.html();
          if (!htmlVal) {
            htmlVal = ts('Untitled');
            element.html(htmlVal);
          }
          ngModel.$setViewValue(htmlVal);
        }

        ngModel.$render = function() {
          element.html(ngModel.$viewValue || ' ');
        };

        // Special handling for enter and escape keys
        element.on('keydown', function(e) {
          // Enter: prevent line break and save
          if (e.which === 13) {
            e.preventDefault();
            element.blur();
          }
          // Escape: undo
          if (e.which === 27) {
            element.html(ngModel.$viewValue || ' ');
            element.blur();
          }
        });

        element.on("blur change", function() {
          scope.$apply(read);
        });

        element.attr('contenteditable', 'true').addClass('crm-editable-enabled');
      }
    };
  });

})(angular, CRM.$, CRM._);

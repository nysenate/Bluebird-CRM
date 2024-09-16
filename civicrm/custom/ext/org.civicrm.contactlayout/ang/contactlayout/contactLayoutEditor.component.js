(function(angular, $, _) {

  angular.module('contactlayout').component('contactLayoutEditor', {
    templateUrl: '~/contactlayout/contactLayoutEditor.html',
    controller: function($scope, $timeout, $q, contactLayoutRelationshipOptions,
             crmApi4, crmStatus, dialogService) {
      var ts = $scope.ts = CRM.ts('contactlayout'),
        ctrl = this,
        vars = CRM.vars.contactlayout,
        profilesReady = $q.defer(),
        allTabs = _.indexBy(vars.tabs, 'id');
      $scope.selectedLayout = null;
      $scope.changesSaved = 1;
      $scope.saving = false;
      $scope.contactTypes = vars.contactTypes;
      this.data = {
        layouts: vars.layouts,
        tabs: vars.defaultTabs
      };
      $scope.systemTabs = vars.tabs;
      $scope.systemBlocks = [];
      $scope.systemLayout = [];
      var newLayoutCount = 0,
        profileEntities = [{entity_name: "contact_1", entity_type: "IndividualModel"}],
        allBlocks = [];
      var CONTACT_ICONS = {
        Individual: 'fa fa-user',
        Organization: 'fa fa-building',
        Household: 'fa fa-home'
      };

      // Determines if the given block can be used for the current layout's contact type
      $scope.checkBlockValidity = function(block) {
        if (!$scope.selectedLayout.contact_type) {
          return true;
        } else if (!block.related_rel) {
          return !block.contact_type || (block.contact_type.includes($scope.selectedLayout.contact_type));
        } else {
          var relationship = contactLayoutRelationshipOptions.getRelationshipFromOption(block.related_rel);

          if (relationship.direction === 'r') {
            return (block.contact_type.includes(relationship.type.contact_type_a) &&
              relationship.type.contact_type_b === $scope.selectedLayout.contact_type) ||
              (block.contact_type.includes(relationship.type.contact_type_b) &&
                relationship.type.contact_type_a === $scope.selectedLayout.contact_type);
          } else {
            var contactTypes = relationship.direction === 'ab' ?
              {onBlock: relationship.type.contact_type_a, viewing: relationship.type.contact_type_b} :
              {onBlock: relationship.type.contact_type_b, viewing: relationship.type.contact_type_a};

            return $scope.selectedLayout.contact_type === contactTypes.viewing ||
              block.contact_type.includes(contactTypes.onBlock);
          }
        }
      };

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
          return getLabels(layout.contact_sub_type, vars.contactTypes).join(', ');
        }
        if (layout.contact_type) {
          return getLabels(layout.contact_type, vars.contactTypes);
        }
        return ts('All contact types');
      };

      $scope.contactTypeLabel = function(contactType) {
        return getLabels(contactType, vars.contactTypes);
      };

      $scope.changeContactType = function(layout) {
        layout.contact_sub_type = null;
        if (layout.contact_type) {
          _.each(layout.blocks, function(row) {
            _.each(row, function(col, i) {
              row[i] = _.filter(col, function(block) {
                return !block.contact_type || block.contact_type.includes(layout.contact_type);
              });
            });
          });
          loadLayout(layout);
        }
      };

      $scope.showGroups = function(layout) {
        if (layout.groups && layout.groups.length) {
          return getLabels(layout.groups, vars.groups).join(', ');
        }
        return ts('All users');
      };

      $scope.selectableSubTypes = function(contactType) {
        typeId = _.where(vars.contactTypes, {name: contactType})[0].id;
        return _.where(vars.contactTypes, {parent_id: typeId});
      };

      $scope.removeBlock = function(index, blocks) {
        blocks.splice(index, 1);
        loadLayout($scope.selectedLayout);
      };

      $scope.editBlock = function(block) {
        var edited;
        if (block.profile_id) {
          profilesReady.promise.then(function() {
            editProfile(block.profile_id);
          });
        }
        // Cannot use angular pages in a popup
        else if(_.includes(block.edit, '#')) {
          window.open(CRM.url(block.edit), '_blank');
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

      /**
       * Opens a modal that allows editing the relationship field for the given block.
       *
       * @param {object} block a contact layout block object.
       */
      $scope.editBlockRelationship = function(block) {
        var model = {
          ts: ts,
          relationshipLabel: '',
          selectedRelationship: block.related_rel,
          relationshipOptions: contactLayoutRelationshipOptions,
          contactIcons: {
            onBlock: CONTACT_ICONS.Individual,
            viewing: CONTACT_ICONS.Individual,
          },
          displayHelp: function(event) {
            event.preventDefault();
            CRM.help('Relationship selection', 'What is the relationship of the contact we want to display on this block?');
          },
          // Stores the relationship label and contact icons for the selected relationship option
          storeRelationshipInfoForSelectedOption: function() {
            if (!model.selectedRelationship) {
              return;
            }

            var relationship = contactLayoutRelationshipOptions.getRelationshipFromOption(model.selectedRelationship);
            var relationshipOption = _.find(model.relationshipOptions.options, {id: model.selectedRelationship});
            var contactIcons = getIconsForRelationship(relationship, block);

            model.relationshipLabel = relationshipOption.text;
            model.contactIcons.onBlock = CONTACT_ICONS[contactIcons.onBlock] || CONTACT_ICONS.Individual;
            model.contactIcons.viewing = CONTACT_ICONS[contactIcons.viewing] || CONTACT_ICONS.Individual;
          }
        };
        var dialogOptions = {
          width: '500px',
          title: ts('Relationship Selection'),
          buttons: [
            {
              text: ts('Save'),
              icons: {primary: 'fa-check'},
              click: function() {
                block.related_rel = model.selectedRelationship;

                dialogService.close('editBlockRelationshipDialog');
                $scope.$digest();
              }
            },
            {
              text: ts('Cancel'),
              icons: {primary: 'fa-times'},
              click: function() {
                dialogService.cancel('editBlockRelationshipDialog');
              }
            }
          ]
        };

        model.storeRelationshipInfoForSelectedOption();
        dialogService.open(
          'editBlockRelationshipDialog',
          '~/contactlayout/edit-block-relationship-dialog.html',
          model,
          dialogOptions
        );
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

      // Returns the set of icons for the given relationship type, direction, and block's contact type.
      function getIconsForRelationship(relationship, block) {
        if (relationship.direction === 'r') {
          return block.contact_type.includes(relationship.type.contact_type_a) ?
            {onBlock: relationship.type.contact_type_a, viewing: relationship.type.contact_type_b} :
            {onBlock: relationship.type.contact_type_b, viewing: relationship.type.contact_type_a};
        } else {
          return relationship.direction === 'ab' ?
            {onBlock: relationship.type.contact_type_a, viewing: relationship.type.contact_type_b} :
            {onBlock: relationship.type.contact_type_b, viewing: relationship.type.contact_type_a};
        }
      }

      $scope.deleteBlock = function(block) {
        var message = [_.escape(ts('Delete the block "%1"?', {1: block.title}))];
        _.each(ctrl.data.layouts, function(layout) {
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
            _.each(ctrl.data.layouts, function(layout) {
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
          $(ui.item.sortable.droptarget).is('#cse-palette')) {
          ui.item.sortable.cancel();
        }
      };

      $scope.newLayout = function() {
        var newLayout = {
          label: ts('Untitled %1', {1: ++newLayoutCount}),
          blocks: [[[], []]]
        };
        $scope.deletedLayout = null;
        loadLayout(newLayout);
        ctrl.data.layouts.unshift(newLayout);
        $scope.selectLayout(newLayout);
      };

      $scope.copyLayout = function(index) {
        var newLayout = angular.copy(ctrl.data.layouts[index]);
        delete newLayout.id;
        newLayout.label += ' (copy)';
        ctrl.data.layouts.splice(index, 0, newLayout);
        $scope.selectLayout(newLayout);
      };

      $scope.copyDefaultLayout = function() {
        var newLayout = {
          label: ts('Untitled %1', {1: ++newLayoutCount}),
          blocks: _.transform(allBlocks, function(layout, block) {
            if (block.system_default && $scope.isSystemBlockEnabled(block)) {
              layout[0][block.system_default[1]].push(block);
            }
          }, [[[], []]])
        };
        loadLayout(newLayout);
        ctrl.data.layouts.push(newLayout);
        $scope.selectLayout(newLayout);
      };

      $scope.deleteLayout = function(index) {
        $scope.deletedLayout = ctrl.data.layouts[index];
        if ($scope.selectedLayout === ctrl.data.layouts[index]) {
          $scope.selectedLayout = null;
        }
        ctrl.data.layouts.splice(index, 1);
      };

      $scope.restoreLayout = function() {
        ctrl.data.layouts.unshift($scope.deletedLayout);
        $scope.selectLayout($scope.deletedLayout);
        $scope.deletedLayout = null;
      };

      $scope.newProfile = function() {
        profilesReady.promise.then(newProfile);
      };

      function newProfile() {
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
      }

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
        _.each(ctrl.data.layouts, function(layout) {
          var empty = true, tabs = [];
          var item = {
            label: layout.label,
            weight: ++layoutWeight,
            id: layout.id,
            contact_type: layout.contact_type || null,
            contact_sub_type: layout.contact_sub_type && layout.contact_sub_type.length ? layout.contact_sub_type : null,
            groups: layout.groups && layout.groups.length ? layout.groups : null,
            blocks: [],
            tabs: layout.tabs ? [] : null
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
            if (tab.title !== allTabs[tab.id].title) {
              tabInfo.title = tab.title;
            }
            if (tab.icon !== allTabs[tab.id].icon) {
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
        return _.pick(block, 'name', 'title', 'collapsible', 'collapsed', 'showTitle', 'related_rel');
      }

      // Write layout data to the server
      function writeRecords(data) {
        $scope.saving = true;
        $scope.deletedLayout = null;
        var apiCalls = [];
        // Replace records (or delete all if there are none)
        if (data.length) {
          apiCalls.push(['ContactLayout', 'replace', {records: data}]);
        } else {
          apiCalls.push(['ContactLayout', 'delete', {where: [['id', 'IS NOT NULL']]}]);
        }
        // Update default tabs
        if (ctrl.data.tabs) {
          apiCalls.push(['Setting', 'set', {values: {'contactlayout_default_tabs': ctrl.data.tabs}}]);
        } else {
          apiCalls.push(['Setting', 'revert', {select: ['contactlayout_default_tabs']}]);
        }
        crmApi4(apiCalls)
          .then(function() {
            $scope.saving = false;
            $scope.changesSaved = true;
          });
      }

      function loadBlocks(blockData) {
        allBlocks.length = 0;
        $scope.systemBlocks.length = 0;
        $scope.systemLayout = [[[], []], [[], []], [[], []], [[], []], [[], []]];
        _.each(blockData, function(group) {
          _.each(group.blocks, function(block) {
            block.group = group.name;
            block.groupTitle = group.title;
            block.icon = group.icon;
            allBlocks.push(block);
            if (block.system_default) {
              $scope.systemBlocks.push(block);
              $scope.systemLayout[block.system_default[0]][block.system_default[1]].push(block);
            }
          });
        });
      }

      function loadLayouts() {
        _.each(ctrl.data.layouts, loadLayout);
      }

      function loadLayout(layout) {
        layout.palette = _.cloneDeep(allBlocks);
        if (layout.tabs) {
          // Filter out tabs that no longer exist
          layout.tabs = _.filter(layout.tabs, function(item) {
            return allTabs[item.id];
          });
          // Set defaults for tabs
          _.each(vars.tabs, function(defaultTab) {
            var layoutTab = _.where(layout.tabs, {id: defaultTab.id})[0];
            if (!layoutTab) {
              layout.tabs.push(defaultTab);
            } else {
              layoutTab.title = layoutTab.title || defaultTab.title;
              layoutTab.icon = layoutTab.icon || defaultTab.icon;
            }
          });
        }
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
              loadBlocks(_.last(data));
              loadLayouts();
            });
          });
      }

      $scope.isSystemBlockTogglable = function(block) {
        var name = block.name.replace('core.', '');
        return !!vars.contactEditOptions[name];
      };

      $scope.isSystemBlockEnabled = function(block) {
        var name = block.name.indexOf('custom.') === 0 ? 'CustomData' : block.name.replace('core.', '');
        return !vars.contactEditOptions[name] || vars.systemDefaultsEnabled[name];
      };

      $scope.toggleSystemBlock = function(block) {
        var name = block.name.replace('core.', '');
        if (vars.systemDefaultsEnabled[name]) {
          delete vars.systemDefaultsEnabled[name];
        } else {
          vars.systemDefaultsEnabled[name] = vars.contactEditOptions[name];
        }
        crmStatus({}, crmApi4('Setting', 'set', {
          values: {contact_edit_options: vars.systemDefaultsEnabled}
        }));
      };

      // Initialize
      this.$onInit = function() {
        loadBlocks(vars.blocks);
        loadLayouts();

        // Load schema for backbone-based profile editor
        CRM.civiSchema = {
          IndividualModel: null,
          OrganizationModel: null,
          HouseholdModel: null
        };
        CRM.Schema.reloadModels().then(function() {
          profilesReady.resolve();
        });
      };

      // Set changesSaved to true on initial load, false thereafter whenever changes are made to the model
      $scope.$watch('$ctrl.data', function() {
        $scope.changesSaved = $scope.changesSaved === 1;
      }, true);

    }
  });

})(angular, CRM.$, CRM._);

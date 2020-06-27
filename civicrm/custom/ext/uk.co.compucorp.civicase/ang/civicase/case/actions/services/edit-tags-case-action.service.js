(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('EditTagsCaseAction', EditTagsCaseAction);

  /**
   *
   * @param {object} dialogService dialog service
   * @param {object} Tag tag
   * @param {object} Tagset tag set
   */
  function EditTagsCaseAction (dialogService, Tag, Tagset) {
    /**
     * Click event handler for the Action
     *
     * @param {Array} cases cases
     * @param {object} action action
     * @param {Function} callbackFn callback function
     */
    this.doAction = function (cases, action, callbackFn) {
      var ts = CRM.ts('civicase');
      var item = cases[0];
      var keys = ['tags'];
      var model = {
        tags: []
      };

      _.each(Tagset.getAll(), function (tagset) {
        model[tagset.id] = [];
        keys.push(tagset.id);
      });

      // Sort case tags into sets
      _.each(item.tag_id, function (tag, id) {
        if (!tag['tag_id.parent_id'] || !model[tag['tag_id.parent_id']]) {
          model.tags.push(id);
        } else {
          model[tag['tag_id.parent_id']].push(id);
        }
      });

      model.tagsets = Tagset.getAll();
      model.colorTags = Tag.getAll();
      model.ts = ts;

      dialogService.open('EditTags', '~/civicase/case/actions/directives/edit-tags.html', model, {
        autoOpen: false,
        height: 'auto',
        width: '40%',
        title: action.title,
        buttons: [{
          text: ts('Save'),
          icons: { primary: 'fa-check' },
          click: editTagModalClickEvent
        }]
      });

      /**
       * Handles the click event for the Edit Tag Modal's Click Event
       */
      function editTagModalClickEvent () {
        var calls = [];
        var values = [];

        /**
         * @param tagIds
         */
        function tagParams (tagIds) {
          var params = { entity_id: item.id, entity_table: 'civicrm_case' };

          _.each(tagIds, function (id, i) {
            params['tag_id_' + i] = id;
          });

          return params;
        }

        _.each(keys, function (key) {
          _.each(model[key], function (id) {
            values.push(id);
          });
        });

        var toRemove = _.difference(_.keys(item.tag_id), values);
        var toAdd = _.difference(values, _.keys(item.tag_id));

        if (toRemove.length) {
          calls.push(['EntityTag', 'delete', tagParams(toRemove)]);
        }

        if (toAdd.length) {
          calls.push(['EntityTag', 'create', tagParams(toAdd)]);
        }

        if (calls.length) {
          calls.push(['Activity', 'create', {
            case_id: item.id,
            status_id: 'Completed',
            activity_type_id: 'Change Case Tags'
          }]);
          callbackFn(calls);
        }

        $(this).dialog('close');
      }
    };
  }
})(angular, CRM.$, CRM._);

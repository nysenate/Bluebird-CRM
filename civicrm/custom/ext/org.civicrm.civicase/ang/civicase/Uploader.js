(function(angular, $, _) {

  function caseFilesController($scope, crmApi, crmBlocker, crmStatus, FileUploader, $q, $timeout) {
    var ts = $scope.ts = CRM.ts('civicase'),
      ctx = $scope.ctx,
      block = $scope.block = crmBlocker();

    function initActivity() {
      $scope.activity = {
        case_id: ctx.id,
        activity_type_id: 'File Upload',
        subject: ''
      };
    }
    initActivity();
    $scope.$watchCollection('ctx.id', initActivity);

    $scope.isUploadActive = function() {
      return ($scope.uploader.queue.length > 0);
    };

    $scope.uploader = new FileUploader({
      url: CRM.url('civicrm/ajax/attachment'),
      onAfterAddingFile: function onAfterAddingFile(item) {
        item.crmData = {description: ''};
      },
      onSuccessItem: function onSuccessItem(item, response, status, headers) {
        var ok = status == 200 && _.isObject(response) && response.file && (response.file.is_error === 0);
        if (!ok) {
          this.onErrorItem(item, response, status, headers);
        }
      },
      onErrorItem: function onErrorItem(item, response, status, headers) {
        var msg = (response && response.file && response.file.error_message) ? response.file.error_message : ts('Unknown error');
        CRM.alert(item.file.name + ' - ' + msg, ts('Attachment failed'), 'error');
      },
      /** Like uploadAll(), but it returns a promise. */
      uploadAllWithPromise: function() {
        var dfr = $q.defer(), self = this;
        self.onCompleteAll = function() {
          dfr.resolve();
          self.onCompleteAll = null;
        };
        self.uploadAll();
        return dfr.promise;
      }
    });

    $scope.deleteActivity = function deleteActivity() {
      $scope.uploader.clearQueue();
      $scope.activity = null;
    };

    $scope.saveActivity = function saveActivity() {
      var promise = crmApi('Activity', 'create', $scope.activity)
        .then(function (r) {
          var target = {entity_table: 'civicrm_activity', entity_id: r.id};
          _.each($scope.uploader.getNotUploadedItems(), function (item) {
            item.formData = [_.extend({crm_attachment_token: CRM.crmAttachment.token}, target, item.crmData)];
          });
          return $scope.uploader.uploadAllWithPromise();
        }).then(function(){
          return pwait(1000); // Let the user absorb what happened.
        }).then(function(){
          $scope.uploader.clearQueue();
          initActivity();
          if ($scope.onUpload) {
            $scope.$parent.$eval($scope.onUpload);
          }
        });
      return block(crmStatus({start: ts('Uploading...'), success: ts('Uploaded')}, promise));
    };

    // TODO: Test interrupted transfer.

    function pwait(delay) {
      var dfr = $q.defer();
      $timeout(function(){ dfr.resolve(); }, delay);
      return dfr.promise;
    }
  }

  angular.module('civicase').directive('civicaseUploader', function() {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/Uploader.html',
      controller: caseFilesController,
      scope: {
        ctx: '=civicaseUploader',
        onUpload: '@'
      }
    };
  });

})(angular, CRM.$, CRM._);

(function(angular, $, _) {

  function FileListCtrl($scope, crmApi, crmBlocker, crmStatus) {
    var ts = $scope.ts = CRM.ts('civicase'),
      block = $scope.block = crmBlocker();
    $scope.CRM = CRM;

    $scope.$watchCollection('apiCtrl.result', function(r){
      // prettier html
      $scope.values = r.values;
      $scope.xref = r.xref;
      // Pre-sorting: (a) cast to array and (b) ensure stable check of isSameDate()
      $scope.activities = r.xref ? _.sortBy(r.xref.activity, 'activity_date_time').reverse() : [];

      $scope.filesByAct = {};
      _.each(r.values, function(match){
        if (!$scope.filesByAct[match.activity_id]) {
          $scope.filesByAct[match.activity_id] = [];
        }
        $scope.filesByAct[match.activity_id].push(r.xref.file[match.id]);
      });

      $scope.delete = function(activity, file) {
        var p = crmApi('Attachment', 'delete', {id: file.id})
          .then(function(){
            $scope.apiCtrl.refresh();
          });
        return block(crmStatus({start: ts('Deleting...'), success: ts('Deleted')}, p));
      }
    });
  }

  angular.module('civicase').directive('civicaseFileList', function() {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/FileList.html',
      controller: FileListCtrl,
      scope: {
        apiCtrl: '=civicaseFileList'
      }
    };
  });

})(angular, CRM.$, CRM._);

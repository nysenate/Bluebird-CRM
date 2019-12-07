(function(angular, $, _) {

  function filesize(size) {
    size = parseInt(size); // paranoid
    if (size === 0) return '0 B';
    // Courtesy of Andrew V, https://stackoverflow.com/questions/10420352/converting-file-size-in-bytes-to-human-readable
    var i = Math.floor(Math.log(size) / Math.log(1024));
    return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'KB', 'MB', 'GB', 'TB'][i];
  }

  // "civicaseFilesize" displays file size using KB, MB, etc
  // Example usage: <div civicase-filesize="numberOfBytes"></div>
  angular.module('civicase').directive('civicaseFilesize', function() {
    return {
      restrict: 'AE',
      scope: {
        civicaseFilesize: '='
      },
      link: function($scope, $el, $attr) {
        $scope.$watch('civicaseFilesize', function(newValue) {
          $el.text(filesize(newValue));
        });
      }
    };
  });
})(angular, CRM.$, CRM._);
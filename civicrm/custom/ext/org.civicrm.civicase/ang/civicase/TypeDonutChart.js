(function(angular, $, _) {
  angular.module('civicase').directive('civicaseTypeDonutChart', function(doNutty, crmApi, civicaseInteger) {
    return {
      restrict: 'AE',
      scope: {
        civicaseTypeDonutChart: '='
      },
      link: function($scope, $el, $attr) {
        var dc = CRM.visual.dc, d3 = CRM.visual.d3, crossfilter = CRM.visual.crossfilter;
        var ts = $scope.ts = CRM.ts('civicase');

        function fullDraw(data) {
          $el.find('svg').remove();

          var ndx = crossfilter(data);
          var caseTypeDimension = ndx.dimension(function(d) { return d['case_type_id.title']; });
          var caseTypeCountGroup = caseTypeDimension.group().reduceSum(function(d){return d.count;});
          var caseTypeSum = ndx.groupAll().reduceSum(function(d){return d.count;});

          var chart = dc.pieChart($el[0]);
          chart
              .slicesCap(5)
              .dimension(caseTypeDimension)
              .group(caseTypeCountGroup)
              .colors(d3.scale.category20())
              .label(function(d) {return "";});

          doNutty(chart, 250, function(){
            var v = caseTypeSum.value();
            return {
              number: civicaseInteger(v),
              text: (v === 1) ? ts('case') : ts('cases')
            };
          });

          chart.render();
          chart.svg().classed('civicase-type-donut-chart', true);
        }

        $scope.$watchCollection('civicaseTypeDonutChart', function(params){
          crmApi('Case', 'gettypestats', params).then(function(response){
            var data = response.values;
            data.forEach(function(x) {
              x.count = +x.count;
            });
            fullDraw(data);
          });
        });
      }
    };
  });
})(angular, CRM.$, CRM._);

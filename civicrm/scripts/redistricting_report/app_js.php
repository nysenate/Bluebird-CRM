var chart;
$(document).ready(function() {
	<?php if ($mode == 'summary'): ?>
		loadSummaryViz();
	<?php elseif ($mode == 'detail'): ?>
		loadDetailViz();
	<?php endif; ?>
});

<?php if ($mode == 'summary'): ?>
function loadSummaryViz(){

    $('table.summary').dataTable({
		"bPaginate": false,
		"bFilter": false,
		"bInfo": false
	});

    chart = new Highcharts.Chart({
        chart: {
            renderTo: 'summary_chart',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        credits : {
				enabled : false
		},
        title: {
            text: 'Distribution of contacts among outside districts'
        },
        tooltip: {
    	    pointFormat: '{series.name}: <b>{point.percentage}%</b>',
        	percentageDecimals: 1
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function() {
                        return '<b>'+ this.point.name +'</b>: '+ (Math.round(this.percentage * 10) / 10) +' %';
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Out of District Share',
            data: <?= redist_summary_pie_data($district_counts) ?>
        }]
    });
}

<?php elseif ($mode == 'detail'): ?>
function loadDetailViz(){

    var all_tables =
        $('table').dataTable({
		  "bPaginate": true,
		  "bFilter": true,
		  "bInfo": true
	    });

    $('.district-view').show();
    $('#detail_load_text').fadeOut();
}

<?php endif; ?>

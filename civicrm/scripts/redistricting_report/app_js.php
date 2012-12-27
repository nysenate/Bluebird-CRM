// DataTables
var summary_oTable;
var detail_oTable;

$(document).ready(function() {
	<?php if ($mode == 'summary'): ?>
		init_summary_page();
	<?php elseif ($mode == 'detail'): ?>
		init_detail_page();
	<?php endif; ?>
});

<?php if ($mode == 'summary'): ?>
function init_summary_page(){

    summary_oTable =
    $('table.summary').dataTable({
		"bPaginate": false,
		"bFilter": false,
		"bInfo": false
	});

    var chart = new Highcharts.Chart({
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

<?php elseif($mode == 'detail'): ?>
function init_detail_page(){

    detail_oTable = load_detail_datatable();

    $('.district-view').show();
    $('#detail_load_text').fadeOut();
}

function load_detail_datatable(){

    return $('table').dataTable({
          "bPaginate": true,
          "bFilter": true,
          "bInfo": true
    });
}
<?php endif; ?>
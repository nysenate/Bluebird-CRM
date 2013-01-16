// DataTables
var summary_oTable;
var detail_oTable;
var detail_paginate;

$(document).ready(function() {
	<?php if ($mode == 'summary'): ?>
		init_summary_page();
	<?php elseif ($mode == 'detail'): ?>
		init_detail_page();

        $("#toggle_pagination").click(function(e){
            e.preventDefault();
            toggle_pagination();

        });

	<?php endif; ?>
});

<?php if ($mode == 'summary'): ?>
function init_summary_page(){

    summary_oTable =
    $('table.summary').dataTable({
		"bPaginate": false,
		"bFilter": false,
		"bInfo": false,
        "aaSorting": [[ 5, "desc" ]] 
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

    detail_oTable = enable_pagination();
    $('.district-view').show();
    $('#detail_load_text').fadeOut();
}

function enable_pagination(){

    detail_paginate = true;
    return $('table').dataTable({
          "bPaginate": true,
          "bFilter": true,
          "bInfo": true,
          "bDestroy": true,
          "bProcessing": true
    }).css('width', '100%');
}

function disable_pagination(){

    detail_paginate = false;
    return $('table').dataTable({
          "bPaginate": false,
          "bInfo": true,
          "bDestroy": true
    }).css('width', '100%');
}

function toggle_pagination(){
    if (detail_paginate){
        disable_pagination();
    }
    else{
        // Just reload the page, it's faster
        location.reload();
    }
}

<?php endif; ?>
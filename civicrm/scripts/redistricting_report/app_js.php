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

    summary_contacts_table =
        $('table.summary.contacts').dataTable({
    		"bPaginate": false,
    		"bFilter": false,
    		"bInfo": false,
            "aaSorting": [[ 5, "desc" ]] 
    	});

    summary_emails_table =
        $('table.summary.emails').dataTable({
            "bPaginate": false,
            "bFilter": false,
            "bInfo": false,
            "aaSorting": [[ 2, "desc" ]] 
        });
    summary_cases_table =
        $('table.summary.cases').dataTable({
            "bPaginate": false,
            "bFilter": false,
            "bInfo": false,
            "aaSorting": [[ 6, "desc" ]] 
        });

    summary_activities_table =
        $('table.summary.activities').dataTable({
            "bPaginate": false,
            "bFilter": false,
            "bInfo": false,
            "aaSorting": [[ 3, "desc" ]] 
        });

    var common_chart_opts = {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        credits : {
                enabled : false
        },
        title: {
            text: ''
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
        }        
    }

    var contacts_chart_ops = common_chart_opts;
    contacts_chart_ops.chart.renderTo = "summary_contacts_chart";
    contacts_chart_ops.title = { text: 'Distribution of contacts among outside districts'}
    contacts_chart_ops.series = [{
            type: 'pie',
            name: 'Out of District Share',
            data: <?= redist_summary_pie_data($district_counts) ?>
    }];

    var contacts_summary_chart = new Highcharts.Chart(contacts_chart_ops);

    var emails_chart_ops = common_chart_opts;
    emails_chart_ops.chart.renderTo = "summary_emails_chart";
    emails_chart_ops.title = { text: 'Distribution of active email addresses among outside districts'}
    emails_chart_ops.series = [{
            type: 'pie',
            name: 'Out of District Share',
            data: <?= redist_summary_pie_data($district_counts, 'active_emails', 5) ?>
    }];
    
    var emails_summary_chart = new Highcharts.Chart(emails_chart_ops);

    var cases_chart_ops = common_chart_opts;
    cases_chart_ops.chart.renderTo = "summary_cases_chart";
    cases_chart_ops.title = { text: 'Distribution of cases among outside districts'}
    cases_chart_ops.series = [{
            type: 'pie',
            name: 'Out of District Share',
            data: <?= redist_summary_pie_data($district_counts, 'all_cases', 5) ?>
    }];
    
    var cases_summary_chart = new Highcharts.Chart(cases_chart_ops);

    var activities_chart_ops = common_chart_opts;
    activities_chart_ops.chart.renderTo = "summary_activities_chart";
    activities_chart_ops.title = { text: 'Distribution of activities among outside districts'}
    activities_chart_ops.series = [{
            type: 'pie',
            name: 'Out of District Share',
            data: <?= redist_summary_pie_data($district_counts, 'activities', 5) ?>
    }];
    
    var activities_summary_chart = new Highcharts.Chart(activities_chart_ops);

    // Initialize the tabs
    $("#tabs").tabs();

    $(".ui-tabs .ui-tabs-nav li a").on("click", function(){
        $(this).blur();
    });

    // Show the corresponding pie chart based on the selector prefix
    $("#tabs").on( "tabsactivate", function( event, ui ) {
        if (ui.newPanel.selector != null && typeof ui.newPanel.selector != "undefined" ){
            table_id = ui.newPanel.selector;
            chart_id = "#summary_" + table_id.substring(1, table_id.length - 6) + "_chart";
            $(".pie-chart").removeClass("active").addClass("inactive");
            $(chart_id).removeClass("inactive").addClass("active");
        } 
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
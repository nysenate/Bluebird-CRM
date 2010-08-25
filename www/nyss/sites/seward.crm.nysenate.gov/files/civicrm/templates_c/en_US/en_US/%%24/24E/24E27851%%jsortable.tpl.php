<?php /* Smarty version 2.6.26, created on 2010-08-16 22:19:20
         compiled from CRM/common/jsortable.tpl */ ?>
<?php echo '
<script type="text/javascript">
cj( function( ) {
var useAjax = '; ?>
<?php if ($this->_tpl_vars['useAjax']): ?>1<?php else: ?>0<?php endif; ?><?php echo ';

var sourceUrl = \'\';
var useClass  = \'display\';

var tcount =1;
if ( useAjax ) {
 sourceUrl = '; ?>
"<?php echo $this->_tpl_vars['sourceUrl']; ?>
"<?php echo ';
 useClass = \'pagerDisplay\';
 tcount =5;
} 
 
var tableId = \'\';
var count   = 1;

//rename id of table with sequence
//and create the object for navigation
cj(\'table.\' + useClass).each(function(){
    cj(this).attr(\'id\',\'option\' + tcount + count);
    tableId += count + \',\';
    count++; 
});

//remove last comma
tableId = tableId.substring(0, tableId.length - 1 );
eval(\'tableId =[\' + tableId + \']\');
 
  cj.each(tableId, function(i,n){
    tabId = \'#option\' + tcount + n; 
    //get the object of first tr data row.
    tdObject = cj(tabId + \' tr:nth(1) td\');
    var id = -1; var count = 0; var columns=\'\'; var sortColumn = \'\';
    //build columns array for sorting or not sorting
    cj(tabId + \' th\').each( function( ) {
        var option = cj(this).attr(\'id\').split("_");
        option  = ( option.length > 1 ) ? option[1] : option[0];
        stype   = \'numeric\';
        switch( option ) { 
            case \'sortable\':
                sortColumn += \'[\' + count + \', "asc" ],\'; 
                columns += \'{"sClass": "\'+ getElementClass( this ) +\'"},\';
            break;
            case \'date\':
                stype = \'date\';
            case \'order\':
                if ( cj(this).attr(\'class\') == \'sortable\' ){
                    sortColumn += \'[\' + count + \', "asc" ],\';
                }
                sortId   = getRowId(tdObject, cj(this).attr(\'id\') +\' hiddenElement\' ); 
                columns += \'{ "sType": \\\'\' + stype + \'\\\', "fnRender": function (oObj) { return oObj.aData[\' + sortId + \']; },"bUseRendered": false},\';
            break;
            case \'nosort\':           
                columns += \'{ "bSortable": false, "sClass": "\'+ getElementClass( this ) +\'"},\';
            break;
            case \'currency\':
                columns += \'{ "sType": "currency" },\';
            break;
            case \'link\':
                columns += \'{"sType": "html"},\';	    
            break;   
            default:
                if ( cj(this).text() ) {
                    columns += \'{"sClass": "\'+ getElementClass( this ) +\'"},\';
                } else {
                    columns += \'{ "bSortable": false },\';
                }
            break;
        }
        count++; 
	});
	columns    = columns.substring(0, columns.length - 1 );
	sortColumn = sortColumn.substring(0, sortColumn.length - 1 );
	eval(\'sortColumn =[\' + sortColumn + \']\');
	eval(\'columns =[\' + columns + \']\');
    	
        var currTable = cj(tabId);
        if (currTable) {
            // contains the dataTables master records
            var s = cj(document).dataTableSettings;
            if (s != \'undefined\') {
                var len = s.length;
                for (var i=0; i < len; i++) {  
                    // if already exists, remove from the array
                    if (s[i].sInstance = tabId) {
                        s.splice(i,1);
	            }
    	        }
  	    }
	}
	
    var oTable = null;
    if ( useAjax ) {
      oTable = cj(tabId).dataTable({
    	        "bFilter"    : false,
		"bAutoWidth" : false,
                "aaSorting"  : sortColumn,
		"aoColumns"  : columns,
	    	"bProcessing": true,
		"sPaginationType": "full_numbers",
	   	"bServerSide": true,
	   	"sAjaxSource": sourceUrl,

		'; ?>
<?php if ($this->_tpl_vars['callBack']): ?><?php echo '
		"fnDrawCallback": function() { checkSelected(); },
		'; ?>
<?php endif; ?><?php echo '

		"fnServerData": function ( sSource, aoData, fnCallback ) {
			$.ajax( {
				"dataType": \'json\', 
				"type": "POST", 
				"url": sSource, 
				"data": aoData, 
				"success": fnCallback
			} ); }
     		}); 
    } else {
      oTable = cj(tabId).dataTable({
			"aaSorting"    : sortColumn,
             	        "bPaginate"    : false,
                	"bLengthChange": true,
                	"bFilter"      : false,
                	"bInfo"        : false,
                	"bAutoWidth"   : false,
               		"aoColumns"   : columns
    			 }); 
    }
    var object;

    if ( !useAjax ) { 
    cj(\'a.action-item\').click( function(){
        object = cj(this);
        cj(\'table.display\').one( \'mouseover\', function() {
            var nNodes     = oTable.fnGetNodes( );
            var tdSelected = cj(object).closest(\'td\');
            var closestEle = cj(object).closest(\'tr\').attr(\'id\');
            cj.each( nNodes, function(i,n) {
                //operation on selected row element.
                if ( closestEle == n.id ){
                    var col = 0; 
                    cj(\'tr#\' + closestEle + \' td:not(.hiddenElement)\').each( function() {
                        if ( tdSelected.get(0) !== cj(this).get(0)  ){ 
                            oTable.fnUpdate( cj(this).html() , i, col );
                        }
                        col++;
                    });
                }
            });
        });
    });
    }
    
    });       
});

function getElementClass( element ) {
if( cj(element).attr(\'class\') )	 return cj(element).attr(\'class\');
return \'\';
}

//function to fetch the occurence of element
function getRowId(row,str){
 cj.each( row, function(i, n) {
    if( str === cj(n).attr(\'class\') ) {
        optionId = i;
    }
 });
return optionId;
}

//plugin to sort on currency
var symbol = "'; ?>
<?php echo $this->_tpl_vars['config']->defaultCurrencySymbol($this->_tpl_vars['config']->defaultSymbol); ?>
<?php echo '";
cj.fn.dataTableExt.oSort[\'currency-asc\']  = function(a,b) {
	var x = (a == "-") ? 0 : a.replace( symbol, "" );
	var y = (b == "-") ? 0 : b.replace( symbol, "" );
	x = parseFloat( x );
	y = parseFloat( y );
	return ((x < y) ? -1 : ((x > y) ?  1 : 0));
};

cj.fn.dataTableExt.oSort[\'currency-desc\'] = function(a,b) {
	var x = (a == "-") ? 0 : a.replace( symbol, "" );
	var y = (b == "-") ? 0 : b.replace( symbol, "" );
	x = parseFloat( x );
	y = parseFloat( y );
	return ((x < y) ?  1 : ((x > y) ? -1 : 0));
};
</script>
'; ?>

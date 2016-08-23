cj(document).ready(function(){
  if (cj.fn.dataTable.isDataTable('.crm-group-selector')) {
    var tblGroups = cj('.crm-group-selector').dataTable();
    tblGroups.fnSetColumnVis(5, false);
  }
  else {
    /*var tblGroups = cj('.crm-group-selector').dataTable({
      "retrieve": true
    });
    tblGroups.fnSetColumnVis(5, false);*/
  }
});

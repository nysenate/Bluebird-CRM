CRM.$(function($) {
  function setJobID() {
    $("#dialogJobID").show().dialog({
      modal: true,
      title: 'Set Job ID',
      bgiframe: true,
      width: 400,
      overlay: {
        opacity: 0.5,
        background: "black"
      },
      beforeclose: function(event, ui) {
        $(this).dialog("destroy");
      },
      buttons: {
        "Cancel": function() {
          $(this).dialog("close");
        },
        "Clear Existing ID": function() {
          $('#bbClearJobId').val(1);
          $("#formSetJob").submit();
          $(this).dialog("close");
        },
        "Set ID": function() {
          $('#bbCurrentJobId').text(' :: ' + $('#bbSetJobId').val())
          $("#formSetJob").submit();
          $(this).dialog("close");
        }
      }
    });
  }

  $('a#bbSetJobId').click(function(){
    console.log('clicked...');
    setJobID();
    return false;
  })
});

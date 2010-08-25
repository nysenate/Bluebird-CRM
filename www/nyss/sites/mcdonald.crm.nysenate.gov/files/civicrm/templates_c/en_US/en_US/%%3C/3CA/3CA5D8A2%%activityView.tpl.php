<?php /* Smarty version 2.6.26, created on 2010-08-23 10:50:46
         compiled from CRM/common/activityView.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/common/activityView.tpl', 49, false),)), $this); ?>
<?php echo '
<script type="text/javascript">
function viewActivity( activityID, contactID ) {
    cj("#view-activity").show( );

    cj("#view-activity").dialog({
        title: "View Activity",
        modal: true, 
        width : "680px", // don\'t remove px
        height: "560", 
        resizable: true,
        bgiframe: true,
        overlay: { 
            opacity: 0.5, 
            background: "black" 
        },

        beforeclose: function(event, ui) {
            cj(this).dialog("destroy");
        },

        open:function() {
            cj("#activity-content").html("");
            var viewUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/case/activity/view','h' => 0,'q' => "snippet=4"), $this);?>
"<?php echo ';
            cj("#activity-content").load( viewUrl + "&cid="+contactID + "&aid=" + activityID);
            
        },

        buttons: { 
            "Done": function() { 	    
                cj(this).dialog("close"); 
                cj(this).dialog("destroy"); 
            }
        }
    });
}
</script>
'; ?>

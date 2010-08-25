<?php /* Smarty version 2.6.26, created on 2010-07-06 12:34:51
         compiled from CRM/Import/Form/Preview.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Import/Form/Preview.tpl', 46, false),)), $this); ?>
<div class="crm-block crm-form-block crm-import-preview-form-block">

<?php echo '
<script type="text/javascript">
function setIntermediate( ) {
	var dataUrl = "'; ?>
<?php echo $this->_tpl_vars['statusUrl']; ?>
<?php echo '";
	cj.getJSON( dataUrl, function( response ) {
	   var dataStr = response.toString();
	   var result  = dataStr.split(",");
	   cj("#intermediate").html( result[1] );
	   cj("#importProgressBar").progressBar( result[0] );
	});
}

function pollLoop( ){
	setIntermediate( );
	window.setTimeout( pollLoop, 10*1000 ); // 10 sec
}

function verify( ) {
    if (! confirm(\''; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Are you sure you want to Import now<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '?\') ) {
        return false;
    }
	
	cj("#id-processing").show( ).dialog({
		modal         : true,
		width         : 350,
		height        : 160,
		resizable     : false,
		bgiframe      : true,
		draggable     : true,
		closeOnEscape : false,
		overlay       : { opacity: 0.5, background: "black" },
		open          : function ( ) {
		    cj("#id-processing").dialog().parents(".ui-dialog").find(".ui-dialog-titlebar").remove();
		}
	});
	
	var imageBase = "'; ?>
<?php echo $this->_tpl_vars['config']->resourceBase; ?>
<?php echo 'packages/jquery/plugins/images/";
    cj("#importProgressBar").progressBar({
        boxImage:       imageBase + \'progressbar.gif\',
        barImage: { 0 : imageBase + \'progressbg_red.gif\',
                    20: imageBase + \'progressbg_orange.gif\',
                    50: imageBase + \'progressbg_yellow.gif\',
                    70: imageBase + \'progressbg_green.gif\'
                  }
	}); 
	cj("#importProgressBar").show( );
	pollLoop( );
}
</script>
'; ?>



  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/WizardHeader.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<div id="help">
    <p>
    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>The information below previews the results of importing your data in CiviCRM. Review the totals to ensure that they represent your expected results.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>         
    </p>
    
    <?php if ($this->_tpl_vars['invalidRowCount']): ?>
        <p class="error">
        <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['invalidRowCount'],'2' => $this->_tpl_vars['downloadErrorRecordsUrl'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>CiviCRM has detected invalid data or formatting errors in %1 records. If you continue, these records will be skipped. OR, you can download a file with just these problem records - <a href='%2'>Download Errors</a>. Then correct them in the original import file, cancel this import and begin again at step 1.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        </p>
    <?php endif; ?>

    <?php if ($this->_tpl_vars['conflictRowCount']): ?>
        <p class="error">
        <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['conflictRowCount'],'2' => $this->_tpl_vars['downloadConflictRecordsUrl'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>CiviCRM has detected %1 records with conflicting email addresses within this data file. If you continue, these records will be skipped. OR, you can download a file with just these problem records - <a href='%2'>Download Conflicts</a>. Then correct them in the original import file, cancel this import and begin again at step 1.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        </p>
    <?php endif; ?>
    
    <p><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Click 'Import Now' if you are ready to proceed.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></p>
</div> 
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div> 
<div id="id-processing" class="hiddenElement">
	<h3>Importing records...</h3><br />
	<div class="progressBar" id="importProgressBar" style="margin-left:45px;display:none;"></div>
	<div id="intermediate"></div>
	<div id="error_status"></div>
</div>

<div id="preview-info">
  <table id="preview-counts" class="report">
    <tr><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Total Rows<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
        <td class="data"><?php echo $this->_tpl_vars['totalRowCount']; ?>
</td>
        <td class="explanation"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Total number of rows in the imported data.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
    </tr>
    
    <?php if ($this->_tpl_vars['invalidRowCount']): ?>
    <tr class="error"><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Rows with Errors<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
        <td class="data"><?php echo $this->_tpl_vars['invalidRowCount']; ?>
</td>
        <td class="explanation"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Rows with invalid data in one or more fields (for example, invalid email address formatting). These rows will be skipped (not imported).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
            <?php if ($this->_tpl_vars['invalidRowCount']): ?>
                <div class="action-link"><a href="<?php echo $this->_tpl_vars['downloadErrorRecordsUrl']; ?>
">&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Download Errors<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a></div>
            <?php endif; ?>
        </td>
    </tr>
    <?php endif; ?>
    
    <?php if ($this->_tpl_vars['conflictRowCount']): ?>
    <tr class="error"><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Conflicting Rows<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
        <td class="data"><?php echo $this->_tpl_vars['conflictRowCount']; ?>
</td>
        <td class="explanation"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Rows with conflicting email addresses within this file. These rows will be skipped (not imported).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
            <?php if ($this->_tpl_vars['conflictRowCount']): ?>
                <div class="action-link"><a href="<?php echo $this->_tpl_vars['downloadConflictRecordsUrl']; ?>
">&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Download Conflicts<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a></div>
            <?php endif; ?>
        </td>
    </tr>
    <?php endif; ?>

    <tr>
		<td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Valid Rows<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
        <td class="data"><?php echo $this->_tpl_vars['validRowCount']; ?>
</td>
        <td class="explanation"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Total rows to be imported.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
    </tr>
 </table>

  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Import/Form/MapTable.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
 
  <div class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add imported records to a new group<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
            <table class="form-layout-compressed">
             <tr>
               <td class="description label"><?php echo $this->_tpl_vars['form']['newGroupName']['label']; ?>
</td>
               <td><?php echo $this->_tpl_vars['form']['newGroupName']['html']; ?>
</td>
             </tr>
             <tr>
               <td class="description label"><?php echo $this->_tpl_vars['form']['newGroupDesc']['label']; ?>
</td>
               <td><?php echo $this->_tpl_vars['form']['newGroupDesc']['html']; ?>
</td>
             </tr>
            </table>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->


      
<div class="crm-accordion-wrapper crm-existing_group-accordion <?php if ($this->_tpl_vars['form']['groups']): ?>crm-accordion-open<?php else: ?>crm-accordion-closed<?php endif; ?>">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div>
  <?php echo $this->_tpl_vars['form']['groups']['label']; ?>

 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
  
        <div class="form-item">
        <table><tr><td style="width: 14em;"></td><td><?php echo $this->_tpl_vars['form']['groups']['html']; ?>
</td></tr></table>
        </div>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

        <div class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div>
  <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Create a new tag and assign it to imported records<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>            
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
  
  <div class="form-item">
	<table class="form-layout-compressed">
           <tr>
       	      <td class="description label"><?php echo $this->_tpl_vars['form']['newTagName']['label']; ?>
</td>
              <td><?php echo $this->_tpl_vars['form']['newTagName']['html']; ?>
</td>
           </tr>
           <tr>
	      <td class="description label"><?php echo $this->_tpl_vars['form']['newTagDesc']['label']; ?>
</td>
              <td><?php echo $this->_tpl_vars['form']['newTagDesc']['html']; ?>
</td>
           </tr>
        </table>
    </div>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
    
<div class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div>
  <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Tag imported records<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
</div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">

        <table class="form-layout-compressed">
            <tr><td style="width: 14em;"></td>
             <td class="listing-box" style="margin-bottom: 0em; width: 15em;">
				<?php $_from = $this->_tpl_vars['form']['tag']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tag_val']):
?> 
					<div><?php echo $this->_tpl_vars['tag_val']['html']; ?>
</div>
				<?php endforeach; endif; unset($_from); ?>
            </td>
          </tr>
        </table>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
</div> 
<div class="crm-submit-buttons">
   <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>
</div>
<?php echo '
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
'; ?>

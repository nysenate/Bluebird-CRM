<?php /* Smarty version 2.6.26, created on 2010-07-06 10:38:33
         compiled from CRM/Import/Form/DataSource.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Import/Form/DataSource.tpl', 37, false),array('function', 'help', 'CRM/Import/Form/DataSource.tpl', 37, false),array('function', 'docURL', 'CRM/Import/Form/DataSource.tpl', 81, false),array('function', 'crmURL', 'CRM/Import/Form/DataSource.tpl', 108, false),)), $this); ?>
  
<div class="crm-block crm-form-block crm-import-datasource-form-block">
<?php if ($this->_tpl_vars['showOnlyDataSourceFormPane']): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['dataSourceFormTemplateFile'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>
    
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/WizardHeader.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
   <div id="help">
      <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>The Import Wizard allows you to easily import contact records from other applications into CiviCRM. For example, if your organization has contacts in MS Access&reg; or Excel&reg;, and you want to start using CiviCRM to store these contacts, you can 'import' them here.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_help(array('id' => 'choose-data-source-intro'), $this);?>

  </div>
  <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
  <div id="choose-data-source" class="form-item">
    <fieldset>
      <legend><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Choose Data Source<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></legend>
      <table class="form-layout">
        <tr class="crm-import-datasource-form-block-dataSource">
            <td class="label"><?php echo $this->_tpl_vars['form']['dataSource']['label']; ?>
</td>
            <td><?php echo $this->_tpl_vars['form']['dataSource']['html']; ?>
 <?php echo smarty_function_help(array('id' => 'data-source-selection'), $this);?>
</td>
        </tr>
      </table>
    </fieldset>
  </div>

    <div id="data-source-form-block">
    <?php if ($this->_tpl_vars['dataSourceFormTemplateFile']): ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['dataSourceFormTemplateFile'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>
  </div>

  <div id="common-form-controls" class="form-item">
    <fieldset>
      <legend><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Import Options<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></legend>
      <table class="form-layout-compressed">
         <tr class="crm-import-datasource-form-block-contactType">
	     <td class="label"><?php echo $this->_tpl_vars['form']['contactType']['label']; ?>
</td>
             <td><?php echo $this->_tpl_vars['form']['contactType']['html']; ?>
 <?php echo smarty_function_help(array('id' => 'contact-type'), $this);?>
&nbsp;&nbsp;&nbsp;
               <span id="contact-subtype"><?php echo $this->_tpl_vars['form']['subType']['label']; ?>
&nbsp;&nbsp;&nbsp;<?php echo $this->_tpl_vars['form']['subType']['html']; ?>
 <?php echo smarty_function_help(array('id' => 'contact-sub-type'), $this);?>
</span></td>
         </tr>
         <tr class="crm-import-datasource-form-block-onDuplicate">
             <td class="label"><?php echo $this->_tpl_vars['form']['onDuplicate']['label']; ?>
</td>
             <td><?php echo $this->_tpl_vars['form']['onDuplicate']['html']; ?>
 <?php echo smarty_function_help(array('id' => 'dupes'), $this);?>
</td>
         </tr>
         <tr><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Core/Date.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></tr>
         <tr>
             <td></td><td class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select the format that is used for date fields in your import data.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
         </tr>
         
        <?php if ($this->_tpl_vars['geoCode']): ?>
         <tr class="crm-import-datasource-form-block-doGeocodeAddress">
             <td><?php echo $this->_tpl_vars['form']['doGeocodeAddress']['html']; ?>
 <?php echo $this->_tpl_vars['form']['doGeocodeAddress']['label']; ?>
<br />
               <span class="description">
                <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>This option is not recommended for large imports. Use the command-line geocoding script instead.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_docURL(array('page' => 'Batch Geocoding Script'), $this);?>

            </td></tr>
        <?php endif; ?>

        <?php if ($this->_tpl_vars['savedMapping']): ?>
         <tr  class="crm-import-datasource-form-block-savedMapping">
              <td class="lable"><?php if ($this->_tpl_vars['loadedMapping']): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select a Different Field Mapping<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php else: ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Load Saved Field Mapping<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?></td>
              <td><?php echo $this->_tpl_vars['form']['savedMapping']['html']; ?>
<br />
	    &nbsp;&nbsp;&nbsp;<span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select Saved Mapping or Leave blank to create a new One.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></td>
         </tr>
        <?php endif; ?>
 </table>
    </fieldset>
  </div>

  <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> </div>

  <?php echo '
    <script type="text/javascript">
      cj(document).ready(function() {    
         //build data source form block
         buildDataSourceFormBlock();
         buildSubTypes();
      });
      
      function buildDataSourceFormBlock(dataSource)
      {
        var dataUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => $this->_tpl_vars['urlPath'],'h' => 0,'q' => $this->_tpl_vars['urlPathVar']), $this);?>
"<?php echo ';

        if (!dataSource ) {
          var dataSource = cj("#dataSource").val();
        }

        if ( dataSource ) {
          dataUrl = dataUrl + \'&dataSource=\' + dataSource;
        } else {
          cj("#data-source-form-block").html( \'\' );
          return;
        }

        cj("#data-source-form-block").load( dataUrl );
      }

      function buildSubTypes( )
      {
        element = cj("\'input[name=contactType]:checked\'").val();
        var postUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/subtype','h' => 0), $this);?>
"<?php echo ';
        var param = \'parentId=\'+ element;
        cj.ajax({ type: "POST", url: postUrl, data: param, async: false, dataType: \'json\',

                        success: function(subtype){
                                                   if ( subtype.length == 0 ) {
                                                      cj("#subType").empty(); 
                                                      cj("#contact-subtype").hide();
                                                   } else {       
                                                       cj("#contact-subtype").show();   
                                                       cj("#subType").empty();                                   

                                                       cj("#subType").append("<option value=\'\'>-Select-</option>");  
                                                       for ( var key in  subtype ) {
                                                           // stick these new options in the subtype select 
                                                           cj("#subType").append("<option value="+key+">"+subtype[key]+" </option>");  
                                                       }
                                                   } 
                                       

                                                 }
  });
       
      }

    </script>
  '; ?>

<?php endif; ?>
</div>
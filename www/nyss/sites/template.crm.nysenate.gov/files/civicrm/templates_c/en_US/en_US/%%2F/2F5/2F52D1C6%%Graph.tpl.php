<?php /* Smarty version 2.6.26, created on 2010-08-24 16:23:39
         compiled from CRM/Report/Form/Layout/Graph.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', 'CRM/Report/Form/Layout/Graph.tpl', 26, false),array('modifier', 'replace', 'CRM/Report/Form/Layout/Graph.tpl', 27, false),)), $this); ?>
<?php $this->assign('chartId', ((is_array($_tmp=$this->_tpl_vars['chartType'])) ? $this->_run_mod_handler('cat', true, $_tmp, "_".($this->_tpl_vars['instanceId'])) : smarty_modifier_cat($_tmp, "_".($this->_tpl_vars['instanceId'])))); ?>
<?php $this->assign('uploadURL', ((is_array($_tmp=$this->_tpl_vars['config']->imageUploadURL)) ? $this->_run_mod_handler('replace', true, $_tmp, 'persist/contribute', 'upload/openFlashChart') : smarty_modifier_replace($_tmp, 'persist/contribute', 'upload/openFlashChart'))); ?>
<?php if ($this->_tpl_vars['chartEnabled'] && $this->_tpl_vars['chartSupported']): ?>
<table class="chart">
        <tr>
            <td>
                <?php if ($this->_tpl_vars['outputMode'] == 'print' || $this->_tpl_vars['outputMode'] == 'pdf'): ?>
                    <img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['uploadURL'])) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['chartId']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['chartId'])); ?>
.png" />                
                <?php else: ?>
	            <div id="open_flash_chart_<?php echo $this->_tpl_vars['uniqueId']; ?>
"></div>
                <?php endif; ?>
            </td>
        </tr>
</table>

<?php if (! $this->_tpl_vars['section']): ?>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/openFlashChart.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<?php echo '
<script type="text/javascript">
   cj( function( ) {
      buildChart( );
      
      var resourceURL = "'; ?>
<?php echo $this->_tpl_vars['config']->userFrameworkResourceURL; ?>
<?php echo '";
      var uploadURL   = "'; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['uploadURL'])) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['chartId']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['chartId'])); ?>
<?php echo '.png";
      var uploadDir   = "'; ?>
<?php echo $this->_tpl_vars['config']->uploadDir; ?>
openFlashChart/<?php echo '"; 

      cj("input[id$=\'submit_print\'],input[id$=\'submit_pdf\']").bind(\'click\', function(){ 
        var url = resourceURL +\'packages/OpenFlashChart/php-ofc-library/ofc_upload_image.php\';  // image creator php file path
           url += \'?name='; ?>
<?php echo $this->_tpl_vars['chartId']; ?>
<?php echo '.png\';                                    // append image name
           url += \'&defaultPath=\' + uploadDir;                                                  // append directory path
        
        //fetch object
        swfobject.getObjectById("open_flash_chart_'; ?>
<?php echo $this->_tpl_vars['uniqueId']; ?>
<?php echo '").post_image( url, true, false );
        });
    });

  function buildChart( ) {
     var chartData = '; ?>
<?php echo $this->_tpl_vars['openFlashChartData']; ?>
<?php echo ';
     cj.each( chartData, function( chartID, chartValues ) {
	     var xSize   = eval( "chartValues.size.xSize" );
	     var ySize   = eval( "chartValues.size.ySize" );
	     var divName = '; ?>
"open_flash_chart_<?php echo $this->_tpl_vars['uniqueId']; ?>
"<?php echo ';

	     var loadDataFunction  = '; ?>
"loadData<?php echo $this->_tpl_vars['uniqueId']; ?>
"<?php echo ';
	     createSWFObject( chartID, divName, xSize, ySize, loadDataFunction );
     });
  }
  
  function loadData'; ?>
<?php echo $this->_tpl_vars['uniqueId']; ?>
<?php echo '( chartID ) {
      var allData = '; ?>
<?php echo $this->_tpl_vars['openFlashChartData']; ?>
<?php echo ';
      var data    = eval( "allData." + chartID + ".object" );
      return JSON.stringify( data );
  }  
</script>
'; ?>

<?php endif; ?>
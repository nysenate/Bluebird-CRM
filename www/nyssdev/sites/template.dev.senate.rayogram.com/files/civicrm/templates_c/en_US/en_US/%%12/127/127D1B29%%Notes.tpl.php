<?php /* Smarty version 2.6.26, created on 2010-05-26 18:02:46
         compiled from CRM/Contact/Form/Edit/Notes.tpl */ ?>
<div class="crm-accordion-wrapper crm-notesBlock-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	<?php echo $this->_tpl_vars['title']; ?>
 
  </div><!-- /.crm-accordion-header -->
  <div class="crm-accordion-body" id="notesBlock">
   <table class="form-layout-compressed">
     <tr>
       <td colspan=3><?php echo $this->_tpl_vars['form']['subject']['label']; ?>
<br  >
        <?php echo $this->_tpl_vars['form']['subject']['html']; ?>
</td>
     </tr>
     <tr>
       <td colspan=3><?php echo $this->_tpl_vars['form']['note']['label']; ?>
<br />
        <?php echo $this->_tpl_vars['form']['note']['html']; ?>

       </td>
     </tr>
   </table>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
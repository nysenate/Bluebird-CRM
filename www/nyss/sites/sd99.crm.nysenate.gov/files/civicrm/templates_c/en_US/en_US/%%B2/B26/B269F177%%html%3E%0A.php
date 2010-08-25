<?php /* Smarty version 2.6.26, created on 2010-08-25 15:04:22
         compiled from string:%3C%21DOCTYPE+html+PUBLIC+%22-//W3C//DTD+XHTML+1.0+Transitional//EN%22+%22http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd%22%3E%0A%3Chtml+xmlns%3D%22http://www.w3.org/1999/xhtml%22%3E%0A%3Chead%3E%0A+%3Cmeta+http-equiv%3D%22Content-Type%22+content%3D%22text/html%3B+charset%3DUTF-8%22+/%3E%0A+%3Ctitle%3E%3C/title%3E%0A%3C/head%3E%0A%3Cbody%3E%0A%0A%7Bcapture+assign%3DheaderStyle%7Dcolspan%3D%222%22+style%3D%22text-align:+left%3B+padding:+4px%3B+border-bottom:+1px+solid+%23999%3B+background-color:+%23eee%3B%22%7B/capture%7D%0A%7Bcapture+assign%3DlabelStyle+%7Dstyle%3D%22padding:+4px%3B+border-bottom:+1px+solid+%23999%3B+background-color:+%23f7f7f7%3B%22%7B/capture%7D%0A%7Bcapture+assign%3DvalueStyle+%7Dstyle%3D%22padding:+4px%3B+border-bottom:+1px+solid+%23999%3B%22%7B/capture%7D%0A%0A%3Ccenter%3E%0A+%3Ctable+width%3D%22620%22+border%3D%220%22+cellpadding%3D%220%22+cellspacing%3D%220%22+id%3D%22crm-event_receipt%22+style%3D%22font-family:+Arial%2C+Verdana%2C+sans-serif%3B+text-align:+left%3B%22%3E%0A%0A++%3C%21--+BEGIN+HEADER+--%3E%0A++%3C%21--+You+can+add+table+row%28s%29+here+with+logo+or+other+header+elements+--%3E%0A++%3C%21--+END+HEADER+--%3E%0A%0A++%3C%21--+BEGIN+CONTENT+--%3E%0A%0A++%3Ctr%3E%0A+++%3Ctd%3E%0A++++%3Ctable+style%3D%22border:+1px+solid+%23999%3B+margin:+1em+0em+1em%3B+border-collapse:+collapse%3B+width:100%25%3B%22%3E%0A+++++%3Ctr%3E%0A++++++%3Cth+%7B%24headerStyle%7D%3E%0A+++++++%7Bts%7DActivity+Summary%7B/ts%7D+-+%7B%24activityTypeName%7D%0A++++++%3C/th%3E%0A+++++%3C/tr%3E%0A+++++%7Bif+%24isCaseActivity%7D%0A++++++%3Ctr%3E%0A+++++++%3Ctd+%7B%24labelStyle%7D%3E%0A++++++++%7Bts%7DYour+Case+Role%28s%29%7B/ts%7D%0A+++++++%3C/td%3E%0A+++++++%3Ctd+%7B%24valueStyle%7D%3E%0A++++++++%7B%24contact.role%7D%0A+++++++%3C/td%3E%0A++++++%3C/tr%3E%0A+++++%7B/if%7D%0A+++++%7Bforeach+from%3D%24activity.fields+item%3Dfield%7D%0A++++++%3Ctr%3E%0A+++++++%3Ctd+%7B%24labelStyle%7D%3E%0A++++++++%7B%24field.label%7D%7Bif+%24field.category%7D%28%7B%24field.category%7D%29%7B/if%7D%0A+++++++%3C/td%3E%0A+++++++%3Ctd+%7B%24valueStyle%7D%3E%0A++++++++%7Bif+%24field.type+eq+%27Date%27%7D%0A+++++++++%7B%24field.value%7CcrmDate:%24config-%3EdateformatDatetime%7D%0A++++++++%7Belse%7D%0A+++++++++%7B%24field.value%7D%0A++++++++%7B/if%7D%0A+++++++%3C/td%3E%0A++++++%3C/tr%3E%0A+++++%7B/foreach%7D%0A%0A+++++%7Bforeach+from%3D%24activity.customGroups+key%3DcustomGroupName+item%3DcustomGroup%7D%0A++++++%3Ctr%3E%0A+++++++%3Cth+%7B%24headerStyle%7D%3E%0A++++++++%7B%24customGroupName%7D%0A+++++++%3C/th%3E%0A++++++%3C/tr%3E%0A++++++%7Bforeach+from%3D%24customGroup+item%3Dfield%7D%0A+++++++%3Ctr%3E%0A++++++++%3Ctd+%7B%24labelStyle%7D%3E%0A+++++++++%7B%24field.label%7D%0A++++++++%3C/td%3E%0A++++++++%3Ctd+%7B%24valueStyle%7D%3E%0A+++++++++%7Bif+%24field.type+eq+%27Date%27%7D%0A++++++++++%7B%24field.value%7CcrmDate:%24config-%3EdateformatDatetime%7D%0A+++++++++%7Belse%7D%0A++++++++++%7B%24field.value%7D%0A+++++++++%7B/if%7D%0A++++++++%3C/td%3E%0A+++++++%3C/tr%3E%0A++++++%7B/foreach%7D%0A+++++%7B/foreach%7D%0A++++%3C/table%3E%0A+++%3C/td%3E%0A++%3C/tr%3E%0A+%3C/table%3E%0A%3C/center%3E%0A%0A%3C/body%3E%0A%3C/html%3E%0A */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'string:<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title></title>
</head>
<body>

{capture assign=headerStyle}colspan="2" style="text-align: left; padding: 4px; border-bottom: 1px solid #999; background-color: #eee;"{/capture}
{capture assign=labelStyle }style="padding: 4px; border-bottom: 1px solid #999; background-color: #f7f7f7;"{/capture}
{capture assign=valueStyle }style="padding: 4px; border-bottom: 1px solid #999;"{/capture}

<center>
 <table width="620" border="0" cellpadding="0" cellspacing="0" id="crm-event_receipt" style="font-family: Arial, Verdana, sans-serif; text-align: left;">

  <!-- BEGIN HEADER -->
  <!-- You can add table row(s) here with logo or other header elements -->
  <!-- END HEADER -->

  <!-- BEGIN CONTENT -->

  <tr>
   <td>
    <table style="border: 1px solid #999; margin: 1em 0em 1em; border-collapse: collapse; width:100%;">
     <tr>
      <th {$headerStyle}>
       {ts}Activity Summary{/ts} - {$activityTypeName}
      </th>
     </tr>
     {if $isCaseActivity}
      <tr>
       <td {$labelStyle}>
        {ts}Your Case Role(s){/ts}
       </td>
       <td {$valueStyle}>
        {$contact.role}
       </td>
      </tr>
     {/if}
     {foreach from=$activity.fields item=field}
      <tr>
       <td {$labelStyle}>
        {$field.label}{if $field.category}({$field.category}){/if}
       </td>
       <td {$valueStyle}>
        {if $field.type eq \'Date\'}
         {$field.value|crmDate:$config->dateformatDatetime}
        {else}
         {$field.value}
        {/if}
       </td>
      </tr>
     {/foreach}

     {foreach from=$activity.customGroups key=customGroupName item=customGroup}
      <tr>
       <th {$headerStyle}>
        {$customGroupName}
       </th>
      </tr>
      {foreach from=$customGroup item=field}
       <tr>
        <td {$labelStyle}>
         {$field.label}
        </td>
        <td {$valueStyle}>
         {if $field.type eq \'Date\'}
          {$field.value|crmDate:$config->dateformatDatetime}
         {else}
          {$field.value}
         {/if}
        </td>
       </tr>
      {/foreach}
     {/foreach}
    </table>
   </td>
  </tr>
 </table>
</center>

</body>
</html>
', 27, false),array('modifier', 'crmDate', 'string:<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title></title>
</head>
<body>

{capture assign=headerStyle}colspan="2" style="text-align: left; padding: 4px; border-bottom: 1px solid #999; background-color: #eee;"{/capture}
{capture assign=labelStyle }style="padding: 4px; border-bottom: 1px solid #999; background-color: #f7f7f7;"{/capture}
{capture assign=valueStyle }style="padding: 4px; border-bottom: 1px solid #999;"{/capture}

<center>
 <table width="620" border="0" cellpadding="0" cellspacing="0" id="crm-event_receipt" style="font-family: Arial, Verdana, sans-serif; text-align: left;">

  <!-- BEGIN HEADER -->
  <!-- You can add table row(s) here with logo or other header elements -->
  <!-- END HEADER -->

  <!-- BEGIN CONTENT -->

  <tr>
   <td>
    <table style="border: 1px solid #999; margin: 1em 0em 1em; border-collapse: collapse; width:100%;">
     <tr>
      <th {$headerStyle}>
       {ts}Activity Summary{/ts} - {$activityTypeName}
      </th>
     </tr>
     {if $isCaseActivity}
      <tr>
       <td {$labelStyle}>
        {ts}Your Case Role(s){/ts}
       </td>
       <td {$valueStyle}>
        {$contact.role}
       </td>
      </tr>
     {/if}
     {foreach from=$activity.fields item=field}
      <tr>
       <td {$labelStyle}>
        {$field.label}{if $field.category}({$field.category}){/if}
       </td>
       <td {$valueStyle}>
        {if $field.type eq \'Date\'}
         {$field.value|crmDate:$config->dateformatDatetime}
        {else}
         {$field.value}
        {/if}
       </td>
      </tr>
     {/foreach}

     {foreach from=$activity.customGroups key=customGroupName item=customGroup}
      <tr>
       <th {$headerStyle}>
        {$customGroupName}
       </th>
      </tr>
      {foreach from=$customGroup item=field}
       <tr>
        <td {$labelStyle}>
         {$field.label}
        </td>
        <td {$valueStyle}>
         {if $field.type eq \'Date\'}
          {$field.value|crmDate:$config->dateformatDatetime}
         {else}
          {$field.value}
         {/if}
        </td>
       </tr>
      {/foreach}
     {/foreach}
    </table>
   </td>
  </tr>
 </table>
</center>

</body>
</html>
', 47, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title></title>
</head>
<body>

<?php ob_start(); ?>colspan="2" style="text-align: left; padding: 4px; border-bottom: 1px solid #999; background-color: #eee;"<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('headerStyle', ob_get_contents());ob_end_clean(); ?>
<?php ob_start(); ?>style="padding: 4px; border-bottom: 1px solid #999; background-color: #f7f7f7;"<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('labelStyle', ob_get_contents());ob_end_clean(); ?>
<?php ob_start(); ?>style="padding: 4px; border-bottom: 1px solid #999;"<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('valueStyle', ob_get_contents());ob_end_clean(); ?>

<center>
 <table width="620" border="0" cellpadding="0" cellspacing="0" id="crm-event_receipt" style="font-family: Arial, Verdana, sans-serif; text-align: left;">

  <!-- BEGIN HEADER -->
  <!-- You can add table row(s) here with logo or other header elements -->
  <!-- END HEADER -->

  <!-- BEGIN CONTENT -->

  <tr>
   <td>
    <table style="border: 1px solid #999; margin: 1em 0em 1em; border-collapse: collapse; width:100%;">
     <tr>
      <th <?php echo $this->_tpl_vars['headerStyle']; ?>
>
       <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Activity Summary<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> - <?php echo $this->_tpl_vars['activityTypeName']; ?>

      </th>
     </tr>
     <?php if ($this->_tpl_vars['isCaseActivity']): ?>
      <tr>
       <td <?php echo $this->_tpl_vars['labelStyle']; ?>
>
        <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Your Case Role(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
       </td>
       <td <?php echo $this->_tpl_vars['valueStyle']; ?>
>
        <?php echo $this->_tpl_vars['contact']['role']; ?>

       </td>
      </tr>
     <?php endif; ?>
     <?php $_from = $this->_tpl_vars['activity']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field']):
?>
      <tr>
       <td <?php echo $this->_tpl_vars['labelStyle']; ?>
>
        <?php echo $this->_tpl_vars['field']['label']; ?>
<?php if ($this->_tpl_vars['field']['category']): ?>(<?php echo $this->_tpl_vars['field']['category']; ?>
)<?php endif; ?>
       </td>
       <td <?php echo $this->_tpl_vars['valueStyle']; ?>
>
        <?php if ($this->_tpl_vars['field']['type'] == 'Date'): ?>
         <?php echo ((is_array($_tmp=$this->_tpl_vars['field']['value'])) ? $this->_run_mod_handler('crmDate', true, $_tmp, $this->_tpl_vars['config']->dateformatDatetime) : smarty_modifier_crmDate($_tmp, $this->_tpl_vars['config']->dateformatDatetime)); ?>

        <?php else: ?>
         <?php echo $this->_tpl_vars['field']['value']; ?>

        <?php endif; ?>
       </td>
      </tr>
     <?php endforeach; endif; unset($_from); ?>

     <?php $_from = $this->_tpl_vars['activity']['customGroups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['customGroupName'] => $this->_tpl_vars['customGroup']):
?>
      <tr>
       <th <?php echo $this->_tpl_vars['headerStyle']; ?>
>
        <?php echo $this->_tpl_vars['customGroupName']; ?>

       </th>
      </tr>
      <?php $_from = $this->_tpl_vars['customGroup']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field']):
?>
       <tr>
        <td <?php echo $this->_tpl_vars['labelStyle']; ?>
>
         <?php echo $this->_tpl_vars['field']['label']; ?>

        </td>
        <td <?php echo $this->_tpl_vars['valueStyle']; ?>
>
         <?php if ($this->_tpl_vars['field']['type'] == 'Date'): ?>
          <?php echo ((is_array($_tmp=$this->_tpl_vars['field']['value'])) ? $this->_run_mod_handler('crmDate', true, $_tmp, $this->_tpl_vars['config']->dateformatDatetime) : smarty_modifier_crmDate($_tmp, $this->_tpl_vars['config']->dateformatDatetime)); ?>

         <?php else: ?>
          <?php echo $this->_tpl_vars['field']['value']; ?>

         <?php endif; ?>
        </td>
       </tr>
      <?php endforeach; endif; unset($_from); ?>
     <?php endforeach; endif; unset($_from); ?>
    </table>
   </td>
  </tr>
 </table>
</center>

</body>
</html>
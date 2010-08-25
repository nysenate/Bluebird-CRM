<?php /* Smarty version 2.6.26, created on 2010-08-23 16:44:21
         compiled from CRM/Case/XMLProcessor/Report.tpl */ ?>
<?php echo '<?xml'; ?>
 version="1.0" encoding="UTF-8"<?php echo '?>'; ?>

<Case>
  <Client><?php echo $this->_tpl_vars['case']['clientName']; ?>
</Client>
  <CaseType><?php echo $this->_tpl_vars['case']['caseType']; ?>
</CaseType>
  <CaseSubject><?php echo $this->_tpl_vars['case']['subject']; ?>
</CaseSubject>
  <CaseStatus><?php echo $this->_tpl_vars['case']['status']; ?>
</CaseStatus>
  <CaseOpen><?php echo $this->_tpl_vars['case']['start_date']; ?>
</CaseOpen>
  <CaseClose><?php echo $this->_tpl_vars['case']['end_date']; ?>
</CaseClose>
  <ActivitySet>
    <Label><?php echo $this->_tpl_vars['activitySet']['label']; ?>
</Label>
    <IncludeActivities><?php echo $this->_tpl_vars['includeActivities']; ?>
</IncludeActivities>
    <Redact><?php echo $this->_tpl_vars['isRedact']; ?>
</Redact>
<?php $_from = $this->_tpl_vars['activities']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['activity']):
?>
    <Activity>
       <EditURL><?php echo $this->_tpl_vars['activity']['editURL']; ?>
</EditURL>
       <Fields>
<?php $_from = $this->_tpl_vars['activity']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field']):
?>
          <Field>
            <Label><?php echo $this->_tpl_vars['field']['label']; ?>
</Label>
<?php if ($this->_tpl_vars['field']['category']): ?>
            <Category><?php echo $this->_tpl_vars['field']['category']; ?>
</Category>
<?php endif; ?>
            <Value><?php echo $this->_tpl_vars['field']['value']; ?>
</Value>
            <Type><?php echo $this->_tpl_vars['field']['type']; ?>
</Type>
          </Field>
<?php endforeach; endif; unset($_from); ?>
<?php if ($this->_tpl_vars['activity']['customGroups']): ?>
         <CustomGroups>
<?php $_from = $this->_tpl_vars['activity']['customGroups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['customGroupName'] => $this->_tpl_vars['customGroup']):
?>
            <CustomGroup>
               <GroupName><?php echo $this->_tpl_vars['customGroupName']; ?>
</GroupName>
<?php $_from = $this->_tpl_vars['customGroup']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field']):
?>
                  <Field>
                    <Label><?php echo $this->_tpl_vars['field']['label']; ?>
</Label>
                    <Value><?php echo $this->_tpl_vars['field']['value']; ?>
</Value>
                    <Type><?php echo $this->_tpl_vars['field']['type']; ?>
</Type>
                  </Field>
<?php endforeach; endif; unset($_from); ?>
            </CustomGroup>
<?php endforeach; endif; unset($_from); ?>
         </CustomGroups>
<?php endif; ?>
       </Fields>
    </Activity>
<?php endforeach; endif; unset($_from); ?>
  </ActivitySet>
</Case>

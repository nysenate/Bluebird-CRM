/* construct the intermediate root */
set @c_root = concat(replace(replace(@root,'//','/'),'\\','/'),'/civicrm/custom/');

/* update the custom PHP path */
set @c_dir = concat(@c_root,'php');
update civicrm_setting 
  set `value`=concat('s:',length(@c_dir),':"',@c_dir,'";') 
  where name='customPHPPathDir';

/* update the custom template path */
set @c_dir = concat(@c_root,'templates');

update civicrm_setting 
  set `value`=concat('s:',length(@c_dir),':"',@c_dir,'";') 
  where name='customTemplateDir';
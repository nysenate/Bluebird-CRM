{*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
<div class="crm-accordion-wrapper crm-demographics-accordion collapsed">
 <div class="crm-accordion-header">
    {$title}
  </div><!-- /.crm-accordion-header -->
  <div id="demographics" class="crm-accordion-body">
  
  {*NYSS*}
  <div class="leftColumn">
  <div class="form-item">
        <span class="label">{$form.gender_id.label}</span>

  <span class="value">
        {*NYSS*}
        {$form.gender_id.html|crmInsert:onclick:'showOtherGender()'}
        <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('gender_id', '{$form.formName}'); return false;">{ts}clear{/ts}</a>)</span>
        </span>
  </div>
  {*NYSS*}
  <div id="showOtherGender" class="form-item" style="display:none;">
        {assign var='custom_45' value=$groupTree.1.fields.45.element_name}
        <span class="labels">{$form.$custom_45.label}</span>
        <span class="fields">{$form.$custom_45.html}</span>
  </div>
  <div class="form-item">
        <span class="label">{$form.birth_date.label}</span>
        <span class="fields">{$form.birth_date.html}</span>
  </div>
  <div class="form-item">
       {$form.is_deceased.html}
       {$form.is_deceased.label}
  </div>
  <div id="showDeceasedDate" class="form-item">
       <span class="label">{$form.deceased_date.label}</span>
       <span class="fields">{$form.deceased_date.html}</span>
  </div> 
  </div>{*NYSS*}
  
  {*NYSS*}
  <div class="rightColumn">
  <div class="form-item">
        {assign var='custom_58' value=$groupTree.1.fields.58.element_name}
        <span class="labels">{$form.$custom_58.label}</span>
        <span class="fields">{$form.$custom_58.html}</span>
  </div>
  <div class="form-item">
        {assign var='custom_62' value=$groupTree.1.fields.62.element_name}
        <span class="labels">{$form.$custom_62.label}</span>
        <span class="fields">{$form.$custom_62.html}</span>
  </div>
  <div class="form-item">
        {assign var='custom_63' value=$groupTree.1.fields.63.element_name}
        <span class="labels">{$form.$custom_63.label}</span>
        <span class="fields">{$form.$custom_63.html}</span>
  </div>
  </div>
  
  <div class="clear"></div>
 
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

{literal}
<script type="text/javascript">
    showDeceasedDate( );
    function showDeceasedDate( )
    {
        if ( cj("#is_deceased").is(':checked') ) {
            cj("#showDeceasedDate").show( );
        } else {
    cj("#showDeceasedDate").hide( );
         cj("#deceased_date").val('');
        }
    }

  //NYSS
  showOtherGender( );
  function showOtherGender( ) {
    var x=document.getElementsByName("gender_id");
    if (x[2].checked){
      cj('#showOtherGender').show();
    }
    else {
      //NYSS 5783
      cj('input[name^=custom_45_]').val('');
      cj('#showOtherGender').hide();
    }
  }

  //NYSS 5783
  cj('div#demographics span.crm-clear-link a').click(function(){
    showOtherGender();
  });
</script>
{/literal}

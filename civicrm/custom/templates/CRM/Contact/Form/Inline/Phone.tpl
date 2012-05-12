{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
{* This file provides the template for inline editing of phones *}
<table class="crm-inline-edit-form">
    <tr>
      <td colspan="5">
        <div class="crm-submit-buttons"> 
          {include file="CRM/common/formButtons.tpl"}
        </div>
      </td>
    </tr>
    <tr>
      <td>{ts}Phone{/ts}&nbsp; 
      <span id="add-more" title="{ts}click to add more{/ts}"><a class="crm-link-action">{ts}add{/ts}</a></span>
      </td>
	    <td>{ts}Phone Location{/ts}</td>
	    <td>{ts}Phone Type{/ts}</td>
      <td>{ts}Primary?{/ts}</td>
      <td>&nbsp;</td>
    </tr>
    {section name='i' start=1 loop=$totalBlocks}
    {assign var='blockId' value=$smarty.section.i.index} 
    <tr id="Phone_Block_{$blockId}" {if $blockId gt $actualBlockCount}class="hiddenElement"{/if}>
        <td>{$form.phone.$blockId.phone.html}&nbsp;&nbsp;{ts}ext.{/ts}&nbsp;{$form.phone.$blockId.phone_ext.html|crmReplace:class:four}&nbsp;</td>
        <td>{$form.phone.$blockId.location_type_id.html}</td>
        <td>{$form.phone.$blockId.phone_type_id.html}</td>
        <td align="center" class="crm-phone-is_primary">{$form.phone.$blockId.is_primary.1.html}</td>
        <td>
          {if $blockId gt 1}
            <a class="crm-delete-phone crm-link-action" title="{ts}delete phone block{/ts}">{ts}delete{/ts}</a>
          {/if}
        </td>
    </tr>
    {/section}
</table>

{literal}
<script type="text/javascript">
    cj( function() {
      // check first primary radio
      cj('#Phone_1_IsPrimary').prop('checked', true );
     
      // make sure only one is primary radio is checked
      cj('.crm-phone-is_primary input').click(function(){
        cj('.crm-phone-is_primary input').each(function(){
          cj(this).prop('checked', false);
        });
        cj(this).prop('checked', true);
      });

      // handle delete of block
      cj('.crm-delete-phone').click( function(){
        cj(this).closest('tr').each(function(){
          cj(this).find('input').val('');
          //if the primary is checked for deleted block
          //unset and set first as primary
          if (cj(this).find('.crm-phone-is_primary input').prop('checked') ) {
            cj(this).find('.crm-phone-is_primary input').prop('checked', false);
            cj('#Phone_1_IsPrimary').prop('checked', true );
          }
          cj(this).addClass('hiddenElement');
        });
      });

      // add more
      cj('#add-more').click(function() {
        cj('tr[id^="Phone_Block_"][class="hiddenElement"] :first').parent().removeClass('hiddenElement');

        if ( cj('tr[id^="Phone_Block_"][class="hiddenElement"]').length == 0  ) {
          cj('#add-more').hide();
        }
      });

      // handle ajax form submitting
      var options = { 
          beforeSubmit:  showRequest  // pre-submit callback  
      }; 
      
      // bind form using 'ajaxForm'
      cj('#Phone').ajaxForm( options );

      // pre-submit callback 
      function showRequest(formData, jqForm, options) { 
          // formData is an array; here we use $.param to convert it to a string to display it 
          // but the form plugin does this for you automatically when it submits the data 
          var queryString = cj.param(formData); 
          queryString = queryString + '&class_name=CRM_Contact_Form_Inline_Phone&snippet=5&cid=' + {/literal}"{$contactId}"{literal};
          var postUrl = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 }"{literal}; 
          var status = '';
          var response = cj.ajax({
             type: "POST",
             url: postUrl,
             async: false,
             data: queryString,
             dataType: "json",
             success: function( response ) {
               status = response.status; 
             }
          }).responseText;

          //check if form is submitted successfully
          if ( status ) {
            // fetch the view of email block after edit
            var postUrl = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 q='snippet=5&reset=1' }"{literal}; 
            var queryString = 'class_name=CRM_Contact_Page_Inline_Phone&type=page&cid=' + {/literal}"{$contactId}"{literal};
            var response = cj.ajax({
               type: "POST",
               url: postUrl,
               async: false,
               data: queryString,
               dataType: "json",
               success: function( response ) {
               }
            }).responseText;
          }
            
          cj('#phone-block').html( response );
          
          // here we could return false to prevent the form from being submitted; 
          // returning anything other than false will allow the form submit to continue 
          return false; 
      }
    });

</script>
{/literal}

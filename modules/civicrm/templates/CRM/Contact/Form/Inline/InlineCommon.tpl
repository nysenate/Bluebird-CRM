{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
{* This file contain js used for form submitting for inline edit *}

{literal}
<script type="text/javascript">
function inlineEditForm( formName, blockName, contactId, cgId, locNo, addId ) {
  // handle ajax form submitting
  var options = { 
    beforeSubmit:  showRequest  // pre-submit callback  
  }; 

  var actualFormName = formName;

  // since we auto-generate form name for address based on address block
  if ( formName == 'Address' ) {
    actualFormName = formName + '_' + locNo;
  }

  cj('#' + actualFormName ).ajaxForm( options );

  // bind form using 'ajaxForm'
  cj('#' + formName ).ajaxForm( options );

  // pre-submit callback 
  function showRequest(formData, jqForm, options) { 
    // formData is an array; here we use $.param to convert it to a string to display it 
    // but the form plugin does this for you automatically when it submits the data 
    var queryString = cj.param(formData); 
    queryString = queryString + '&class_name=CRM_Contact_Form_Inline_' + formName + '&snippet=5&cid=' + contactId;
    if ( cgId ) {
      queryString += '&groupID=' + cgId;
    }

    if ( locNo ) {
      queryString += '&locno=' + locNo;
    }

    if ( addId ) {
      queryString += '&aid=' + addId;
    }

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
          if ( response.addressId ) {
            addId = response.addressId;
          }
        }
    }).responseText;

    //check if form is submitted successfully
    if ( status ) {
      // fetch the view of the block after edit
      var postUrl = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 q='snippet=5&reset=1' }"{literal}; 
      var queryString = 'class_name=CRM_Contact_Page_Inline_' + formName + '&type=page&cid=' + contactId;
      if ( cgId ) {
        queryString += '&groupID=' + cgId;
      }
 
      if ( locNo ) {
        queryString += '&locno=' + locNo;
      }

      if ( addId ) {
        queryString += '&aid=' + addId;
      }

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
    
    var blockSelector = cj('#' + blockName);

    blockSelector.html( response );
    
    // hack to append add link properly.
    if ( formName == 'Address' ) {
      var addLinkBlock = cj('#' + blockName + ' div.appendAddLink');
      blockSelector.parents('.contact_panel').append(addLinkBlock);
    }

    // here we could return false to prevent the form from being submitted; 
    // returning anything other than false will allow the form submit to continue 
    return false; 
  }
}

</script>
{/literal}

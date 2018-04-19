CRM.$(function($) {
  $("#mainTabContainer").tabs({active: 0});
  var contact_id = CRM.vars.NYSS.matched_id;
  $('#contact_positions').on('select2-selecting', function(e) {
    CRM.api3('nyss_tags', 'savePosition', {value:e.val, contactId:contact_id}, false);
  });

  //process emails
  loadEmails();
  $('#assignee').change(function(){
    loadEmails();
  });

  // DOM element creation function for match details.
  /*
  $('div#match-emails .content').append('<div id="cid-' + id +
    '" class="match-details"><input type="text" name="email-' + id + '" value="' + result.email +
    '"><input type="hidden" name="emailorig-' + id + '" value="' + result.email + '"></div>');
    */
  function addDetailRow(r) {
    var cid = r.contact_id,
      ctnr = $('<div class="match-details"></div>')
        .attr('id', 'cid-' + cid),
      ctrl = null,
      d = null;

    ['email', 'phone'].forEach(function (v) {
      ctrl = $('<input type="text" />')
        .addClass('text-input-' + v)
        .attr('name', v + '-' + cid)
        .attr('placeholder', v);
      if (r[v]) {
        ctrl.attr('value', r[v]);
      }
      ctnr.append(ctrl);
      ctnr.append(ctrl.clone()
        .attr('name', v + 'orig-' + cid)
        .attr('type', 'hidden'));
    });

    return ctnr;

  }

  function loadEmails() {
    var id = $('#assignee').val();
    if (!id) {
      id = $('input[name=matched_id]').val();
    }

    if (typeof id !== 'undefined' && id.length > 0) {
      //remove existing value first
      $('div.match-details').remove();
      var contact = CRM.api3('contact', 'getsingle', {id: id})
        .done(function (result) {
          //console.log(result);
          $('div#match-emails .content').empty().html(addDetailRow(result));
        });
    }
  }

  // Click handler for phone numbers, populates the selected contacts.
  $('span.found.phone').click(function () {
    var pn = $(this).text();
    $('.match-details input.text-input-phone[type=text]').val(pn);
  });

  // Click handler for normal email addresses, populates the selected contacts.
  $('span.email_address').click(function () {
    var email = $(this).text();
    $('.match-details input.text-input-email[type=text]').val(email);
  });

  // Click handler for aggregator emails, populates only after confirmation.
  $('span.aggregator_email').click(function () {
    var email = $(this).text(),
      msg = "The email address " + email + " has been flagged as belonging " +
        "to a public action entity.  This is not a constituent's email " +
        "address.<br />Are you sure you want to add this email address to the " +
        "consitutent record?",
      options = {message: msg};
    CRM.confirm(options)
      .on('crmConfirm:yes', function () {
        $('.match-details input.text-input-email[type=text]').val(email);
      });
  });

  //tag tree
  (function($, _){
    var entityID = null,
      entityTable='civicrm_contact',
      $form = $('form.CRM_NYSS_Inbox_Form_Process');

    $(function() {
      // Load js tree.
      CRM.loadScript(CRM.config.resourceBase + 'bower_components/jstree/dist/jstree.min.js').done(function() {
        $("#tagtree").jstree({
          plugins : ['search', 'wholerow', 'checkbox'],
          core: {
            animation: 100,
            themes: {
              "theme": 'classic',
              "dots": false,
              "icons": false
            }
          },
          'search': {
            'case_insensitive' : true,
            'show_only_matches': true
          },
          checkbox: {
            three_state: false
          }
        })
          .on('select_node.jstree deselect_node.jstree', function(e, selected) {
            /*var id = selected.node.a_attr.id.replace('tag_', ''),
              op = e.type === 'select_node' ? 'create' : 'delete';
            CRM.api3('entity_tag', op, {entity_table: entityTable, entity_id: entityID, tag_id: id}, true);*/
          });
      });

      $('input[name=filter_tag_tree]', '#Tag').on('keyup change', function() {
        $("#tagtree").jstree(true).search($(this).val());
      });
    });
  })(CRM.$, CRM._);
});

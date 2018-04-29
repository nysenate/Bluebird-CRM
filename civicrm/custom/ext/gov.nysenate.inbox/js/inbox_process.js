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

  function addDetailRow(r) {
    var cid = r.contact_id,
      ctnr = $('<div class="match-details-row"></div>')
        .attr('id', 'cid-' + cid),
      ctrl = null,
      d = null,
      icons = {email: 'fa-envelope-o', phone: 'fa-phone'};

    ['email', 'phone'].forEach(function (v) {
      d = $('<div></div>').addClass('match-details match-details-' + v);
      if (icons.hasOwnProperty(v)) {
        d.prepend($('<i></i>').addClass('crm-i ' + icons[v]));
      }
      ctrl = $('<input type="text" />')
        .addClass('text-input-' + v)
        .attr('name', v + '-' + cid)
        .attr('placeholder', v);
      if (r[v]) {
        ctrl.attr('value', r[v]);
      }
      d.append(ctrl);
      d.append(ctrl.clone()
        .attr('name', v + 'orig-' + cid)
        .attr('type', 'hidden'));

      ctnr.append(d);
    });

    return ctnr;

  }

  function loadEmails() {
    var id = $('#assignee').val(),
      orig_id = $('input[name=matched_id]').val(),
      new_assign = (typeof id !== undefined && id.length > 0);

    // If a new assignment is not detected, we will be pulling the row
    // for the original/current assignee.  We'll need to remove the
    // existing row.
    if (!new_assign) {
      id = orig_id;
      $('#current-assignee .match-details-row').remove();
    }

    // remove any new match details from previous searches.
    $('#matched-contacts .match-details-row').remove();

    if (typeof id !== 'undefined' && id.length > 0) {
      var contact = CRM.api3('contact', 'getsingle', {id: id})
        .done(function (result) {
          // If there is a new assignment, replace the current assignee's form
          // controls with "disabled" text boxes.
          if (new_assign) {
            ['email','phone'].forEach(function(vv) {
              var orig_val = $('#current-assignee input[name=' + vv + 'orig-' + orig_id + ']').val(),
                replaceDiv = $('<div class="disabled-text-input"></div>').html(orig_val);
              $('#current-assignee input[name=' + vv + '-' + orig_id + ']').replaceWith(replaceDiv);
            });
          }

          //console.log(result);
          var selector = new_assign ? '#matched-contacts' : '#current-assignee';
          $(selector + ' .content').append(addDetailRow(result));
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

// generic JS fixes
var $ = jQuery.noConflict();

// various JavaScript object.
var Blueprint = {};

// jump to the value in a select drop down
Blueprint.go = function(e) {
  var destination = e.options[e.selectedIndex].value;
  if (destination && destination != 0) location.href = destination;
};

// prevent users from clicking a submit button twice
Blueprint.formCheck = function() {
  // only apply this to node and comment and new user registration forms
  var forms = $("#node-form>div>div>#edit-submit,#comment-form>div>#edit-submit,#user-register>div>#edit-submit");

  // insert the saving div now to cache it for better performance and to show the loading image
  $('<div id="saving"><p class="saving">Saving&hellip;</p></div>').insertAfter(forms);

  forms.click(function() {
    $(this).siblings("input:submit").hide();
    $(this).hide();
    $("#saving").show();

    var notice = function() {
      $('<p id="saving-notice">Not saving? Wait a few seconds, reload this page, and try again. Every now and then the internet hiccups too :-)</p>').appendTo("#saving").fadeIn();
    };

    // append notice if form saving isn't work, perhaps a timeout issue
    setTimeout(notice, 24000);
  });
};

$(document).ready(Blueprint.formCheck);

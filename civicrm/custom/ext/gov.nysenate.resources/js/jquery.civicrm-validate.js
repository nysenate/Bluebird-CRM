/**
 *  Additional methods to extend jQuery Validation Plugin 1.8.0
 *  You can add validation methods here based on classes prefixed with crm_
 *  Example: class='crm_phone' is assigned to phone number fields
 *  To define phone validation for your site:
 *  jQuery.validator.addMethod("crm_phone", function(phone_number, element) { validation logic here }
 */

/* US phone and US postal code validations */
(function() {
  /*    jQuery.validator.addMethod("crm_phone", function(phone_number, element) {
   phone_number = phone_number.replace(/\s+/g, "");
   return this.optional(element) || phone_number.length > 9 &&
   phone_number.match(/^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
   }, "Enter a valid phone number (10 digits, dashes or parentheses optional).");
   */
  jQuery.validator.addMethod("crm_postal_code", function(postalcode, element) {
    return this.optional(element) || postalcode.match(/(^\d{5}(-\d{4})?$)|(^[ABCEGHJKLMNPRSTVXYabceghjklmnpstvxy]{1}\d{1}[A-Za-z]{1} ?\d{1}[A-Za-z]{1}\d{1})$/);
  }, "Enter a valid 5 digit zip code.");

})();

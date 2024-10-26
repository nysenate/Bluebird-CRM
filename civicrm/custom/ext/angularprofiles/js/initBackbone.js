// CiviCRM loads Backbone very conservatively/inconsistently. We load it here
// so that we can make the Profiles widget available in Angular contexts where
// core hasn't already made Backbone available through the CRM object, but do so
// conditionally in case core starts providing it more consistently.
if (!CRM.hasOwnProperty('BB')) {
  CRM.BB = Backbone.noConflict();
}
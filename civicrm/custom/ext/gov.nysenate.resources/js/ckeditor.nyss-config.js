/**
 * NYSS CKEditor 4 config.
 *
 * Loaded ahead of the ckeditor4 extension's per-preset config file (see
 * resources_civicrm_alterResourceSettings), then chains to that file via
 * config.customConfig so admin-configured settings still apply.
 * NYSS #19204, #5353, #6419
 */
CKEDITOR.editorConfig = function(config) {

  // Use the browser's built-in spellchecker instead, which runs locally.
  config.disableNativeSpellChecker = false;

  // Remove SCAYT after all config files have loaded, so a preset that sets its
  // own removePlugins can't bring it back.
  this.on('configLoaded', function(evt) {
    var cfg = evt.editor.config,
      plugins = cfg.removePlugins ? cfg.removePlugins.split(',') : [];
    if (plugins.indexOf('scayt') < 0) {
      plugins.push('scayt');
    }
    cfg.removePlugins = plugins.join(',');
  });

  // Chain to the config file the ckeditor4 extension would have loaded.
  var next = CRM.config.nyssCKEditorNextConfig || {},
    preset = this.element.data('preset') || 'default';
  config.customConfig = next[preset] || next['default'] || '';
};

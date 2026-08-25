(function(angular, $, _) {
  // Declare a list of dependencies.
  angular.module('crmNyssCaseloadDash', CRM.angRequires('crmNyssCaseloadDash'));

    // brute-force the title of the Case Manager pop-up dialog on the Caseload dashboard
    // force it to overwrite the default Afform title.
    $(document).on('crmPopupOpen', function(e, dialog) {
        // Extract popup_title from the URL hash (embedded by the rewrite)
        var hash = ($(e.target).attr('href') || '').split('#')[1] || '';
        var customTitle = new URLSearchParams(hash.replace(/^\?/, '')).get('popup_title');
        if (!customTitle) return;

        var $titleSpan;

        var titleObserver = new MutationObserver(function() {
            // crmPageTitle directive just overwrote our title — restore it
            if ($(dialog).dialog('option', 'title') !== customTitle) {
                titleObserver.disconnect();
                $(dialog).dialog('option', 'title', customTitle);
                // Resume watching for subsequent addTitle() calls
                titleObserver.observe($titleSpan[0], {childList: true, subtree: true, characterData:
                        true});
            }
        });

        dialog.on('crmLoad', function() {
            $(this).parent().find('a.crm-dialog-titlebar-print').hide(); // print isn't working right now. Likely bug in Civi Core. So, hide it.
            $(this).dialog('option', 'title', customTitle);
            $titleSpan = $(this).parent().find('.ui-dialog-title');
            if ($titleSpan.length) {
                titleObserver.observe($titleSpan[0], {childList: true, subtree: true, characterData:
                        true});
            }
        });

        dialog.one('dialogclose', function() {
            titleObserver.disconnect();
        });
    });
})(angular, CRM.$, CRM._);

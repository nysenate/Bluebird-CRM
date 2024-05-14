(function(angular, $, _) {
  angular.module('crmMosaico').service('crmMosaicoVariants', function() {
    // This utility helps you manipulate the `variants` on a mailing.

    function angularObj(obj) {
      return JSON.parse(angular.toJson(obj));
    }

    const mainPlaceholders = {subject: 'VARIANT SUBJECTS', body_html: 'VARIANT HTMLS', mosaicoTemplate: "", mosaicoMetadata: '{}', mosaicoContent: '{}'};

    const self = {
      getLabels: () => ['A', 'B'],

      // Enable variations for a particular field. (By default, old value is copied to A+B. But you can optionally override B.)
      // Ex: crmMosaicoVariants.split(mymailing, 'subject')
      // Ex: crmMosaicoVariants.split(mymailing, ['body_html', 'mosaicoContent'])
      // Ex: crmMosaicoVariants.split(mymailing, 'subject', '')
      // Ex: crmMosaicoVariants.split(mymailing, ['body_html', 'mosaicoContent'], {body_html: '', mosaicoContent: null})
      split: function split(mailing, field, bValue) {
        if (_.isArray(field)) {
          bValue = bValue || {};
          return angular.forEach(field, (f) => self.split(mailing, f, bValue[field]));
        }

        mailing.template_options.variants = mailing.template_options.variants || [];

        const mainObj = field.match(/^mosaico/) ? mailing.template_options : mailing;
        mailing.template_options.variants[0] = mailing.template_options.variants[0] || {};

        mailing.template_options.variants[1] = mailing.template_options.variants[1] || {};
        mailing.template_options.variants[0][field] = mainObj[field];
        mailing.template_options.variants[1][field] = (bValue === undefined ? mainObj[field] : bValue);

        if (mainPlaceholders[field] !== undefined) {
          mainObj[field] = mainPlaceholders[field];
        }
      },

      // Disable variations for a particular field. Remove a particular record.
      // Ex: crmMosaicoVariants.remove(mymailing, 'subject', 0)
      // Ex: crmMosaicoVariants.remove(mymailing, ['body_html', 'mosaicoContent'], 1)
      remove: function remove(mailing, field, deleteVid) {
        if (_.isArray(field)) {
          return angular.forEach(field, (f) => self.remove(mailing, f, deleteVid));
        }

        const mainObj = field.match(/^mosaico/) ? mailing.template_options : mailing;
        const deadVariant = mailing.template_options.variants[deleteVid];
        const liveVariant = mailing.template_options.variants[deleteVid ? 0 : 1];

        mainObj[field] = liveVariant[field];
        delete liveVariant[field];
        delete deadVariant[field];

        // If the variants are empty, then delete the variant objects
        const nonEmpties = _.filter(mailing.template_options.variants, (v) => !_.isEmpty(angularObj(v)));
        if (nonEmpties.length === 0) {
          delete mailing.template_options.variants;
        }
      },

      // Create a new, flat `mailing` record which includes overrides for a specific variant.
      preview: function preview(mailing, vid) {
        const preview = angular.copy(mailing, {}, 5);
        if (vid !== null && vid !== undefined) {
          angular.extend(preview, mailing.template_options.variants[vid]);
        }
        delete preview.id;
        delete preview.template_options.variants;
        delete preview.mosaicoTemplate;
        delete preview.mosaicoMetadata;
        delete preview.mosaicoContent;
        return preview;
      },

      // isSplit(mailing): Determine if there are -any- split/variant fields.
      // isSplit(mailing, field): Determine if a -specific- field has variations.
      isSplit: function isSplit(mailing, field) {
        if (!mailing.template_options || !mailing.template_options.variants) {
          return false;
        }
        if (field) {
          return (field in mailing.template_options.variants[0]);
        }
        const nonEmpties = _.filter(mailing.template_options.variants, (v) => !_.isEmpty(angularObj(v)));
        return nonEmpties.length > 0;
      }
    };
    return self;
  });
})(angular, CRM.$, CRM._);

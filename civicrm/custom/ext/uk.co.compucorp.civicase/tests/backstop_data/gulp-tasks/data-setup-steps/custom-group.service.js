const createUniqueRecordFactory = require('../utils/create-unique-record-factory.js');
const createUniqueCustomGroup = createUniqueRecordFactory('CustomGroup', ['title']);

const service = {
  setupData,
  inline: {
    id: null,
    extends: 'Case',
    fieldLabel: 'Backstop Case Inline Custom Field',
    groupTitle: 'Backstop Case Inline Custom Group'
  },
  tab: {
    id: null,
    extends: 'Case',
    fieldLabel: 'Backstop Case Tab Custom Field',
    groupTitle: 'Backstop Case Tab Custom Group'
  },
  awardReviewField: {
    id: 'Applicant_Review',
    fieldLabel: 'Backstop Review Field'
  },
  awardCustomField: {
    id: null,
    extends: 'applicant_managementType',
    groupTitle: 'Backstop Award Custom Group',
    fieldLabel: 'Backstop Award Custom Field'
  }
};

/**
 * Create Custom Groups
 */
function setupData () {
  service.inline.id = createCustomGroup('Inline', service.inline.groupTitle, service.inline.extends).id;
  service.tab.id = createCustomGroup('Tab', service.tab.groupTitle, service.tab.extends).id;
  service.awardCustomField.id = createCustomGroup('Inline', service.awardCustomField.groupTitle, service.awardCustomField.extends).id;

  console.log('Custom Group data setup successful.');
}

/**
 * Create Custom Groups
 *
 * @param {string} style style
 * @param {string} title title
 * @param {string} extendsVal extends value
 * @returns {object} custom group
 */
function createCustomGroup (style, title, extendsVal) {
  return createUniqueCustomGroup({
    extends: extendsVal,
    style: style,
    title: title
  });
}

module.exports = service;

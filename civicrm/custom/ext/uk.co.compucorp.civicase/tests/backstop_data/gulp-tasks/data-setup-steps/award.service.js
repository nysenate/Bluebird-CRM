const createUniqueRecordFactory = require('../utils/create-unique-record-factory.js');
const tagService = require('./tag.service.js');
const relationshipTypeService = require('./relationship-type.service.js');
const customFieldService = require('./custom-field.service.js');
const contactService = require('./contact.service.js');
const createUniqueAward = createUniqueRecordFactory('CaseType', ['name']);
const createUniqueAwardDetail = createUniqueRecordFactory('AwardDetail', ['case_type_id']);
const createUniqueAwardReviewPanel = createUniqueRecordFactory('AwardReviewPanel', ['title']);

const service = {
  setupData,
  award: null,
  awardName: 'backstop_award',
  awardTitle: 'Backstop Award'
};

/**
 * Create Award
 */
function setupData () {
  service.award = createUniqueAward({
    name: service.awardName,
    case_type_category: 'Awards',
    title: service.awardTitle,
    definition: {
      activityTypes: [{
        name: 'Applicant Review'
      }, {
        name: 'Email'
      }, {
        name: 'Follow up'
      }, {
        name: 'Meeting'
      }, {
        name: 'Phone Call'
      }],
      activitySets: [],
      caseRoles: [
        {
          name: 'Application Manager',
          manager: '0'
        }
      ],
      statuses: ['Open', 'Closed', 'Urgent'],
      timelineActivityTypes: []
    }
  });

  createUniqueAwardDetail({
    award_manager: [contactService.adminUserID],
    case_type_id: service.award.id,
    start_date: '2021-03-01',
    end_date: '2021-03-31',
    award_subtype: '2',
    review_fields: [{
      id: customFieldService.awardReviewFieldID,
      required: '0',
      weight: 1
    }],
    is_template: false
  });

  createUniqueAwardReviewPanel({
    title: 'Backstop Panel 1',
    is_active: '1',
    case_type_id: service.award.id,
    contact_settings: {
      exclude_groups: ['2'],
      include_groups: ['1'],
      relationship: [{
        is_a_to_b: '1',
        relationship_type_id: relationshipTypeService.benefitsSpecialistRelType.id,
        contact_id: ['2']
      }]
    },
    visibility_settings: {
      application_status: ['1', '2'],
      anonymize_application: '1',
      application_tags: [tagService.caseTagID],
      is_application_status_restricted: '0',
      restricted_application_status: []
    }
  });

  console.log('Awards data setup successful.');
}

module.exports = service;

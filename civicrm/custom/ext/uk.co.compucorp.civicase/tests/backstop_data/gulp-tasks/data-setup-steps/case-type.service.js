const createUniqueRecordFactory = require('../utils/create-unique-record-factory.js');
const relationshipTypesService = require('./relationship-type.service.js');
const createUniqueCaseType = createUniqueRecordFactory('CaseType', ['name']);

const service = {
  setupData,
  caseTypeName: 'backstop_case_type',
  caseTypeTitle: 'Backstop Case Type'
};

/**
 * Create Case Type
 */
function setupData () {
  service.caseType = createUniqueCaseType({
    name: service.caseTypeName,
    case_type_category: 'Cases',
    title: service.caseTypeTitle,
    definition: {
      activityTypes: [{
        name: 'Open Case',
        max_instances: '1'
      }, {
        name: 'Follow up'
      }, {
        name: 'File Upload'
      }],
      activitySets: [],
      caseRoles: [
        {
          name: relationshipTypesService.relationshipTypeNames.homelessCoordinator,
          creator: '1',
          manager: '0'
        }, {
          name: relationshipTypesService.relationshipTypeNames.healthServiceCoordinator,
          manager: '0'
        }, {
          name: relationshipTypesService.relationshipTypeNames.benefitsSpecialist,
          manager: '1'
        }
      ],
      timelineActivityTypes: []
    }
  });
}

module.exports = service;

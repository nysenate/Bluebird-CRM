const cvApi = require('../utils/cv-api.js');

const service = {
  setupData
};

/**
 * Enable Finance Management for Awards
 */
function setupData () {
  enableFinanceManagementFor();
}

/**
 * Enable Civicase Component
 * Required for scenarios in 'civicase.json'
 */
function enableFinanceManagementFor () {
  cvApi('FinanceManagement', 'setsetting', {
    case_type_category_id: 2,
    value: 1
  });

  console.log('Finance Management enabled for awards.');
}

module.exports = service;

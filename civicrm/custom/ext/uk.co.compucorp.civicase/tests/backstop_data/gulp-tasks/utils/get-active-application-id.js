const cvApi = require('./cv-api.js');

module.exports = getActiveApplicationId;

/**
 * Returns the ID of a application that is active and has an activity for the current
 * calendar month.
 *
 * @returns {number} case id of an active case
 */
function getActiveApplicationId () {
  var activity = cvApi('Activity', 'get', {
    sequential: 1,
    'case_id.is_deleted': 0,
    'case_id.status_id': 'Approved',
    activity_type_id: 'Awards Payment',
    case_filter: { 'case_type_id.case_type_category': 'awards' },
    return: ['case_id'],
    options: { limit: 1 }
  });

  if (!activity.count) {
    throw new Error('Please add an activity for a application with "Awards Payment" type and "Approved" status');
  }

  return activity.count && activity.values[0].case_id[0];
}

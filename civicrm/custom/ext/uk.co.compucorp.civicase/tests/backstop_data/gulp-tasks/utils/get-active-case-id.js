var moment = require('moment');
const cvApi = require('./cv-api.js');

module.exports = getActiveCaseId;

/**
 * Returns the ID of a case that is active and has an activity for the current
 * calendar month.
 *
 * @returns {number} case id of an active case
 */
function getActiveCaseId () {
  var startDate = moment().startOf('month').format('YYYY-MM-DD');
  var endDate = moment().endOf('month').format('YYYY-MM-DD');
  var activity = cvApi('Activity', 'get', {
    sequential: 1,
    activity_date_time: { BETWEEN: [startDate, endDate] },
    'case_id.is_deleted': 0,
    'case_id.status_id': 'Scheduled',
    case_filter: { 'case_type_id.case_type_category': 'cases' },
    return: ['case_id'],
    options: { limit: 1 }
  });

  if (!activity.count) {
    throw new Error('Please add an activity for the current month and for a case with a "Scheduled" status');
  }

  return activity.count && activity.values[0].case_id[0];
}

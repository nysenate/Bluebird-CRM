(function () {
  var module = angular.module('civicase.data');

  CRM['civicase-base'].activityTypes = {
    1: {
      label: 'Meeting',
      icon: 'fa-slideshare',
      name: 'Meeting',
      grouping: 'communication',
      is_active: '1'
    },
    2: {
      label: 'Phone Call',
      icon: 'fa-phone',
      name: 'Phone Call',
      grouping: 'communication',
      is_active: '1'
    },
    3: {
      label: 'Email',
      icon: 'fa-envelope-o',
      name: 'Email',
      grouping: 'communication',
      is_active: '1'
    },
    4: {
      label: 'Outbound SMS',
      icon: 'fa-mobile',
      name: 'SMS',
      grouping: 'communication',
      is_active: '1'
    },
    5: {
      label: 'Event Registration',
      name: 'Event Registration',
      is_active: '1'
    },
    6: {
      label: 'Contribution',
      name: 'Contribution',
      is_active: '1'
    },
    7: {
      label: 'Membership Signup',
      name: 'Membership Signup',
      is_active: '1'
    },
    8: {
      label: 'Membership Renewal',
      name: 'Membership Renewal',
      is_active: '1'
    },
    9: {
      label: 'Tell a Friend',
      name: 'Tell a Friend',
      is_active: '1'
    },
    10: {
      label: 'Pledge Acknowledgment',
      name: 'Pledge Acknowledgment',
      is_active: '1'
    },
    11: {
      label: 'Pledge Reminder',
      name: 'Pledge Reminder',
      is_active: '1'
    },
    12: {
      label: 'Inbound Email',
      name: 'Inbound Email',
      grouping: 'communication',
      is_active: '1'
    },
    13: {
      label: 'Open Case',
      icon: 'fa-folder-open-o',
      name: 'Open Case',
      grouping: 'milestone',
      is_active: '1'
    },
    14: {
      label: 'Follow up',
      icon: 'fa-share-square-o',
      name: 'Follow up',
      grouping: 'communication',
      is_active: '1'
    },
    15: {
      label: 'Change Case Type',
      icon: 'fa-random',
      name: 'Change Case Type',
      grouping: 'system',
      is_active: '1'
    },
    16: {
      label: 'Change Case Status',
      icon: 'fa-pencil-square-o',
      name: 'Change Case Status',
      grouping: 'system',
      is_active: '1'
    },
    17: {
      label: 'Membership Renewal Reminder',
      name: 'Membership Renewal Reminder',
      is_active: '1'
    },
    18: {
      label: 'Change Case Start Date',
      icon: 'fa-calendar',
      name: 'Change Case Start Date',
      grouping: 'system',
      is_active: '1'
    },
    19: {
      label: 'Bulk Email',
      name: 'Bulk Email',
      is_active: '1'
    },
    20: {
      label: 'Assign Case Role',
      icon: 'fa-user-plus',
      name: 'Assign Case Role',
      grouping: 'system',
      is_active: '1'
    },
    21: {
      label: 'Remove Case Role',
      icon: 'fa-user-times',
      name: 'Remove Case Role',
      grouping: 'system',
      is_active: '1'
    },
    22: {
      label: 'Print/Merge Document',
      icon: 'fa-file-pdf-o',
      name: 'Print PDF Letter',
      grouping: 'communication',
      is_active: '1'
    },
    23: {
      label: 'Merge Case',
      icon: 'fa-compress',
      name: 'Merge Case',
      grouping: 'system',
      is_active: '1'
    },
    24: {
      label: 'Reassigned Case',
      icon: 'fa-user-circle-o',
      name: 'Reassigned Case',
      grouping: 'system',
      is_active: '1'
    },
    25: {
      label: 'Link Cases',
      icon: 'fa-link',
      name: 'Link Cases',
      grouping: 'system',
      is_active: '1'
    },
    26: {
      label: 'Change Case Tags',
      icon: 'fa-tags',
      name: 'Change Case Tags',
      grouping: 'system',
      is_active: '1'
    },
    27: {
      label: 'Add Client To Case',
      icon: 'fa-users',
      name: 'Add Client To Case',
      grouping: 'system',
      is_active: '1'
    },
    28: {
      label: 'Survey',
      name: 'Survey',
      is_active: '1'
    },
    29: {
      label: 'Canvass',
      name: 'Canvass',
      is_active: '1'
    },
    30: {
      label: 'PhoneBank',
      name: 'PhoneBank',
      is_active: '1'
    },
    31: {
      label: 'WalkList',
      name: 'WalkList',
      is_active: '1'
    },
    32: {
      label: 'Petition Signature',
      name: 'Petition',
      is_active: '1'
    },
    33: {
      label: 'Change Custom Data',
      icon: 'fa-table',
      name: 'Change Custom Data',
      grouping: 'system',
      is_active: '1'
    },
    34: {
      label: 'Mass SMS',
      name: 'Mass SMS',
      is_active: '1'
    },
    35: {
      label: 'Change Membership Status',
      name: 'Change Membership Status',
      is_active: '1'
    },
    36: {
      label: 'Change Membership Type',
      name: 'Change Membership Type',
      is_active: '1'
    },
    37: {
      label: 'Cancel Recurring Contribution',
      name: 'Cancel Recurring Contribution',
      is_active: '1'
    },
    38: {
      label: 'Update Recurring Contribution Billing Details',
      name: 'Update Recurring Contribution Billing Details',
      is_active: '1'
    },
    39: {
      label: 'Update Recurring Contribution',
      name: 'Update Recurring Contribution',
      is_active: '1'
    },
    40: {
      label: 'Reminder Sent',
      name: 'Reminder Sent',
      is_active: '1'
    },
    41: {
      label: 'Export Accounting Batch',
      name: 'Export Accounting Batch',
      is_active: '1'
    },
    42: {
      label: 'Create Batch',
      name: 'Create Batch',
      is_active: '1'
    },
    43: {
      label: 'Edit Batch',
      name: 'Edit Batch',
      is_active: '1'
    },
    44: {
      label: 'SMS delivery',
      name: 'SMS delivery',
      is_active: '1'
    },
    45: {
      label: 'Inbound SMS',
      name: 'Inbound SMS',
      is_active: '1'
    },
    46: {
      label: 'Payment',
      name: 'Payment',
      is_active: '1'
    },
    47: {
      label: 'Refund',
      name: 'Refund',
      is_active: '1'
    },
    48: {
      label: 'Change Registration',
      name: 'Change Registration',
      is_active: '1'
    },
    49: {
      label: 'Downloaded Invoice',
      name: 'Downloaded Invoice',
      is_active: '1'
    },
    50: {
      label: 'Emailed Invoice',
      name: 'Emailed Invoice',
      is_active: '1'
    },
    51: {
      label: 'Contact Merged',
      name: 'Contact Merged',
      is_active: '1'
    },
    52: {
      label: 'Contact Deleted by Merge',
      name: 'Contact Deleted by Merge',
      is_active: '1'
    },
    53: {
      label: 'Change Case Subject',
      icon: 'fa-pencil-square-o',
      name: 'Change Case Subject',
      grouping: 'system',
      is_active: '1'
    },
    54: {
      label: 'Failed Payment',
      name: 'Failed Payment',
      is_active: '1'
    },
    55: {
      label: 'Interview',
      icon: 'fa-comment-o',
      name: 'Interview',
      is_active: '1'
    },
    56: {
      label: 'Medical evaluation',
      name: 'Medical evaluation',
      grouping: 'milestone',
      is_active: '1'
    },
    58: {
      label: 'Mental health evaluation',
      name: 'Mental health evaluation',
      grouping: 'milestone',
      is_active: '1'
    },
    60: {
      label: 'Secure temporary housing',
      name: 'Secure temporary housing',
      grouping: 'milestone',
      is_active: '1'
    },
    62: {
      label: 'Income and benefits stabilization',
      name: 'Income and benefits stabilization',
      is_active: '1'
    },
    64: {
      label: 'Long-term housing plan',
      name: 'Long-term housing plan',
      grouping: 'milestone',
      is_active: '1'
    },
    66: {
      label: 'ADC referral',
      name: 'ADC referral',
      is_active: '1'
    },
    68: {
      label: 'File Upload',
      icon: 'fa-file',
      name: 'File Upload',
      is_active: '1'
    },
    69: {
      label: 'Remove Client From Case',
      icon: 'fa-user-times',
      name: 'Remove Client From Case',
      grouping: 'system',
      is_active: '1'
    },
    70: {
      label: 'Case Task',
      name: 'Case Task',
      grouping: 'task',
      is_active: '1'
    },
    72: {
      label: 'Communication Act',
      name: 'Communication Act',
      grouping: 'communication',
      is_active: '1'
    },
    73: {
      label: 'Alert',
      icon: 'fa-exclamation',
      name: 'Alert',
      grouping: 'alert',
      is_active: '0'
    }
  };

  module.constant('ActivityTypesData', {
    values: CRM['civicase-base'].activityTypes
  });
}());

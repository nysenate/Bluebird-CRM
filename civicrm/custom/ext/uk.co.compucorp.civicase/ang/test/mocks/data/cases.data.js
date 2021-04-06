((angular, _) => {
  const module = angular.module('civicase.data');

  module.service('CasesData', function () {
    const casesMockData = {
      values: [
        {
          id: '141',
          subject: 'This case is in reference to Ashlie Bachman-Wattson.',
          case_type_id: '1',
          status_id: '3',
          is_deleted: false,
          start_date: '2017-11-11',
          modified_date: '2018-08-16 07:48:18',
          contacts: [
            {
              contact_id: '170',
              sort_name: 'Adams, Kiara',
              display_name: 'Kiara Adams',
              email: 'adams.kiara@airmail.co.nz',
              phone: '(781) 205-2601',
              birth_date: '1980-10-09',
              role: 'Client'
            },
            {
              contact_id: '202',
              display_name: 'admin@example.com',
              sort_name: 'admin@example.com',
              relationship_type_id: '11',
              role: 'Homeless Services Coordinator',
              email: 'admin@example.com',
              phone: null,
              creator: '1',
              manager: '1'
            }
          ],
          activity_summary: {
            task: [
              {
                id: '1777',
                activity_type_id: '70',
                activity_date_time: '2017-11-20 00:00:00',
                status_id: '1',
                is_star: '0',
                case_id: '141',
                is_overdue: true,
                source_contact_id: '202',
                target_contact_id: [
                  '170'
                ],
                target_contact_name: {
                  170: 'Kiara Adams'
                },
                assignee_contact_id: [],
                category: [
                  'task'
                ],
                type: 'Case Task',
                status: 'Scheduled',
                status_name: 'Scheduled',
                status_type: 'incomplete',
                is_completed: false,
                color: '#42afcb',
                status_css: 'status-type-incomplete activity-status-scheduled'
              }
            ],
            file: [],
            communication: [
              {
                id: '1773',
                activity_type_id: '14',
                activity_date_time: '2017-11-14 00:00:00',
                status_id: '1',
                is_star: '0',
                case_id: '141',
                is_overdue: true,
                source_contact_id: '202',
                target_contact_id: [
                  '170'
                ],
                target_contact_name: {
                  170: 'Kiara Adams'
                },
                assignee_contact_id: [],
                category: [
                  'communication'
                ],
                icon: 'fa-share-square-o',
                type: 'Follow up',
                status: 'Scheduled',
                status_name: 'Scheduled',
                status_type: 'incomplete',
                is_completed: false,
                color: '#42afcb',
                status_css: 'status-type-incomplete activity-status-scheduled'
              }
            ],
            milestone: [
              {
                id: '1770',
                activity_type_id: '56',
                activity_date_time: '2017-11-12 00:00:00',
                status_id: '1',
                is_star: '0',
                case_id: '141',
                is_overdue: true,
                source_contact_id: '202',
                target_contact_id: [
                  '170'
                ],
                target_contact_name: {
                  170: 'Kiara Adams'
                },
                assignee_contact_id: [],
                category: [
                  'milestone'
                ],
                type: 'Medical evaluation',
                status: 'Scheduled',
                status_name: 'Scheduled',
                status_type: 'incomplete',
                is_completed: false,
                color: '#42afcb',
                status_css: 'status-type-incomplete activity-status-scheduled'
              }
            ],
            alert: [],
            system: [
              {
                id: '1779',
                activity_type_id: '25',
                activity_date_time: '2018-08-16 07:47:00',
                status_id: '1',
                is_star: '0',
                case_id: '141',
                is_overdue: true,
                source_contact_id: '202',
                target_contact_id: [
                  '170'
                ],
                target_contact_name: {
                  170: 'Kiara Adams'
                },
                assignee_contact_id: [],
                category: [
                  'system'
                ],
                icon: 'fa-link',
                type: 'Link Cases',
                status: 'Scheduled',
                status_name: 'Scheduled',
                status_type: 'incomplete',
                is_completed: false,
                color: '#42afcb',
                status_css: 'status-type-incomplete activity-status-scheduled'
              }
            ],
            next: [
              {
                id: '1770',
                activity_type_id: '56',
                activity_date_time: '2017-11-12 00:00:00',
                status_id: '1',
                is_star: '0',
                case_id: '141',
                is_overdue: true,
                source_contact_id: '202',
                target_contact_id: [
                  '170'
                ],
                target_contact_name: {
                  170: 'Kiara Adams'
                },
                assignee_contact_id: [],
                category: [
                  'milestone'
                ],
                type: 'Medical evaluation',
                status: 'Scheduled',
                status_name: 'Scheduled',
                status_type: 'incomplete',
                is_completed: false,
                color: '#42afcb',
                status_css: 'status-type-incomplete activity-status-scheduled'
              }
            ]
          },
          activity_count: {
            13: '1',
            15: '4',
            16: '1',
            53: '1'
          },
          'api.Case.getcaselist.relatedCasesByContact': { values: [] },
          'api.Case.getcaselist.linkedCases': { values: [] },
          'api.Relationship.get': { values: [] },
          'api.Activity.getAll.1': {
            is_error: 0,
            version: 3,
            count: 18,
            values: [
              {
                id: '1770',
                activity_type_id: '56',
                activity_date_time: '2017-11-12 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1771',
                activity_type_id: '58',
                activity_date_time: '2017-11-12 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1772',
                activity_type_id: '60',
                activity_date_time: '2017-11-13 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1773',
                activity_type_id: '14',
                activity_date_time: '2017-11-14 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1774',
                activity_type_id: '62',
                activity_date_time: '2017-11-18 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1775',
                activity_type_id: '64',
                activity_date_time: '2017-11-25 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1776',
                activity_type_id: '14',
                activity_date_time: '2017-12-02 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1777',
                activity_type_id: '70',
                activity_date_time: '2017-11-20 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1778',
                activity_type_id: '70',
                activity_date_time: '2017-11-21 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1779',
                activity_type_id: '25',
                activity_date_time: '2018-08-16 07:47:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                medium_id: '2',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-16 07:47:23',
                source_contact_id: '202'
              },
              {
                id: '1780',
                activity_type_id: '23',
                subject: 'Case 81 copied from contact id 122 to contact id 170 via merge. New Case ID is 141.',
                activity_date_time: '2018-08-16 07:47:43',
                status_id: '2',
                priority_id: '2',
                is_test: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:43',
                modified_date: '2018-08-16 07:47:43',
                source_contact_id: '202'
              },
              {
                id: '1782',
                activity_type_id: '16',
                subject: 'Case status changed from Ongoing to Urgent',
                activity_date_time: '2018-08-16 07:48:18',
                status_id: '2',
                priority_id: '2',
                is_test: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:48:18',
                modified_date: '2018-08-16 07:48:18',
                source_contact_id: '202'
              },
              {
                id: '1791',
                activity_type_id: '56',
                subject: 'Overdue task',
                activity_date_time: '2018-09-04 12:50:00',
                duration: '20',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                medium_id: '2',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 12:51:46',
                modified_date: '2018-09-06 12:51:46',
                source_contact_id: '202'
              },
              {
                id: '1792',
                source_record_id: '1791',
                activity_type_id: '3',
                subject: 'Overdue task - copy sent to admin@example.com',
                activity_date_time: '2018-09-06 12:51:46',
                details: '===========================================================\nActivity Summary - Medical evaluation\n===========================================================\nYour Case Role(s) : Homeless Services Coordinator\nManage Case : http://civicase.local/civicrm/contact/view/case?reset=1&amp;id=141&amp;cid=170&amp;action=view&amp;context=home\n\nEdit activity : http://civicase.local/civicrm/case/activity?reset=1&amp;cid=170&amp;caseid=141&amp;action=update&amp;id=1791\nView activity : http://civicase.local/civicrm/case/activity/view?reset=1&amp;aid=1791&amp;cid=170&amp;caseID=141\n\nClient : Kiara Adams\nActivity Type : Medical evaluation\nSubject : Overdue task\nCreated By : admin@example.com\nReported By : admin@example.com\nMedium : Phone\nLocation : \nDate and Time : September 4th, 2018 12:50 PM\nDetails : \nDuration : 20 minutes\nStatus : Scheduled\nPriority : \nCase ID : 141\n\n',
                status_id: '2',
                priority_id: '2',
                is_test: '0',
                medium_id: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 12:51:47',
                modified_date: '2018-09-06 12:51:47',
                source_contact_id: '202'
              },
              {
                id: '1793',
                activity_type_id: '70',
                subject: 'Some overdue task',
                activity_date_time: '2018-09-03 12:53:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                medium_id: '2',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 12:54:46',
                modified_date: '2018-09-06 12:54:46',
                source_contact_id: '202'
              },
              {
                id: '1794',
                activity_type_id: '3',
                subject: 'TO be happneing in future',
                activity_date_time: '2018-09-06 14:29:13',
                details: '<p>Some conv</p>\n',
                status_id: '2',
                priority_id: '2',
                is_test: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 14:29:13',
                modified_date: '2018-09-06 14:29:13',
                source_contact_id: '202'
              },
              {
                id: '1797',
                activity_type_id: '70',
                subject: 'TO be happening in future',
                activity_date_time: '2025-09-18 14:29:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                medium_id: '2',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 14:31:12',
                modified_date: '2018-09-06 14:31:12',
                source_contact_id: '202'
              },
              {
                id: '1798',
                activity_type_id: '70',
                subject: 'Some subject',
                activity_date_time: '2018-09-29 00:00:00',
                status_id: '1',
                priority_id: '2',
                parent_id: '1797',
                is_test: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 14:31:12',
                modified_date: '2018-09-06 14:31:12',
                source_contact_id: '202'
              }
            ]
          },
          'api.Activity.getcount.scheduled': 2,
          'api.Activity.getcount.scheduled_overdue': 3,
          'api.Activity.getAll.recentCommunication': { values: [] },
          'api.Activity.getAll.tasks': { values: [] },
          'api.Activity.getAll.nextActivitiesWhichIsNotMileStone': {
            values: [
              {
                id: '1009',
                activity_type_id: '14',
                subject: 'Some random subject is changed',
                activity_date_time: '2018-01-20 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-06 14:14:56',
                modified_date: '2018-10-01 10:24:04',
                source_contact_id: '202'
              }
            ]
          },
          'api.CustomValue.getalltreevalues': { values: [] },
          category_count: {
            incomplete: {
              task: 2,
              communication: 2,
              milestone: 4,
              system: 1
            },
            completed: {
              system: 2
            }
          },
          manager: {
            contact_id: '202',
            display_name: 'admin@example.com',
            sort_name: 'admin@example.com',
            relationship_type_id: '11',
            role: 'Homeless Services Coordinator',
            email: 'admin@example.com',
            phone: null,
            creator: '1',
            manager: '1'
          },
          myRole: [],
          next_activity: {
            id: '1770',
            activity_type_id: '56',
            activity_date_time: '2017-11-12 00:00:00',
            status_id: '1',
            is_star: '0',
            case_id: '141',
            is_overdue: true,
            source_contact_id: '202',
            target_contact_id: [
              '170'
            ],
            target_contact_name: {
              170: 'Kiara Adams'
            },
            assignee_contact_id: [],
            category: [
              'milestone'
            ],
            type: 'Medical evaluation',
            status: 'Scheduled',
            status_name: 'Scheduled',
            status_type: 'incomplete',
            is_completed: false,
            color: '#42afcb',
            status_css: 'status-type-incomplete activity-status-scheduled'
          },
          client: [
            {
              contact_id: '170',
              sort_name: 'Adams, Kiara',
              display_name: 'Kiara Adams',
              email: 'adams.kiara@airmail.co.nz',
              phone: '(781) 205-2601',
              birth_date: '1980-10-09',
              role: 'Client'
            }
          ],
          status: 'Urgent',
          color: '#e6807f',
          case_type: 'Housing Support',
          selected: false
        },
        {
          id: '3',
          subject: 'This case is in reference to Shauna Barkley.',
          case_type_id: '1',
          status_id: '1',
          is_deleted: false,
          start_date: '2018-05-11',
          modified_date: '2018-08-06 14:14:54',
          contacts: [
            {
              contact_id: '4',
              sort_name: 'Barkley, Shauna',
              display_name: 'Shauna Barkley',
              email: 'barkley.shauna@notmail.org',
              phone: null,
              birth_date: '2004-05-15',
              role: 'Client'
            },
            {
              contact_id: '202',
              display_name: 'admin@example.com',
              sort_name: 'admin@example.com',
              relationship_type_id: '11',
              role: 'Homeless Services Coordinator',
              email: 'admin@example.com',
              phone: null,
              creator: '1',
              manager: '1'
            }
          ],
          tag_id: {
            10: {
              tag_id: '10',
              'tag_id.name': 'Orange',
              'tag_id.color': '#ff9d2a',
              'tag_id.description': 'Orange you glad this isn\'t a pun?'
            }
          },
          activity_summary: {
            task: [
              {
                id: '649',
                activity_type_id: '70',
                activity_date_time: '2018-05-20 00:00:00',
                status_id: '1',
                is_star: '0',
                case_id: '3',
                is_overdue: true,
                source_contact_id: '202',
                target_contact_id: [
                  '4'
                ],
                target_contact_name: {
                  4: 'Shauna Barkley'
                },
                assignee_contact_id: [],
                category: [
                  'task'
                ],
                type: 'Case Task',
                status: 'Scheduled',
                status_name: 'Scheduled',
                status_type: 'incomplete',
                is_completed: false,
                color: '#42afcb',
                status_css: 'status-type-incomplete activity-status-scheduled'
              }
            ],
            file: [],
            communication: [
              {
                id: '645',
                activity_type_id: '14',
                activity_date_time: '2018-05-14 00:00:00',
                status_id: '1',
                is_star: '0',
                case_id: '3',
                is_overdue: true,
                source_contact_id: '202',
                target_contact_id: [
                  '4'
                ],
                target_contact_name: {
                  4: 'Shauna Barkley'
                },
                assignee_contact_id: [],
                category: [
                  'communication'
                ],
                icon: 'fa-share-square-o',
                type: 'Follow up',
                status: 'Scheduled',
                status_name: 'Scheduled',
                status_type: 'incomplete',
                is_completed: false,
                color: '#42afcb',
                status_css: 'status-type-incomplete activity-status-scheduled'
              }
            ],
            milestone: [
              {
                id: '642',
                activity_type_id: '56',
                activity_date_time: '2018-05-12 00:00:00',
                status_id: '1',
                is_star: '0',
                case_id: '3',
                is_overdue: true,
                source_contact_id: '202',
                target_contact_id: [
                  '4'
                ],
                target_contact_name: {
                  4: 'Shauna Barkley'
                },
                assignee_contact_id: [],
                category: [
                  'milestone'
                ],
                type: 'Medical evaluation',
                status: 'Scheduled',
                status_name: 'Scheduled',
                status_type: 'incomplete',
                is_completed: false,
                color: '#42afcb',
                status_css: 'status-type-incomplete activity-status-scheduled'
              }
            ],
            alert: [],
            system: [],
            next: [
              {
                id: '642',
                activity_type_id: '56',
                activity_date_time: '2018-05-12 00:00:00',
                status_id: '1',
                is_star: '0',
                case_id: '3',
                is_overdue: true,
                source_contact_id: '202',
                target_contact_id: [
                  '4'
                ],
                target_contact_name: {
                  4: 'Shauna Barkley'
                },
                assignee_contact_id: [],
                category: [
                  'milestone'
                ],
                type: 'Medical evaluation',
                status: 'Scheduled',
                status_name: 'Scheduled',
                status_type: 'incomplete',
                is_completed: false,
                color: '#42afcb',
                status_css: 'status-type-incomplete activity-status-scheduled'
              }
            ]
          },
          category_count: {
            incomplete: {
              task: 2,
              communication: 2,
              milestone: 4
            },
            completed: {
              milestone: 1
            }
          },
          manager: {
            contact_id: '202',
            display_name: 'admin@example.com',
            sort_name: 'admin@example.com',
            relationship_type_id: '11',
            role: 'Homeless Services Coordinator',
            email: 'admin@example.com',
            phone: null,
            creator: '1',
            manager: '1'
          },
          myRole: [],
          next_activity: {
            id: '642',
            activity_type_id: '56',
            activity_date_time: '2018-05-12 00:00:00',
            status_id: '1',
            is_star: '0',
            case_id: '3',
            is_overdue: true,
            source_contact_id: '202',
            target_contact_id: [
              '4'
            ],
            target_contact_name: {
              4: 'Shauna Barkley'
            },
            assignee_contact_id: [],
            category: [
              'milestone'
            ],
            type: 'Medical evaluation',
            status: 'Scheduled',
            status_name: 'Scheduled',
            status_type: 'incomplete',
            is_completed: false,
            color: '#42afcb',
            status_css: 'status-type-incomplete activity-status-scheduled'
          },
          client: [
            {
              contact_id: '4',
              sort_name: 'Barkley, Shauna',
              display_name: 'Shauna Barkley',
              email: 'barkley.shauna@notmail.org',
              phone: null,
              birth_date: '2004-05-15',
              role: 'Client'
            }
          ],
          status: 'Ongoing',
          color: '#42afcb',
          case_type: 'Housing Support',
          selected: false,
          'api.Case.getcaselist.relatedCasesByContact': { values: [] },
          'api.Case.getcaselist.linkedCases': { values: [] },
          'api.Relationship.get': { values: [] },
          'api.Activity.getAll.1': {
            is_error: 0,
            version: 3,
            count: 18,
            values: [
              {
                id: '1770',
                activity_type_id: '56',
                activity_date_time: '2017-11-12 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1771',
                activity_type_id: '58',
                activity_date_time: '2017-11-12 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1772',
                activity_type_id: '60',
                activity_date_time: '2017-11-13 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1773',
                activity_type_id: '14',
                activity_date_time: '2017-11-14 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1774',
                activity_type_id: '62',
                activity_date_time: '2017-11-18 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1775',
                activity_type_id: '64',
                activity_date_time: '2017-11-25 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1776',
                activity_type_id: '14',
                activity_date_time: '2017-12-02 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1777',
                activity_type_id: '70',
                activity_date_time: '2017-11-20 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1778',
                activity_type_id: '70',
                activity_date_time: '2017-11-21 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1779',
                activity_type_id: '25',
                activity_date_time: '2018-08-16 07:47:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                medium_id: '2',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-16 07:47:23',
                source_contact_id: '202'
              },
              {
                id: '1780',
                activity_type_id: '23',
                subject: 'Case 81 copied from contact id 122 to contact id 170 via merge. New Case ID is 141.',
                activity_date_time: '2018-08-16 07:47:43',
                status_id: '2',
                priority_id: '2',
                is_test: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:43',
                modified_date: '2018-08-16 07:47:43',
                source_contact_id: '202'
              },
              {
                id: '1782',
                activity_type_id: '16',
                subject: 'Case status changed from Ongoing to Urgent',
                activity_date_time: '2018-08-16 07:48:18',
                status_id: '2',
                priority_id: '2',
                is_test: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:48:18',
                modified_date: '2018-08-16 07:48:18',
                source_contact_id: '202'
              },
              {
                id: '1791',
                activity_type_id: '56',
                subject: 'Overdue task',
                activity_date_time: '2018-09-04 12:50:00',
                duration: '20',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                medium_id: '2',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 12:51:46',
                modified_date: '2018-09-06 12:51:46',
                source_contact_id: '202'
              },
              {
                id: '1792',
                source_record_id: '1791',
                activity_type_id: '3',
                subject: 'Overdue task - copy sent to admin@example.com',
                activity_date_time: '2018-09-06 12:51:46',
                details: '===========================================================\nActivity Summary - Medical evaluation\n===========================================================\nYour Case Role(s) : Homeless Services Coordinator\nManage Case : http://civicase.local/civicrm/contact/view/case?reset=1&amp;id=141&amp;cid=170&amp;action=view&amp;context=home\n\nEdit activity : http://civicase.local/civicrm/case/activity?reset=1&amp;cid=170&amp;caseid=141&amp;action=update&amp;id=1791\nView activity : http://civicase.local/civicrm/case/activity/view?reset=1&amp;aid=1791&amp;cid=170&amp;caseID=141\n\nClient : Kiara Adams\nActivity Type : Medical evaluation\nSubject : Overdue task\nCreated By : admin@example.com\nReported By : admin@example.com\nMedium : Phone\nLocation : \nDate and Time : September 4th, 2018 12:50 PM\nDetails : \nDuration : 20 minutes\nStatus : Scheduled\nPriority : \nCase ID : 141\n\n',
                status_id: '2',
                priority_id: '2',
                is_test: '0',
                medium_id: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 12:51:47',
                modified_date: '2018-09-06 12:51:47',
                source_contact_id: '202'
              },
              {
                id: '1793',
                activity_type_id: '70',
                subject: 'Some overdue task',
                activity_date_time: '2018-09-03 12:53:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                medium_id: '2',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 12:54:46',
                modified_date: '2018-09-06 12:54:46',
                source_contact_id: '202'
              },
              {
                id: '1794',
                activity_type_id: '3',
                subject: 'TO be happneing in future',
                activity_date_time: '2018-09-06 14:29:13',
                details: '<p>Some conv</p>\n',
                status_id: '2',
                priority_id: '2',
                is_test: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 14:29:13',
                modified_date: '2018-09-06 14:29:13',
                source_contact_id: '202'
              },
              {
                id: '1797',
                activity_type_id: '70',
                subject: 'TO be happening in future',
                activity_date_time: '2025-09-18 14:29:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                medium_id: '2',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 14:31:12',
                modified_date: '2018-09-06 14:31:12',
                source_contact_id: '202'
              },
              {
                id: '1798',
                activity_type_id: '70',
                subject: 'Some subject',
                activity_date_time: '2018-09-29 00:00:00',
                status_id: '1',
                priority_id: '2',
                parent_id: '1797',
                is_test: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 14:31:12',
                modified_date: '2018-09-06 14:31:12',
                source_contact_id: '202'
              }
            ]
          },
          'api.Activity.getcount.scheduled': 4,
          'api.Activity.getcount.scheduled_overdue': 5,
          'api.Activity.getAll.recentCommunication': { values: [] },
          'api.Activity.getAll.tasks': { values: [] },
          'api.Activity.getAll.nextActivitiesWhichIsNotMileStone': {
            values: [
              {
                id: '1009',
                activity_type_id: '14',
                subject: 'Some random subject is changed',
                activity_date_time: '2018-01-20 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-06 14:14:56',
                modified_date: '2018-10-01 10:24:04',
                source_contact_id: '202'
              }
            ]
          }
        },
        {
          id: '119',
          subject: 'This case is in reference to Lou Blackwell Sr. and Mr. Maxwell Zope Sr. and Mrs. Tanya Jameson.',
          case_type_id: '2',
          status_id: '1',
          is_deleted: false,
          start_date: '2018-06-12',
          modified_date: '2018-08-06 14:15:01',
          contacts: [
            {
              contact_id: '167',
              sort_name: 'Blackwell, Lou',
              display_name: 'Lou Blackwell Sr.',
              email: null,
              phone: null,
              birth_date: '2005-01-10',
              role: 'Client'
            },
            {
              contact_id: '128',
              sort_name: 'Zope, Maxwell',
              display_name: 'Mr. Maxwell Zope Sr.',
              email: 'zope.maxwell40@spamalot.co.in',
              phone: null,
              birth_date: '1985-04-08',
              role: 'Client'
            },
            {
              contact_id: '129',
              sort_name: 'Jameson, Tanya',
              display_name: 'Mrs. Tanya Jameson',
              email: null,
              phone: '656-8538',
              birth_date: '1955-03-12',
              role: 'Client'
            },
            {
              contact_id: '202',
              display_name: 'admin@example.com',
              sort_name: 'admin@example.com',
              relationship_type_id: '13',
              role: 'Senior Services Coordinator',
              email: 'admin@example.com',
              phone: null,
              creator: '1',
              manager: '1'
            }
          ],
          tag_id: {
            9: {
              tag_id: '9',
              'tag_id.name': 'Grape',
              'tag_id.color': '#9044b8',
              'tag_id.description': 'I heard it through the grapevine'
            }
          },
          activity_summary: {
            task: [],
            file: [],
            communication: [
              {
                id: '1575',
                activity_type_id: '14',
                activity_date_time: '2018-06-26 00:00:00',
                status_id: '1',
                is_star: '0',
                case_id: '119',
                is_overdue: true,
                source_contact_id: '202',
                target_contact_id: [
                  '167',
                  '128',
                  '129'
                ],
                target_contact_name: {
                  128: 'Mr. Maxwell Zope Sr.',
                  129: 'Mrs. Tanya Jameson',
                  167: 'Lou Blackwell Sr.'
                },
                assignee_contact_id: [],
                category: [
                  'communication'
                ],
                icon: 'fa-share-square-o',
                type: 'Follow up',
                status: 'Scheduled',
                status_name: 'Scheduled',
                status_type: 'incomplete',
                is_completed: false,
                color: '#42afcb',
                status_css: 'status-type-incomplete activity-status-scheduled'
              }
            ],
            milestone: [
              {
                id: '1572',
                activity_type_id: '56',
                activity_date_time: '2018-06-15 00:00:00',
                status_id: '1',
                is_star: '0',
                case_id: '119',
                is_overdue: true,
                source_contact_id: '202',
                target_contact_id: [
                  '167',
                  '128',
                  '129'
                ],
                target_contact_name: {
                  128: 'Mr. Maxwell Zope Sr.',
                  129: 'Mrs. Tanya Jameson',
                  167: 'Lou Blackwell Sr.'
                },
                assignee_contact_id: [],
                category: [
                  'milestone'
                ],
                type: 'Medical evaluation',
                status: 'Scheduled',
                status_name: 'Scheduled',
                status_type: 'incomplete',
                is_completed: false,
                color: '#42afcb',
                status_css: 'status-type-incomplete activity-status-scheduled'
              }
            ],
            alert: [],
            system: [],
            next: [
              {
                id: '1572',
                activity_type_id: '56',
                activity_date_time: '2018-06-15 00:00:00',
                status_id: '1',
                is_star: '0',
                case_id: '119',
                is_overdue: true,
                source_contact_id: '202',
                target_contact_id: [
                  '167',
                  '128',
                  '129'
                ],
                target_contact_name: {
                  128: 'Mr. Maxwell Zope Sr.',
                  129: 'Mrs. Tanya Jameson',
                  167: 'Lou Blackwell Sr.'
                },
                assignee_contact_id: [],
                category: [
                  'milestone'
                ],
                type: 'Medical evaluation',
                status: 'Scheduled',
                status_name: 'Scheduled',
                status_type: 'incomplete',
                is_completed: false,
                color: '#42afcb',
                status_css: 'status-type-incomplete activity-status-scheduled'
              }
            ]
          },
          category_count: {
            incomplete: {
              communication: 1,
              milestone: 2
            },
            completed: {
              milestone: 1
            }
          },
          manager: {
            contact_id: '202',
            display_name: 'admin@example.com',
            sort_name: 'admin@example.com',
            relationship_type_id: '13',
            role: 'Senior Services Coordinator',
            email: 'admin@example.com',
            phone: null,
            creator: '1',
            manager: '1'
          },
          myRole: [],
          next_activity: {
            id: '1572',
            activity_type_id: '56',
            activity_date_time: '2018-06-15 00:00:00',
            status_id: '1',
            is_star: '0',
            case_id: '119',
            is_overdue: true,
            source_contact_id: '202',
            target_contact_id: [
              '167',
              '128',
              '129'
            ],
            target_contact_name: {
              128: 'Mr. Maxwell Zope Sr.',
              129: 'Mrs. Tanya Jameson',
              167: 'Lou Blackwell Sr.'
            },
            assignee_contact_id: [],
            category: [
              'milestone'
            ],
            type: 'Medical evaluation',
            status: 'Scheduled',
            status_name: 'Scheduled',
            status_type: 'incomplete',
            is_completed: false,
            color: '#42afcb',
            status_css: 'status-type-incomplete activity-status-scheduled'
          },
          client: [
            {
              contact_id: '167',
              sort_name: 'Blackwell, Lou',
              display_name: 'Lou Blackwell Sr.',
              email: null,
              phone: null,
              birth_date: '2005-01-10',
              role: 'Client'
            },
            {
              contact_id: '128',
              sort_name: 'Zope, Maxwell',
              display_name: 'Mr. Maxwell Zope Sr.',
              email: 'zope.maxwell40@spamalot.co.in',
              phone: null,
              birth_date: '1985-04-08',
              role: 'Client'
            },
            {
              contact_id: '129',
              sort_name: 'Jameson, Tanya',
              display_name: 'Mrs. Tanya Jameson',
              email: null,
              phone: '656-8538',
              birth_date: '1955-03-12',
              role: 'Client'
            }
          ],
          status: 'Ongoing',
          color: '#42afcb',
          case_type: 'Adult Day Care Referral',
          selected: false,
          'api.Case.getcaselist.relatedCasesByContact': { values: [] },
          'api.Case.getcaselist.linkedCases': { values: [] },
          'api.Relationship.get': { values: [] },
          'api.Activity.getAll.1': {
            is_error: 0,
            version: 3,
            count: 18,
            values: [
              {
                id: '1770',
                activity_type_id: '56',
                activity_date_time: '2017-11-12 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1771',
                activity_type_id: '58',
                activity_date_time: '2017-11-12 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1772',
                activity_type_id: '60',
                activity_date_time: '2017-11-13 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1773',
                activity_type_id: '14',
                activity_date_time: '2017-11-14 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1774',
                activity_type_id: '62',
                activity_date_time: '2017-11-18 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1775',
                activity_type_id: '64',
                activity_date_time: '2017-11-25 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1776',
                activity_type_id: '14',
                activity_date_time: '2017-12-02 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1777',
                activity_type_id: '70',
                activity_date_time: '2017-11-20 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1778',
                activity_type_id: '70',
                activity_date_time: '2017-11-21 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-06 14:14:58',
                source_contact_id: '202'
              },
              {
                id: '1779',
                activity_type_id: '25',
                activity_date_time: '2018-08-16 07:47:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                medium_id: '2',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:42',
                modified_date: '2018-08-16 07:47:23',
                source_contact_id: '202'
              },
              {
                id: '1780',
                activity_type_id: '23',
                subject: 'Case 81 copied from contact id 122 to contact id 170 via merge. New Case ID is 141.',
                activity_date_time: '2018-08-16 07:47:43',
                status_id: '2',
                priority_id: '2',
                is_test: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:47:43',
                modified_date: '2018-08-16 07:47:43',
                source_contact_id: '202'
              },
              {
                id: '1782',
                activity_type_id: '16',
                subject: 'Case status changed from Ongoing to Urgent',
                activity_date_time: '2018-08-16 07:48:18',
                status_id: '2',
                priority_id: '2',
                is_test: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-16 07:48:18',
                modified_date: '2018-08-16 07:48:18',
                source_contact_id: '202'
              },
              {
                id: '1791',
                activity_type_id: '56',
                subject: 'Overdue task',
                activity_date_time: '2018-09-04 12:50:00',
                duration: '20',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                medium_id: '2',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 12:51:46',
                modified_date: '2018-09-06 12:51:46',
                source_contact_id: '202'
              },
              {
                id: '1792',
                source_record_id: '1791',
                activity_type_id: '3',
                subject: 'Overdue task - copy sent to admin@example.com',
                activity_date_time: '2018-09-06 12:51:46',
                details: '===========================================================\nActivity Summary - Medical evaluation\n===========================================================\nYour Case Role(s) : Homeless Services Coordinator\nManage Case : http://civicase.local/civicrm/contact/view/case?reset=1&amp;id=141&amp;cid=170&amp;action=view&amp;context=home\n\nEdit activity : http://civicase.local/civicrm/case/activity?reset=1&amp;cid=170&amp;caseid=141&amp;action=update&amp;id=1791\nView activity : http://civicase.local/civicrm/case/activity/view?reset=1&amp;aid=1791&amp;cid=170&amp;caseID=141\n\nClient : Kiara Adams\nActivity Type : Medical evaluation\nSubject : Overdue task\nCreated By : admin@example.com\nReported By : admin@example.com\nMedium : Phone\nLocation : \nDate and Time : September 4th, 2018 12:50 PM\nDetails : \nDuration : 20 minutes\nStatus : Scheduled\nPriority : \nCase ID : 141\n\n',
                status_id: '2',
                priority_id: '2',
                is_test: '0',
                medium_id: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 12:51:47',
                modified_date: '2018-09-06 12:51:47',
                source_contact_id: '202'
              },
              {
                id: '1793',
                activity_type_id: '70',
                subject: 'Some overdue task',
                activity_date_time: '2018-09-03 12:53:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                medium_id: '2',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 12:54:46',
                modified_date: '2018-09-06 12:54:46',
                source_contact_id: '202'
              },
              {
                id: '1794',
                activity_type_id: '3',
                subject: 'TO be happneing in future',
                activity_date_time: '2018-09-06 14:29:13',
                details: '<p>Some conv</p>\n',
                status_id: '2',
                priority_id: '2',
                is_test: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 14:29:13',
                modified_date: '2018-09-06 14:29:13',
                source_contact_id: '202'
              },
              {
                id: '1797',
                activity_type_id: '70',
                subject: 'TO be happening in future',
                activity_date_time: '2025-09-18 14:29:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                medium_id: '2',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 14:31:12',
                modified_date: '2018-09-06 14:31:12',
                source_contact_id: '202'
              },
              {
                id: '1798',
                activity_type_id: '70',
                subject: 'Some subject',
                activity_date_time: '2018-09-29 00:00:00',
                status_id: '1',
                priority_id: '2',
                parent_id: '1797',
                is_test: '0',
                is_auto: '0',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-09-06 14:31:12',
                modified_date: '2018-09-06 14:31:12',
                source_contact_id: '202'
              }
            ]
          },
          'api.Activity.getcount.scheduled': 6,
          'api.Activity.getcount.scheduled_overdue': 7,
          'api.Activity.getAll.recentCommunication': { values: [] },
          'api.Activity.getAll.tasks': { values: [] },
          'api.Activity.getAll.nextActivitiesWhichIsNotMileStone': {
            values: [
              {
                id: '1009',
                activity_type_id: '14',
                subject: 'Some random subject is changed',
                activity_date_time: '2018-01-20 00:00:00',
                status_id: '1',
                priority_id: '2',
                is_test: '0',
                is_auto: '1',
                is_current_revision: '1',
                is_deleted: '0',
                is_star: '0',
                created_date: '2018-08-06 14:14:56',
                modified_date: '2018-10-01 10:24:04',
                source_contact_id: '202'
              }
            ]
          }
        }
      ]
    };

    _.each(casesMockData.values, (caseObj) => {
      caseObj['api.Case.getcaselist.relatedCasesByContact'].values = [angular.copy(casesMockData.values[0])];
      caseObj['api.Case.getcaselist.linkedCases'].values = [angular.copy(casesMockData.values[1])];
    });

    return {
      /**
       * Returns a list of mocked cases
       *
       * @returns {Array} each array contains an object with the activity data.
       */
      get: () => {
        return angular.copy(casesMockData);
      }
    };
  });
})(angular, CRM._);

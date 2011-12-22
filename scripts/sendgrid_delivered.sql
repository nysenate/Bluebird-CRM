-- Project: BluebirdCRM
-- Author: Ken Zalewski
-- Organization: New York State Senate
-- Date: 2011-12-21
-- Revised: 2011-12-22

CREATE TABLE civicrm_mailing_event_sendgrid_delivered (
  id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
  event_queue_id int(10) unsigned,
  time_stamp datetime,
  FOREIGN KEY (event_queue_id) REFERENCES civicrm_mailing_event_queue(id)
);

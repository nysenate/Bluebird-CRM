const createUniqueRecordFactory = require('../utils/create-unique-record-factory.js');
const createUniqueContact = createUniqueRecordFactory('Contact', ['display_name']);
const createUniqueEmail = createUniqueRecordFactory('Email', ['email']);

const service = {
  setupData,
  adminUserID: '2',
  activeContact: {
    id: null,
    displayName: 'Arnold Backstop',
    emailID: 'arnold@backstop.com'
  },
  emptyContact: {
    id: null,
    displayName: 'Emil Backstop',
    emailID: 'emil@backstop.com'
  }
};

/**
 * Create Contacts
 */
function setupData () {
  service.activeContact.id = createContact(service.activeContact).id;
  service.emptyContact.id = createContact(service.emptyContact).id;

  console.log('Contact data setup successful.');
}

/**
 * Create Contacts
 *
 * @param {object} contact contact object
 * @returns {object} contact object
 */
function createContact (contact) {
  const contactObj = createUniqueContact({
    contact_type: 'Individual',
    display_name: contact.displayName
  });

  createUniqueEmail({
    contact_id: contactObj.id,
    email: contact.emailID
  });

  return contactObj;
}

module.exports = service;

{
    "url": "civicrm/contact/view",
    "title": ts("Introduction to Contacts"),
    "auto_start": true,
    "steps": [
        {
            "target": "#contactname-block",
            "title": ts("Welcome to the contact summary."),
            "placement": "bottom",
            "icon": "fa-user",
            "content": ts("This is the main view of your contacts in CiviCRM. Let's take a look around...")
        },
        {
            "target": "#crm-phone-content  .crm-label",
            "title": ts("View and edit contact information."),
            "placement": "top",
            "content": ts("Click on a box to add or edit this contact's email, phone, address, etc."),
            "icon": "fa-pencil"
        },
        {
            "target": "#crm-contactname-content  .crm-summary-display_name",
            "title": ts("Click to edit name"),
            "placement": "bottom",
            "content": ts("You can edit this contact's name by clicking here."),
            "icon": "fa-pencil"
        },
        {
            "target": "#crm-contact-actions-wrapper",
            "title": ts("Things you can do."),
            "placement": "right",
            "content": ts("The actions menu gives a quick list of ways you can interact with this contact."),
            "icon": "fa-bars"
        },
        {
            "target": "#tab_activity",
            "title": ts("Related information in tabs."),
            "placement": "bottom",
            "content": ts("Click around in the other tabs to see this contact's contributions, activities, relationships, etc."),
            "icon": "fa-list-ol"
        },
        {
            "target": "#contact-summary  .separator",
            "title": ts("Custom Data"),
            "placement": "top",
            "content": ts("All the custom fields your organization has created for contacts are shown here. You can click to edit these too."),
            "icon": "fa-cog"
        }
    ],
    "groups": []
}

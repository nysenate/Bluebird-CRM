(function () {
  var module = angular.module('civicase.data');

  module.constant('ContactsData', {
    values: [
      {
        contact_id: '1',
        contact_type: 'Organization',
        contact_sub_type: '',
        sort_name: 'Default Organization',
        display_name: 'Default Organization',
        do_not_email: '0',
        do_not_phone: '0',
        do_not_mail: '0',
        do_not_sms: '0',
        do_not_trade: '0',
        is_opt_out: '0',
        legal_identifier: '',
        external_identifier: '',
        nick_name: '',
        legal_name: 'Default Organization',
        image_URL: '1.jpg',
        preferred_communication_method: '',
        preferred_language: '',
        preferred_mail_format: 'Both',
        first_name: '',
        middle_name: '',
        last_name: '',
        prefix_id: '',
        suffix_id: '',
        formal_title: '',
        communication_style_id: '',
        job_title: '',
        gender_id: '',
        birth_date: '',
        is_deceased: '0',
        deceased_date: '',
        household_name: '',
        organization_name: 'Default Organization',
        sic_code: '',
        contact_is_deleted: '0',
        current_employer: '',
        address_id: '180',
        street_address: '123 Some St',
        supplemental_address_1: '',
        supplemental_address_2: '',
        supplemental_address_3: '',
        city: 'Hereville',
        postal_code_suffix: '',
        postal_code: '94100',
        geo_code_1: '',
        geo_code_2: '',
        state_province_id: '1004',
        country_id: '1228',
        phone_id: '',
        phone_type_id: '',
        phone: '',
        email_id: '',
        email: '',
        on_hold: '',
        im_id: '',
        provider_id: '',
        im: '',
        worldregion_id: '2',
        world_region: 'America South, Central, North and Caribbean',
        languages: '',
        individual_prefix: '',
        individual_suffix: '',
        communication_style: '',
        gender: '',
        state_province_name: 'California',
        state_province: 'CA',
        country: 'United States',
        id: '1',
        'api.Phone.get': {
          is_error: 0,
          version: 3,
          count: 2,
          values: [
            {
              'api.LocationType.get': {
                count: 1,
                id: 1,
                is_error: 0,
                values: [
                  {
                    description: 'Place of residence',
                    display_name: 'Home',
                    id: '1',
                    is_active: '1',
                    is_default: '1',
                    is_reserved: '0',
                    name: 'Home',
                    vcard_name: 'HOME'
                  }
                ],
                version: 3
              },
              id: '172',
              phone: '123123123',
              'phone_type_id.name': 'Mobile'
            },
            {
              'api.LocationType.get': {
                count: 1,
                id: 1,
                is_error: 0,
                values: [
                  {
                    description: 'Place of residence',
                    display_name: 'Home',
                    id: '1',
                    is_active: '1',
                    is_default: '1',
                    is_reserved: '0',
                    name: 'Home',
                    vcard_name: 'HOME'
                  }
                ],
                version: 3
              },
              id: '171',
              phone: '9991112222',
              'phone_type_id.name': 'Phone'
            }
          ]
        },
        'api.GroupContact.get': {
          is_error: 0,
          version: 3,
          count: 2,
          values: [
            {
              id: '102',
              group_id: '1',
              title: 'Administrators',
              visibility: 'User and User Admin Only',
              is_hidden: '0',
              in_date: '2018-09-11 03:17:26',
              in_method: 'Admin'
            },
            {
              id: '103',
              group_id: '5',
              title: 'Case Resources',
              visibility: 'User and User Admin Only',
              is_hidden: '0',
              in_date: '2018-09-11 03:17:29',
              in_method: 'Admin'
            }
          ]
        }
      },
      {
        contact_id: '2',
        contact_type: 'Individual',
        contact_sub_type: '',
        sort_name: 'Wattson, Shauna',
        display_name: 'Shauna Wattson',
        do_not_email: '0',
        do_not_phone: '0',
        do_not_mail: '0',
        do_not_sms: '0',
        do_not_trade: '0',
        is_opt_out: '0',
        legal_identifier: '',
        external_identifier: '',
        nick_name: '',
        legal_name: '',
        image_URL: '2.jpg',
        preferred_communication_method: [
          '4'
        ],
        languages: '',
        preferred_language: '',
        preferred_mail_format: 'Both',
        first_name: 'Shauna',
        middle_name: 'H',
        last_name: 'Wattson',
        individual_prefix: '',
        prefix_id: '',
        individual_suffix: '',
        suffix_id: '',
        formal_title: '',
        communication_style: '',
        communication_style_id: '',
        job_title: '',
        gender: 'Female',
        gender_id: '1',
        birth_date: '',
        is_deceased: '0',
        deceased_date: '',
        household_name: '',
        organization_name: '',
        sic_code: '',
        contact_is_deleted: '0',
        current_employer: '',
        address_id: '',
        street_address: '',
        supplemental_address_1: '',
        supplemental_address_2: '',
        supplemental_address_3: '',
        city: '',
        postal_code_suffix: '',
        postal_code: '',
        geo_code_1: '',
        geo_code_2: '',
        state_province_id: '',
        country_id: '',
        phone_id: '60',
        phone_type_id: '1',
        phone: '254-8403',
        email_id: '',
        email: '',
        on_hold: '',
        im_id: '',
        provider_id: '',
        im: '',
        worldregion_id: '',
        world_region: '',
        state_province_name: '',
        state_province: '',
        country: '',
        id: '2',
        'api.Phone.get': {
          is_error: 0,
          version: 3,
          count: 2,
          values: [
            {
              'api.LocationType.get': {
                count: 1,
                id: 1,
                is_error: 0,
                values: [
                  {
                    description: 'Place of residence',
                    display_name: 'Home',
                    id: '1',
                    is_active: '1',
                    is_default: '1',
                    is_reserved: '0',
                    name: 'Home',
                    vcard_name: 'HOME'
                  }
                ],
                version: 3
              },
              id: '172',
              phone: '123123123',
              'phone_type_id.name': 'Mobile'
            },
            {
              'api.LocationType.get': {
                count: 1,
                id: 1,
                is_error: 0,
                values: [
                  {
                    description: 'Place of residence',
                    display_name: 'Home',
                    id: '1',
                    is_active: '1',
                    is_default: '1',
                    is_reserved: '0',
                    name: 'Home',
                    vcard_name: 'HOME'
                  }
                ],
                version: 3
              },
              id: '171',
              phone: '9991112222',
              'phone_type_id.name': 'Phone'
            }
          ]
        },
        'api.GroupContact.get': {
          is_error: 0,
          version: 3,
          count: 2,
          values: [
            {
              id: '102',
              group_id: '1',
              title: 'Administrators',
              visibility: 'User and User Admin Only',
              is_hidden: '0',
              in_date: '2018-09-11 03:17:26',
              in_method: 'Admin'
            },
            {
              id: '103',
              group_id: '5',
              title: 'Case Resources',
              visibility: 'User and User Admin Only',
              is_hidden: '0',
              in_date: '2018-09-11 03:17:29',
              in_method: 'Admin'
            }
          ]
        }
      },
      {
        contact_id: '3',
        contact_type: 'Individual',
        contact_sub_type: '',
        sort_name: 'Jones, Kiara',
        display_name: 'Kiara Jones',
        do_not_email: '1',
        do_not_phone: '0',
        do_not_mail: '0',
        do_not_sms: '0',
        do_not_trade: '1',
        is_opt_out: '0',
        legal_identifier: '',
        external_identifier: '',
        nick_name: '',
        legal_name: '',
        image_URL: '3.jpg',
        preferred_communication_method: [
          '1'
        ],
        languages: '',
        preferred_language: '',
        preferred_mail_format: 'Both',
        first_name: 'Kiara',
        middle_name: 'R',
        last_name: 'Jones',
        individual_prefix: '',
        prefix_id: '',
        individual_suffix: '',
        suffix_id: '',
        formal_title: '',
        communication_style: '',
        communication_style_id: '',
        job_title: '',
        gender: 'Female',
        gender_id: '1',
        birth_date: '1958-08-01',
        is_deceased: '0',
        deceased_date: '',
        household_name: '',
        organization_name: '',
        sic_code: '',
        contact_is_deleted: '0',
        current_employer: '',
        address_id: '',
        street_address: '',
        supplemental_address_1: '',
        supplemental_address_2: '',
        supplemental_address_3: '',
        city: '',
        postal_code_suffix: '',
        postal_code: '',
        geo_code_1: '',
        geo_code_2: '',
        state_province_id: '',
        country_id: '',
        phone_id: '',
        phone_type_id: '',
        phone: '',
        email_id: '22',
        email: 'jones.kiara47@fakemail.co.nz',
        on_hold: '0',
        im_id: '',
        provider_id: '',
        im: '',
        worldregion_id: '',
        world_region: '',
        state_province_name: '',
        state_province: '',
        country: '',
        id: '3',
        'api.Phone.get': {
          is_error: 0,
          version: 3,
          count: 2,
          values: [
            {
              id: '172',
              phone: '123123123',
              'phone_type_id.name': 'Mobile'
            },
            {
              id: '171',
              phone: '9991112222',
              'phone_type_id.name': 'Phone'
            }
          ]
        },
        'api.GroupContact.get': {
          is_error: 0,
          version: 3,
          count: 2,
          values: [
            {
              id: '102',
              group_id: '1',
              title: 'Administrators',
              visibility: 'User and User Admin Only',
              is_hidden: '0',
              in_date: '2018-09-11 03:17:26',
              in_method: 'Admin'
            },
            {
              id: '103',
              group_id: '5',
              title: 'Case Resources',
              visibility: 'User and User Admin Only',
              is_hidden: '0',
              in_date: '2018-09-11 03:17:29',
              in_method: 'Admin'
            }
          ]
        }
      },
      {
        contact_id: '4',
        contact_type: 'Individual',
        contact_sub_type: '',
        sort_name: 'Barkley, Shauna',
        display_name: 'Shauna Barkley',
        do_not_email: '0',
        do_not_phone: '1',
        do_not_mail: '0',
        do_not_sms: '0',
        do_not_trade: '0',
        is_opt_out: '0',
        legal_identifier: '',
        external_identifier: '',
        nick_name: '',
        legal_name: '',
        image_URL: '4.jpg',
        preferred_communication_method: '',
        languages: '',
        preferred_language: '',
        preferred_mail_format: 'Both',
        first_name: 'Shauna',
        middle_name: '',
        last_name: 'Barkley',
        individual_prefix: '',
        prefix_id: '',
        individual_suffix: '',
        suffix_id: '',
        formal_title: '',
        communication_style: '',
        communication_style_id: '',
        job_title: '',
        gender: 'Female',
        gender_id: '1',
        birth_date: '2004-05-15',
        is_deceased: '0',
        deceased_date: '',
        household_name: '',
        organization_name: '',
        sic_code: '',
        contact_is_deleted: '0',
        current_employer: '',
        address_id: '98',
        street_address: '175E Bay Dr W',
        supplemental_address_1: '',
        supplemental_address_2: '',
        supplemental_address_3: '',
        city: 'Seville',
        postal_code_suffix: '',
        postal_code: '31084',
        geo_code_1: '31.989039',
        geo_code_2: '-83.394574',
        state_province_id: '1009',
        country_id: '1228',
        phone_id: '',
        phone_type_id: '',
        phone: '',
        email_id: '80',
        email: 'barkley.shauna@notmail.org',
        on_hold: '0',
        im_id: '',
        provider_id: '',
        im: '',
        worldregion_id: '2',
        world_region: 'America South, Central, North and Caribbean',
        state_province_name: 'Georgia',
        state_province: 'GA',
        country: 'United States',
        id: '4',
        'api.Phone.get': {
          is_error: 0,
          version: 3,
          count: 2,
          values: [
            {
              id: '172',
              phone: '123123123',
              'phone_type_id.name': 'Mobile'
            },
            {
              id: '171',
              phone: '9991112222',
              'phone_type_id.name': 'Phone'
            }
          ]
        },
        'api.GroupContact.get': {
          is_error: 0,
          version: 3,
          count: 2,
          values: [
            {
              id: '102',
              group_id: '1',
              title: 'Administrators',
              visibility: 'User and User Admin Only',
              is_hidden: '0',
              in_date: '2018-09-11 03:17:26',
              in_method: 'Admin'
            },
            {
              id: '103',
              group_id: '5',
              title: 'Case Resources',
              visibility: 'User and User Admin Only',
              is_hidden: '0',
              in_date: '2018-09-11 03:17:29',
              in_method: 'Admin'
            }
          ]
        }
      }
    ]
  });
}());

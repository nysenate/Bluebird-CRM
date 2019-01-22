<?php
use CRM_Districtstats_ExtensionUtil as E;

class CRM_Districtstats_Page_DistrictStats extends CRM_Core_Page {

  //7447
  public function run() {
    CRM_Core_Resources::singleton()->addStyleFile(E::LONG_NAME, 'css/DistrictStats.css');

    if (CRM_Utils_Request::retrieve('snippet', 'Positive') == 2) {
      CRM_Core_Resources::singleton()->addStyleFile(E::LONG_NAME, 'css/DistrictStatsPrint.css');
    }

    //contact counts by type
    $allContacts  = 0;
    $contactTypes = array();
    $sql_contacts = "
      SELECT contact_type, COUNT(*) AS ct_count
      FROM civicrm_contact
      WHERE is_deleted != 1
      GROUP BY contact_type;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_contacts );
    while ( $dao->fetch( ) ) {
      $contactTypes[$dao->contact_type] = $dao->ct_count;
      $allContacts = $allContacts + $dao->ct_count;
    }
    $contactTypes['All Contacts'] = $allContacts;

    //trashed contacts
    $sql_trashed = "
      SELECT COUNT(*) AS trashed_count
      FROM civicrm_contact
      WHERE is_deleted = 1;
    ";
    $trashed = CRM_Core_DAO::singleValueQuery( $sql_trashed );
    $contactTypes['Trashed Contacts'] = $trashed;

    //deceased contacts
    $sql_trashed = "
      SELECT COUNT(*) AS deceased_count
      FROM civicrm_contact
      WHERE is_deleted != 1 AND is_deceased = 1;
    ";
    $contactTypes['Deceased Contacts'] = CRM_Core_DAO::singleValueQuery( $sql_trashed );

    $this->assign('contactTypes', $contactTypes);

    //get gender counts
    $sql_genders = "
      SELECT gender_id, COUNT(*) AS gender_count
      FROM civicrm_contact
      WHERE is_deleted != 1
      GROUP BY gender_id;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_genders );
    while ( $dao->fetch( ) ) {
      $contactGenders[$dao->gender_id] = $dao->gender_count;
    }
    $this->assign('contactGenders', $contactGenders);

    //email counts
    $emailCounts = array();

    //get contacts with emails
    $sql_emails = "
      SELECT COUNT( c.id ) AS email_count
      FROM civicrm_contact c
      JOIN civicrm_email ce
        ON c.id = ce.contact_id
        AND ce.is_primary = 1
      WHERE is_deleted != 1
        AND email IS NOT NULL
        AND email != '';
    ";
    $emailsPri = CRM_Core_DAO::singleValueQuery( $sql_emails );

    //contacts with bulk, non-primary emails
    $sql_emailsBulk = "
      SELECT COUNT( c.id ) AS emailBulk_count
      FROM civicrm_contact c
      JOIN civicrm_email ce
        ON c.id = ce.contact_id
        AND ce.is_primary != 1
        AND ce.is_bulkmail = 1
      WHERE is_deleted != 1
        AND email IS NOT NULL
        AND email != '';
    ";
    $emailsBulk = CRM_Core_DAO::singleValueQuery( $sql_emailsBulk );

    $emailCounts['Primary-Only Emails'] = $emailsPri - $emailsBulk;

    $emailCounts['Alternate Bulk Emails'] = $emailsBulk;

    $emailCounts['Total Contacts with Emails'] = $emailsPri;

    //pri or bulk on_hold
    $sql_emailsOH = "
      SELECT COUNT( c.id ) AS emailOH_count
      FROM civicrm_contact c
      JOIN civicrm_email ce
        ON c.id = ce.contact_id
        AND ( ( ce.is_primary = 1 AND ce.is_bulkmail = 0 )
          OR ( ce.is_primary = 0 AND ce.is_bulkmail = 1 ) )
        AND ce.on_hold = 1
        AND ce.email IS NOT NULL
        AND ce.email != ''
      WHERE is_deleted != 1;
    ";
    $emailCounts['Primary/Bulk On Hold'] = CRM_Core_DAO::singleValueQuery( $sql_emailsOH );

    //do not email
    $sql_emailsDNE = "
      SELECT COUNT( c.id ) AS emailDNE_count
      FROM civicrm_contact c
      JOIN civicrm_email ce
        ON c.id = ce.contact_id
        AND ce.email IS NOT NULL
        AND ce.email != ''
      WHERE is_deleted != 1
        AND do_not_email = 1;
    ";
    $emailCounts['Do Not Email'] = CRM_Core_DAO::singleValueQuery( $sql_emailsDNE );

    //opt out
    $sql_emailsOO = "
      SELECT COUNT( c.id ) AS emailOO_count
      FROM civicrm_contact c
      JOIN civicrm_email ce
        ON c.id = ce.contact_id
        AND ce.email IS NOT NULL
        AND ce.email != ''
      WHERE is_deleted != 1
        AND is_opt_out = 1;
    ";
    $emailCounts['Contact Opt Out/No Bulk Email'] = CRM_Core_DAO::singleValueQuery( $sql_emailsOO );

    //contact deceased, with email
    $sql_emailDec = "
      SELECT COUNT( c.id ) AS emailDec_count
      FROM civicrm_contact c
      JOIN civicrm_email ce
        ON c.id = ce.contact_id
        AND ce.is_primary = 1
        AND ce.email IS NOT NULL
        AND ce.email != ''
      WHERE is_deleted != 1
        AND is_deceased = 1;
    ";
    $emailCounts['Contacts Deceased, with Email'] = CRM_Core_DAO::singleValueQuery( $sql_emailDec );

    //duplicate emails
    $sql_dupeEmails = "
      SELECT count(em3.id) AS dupeEmail
      FROM (
        SELECT ANY_VALUE(em1.id) id
        FROM civicrm_email em1
        JOIN civicrm_email em2
          ON em1.email = em2.email
          AND em1.contact_id != em2.contact_ID
          AND em2.email IS NOT NULL
          AND em2.email != ''
        LEFT JOIN civicrm_contact c1
          ON em1.contact_id = c1.id
        LEFT JOIN civicrm_contact c2
          ON em2.contact_id = c2.id
        WHERE c1.is_deleted != 1
          AND c2.is_deleted != 1
        GROUP BY em1.email
      ) em3;
    ";
    $emailCounts['Duplicate Emails'] = CRM_Core_DAO::singleValueQuery( $sql_dupeEmails );

    //potential maximum audience
    $sql_emailsMax = "
      SELECT COUNT(c.id) AS emailMax_count
      FROM civicrm_contact c
      JOIN (
        SELECT contact_id
        FROM civicrm_email
        WHERE
          (
            (is_primary = 1 AND is_bulkmail = 0)
              OR
            (is_primary = 0 AND is_bulkmail = 1)
          )
          AND on_hold = 0
          AND email IS NOT NULL
          AND email != ''
        GROUP BY contact_id
      ) ce
        ON c.id = ce.contact_id
      WHERE is_deleted != 1
        AND do_not_email = 0
        AND is_opt_out = 0
        AND is_deceased = 0;
    ";
    $emailCounts['Effective Maximum Mailing'] = CRM_Core_DAO::singleValueQuery( $sql_emailsMax ) - $emailCounts['Duplicate Emails'];

    $this->assign('emailCounts', $emailCounts);

    //misc contact stats
    $miscCounts = array();

    //phone contacts
    $sql_phone = "
      SELECT COUNT( c.id ) AS phone_count
      FROM civicrm_contact c
      JOIN civicrm_phone cp
        ON ( c.id = cp.contact_id AND cp.is_primary = 1 AND cp.phone_type_id = 1 )
      WHERE is_deleted != 1;
    ";
    $miscCounts['Contacts with Phone'] = CRM_Core_DAO::singleValueQuery( $sql_phone );

    //do not mail
    $sql_DNM = "
      SELECT COUNT( c.id ) AS emailDNM_count
      FROM civicrm_contact c
      WHERE is_deleted != 1
        AND do_not_mail = 1;
    ";
    $miscCounts['Do Not Mail'] = CRM_Core_DAO::singleValueQuery( $sql_DNM );

    //mailing seed group
    $mailingSeedGroup = CRM_Core_DAO::singleValueQuery( "SELECT id FROM civicrm_group WHERE name = 'Mailing_Seeds';" );
    if ( $mailingSeedGroup ) {
      $sql_MailingSeeds = "
        SELECT COUNT( c.id ) AS mailingSeeds_count
        FROM civicrm_contact c
        JOIN civicrm_group_contact gc
          ON ( c.id = gc.contact_id AND group_id = $mailingSeedGroup )
        WHERE is_deleted != 1;
      ";
      $miscCounts['Mailing Seeds'] = CRM_Core_DAO::singleValueQuery( $sql_MailingSeeds );
    }

    //log last 30 days
    $sql_log30 = "
      SELECT COUNT(DISTINCT c.id ) AS log30_count
      FROM civicrm_contact c
      JOIN civicrm_log cl
        ON ( c.id = cl.entity_id
        AND cl.entity_table = 'civicrm_contact' )
      WHERE is_deleted != 1
        AND cl.modified_date > (DATE_SUB(NOW(), interval 30 day));
    ";
    $miscCounts['Modified Last 30 Days'] = CRM_Core_DAO::singleValueQuery( $sql_log30 );

    //log YTD
    $sql_logYTD  = "
      SELECT COUNT(DISTINCT c.id ) AS logYTD_count
      FROM civicrm_contact c
      JOIN civicrm_log cl
        ON ( c.id = cl.entity_id
        AND cl.entity_table = 'civicrm_contact' )
      WHERE is_deleted != 1
        AND cl.modified_date > CONCAT( YEAR(NOW()),'-01-01' );
    ";
    $miscCounts['Modified this Year'] = CRM_Core_DAO::singleValueQuery( $sql_logYTD );

    $this->assign('miscCounts', $miscCounts);

    //get contact counts by Senate District
    $sql_sd = "
      SELECT COUNT( civicrm_contact.id ) as sd_count, ny_senate_district_47
      FROM ( civicrm_address
      INNER JOIN civicrm_value_district_information_7
        ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
      INNER JOIN civicrm_contact
        ON ( civicrm_contact.id = civicrm_address.contact_id )
      WHERE ( civicrm_contact.is_deleted != 1 )
        AND ( civicrm_address.is_primary = 1 )
        AND ( civicrm_value_district_information_7.ny_senate_district_47 IS NOT NULL )
        AND ( civicrm_value_district_information_7.ny_senate_district_47 != '' )
      GROUP BY ny_senate_district_47;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_sd );
    while ( $dao->fetch( ) ) {
      $contactSD[$dao->ny_senate_district_47] = $dao->sd_count;
    }
    $this->assign('contactSD', $contactSD);

    //get contact counts by Assembly District
    $sql_ad = "
      SELECT COUNT( civicrm_contact.id ) as ad_count, ny_assembly_district_48
      FROM ( civicrm_address
      INNER JOIN civicrm_value_district_information_7
        ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
      INNER JOIN civicrm_contact
        ON ( civicrm_contact.id = civicrm_address.contact_id )
      WHERE ( civicrm_contact.is_deleted != 1 )
        AND ( civicrm_address.is_primary = 1 )
        AND ( civicrm_value_district_information_7.ny_assembly_district_48 IS NOT NULL )
        AND ( civicrm_value_district_information_7.ny_assembly_district_48 != '' )
      GROUP BY ny_assembly_district_48;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_ad );
    while ( $dao->fetch( ) ) {
      $contactAD[$dao->ny_assembly_district_48] = $dao->ad_count;
    }
    $this->assign('contactAD', $contactAD);

    //get contact counts by Election District
    $sql_ed = "
      SELECT COUNT( civicrm_contact.id ) as ed_count, election_district_49
      FROM ( civicrm_address
      INNER JOIN civicrm_value_district_information_7
        ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
      INNER JOIN civicrm_contact
        ON ( civicrm_contact.id = civicrm_address.contact_id )
      WHERE ( civicrm_contact.is_deleted != 1 )
        AND ( civicrm_address.is_primary = 1 )
        AND ( civicrm_value_district_information_7.election_district_49 IS NOT NULL )
        AND ( civicrm_value_district_information_7.election_district_49 != '' )
      GROUP BY election_district_49;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_ed );
    while ( $dao->fetch( ) ) {
      $contactED[$dao->election_district_49] = $dao->ed_count;
    }
    $this->assign('contactED', $contactED);
    //CRM_Core_Error::debug($contactAD);

    //contact counts by Town/Assembly District/Election District
    $sql_townaded = "
      SELECT COUNT( civicrm_contact.id ) as townaded_count, town_52, ny_assembly_district_48, election_district_49
      FROM ( civicrm_address
      INNER JOIN civicrm_value_district_information_7
        ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
      INNER JOIN civicrm_contact
        ON civicrm_contact.id = civicrm_address.contact_id
      WHERE ( civicrm_contact.is_deleted != 1 )
        AND ( civicrm_address.is_primary = 1 )
        AND ( civicrm_value_district_information_7.town_52 IS NOT NULL )
        AND ( civicrm_value_district_information_7.town_52 != '' )
        AND ( civicrm_value_district_information_7.ny_assembly_district_48 IS NOT NULL )
        AND ( civicrm_value_district_information_7.ny_assembly_district_48 != '' )
        AND ( civicrm_value_district_information_7.election_district_49 IS NOT NULL )
        AND ( civicrm_value_district_information_7.election_district_49 != '' )
      GROUP BY town_52, ny_assembly_district_48, election_district_49;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_townaded );
    while ( $dao->fetch( ) ) {
      $contactTownADED[] = array(
        'town' => $dao->town_52,
        'ad' => $dao->ny_assembly_district_48,
        'ed' => $dao->election_district_49,
        'count' => $dao->townaded_count
      );
    }
    $this->assign('contactTownADED', $contactTownADED);
    //CRM_Core_Error::debug($contactTownADED);

    //get contact counts by Congressional District
    $sql_cd = "
      SELECT COUNT( civicrm_contact.id ) as cd_count, congressional_district_46
      FROM ( civicrm_address
      INNER JOIN civicrm_value_district_information_7
        ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
      INNER JOIN civicrm_contact
        ON ( civicrm_contact.id = civicrm_address.contact_id )
      WHERE ( civicrm_contact.is_deleted != 1 )
        AND ( civicrm_address.is_primary = 1 )
        AND ( civicrm_value_district_information_7.congressional_district_46 IS NOT NULL )
        AND ( civicrm_value_district_information_7.congressional_district_46 != '' )
      GROUP BY congressional_district_46;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_cd );
    while ( $dao->fetch( ) ) {
      $contactCD[$dao->congressional_district_46] = $dao->cd_count;
    }
    $this->assign('contactCD', $contactCD);

    //get contact counts by County
    $sql_county = "
      SELECT COUNT( civicrm_contact.id ) as county_count, county_50
      FROM ( civicrm_address
      INNER JOIN civicrm_value_district_information_7
        ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
      INNER JOIN civicrm_contact
        ON ( civicrm_contact.id = civicrm_address.contact_id )
      WHERE ( civicrm_contact.is_deleted != 1 )
        AND ( civicrm_address.is_primary = 1 )
        AND ( civicrm_value_district_information_7.county_50 IS NOT NULL )
        AND ( civicrm_value_district_information_7.county_50 != '' )
      GROUP BY county_50;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_county );
    while ( $dao->fetch( ) ) {
      $contactCounty[$dao->county_50] = $dao->county_count;
    }
    $this->assign('contactCounty', $contactCounty);

    //get contact counts by Towns
    $sql_town = "
      SELECT COUNT( civicrm_contact.id ) as town_count, town_52
      FROM ( civicrm_address
      INNER JOIN civicrm_value_district_information_7
        ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
      INNER JOIN civicrm_contact
        ON ( civicrm_contact.id = civicrm_address.contact_id )
      WHERE ( civicrm_contact.is_deleted != 1 )
        AND ( civicrm_address.is_primary = 1 )
        AND ( civicrm_value_district_information_7.town_52 IS NOT NULL )
        AND ( civicrm_value_district_information_7.town_52 != '' )
      GROUP BY town_52;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_town );
    while ( $dao->fetch( ) ) {
      $contactTown[$dao->town_52] = $dao->town_count;
    }
    $this->assign('contactTown', $contactTown);

    //get contact counts by Wards
    $sql_ward = "
      SELECT COUNT( civicrm_contact.id ) as ward_count, ward_53
      FROM ( civicrm_address
      INNER JOIN civicrm_value_district_information_7
        ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
      INNER JOIN civicrm_contact
        ON ( civicrm_contact.id = civicrm_address.contact_id )
      WHERE ( civicrm_contact.is_deleted != 1 )
        AND ( civicrm_address.is_primary = 1 )
        AND ( civicrm_value_district_information_7.ward_53 IS NOT NULL )
        AND ( civicrm_value_district_information_7.ward_53 != '' )
      GROUP BY ward_53;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_ward );
    while ( $dao->fetch( ) ) {
      $contactWard[$dao->ward_53] = $dao->ward_count;
    }
    $this->assign('contactWard', $contactWard);

    //get contact counts by School District
    $sql_sc = "
      SELECT COUNT(civicrm_contact.id) as sc_count, DistrictName, school_district_54 as sc_id
      FROM (civicrm_address
      INNER JOIN civicrm_value_district_information_7
        ON (civicrm_address.id = civicrm_value_district_information_7.entity_id))
      INNER JOIN civicrm_contact
        ON (civicrm_contact.id = civicrm_address.contact_id)
      INNER JOIN nyss_schooldistricts nsd
        ON (LPAD(school_district_54, 3, 0) = nsd.Code)
      WHERE (civicrm_contact.is_deleted != 1)
        AND (civicrm_address.is_primary = 1)
        AND (civicrm_value_district_information_7.school_district_54 IS NOT NULL)
        AND (civicrm_value_district_information_7.school_district_54 != '')
      GROUP BY school_district_54, DistrictName;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_sc );
    while ( $dao->fetch( ) ) {
      $contactSC["{$dao->DistrictName} [{$dao->sc_id}]"] = $dao->sc_count;
    }
    $this->assign('contactSC', $contactSC);

    //get contact counts by Zip code
    $sql_zp = "
      SELECT COUNT( civicrm_contact.id ) as zip_count, postal_code
      FROM civicrm_address
      INNER JOIN civicrm_contact
        ON ( civicrm_contact.id = civicrm_address.contact_id )
      WHERE ( civicrm_contact.is_deleted != 1 )
        AND ( civicrm_address.is_primary = 1 )
        AND ( civicrm_address.postal_code != '' )
        AND ( civicrm_address.postal_code IS NOT NULL )
      GROUP BY postal_code
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_zp );
    while ( $dao->fetch( ) ) {
      $contactZip[$dao->postal_code] = $dao->zip_count;
    }
    $this->assign('contactZip', $contactZip);

    //get contact issue codes
    $sql_ic = "
      SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as ic_count
      FROM civicrm_entity_tag
      INNER JOIN civicrm_tag
        ON ( civicrm_entity_tag.tag_id = civicrm_tag.id )
      INNER JOIN civicrm_contact
        ON ( civicrm_contact.id = civicrm_entity_tag.entity_id )
      WHERE ( civicrm_entity_tag.entity_table LIKE '%civicrm_contact%' )
        AND ( civicrm_tag.parent_id != 292 )
        AND ( civicrm_tag.parent_id != 296 )
        AND ( civicrm_tag.is_tagset != 1 )
        AND ( civicrm_contact.is_deleted != 1 )
      GROUP BY name
      ORDER BY ic_count DESC;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_ic );
    while ( $dao->fetch( ) ) {
      $issueCodes[$dao->name] = $dao->ic_count;
    }
    $this->assign('issueCodes', $issueCodes);

    //get contact keywords
    $sql_kword = "
      SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as kword_count
      FROM civicrm_entity_tag
      INNER JOIN civicrm_tag
        ON ( civicrm_entity_tag.tag_id = civicrm_tag.id )
      INNER JOIN civicrm_contact
        ON ( civicrm_contact.id = civicrm_entity_tag.entity_id )
      WHERE ( civicrm_entity_tag.entity_table LIKE '%civicrm_contact%' )
        AND ( civicrm_tag.parent_id = 296 )
        AND ( civicrm_tag.is_tagset != 1 )
        AND ( civicrm_contact.is_deleted != 1 )
      GROUP BY name
      ORDER BY kword_count DESC;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_kword );
    while ( $dao->fetch( ) ) {
      $keywords[$dao->name] = $dao->kword_count;
    }
    $this->assign('keywords', $keywords);

    //get activity keywords
    $sql_akword = "
      SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as akword_count
      FROM civicrm_entity_tag
      INNER JOIN civicrm_tag
        ON ( civicrm_entity_tag.tag_id = civicrm_tag.id )
      INNER JOIN civicrm_activity
        ON ( civicrm_activity.id = civicrm_entity_tag.entity_id )
      WHERE ( civicrm_entity_tag.entity_table LIKE '%civicrm_activity%' )
        AND ( civicrm_tag.parent_id = 296 )
        AND ( civicrm_tag.is_tagset != 1 )
        AND ( civicrm_activity.is_deleted != 1 )
      GROUP BY name
      ORDER BY akword_count DESC
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_akword );
    while ( $dao->fetch( ) ) {
      $akeywords[$dao->name] = $dao->akword_count;
    }
    $this->assign('akeywords', $akeywords);

    //get case keywords
    $sql_ckword = "
      SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as ckword_count
      FROM civicrm_entity_tag
      INNER JOIN civicrm_tag
        ON ( civicrm_entity_tag.tag_id = civicrm_tag.id )
      INNER JOIN civicrm_case
        ON ( civicrm_case.id = civicrm_entity_tag.entity_id )
      WHERE ( civicrm_entity_tag.entity_table LIKE '%civicrm_case%' )
        AND ( civicrm_tag.parent_id = 296 )
        AND ( civicrm_tag.is_tagset != 1 )
        AND ( civicrm_case.is_deleted != 1 )
      GROUP BY name
      ORDER BY ckword_count DESC
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_ckword );
    while ( $dao->fetch( ) ) {
      $ckeywords[$dao->name] = $dao->ckword_count;
    }
    $this->assign('ckeywords', $ckeywords);

    //get contact positions
    $sql_pos = "
      SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as pos_count
      FROM civicrm_entity_tag
      INNER JOIN civicrm_tag
        ON ( civicrm_entity_tag.tag_id = civicrm_tag.id )
      INNER JOIN civicrm_contact
        ON ( civicrm_contact.id = civicrm_entity_tag.entity_id )
      WHERE ( civicrm_entity_tag.entity_table LIKE '%civicrm_contact%' )
        AND ( civicrm_tag.parent_id = 292 )
        AND ( civicrm_tag.is_tagset != 1 )
        AND ( civicrm_contact.is_deleted != 1 )
      GROUP BY name
      ORDER BY pos_count DESC;
    ";
    $dao = CRM_Core_DAO::executeQuery( $sql_pos );
    while ( $dao->fetch( ) ) {
      $positions[$dao->name] = $dao->pos_count;
    }
    $this->assign('positions', $positions);

    return parent::run( );
  }
}

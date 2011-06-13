<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Page.php';
require_once 'CRM/Core/DAO.php';

/**
 * Main page for activity dashlet
 *
 */
class CRM_Dashlet_Page_DistrictStats extends CRM_Core_Page 
{
    /**
     * Assemble database stats
     *
     * @return none
     *
     * @access public
     */
    function run( ) {
        
		//get contact counts by type
		$allContacts = 0;
		$sql_contacts = "SELECT contact_type, COUNT( id ) AS ct_count
						 FROM civicrm_contact
						 WHERE is_deleted != 1
						 GROUP BY contact_type;";
		$dao = CRM_Core_DAO::executeQuery( $sql_contacts );
		while ( $dao->fetch( ) ) {
            $contactTypes[$dao->contact_type] = $dao->ct_count;
			$allContacts = $allContacts + $dao->ct_count;
        }
		$contactTypes['All Contacts'] = $allContacts;
		
		//get trashed contacts
		$sql_trashed = "SELECT COUNT( id ) AS trashed_count
						 FROM civicrm_contact
						 WHERE is_deleted = 1;";
		$trashed = CRM_Core_DAO::singleValueQuery( $sql_trashed );
		$contactTypes['Trashed Contacts'] = $trashed;
		
		//get contacts with emails
		$sql_emails = "SELECT COUNT( c.id ) AS email_count
					   FROM civicrm_contact c
					     JOIN civicrm_email ce ON ( c.id = ce.contact_id AND ce.is_primary = 1 )
					   WHERE is_deleted != 1;";
		$contactEmails = CRM_Core_DAO::singleValueQuery( $sql_emails );
		$contactTypes['Contacts with Emails'] = $contactEmails;
		
		$this->assign('contactTypes', $contactTypes);
		//CRM_Core_Error::debug($contactTypes);
		
		//get gender counts
		$sql_genders = "SELECT gender_id, COUNT( id ) AS gender_count
						FROM civicrm_contact
						WHERE is_deleted != 1
						GROUP BY gender_id;";
		$dao = CRM_Core_DAO::executeQuery( $sql_genders );
		while ( $dao->fetch( ) ) {
            $contactGenders[$dao->gender_id] = $dao->gender_count;
        }
		$this->assign('contactGenders', $contactGenders);
		
		//get contact counts by Senate District
		$sql_sd = "SELECT COUNT( civicrm_contact.id ) as sd_count, ny_senate_district_47
  				   FROM ( civicrm_address
           			INNER JOIN civicrm_value_district_information_7 
					 ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
       			   INNER JOIN civicrm_contact
       				ON ( civicrm_contact.id = civicrm_address.contact_id )
 				   WHERE ( civicrm_contact.is_deleted != 1 ) 
				    AND ( civicrm_address.is_primary = 1 )
					AND ( civicrm_value_district_information_7.ny_senate_district_47 IS NOT NULL )
					AND ( civicrm_value_district_information_7.ny_senate_district_47 != '' )
				   GROUP BY ny_senate_district_47;";
		$dao = CRM_Core_DAO::executeQuery( $sql_sd );
		while ( $dao->fetch( ) ) {
            $contactSD[$dao->ny_senate_district_47] = $dao->sd_count;
        }
		$this->assign('contactSD', $contactSD);
		
		//get contact counts by Assembly District
		$sql_ad = "SELECT COUNT( civicrm_contact.id ) as ad_count, ny_assembly_district_48
  				   FROM ( civicrm_address
           			INNER JOIN civicrm_value_district_information_7 
					 ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
       			   INNER JOIN civicrm_contact
       				ON ( civicrm_contact.id = civicrm_address.contact_id )
 				   WHERE ( civicrm_contact.is_deleted != 1 ) 
				    AND ( civicrm_address.is_primary = 1 )
					AND ( civicrm_value_district_information_7.ny_assembly_district_48 IS NOT NULL )
					AND ( civicrm_value_district_information_7.ny_assembly_district_48 != '' )
				   GROUP BY ny_assembly_district_48;";
		$dao = CRM_Core_DAO::executeQuery( $sql_ad );
		while ( $dao->fetch( ) ) {
            $contactAD[$dao->ny_assembly_district_48] = $dao->ad_count;
        }
		$this->assign('contactAD', $contactAD);
		
		//get contact counts by Election District
		$sql_ed = "SELECT COUNT( civicrm_contact.id ) as ed_count, election_district_49
  				   FROM ( civicrm_address
           			INNER JOIN civicrm_value_district_information_7 
					 ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
       			   INNER JOIN civicrm_contact
       				ON ( civicrm_contact.id = civicrm_address.contact_id )
 				   WHERE ( civicrm_contact.is_deleted != 1 ) 
				    AND ( civicrm_address.is_primary = 1 )
					AND ( civicrm_value_district_information_7.election_district_49 IS NOT NULL )
					AND ( civicrm_value_district_information_7.election_district_49 != '' )
				   GROUP BY election_district_49;";
		$dao = CRM_Core_DAO::executeQuery( $sql_ed );
		while ( $dao->fetch( ) ) {
            $contactED[$dao->election_district_49] = $dao->ed_count;
        }
		$this->assign('contactED', $contactED);
		//CRM_Core_Error::debug($contactAD);
		
		//get contact counts by Congressional District
		$sql_cd = "SELECT COUNT( civicrm_contact.id ) as cd_count, congressional_district_46
  				   FROM ( civicrm_address
           			INNER JOIN civicrm_value_district_information_7 
					 ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
       			   INNER JOIN civicrm_contact
       				ON ( civicrm_contact.id = civicrm_address.contact_id )
 				   WHERE ( civicrm_contact.is_deleted != 1 ) 
				    AND ( civicrm_address.is_primary = 1 )
					AND ( civicrm_value_district_information_7.congressional_district_46 IS NOT NULL )
					AND ( civicrm_value_district_information_7.congressional_district_46 != '' )
				   GROUP BY congressional_district_46;";
		$dao = CRM_Core_DAO::executeQuery( $sql_cd );
		while ( $dao->fetch( ) ) {
            $contactCD[$dao->congressional_district_46] = $dao->cd_count;
        }
		$this->assign('contactCD', $contactCD);
		
		//get contact counts by County
		$sql_county = "SELECT COUNT( civicrm_contact.id ) as county_count, county_50
  				   FROM ( civicrm_address
           			INNER JOIN civicrm_value_district_information_7 
					 ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
       			   INNER JOIN civicrm_contact
       				ON ( civicrm_contact.id = civicrm_address.contact_id )
 				   WHERE ( civicrm_contact.is_deleted != 1 ) 
				    AND ( civicrm_address.is_primary = 1 )
					AND ( civicrm_value_district_information_7.county_50 IS NOT NULL )
					AND ( civicrm_value_district_information_7.county_50 != '' )
				   GROUP BY county_50;";
		$dao = CRM_Core_DAO::executeQuery( $sql_county );
		while ( $dao->fetch( ) ) {
            $contactCounty[$dao->county_50] = $dao->county_count;
        }
		$this->assign('contactCounty', $contactCounty);
		
		//get contact counts by Towns
		$sql_town = "SELECT COUNT( civicrm_contact.id ) as town_count, town_52
  				   FROM ( civicrm_address
           			INNER JOIN civicrm_value_district_information_7 
					 ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
       			   INNER JOIN civicrm_contact
       				ON ( civicrm_contact.id = civicrm_address.contact_id )
 				   WHERE ( civicrm_contact.is_deleted != 1 ) 
				    AND ( civicrm_address.is_primary = 1 )
					AND ( civicrm_value_district_information_7.town_52 IS NOT NULL )
					AND ( civicrm_value_district_information_7.town_52 != '' )
				   GROUP BY town_52;";
		$dao = CRM_Core_DAO::executeQuery( $sql_town );
		while ( $dao->fetch( ) ) {
            $contactTown[$dao->town_52] = $dao->town_count;
        }
		$this->assign('contactTown', $contactTown);
		
		//get contact counts by Wards
		$sql_ward = "SELECT COUNT( civicrm_contact.id ) as ward_count, ward_53
  				   FROM ( civicrm_address
           			INNER JOIN civicrm_value_district_information_7 
					 ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
       			   INNER JOIN civicrm_contact
       				ON ( civicrm_contact.id = civicrm_address.contact_id )
 				   WHERE ( civicrm_contact.is_deleted != 1 ) 
				    AND ( civicrm_address.is_primary = 1 )
					AND ( civicrm_value_district_information_7.ward_53 IS NOT NULL )
					AND ( civicrm_value_district_information_7.ward_53 != '' )
				   GROUP BY ward_53;";
		$dao = CRM_Core_DAO::executeQuery( $sql_ward );
		while ( $dao->fetch( ) ) {
            $contactWard[$dao->ward_53] = $dao->ward_count;
        }
		$this->assign('contactWard', $contactWard);

		//get contact counts by School District
		$sql_sc = "SELECT COUNT( civicrm_contact.id ) as sc_count, school_district_54
  				   FROM ( civicrm_address
           			INNER JOIN civicrm_value_district_information_7 
					 ON ( civicrm_address.id = civicrm_value_district_information_7.entity_id ) )
       			   INNER JOIN civicrm_contact
       				ON ( civicrm_contact.id = civicrm_address.contact_id )
 				   WHERE ( civicrm_contact.is_deleted != 1 ) 
				    AND ( civicrm_address.is_primary = 1 )
					AND ( civicrm_value_district_information_7.school_district_54 IS NOT NULL )
					AND ( civicrm_value_district_information_7.school_district_54 != '' )
				   GROUP BY school_district_54;";
		$dao = CRM_Core_DAO::executeQuery( $sql_sc );
		while ( $dao->fetch( ) ) {
            $contactSC[$dao->school_district_54] = $dao->sc_count;
        }
		$this->assign('contactSC', $contactSC);
		
		//get contact counts by Zip code
		$sql_zp = "SELECT COUNT( civicrm_contact.id ) as zip_count, postal_code
  				   FROM civicrm_address
       			   INNER JOIN civicrm_contact
       				ON ( civicrm_contact.id = civicrm_address.contact_id )
 				   WHERE ( civicrm_contact.is_deleted != 1 ) 
				    AND ( civicrm_address.is_primary = 1 )
					AND ( civicrm_address.postal_code != '' )
					AND ( civicrm_address.postal_code IS NOT NULL )
				   GROUP BY postal_code";
		$dao = CRM_Core_DAO::executeQuery( $sql_zp );
		while ( $dao->fetch( ) ) {
            $contactZip[$dao->postal_code] = $dao->zip_count;
        }
		$this->assign('contactZip', $contactZip);
				
		//get contact issue codes
		$sql_ic = "SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as ic_count
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
				   ORDER BY ic_count DESC;";
		$dao = CRM_Core_DAO::executeQuery( $sql_ic );
		while ( $dao->fetch( ) ) {
            $issueCodes[$dao->name] = $dao->ic_count;
        }
		$this->assign('issueCodes', $issueCodes);

		//get contact keywords
		$sql_kword = "SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as kword_count
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
				   ORDER BY kword_count DESC;";
		$dao = CRM_Core_DAO::executeQuery( $sql_kword );
		while ( $dao->fetch( ) ) {
            $keywords[$dao->name] = $dao->kword_count;
        }
		$this->assign('keywords', $keywords);
		
		//get activity keywords
		$sql_akword = "SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as akword_count
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
				   ORDER BY akword_count DESC";
		$dao = CRM_Core_DAO::executeQuery( $sql_akword );
		while ( $dao->fetch( ) ) {
            $akeywords[$dao->name] = $dao->akword_count;
        }
		$this->assign('akeywords', $akeywords);
		
		//get case keywords
		$sql_ckword = "SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as ckword_count
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
				   ORDER BY ckword_count DESC";
		$dao = CRM_Core_DAO::executeQuery( $sql_ckword );
		while ( $dao->fetch( ) ) {
            $ckeywords[$dao->name] = $dao->ckword_count;
        }
		$this->assign('ckeywords', $ckeywords);
		
		//get contact positions
		$sql_pos = "SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as pos_count
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
				   ORDER BY pos_count DESC;";
		$dao = CRM_Core_DAO::executeQuery( $sql_pos );
		while ( $dao->fetch( ) ) {
            $positions[$dao->name] = $dao->pos_count;
        }
		$this->assign('positions', $positions);

        return parent::run( );
    }
}
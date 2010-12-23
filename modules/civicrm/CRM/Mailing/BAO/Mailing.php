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
require_once 'Mail/mime.php';
require_once 'CRM/Utils/Mail.php';

require_once 'CRM/Contact/BAO/SavedSearch.php';
require_once 'CRM/Contact/BAO/Query.php';
require_once 'CRM/Contact/BAO/Group.php';

require_once 'CRM/Mailing/DAO/Mailing.php';
require_once 'CRM/Mailing/DAO/Group.php';
require_once 'CRM/Mailing/Event/BAO/Queue.php';
require_once 'CRM/Mailing/Event/BAO/Delivered.php';
require_once 'CRM/Mailing/Event/BAO/Bounce.php';
require_once 'CRM/Mailing/BAO/TrackableURL.php';
require_once 'CRM/Mailing/BAO/Component.php';
require_once 'CRM/Mailing/BAO/Spool.php';

class CRM_Mailing_BAO_Mailing extends CRM_Mailing_DAO_Mailing 
{
    /**
     * An array that holds the complete templates
     * including any headers or footers that need to be prepended
     * or appended to the body
     */
    private $preparedTemplates = null;

    /**
     * An array that holds the complete templates
     * including any headers or footers that need to be prepended
     * or appended to the body
     */
    private $templates = null;

    /**
     * An array that holds the tokens that are specifically found in our text and html bodies
     */
    private $tokens = null;

    /**
     * The header associated with this mailing
     */
    private $header = null;

    /**
     * The footer associated with this mailing
     */
    private $footer = null;


    /**
     * The HTML content of the message
     */
    private $html = null;

    /**
     * The text content of the message
     */
    private $text = null;

    /**
     * Cached BAO for the domain
     */
    private $_domain = null;


    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }

    /**
     * Find all intended recipients of a mailing
     *
     * @param int  $job_id            Job ID
     * @param bool $includeDelivered  Whether to include the recipients who already got the mailing
     * @return object                 A DAO loaded with results of the form (email_id, contact_id)
     */
    function &getRecipientsObject($job_id, $includeDelivered = false, $offset = NULL, $limit = NULL) 
    {
        $eq = self::getRecipients($job_id, $includeDelivered, $this->id, $offset, $limit);
        return $eq;
    }
    
    function &getRecipientsCount($job_id, $includeDelivered = false, $mailing_id = null) 
    {
        $eq = self::getRecipients($job_id, $includeDelivered, $mailing_id);
        return $eq->N;
    }
    
    function &getRecipients($job_id, $includeDelivered = false, $mailing_id = null,
                            $offset = NULL, $limit = NULL) 
    {
        $mailingGroup = new CRM_Mailing_DAO_Group();
        
        $mailing    = CRM_Mailing_BAO_Mailing::getTableName();
        $job        = CRM_Mailing_BAO_Job::getTableName();
        $mg         = CRM_Mailing_DAO_Group::getTableName();
        $eq         = CRM_Mailing_Event_DAO_Queue::getTableName();
        $ed         = CRM_Mailing_Event_DAO_Delivered::getTableName();
        $eb         = CRM_Mailing_Event_DAO_Bounce::getTableName();
        
        $email      = CRM_Core_DAO_Email::getTableName();
        $contact    = CRM_Contact_DAO_Contact::getTableName();

        require_once 'CRM/Contact/DAO/Group.php';
        $group      = CRM_Contact_DAO_Group::getTableName();
        $g2contact  = CRM_Contact_DAO_GroupContact::getTableName();
      
        /* Create a temp table for contact exclusion */
        $mailingGroup->query(
            "CREATE TEMPORARY TABLE X_$job_id 
            (contact_id int primary key) 
            ENGINE=HEAP"
        );

        /* Add all the members of groups excluded from this mailing to the temp
         * table */
        $excludeSubGroup =
                    "INSERT INTO        X_$job_id (contact_id)
                    SELECT  DISTINCT    $g2contact.contact_id
                    FROM                $g2contact
                    INNER JOIN          $mg
                            ON          $g2contact.group_id = $mg.entity_id AND $mg.entity_table = '$group'
                    WHERE
                                        $mg.mailing_id = {$mailing_id}
                        AND             $g2contact.status = 'Added'
                        AND             $mg.group_type = 'Exclude'";
        $mailingGroup->query($excludeSubGroup);
        
        /* Add all unsubscribe members of base group from this mailing to the temp
         * table */ 
        $unSubscribeBaseGroup =
                    "INSERT INTO        X_$job_id (contact_id)
                    SELECT  DISTINCT    $g2contact.contact_id
                    FROM                $g2contact
                    INNER JOIN          $mg
                            ON          $g2contact.group_id = $mg.entity_id AND $mg.entity_table = '$group'
                    WHERE
                                        $mg.mailing_id = {$mailing_id}
                        AND             $g2contact.status = 'Removed'
                        AND             $mg.group_type = 'Base'";
        
        
        $mailingGroup->query($unSubscribeBaseGroup);
        
        /* Add all the (intended) recipients of an excluded prior mailing to
         * the temp table */
        $excludeSubMailing = 
                    "INSERT IGNORE INTO X_$job_id (contact_id)
                    SELECT  DISTINCT    $eq.contact_id
                    FROM                $eq
                    INNER JOIN          $job
                            ON          $eq.job_id = $job.id
                    INNER JOIN          $mg
                            ON          $job.mailing_id = $mg.entity_id AND $mg.entity_table = '$mailing'
                    WHERE
                                        $mg.mailing_id = {$mailing_id}
                        AND             $mg.group_type = 'Exclude'";
        $mailingGroup->query($excludeSubMailing);
        
        $ss = new CRM_Core_DAO();
        $ss->query(
                "SELECT             $group.saved_search_id as saved_search_id,
                                    $group.id as id
                FROM                $group
                INNER JOIN          $mg
                        ON          $mg.entity_id = $group.id
                WHERE               $mg.entity_table = '$group'
                    AND             $mg.group_type = 'Exclude'
                    AND             $mg.mailing_id = {$mailing_id}
                    AND             $group.saved_search_id IS NOT null");

        $whereTables = array( );
        while ( $ss->fetch( ) ) {
            /* run the saved search query and dump result contacts into the temp
             * table */
            $tables = array($contact => 1);
            $sql = CRM_Contact_BAO_SavedSearch::contactIDsSQL( $ss->saved_search_id );
            $sql = $sql. " AND contact_a.id NOT IN ( 
                              SELECT contact_id FROM $g2contact 
                              WHERE $g2contact.group_id = {$ss->id} AND $g2contact.status = 'Removed')"; 
            
            $mailingGroup->query( "INSERT IGNORE INTO X_$job_id (contact_id) $sql" );
        }

        /* Get all the group contacts we want to include */
        
        $mailingGroup->query(
            "CREATE TEMPORARY TABLE I_$job_id 
            (email_id int, contact_id int primary key)
            ENGINE=HEAP"
        );
        
        /* Get the group contacts, but only those which are not in the
         * exclusion temp table */

        /* Get the emails with no override */
        
        $query =    "REPLACE INTO       I_$job_id (email_id, contact_id)
                    SELECT DISTINCT     $email.id as email_id,
                                        $contact.id as contact_id
                    FROM                $email
                    INNER JOIN          $contact
                            ON          $email.contact_id = $contact.id
                    INNER JOIN          $g2contact
                            ON          $contact.id = $g2contact.contact_id
                    INNER JOIN          $mg
                            ON          $g2contact.group_id = $mg.entity_id
                                AND     $mg.entity_table = '$group'
                    LEFT JOIN           X_$job_id
                            ON          $contact.id = X_$job_id.contact_id
                    WHERE           
                                       ($mg.group_type = 'Include' OR $mg.group_type = 'Base')
                        AND             $mg.search_id IS NULL
                        AND             $g2contact.status = 'Added'
                        AND             $g2contact.email_id IS null
                        AND             $contact.do_not_email = 0
                        AND             $contact.is_opt_out = 0
                        AND             $contact.is_deceased = 0
                        AND            ($email.is_bulkmail = 1 OR $email.is_primary = 1)
                        AND             $email.on_hold = 0
                        AND             $mg.mailing_id = {$mailing_id}
                        AND             X_$job_id.contact_id IS null
                    ORDER BY $email.is_bulkmail";
        $mailingGroup->query($query);


        /* Query prior mailings */
        $mailingGroup->query(
                    "REPLACE INTO       I_$job_id (email_id, contact_id)
                    SELECT DISTINCT     $email.id as email_id,
                                        $contact.id as contact_id
                    FROM                $email
                    INNER JOIN          $contact
                            ON          $email.contact_id = $contact.id
                    INNER JOIN          $eq
                            ON          $eq.contact_id = $contact.id
                    INNER JOIN          $job
                            ON          $eq.job_id = $job.id
                    INNER JOIN          $mg
                            ON          $job.mailing_id = $mg.entity_id AND $mg.entity_table = '$mailing'
                    LEFT JOIN           X_$job_id
                            ON          $contact.id = X_$job_id.contact_id
                    WHERE
                                       ($mg.group_type = 'Include' OR $mg.group_type = 'Base')
                        AND             $contact.do_not_email = 0
                        AND             $contact.is_opt_out = 0
                        AND             $contact.is_deceased = 0
                        AND            ($email.is_bulkmail = 1 OR $email.is_primary = 1)
                        AND             $email.on_hold = 0
                        AND             $mg.mailing_id = {$mailing_id}
                        AND             X_$job_id.contact_id IS null
                    ORDER BY $email.is_bulkmail");

        /* Construct the saved-search queries */
        $ss->query("SELECT          $group.saved_search_id as saved_search_id,
                                    $group.id as id
                    FROM            $group
                    INNER JOIN      $mg
                            ON      $mg.entity_id = $group.id
                                AND $mg.entity_table = '$group'
                    WHERE               
                                    $mg.group_type = 'Include'
                        AND         $mg.search_id IS NULL
                        AND         $mg.mailing_id = {$mailing_id}
                        AND         $group.saved_search_id IS NOT null");

        $whereTables = array( );
        while ($ss->fetch()) {
            $tables = array($contact => 1, $location => 1, $email => 1);
            list( $from, $where ) = CRM_Contact_BAO_SavedSearch::fromWhereEmail( $ss->saved_search_id );
            $where = trim( $where );
            if ( $where ) {
                $where = " AND $where ";
            }
            $ssq = "INSERT IGNORE INTO  I_$job_id (email_id, contact_id)
                    SELECT DISTINCT     $email.id as email_id,
                                        contact_a.id as contact_id 
                    $from
                    LEFT JOIN           X_$job_id
                            ON          contact_a.id = X_$job_id.contact_id
                    WHERE           
                                        contact_a.do_not_email = 0
                        AND             contact_a.is_opt_out = 0
                        AND             contact_a.is_deceased = 0
                        AND             ($email.is_bulkmail = 1 OR $email.is_primary = 1)
                        AND             $email.on_hold = 0
                                        $where
                        AND             contact_a.id NOT IN ( 
                                          SELECT contact_id FROM $g2contact 
                                          WHERE $g2contact.group_id = {$ss->id} AND $g2contact.status = 'Removed') 
                        AND             X_$job_id.contact_id IS null
                    ORDER BY $email.is_bulkmail";
            $mailingGroup->query($ssq);
        }

        /**
         * Construct the filtered search queries
         */
        $query = "
SELECT search_id, search_args, entity_id
FROM   $mg
WHERE  $mg.search_id IS NOT NULL
AND    $mg.mailing_id = {$mailing_id}
";
        $dao = CRM_Core_DAO::executeQuery( $query );
        require_once 'CRM/Contact/BAO/SearchCustom.php';
        while ( $dao->fetch( ) ) {
            $customSQL = CRM_Contact_BAO_SearchCustom::civiMailSQL( $dao->search_id,
                                                                    $dao->search_args,
                                                                    $dao->entity_id );
            $query =    "REPLACE INTO       I_$job_id (email_id, contact_id)
                         $customSQL";
            $mailingGroup->query($query);
        }

        /* Get the emails with only location override */
        $query =    "REPLACE INTO       I_$job_id (email_id, contact_id)
                    SELECT DISTINCT     $email.id as local_email_id,
                                        $contact.id as contact_id
                    FROM                $email
                    INNER JOIN          $contact
                            ON          $email.contact_id = $contact.id
                    INNER JOIN          $g2contact
                            ON          $contact.id = $g2contact.contact_id
                    INNER JOIN          $mg
                            ON          $g2contact.group_id = $mg.entity_id
                    LEFT JOIN           X_$job_id
                            ON          $contact.id = X_$job_id.contact_id
                    WHERE           
                                        $mg.entity_table = '$group'
                        AND             $mg.group_type = 'Include'
                        AND             $g2contact.status = 'Added'
                        AND             $g2contact.email_id is null
                        AND             $contact.do_not_email = 0
                        AND             $contact.is_opt_out = 0
                        AND             $contact.is_deceased = 0
                        AND             ($email.is_bulkmail = 1 OR $email.is_primary = 1)
                        AND             $email.on_hold = 0
                        AND             $mg.mailing_id = {$mailing_id}
                        AND             X_$job_id.contact_id IS null
                    ORDER BY $email.is_bulkmail";
        $mailingGroup->query($query);
                    
        /* Get the emails with full override */
        $mailingGroup->query(
                    "REPLACE INTO       I_$job_id (email_id, contact_id)
                    SELECT DISTINCT     $email.id as email_id,
                                        $contact.id as contact_id
                    FROM                $email
                    INNER JOIN          $g2contact
                            ON          $email.id = $g2contact.email_id
                    INNER JOIN          $contact
                            ON          $contact.id = $g2contact.contact_id
                    INNER JOIN          $mg
                            ON          $g2contact.group_id = $mg.entity_id
                    LEFT JOIN           X_$job_id
                            ON          $contact.id = X_$job_id.contact_id
                    WHERE           
                                        $mg.entity_table = '$group'
                        AND             $mg.group_type = 'Include'
                        AND             $g2contact.status = 'Added'
                        AND             $g2contact.email_id IS NOT null
                        AND             $contact.do_not_email = 0
                        AND             $contact.is_opt_out = 0
                        AND             $contact.is_deceased = 0
                        AND             ($email.is_bulkmail = 1 OR $email.is_primary = 1)
                        AND             $email.on_hold = 0
                        AND             $mg.mailing_id = {$mailing_id}
                        AND             X_$job_id.contact_id IS null
                    ORDER BY $email.is_bulkmail");
                        
        $results = array();

        $eq = new CRM_Mailing_Event_BAO_Queue();
        
        require_once 'CRM/Contact/BAO/Contact/Permission.php';
        list( $aclFrom, $aclWhere ) = CRM_Contact_BAO_Contact_Permission::cacheClause( );
        $aclWhere = $aclWhere ? "WHERE {$aclWhere}" : '';
        $limitString = null;
        if ( $limit && $offset !== null) {
            $limitString = "LIMIT $offset, $limit";
        }

        $eq->query("SELECT i.contact_id, i.email_id 
                    FROM  civicrm_contact contact_a
                    INNER JOIN I_$job_id i ON contact_a.id = i.contact_id
                    {$aclFrom}
                    {$aclWhere}
                    ORDER BY i.contact_id, i.email_id
                    $limitString");

        /* Delete the temp table */
        $mailingGroup->reset();
        $mailingGroup->query("DROP TEMPORARY TABLE X_$job_id");
        $mailingGroup->query("DROP TEMPORARY TABLE I_$job_id");

        return $eq;
    }
    
    private function _getMailingGroupIds( $type = 'Include' ) 
    {
        $mailingGroup = new CRM_Mailing_DAO_Group();
        $group = CRM_Contact_DAO_Group::getTableName();
        if ( ! isset( $this->id ) ) {
            // we're just testing tokens, so return any group
            $query = "SELECT   id AS entity_id
                      FROM     $group
                      ORDER BY id
                      LIMIT 1";
        } else {
            $query = "SELECT entity_id
                      FROM   $mg
                      WHERE  mailing_id = {$this->id}
                      AND    group_type = '$type'
                      AND    entity_table = '$group'";
        }
        $mailingGroup->query( $query );
        
        $groupIds = array( );
        while ( $mailingGroup->fetch( ) ) {
            $groupIds[] = $mailingGroup->entity_id;
        }
        
        return $groupIds;
    }


    /**
     * 
     * Returns the regex patterns that are used for preparing the text and html templates
     *   
     * @access private
     * 
     **/
    private function &getPatterns($onlyHrefs = false) 
    {
        
        $patterns = array();
        
        $protos = '(https?|ftp)';
        $letters = '\w';
        $gunk = '\{\}/#~:.?+=&;%@!\,\-';
        $punc = '.:?\-';
        $any = "{$letters}{$gunk}{$punc}";
        if ( $onlyHrefs ) {
            $pattern = "\\bhref[ ]*=[ ]*([\"'])?(($protos:[$any]+?(?=[$punc]*[^$any]|$)))([\"'])?";
        } else {
            $pattern = "\\b($protos:[$any]+?(?=[$punc]*[^$any]|$))";
        }
        
        $patterns[] = $pattern;
        $patterns[] = '\\\\\{\w+\.\w+\\\\\}|\{\{\w+\.\w+\}\}';
        $patterns[] = '\{\w+\.\w+\}';
        
        $patterns = '{'.join('|',$patterns).'}im';
        
        return $patterns;
    }

    /**
     *  returns an array that denotes the type of token that we are dealing with
     *  we use the type later on when we are doing a token replcement lookup
     *
     *  @param string $token       The token for which we will be doing adata lookup
     *  
     *  @return array $funcStruct  An array that holds the token itself and the type.
     *                             the type will tell us which function to use for the data lookup
     *                             if we need to do a lookup at all
     */
    function &getDataFunc($token) 
    {
        static $_categories = null;
        static $_categoryString = null;
        if ( ! $_categories ) {
            $_categories = array( 'domain'  => null, 
                                  'action'  => null,
                                  'mailing' => null,
                                  'contact' => null );

            require_once 'CRM/Utils/Hook.php';
            CRM_Utils_Hook::tokens( $_categories );
            $_categoryString = implode( '|', array_keys( $_categories ) );
        }

        $funcStruct = array('type' => null,'token' => $token);
        $matches = array();
        if ( ( preg_match('/^href/i',$token) || preg_match('/^http/i',$token) ) ) {
            // it is a url so we need to check to see if there are any tokens embedded
            // if so then call this function again to get the token dataFunc
            // and assign the type 'embedded'  so that the data retrieving function
            // will know what how to handle this token.
            if ( preg_match_all('/(\{\w+\.\w+\})/', $token, $matches) ) {
                $funcStruct['type'] = 'embedded_url';
                $funcStruct['embed_parts'] = $funcStruct['token'] = array( );
                foreach ( $matches[1] as $match ) {
                    $preg_token = '/'.preg_quote($match,'/').'/';
                    $list = preg_split($preg_token,$token,2);
                    $funcStruct['embed_parts'][] = $list[0];
                    $token = $list[1];
                    $funcStruct['token'][] = $this->getDataFunc($match);
                }
                // fixed truncated url, CRM-7113
                if ( $token ) {
                    $funcStruct['embed_parts'][] = $token;
                }
            } else {
                $funcStruct['type'] = 'url';
            }
            
        } else if ( preg_match('/^\{(' . $_categoryString . ')\.(\w+)\}$/', $token, $matches) ) {
            $funcStruct['type'] = $matches[1];
            $funcStruct['token'] = $matches[2];
        } else if(preg_match('/\\\\\{(\w+\.\w+)\\\\\}|\{\{(\w+\.\w+)\}\}/', $token, $matches) ) {
            // we are an escaped token
            // so remove the escape chars
            $unescaped_token = preg_replace('/\{\{|\}\}|\\\\\{|\\\\\}/','',$matches[0]);
            $funcStruct['token'] = '{'.$unescaped_token.'}';
        }
        return $funcStruct;
    }

    /**
     * 
     * Prepares the text and html templates
     * for generating the emails and returns a copy of the
     * prepared templates
     *   
     * @access private
     * 
     **/
    private function getPreparedTemplates( )
    {
        if ( !$this->preparedTemplates ) {
            $patterns['html'] = $this->getPatterns(true);
            $patterns['subject'] = $patterns['text'] = $this->getPatterns();
            $templates = $this->getTemplates();
            
            $this->preparedTemplates = array();
            
            foreach (array('html','text', 'subject') as $key) {
                if (!isset($templates[$key])) {
                    continue;
                }
                
                $matches = array();
                $tokens = array();
                $split_template = array();
                
                $email = $templates[$key];
                preg_match_all($patterns[$key],$email,$matches,PREG_PATTERN_ORDER);
                foreach ($matches[0] as $idx => $token) {
                    $preg_token = '/'.preg_quote($token,'/').'/im';
                    list($split_template[],$email) = preg_split($preg_token,$email,2);
                    array_push($tokens, $this->getDataFunc($token));
                }
                if ($email) {
                    $split_template[] = $email;
                }
                $this->preparedTemplates[$key]['template'] = $split_template;
                $this->preparedTemplates[$key]['tokens'] = $tokens;
            }
        }
        return($this->preparedTemplates);
    }

    /**
     * 
     *  Retrieve a ref to an array that holds the email and text templates for this email
     *  assembles the complete template including the header and footer
     *  that the user has uploaded or declared (if they have dome that)
     *  
     *  
     * @return array reference to an assoc array
     * @access private
     * 
     **/
    private function &getTemplates( )
    {
        require_once('CRM/Utils/String.php');
        if (!$this->templates) {
          $this->getHeaderFooter();
          $this->templates = array(  );
          
          if ( $this->body_text ) {
              $template = array();
              if ( $this->header ) {
                  $template[] = $this->header->body_text;
              }

              $template[] = $this->body_text;
              
              if ( $this->footer ) {
                  $template[] = $this->footer->body_text;
              }

              $this->templates['text'] = join("\n",$template);
          }

          if ( $this->body_html ) {
              
              $template = array();
              if ( $this->header ) {
                  $template[] = $this->header->body_html;
              }

              $template[] = $this->body_html;

              if ( $this->footer ) {
                  $template[] = $this->footer->body_html;
              }
              
              $this->templates['html'] = join("\n",$template);
    
              // this is where we create a text template from the html template if the text template did not exist
              // this way we ensure that every recipient will receive an email even if the pref is set to text and the
              // user uploads an html email only
              if ( !$this->body_text ) {
                  $this->templates['text'] = CRM_Utils_String::htmlToText( $this->templates['html'] );
              }
          }

          if ( $this->subject ) {
              $template = array();
              $template[] = $this->subject;
              $this->templates['subject'] = join("\n",$template);
          }
      }
      return $this->templates;    
    }

    /**
     * 
     *  Retrieve a ref to an array that holds all of the tokens in the email body
     *  where the keys are the type of token and the values are ordinal arrays
     *  that hold the token names (even repeated tokens) in the order in which
     *  they appear in the body of the email.
     *  
     *  note: the real work is done in the _getTokens() function
     *  
     *  this function needs to have some sort of a body assigned
     *  either text or html for this to have any meaningful impact
     *  
     * @return array               reference to an assoc array
     * @access public
     * 
     **/
    public function &getTokens( ) 
    {
        if (!$this->tokens) {
            
            $this->tokens = array( 'html' => array(), 'text' => array(), 'subject' => array() );
            
            if ($this->body_html) {
                $this->_getTokens('html');
            }
            
            if ($this->body_text) {
                $this->_getTokens('text');
            }

            if ($this->subject) {
                $this->_getTokens('subject');
            }
        }
        return $this->tokens;      
    }
    
    /**
     *
     *  _getTokens parses out all of the tokens that have been
     *  included in the html and text bodies of the email
     *  we get the tokens and then separate them into an
     *  internal structure named tokens that has the same
     *  form as the static tokens property(?) of the CRM_Utils_Token class.
     *  The difference is that there might be repeated token names as we want the
     *  structures to represent the order in which tokens were found from left to right, top to bottom.
     *
     *  
     * @param str $prop     name of the property that holds the text that we want to scan for tokens (html, text)
     * @access private
     * @return void
     */
    private function _getTokens( $prop ) 
    {
        $templates = $this->getTemplates();
        $matches = array();
        preg_match_all( '/(?<!\{|\\\\)\{(\w+\.\w+)\}(?!\})/',
                        $templates[$prop],
                        $matches,
                        PREG_PATTERN_ORDER);
        
        if ( $matches[1] ) {
            foreach ( $matches[1] as $token ) {
                list($type,$name) = preg_split( '/\./', $token, 2 );
                if ( $name ) {
                    if ( ! isset( $this->tokens[$prop][$type] ) ) {
                        $this->tokens[$prop][$type] = array( );
                    }
                    $this->tokens[$prop][$type][] = $name;
                }
            }
        }
    }

    /**
     * Generate an event queue for a test job 
     *
     * @params array $params contains form values
     * @return void
     * @access public
     */
    public function getTestRecipients($testParams) 
    {
        if (array_key_exists($testParams['test_group'], CRM_Core_PseudoConstant::group())) {
            $group = new CRM_Contact_DAO_Group();
            $group->id = $testParams['test_group'];
            $contacts = CRM_Contact_BAO_GroupContact::getGroupContacts($group);
            foreach ($contacts as $contact) {
                $query = 
                    "SELECT DISTINCT civicrm_email.id AS email_id, civicrm_email.is_primary as is_primary,
                                 civicrm_email.is_bulkmail as is_bulkmail
FROM civicrm_email
INNER JOIN civicrm_contact ON civicrm_email.contact_id = civicrm_contact.id
WHERE civicrm_email.is_bulkmail = 1
AND civicrm_contact.id = {$contact->contact_id}
AND civicrm_contact.do_not_email = 0
AND civicrm_contact.is_deceased = 0
AND civicrm_email.on_hold = 0
AND civicrm_contact.is_opt_out =0";
                $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray);
                if ($dao->fetch( ) ) {
                    $params = array(
                                    'job_id'        => $testParams['job_id'],
                                    'email_id'      => $dao->email_id,
                                    'contact_id'    => $contact->contact_id
                                    );
                    $queue = CRM_Mailing_Event_BAO_Queue::create($params);  
                } else {
                    $query = 
                    "SELECT DISTINCT civicrm_email.id AS email_id, civicrm_email.is_primary as is_primary,
                                 civicrm_email.is_bulkmail as is_bulkmail
FROM civicrm_email
INNER JOIN civicrm_contact ON civicrm_email.contact_id = civicrm_contact.id
WHERE civicrm_email.is_primary = 1
AND civicrm_contact.id = {$contact->contact_id}
AND civicrm_contact.do_not_email =0
AND civicrm_contact.is_deceased = 0
AND civicrm_email.on_hold = 0
AND civicrm_contact.is_opt_out =0";
                    $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray);
                    if ($dao->fetch( ) ) {
                        $params = array(
                                        'job_id'        => $testParams['job_id'],
                                        'email_id'      => $dao->email_id,
                                        'contact_id'    => $contact->contact_id
                                        );
                        $queue = CRM_Mailing_Event_BAO_Queue::create($params);  
                    }                    
                }
            }
        }
    }
    
    /**
     * Retrieve the header and footer for this mailing
     *
     * @param void
     * @return void
     * @access private
     */
    private function getHeaderFooter() 
    {
        if (!$this->header and $this->header_id) {
            $this->header = new CRM_Mailing_BAO_Component();
            $this->header->id = $this->header_id;
            $this->header->find(true);
            $this->header->free( );
        }
        
        if (!$this->footer and $this->footer_id) {
            $this->footer = new CRM_Mailing_BAO_Component();
            $this->footer->id = $this->footer_id;
            $this->footer->find(true);
            $this->footer->free( );
        }
    }



    /**
     * static wrapper for getting verp and urls
     *
     * @param int $job_id           ID of the Job associated with this message
     * @param int $event_queue_id   ID of the EventQueue
     * @param string $hash          Hash of the EventQueue
     * @param string $email         Destination address
     * @return (reference) array    array ref that hold array refs to the verp info and urls
     */
    static function getVerpAndUrls($job_id, $event_queue_id, $hash, $email){
        // create a skeleton object and set its properties that are required by getVerpAndUrlsAndHeaders()
        require_once 'CRM/Core/BAO/Domain.php';
        $config = CRM_Core_Config::singleton();
        $bao = new CRM_Mailing_BAO_Mailing();
        $bao->_domain =& CRM_Core_BAO_Domain::getDomain( );
        $bao->from_name = $bao->from_email = $bao->subject = '';

        // use $bao's instance method to get verp and urls
        list($verp, $urls, $_) = $bao->getVerpAndUrlsAndHeaders($job_id, $event_queue_id, $hash, $email);
        return array($verp, $urls);
    }

    /**
     * get verp, urls and headers
     *
     * @param int $job_id           ID of the Job associated with this message
     * @param int $event_queue_id   ID of the EventQueue
     * @param string $hash          Hash of the EventQueue
     * @param string $email         Destination address
     * @return (reference) array    array ref that hold array refs to the verp info, urls, and headers
     * @access private
     */
    private function getVerpAndUrlsAndHeaders( $job_id, $event_queue_id, $hash, $email, $isForward = false )
    {
        $config = CRM_Core_Config::singleton( );
        /**
         * Inbound VERP keys:
         *  reply:          user replied to mailing
         *  bounce:         email address bounced
         *  unsubscribe:    contact opts out of all target lists for the mailing
         *  resubscribe:    contact opts back into all target lists for the mailing
         *  optOut:         contact unsubscribes from the domain
         */
        $verp = array( );
        $verpTokens = array( 'reply'       => 'r' ,
                             'bounce'      => 'b' ,
                             'unsubscribe' => 'u' ,
                             'resubscribe' => 'e',
                             'optOut'      => 'o'  );
        
        require_once 'CRM/Core/BAO/MailSettings.php';
        $localpart   = CRM_Core_BAO_MailSettings::defaultLocalpart();
        $emailDomain = CRM_Core_BAO_MailSettings::defaultDomain();

        foreach ($verpTokens as $key => $value ) {
            $verp[$key] = implode($config->verpSeparator,
                                  array(
                                        $localpart . $value,
                                        $job_id, 
                                        $event_queue_id,
                                        $hash
                                        )
                                  ) . "@$emailDomain";
        }
        
        //handle should override VERP address.    
        $skipEncode = false;
        $query = "
SELECT override_verp 
FROM   civicrm_mailing, civicrm_mailing_job 
WHERE  civicrm_mailing_job.id = {$job_id} 
AND    civicrm_mailing.id = civicrm_mailing_job.mailing_id";
        
        if ( $job_id && 
            CRM_Core_DAO::singleValueQuery( $query ) ) {
            $verp['reply'] = "\"{$this->from_name}\" <{$this->from_email}>"; 
        }
        
        $urls = array(
                      'forward'        => CRM_Utils_System::url('civicrm/mailing/forward', 
                                                                "reset=1&jid={$job_id}&qid={$event_queue_id}&h={$hash}",
                                                                true, null, true, true),
                      'unsubscribeUrl' => CRM_Utils_System::url('civicrm/mailing/unsubscribe', 
                                                                "reset=1&jid={$job_id}&qid={$event_queue_id}&h={$hash}",
                                                                true, null, true, true),
                      'resubscribeUrl' => CRM_Utils_System::url('civicrm/mailing/resubscribe', 
                                                                "reset=1&jid={$job_id}&qid={$event_queue_id}&h={$hash}",
                                                                true, null, true, true),
                      'optOutUrl'      => CRM_Utils_System::url('civicrm/mailing/optout', 
                                                                "reset=1&jid={$job_id}&qid={$event_queue_id}&h={$hash}",
                                                                true, null, true, true),
                      'subscribeUrl'   => CRM_Utils_System::url( 'civicrm/mailing/subscribe',
                                                                 'reset=1',
                                                                 true, null, true, true )
                      );

        $headers = array(
            'Reply-To'         => $verp['reply'],
            'Return-Path'      => $verp['bounce'],
            'From'             => "\"{$this->from_name}\" <{$this->from_email}>",
            'Subject'          => $this->subject,
            'List-Unsubscribe' => "<mailto:{$verp['unsubscribe']}>",
        );

        if( $isForward ) {
            $headers['Subject'] = "[Fwd:{$this->subject}]";
        } 
        return array( &$verp, &$urls, &$headers );
    }

    /**
     * Compose a message
     *
     * @param int $job_id           ID of the Job associated with this message
     * @param int $event_queue_id   ID of the EventQueue
     * @param string $hash          Hash of the EventQueue
     * @param string $contactId     ID of the Contact
     * @param string $email         Destination address
     * @param string $recipient     To: of the recipient
     * @param boolean $test         Is this mailing a test?
     * @param boolean $isForward    Is this mailing compose for forward?
     * @param string  $fromEmail    email address of who is forwardinf it.
     * @return object               The mail object
     * @access public
     */
    public function &compose($job_id, $event_queue_id, $hash, $contactId, 
                             $email, &$recipient, $test, 
                             $contactDetails, &$attachments, $isForward = false, $fromEmail = null ) 
    {
        
        require_once 'api/v2/Contact.php';
        require_once 'CRM/Utils/Token.php';
        require_once 'CRM/Activity/BAO/Activity.php';
        $config = CRM_Core_Config::singleton( );
        $knownTokens = $this->getTokens();
        
        if ($this->_domain == null) {
            require_once 'CRM/Core/BAO/Domain.php';
            $this->_domain =& CRM_Core_BAO_Domain::getDomain( );
        }

        list( $verp, $urls, $headers) = $this->getVerpAndUrlsAndHeaders($job_id, $event_queue_id, $hash, $email, $isForward );
        //set from email who is forwarding it and not original one.      
        if ( $fromEmail ) {
            unset( $headers['From'] );
            $headers['From'] = "<{$fromEmail}>";
        } 

        if ( defined( 'CIVICRM_MAIL_SMARTY' ) ) {
            require_once 'CRM/Core/Smarty/resources/String.php';
            civicrm_smarty_register_string_resource( );
        }
        
        if ( $contactDetails ) {
            $contact = $contactDetails;
        } else {
            $params  = array( 'contact_id' => $contactId );
            $contact =& civicrm_contact_get( $params );
            
            //CRM-4524
            $contact = reset( $contact );
            
            if ( !$contact || is_a( $contact, 'CRM_Core_Error' ) ) {
                return null;
            }
            
            // also call the hook to get contact details
            require_once 'CRM/Utils/Hook.php';
            CRM_Utils_Hook::tokenValues( $contact, $contactId, $job_id );
        }

        $pTemplates = $this->getPreparedTemplates();
        $pEmails = array( );

        foreach( $pTemplates as $type => $pTemplate ) {
            $html = ($type == 'html') ? true : false;
            $pEmails[$type] = array( );
            $pEmail   =& $pEmails[$type];
            $template =& $pTemplates[$type]['template'];
            $tokens   =& $pTemplates[$type]['tokens'];
            $idx = 0;
            if ( !empty( $tokens ) ) {
                foreach ($tokens as $idx => $token) {
                    $token_data = $this->getTokenData($token, $html, $contact, $verp, $urls, $event_queue_id);
                    array_push($pEmail, $template[$idx]);
                    array_push($pEmail, $token_data);
                }
            } else {
                array_push($pEmail, $template[$idx]);
            }
            
            if ( isset( $template[($idx + 1)] ) ) {
                array_push($pEmail, $template[($idx + 1)]);
            }
        }

        $html = null;
        if ( isset( $pEmails['html'] ) &&  is_array( $pEmails['html'] ) && count( $pEmails['html'] ) ) {
            $html = &$pEmails['html'];
        }

        $text = null;
        if ( isset( $pEmails['text'] ) && is_array( $pEmails['text'] ) && count( $pEmails['text'] ) ){
            $text = &$pEmails['text'];
        }
        
        // push the tracking url on to the html email if necessary
        if ($this->open_tracking && $html ) {
            array_push($html,"\n".'<img src="' . $config->userFrameworkResourceURL . 
                       "extern/open.php?q=$event_queue_id\" width='1' height='1' alt='' border='0'>");
        }
        
        $message = new Mail_mime("\n");
        
        if ( defined( 'CIVICRM_MAIL_SMARTY' ) ) {
            $smarty = CRM_Core_Smarty::singleton( );
            // also add the contact tokens to the template
            $smarty->assign_by_ref( 'contact', $contact );
        }

        $mailParams = $headers;
        if ($text && ( $test || $contact['preferred_mail_format'] == 'Text' ||
                       $contact['preferred_mail_format'] == 'Both' ||
                       ( $contact['preferred_mail_format'] == 'HTML' && !array_key_exists('html',$pEmails) ) ) ) {
            $textBody = join( '', $text );
            if ( defined( 'CIVICRM_MAIL_SMARTY' ) ) {
                $smarty->security = true;
                $textBody = $smarty->fetch( "string:$textBody" );
                $smarty->security = false;
            }
            $mailParams['text'] = $textBody;
        }
        
        if ( $html && ( $test ||  ( $contact['preferred_mail_format'] == 'HTML' ||
                                    $contact['preferred_mail_format'] == 'Both') ) ) {
            $htmlBody = join( '', $html );
            if ( defined( 'CIVICRM_MAIL_SMARTY' ) ) {
                $smarty->security = true;
                $htmlBody = $smarty->fetch( "string:$htmlBody" );
                $smarty->security = false;
            }
            $mailParams['html'] = $htmlBody;
        }

        $mailParams['attachments'] = $attachments;
        
        $mailingSubject = CRM_Utils_Array::value( 'subject', $pEmails );
        if ( is_array( $mailingSubject ) ) {
            $mailingSubject  = join( '', $mailingSubject );
        }
        $mailParams['Subject'] = $mailingSubject;

        $mailParams['toName' ] = $contact['display_name'];
        $mailParams['toEmail'] = $email;

        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::alterMailParams( $mailParams );

        if ( ! empty( $mailParams['text'] ) ) {
            $message->setTxtBody( $mailParams['text'] );
        }

        if ( ! empty( $mailParams['html'] ) ) {
            $message->setHTMLBody( $mailParams['html'] );
        }

        if ( ! empty( $mailParams['attachments'] ) ) {
            foreach ( $mailParams['attachments'] as $fileID => $attach ) {
                $message->addAttachment( $attach['fullPath'],
                                         $attach['mime_type'],
                                         $attach['cleanName'] );
            }
        }

        $headers['To'] = "{$mailParams['toName']} <{$mailParams['toEmail']}>";
        $headers['Precedence'] = 'bulk';
        // Will test in the mail processor if the X-VERP is set in the bounced email.
        // (As an option to replace real VERP for those that can't set it up)
        $headers['X-CiviMail-Bounce'] = $verp['bounce'];

        //CRM-5058
        //token replacement of subject
        $headers['Subject'] = $mailingSubject;
        
        CRM_Utils_Mail::setMimeParams( $message );
        $headers = $message->headers( $headers );
        
        //get formatted recipient
        $recipient = $headers['To'];
        
        // make sure we unset a lot of stuff
        unset( $verp );
        unset( $urls );
        unset( $params );
        unset( $contact );
        unset( $ids );

        return $message;
    }

    /**
     *
     * get mailing object and replaces subscribeInvite,
     * domain and mailing tokens
     *
     */
    function tokenReplace( &$mailing )
    {
        require_once 'CRM/Core/BAO/Domain.php';
        $domain =& CRM_Core_BAO_Domain::getDomain( );
        foreach ( array('text', 'html') as $type ) {
            require_once 'CRM/Utils/Token.php';
            $tokens = $mailing->getTokens();
            if ( isset( $mailing->templates[$type] ) ) {
                $mailing->templates[$type] =  
                    CRM_Utils_Token::replaceSubscribeInviteTokens($mailing->templates[$type]);
                $mailing->templates[$type] = 
                    CRM_Utils_Token::replaceDomainTokens( $mailing->templates[$type],
                                                          $domain, 
                                                          $type == 'html' ? true : false,
                                                          $tokens[$type] );
                $mailing->templates[$type] = 
                    CRM_Utils_Token::replaceMailingTokens($mailing->templates[$type], $mailing, null, $tokens[$type]);
            }
        }
    }
    /**
     *
     *  getTokenData receives a token from an email
     *  and returns the appropriate data for the token
     *
     */
    private function getTokenData(&$token_a, $html = false, &$contact, &$verp, &$urls, $event_queue_id)
    { 
        $type = $token_a['type'];
        $token = $token_a['token'];
        $data = $token;

        $escapeSmarty = defined( 'CIVICRM_MAIL_SMARTY' ) ? true : false;

        if ($type == 'embedded_url') {
            $embed_data = array( );
            foreach ( $token as $t ) {
                $embed_data[] = $this->getTokenData($t, $html = false, $contact, $verp, $urls, $event_queue_id);
            }
            $numSlices = count( $embed_data );
            $url = '';
            for ( $i = 0; $i < $numSlices; $i++ ) {
                $url .= "{$token_a['embed_parts'][$i]}{$embed_data[$i]}";
            }
            if ( isset( $token_a['embed_parts'][$numSlices] ) ) {
                $url .= $token_a['embed_parts'][$numSlices];
            }
            // add trailing quote since we've gobbled it up in a previous regex
            // function getPatterns, line 431
            if ( preg_match( '/^href[ ]*=[ ]*\'/', $url ) ) {
                $url .= "'";
            } else if ( preg_match( '/^href[ ]*=[ ]*\"/', $url ) ) {
                $url .= '"';
            }
            $data = $url;
        } else if ( $type == 'url' ) {
            if ( $this->url_tracking ) {
                $data = CRM_Mailing_BAO_TrackableURL::getTrackerURL($token, $this->id, $event_queue_id);
            } else {
                $data = $token;
            }
        } else if ( $type == 'contact' ) {
            $data = CRM_Utils_Token::getContactTokenReplacement($token, $contact, false, false, $escapeSmarty );
        } else if ( $type == 'action' ) {
          $data = CRM_Utils_Token::getActionTokenReplacement($token, $verp, $urls, $html);
        } else if ( $type == 'domain' ) {
            require_once 'CRM/Core/BAO/Domain.php';
            $domain =& CRM_Core_BAO_Domain::getDomain( );
            $data = CRM_Utils_Token::getDomainTokenReplacement($token, $domain, $html);
        } else if( $type == 'mailing') {
            require_once 'CRM/Mailing/BAO/Mailing.php';
            $mailing = new CRM_Mailing_BAO_Mailing( );
            $mailing->find( true );
            if ( $token == 'name' ) {
                $data = $mailing->name ;
            } else if ( $token == 'group' ) {
                $groups = $mailing->getGroupNames( );
                $data = implode(', ', $groups);
            }         
        } else {
            $data = CRM_Utils_Array::value( "{$type}.{$token}", $contact );
        }
        return $data;
    }
    
    /**
     * Return a list of group names for this mailing.  Does not work with
     * prior-mailing targets.
     *
     * @return array        Names of groups receiving this mailing
     * @access public
     */
    public function &getGroupNames() {
        if (! isset($this->id)) {
            return array();
        }
        $mg = new CRM_Mailing_DAO_Group();
        $mgtable = CRM_Mailing_DAO_Group::getTableName();
        $group = CRM_Contact_BAO_Group::getTableName();

        $mg->query("SELECT      $group.title as name FROM $mgtable 
                    INNER JOIN  $group ON $mgtable.entity_id = $group.id
                    WHERE       $mgtable.mailing_id = {$this->id}
                        AND     $mgtable.entity_table = '$group'
                        AND     $mgtable.group_type = 'Include'
                    ORDER BY    $group.name");

        $groups = array();
        while ($mg->fetch()) {
            $groups[] = $mg->name;
        }
        return $groups;
    }
    
    /**
     * function to add the mailings
     *
     * @param array $params reference array contains the values submitted by the form
     * @param array $ids    reference array contains the id
     * 
     * @access public
     * @static 
     * @return object
     */
    static function add( &$params, &$ids )
    {
        require_once 'CRM/Utils/Hook.php';
        
        if ( CRM_Utils_Array::value( 'mailing', $ids ) ) {
            CRM_Utils_Hook::pre( 'edit', 'Mailing', $ids['mailing_id'], $params );
        } else {
            CRM_Utils_Hook::pre( 'create', 'Mailing', null, $params ); 
        }
        
        $mailing = new CRM_Mailing_DAO_Mailing( );
        $mailing->id = CRM_Utils_Array::value( 'mailing_id', $ids );
        
        if (  ! isset( $params['replyto_email'] ) &&
              isset( $params['from_email'] ) ) {
            $params['replyto_email'] = $params['from_email'];
        }

        $mailing->copyValues( $params );

        $result = $mailing->save( );

        if ( CRM_Utils_Array::value( 'mailing', $ids ) ) {
            CRM_Utils_Hook::post( 'edit', 'Mailing', $mailing->id, $mailing );
        } else {
            CRM_Utils_Hook::post( 'create', 'Mailing', $mailing->id, $mailing );
        }

        return $result;
    }

    /**
     * Construct a new mailing object, along with job and mailing_group
     * objects, from the form values of the create mailing wizard.
     *
     * @params array $params        Form values
     * @return object $mailing      The new mailing object
     * @access public
     * @static
     */
    public static function create( &$params, &$ids ) 
    {
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        $mailing = self::add($params, $ids);

        if( is_a( $mailing, 'CRM_Core_Error') ) {
            $transaction->rollback( );
            return $mailing;
        }
        
        require_once 'CRM/Contact/BAO/Group.php';

        $groupTableName   = CRM_Contact_BAO_Group::getTableName( );
        $mailingTableName = CRM_Mailing_BAO_Mailing::getTableName( ); 

        /* Create the mailing group record */
        $mg = new CRM_Mailing_DAO_Group();
        foreach( array( 'groups', 'mailings' ) as $entity ) {
            foreach( array( 'include', 'exclude', 'base' ) as $type ) {                
                if( CRM_Utils_Array::value( $type, $params[$entity] ) && is_array( $params[$entity][$type] ) ) {                    
                    foreach( $params[$entity][$type] as $entityId ) {
                        $mg->reset( );
                        $mg->mailing_id = $mailing->id;                        
                        $mg->entity_table   =
                            ( $entity == 'groups' ) 
                            ? $groupTableName
                            : $mailingTableName;
                        $mg->entity_id  = $entityId;
                        $mg->group_type = $type;
                        $mg->save( );
                    }
                }
            }
        }

        if ( ! empty( $params['search_id'] ) &&
             ! empty( $params['group_id'] ) ) {
            $mg->reset( );
            $mg->mailing_id   = $mailing->id;
            $mg->entity_table = $groupTableName;
            $mg->entity_id    = $params['group_id'];
            $mg->search_id    = $params['search_id'];
            $mg->search_args  = $params['search_args'];
            $mg->group_type   = 'Include';
            $mg->save( );
        }

        // check and attach and files as needed
        require_once 'CRM/Core/BAO/File.php';
        CRM_Core_BAO_File::processAttachment( $params,
                                              'civicrm_mailing',
                                              $mailing->id );

        $transaction->commit( );
        return $mailing;
    }


    /**
     * Generate a report.  Fetch event count information, mailing data, and job
     * status.
     *
     * @param int     $id          The mailing id to report
     * @param boolean $skipDetails whether return all detailed report
     * @return array        Associative array of reporting data
     * @access public
     * @static
     */
    public static function &report( $id, $skipDetails = false ) {
        $mailing_id = CRM_Utils_Type::escape($id, 'Integer');
        
        $mailing = new CRM_Mailing_BAO_Mailing();

        require_once 'CRM/Mailing/Event/BAO/Opened.php';
        require_once 'CRM/Mailing/Event/BAO/Reply.php';
        require_once 'CRM/Mailing/Event/BAO/Unsubscribe.php';
        require_once 'CRM/Mailing/Event/BAO/Forward.php';
        require_once 'CRM/Mailing/Event/BAO/TrackableURLOpen.php';
        require_once 'CRM/Mailing/BAO/Spool.php';
        $t = array(
                'mailing'   => self::getTableName(),
                'mailing_group'  => CRM_Mailing_DAO_Group::getTableName(),
                'group'     => CRM_Contact_BAO_Group::getTableName(),
                'job'       => CRM_Mailing_BAO_Job::getTableName(),
                'queue'     => CRM_Mailing_Event_BAO_Queue::getTableName(),
                'delivered' => CRM_Mailing_Event_BAO_Delivered::getTableName(),
                'opened'    => CRM_Mailing_Event_BAO_Opened::getTableName(),
                'reply'     => CRM_Mailing_Event_BAO_Reply::getTableName(),
                'unsubscribe'   =>
                            CRM_Mailing_Event_BAO_Unsubscribe::getTableName(),
                'bounce'    => CRM_Mailing_Event_BAO_Bounce::getTableName(),
                'forward'   => CRM_Mailing_Event_BAO_Forward::getTableName(),
                'url'       => CRM_Mailing_BAO_TrackableURL::getTableName(),
                'urlopen'   =>
                    CRM_Mailing_Event_BAO_TrackableURLOpen::getTableName(),
                'component' =>  CRM_Mailing_BAO_Component::getTableName(),
                'spool'     => CRM_Mailing_BAO_Spool::getTableName() 
            );
        
        
        $report = array();
                
        /* Get the mailing info */
        $mailing->query("
            SELECT          {$t['mailing']}.*
            FROM            {$t['mailing']}
            WHERE           {$t['mailing']}.id = $mailing_id");
            
        $mailing->fetch();
        

        $report['mailing'] = array();
        foreach (array_keys(self::fields()) as $field) {
            $report['mailing'][$field] = $mailing->$field;
        }

        //mailing report is called by activity
        //we dont need all detail report
        if ( $skipDetails ) {
            return $report;
        }

        /* Get the component info */
        $query = array();
        
        $components = array(
                        'header'        => ts('Header'),
                        'footer'        => ts('Footer'),
                        'reply'         => ts('Reply'),
                        'unsubscribe'   => ts('Unsubscribe'),
                        'optout'        => ts('Opt-Out')
                    );
        foreach(array_keys($components) as $type) {
            $query[] = "SELECT          {$t['component']}.name as name,
                                        '$type' as type,
                                        {$t['component']}.id as id
                        FROM            {$t['component']}
                        INNER JOIN      {$t['mailing']}
                                ON      {$t['mailing']}.{$type}_id =
                                                {$t['component']}.id
                        WHERE           {$t['mailing']}.id = $mailing_id";
        }
        $q = '(' . implode(') UNION (', $query) . ')';
        $mailing->query($q);

        $report['component'] = array();
        while ($mailing->fetch()) {
            $report['component'][] = array(
                                    'type'  => $components[$mailing->type],
                                    'name'  => $mailing->name,
                                    'link'  =>
                                    CRM_Utils_System::url('civicrm/mailing/component', 
                                                          "reset=1&action=update&id={$mailing->id}"),
                                    );
        }
        
        /* Get the recipient group info */
        $mailing->query("
            SELECT          {$t['mailing_group']}.group_type as group_type,
                            {$t['group']}.id as group_id,
                            {$t['group']}.title as group_title,
                            {$t['mailing']}.id as mailing_id,
                            {$t['mailing']}.name as mailing_name
            FROM            {$t['mailing_group']}
            LEFT JOIN       {$t['group']}
                    ON      {$t['mailing_group']}.entity_id = {$t['group']}.id
                    AND     {$t['mailing_group']}.entity_table =
                                                                '{$t['group']}'
            LEFT JOIN       {$t['mailing']}
                    ON      {$t['mailing_group']}.entity_id =
                                                            {$t['mailing']}.id
                    AND     {$t['mailing_group']}.entity_table =
                                                            '{$t['mailing']}'

            WHERE           {$t['mailing_group']}.mailing_id = $mailing_id
            ");
        
        $report['group'] = array('include' => array(), 'exclude' => array());
        while ($mailing->fetch()) {
            $row = array();
            if (isset($mailing->group_id)) {
                $row['id'] = $mailing->group_id;
                $row['name'] = $mailing->group_title;
                $row['link'] = CRM_Utils_System::url('civicrm/group/search',
                            "reset=1&force=1&context=smog&gid={$row['id']}");
            } else {
                $row['id'] = $mailing->mailing_id;
                $row['name'] = $mailing->mailing_name;
                $row['mailing'] = true;
                $row['link'] = CRM_Utils_System::url('civicrm/mailing/report',
                                                    "mid={$row['id']}");
            }
            
            if ($mailing->group_type == 'Include') {
                $report['group']['include'][] = $row;
            } else {
                $report['group']['exclude'][] = $row;
            }
        }

        /* Get the event totals, grouped by job (retries) */
        $mailing->query("
            SELECT          {$t['job']}.*,
                            COUNT(DISTINCT {$t['queue']}.id) as queue,
                            COUNT(DISTINCT {$t['delivered']}.id) as delivered,
                            COUNT(DISTINCT {$t['reply']}.id) as reply,
                            COUNT(DISTINCT {$t['forward']}.id) as forward,
                            COUNT(DISTINCT {$t['bounce']}.id) as bounce,
                            COUNT(DISTINCT {$t['urlopen']}.id) as url,
                            COUNT(DISTINCT {$t['spool']}.id) as spool
            FROM            {$t['job']}
            LEFT JOIN       {$t['queue']}
                    ON      {$t['queue']}.job_id = {$t['job']}.id
            LEFT JOIN       {$t['reply']}
                    ON      {$t['reply']}.event_queue_id = {$t['queue']}.id
            LEFT JOIN       {$t['forward']}
                    ON      {$t['forward']}.event_queue_id = {$t['queue']}.id
            LEFT JOIN       {$t['bounce']}
                    ON      {$t['bounce']}.event_queue_id = {$t['queue']}.id
            LEFT JOIN       {$t['delivered']}
                    ON      {$t['delivered']}.event_queue_id = {$t['queue']}.id
                    AND     {$t['bounce']}.id IS null
            LEFT JOIN       {$t['urlopen']}
                    ON      {$t['urlopen']}.event_queue_id = {$t['queue']}.id
            LEFT JOIN       {$t['spool']}
                    ON      {$t['spool']}.job_id = {$t['job']}.id
            WHERE           {$t['job']}.mailing_id = $mailing_id
                    AND     {$t['job']}.is_test = 0
            GROUP BY        {$t['job']}.id");
        
        $report['jobs'] = array();
        $report['event_totals'] = array();
        $elements = array(  'queue', 'delivered', 'url', 'forward',
                            'reply', 'unsubscribe', 'opened', 'bounce', 'spool' );

        // initialize various counters
        foreach ( $elements as $field ) {
            $report['event_totals'][$field] = 0;
        }

        while ($mailing->fetch()) {
            $row = array();
            foreach ( $elements as $field ) {
                if ( isset( $mailing->$field ) ) {
                    $row[$field] = $mailing->$field;
                    $report['event_totals'][$field] += $mailing->$field;
                }
            }
            
            // compute open total separately to discount duplicates
            // CRM-1258
            $row['opened'] = CRM_Mailing_Event_BAO_Opened::getTotalCount( $mailing_id, $mailing->id, true );
            $report['event_totals']['opened'] += $row['opened'];
            
            // compute unsub total separately to discount duplicates
            // CRM-1783
            $row['unsubscribe'] = CRM_Mailing_Event_BAO_Unsubscribe::getTotalCount( $mailing_id, $mailing->id, true );
            $report['event_totals']['unsubscribe'] += $row['unsubscribe'];


            foreach ( array_keys(CRM_Mailing_BAO_Job::fields( ) ) as $field ) {
                $row[$field] = $mailing->$field;
            }
            
            if ($mailing->queue) {
                $row['delivered_rate'] = (100.0 * $mailing->delivered ) /
                    $mailing->queue;
                $row['bounce_rate'] = (100.0 * $mailing->bounce ) /
                    $mailing->queue;
                $row['unsubscribe_rate'] = (100.0 * $row['unsubscribe'] ) /
                    $mailing->queue;
            } else {
                $row['delivered_rate'] = 0;
                $row['bounce_rate'] = 0;
                $row['unsubscribe_rate'] = 0;
            }
            
            $row['links'] = array(
                'clicks' => CRM_Utils_System::url(
                        'civicrm/mailing/report/event',
                        "reset=1&event=click&mid=$mailing_id&jid={$mailing->id}"
                ),
                'queue' =>  CRM_Utils_System::url(
                        'civicrm/mailing/report/event',
                        "reset=1&event=queue&mid=$mailing_id&jid={$mailing->id}"
                ),
                'delivered' => CRM_Utils_System::url(
                        'civicrm/mailing/report/event',
                        "reset=1&event=delivered&mid=$mailing_id&jid={$mailing->id}"
                ),
                'bounce'    => CRM_Utils_System::url(
                        'civicrm/mailing/report/event',
                        "reset=1&event=bounce&mid=$mailing_id&jid={$mailing->id}"
                ),
                'unsubscribe'   => CRM_Utils_System::url(
                        'civicrm/mailing/report/event',
                        "reset=1&event=unsubscribe&mid=$mailing_id&jid={$mailing->id}"
                ),
                'forward'       => CRM_Utils_System::url(
                        'civicrm/mailing/report/event',
                        "reset=1&event=forward&mid=$mailing_id&jid={$mailing->id}"
                ),
                'reply'         => CRM_Utils_System::url(
                        'civicrm/mailing/report/event',
                        "reset=1&event=reply&mid=$mailing_id&jid={$mailing->id}"
                ),
                'opened'        => CRM_Utils_System::url(
                        'civicrm/mailing/report/event',
                        "reset=1&event=opened&mid=$mailing_id&jid={$mailing->id}"
                ),
            );

            foreach (array('scheduled_date', 'start_date', 'end_date') as $key) {
                $row[$key] = CRM_Utils_Date::customFormat($row[$key]);
            }
            $report['jobs'][] = $row;
        }
        $report['event_totals']['queue'] = self::getRecipientsCount( $mailing_id, false, $mailing_id );

        if (CRM_Utils_Array::value('queue',$report['event_totals'] )) {
            $report['event_totals']['delivered_rate'] = (100.0 * $report['event_totals']['delivered']) / $report['event_totals']['queue'];
            $report['event_totals']['bounce_rate'] = (100.0 * $report['event_totals']['bounce']) / $report['event_totals']['queue'];
            $report['event_totals']['unsubscribe_rate'] = (100.0 * $report['event_totals']['unsubscribe']) / $report['event_totals']['queue'];
        } else {
            $report['event_totals']['delivered_rate'] = 0;
            $report['event_totals']['bounce_rate'] = 0;
            $report['event_totals']['unsubscribe_rate'] = 0;
        }

        /* Get the click-through totals, grouped by URL */
        $mailing->query("
            SELECT      {$t['url']}.url,
                        {$t['url']}.id,
                        COUNT({$t['urlopen']}.id) as clicks,
                        COUNT(DISTINCT {$t['queue']}.id) as unique_clicks
            FROM        {$t['url']}
            LEFT JOIN   {$t['urlopen']}
                    ON  {$t['urlopen']}.trackable_url_id = {$t['url']}.id
            LEFT JOIN  {$t['queue']}
                    ON  {$t['urlopen']}.event_queue_id = {$t['queue']}.id
            LEFT JOIN  {$t['job']}
                    ON  {$t['queue']}.job_id = {$t['job']}.id
            WHERE       {$t['url']}.mailing_id = $mailing_id
                    AND {$t['job']}.is_test = 0
            GROUP BY    {$t['url']}.id");
       
        $report['click_through'] = array();
        
        while ($mailing->fetch()) {
            $report['click_through'][] = array(
                                    'url' => $mailing->url,
                                    'link' =>
                                    CRM_Utils_System::url(
                    'civicrm/mailing/report/event',
                    "reset=1&event=click&mid=$mailing_id&uid={$mailing->id}"),
                                    'link_unique' =>
                                    CRM_Utils_System::url(
                    'civicrm/mailing/report/event',
                    "reset=1&event=click&mid=$mailing_id&uid={$mailing->id}&distinct=1"),
                                    'clicks' => $mailing->clicks,
                                    'unique' => $mailing->unique_clicks,
                                    'rate'   => CRM_Utils_Array::value('delivered',$report['event_totals']) ? (100.0 * $mailing->unique_clicks) / $report['event_totals']['delivered'] : 0
                                );
        }

        $report['event_totals']['links'] = array(
            'clicks' => CRM_Utils_System::url(
                            'civicrm/mailing/report/event',
                            "reset=1&event=click&mid=$mailing_id"
            ),
            'clicks_unique' => CRM_Utils_System::url(
                            'civicrm/mailing/report/event',
                            "reset=1&event=click&mid=$mailing_id&distinct=1"
            ),
            'queue' =>  CRM_Utils_System::url(
                            'civicrm/mailing/report/event',
                            "reset=1&event=queue&mid=$mailing_id"
            ),
            'delivered' => CRM_Utils_System::url(
                            'civicrm/mailing/report/event',
                            "reset=1&event=delivered&mid=$mailing_id"
            ),
            'bounce'    => CRM_Utils_System::url(
                            'civicrm/mailing/report/event',
                            "reset=1&event=bounce&mid=$mailing_id"
            ),
            'unsubscribe'   => CRM_Utils_System::url(
                            'civicrm/mailing/report/event',
                            "reset=1&event=unsubscribe&mid=$mailing_id"
            ),
            'forward'         => CRM_Utils_System::url(
                            'civicrm/mailing/report/event',
                            "reset=1&event=forward&mid=$mailing_id"
            ),
            'reply'         => CRM_Utils_System::url(
                            'civicrm/mailing/report/event',
                            "reset=1&event=reply&mid=$mailing_id"
            ),
            'opened'        => CRM_Utils_System::url(
                            'civicrm/mailing/report/event',
                            "reset=1&event=opened&mid=$mailing_id"
            ),
        );

        return $report;
    }

    /**
     * Get the count of mailings 
     *
     * @param
     * @return int              Count
     * @access public
     */
    public function getCount() {
        $this->selectAdd();
        $this->selectAdd('COUNT(id) as count');
        
        $session = CRM_Core_Session::singleton();
        $this->find(true);
        
        return $this->count;
    }


    static function checkPermission( $id ) {
        if ( ! $id ) {
            return;
        }

        $mailingIDs =& CRM_Mailing_BAO_Mailing::mailingACLIDs( );
        if ( ! in_array( $id,
                         $mailingIDs ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have permission to access this mailing report' ) );
        }
        return;
    }

    static function mailingACL( $alias = null ) {
        $mailingACL = " ( 0 ) ";

        $mailingIDs =& self::mailingACLIDs( );
        if ( ! empty( $mailingIDs ) ) {
            $mailingIDs = implode( ',', $mailingIDs );
            $tableName  = !$alias ? self::getTableName( ) : $alias;
            $mailingACL = " $tableName.id IN ( $mailingIDs ) ";
        }
        return $mailingACL;
    }

    static function &mailingACLIDs( $count = false, $condition = null ) {
        $mailingIDs = array( );

        // get all the groups that this user can access
        // if they dont have universal access
        $groups   = CRM_Core_PseudoConstant::group( );
        if ( ! empty( $groups ) ) {
            $groupIDs = implode( ',',
                                 array_keys( $groups ) );
            $selectClause = ( $count ) ? 'COUNT( DISTINCT m.id) as count' : 'DISTINCT( m.id ) as id';
            // get all the mailings that are in this subset of groups
            $query = "
SELECT $selectClause 
  FROM civicrm_mailing m,
       civicrm_mailing_group g
 WHERE g.mailing_id   = m.id
   AND g.entity_table = 'civicrm_group'
   AND g.entity_id IN ( $groupIDs )
   $condition";
            $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
            if ( $count ) {
                $dao->fetch( );
                return $dao->count;
            }
            $mailingIDs = array( );
            while ( $dao->fetch( ) ) {
                $mailingIDs[] = $dao->id;
            }
        }

        return $mailingIDs;
    }

    /**
     * Get the rows for a browse operation
     *
     * @param int $offset       The row number to start from
     * @param int $rowCount     The nmber of rows to return
     * @param string $sort      The sql string that describes the sort order
     * 
     * @return array            The rows
     * @access public
     */
    public function &getRows($offset, $rowCount, $sort, $additionalClause = null, $additionalParams = null ) {
        $mailing    = self::getTableName();
        $job        = CRM_Mailing_BAO_Job::getTableName();
        $group      = CRM_Mailing_DAO_Group::getTableName( );
        $session    = CRM_Core_Session::singleton();

        $mailingACL = self::mailingACL( );

        $query = "
            SELECT      $mailing.id,
                        $mailing.name, 
                        $job.status, 
                        MIN($job.scheduled_date) as scheduled_date, 
                        MIN($job.start_date) as start_date,
                        MAX($job.end_date) as end_date,
                        createdContact.sort_name as created_by, 
                        scheduledContact.sort_name as scheduled_by,
                        $mailing.created_id as created_id, 
                        $mailing.scheduled_id as scheduled_id,
                        $mailing.is_archived as archived
            FROM        $mailing
            LEFT JOIN   $job ON ( $job.mailing_id = $mailing.id AND $job.is_test = 0)
            LEFT JOIN   civicrm_contact createdContact ON ( civicrm_mailing.created_id = createdContact.id )
            LEFT JOIN   civicrm_contact scheduledContact ON ( civicrm_mailing.scheduled_id = scheduledContact.id ) 
            WHERE       $mailingACL $additionalClause  
            GROUP BY    $mailing.id ";

        if ($sort) {
            $orderBy = trim( $sort->orderBy() );
            if ( ! empty( $orderBy ) ) {
                $query .= " ORDER BY $orderBy";
            }
        }

        if ($rowCount) {
            $query .= " LIMIT $offset, $rowCount ";
        }

        if ( ! $additionalParams ) {
            $additionalParams = array( );
        }

        $dao = CRM_Core_DAO::executeQuery( $query, $additionalParams );
        
        $rows = array();
        while ($dao->fetch()) {
            $rows[] = array(
                            'id'            => $dao->id,                            
                            'name'          => $dao->name, 
                            'status'        => $dao->status ? $dao->status : 'Not scheduled', 
                            'scheduled'     => CRM_Utils_Date::customFormat($dao->scheduled_date),
                            'scheduled_iso' => $dao->scheduled_date,
                            'start'         => CRM_Utils_Date::customFormat($dao->start_date), 
                            'end'           => CRM_Utils_Date::customFormat($dao->end_date),
                            'created_by'    => $dao->created_by,
                            'scheduled_by'  => $dao->scheduled_by,
                            'created_id'    => $dao->created_id,
                            'scheduled_id'  => $dao->scheduled_id,
                            'archived'      => $dao->archived,
                            );
        }
        return $rows;
    }


    /**
     * Function to show detail Mailing report 
     *
     * @param int $id
     *
     * @static
     * @access public
     */

    static function showEmailDetails( $id )
    {
        return CRM_Utils_System::url('civicrm/mailing/report', "mid=$id");
    }

     /**
     * Delete Mails and all its associated records
     * 
     * @param  int  $id id of the mail to delete
     *
     * @return void
     * @access public
     * @static
     */
    public static function del($id) {
        if ( empty( $id ) ) {
            CRM_Core_Error::fatal( );
        }

        // delete all file attachments 
        require_once 'CRM/Core/BAO/File.php';
        CRM_Core_BAO_File::deleteEntityFile( 'civicrm_mailing',
                                             $id );

        $dao = new CRM_Mailing_DAO_Mailing();
        $dao->id = $id;
        $dao->delete( );
        
        CRM_Core_Session::setStatus(ts('Selected mailing has been deleted.'));
    }
    
    /**
     * Delete Jobss and all its associated records 
     * related to test Mailings
     *
     * @param  int  $id id of the Job to delete
     *
     * @return void
     * @access public
     * @static
     */
    public static function delJob($id) {
        if ( empty( $id ) ) {
            CRM_Core_Error::fatal( );
        }

        $dao     = new CRM_Mailing_BAO_Job();
        $dao->id = $id;
        $dao->delete();
    }

    function getReturnProperties( ) {
        $tokens =& $this->getTokens( );

        $properties = array( );
        if ( isset( $tokens['html'] ) &&
             isset( $tokens['html']['contact'] ) ) {
            $properties = array_merge( $properties, $tokens['html']['contact'] );
        }

        if ( isset( $tokens['text'] ) &&
             isset( $tokens['text']['contact'] ) ) {
            $properties = array_merge( $properties, $tokens['text']['contact'] );
        }

        if ( isset( $tokens['subject'] ) &&
             isset( $tokens['subject']['contact'] ) ) {
            $properties = array_merge( $properties, $tokens['subject']['contact'] );
        }
        
        $returnProperties = array( );
        $returnProperties['display_name'] = 
            $returnProperties['contact_id'] = $returnProperties['preferred_mail_format'] = 1;

        foreach ( $properties as $p ) {
            $returnProperties[$p] = 1;
        }

        return $returnProperties;
    }
    
    /**
     * gives required details of contacts 
     *
     * @param  array   $contactIds       of conatcts
     * @param  array   $returnProperties of required properties
     * @param  boolean $skipOnHold       don't return on_hold contact info also.
     * @param  boolean $skipDeceased     don't return deceased contact info.
     * @param  array   $extraParams      extra params
     *
     * @return array
     * @access public
     */
    function getDetails($contactIDs,
                        $returnProperties = null,
                        $skipOnHold = true,
                        $skipDeceased = true,
                        $extraParams = null ) 
    {
        $params = array( );
        foreach ( $contactIDs  as $key => $contactID ) {
            $params[] = array( CRM_Core_Form::CB_PREFIX . $contactID,
                               '=', 1, 0, 0);
        }
        
        // fix for CRM-2613
        if ( $skipDeceased ) {
            $params[] = array( 'is_deceased', '=', 0, 0, 0 );
        }
        
        //fix for CRM-3798
        if ( $skipOnHold ) {
            $params[] = array( 'on_hold', '=', 0, 0, 0 );
        }
        
        if ( $extraParams ) {
            $params = array_merge( $params, $extraParams );
        }
            
        // if return properties are not passed then get all return properties
        if ( empty( $returnProperties ) ) {
            require_once 'CRM/Contact/BAO/Contact.php';
            $fields = array_merge( array_keys(CRM_Contact_BAO_Contact::exportableFields( ) ),
                                   array( 'display_name', 'checksum', 'contact_id'));
            foreach( $fields as $key => $val) {
                $returnProperties[$val] = 1;
            }
        }

        $custom = array( );
        foreach ( $returnProperties as $name => $dontCare ) {
            $cfID = CRM_Core_BAO_CustomField::getKeyID( $name );
            if ( $cfID ) {
                $custom[] = $cfID;
            }
        }
                
        //get the total number of contacts to fetch from database.
        $numberofContacts = count( $contactIDs );

        require_once 'CRM/Contact/BAO/Query.php';
        $query   = new CRM_Contact_BAO_Query( $params, $returnProperties );
        $details = $query->apiQuery( $params, $returnProperties, NULL, NULL, 0, $numberofContacts );
        
        $contactDetails =& $details[0];
                
        foreach ( $contactIDs as $key => $contactID ) {
            if ( array_key_exists( $contactID, $contactDetails ) ) {
                
                if ( CRM_Utils_Array::value( 'preferred_communication_method', $returnProperties ) == 1 
                     && array_key_exists( 'preferred_communication_method', $contactDetails[$contactID] ) ) {
                    require_once 'CRM/Core/PseudoConstant.php';
                    $pcm = CRM_Core_PseudoConstant::pcm();
                    
                    // communication Prefferance
                    require_once 'CRM/Core/BAO/CustomOption.php';
                    $contactPcm = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR,
                                          $contactDetails[$contactID]['preferred_communication_method']);
                    $result = array( );
                    foreach ( $contactPcm as $key => $val) {
                        if ($val) {
                            $result[$val] = $pcm[$val];
                        } 
                    }
                    $contactDetails[$contactID]['preferred_communication_method'] = implode( ', ', $result );
                }
                
                foreach ( $custom as $cfID ) {
                    if ( isset ( $contactDetails[$contactID]["custom_{$cfID}"] ) ) {
                        $contactDetails[$contactID]["custom_{$cfID}"] = 
                            CRM_Core_BAO_CustomField::getDisplayValue( $contactDetails[$contactID]["custom_{$cfID}"],
                                                                       $cfID, $details[1] );
                    }
                }
                
                //special case for greeting replacement
                foreach ( array( 'email_greeting', 'postal_greeting', 'addressee' ) as $val ) {
                    if ( CRM_Utils_Array::value( $val, $contactDetails[$contactID] ) ) {
                        $contactDetails[$contactID][$val] = $contactDetails[$contactID]["{$val}_display"];
                    }
                }
            }
        }

        // also call a hook and get token details
        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::tokenValues( $details[0], $contactIDs );
        return $details; 
    }

    /**
     * Function to build the  compose mail form
     * @param   $form 
     * @return None
     * @access public
     */
    
    public function commonCompose ( &$form )
    {
        //get the tokens.
        $tokens = CRM_Core_SelectValues::contactTokens( );

        //token selector for subject
        //CRM-5058
        $form->add( 'select', 'token3',  ts( 'Insert Token' ), 
                    $tokens , false, 
                    array(
                          'size'     => "5",
                          'multiple' => true,
                          'onchange' => "return tokenReplText(this);"
                          )
                    );
        
        if ( CRM_Utils_System::getClassName( $form )  == 'CRM_Mailing_Form_Upload' ) {
            $tokens = array_merge( CRM_Core_SelectValues::mailingTokens( ), $tokens );
        }

        //sorted in ascending order tokens by ignoring word case
        natcasesort($tokens);
        $form->assign( 'tokens', json_encode( $tokens ) );
        
        $form->add( 'select', 'token1',  ts( 'Insert Tokens' ), 
                    $tokens , false, 
                    array(
                          'size'     => "5",
                          'multiple' => true,
                          'onchange' => "return tokenReplText(this);"
                          )
                    );
        
        $form->add( 'select', 'token2',  ts( 'Insert Tokens' ), 
                    $tokens , false,
                    array(
                          'size'     => "5",
                          'multiple' => true,
                          'onchange' => "return tokenReplHtml(this);"
                          )
                    );
        
               
        require_once 'CRM/Core/BAO/MessageTemplates.php';
        $form->_templates = CRM_Core_BAO_MessageTemplates::getMessageTemplates( false );
        if ( !empty( $form->_templates ) ) {
            $form->assign('templates', true);
            $form->add('select', 'template', ts('Use Template'),
                       array( '' => ts( '- select -' ) ) + $form->_templates, false,
                       array('onChange' => "selectValue( this.value );") );
            $form->add('checkbox','updateTemplate',ts('Update Template'), null);
        } 
        
        $form->add('checkbox','saveTemplate',ts('Save As New Template'), null,false,
                          array( 'onclick' => "showSaveDetails(this);" ));
        $form->add('text','saveTemplateName',ts('Template Title'));
      
       
        //insert message Text by selecting "Select Template option"
        $form->add( 'textarea', 
                    'text_message', 
                    ts('Plain-text format'),
                    array('cols' => '80', 'rows' => '8',
                          'onkeyup' => "return verify(this)"));
        $form->addWysiwyg( 'html_message',
                           ts('HTML format'),
                           array('cols' => '80', 'rows' => '8',
                                 'onkeyup' =>"return verify(this)" ) );
 
    }

    /**
     * Function to build the  compose PDF letter form
     * @param   $form 
     * @return None
     * @access public
     */
    
    public function commonLetterCompose ( &$form )
    {
        //get the tokens.
        $tokens = CRM_Core_SelectValues::contactTokens( );
        if ( CRM_Utils_System::getClassName( $form )  == 'CRM_Mailing_Form_Upload' ) {
            $tokens = array_merge( CRM_Core_SelectValues::mailingTokens( ), $tokens );
        }

        //sorted in ascending order tokens by ignoring word case
        natcasesort($tokens);

        $form->assign( 'tokens', json_encode( $tokens ) );

        $form->add( 'select', 'token1',  ts( 'Insert Tokens' ), 
                    $tokens , false, 
                    array(
                          'size'     => "5",
                          'multiple' => true,
                          'onchange' => "return tokenReplHtml(this);"
                          )
                    );
        
        require_once 'CRM/Core/BAO/MessageTemplates.php';
        $form->_templates = CRM_Core_BAO_MessageTemplates::getMessageTemplates( false );
        if ( !empty( $form->_templates ) ) {
            $form->assign('templates', true);
            $form->add('select', 'template', ts('Select Template'),
                       array( '' => ts( '- select -' ) ) + $form->_templates, false,
                       array('onChange' => "selectValue( this.value );") );
            $form->add('checkbox','updateTemplate',ts('Update Template'), null);
        } 
        
        $form->add('checkbox','saveTemplate',ts('Save As New Template'), null,false,
                          array( 'onclick' => "showSaveDetails(this);" ));
        $form->add('text','saveTemplateName',ts('Template Title'));
      
       
        $form->addWysiwyg( 'html_message',
                           ts('Your Letter'),
                           array('cols' => '80', 'rows' => '8',
                                 'onkeyup' =>"return verify(this)" ) );
        $action = CRM_Utils_Request::retrieve( 'action', 'String', $this, false );
        if ( ( CRM_Utils_System::getClassName( $form )  == 'CRM_Contact_Form_Task_PDF' )&& 
             $action == CRM_Core_Action::VIEW ) { 
            $form->freeze( 'html_message' );
        }
        
    }
    
    /**
     * Get the search based mailing Ids
     *
     * @return array $mailingIDs, searched base mailing ids.
     * @access public
     */
    public function searchMailingIDs( ) 
    {
        $group   = CRM_Mailing_DAO_Group::getTableName( );
        $mailing = self::getTableName();
        
        $query = "
SELECT  $mailing.id as mailing_id
  FROM  $mailing, $group
 WHERE  $group.mailing_id = $mailing.id
   AND  $group.group_type = 'Base'"; 
        
        $searchDAO = CRM_Core_DAO::executeQuery( $query );
        $mailingIDs = array( );
        while ( $searchDAO->fetch( ) ) {
            $mailingIDs[] = $searchDAO->mailing_id;
        }
        
        return $mailingIDs;
    }

    /**
     * Get the content/components of mailing based on mailing Id
     *
     * @param $report array of mailing report
     * 
     * @param $form reference of this  
     *
     * @return $report array content/component.
     * @access public
     */
    public function getMailingContent( &$report, &$form ) 
    {
        require_once 'CRM/Mailing/BAO/Component.php';
        if ($report['mailing']['header_id']) { 
            $header = new CRM_Mailing_BAO_Component();
            $header->id = $report['mailing']['header_id'];
            $header->find(true);
            $htmlHeader = $header->body_html;
            $textHeader = $header->body_text;
        }
        
        if ($report['mailing']['footer_id']) { 
            $footer = new CRM_Mailing_BAO_Component();
            $footer->id = $report['mailing']['footer_id'];
            $footer->find(true);
            $htmlFooter = $footer->body_html;
            $textFooter = $footer->body_text;
        }
        
        $text = CRM_Utils_Request::retrieve( 'text', 'Boolean', $form );
        if ( $text ) {
            echo "<pre>{$textHeader}</br>{$report['mailing']['body_text']}</br>{$textFooter}</pre>";
            CRM_Utils_System::civiExit( );
        }
        
        $html = CRM_Utils_Request::retrieve( 'html', 'Boolean', $form );
        if ( $html ) {
            echo $htmlHeader . $report['mailing']['body_html'] . $htmlFooter;
            CRM_Utils_System::civiExit( );
        }
        
        if ( ! empty( $report['mailing']['body_text'] ) ) {
            $url   = CRM_Utils_System::url( 'civicrm/mailing/report', 'reset=1&text=1&mid=' . $form->_mailing_id );
            $popup =  "javascript:popUp(\"$url\");";
            $form->assign( 'textViewURL' , $popup  );
        }
        
        if ( ! empty( $report['mailing']['body_html'] ) ) {
            $url   = CRM_Utils_System::url( 'civicrm/mailing/report', 'reset=1&html=1&mid=' . $form->_mailing_id );
            $popup =  "javascript:popUp(\"$url\");";
            $form->assign( 'htmlViewURL' , $popup  );
        }
        
        require_once 'CRM/Core/BAO/File.php';
        $report['mailing']['attachment'] = CRM_Core_BAO_File::attachmentInfo( 'civicrm_mailing',
                                                                              $form->_mailing_id );
        return $report;
    }

}



<?php

require_once 'api/api.php';
require_once 'CRM/Core/Page.php';
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/System.php';
require_once 'CRM/Utils/Array.php';
require_once 'CRM/Core/BAO/Tag.php';


// TODO: The DAO layer is injection safe...right?
// TODO: Needs to return good responses when things go wrong!
// TODO: How do you tell if a save was successful?
class CRM_Tag_AJAX extends CRM_Core_Page {
    const SUCCESS = 1;
    const ERROR = 0;
    // The full set of fields that can be attached to each node.
    // If you don't want a field, just comment it out.
    //
    // id, parent_id, and is_tagset are required.
    // sets the role access to 13 (crmVolunteer, will be modified on access)
    static $ROLE_ACCESS = 13;
    static public $TAG_FIELDS = array(
            'id',
            'name',
            'description',
            'parent_id',
            'is_selectable',
            'is_reserved',
            'is_tagset',
            'used_for',
            'created_id',
            'created_display_name',
            'created_date'
        );
    static public $USER_ROLES = array(
            "administer" => 9,
            "manage"     => 10,
            "edit"       => 12,
            "volunteer"  => 13
        );
    static function _build_tree($root, &$nodes) {
        // We need a copy to do looping
        $current_nodes = $nodes;
        foreach($current_nodes as $index=>$node) {
            if ($node['parent_id'] === $root['id']) {
                unset($nodes[$index]);
                $root['children'][] = self::_build_tree($node,$nodes);
            }
        }
        return $root;
    }

    static function _build_node($source, $entity_counts) {
        $node = array();
        foreach(self::$TAG_FIELDS as $field){
            $node[$field] = $source->$field;
        }
        $node['children'] = array();
        return $node;
    }

    static function _require($key, $store, $msg) {
        if(array_key_exists($key, $store)){
            return $store[$key];
        } else {
            echo "ERROR: ",$msg;
            exit();
        }
    }

    static function tag_tree() {
        self::check_user_level("volunteer");

        //If they request entity counts, build that into the tree as well.
        if(CRM_Utils_Array::value('entity_counts', $_GET)) {
            $entity_table = CRM_Core_DAO::escapeString(self::_require('entity_table', $_GET, "`entity_table` parameter is required."));
            // There is definitely nothing like this in the civicrm_api. Using
            // the DAO layer is way too slow when we get to hundreds of results.
            // Hand rolled SQL it is...
            $dao = new CRM_Core_DAO();
            $conn = $dao->getDatabaseConnection()->connection;
            $result = mysql_query("
                SELECT tag.id, count(entity_tag.entity_id) as entity_count
                FROM civicrm_tag as tag
                  LEFT JOIN civicrm_entity_tag as entity_tag ON (
                         tag.id = entity_tag.tag_id AND
                         entity_tag.entity_table = '$entity_table')
                  LEFT JOIN $entity_table as entity ON (
                         entity.id = entity_tag.entity_id)
                WHERE tag.used_for LIKE '%$entity_table%'
                  AND entity.is_deleted = 0
                GROUP BY tag.id", $conn);


            $entity_counts = array();
            while($row = mysql_fetch_assoc($result))
                $entity_counts[$row['id']] = $row['entity_count'];

        } else {
            $entity_counts = null;
        }
        // We need to build a list of tags ordered by hierarchy and sorted by
        // name. Instead of recursively making mysql queries, we'll make one
        // big query and build the heirarchy with the recursive algorithm below.
        //
        //Can't use the API because it doesn't support sorting (yet) and we need
        //the tags to be sorted in alphabetical order on each level. Can't use
        //the DAO object because it doesn't support queries by LIKE. Atleast, I
        //don't know how you would do it, maybe it can be done.
        $tags = CRM_Core_DAO::executeQuery("
                SELECT tag.*, contact.display_name as created_display_name
                FROM civicrm_tag as tag
                LEFT JOIN civicrm_contact as contact ON contact.id=tag.created_id
                WHERE used_for LIKE %1
                ORDER BY tag.name
            ",array( 1 => array("%$entity_table%",'String')));

        // Sort all the tags into root and nodes buckets. This simpifies the process
        // to building the root nodes by moving tags from the nodes bucket.
        while($tags->fetch()) {
            if (!$tags->parent_id)
                $roots[] = self::_build_node($tags, $entity_counts);
            else
                $nodes[] = self::_build_node($tags, $entity_counts);
        }

        // Recursively build the tree from each "root" using the "nodes"
        // as the available building blocks for each subtree
        $tree = array();
        foreach($roots as $root) {
            $tree[] = self::_build_tree($root,$nodes);
        }

        echo json_encode(array("code"=>self::SUCCESS,"message"=>$tree));
        CRM_Utils_System::civiExit();
    }
    static function get_entity_tag(){
        self::check_user_level("volunteer");
        if(array_key_exists('entity_id', $_GET) && array_key_exists('entity_table', $_GET)) {
            $entity_id = $_GET['entity_id'];
            $entity_table = $_GET['entity_table'];
            //Get the tags for the specifed entity
            $params = array('version'=>3,
                            'entity_table'=>$entity_table,
                            'entity_id'=>$entity_id);
            $result = civicrm_api('entity_tag', 'get', $params);
            echo $entity_table;
            $entity_tags = array();
            foreach($result['values'] as $entity_tag)
                $entity_tags[] = $entity_tag['tag_id'];
        } else {
            $entity_tags = null;
        }
        echo json_encode(array("code" => self::SUCCESS, "message" => $entity_tags));
        CRM_Utils_System::civiExit();
    }

    static function tag_create() {
        self::check_user_level("edit");
        // Extract the new tag parameters
        $tag = array('version'=>3);
        foreach(self::$TAG_FIELDS as $field) {
            $value = CRM_Utils_Array::value($field, $_GET);
            if($value) {
                $tag[$field] = $value;
            }
        }
        $result = civicrm_api('tag', 'create', $tag);
        if($result['is_error'])
            echo json_encode(array("code"=>self::ERROR, "message"=>$result['error_message']));
        else
            echo json_encode(array("code" => self::SUCCESS, "message" => $result['values'][$result['id']]));
        CRM_Utils_System::civiExit();
    }

    static function tag_update() {
        self::check_user_level("edit");
        // Get the existing tag for manipulation
        $tag_id = self::_require('id', $_GET, '`id` parameter is required to identify the tag to be updated.');
        $params = array('version'=>3, 'id'=>$tag_id);
        $result = civicrm_api('tag', 'get', $params);

        // A bad id will cause an error
        if($result['is_error']) {
            echo json_encode(array('code'=>self::ERROR, 'message'=>$result['error_message']));
            CRM_Utils_System::civiExit();
        }

        // Populate the parameters with the new values for update
        $tag = $result['values'][$result['id']];
        foreach(self::$TAG_FIELDS as $field) {
            $params[$field] = CRM_Utils_Array::value($field, $_GET, $tag[$field]);
        }

        // create actually does an update if the id already exists...
        $result = civicrm_api('tag', 'create', $params);
        if($result['is_error'])
            echo json_encode(array("code"=>self::ERROR, "message"=>$result['error_message']));
        else
            echo json_encode(array("code" => self::SUCCESS, "message" => $result['values'][$result['id']]));
        CRM_Utils_System::civiExit();
    }

    static function tag_delete() {
        self::check_user_level("edit");
        $id = self::_require('id', $_GET, '`id` of the tag to be deleted is required.');
        $params = array('version'=>3, 'tag_id'=>$id);
        $result = civicrm_api('tag', 'delete', $params);

        // Result information is hard to work with
        if($result['is_error'])
            echo json_encode(array("code"=>self::ERROR, "message"=>$result['error_message']));
        else if($result['count'])
            echo json_encode(array("code" => self::SUCCESS, "message" => $id));
        else
            echo json_encode(array("code" => self::SUCCESS, "message" => "WARNING: Tag was (probably) not found?"));
        CRM_Utils_System::civiExit();
    }

    static function entity_tag_create() {
        self::check_user_level("volunteer");
        $tag_id = self::_require('tag_id', $_GET, '`tag_id` parameter is required to identify the tag to apply.');
        $entity_id = self::_require('entity_id', $_GET, '`entity_id` parameter is required to identify the entity being tagged.');
        $entity_table = self::_require('entity_table', $_GET, '`entity_table` parameter is required to identify the entity being tagged.');

        // Allow use of tag_id[]=1&tag_id[]=2 for creating multiple tags at once.
        if (is_array($tag_id)) {
            $tag_ids = $tag_id;
        } else {
            $tag_ids = array($tag_id);
        }

        // Create all the tags and collect the results of each operations
        $results = array();
        foreach($tag_ids as $new_tag_id) {
            $params = array('version'=>3,
                            'tag_id'=>$new_tag_id,
                            'entity_id'=>$entity_id,
                            'entity_table'=>$entity_table);
            $result = civicrm_api('entity_tag', 'create', $params);

            // Error handling for entity tags is somewhat less informative...
            if($result['is_error'])
                $results[] = array("code"=>self::ERROR, 'message'=>$result['error_message']);
            else if($result['added'])
                $results[] = array("code" => self::SUCCESS, "message"=>"SUCCESS");
            else
                $results[] = array("code" => self::ERROR, "message"=>"WARNING: Entity tag [$new_tag_id] already exists.");
        }

        // Response is a json array of results if an array of ids was passed in.
        if (is_array($tag_id)) {
            echo json_encode($results);
        } else {
            echo json_encode($results[0]);
        }

        CRM_Utils_System::civiExit();
    }

    static function entity_tag_delete() {
        self::check_user_level("volunteer");
        $tag_id = self::_require('tag_id', $_GET, '`tag_id` parameter is required to identify the tag to apply.');
        $entity_id = self::_require('entity_id', $_GET, '`entity_id` parameter is required to identify the entity being tagged.');
        $entity_table = self::_require('entity_table', $_GET, '`entity_table` parameter is required to identify the entity being tagged.');


        // Allow use of tag_id[]=1&tag_id[]=2 for creating multiple tags at once.
        if (is_array($tag_id)) {
            $tag_ids = $tag_id;
        } else {
            $tag_ids = array($tag_id);
        }

        // Create all the tags and collect the results of each operations
        $results = array();
        foreach($tag_ids as $new_tag_id) {
            // The API doesn't let you identify entity_tags by entity tag id
            $params = array('version'=>3,
                            'tag_id'=>$new_tag_id,
                            'entity_id'=>$entity_id,
                            'entity_table'=>$entity_table);
            $result = civicrm_api('entity_tag', 'delete', $params);

            // Error handling for entity tags is somewhat less informative...
            if($result['is_error'])
                $results[] = array("code"=>self::ERROR, 'message'=>$result['error_message']);
            else if($result['removed'])
                $results[] = array("code" => self::SUCCESS, "message"=>"SUCCESS");
            else
                $results[] = array("code" => self::ERROR, "message"=>"WARNING: Entity tag [$new_tag_id] not found.");
        }

        // Response is a json array of results if an array of ids was passed in.
        if (is_array($tag_id)) {
            echo json_encode($results);
        } else {
            echo json_encode($results[0]);
        }
        CRM_Utils_System::civiExit();
    }
    // Checking User levels,
    // Avaiable thought api call, or locally
    // args = return true / false toggles return / echo 
    static function check_user_against_role($role_name){
        // looking for "administer", "manage", "edit", "volunteer"
        $role_access = self::$ROLE_ACCESS;
        $user_roles = self::$USER_ROLES;
        if( $role_access <= $user_roles[$role_name] ) {
            return true;
        }
        return false;
    }
    static function check_user_level($role_name) {
        // declares drupal global user roles
        global $user;
        $array_of_roles = array_keys(($user->roles));

        // pops out 1&2 from user roles (anon & authenticated,)
        while(min($array_of_roles) < 3)
        {
            array_shift($array_of_roles);
        }
        self::$ROLE_ACCESS = min($array_of_roles);
        $role = self::check_user_against_role($role_name);

        // check if the users are accessing their own page
        $entity_id = 0;
        if(array_key_exists('entity_id', $_GET))
        {
            // checks entity_id (activity) to see if their user id belongs to the activity
            $entity_id = $_GET['entity_id'];
            $entity_table = CRM_Core_DAO::escapeString(self::_require('entity_table', $_GET, "`entity_table` parameter is required."));
            if($entity_table == "civicrm_activity")
            {
                // checks the m2m table activity_target on activity_id
                $dao = new CRM_Core_DAO();
                $conn = $dao->getDatabaseConnection()->connection;
                $result = mysql_query("
                    SELECT target_contact_id from civicrm_activity_target
                        WHERE activity_id = $entity_id", $conn);
                $activity_contact_target = [];
                while($row = mysql_fetch_assoc($result))
                    array_push($activity_contact_target, $row["target_contact_id"]);
            }
            // grabs the current user id
            $session = CRM_Core_Session::singleton();
            $userid = $session->get('userID');
            // 
            if(!in_array($userid, $activity_contact_target))
            {
                $role = false;
            }
        }
        $message = ($role == true ) ? 'SUCCESS' : "WARNING: Bad user level"; 
        $output = array(
            "code"=>$role,
            "userId"=>$userid,
            "entity_table"=>$entity_table,
            "entity_id"=>$entity_id,
            "message"=> $message
        );

        if($return == 'true'){
            return $output;
        }else{
            echo json_encode($output);
        } 
        CRM_Utils_System::civiExit();
    }
}

?>

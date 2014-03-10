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

        if($entity_counts !== null)
            $node['entity_count'] = CRM_Utils_Array::value($node['id'], $entity_counts, 0);

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
        $stop = self::check_user_level('true');
        if($stop['code'] == false){
            echo json_encode(array("code" => self::ERROR, "message"=>"WARNING: Bad user level."));
            CRM_Utils_System::civiExit();
        }
        $start = microtime(TRUE);
        $entity_type = CRM_Core_DAO::escapeString(self::_require('entity_type', $_GET, "`entity_type` parameter is required."));

        //If they request entity counts, build that into the tree as well.
        $ec_start = microtime(TRUE);
        $do_entity_counts = CRM_Utils_Array::value('entity_counts', $_GET);
        if($do_entity_counts && strtolower($do_entity_counts) != "false") {

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
                         entity_tag.entity_table = '$entity_type')
                  LEFT JOIN $entity_type as entity ON (
                         entity.id = entity_tag.entity_id)
                WHERE tag.used_for LIKE '%$entity_type%'
                  AND entity.is_deleted = 0
                GROUP BY tag.id", $conn);

            $entity_counts = array();
            while($row = mysql_fetch_assoc($result))
                $entity_counts[$row['id']] = $row['entity_count'];

        } else {
            $entity_counts = null;
        }
        $ec_time = microtime(TRUE)-$ec_start;

        // If they pass in an entity_id we can also get information on which tags apply
        // to the specified entity and include that along with the tree
        $et_start = microtime(TRUE);
        // if(array_key_exists('entity_id', $_GET)) {
        //     $entity_id = $_GET['entity_id'];

        //     //Get the tags for the specifed entity
        //     $params = array('version'=>3,
        //         'entity_type'=>$entity_type,
        //         'entity_id'=>$entity_id);
        //     $result = civicrm_api('entity_tag', 'get', $params);

        //     $entity_tags = array();
        //     foreach($result['values'] as $entity_tag)
        //         $entity_tags[] = $entity_tag['tag_id'];

        // } else {
        //     $entity_tags = null;
        // }
        $et_time = microtime(TRUE)-$et_start;

        // We need to build a list of tags ordered by hierarchy and sorted by
        // name. Instead of recursively making mysql queries, we'll make one
        // big query and build the heirarchy with the recursive algorithm below.
        //
        //Can't use the API because it doesn't support sorting (yet) and we need
        //the tags to be sorted in alphabetical order on each level. Can't use
        //the DAO object because it doesn't support queries by LIKE. Atleast, I
        //don't know how you would do it, maybe it can be done.
        $bt_start = microtime(TRUE);
        $tags = CRM_Core_DAO::executeQuery("
                SELECT tag.*, contact.display_name as created_display_name
                FROM civicrm_tag as tag
                LEFT JOIN civicrm_contact as contact ON contact.id=tag.created_id
                WHERE used_for LIKE %1
                ORDER BY tag.name
            ",array( 1 => array("%$entity_type%",'String')));

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
        $bt_time = microtime(TRUE)-$bt_start;

        echo json_encode(array("code"=>self::SUCCESS,"message"=>$tree,'build_time'=>(microtime(TRUE)-$start),'ec_time'=>$ec_time,'et_time'=>$et_time,'bt_time'=>$bt_time));
        CRM_Utils_System::civiExit();
    }
    static function get_entity_tag(){
        $stop = self::check_user_level('true');
        if($stop['code'] == false){
            echo json_encode(array("code" => self::ERROR, "message"=>"WARNING: Bad user level."));
            CRM_Utils_System::civiExit();
        }
        if(array_key_exists('entity_id', $_GET)) {
            $entity_id = $_GET['entity_id'];

            //Get the tags for the specifed entity
            $params = array('version'=>3,
                'entity_type'=>$entity_type,
                'entity_id'=>$entity_id);
            $result = civicrm_api('entity_tag', 'get', $params);

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
        $stop = self::check_user_level('true');
        if($stop['code'] == false){
            echo json_encode(array("code" => self::ERROR, "message"=>"WARNING: Bad user level."));
            CRM_Utils_System::civiExit();
        }
        // Extract the new tag parameters
        $tag = array('version'=>3);
        foreach(self::$TAG_FIELDS as $field) {
          $value = CRM_Utils_Array::value($field, $_GET);
          //NYSS 6558
          if ( isset($value) ) {
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
        $stop = self::check_user_level('true');
        if($stop['code'] == false){
            echo json_encode(array("code" => self::ERROR, "message"=>"WARNING: Bad user level."));
            CRM_Utils_System::civiExit();
        }
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
        $stop = self::check_user_level('true');
        if($stop['code'] == false){
            echo json_encode(array("code" => self::ERROR, "message"=>"WARNING: Bad user level."));
            CRM_Utils_System::civiExit();
        }
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
        $stop = self::check_user_level('true');
        if($stop['code'] == false || $stop['view_only'] == true){
            echo json_encode(array("code" => self::ERROR, "message"=>"WARNING: Bad user level."));
            CRM_Utils_System::civiExit();
        }
        $tag_id = self::_require('tag_id', $_GET, '`tag_id` parameter is required to identify the tag to apply.');
        $entity_id = self::_require('entity_id', $_GET, '`entity_id` parameter is required to identify the entity being tagged.');
        $entity_type = self::_require('entity_type', $_GET, '`entity_type` parameter is required to identify the entity being tagged.');

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
                            'entity_type'=>$entity_type);
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
        $stop = self::check_user_level('true');
        if($stop['code'] == false || $stop['view_only'] == true){
            echo json_encode(array("code" => self::ERROR, "message"=>"WARNING: Bad user level."));
            CRM_Utils_System::civiExit();
        }
        $tag_id = self::_require('tag_id', $_GET, '`tag_id` parameter is required to identify the tag to apply.');
        $entity_id = self::_require('entity_id', $_GET, '`entity_id` parameter is required to identify the entity being tagged.');
        $entity_type = self::_require('entity_type', $_GET, '`entity_type` parameter is required to identify the entity being tagged.');


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
                            'entity_type'=>$entity_type);
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
    static function check_user_level($return) {
        $start = microtime(TRUE);
        $call = self::_require('call_uri', $_GET, '`call_uri` parameter is required to identify the tag to apply.');
        $call_uri = parse_url($call);

        parse_str($call_uri['query'],$cid);
        $entityId = $cid['cid'];

        $session = CRM_Core_Session::singleton();
        $userid = $session->get('userID');

        // different functionality for different areas of the UI.
        switch ($call_uri['path']) {
            case '/civicrm/contact/add':
                // if can add contact then user can add tag.
                $role = CRM_Core_Permission::check('add contacts');
                break;
            case '/civicrm/contact/view':
                if($userid == $entityId){ // if is viewing and is my contact record -> can edit
                    $role = true;
                }else{ // else -> check permissions to edit
                    $role = CRM_Core_Permission::check('edit all contacts');
                    if( $role == false ) {
                        $role = CRM_Core_Permission::check('view all contacts');
                        $view_only = true;
                    }
                }
                break;
            default:
                $role = CRM_Core_Permission::check('edit all contacts');
                break;
        }

        $message = ($role == true )? 'SUCCESS' : "WARNING: Bad user level";
        $output = array(
            "code"=>$role,
            "view_only"=>$view_only,
            "userId"=>$userid,
            "message"=> $message,
            'build_time'=>(microtime(TRUE)-$start),
            'bt_time'=>$bt_time
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

<?php

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
    const NOT_FOUND = 0;
    const NOT_DELETED = 2;
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

    static function _build_node($source) {
        $node = array();
        foreach(self::$TAG_FIELDS as $field)
            $node[$field] = $source->$field;
        $node['children'] = array();
        return $node;
    }

    static function tag_tree() {
        // We need to build a list of tags ordered by hierarchy and sorted by
        // name. Instead of recursively making mysql queries, we'll make one
        // big query and build the heirarchy with the recursive algorithm below.
        $tags = new CRM_Core_BAO_Tag();
        $tags->used_for = $_GET['used_for'];
        $tags->orderBy('name');
        $tags->find();

        // Sort all the tags into root and nodes buckets. This simpifies the process
        // to building the root nodes by moving tags from the nodes bucket.
        while ($tags->fetch()) {
            if (!$tags->parent_id && $tags->is_tagset==0)
                $roots[] = self::_build_node($tags);
            else
                $nodes[] = self::_build_node($tags);
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

    static function create() {
        $tag = new CRM_Core_BAO_Tag();
        foreach(self::$TAG_FIELDS as $field) {
            $value = CRM_Utils_Array::value($field, $_GET);
            if($value) {
                $tag->$field = $value;
            }
        }
        $tag->insert();
        echo json_encode(array(
            "code" => self::SUCCESS,
            "message" => $tag->id));
        CRM_Utils_System::civiExit();
    }

    static function update() {
        $tag = new CRM_Core_BAO_Tag();
        $tag->id = CRM_Utils_Array::value('id', $_GET);
        if($tag->find() && $tag->fetch()) {
            // Use the current value if a new value wasn't passed in
            foreach(self::$TAG_FIELDS as $field) {
                $tag->$field = CRM_Utils_Array::value($field, $_GET, $tag->$field);
            }
            $tag->update();

            $result = array(
                "code" => self::SUCCESS,
                "message" => $tag->id);

        } else {
            $result = array(
                "code" => self::NOT_FOUND,
                "message" => "Tag with id {$tag->id} was not found. Update failure");
        }

        echo json_encode($result);
        CRM_Utils_System::civiExit();
    }

    static function delete() {
        $tag = new CRM_Core_BAO_Tag();
        $tag->id = CRM_Utils_Array::value('id', $_GET);
        if( $tag->delete() )
            $result = array(
                "code" => self::SUCCESS,
                "message" => $tag->id);
        else
            $result = array(
                "code" => self::NOT_DELETED,
                "message" => "Tag couldn't be deleted");

        echo json_encode($result);
        CRM_Utils_System::civiExit();
    }
}

?>
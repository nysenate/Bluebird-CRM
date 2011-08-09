<?php

require_once 'CRM/Core/Page.php';
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/System.php';


class CRM_Tag_AJAX extends CRM_Core_Page {

    // The full set of fields that cna be attached to each node.
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
        $used_for = $_GET['used_for'];

        // We need to build a list of tags ordered by hierarchy and sorted by
        // name. Instead of recursively making mysql queries, we'll make one
        // big query and build the heirarchy with the recursive algorithm below.
        $query = "SELECT *
                  FROM civicrm_tag
                  WHERE used_for LIKE '%{$used_for}%'
                  ORDER BY name";
        $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray, true, null, false, false );

        // Grab all the tags one by one and sort them into either the "root" or "nodes" bucket.
        while ($dao->fetch()) {
            if (!$dao->parent_id && $dao->is_tagset==0)
                $roots[] = self::_build_node($dao);
            else
                $nodes[] = self::_build_node($dao);
        }

        // Recursively build the tree from each "root" using the "nodes"
        // as the available building blocks for each subtree
        $tree = array();
        foreach($roots as $root) {
            $tree[] = self::_build_tree($root,$nodes);
        }

        echo json_encode($tree);
        CRM_Utils_System::civiExit( );
    }
}

?>
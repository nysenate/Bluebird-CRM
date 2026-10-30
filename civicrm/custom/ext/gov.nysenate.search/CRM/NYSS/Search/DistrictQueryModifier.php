<?php

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CRM_NYSS_Search_DistrictQueryModifier
{

    /** @var int[] list of custom value ids that are of type int */
    private static $district_int_fields = [46, 47, 48, 49, 50, 51, 53, 54, 55];

    /** @var int[] list of custom value ids that are of type string */
    private static $district_str_fields = [52, 56];

    public static function isDistrictIntegerField(int $field_id) : bool {
        return in_array($field_id, self::$district_int_fields);
    }

    public static function isDistrictStringField(int $field_id) : bool {
        return in_array($field_id, self::$district_str_fields);
    }

    /**
     * Allow comma-delimited multi-value searches for "String"" values in civicrm_value_district_information_7
     * @param GenericHookEvent $event
     * @return void
     * @note refactored from civicrm/custom/php/CRM/Core/BAO/CustomQuery.php.
     * @note no Redmine issue number provided... but possibly related to #4735, #4922, #4969
     */
    public static function modifyStringFieldClause(int $field_id, string &$op, mixed &$value) {
        if ($op == '=' && self::isDistrictStringField($field_id)) {
            $op = 'IN';
            $value = array_map('trim', explode('[:comma:]', $value));
        }
    }

    /** check if we should "skip" this field.  We avoid adding where clauses for integer fields that are empty */
    public static function skipIntegerFieldClause(int $field_id, string $op, $value) : bool {
        if (self::isDistrictIntegerField($field_id) && $op == '=' && empty($value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Allow comma-delimited multi-value searches for "int" values in civicrm_value_district_information_7
     * Also allow % queries
     * @param GenericHookEvent $event
     * @return void
     * @note refactored from civicrm/custom/php/CRM/Core/BAO/CustomQuery.php.
     * @note no Redmine issue number provided except NYSS 13461, which focuses on LIKE/%
     */
    public static function modifyIntegerFieldClause(int $field_id, string &$op, mixed &$value, string &$data_type) {
        if (self::isDistrictIntegerField($field_id)) {
            if ($op == '=') {
                if (empty($value)) {
                    return;
                }
                if (str_contains($value, ',')) {
                    $op = 'IN';
                    $value = array_map('trim', explode(',', $value));
                }
            }
            // NYSS 13461 - allow a percentage symbol
            elseif ($op == 'LIKE') {
                $data_type = 'String';
            }
        }
    }
}
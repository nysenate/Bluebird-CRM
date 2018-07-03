<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2015                                |
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

namespace Civi\Api4\Query;

use Civi\API\SelectQuery;
use Civi\Api4\Event\Events;
use Civi\Api4\Event\PostSelectQueryEvent;
use Civi\Api4\Service\Schema\Joinable\CustomGroupJoinable;
use Civi\Api4\Service\Schema\Joinable\Joinable;
use Civi\Api4\Utils\FormattingUtil;
use CRM_Core_DAO_AllCoreTables as TableHelper;
use CRM_Core_DAO_CustomField as CustomFieldDAO;
use CRM_Utils_Array as UtilsArray;

/**
 * A query `node` may be in one of three formats:
 *
 * * leaf: [$fieldName, $operator, $criteria]
 * * negated: ['NOT', $node]
 * * branch: ['OR|NOT', [$node, $node, ...]]
 *
 * Leaf operators are one of:
 *
 * * '=', '<=', '>=', '>', '<', 'LIKE', "<>", "!=",
 * * "NOT LIKE", 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN',
 * * 'IS NOT NULL', or 'IS NULL'.
 */
class Api4SelectQuery extends SelectQuery {

  /**
   * @var int
   */
  protected $apiVersion = 4;

  /**
   * @var array
   *   Maps select fields to [<table_alias>, <column_alias>]
   */
  protected $fkSelectAliases = [];

  /**
   * @var Joinable[]
   *   The joinable tables that have been joined so far
   */
  protected $joinedTables = [];

  /**
   * Why walk when you can
   *
   * @return array|int
   */
  public function run() {
    $this->preRun();
    $baseResults = parent::run();
    $event = new PostSelectQueryEvent($baseResults, $this);
    \Civi::dispatcher()->dispatch(Events::POST_SELECT_QUERY, $event);

    return $event->getResults();
  }

  /**
   * Gets all FK fields and does the required joins
   */
  protected function preRun() {
    $whereFields = array_column($this->where, 0);
    $allFields = array_merge($whereFields, $this->select, $this->orderBy);
    $dotFields = array_unique(array_filter($allFields, function ($field) {
      return strpos($field, '.') !== FALSE;
    }));

    foreach ($dotFields as $dotField) {
      $this->joinFK($dotField);
    }
  }

  /**
   * @inheritDoc
   */
  protected function buildWhereClause() {
    foreach ($this->where as $clause) {
      $sql_clause = $this->treeWalkWhereClause($clause);
      $this->query->where($sql_clause);
    }
  }

  /**
   * @inheritDoc
   */
  protected function buildOrderBy() {
    foreach ($this->orderBy as $field => $dir) {
      if ($dir !== 'ASC' && $dir !== 'DESC') {
        throw new \API_Exception("Invalid sort direction. Cannot order by $field $dir");
      }
      if ($this->getField($field)) {
        $this->query->orderBy(self::MAIN_TABLE_ALIAS . '.' . $field . " $dir");
      }
      // TODO: Handle joined fields, custom fields, etc.
      else {
        throw new \API_Exception("Invalid sort field. Cannot order by $field $dir");
      }
    }
  }

  /**
   * Recursively validate and transform a branch or leaf clause array to SQL.
   *
   * @param array $clause
   * @return string SQL where clause
   *
   * @uses validateClauseAndComposeSql() to generate the SQL etc.
   * @todo if an 'and' is nested within and 'and' (or or-in-or) then should
   * flatten that to be a single list of clauses.
   */
  protected function treeWalkWhereClause($clause) {
    switch ($clause[0]) {
      case 'OR':
      case 'AND':
        // handle branches
        if (count($clause[1]) === 1) {
          // a single set so AND|OR is immaterial
          return $this->treeWalkWhereClause($clause[1][0]);
        }
        else {
          $sql_subclauses = [];
          foreach ($clause[1] as $subclause) {
            $sql_subclauses[] = $this->treeWalkWhereClause($subclause);
          }
          return '(' . implode("\n" . $clause[0], $sql_subclauses) . ')';
        }

      case 'NOT':
        // possibly these brackets are redundant
        return 'NOT (' . $this->treeWalkWhereClause($clause[1]) . ')';

      default:
        return $this->validateClauseAndComposeSql($clause);
    }
  }

  /**
   * Validate and transform a leaf clause array to SQL.
   * @param array $clause [$fieldName, $operator, $criteria]
   * @return string SQL
   * @throws \API_Exception
   * @throws \Exception
   */
  protected function validateClauseAndComposeSql($clause) {
    // Pad array for unary operators
    list($key, $operator, $value) = array_pad($clause, 3, NULL);
    $fieldSpec = $this->getField($key);
    // derive table and column:
    $table_name = NULL;
    $column_name = NULL;
    if (in_array($key, $this->entityFieldNames)) {
      $table_name = self::MAIN_TABLE_ALIAS;
      $column_name = $key;
    }
    elseif (strpos($key, '.') && isset($this->fkSelectAliases[$key])) {
      list($table_name, $column_name) = explode('.', $this->fkSelectAliases[$key]);
    }

    if (!$table_name || !$column_name) {
      throw new \API_Exception("Invalid field '$key' in where clause.");
    }

    FormattingUtil::formatValue($value, $fieldSpec, $this->getEntity());

    $sql_clause = \CRM_Core_DAO::createSQLFilter("`$table_name`.`$column_name`", [$operator => $value]);
    if ($sql_clause === NULL) {
      throw new \API_Exception("Invalid value in where clause for field '$key'");
    }
    return $sql_clause;
  }

  /**
   * @inheritDoc
   */
  protected function getFields() {
    $fields = civicrm_api4($this->entity, 'getFields', ['action' => 'get', 'includeCustom' => FALSE])->indexBy('name');
    return (array) $fields;
  }

  /**
   * Fetch a field from the getFields list
   *
   * @param string $fieldName
   *
   * @return string|null
   */
  protected function getField($fieldName) {
    if ($fieldName) {
      $fieldPath = explode('.', $fieldName);
      if (count($fieldPath) > 1) {
        $fieldName = implode('.', array_slice($fieldPath, -2));
      }
      return UtilsArray::value($fieldName, $this->apiFieldSpec);
    }
    return NULL;
  }

  /**
   * @param $key
   */
  protected function joinFK($key) {
    $stack = explode('.', $key);

    if (count($stack) < 2) {
      return;
    }

    $joiner = \Civi::container()->get('joiner');
    $pathArray = explode('.', $key);
    $field = array_pop($pathArray);
    $pathString = implode('.', $pathArray);

    if (!$joiner->canJoin($this, $pathString)) {
      return;
    }

    $joinPath = $joiner->join($this, $pathString);
    /** @var Joinable $lastLink */
    $lastLink = array_pop($joinPath);

    // Cache field info for retrieval by $this->getField()
    $prefix = array_pop($pathArray) . '.';
    if (!isset($this->apiFieldSpec[$prefix . $field])) {
      $joinEntity = $lastLink->getEntity();
      // Custom fields are already prefixed
      if ($lastLink instanceof CustomGroupJoinable) {
        $prefix = '';
      }
      foreach ($lastLink->getEntityFields() as $fieldObject) {
        $this->apiFieldSpec[$prefix . $fieldObject->getName()] = $fieldObject->toArray() + ['entity' => $joinEntity];
      }
    }

    // custom groups use aliases for field names
    if ($lastLink instanceof CustomGroupJoinable) {
      $field = $lastLink->getSqlColumn();
    }

    $this->fkSelectAliases[$key] = sprintf('%s.%s', $lastLink->getAlias(), $field);
  }

  /**
   * @param Joinable $joinable
   *
   * @return $this
   */
  public function addJoinedTable(Joinable $joinable) {
    $this->joinedTables[] = $joinable;

    return $this;
  }

  /**
   * @return FALSE|string
   */
  public function getFrom() {
    return TableHelper::getTableForClass(TableHelper::getFullName($this->entity));
  }

  /**
   * @return string
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * @return array
   */
  public function getSelect() {
    return $this->select;
  }

  /**
   * @return array
   */
  public function getWhere() {
    return $this->where;
  }

  /**
   * @return array
   */
  public function getOrderBy() {
    return $this->orderBy;
  }

  /**
   * @return mixed
   */
  public function getLimit() {
    return $this->limit;
  }

  /**
   * @return mixed
   */
  public function getOffset() {
    return $this->offset;
  }

  /**
   * @return array
   */
  public function getSelectFields() {
    return $this->selectFields;
  }

  /**
   * @return bool
   */
  public function isFillUniqueFields() {
    return $this->isFillUniqueFields;
  }

  /**
   * @return \CRM_Utils_SQL_Select
   */
  public function getQuery() {
    return $this->query;
  }

  /**
   * @return array
   */
  public function getJoins() {
    return $this->joins;
  }

  /**
   * @return array
   */
  public function getApiFieldSpec() {
    return $this->apiFieldSpec;
  }

  /**
   * @return array
   */
  public function getEntityFieldNames() {
    return $this->entityFieldNames;
  }

  /**
   * @return array
   */
  public function getAclFields() {
    return $this->aclFields;
  }

  /**
   * @return bool|string
   */
  public function getCheckPermissions() {
    return $this->checkPermissions;
  }

  /**
   * @return int
   */
  public function getApiVersion() {
    return $this->apiVersion;
  }

  /**
   * @return array
   */
  public function getFkSelectAliases() {
    return $this->fkSelectAliases;
  }

  /**
   * @return Joinable[]
   */
  public function getJoinedTables() {
    return $this->joinedTables;
  }

  /**
   * @return Joinable
   */
  public function getJoinedTable($alias) {
    foreach ($this->joinedTables as $join) {
      if ($join->getAlias() == $alias) {
        return $join;
      }
    }
  }

}

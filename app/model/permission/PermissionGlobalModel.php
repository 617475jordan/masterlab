<?php

namespace main\app\model\permission;

use main\app\model\BaseDictionaryModel;

/**
 * 全局权限项
 */
class PermissionGlobalModel extends BaseDictionaryModel
{
    public $prefix = 'permission_';

    public $table = 'global';

    /**
     * PermissionGlobalModel constructor.
     * @param bool $persistent
     * @throws \Exception
     */
    public function __construct($persistent = false)
    {
        parent::__construct($persistent);
    }

    /**
     * 新增
     * @param $info
     * @return array
     * @throws \Exception
     */
    public function add($info)
    {
        if (empty($info)) {
            return [false, 'params_is_empty'];
        }
        return $this->insert($info);
    }


    /**
     * 获取父权限
     * @return array
     */
    public function getParent()
    {
        return $this->getRows('*', ['parent_id' => 0]);
    }

    /**
     * 获取子权限
     * @return array
     */
    public function getChildren()
    {
        $params = [];
        $table = $this->getTable();
        $sql = " select `id` , `name` , `parent_id` , `description` , `_key` from {$table}   where  1 ";
        $sql .= " AND  parent_id > 0 ";

        $rows = $this->fetchALLForGroup($sql, $params, true);

        return $rows;
    }

    /**
     * 通过id数组获取权限key
     * @param $permIds
     * @return array
     */
    public function getKeysById($permIds)
    {

        if (empty($permIds) || !is_array($permIds)) {
            return [];
        }
        $params = [];
        $table = $this->getTable();
        $sql = "select _key from {$table}   where  1 ";

        $ids_str = implode(',', $permIds);
        $sql .= " AND  id IN ({$ids_str}) ";

        $rows = $this->fetchALLForGroup($sql, $params, true);

        if (empty($rows)) {
            return [];
        }

        return array_keys($rows);
    }

}

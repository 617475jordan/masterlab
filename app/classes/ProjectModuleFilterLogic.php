<?php

namespace main\app\classes;

use main\app\model\project\ProjectModuleModel;

class ProjectModuleFilterLogic
{
    /**
     * @param $project_id
     * @param string $name
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getModuleByFilter($project_id, $name = '', $page = 1, $pageSize = 30)
    {
        $model = new ProjectModuleModel();
        $table = $model->getTable();

        $where = " WHERE `project_id`=$project_id AND `name` LIKE '%{$name}%'";
        $start = $pageSize * ($page - 1);
        $limit = " LIMIT $start, $pageSize";
        $order = " ORDER BY order_weight DESC,id DESC";

        $sqlCount = "SELECT count(*) as cc FROM  {$table} {$where}";
        $count = $model->getFieldBySql($sqlCount);

        $sql = "SELECT * FROM {$table} {$where}";
        $sql .= $order . $limit;

        $arr = $model->db->fetchAll($sql);
        return [true, $arr, $count];
    }

    public function getByProjectWithUser($projectId)
    {
        $model = new ProjectModuleModel();
        $sql = "SELECT m.*, u.display_name from project_module m LEFT JOIN user_main u ON m.lead=u.uid WHERE project_id={$projectId} ORDER BY id DESC";
        return $model->db->fetchAll($sql);
    }

    public function getByProjectWithUserLikeName($projectId, $name)
    {
        $model = new ProjectModuleModel();
        $sql = "SELECT m.*, u.display_name from project_module m LEFT JOIN user_main u ON m.lead=u.uid WHERE project_id={$projectId} AND m.name LIKE '%{$name}%' ORDER BY id DESC";
        return $model->db->fetchAll($sql);
    }
}
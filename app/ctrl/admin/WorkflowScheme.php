<?php

namespace main\app\ctrl\admin;

use main\app\classes\PermissionGlobal;
use main\app\classes\UserAuth;
use main\app\ctrl\BaseCtrl;
use main\app\ctrl\BaseAdminCtrl;
use main\app\model\issue\WorkflowSchemeModel;
use main\app\model\issue\WorkflowSchemeDataModel;
use main\app\model\issue\IssueTypeModel;
use main\app\model\issue\WorkflowModel;
use main\app\classes\WorkflowLogic;
use main\app\model\project\ProjectModel;

/**
 * WorkflowScheme
 */
class WorkflowScheme extends BaseAdminCtrl
{

    /**
     * WorkflowScheme constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $userId = UserAuth::getId();
        $this->addGVar('top_menu_active', 'system');
        $check = PermissionGlobal::check($userId, PermissionGlobal::MANAGER_ISSUE_PERM_ID);

        if (!$check) {
            $this->error('权限错误', '您还未获取此模块的权限！');
            exit;
        }
    }

    public function pageIndex()
    {
        $data = [];
        $data['title'] = 'Users';
        $data['nav_links_active'] = 'issue';
        $data['sub_nav_active'] = 'issue_type';
        $data['left_nav_active'] = 'workflow_scheme';
        $this->render('twig/admin/issue/workflow_scheme.twig', $data);
    }

    /**
     * 获取所有数据
     * @throws \Exception
     */
    public function fetchAll()
    {
        $workflowSchemeModel = new WorkflowSchemeModel();
        $workflowScheme = $workflowSchemeModel->fetchAll(false);

        $workflowModel = new WorkflowModel();
        $workflow = $workflowModel->getAll();

        $issueTypeModel = new IssueTypeModel();
        $issueTypes = $issueTypeModel->getAll();
        $arr = (new ProjectModel())->getAllByFields('id,org_path,`key`,name,workflow_scheme_id');
        $projectWorkflowSchemeArr = [];
        foreach ($arr as $item) {
            $id = $item['workflow_scheme_id'];
            if($id=='0'){
                $id = '1';
            }
            $projectWorkflowSchemeArr[$id][] = $item;
        }

        $wfSchemeDataModel = new WorkflowSchemeDataModel();
        $workflowSchemeData = $wfSchemeDataModel->getAllItems();
        $tmp = [];
        foreach ($workflowSchemeData as $row) {
            $issueTypeId = $row['issue_type_id'];
            $workflowId = $row['workflow_id'];
            $issueTypeId = empty($issueTypeId) ? 1 : $issueTypeId;
            $workflowId  = empty($workflowId)  ? 1 : $workflowId;
            $row['workflow_name'] = isset($workflow[$workflowId]['name']) ? $workflow[$workflowId]['name'] : '';
            $row['issue_name'] = isset($issueTypes[$issueTypeId]['name']) ? $issueTypes[$issueTypeId]['name'] : '';

            $tmp[$row['scheme_id']][] = $row;
        }
        $workflowSchemeData = $tmp;

        foreach ($workflowScheme as &$s) {
            $schemeId = $s['id'];
            $s['project_arr'] = isset($projectWorkflowSchemeArr[$schemeId]) ? $projectWorkflowSchemeArr[$schemeId]:[];
            $s['relation'] = isset($workflowSchemeData[$s['id']]) ? $workflowSchemeData[$s['id']] : [];
        }

        $data = [];
        $data['workflow_scheme'] = $workflowScheme;
        $data['issue_types'] = array_values($issueTypes);
        $data['workflow'] = array_values($workflow);


        $data['project_workflow_scheme_id_arr'] =  $projectWorkflowSchemeArr;


        $this->ajaxSuccess('操作成功', $data);
    }

    /**
     * 获取单条数据
     * @throws \Exception
     */
    public function get()
    {
        $id = null;
        if (isset($_GET['_target'][3])) {
            $id = (int)$_GET['_target'][3];
        }
        if (isset($_REQUEST['id'])) {
            $id = (int)$_REQUEST['id'];
        }
        if (!$id) {
            $this->ajaxFailed('参数错误', 'id不能为空');
        }
        $id = (int)$id;
        $model = new WorkflowSchemeModel();
        $scheme = $model->getRowById($id);

        $workflowModel = new WorkflowModel();
        $workflow = $workflowModel->getAll();

        $issueTypeModel = new IssueTypeModel();
        $issueTypes = $issueTypeModel->getAll();

        $wfSchemeDataModel = new WorkflowSchemeDataModel();
        $workflowSchemeData = $wfSchemeDataModel->getItemsBySchemeId($id);
        if ($workflowSchemeData) {
            foreach ($workflowSchemeData as &$row) {
                $issueTypeId = $row['issue_type_id'];
                $workflowId = $row['workflow_id'];
                $issueTypeId = empty($issueTypeId) ? 1 : $issueTypeId;
                $workflowId = empty($workflowId) ? 1 : $workflowId;

                $row['workflow_name'] = isset($workflow[$workflowId]['name']) ? $workflow[$workflowId]['name'] : '';
                $row['issue_name'] = isset($issueTypes[$issueTypeId]['name']) ? $issueTypes[$issueTypeId]['name'] : '';
            }
        }

        $data = [];
        $data['scheme'] = (object)$scheme;
        $data['workflow'] = $workflow;
        $data['scheme_data'] = $workflowSchemeData;

        $this->ajaxSuccess('操作成功', $data);
    }

    /**
     * 新增
     * @param null $params
     * @throws \Exception
     */
    public function add($params = null)
    {
        if (empty($params)) {
            $this->ajaxFailed('错误', '没有提交表单数据');
        }

        $errorMsg = [];
        if (!isset($params['name']) || empty($params['name'])) {
            $errorMsg['name'] = '名称不能为空';
        }
        $model = new WorkflowSchemeModel();
        if (isset($model->getByName($params['name'])['id'])) {
            $errorMsg['name'] = '名称已经被使用';
        }
        if (!empty($errorMsg)) {
            $this->ajaxFailed('参数错误', $errorMsg, BaseCtrl::AJAX_FAILED_TYPE_FORM_ERROR);
        }

        $info = [];
        $info['name'] = $params['name'];
        if (isset($params['description'])) {
            $info['description'] = $params['description'];
        }
        list($ret, $msg) = $model->insertItem($info);
        if ($ret) {
            if (isset($params['issue_type_workflow'])) {
                $issueTypeWorkflow = json_decode($params['issue_type_workflow'], true);
                $workflowLogic = new WorkflowLogic();
                $workflowLogic->updateSchemeTypesWorkflow($msg, $issueTypeWorkflow);
            }
            $this->ajaxSuccess('操作成功');
        } else {
            $this->ajaxFailed('服务器错误:', '数据库插入失败,详情 :' . $msg);
        }
    }

    /**
     * 更新
     * @param array $params
     * @throws \Exception
     */
    public function update($params = [])
    {
        $id = null;
        if (isset($_GET['_target'][3])) {
            $id = (int)$_GET['_target'][3];
        }
        if (isset($_REQUEST['id'])) {
            $id = (int)$_REQUEST['id'];
        }
        if (!$id) {
            $this->ajaxFailed('参数错误', 'id不能为空');
        }
        $errorMsg = [];
        if (empty($params)) {
            $this->ajaxFailed('错误', '没有提交表单数据');
        }

        if (!isset($params['name']) || empty($params['name'])) {
            $errorMsg['name'] = '名称不能为空';
        }
        $model = new WorkflowSchemeModel();
        $row = $model->getByName($params['name']);
        //var_dump($row);
        if (isset($row['id']) && ($row['id'] != $id)) {
            $errorMsg['name'] = '名称已经被使用';
        }

        if (!empty($errorMsg)) {
            $this->ajaxFailed('参数错误', $errorMsg, BaseCtrl::AJAX_FAILED_TYPE_FORM_ERROR);
        }

        $id = (int)$id;
        $info = [];
        $info['name'] = $params['name'];
        if (isset($params['description'])) {
            $info['description'] = $params['description'];
        }

        $ret = $model->updateItem($id, $info);
        if ($ret) {
            if (isset($params['issue_type_workflow'])) {
                $issueTypeWorkflow = json_decode($params['issue_type_workflow'], true);
                $workflowLogic = new WorkflowLogic();
                $workflowLogic->updateSchemeTypesWorkflow($id, $issueTypeWorkflow);
            }
            $this->ajaxSuccess('操作成功');
        } else {
            $this->ajaxFailed('服务器错误', '更新数据失败');
        }
    }

    /**
     * 删除
     * @throws \Exception
     */
    public function delete()
    {
        $id = null;
        if (isset($_GET['_target'][3])) {
            $id = (int)$_GET['_target'][3];
        }
        if (isset($_REQUEST['id'])) {
            $id = (int)$_REQUEST['id'];
        }
        if (!$id) {
            $this->ajaxFailed('参数错误', 'id不能为空');
        }

        $id = (int)$id;
        $model = new WorkflowSchemeModel();
        $ret = $model->deleteItem($id);
        if (!$ret) {
            $this->ajaxFailed('服务器错误', '删除数据失败');
        } else {
            $model = new WorkflowSchemeDataModel();
            $model->deleteBySchemeId($id);
            $this->ajaxSuccess('操作成功');
        }
    }
}

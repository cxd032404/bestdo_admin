<?php
/**
 * 部门管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_DepartmentController extends AbstractController
{
    /**部门:Department
     * @var string
     */
    protected $sign = '?ctl=hj/department';
    protected $ctl = 'hj/department';

    /**
     * game对象
     * @var object
     */
    protected $oDepartment;
    protected $oCompany;
    protected $oDepartmentElement;

    /**
     * 初始化
     * (non-PHPdoc)
     * @see AbstractController#init()
     */
    public function init()
    {
        parent::init();
        $this->oDepartment = new Hj_Department();
        $this->oCompany = new Hj_Company();

    }
    //部门配置列表部门
    public function indexAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //部门ID
            $company_id = intval($this->request->company_id??0);
            //获取部门列表
            $departmentList = $this->oDepartment->getDepartmentList(['company_id'=>$company_id]);
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //循环部门列表
            foreach($departmentList as $key => $departmentInfo)
            {
                //数据解包
                $departmentList[$key]['company_name'] = ($departmentInfo['company_id']==0)?"无对应":($companyList[$departmentInfo['company_id']]['company_name']??"未知");
                $departmentList[$key]['element_count'] = $this->oDepartmentElement->getElementCount(['department_id'=>$departmentInfo['department_id']]);
                $departmentList[$key]['detail'] = json_decode($departmentInfo['detail'],true);
                $departmentList[$key]['department_url'] = $this->config->siteUrl."/".$departmentInfo['department_sign'];
                $departmentList[$key]['test_url'] = $this->config->apiUrl.$this->config->api['api']['get_department']."".$departmentInfo['company_id']."/".$departmentInfo['department_sign'];
                $departmentList[$key]['department_params'] = $this->oDepartment->processDefaultParams($departmentList[$key]['detail']['params']??[]);
            }
            //渲染模版
            include $this->tpl('Hj_Department_DepartmentList');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加部门类型填写配置部门
    public function departmentAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("addDepartment");
        if($PermissionCheck['return'])
        {
            //获取顶级部门列表
            $companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //渲染模版
            include $this->tpl('Hj_Department_DepartmentAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }

    //添加新部门
    public function departmentInsertAction()
    {
        //检查权限
        $bind=$this->request->from('department_name','department_url','company_id','department_sign','need_login','detail');
        //部门名称不能为空
        if(trim($bind['department_name'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            if(trim($bind['department_url'])=="")
            {
                $response = array('errno' => 2);
            }
            else
            {
                if(trim($bind['department_sign'])=="")
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    $departmentExists = $this->oDepartment->getDepartmentList(['company_id'=>$bind['company_id'],'department_sign'=>$bind['department_sign']],'department_id,department_sign');
                    if(count($departmentExists)>0)
                    {
                        $response = array('errno' => 4);
                    }
                    else
                    {
                        //处理部门必备参数
                        $bind['detail']['params'] = $this->oDepartment->unpackDepartmentParams($bind['detail']['params']);
                        //数据打包
                        $bind['detail'] = json_encode($bind['detail']);
                        //添加部门
                        $res = $this->oDepartment->insertDepartment($bind);
                        $response = $res ? array('errno' => 0) : array('errno' => 9);
                    }
                }
            }

        }
        echo json_encode($response);
        return true;
    }

    //修改部门信息页面
    public function departmentModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateDepartment");
        if($PermissionCheck['return'])
        {
            //部门ID
            $department_id= intval($this->request->department_id);
            //获取部门信息
            $departmentInfo = $this->oDepartment->getDepartment($department_id,'*');
            //数据解包
            $departmentInfo['detail'] = json_decode($departmentInfo['detail'],true);
            $departmentInfo['detail']['params'] = $this->oDepartment->packDepartmentParams($departmentInfo['detail']['params']);
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //渲染模版
            include $this->tpl('Hj_Department_DepartmentModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }

    //更新部门信息
    public function departmentUpdateAction()
    {
        //接收部门参数
        $bind=$this->request->from('department_id','department_name','company_id','department_url','need_login','department_sign','detail');
        //部门名称不能为空
        if(trim($bind['department_name'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            if(trim($bind['department_url'])=="")
            {
                $response = array('errno' => 2);
            }
            else
            {
                if(trim($bind['department_sign'])=="")
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    $departmentExists = $this->oDepartment->getDepartmentList(['company_id'=>$bind['company_id'],'department_sign'=>$bind['department_sign'],'exclude_id'=>$bind['department_id']],'department_id,department_sign');
                    if(count($departmentExists)>0)
                    {
                        $response = array('errno' => 4);
                    }
                    else
                    {
                        //处理部门必备参数
                        $bind['detail']['params'] = $this->oDepartment->unpackDepartmentParams($bind['detail']['params']);
                        //数据打包
                        $bind['detail'] = json_encode($bind['detail']);
                        $currentDepartmentInfo = $this->oDepartment->getDepartment($bind['department_id'],"department_id,company_id");
                        //修改部门
                        $res = $this->oDepartment->updateDepartment($bind['department_id'],$bind);
                        $response = $res ? array('errno' => 0) : array('errno' => 9,'company_id'=>$currentDepartmentInfo['company_id']);
                    }
                }
            }
        }
        echo json_encode($response);
        return true;
    }

    //删除部门
    public function departmentDeleteAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("deleteDepartment");
        if($PermissionCheck['return'])
        {
            //部门ID
            $department_id = trim($this->request->department_id);
            //删除部门
            $this->oDepartment->deleteDepartment($department_id);
            //返回之前的部门
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}


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
                $departmentList[$key]['parent_department_name'] = ($departmentInfo['parent_id']==0)?"无对应":($departmentList[$departmentInfo['parent_id']]['department_name']??"未知");
                $departmentList[$key]['child_count'] = $this->oDepartment->getDepartmentCount(['company_id'=>$company_id,'parent_id'=>$departmentInfo['department_id']]);

                $name_prefix = "";
                $departmentList[$key]['department_sign'] = $departmentInfo['department_id']."_0_0";
                //有上级
                if($departmentInfo['parent_id']>0)
                {
                    //上级有上级
                    if($departmentList[$departmentInfo['parent_id']]['parent_id']>0)
                    {
                        $name_prefix = " ┠&nbsp;&nbsp; ┠&nbsp;&nbsp; ";
                        $departmentList[$key]['department_sign'] = $departmentList[$departmentInfo['parent_id']]['parent_id']."_".$departmentInfo['parent_id']."_".$departmentInfo['department_id'];
                    }
                    else
                    {
                        $departmentList[$key]['department_sign'] = $departmentInfo['parent_id']."_".$departmentInfo['department_id'];
                        $name_prefix = " ┠&nbsp;&nbsp; ";

                    }
                }
                $departmentList[$key]['display_department_name']  =  $name_prefix.$departmentList[$key]['department_name'];
            }
            array_multisort(array_column($departmentList,"department_sign"),SORT_ASC,$departmentList);
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
        $bind=$this->request->from('department_name','company_id','parent_id','parent_id_2');
        //部门名称不能为空
        if(trim($bind['department_name'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            if(intval($bind['company_id'])==0)
            {
                $response = array('errno' => 2);
            }
            else
            {
                $departmentExists = $this->oDepartment->getDepartmentList(['company_id'=>$bind['company_id'],'department_name'=>$bind['department_name']],'department_id,department_name');
                if(count($departmentExists)>0)
                {
                    $response = array('errno' => 4);
                }
                else
                {
                    if($bind['parent_id']==0)
                    {
                        //第一级部门
                        unset($bind['parent_id_2']);
                    }
                    elseif($bind['parent_id_2']==0)
                    {
                        //第二级部门
                        unset($bind['parent_id_2']);
                    }
                    else
                    {
                        $bind['parent_id'] = $bind['parent_id_2'];
                        //第三级部门
                        unset($bind['parent_id_2']);
                    }
                    //添加部门
                    $res = $this->oDepartment->insertDepartment($bind);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
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
            $departmentList = [];
            $parentDepartmentList = [];
            //第一级
            if($departmentInfo['parent_id']==0)
            {
                //第一级的列表获取
                $departmentList = $this->oDepartment->getDepartmentList(['company_id'=>$departmentInfo['company_id'],'parent_id'=>0]);
            }
            else
            {
                //获取上级数据
                $parentDepartmentInfo = $this->oDepartment->getDepartment($departmentInfo['parent_id'],'department_name,department_id,parent_id');
                //第一级的列表获取
                $departmentList = $this->oDepartment->getDepartmentList(['company_id'=>$departmentInfo['company_id'],'parent_id'=>0]);
                {
                    //第二级
                    if($parentDepartmentInfo['parent_id']==0)
                    {
                        //第一级的列表获取
                        $departmentList = $this->oDepartment->getDepartmentList(['company_id'=>$departmentInfo['company_id'],'parent_id'=>0]);
                        $departmentList[$departmentInfo['parent_id']]['selected'] = 1;
                        $parentDepartmentList = [];
                    }
                    else//第三级别
                    {
                        //第一级的列表获取
                        $departmentList = $this->oDepartment->getDepartmentList(['company_id'=>$departmentInfo['company_id'],'parent_id'=>0]);
                        //第二级的列表获取
                        $parentDepartmentList = $this->oDepartment->getDepartmentList(['company_id'=>$departmentInfo['company_id'],'parent_id'=>$parentDepartmentInfo['parent_id']]);
                        $departmentList[$parentDepartmentInfo['parent_id']]['selected'] = 1;
                        $parentDepartmentList[$departmentInfo['parent_id']]['selected'] = 1;

                    }
                }
            }
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
        $bind=$this->request->from('department_id','department_name','company_id','parent_id','parent_id_2');
        //部门名称不能为空
        if(trim($bind['department_name'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            if(intval($bind['company_id'])==0)
            {
                $response = array('errno' => 2);
            }
            else
            {
                $departmentExists = $this->oDepartment->getDepartmentList(['exclude_id'=>$bind['department_id'],'company_id'=>$bind['company_id'],'department_name'=>$bind['department_name']],'department_id,department_name');
                if(count($departmentExists)>0)
                {
                    $response = array('errno' => 4);
                }
                else
                {
                    if($bind['parent_id']==0)
                    {
                        //第一级部门
                        unset($bind['parent_id_2']);
                    }
                    elseif($bind['parent_id_2']==0)
                    {
                        //第二级部门
                        unset($bind['parent_id_2']);
                    }
                    else
                    {
                        $bind['parent_id'] = $bind['parent_id_2'];
                        //第三级部门
                        unset($bind['parent_id_2']);
                    }
                    //修改部门
                    $res = $this->oDepartment->updateDepartment($bind['department_id'],$bind);
                    $response = $res ? array('errno' => 0) : array('errno' => 9,'company_id'=>$bind['company_id']);
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
    //获取企业获取活动列表
    public function getDepartmentByCompanyAction()
    {
        //企业ID
        $company_id = intval($this->request->company_id);
        $parent_id = intval($this->request->parent_id??0);
        //获取部门列表
        $departmentList = $this->oDepartment->getDepartmentList(['company_id'=>$company_id,'parent_id'=>$parent_id]);
        $text = '';
        $text .= '<option value="0">不指定</option>';
        //循环赛事分站列表
        foreach($departmentList as $departmentInfo)
        {
            //初始化选中状态
            $selected = "";
            /*
            //如果分站ID与传入的分站ID相符
            if($RaceStageInfo['RaceStageId'] == $StageId)
            {
                //选中拼接
                $selected = 'selected="selected"';
            }
            */
            //字符串拼接
            $text .= '<option value="'.$departmentInfo['department_id'].'">'.$departmentInfo['department_name'].'</option>';
        }
        echo $text;
        die();
    }
}


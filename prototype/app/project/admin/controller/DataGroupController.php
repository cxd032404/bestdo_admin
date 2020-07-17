<?php
/**
 * 数据组管理
 * @author <cxd032404@hotmail.com>
 * $Id: DataGroupController.php 15233 2014-08-04 06:46:08Z 334746 $
 */
class DataGroupController extends AbstractController
{
	protected $sign = "?ctl=data.group";
	
	public function indexAction()
	{		
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			$oGroup = new Widget_Group();
			$groupArr = $oGroup->getClass('2');
			include $this->tpl();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	public function addAction()
	{
        $PermissionCheck = $this->manager->checkMenuPermission("AddDataGroup",$this->sign);
        if($PermissionCheck['return'])
        {
            include $this->tpl();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }

	}
	
	public function insertAction()
	{
	    $data = $this->request->from('name', 'ClassId');
		$oGroup = new Widget_Group();
		if(empty($data['name']))
		{
			echo json_encode(array('errno' => 1));
			return false;
		}
		$res = $oGroup->insert($data);
		if (!$res)
		{
			echo json_encode(array('errno' => 9));
			return false;
		}

		echo  json_encode(array('errno' => 0));
		return true;
		
	}
	
	public function modifyAction()
	{
        $PermissionCheck = $this->manager->checkMenuPermission("UpdateDataGroup",$this->sign);
        if($PermissionCheck['return'])
        {
            $group_id = $this->request->group_id;
            $oGroup = new Widget_Group();
            $group = $oGroup->get($group_id);
            include $this->tpl();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
	}
	
	public function updateAction()
	{
		$data = $this->request->from('name', 'ClassId');
		$group_id = $this->request->group_id;
		if(!intVal($group_id))
		{
			echo json_encode(array('errno' => 1));
			return false;
		}
		if(empty($data['name']))
		{
			echo json_encode(array('errno' => 2));
			return false;
		}
		$oGroup = new Widget_Group();
		$res = $oGroup->update($group_id, $data);
		if (!$res)
		{
			echo json_encode(array('errno' => 9));
			return false;
		}
		echo  json_encode(array('errno' => 0));
		return true;
	}
	
	public function deleteAction()
	{
        $PermissionCheck = $this->manager->checkMenuPermission("DeleteDataGroup",$this->sign);
        if($PermissionCheck['return'])
        {
            $group_id = intval($this->request->group_id);
            $oGroup = new Widget_Group();
            $res = $oGroup->delete($group_id);
            if ($res)
            {
                $Widget_Menu_Permission = new Widget_Menu_Permission();
                $Widget_Menu_Permission->deleteByGroup($group_id);
            }
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }

	}

    /**
     * 获取当前用户组的权限配置页面
     * @params data_groups 用户组id
     */
    public function permissionByGroupAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
        if($PermissionCheck['return'])
        {
            $group_id = intval($this->request->group_id);
            $oGroup = new Widget_Group();
            $group = $oGroup->get($group_id);
            $totalPermission = $this->manager->getPermissionList($group_id);
            include $this->tpl('DataGroup_PermissionList');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    /**
     * 更新当前用户组的权限配置
     * @params data_groups 用户组id
     */
    public function permissionModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("UpdateDataGroup",$this->sign);
        if($PermissionCheck['return'])
        {
            //获取 页面参数
            $bind=$this->request->from('group_id','company_list');
            $update = $this->manager->updateDataPermissionByGroup($bind);
            //返回之前页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}






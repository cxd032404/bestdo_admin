<?php
/**
 * 菜单用户组管理
 * @author <cxd032404@hotmail.com>
 * $Id: MenuGroupController.php 15233 2014-08-04 06:46:08Z 334746 $
 */
class MenuGroupController extends AbstractController
{
	protected $sign = "?ctl=menu.group";
	
	public function indexAction()
	{
		/**
		 * 记录日志
		 */
		$log = "菜单用户组管理\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->pushLog('log', $log);
				
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			$oGroup = new Widget_Group();
			$groupArr = $oGroup->getClass('1');
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
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("AddMenuGroup");
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
		//检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("UpdateMenuGroup");
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
		//检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("DeleteMenuGroup");
		if($PermissionCheck['return'])
		{
			$group_id = intVal($this->request->group_id);
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
}






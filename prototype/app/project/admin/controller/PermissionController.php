<?php
/**
 * 数据权限控制
 * @author Chen <cxd032404@hotmail.com>
 * $Id: PermissionController.php 15195 2014-07-23 07:18:26Z 334746 $
 */
class PermissionController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=permission';
	/**
	 * App对象
	 * @var object
	 */
	protected $oRace;


	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
        $this->oRace = new Xrace_Race();
		//$this->oPermission = new Config_Permission();

	}
	/**
	 * 获取当前用户组的权限配置页面
	 * @params data_groups 用户组id
	 * @return 下拉列表
	 */
	public function permissionByGroupAction()
	{
		$group_id = intval($this->request->group_id);
		$totalPermission = $this->manager->getPermissionList($group_id);
        include $this->tpl('group_PermissionList');
		//$totalDefaultPermission = $this->oPermission->listPartertotalDefaultPermission($group_id);
		
		//include $this->tpl('Config_Permission_modify');
	}
	/**
	 * 获取当前用户组的权限配置页面2
	 * @params data_groups 用户组id
	 * @return 下拉列表
	 */
	public function listPartnerPermission2Action()
	{
		$group_id = intval($this->request->group_id)?intval($this->request->group_id):3;
		//echo $group_id."<br/>";
		$totalPermission = $this->oPermission->AllParterPermissionList('PartnerId,AppId,name,AreaId',$group_id);		

	}	
	/**
	 * 更新用户组的数据权限
	 * @params group_id	权限组
	 * @params $total_default_permission 全局默认权限
	 * @params $default_permission  游戏默认权限
	 * @params $PartnerIds  基本权限
	 * @return 回前一页面
	 */
	public function permissionModifyAction()
	{
		$group_id = abs(intval($this->request->group_id));
		$PartnerIds = $this->request->PartnerIds;
		$default_permission = $this->request->default_permission;
		$total_default_permission = $this->request->total_default_permission;
		
		$this->oPermission->modifyParterPermission($group_id,$total_default_permission,$default_permission,$PartnerIds);
		$this->response->goBack();
	}
	
	/*
	 *selena 数据权限更新
	 */
	public function permissionModify2Action()
	{				
		$group_id = abs(intval($this->request->group_id));
		$this->oPermission->DelPermissionByGroup($group_id);
		/*$PartnerIds = $this->request->PartnerIds;
		$default_permission = $this->request->default_permission;
		$total_default_permission = $this->request->total_default_permission;*/
		$PartnerIds = $this->request->PartnerIds;		
		foreach($PartnerIds as $k=>$v)
		{
			$listarr = $this->arr_process($v);
			$res = $this->oPermission->InsArrPermission($listarr["AppId"],$listarr["PartnerId"],$listarr["AreaId"],$listarr["partner_type"],$group_id);			
			
		}
		$this->response->goBack();
	}
	
	/*格式化数组
	 *@author selena
	 */
	public function arr_process($str)
	{
		$arr = array();
		$text_arr = explode("_",$str);		
		foreach($text_arr as $k=>$v)
		{
			$text_arr_2 = explode("|",$v);
			//print_r($text_arr_2);
			if($text_arr_2[0]=="App")
			{
				$text_arr_2[0] = "AppId";
				$arr[$text_arr_2[0]] = $text_arr_2[1];				
			}
			if($text_arr_2[0]=="Area")
			{
				$text_arr_2[0] = "AreaId";
				$arr[$text_arr_2[0]] = $text_arr_2[1];	
			}
			if($text_arr_2[0]=="Partner")
			{
				$text_arr_2[0] = "PartnerId";
				$arr[$text_arr_2[0]] = $text_arr_2[1];	
			}
			if($text_arr_2[0]=="Type")
			{
				$text_arr_2[0] = "partner_type";
				$arr[$text_arr_2[0]] = $text_arr_2[1];	
			}
		}
		return $arr;
	}
}

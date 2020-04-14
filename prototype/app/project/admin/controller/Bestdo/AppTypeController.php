<?php
/**
 * APP类型管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Xrace_AppTypeController extends AbstractController
{
	/**APP类型列表:AppType
	 * @var string
	 */
	protected $sign = '?ctl=xrace/app.type';
	/**
	 * game对象
	 * @var object
	 */
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oApp = new Xrace_App();

	}
	//APP类型列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//获取APP类型列表
			$AppTypeList = $this->oApp->getAppTypeList();
			//渲染模版
			include $this->tpl('Xrace_App_AppTypeList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加APP类型填写配置页面
	public function appTypeAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("AppTypeInsert");
		if($PermissionCheck['return'])
		{
			//渲染模版
			include $this->tpl('Xrace_App_AppTypeAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加新APP类型
	public function appTypeInsertAction()
	{
		//检查权限
		$bind=$this->request->from('AppTypeName');
		//APP类型名称不能为空
		if(trim($bind['AppTypeName'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			//添加APP类型
			$res = $this->oApp->insertAppType($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	//修改APP类型信息页面
	public function appTypeModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("AppTypeModify");
		if($PermissionCheck['return'])
		{
			//APP类型ID
			$AppTypeId = intval($this->request->AppTypeId);
			//获取APP类型信息
			$AppTypeInfo = $this->oApp->getAppType($AppTypeId,'*');
			//渲染模版
			include $this->tpl('Xrace_App_AppTypeModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//更新APP类型信息
	public function appTypeUpdateAction()
	{
		//获取页面参数
		$bind=$this->request->from('AppTypeId','AppTypeName');
		//APP类型名称不能为空
		if(trim($bind['AppTypeName'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			//更新APP类型
			$res = $this->oApp->updateAppType($bind['AppTypeId'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	//删除APP类型
	public function appTypeDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("AppTypeDelete");
		if($PermissionCheck['return'])
		{
			//APP类型ID
			$AppTypeId = trim($this->request->AppTypeId);
			//删除APP类型
			$this->oApp->deleteAppType($AppTypeId);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
}

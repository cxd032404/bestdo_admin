<?php
/**
 * 更新记录管理
 * @author Chen<cxd032404@hotmail.com>
 */

class UpdateLogController extends AbstractController
{
	/**更新记录:UpdateLog
	 * @var string
	 */
	protected $sign = '?ctl=update.log';
	/**
	 * game对象
	 * @var object
	 */
	protected $oUpdateLog;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oUpdateLog = new Xrace_UpdateLog();
	}
	//更新记录列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
		    //获取更新记录列表
			$UpdateLogList = $this->oUpdateLog->getUpdateLogList();
            //更新记录类型列表
            $UpdateLogTypeList = $this->oUpdateLog->getLogTypeList();
            //渲染模版
			include $this->tpl('UpdateLog_UpdateLogList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//修改更新记录信息页面
	public function updateLogModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("UpdateUpdateLog");
		if($PermissionCheck['return'])
		{
			//更新记录类型ID
			$UpdateLogId = intval($this->request->UpdateLogId);
            //获取更新记录类型列表
			$UpdateLogInfo = $this->oUpdateLog->getUpdateLog($UpdateLogId,'*');
            //更新记录类型列表
            $UpdateLogTypeList = $this->oUpdateLog->getLogTypeList();
			//渲染模版
			include $this->tpl('UpdateLog_UpdateLogModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//更新更新记录信息
	public function updateLogUpdateAction()
	{
		//获取页面参数
		$bind=$this->request->from('UpdateLogId','UpdateDate','LogType','comment');
		//UpdateLog类型名称不能为空
		if(trim($bind['comment'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			//更新UpdateLog类型
			$res = $this->oUpdateLog->updateUpdateLog($bind['UpdateLogId'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	//添加更新记录填写配置页面
	public function updateLogAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("InsertUpdateLog");
		if($PermissionCheck['return'])
		{
			$UpdateDate = date("Y-m-d",time());
            //更新记录类型列表
            $UpdateLogTypeList = $this->oUpdateLog->getLogTypeList();
		    //渲染模版
			include $this->tpl('UpdateLog_UpdateLogAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加新更新记录
	public function updateLogInsertAction()
	{
		//检查权限
		$bind = $this->request->from('UpdateDate','LogType','comment');
        //更新内容不能为空
		if(trim($bind['comment'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			//添加更新记录
			$res = $this->oUpdateLog->insertUpdateLog($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	//删除更新记录
	public function updateLogDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("DeleteUpdateLog");
		if($PermissionCheck['return'])
		{
			//UpdateLog类型ID
			$UpdateLogId = trim($this->request->UpdateLogId);
			//删除UpdateLog类型
			$this->oUpdateLog->deleteUpdateLog($UpdateLogId);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
}

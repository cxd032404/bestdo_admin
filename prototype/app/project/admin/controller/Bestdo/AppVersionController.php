<?php
/**
 * APP版本管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Xrace_AppVersionController extends AbstractController
{
	/**APP版本:AppVersionList
	 * @var string
	 */
	protected $sign = '?ctl=xrace/app.version';
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
	//APP版本信息列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//获取最新版本信息
			$NewestAppVersionList = $this->oApp->getNewestVersionList(1);
			//APP类型ID
			$AppTypeId = isset($this->request->AppTypeId)?intval($this->request->AppTypeId):0;
			//APP系统ID
			$AppOSId = isset($this->request->AppOSId)?intval($this->request->AppOSId):0;
			//获取APP系统列表
			$AppVersionList = $this->oApp->getAppVersionList($AppTypeId,$AppOSId);
			//获取APP类型列表
			$AppTypeList = $this->oApp->getAppTypeList("AppTypeId,AppTypeName");
			//获取APP系统列表
			$AppOSList = $this->oApp->getAppOSList("AppOSId,AppOSName");
			//循环APP版本列表
			foreach($AppVersionList as $AppVersionId => $AppVersionInfo)
			{
				//如果存在于最新版本列表
				if(isset($NewestAppVersionList[$AppVersionInfo['AppOSId']][$AppVersionInfo['AppTypeId']]) && ($NewestAppVersionList[$AppVersionInfo['AppOSId']][$AppVersionInfo['AppTypeId']]['AppVersionId']==$AppVersionId))
				{
					$AppVersionList[$AppVersionId]['NewestVersion'] = 1;
				}
				else
				{
					$AppVersionList[$AppVersionId]['NewestVersion'] = 0;
				}
				//格式化版本信息
				$AppVersionList[$AppVersionId]['AppVersion'] = Base_Common::ParthIntToVersion($AppVersionInfo['AppVersion']);
				//如果当前尚未获取过APP类型
				if(isset($AppTypeList[$AppVersionInfo['AppTypeId']]))
				{
					//获取APP类型名称
					$AppVersionList[$AppVersionId]['AppTypeName'] = $AppTypeList[$AppVersionInfo['AppTypeId']]['AppTypeName'];
				}
				else
				{
					$AppVersionList[$AppVersionId]['AppTypeName'] = "未定义";
				}
				//如果当前尚未获取过APP系统
				if(isset($AppOSList[$AppVersionInfo['AppOSId']]))
				{
					//获取APP系统名称
					$AppVersionList[$AppVersionId]['AppOSName'] = $AppOSList[$AppVersionInfo['AppOSId']]['AppOSName'];
				}
				else
				{
					$AppVersionList[$AppVersionId]['AppOSName'] = "未定义";
				}
				//解压缩下载路径
				$AppVersionList[$AppVersionId]['AppDownloadUrl'] = urldecode($AppVersionInfo['AppDownloadUrl']);
				//数据解包
				$AppVersionList[$AppVersionId]['comment'] = json_decode($AppVersionInfo['comment'],true);
			}
			//渲染模版
			include $this->tpl('Xrace_App_AppVersionList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加APP版本信息填写配置页面
	public function appVersionAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("AppVersionInsert");
		if($PermissionCheck['return'])
		{
			//获取APP类型列表
			$AppTypeList = $this->oApp->getAppTypeList("AppTypeId,AppTypeName");
			//获取APP系统列表
			$AppOSList = $this->oApp->getAppOSList("AppOSId,AppOSName");
			//渲染模版
			include $this->tpl('Xrace_App_AppVersionAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加新APP版本
	public function appVersionInsertAction()
	{
		//检查权限
		$bind = $this->request->from('AppVersion','AppTypeId','AppOSId','AppDownloadUrl');
		//获取APP类型列表
		$AppTypeList = $this->oApp->getAppTypeList("AppTypeId");
		//获取APP系统列表
		$AppOSList = $this->oApp->getAppOSList("AppOSId");
		//APP类型必须有效
		if(!isset($AppTypeList[$bind['AppTypeId']]))
		{
			$response = array('errno' => 1);
		}
		//APP系统必须有效
		elseif(!isset($AppOSList[$bind['AppOSId']]))
		{
			$response = array('errno' => 2);
		}
		//APP下载链接过短
		elseif(strlen(trim($bind['AppDownloadUrl']))<=10)
		{
			$response = array('errno' => 3);
		}
		else
		{
			//格式化版本信息
			$bind['AppVersion'] = Base_Common::ParthVersionToInt($bind['AppVersion']);
			//下载路径编码
			$bind['AppDownloadUrl'] = urlencode($bind['AppDownloadUrl']);
			//获取并过滤版本说明文字
			$bind['comment']['VersionComment'] = htmlspecialchars(trim($this->request->VersionComment));
			//数据压缩
			$bind['comment'] = json_encode($bind['comment']);
			//添加APP版本
			$res = $this->oApp->insertAppVersion($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
			//更新最新版本信息缓存
			$this->oApp->getNewestVersionList(0);
		}
		echo json_encode($response);
		return true;
	}
	//修改APP版本信息页面
	public function appVersionModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("AppVersionModify");
		if($PermissionCheck['return'])
		{
			//获取APP类型列表
			$AppTypeList = $this->oApp->getAppTypeList("AppTypeId,AppTypeName");
			//获取APP系统列表
			$AppOSList = $this->oApp->getAppOSList("AppOSId,AppOSName");
			//APP版本ID
			$AppVersionId = intval($this->request->AppVersionId);
			//获取APP版本信息
			$AppVersionInfo = $this->oApp->getAppVersion($AppVersionId,'*');
			//格式化版本信息
			$AppVersionInfo['AppVersion'] = Base_Common::ParthIntToVersion($AppVersionInfo['AppVersion']);
			//解压缩下载路径
			$AppVersionInfo['AppDownloadUrl'] = urldecode($AppVersionInfo['AppDownloadUrl']);
			//数据解包
			$AppVersionInfo['comment'] = json_decode($AppVersionInfo['comment'],true);
			//渲染模版
			include $this->tpl('Xrace_App_AppVersionModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加新APP版本
	public function appVersionUpdateAction()
	{
		//检查权限
		$bind = $this->request->from('AppVersionId','AppVersion','AppTypeId','AppOSId','AppDownloadUrl');
		//获取APP类型列表
		$AppTypeList = $this->oApp->getAppTypeList("AppTypeId");
		//获取APP系统列表
		$AppOSList = $this->oApp->getAppOSList("AppOSId");
		//APP类型必须有效
		if(!isset($AppTypeList[$bind['AppTypeId']]))
		{
			$response = array('errno' => 1);
		}
		//APP系统必须有效
		elseif(!isset($AppOSList[$bind['AppOSId']]))
		{
			$response = array('errno' => 2);
		}
		//APP下载链接过短
		elseif(strlen(trim($bind['AppDownloadUrl']))<=10)
		{
			$response = array('errno' => 3);
		}
		else
		{
			//格式化版本信息
			$bind['AppVersion'] = Base_Common::ParthVersionToInt($bind['AppVersion']);
			//获取APP版本信息
			$AppVersionInfo = $this->oApp->getAppVersion($bind['AppVersionId'],'comment');
			//下载路径编码
			$bind['AppDownloadUrl'] = urlencode($bind['AppDownloadUrl']);
			//数据解包
			$bind['comment'] = json_decode($AppVersionInfo['comment'],true);
			//获取并过滤版本说明文字
			$bind['comment']['VersionComment'] = htmlspecialchars(trim($this->request->VersionComment));
			//数据压缩
			$bind['comment'] = json_encode($bind['comment']);
			//添加APP版本
			$res = $this->oApp->updateAppVersion($bind['AppVersionId'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
			//更新最新版本信息缓存
			$this->oApp->getNewestVersionList(0);
		}
		echo json_encode($response);
		return true;
	}
	//删除APP
	public function appVersionDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("AppVersionDelete");
		if($PermissionCheck['return'])
		{
			//APP版本ID
			$AppVersionId = trim($this->request->AppVersionId);
			//删除APP版本
			$this->oApp->deleteAppVersion($AppVersionId);
			//更新最新版本信息缓存
			$this->oApp->getNewestVersionList(0);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
}

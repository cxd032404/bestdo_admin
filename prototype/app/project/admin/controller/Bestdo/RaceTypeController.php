<?php
/**
 * 比赛类型管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Xrace_RaceTypeController extends AbstractController
{
	/**比赛类型:RaceType
	 * @var string
	 */
	protected $sign = '?ctl=xrace/race.type';
	/**
	 * race对象
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

	}
	//比赛类型列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//获取比赛类型列表
			$RaceTypeList  = $this->oRace->getRaceTypeList();
			//渲染模板
			include $this->tpl('Xrace_Race_RaceTypeList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加比赛类型填写配置页面
	public function raceTypeAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceTypeInsert");
		if($PermissionCheck['return'])
		{
			//渲染模板
			include $this->tpl('Xrace_Race_RaceTypeAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//添加新比赛类型
	public function raceTypeInsertAction()
	{
		//检查权限
		$bind=$this->request->from('RaceTypeName');
		if(trim($bind['RaceTypeName'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			//文件上传
			$oUpload = new Base_Upload('RaceTypeIcon');
			$upload = $oUpload->upload('RaceTypeIcon');
			$res[1] = $upload->resultArr;
			$path = $res[1][1];
			//如果正确上传，就保存文件路径
			if(strlen($path['path'])>2)
			{
				$bind['comment']['RaceTypeIcon'] = $path['path'];
				$bind['comment']['RaceTypeIcon_root'] = $path['path_root'];
			}
			//数据压缩
			$bind['comment'] = json_encode($bind['comment']);
			//添加比赛类型
			$res = $this->oRace->insertRaceType($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	//修改比赛类型信息页面
	public function raceTypeModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceTypeModify");
		if($PermissionCheck['return'])
		{
			//站点根域名
			$RootUrl = "http://".$_SERVER['HTTP_HOST'];
			//比赛类型ID
			$RaceTypeId = intval($this->request->RaceTypeId);
			//获取比赛类型信息
			$RaceTypeInfo = $this->oRace->getRaceType($RaceTypeId,'*');
			//数据解包
			$RaceTypeInfo['comment'] = json_decode($RaceTypeInfo['comment'],true);
			include $this->tpl('Xrace_Race_RaceTypeModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//更新比赛类型
	public function raceTypeUpdateAction()
	{
		$bind=$this->request->from('RaceTypeId','RaceTypeName');
		//比赛类型名称不能为空
		if(trim($bind['RaceTypeName'])=="")
		{
			$response = array('errno' => 1);
		}
		//比赛类型ID必须大于0
		elseif(intval($bind['RaceTypeId'])<=0)
		{
			$response = array('errno' => 2);
		}
		else
		{
			//获取原有数据
			$RaceTypeInfo = $this->oRace->getRaceType($bind['RaceTypeId'],'*');
			//数据解包
			$bind['comment'] = json_decode($RaceTypeInfo['comment'],true);
			//文件上传
			$oUpload = new Base_Upload('RaceTypeIcon');
			$upload = $oUpload->upload('RaceTypeIcon');
			$res[1] = $upload->resultArr;
			$path = $res[1][1];
			//如果正确上传，就保存文件路径
			if(strlen($path['path'])>2)
			{
				$bind['comment']['RaceTypeIcon'] = $path['path'];
				$bind['comment']['RaceTypeIcon_root'] = $path['path_root'];
			}
			//数据压缩
			$bind['comment'] = json_encode($bind['comment']);
			//更新比赛类型
			$res = $this->oRace->updateRaceType($bind['RaceTypeId'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}

	//删除比赛类型
	public function raceTypeDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceTypeDelete");
		if($PermissionCheck['return'])
		{
			//比赛类型ID
			$RaceTypeId = intval($this->request->RaceTypeId);
			//删除比赛类型
			$this->oRace->deleteRaceType($RaceTypeId);
			//返回之前页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//删除比赛类型图标
	public function raceTypeIconDeleteAction()
	{
		//比赛类型ID
		$RaceTypeId = intval($this->request->RaceTypeId);
		//获取原有数据
		$RaceTypeInfo = $this->oRace->getRaceType($RaceTypeId,'comment');
		//图片数据解包
		$RaceTypeInfo['comment'] = json_decode($RaceTypeInfo['comment'],true);
		//删除存储的图片路径
		unset($RaceTypeInfo['comment']['RaceTypeIcon']);
		unset($RaceTypeInfo['comment']['RaceTypeIcon_root']);
		//图片数据压缩
		$RaceTypeInfo['comment']= json_encode($RaceTypeInfo['comment']);
		//更新数据
		$res = $this->oRace->updateRaceType($RaceTypeId,$RaceTypeInfo);
		//返回之前页面
		$this->response->goBack();
	}
}

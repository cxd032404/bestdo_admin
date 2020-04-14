<?php
/**
 * 赛事分组管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Xrace_RaceGroupController extends AbstractController
{
	/**运动类型列表:RaceGroup
	 * @var string
	 */
	protected $sign = '?ctl=xrace/race.group';
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
	//赛事分组列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//获取赛事ID
			$RaceCatalogId = isset($this->request->RaceCatalogId)?intval($this->request->RaceCatalogId):0;
            $DataPermissionListWhere = $this->manager->getDataPermissionByGroupWhere();
			//获取赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0,$DataPermissionListWhere);
			//获取分组列表
			$RaceGroupArr = $this->oRace->getRaceGroupList($RaceCatalogId,"*",$DataPermissionListWhere);
			//初始化空的分组列表
			$RaceGroupList = array();
			//循环分组列表
			foreach($RaceGroupArr as $key => $value)
			{
				//数据解包
				$value['comment'] = json_decode($value['comment'],true);
				//拼接权限审核条件
				$value['LicenseListText'] = isset($value['comment']['LicenseList'])? $this->oRace->ParthRaceLicenseListToHtml($value['comment']['LicenseList'],0,$key):"";
				//将分组分配到赛事下一级
				$RaceGroupList[$value['RaceCatalogId']]['RaceGroupList'][$key] = $value;
				//累加赛事下的分组数量
				$RaceGroupList[$value['RaceCatalogId']]['RaceGroupCount'] = isset($RaceGroupList[$value['RaceCatalogId']]['RaceGroupCount'])?$RaceGroupList[$value['RaceCatalogId']]['RaceGroupCount']+1:1;
				//如果赛事ID有效
				if(isset($RaceCatalogList[$value['RaceCatalogId']]))
				{
					//获取赛事名称
					$RaceGroupList[$value['RaceCatalogId']]['RaceCatalogName'] = $RaceCatalogList[$value['RaceCatalogId']]['RaceCatalogName'];
				}
				else
				{
					$RaceGroupList[$value['RaceCatalogId']]['RaceCatalogName'] = 	"未定义";
				}
				//数据解包
				//$RaceGroupList[$value['RaceCatalogId']]['RaceGroupList'][$key]['comment'] = json_decode($RaceGroupList[$value['RaceCatalogId']]['RaceGroupList'][$key]['comment'],true);
			}
			//渲染模板
			include $this->tpl('Xrace_Race_RaceGroupList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加赛事分组填写配置页面
	public function raceGroupAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceGroupInsert");
		if($PermissionCheck['return'])
		{
            $DataPermissionListWhere = $this->manager->getDataPermissionByGroupWhere();
			//赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0,$DataPermissionListWhere);
			//模板渲染
			include $this->tpl('Xrace_Race_RaceGroupAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加新赛事分组
	public function raceGroupInsertAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceGroupInsert");
		if($PermissionCheck['return'])
		{
            $DataPermissionListWhere = $this->manager->getDataPermissionByGroupWhere();
			//检查权限
			$bind=$this->request->from('RaceGroupName','RaceCatalogId');
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0,$DataPermissionListWhere);
			if(trim($bind['RaceGroupName'])=="")
			{
				$response = array('errno' => 1);
			}
			elseif(!isset($RaceCatalogList[$bind['RaceCatalogId']]))
			{
				$response = array('errno' => 3);
			}
			else
			{
				$res = $this->oRace->insertRaceGroup($bind);
				$response = $res ? array('errno' => 0) : array('errno' => 9);
			}
			echo json_encode($response);
			return true;
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}

	}
	//修改赛事分组页面
	public function raceGroupModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceGroupModify");
		if($PermissionCheck['return'])
		{
            $DataPermissionListWhere = $this->manager->getDataPermissionByGroupWhere();
			//赛事分组ID
			$RaceGroupId = trim($this->request->RaceGroupId);
			//赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0,$DataPermissionListWhere);
			//赛事分组信息
			$RaceGroupInfo = $this->oRace->getRaceGroup($RaceGroupId,'*');
			//数据解包
			$RaceGroupInfo['comment'] = json_decode($RaceGroupInfo['comment'],true);
			//如果有填写审核类型列表
			if(isset($RaceGroupInfo['comment']['LicenseList']))
			{
				//将执照判断条件转化为HTML
				$RaceLicenseListHtml = $this->oRace->ParthRaceLicenseListToHtml($RaceGroupInfo['comment']['LicenseList'],0,$RaceGroupInfo['RaceGroupId']);
			}
			//模板渲染
			include $this->tpl('Xrace_Race_RaceGroupModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//更新赛事分组信息
	public function raceGroupUpdateAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceGroupModify");
		if($PermissionCheck['return'])
		{
			$bind=$this->request->from('RaceGroupId','RaceGroupName','RaceCatalogId','LicenseList');
			if(is_array($bind['LicenseList']))
			{
				//获取条件类型列表
				$RaceLisenceTypeList = $this->oRace->getRaceLicenseType();
				//循环条件列表
				foreach($bind['LicenseList'] as $k => $v)
				{
					//如果条件类型不符合
					if(!isset($RaceLisenceTypeList[$v['LicenseType']]))
					{
						//删除数据
						unset($bind['LicenseList'][$k]);
					}
					//如果设置为不需要管理员赋予
					elseif(($v['LicenseType']=="manager") && ($v['License'] == "0"))
					{
						//删除数据
						unset($bind['LicenseList'][$k]);
					}
				}
			}
			else
			{
				//置为空数组
				$bind['LicenseList'] = array();
			}
			//获取赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0);
			//分组名称不能为空
			if(trim($bind['RaceGroupName'])=="")
			{
				$response = array('errno' => 1);
			}
			//分组ID必须填写
			elseif(intval($bind['RaceGroupId'])==0)
			{
				$response = array('errno' => 2);
			}
			//分组必须属于某个已配置的赛事
			elseif(!isset($RaceCatalogList[$bind['RaceCatalogId']]))
			{
				$response = array('errno' => 3);
			}
			else
			{
				//赛事分组信息
				$RaceGroupInfo = $this->oRace->getRaceGroup($bind['RaceGroupId'],'*');
				//数据解包
				$bind['comment'] = json_decode($RaceGroupInfo['comment'],true);
				//移动条件列表到comment数组下
				$bind['comment']['LicenseList'] = $bind['LicenseList'];
				//删除原有数组
				unset($bind['LicenseList']);
				//数据打包
				$bind['comment'] = json_encode($bind['comment']);
				//更新数据
				$res = $this->oRace->updateRaceGroup($bind['RaceGroupId'],$bind);
				$response = $res ? array('errno' => 0) : array('errno' => 9);
			}
			echo json_encode($response);
			return true;
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//删除赛事分组
	public function raceGroupDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceGroupDelete");
		if($PermissionCheck['return'])
		{
			$RaceGroupId = intval($this->request->RaceGroupId);
			$this->oRace->deleteRaceGroup($RaceGroupId);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加赛事分组的权限审核填写配置页面
	public function groupLicenseAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceGroupModify");
		if($PermissionCheck['return'])
		{
			//赛事分组ID
			$RaceGroupId = trim($this->request->RaceGroupId);
			//赛事分组信息
			$RaceGroupInfo = $this->oRace->getRaceGroup($RaceGroupId,'*');
			//数据解包
			$RaceGroupInfo['comment'] = json_decode($RaceGroupInfo['comment'],true);
			//获取条件类型列表
			$RaceLisenceTypeList = $this->oRace->getRaceLicenseType();
			//模板渲染
			include $this->tpl('Xrace_Race_GroupLicenseAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加赛事分组的权限审核
	public function groupLicenseInsertAction()
	{
		$bind=$this->request->from('RaceGroupId','LicenseList');
		if(is_array($bind['LicenseList']))
		{
			//获取条件类型列表
			$RaceLisenceTypeList = $this->oRace->getRaceLicenseType();
			//如果条件类型不符合
			if(!isset($RaceLisenceTypeList[$bind['LicenseList']['LicenseType']]))
			{
				//置为空数组
				$bind['LicenseList'] = array();
			}
			//如果设置为不需要管理员赋予
			elseif(($bind['LicenseList']['LicenseType']=="manager") && ($bind['LicenseList']['License'] == "0"))
			{
				//置为空数组
				$bind['LicenseList'] = array();
			}
		}
		else
		{
			//置为空数组
			$bind['LicenseList'] = array();
		}
		//分组ID必须填写
		if(intval($bind['RaceGroupId'])==0)
		{
			$response = array('errno' => 1);
		}
		else
		{
			if(count($bind['LicenseList'])>=1)
			{
				//赛事分组信息
				$RaceGroupInfo = $this->oRace->getRaceGroup($bind['RaceGroupId'],'*');
				//数据解包
				$bind['comment'] = json_decode($RaceGroupInfo['comment'],true);
				//初始化空数组
				$t = array();
				//如果已经有添加过审核条件
				if(isset($bind['comment']['LicenseList']))
				{
					//初始键值
					$i = 1;
					//标签表示数组内有重复数据
					$flag = 0;
					//将数组内的条件循环放到另外的临时数组里面按次序排放
					foreach($bind['comment']['LicenseList'] as $key => $value)
					{
						$t[$i] = $value;
						if(($value['LicenseType'] == $bind['LicenseList']['LicenseType']) && (count(array_diff($value['License'],$bind['LicenseList']['License']))==0))
						{$flag=1;}
						$i++;
					}
				}
				if($flag==1)
				{
					$response = array('errno' => 0);
				}
				else
				{
					//尾部添加新的数据
					$t[count($t)+1] = $bind['LicenseList'];
					//移动条件列表到comment数组下
					$bind['comment']['LicenseList'] = $t;
					//删除原有数组
					unset($bind['LicenseList']);
					//数据打包
					$bind['comment'] = json_encode($bind['comment']);
					//更新数据
					$res = $this->oRace->updateRaceGroup($bind['RaceGroupId'],$bind);
					$response = $res ? array('errno' => 0) : array('errno' => 9);
				}
			}
			else
			{
				$response = array('errno' => 0);
			}
		}
		echo json_encode($response);
		return true;
	}
	//更新赛事分组的权限审核
	public function groupLicenseDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceGroupModify");
		if($PermissionCheck['return'])
		{
			$RaceGroupId = intval($this->request->RaceGroupId);
			$LicenseId = intval($this->request->LicenseId);
			//赛事分组信息
			$RaceGroupInfo = $this->oRace->getRaceGroup($RaceGroupId,'*');
			//数据解包
			$bind['comment'] = json_decode($RaceGroupInfo['comment'],true);
			//初始化空数组
			$t = array();
			//如果已经有添加过审核条件
			if(isset($bind['comment']['LicenseList'][$LicenseId]))
			{
				unset($bind['comment']['LicenseList'][$LicenseId]);
				//初始键值
				$i = 1;
				//标签表示数组内有重复数据
				$flag = 0;
				//将数组内的条件循环放到另外的临时数组里面按次序排放
				foreach($bind['comment']['LicenseList'] as $key => $value)
				{
					$t[$i] = $value;
					$i++;
				}
				//移动条件列表到comment数组下
				$bind['comment']['LicenseList'] = $t;
				//数据打包
				$bind['comment'] = json_encode($bind['comment']);
				//更新数据
				$res = $this->oRace->updateRaceGroup($RaceGroupId,$bind);
			}
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//赛事分组的权限审核条件显示接口
	public function getLicenseConditionAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceGroupModify");
		if($PermissionCheck['return'])
		{
			//赛事分组ID
			$LicenseType = trim($this->request->LicenseType);
			//获取条件类型列表
			$RaceLisenceTypeList = $this->oRace->getRaceLicenseType();
			if(isset($RaceLisenceTypeList[$LicenseType]))
			{
				$functionName = $LicenseType."ConditionToHtml";
				echo  $this->oRace->$functionName("LicenseList",array(),$edit = 1);
			}
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//获取赛事获取已经选择的分组列表
	public function getGroupByCatalogAction()
	{
		//赛事ID
		$RaceCatalogId = intval($this->request->RaceCatalogId);
		//分组ID
		$RaceGroupId = intval($this->request->RaceGroupId);
		//所有赛事分组列表
		$RaceGroupArr = $this->oRace->getRaceGroupList($RaceCatalogId,'RaceGroupId,RaceGroupName');
		$text = '';
		//循环赛事分组列表
		foreach($RaceGroupArr as $GroupId => $RaceGroupInfo)
		{
			//初始化选中状态
			$selected = "";
			//如果分组ID与传入的分组ID相符
			if($RaceGroupInfo['RaceGroupId'] == $GroupId)
			{
				//选中拼接
				$selected = 'selected="selected"';
			}
			//字符串拼接
			$text .= '<option value="'.$RaceGroupInfo['RaceGroupId'].'">'.$RaceGroupInfo['RaceGroupName'].'</option>';
		}
		echo $text;
		die();
	}
}

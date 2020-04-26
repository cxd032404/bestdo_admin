<?php
/**
 * 赛事分站管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Xrace_RaceStageController extends AbstractController
{
	/**赛事分站:
	 * @var string
	 */
	protected $sign = '?ctl=xrace/race.stage';
	/**
	 * race对象
	 * @var object
	 */
	protected $oRace;
        /**
	 * product对象
	 * @var object
	 */
    protected $oProduct;
    protected $oCredit;
	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oRace = new Xrace_Race();
		$this->oProduct = new Xrace_Product();
        $this->oCredit = new Xrace_Credit();

    }
	//赛事分站列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			$DataPermissionListWhere = $this->manager->getDataPermissionByGroupWhere();
			//获取站点根域名
			$RootUrl = "http://".$_SERVER['HTTP_HOST'];
			//赛事ID
			$RaceCatalogId = isset($this->request->RaceCatalogId)?intval($this->request->RaceCatalogId):0;
			//赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0,$DataPermissionListWhere);
			//赛事分站列表
			$RaceStageArr = $this->oRace->getRaceStageList($RaceCatalogId,"RaceStageId,RaceStageName,RaceCatalogId,comment,StageStartDate,StageEndDate,RaceStageIcon,Display",0,$DataPermissionListWhere);
			//比赛-分组的层级规则
			$RaceStructureList  = $this->oRace->getRaceStructure();
			//赛事分组列表
			$RaceGroupList = $this->oRace->getRaceGroupList($RaceCatalogId,'RaceGroupId,RaceGroupName');
            //产品类型列表
			$ProductTypeList = $this->oProduct->getProductTypeList($RaceCatalogId,'ProductTypeId,ProductTypeName');
			//初始化一个空的赛事分站列表
			$RaceStageList = array();
			//循环赛事分站列表
			foreach($RaceStageArr as $RaceStageId => $RaceStageInfo)
			{
				$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId] = $RaceStageInfo;
				//计算分站数量，用于页面跨行显示
				$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageCount'] = isset($RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageCount'])?$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageCount']+1:1;
				//如果相关赛事ID有效
				if(isset($RaceCatalogList[$RaceStageInfo['RaceCatalogId']]))
				{
					//获取赛事ID
					$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceCatalogName'] = isset($RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceCatalogName'])?$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceCatalogName']:$RaceCatalogList[$RaceStageInfo['RaceCatalogId']]['RaceCatalogName'];
					//解包压缩数组
					$RaceStageInfo['RaceStageIcon'] = json_decode($RaceStageInfo['RaceStageIcon'],true);
					$t = array();
					$TotalRaceCount = 0;
					//如果没有配置比赛结构 或者 比赛结构配置不在配置列表中
					if(!isset($RaceStageInfo['comment']['RaceStructure']) || !isset($RaceStructureList[$RaceStageInfo['comment']['RaceStructure']]))
					{
						//默认为分组优先
						$RaceStageInfo['comment']['RaceStructure'] = "group";
					}
					//分组优先模式
					if($RaceStageInfo['comment']['RaceStructure'] == "group")
					{
						//如果有已经选择的赛事组别
						if(isset($RaceStageInfo['comment']['SelectedRaceGroup']) && is_array($RaceStageInfo['comment']['SelectedRaceGroup']))
						{
							$TotalRaceCount = 0;
							//循环各个组别
							foreach($RaceStageInfo['comment']['SelectedRaceGroup'] as $k => $v)
							{
								//获取各个组别的比赛场次数量
								$RaceCount = $this->oRace->getRaceCount($RaceStageInfo['RaceStageId'],$v);
								//如果有配置比赛场次
								if($RaceCount>0)
								{
									//添加场次数量
									$Prefix = "(".$RaceCount.")";
								}
								else
								{
									$Prefix = "";
								}
								$TotalRaceCount+=$RaceCount;
								//如果赛事组别配置有效
								if(isset($RaceGroupList[$v]))
								{
								    //生成到比赛详情页面的链接
									$t[$k] = "<a href='".Base_Common::getUrl('','xrace/race.stage','race.list',array('RaceStageId'=>$RaceStageInfo['RaceStageId'],'RaceGroupId'=>$v)) ."'>".$RaceGroupList[$v]['RaceGroupName'].$Prefix."</a>";
								}
							}
						}
						//如果检查后有至少一个有效的赛事组别配置
						if(count($t))
						{
							//生成页面显示的数组
							$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['SelectedGroupList'] = implode("/",$t);
							$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['GroupCount'] = count($t);
						}
						else
						{
							//生成默认的入口
							$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['SelectedGroupList'] = "尚未配置";
							$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['GroupCount'] = 0;
						}
						$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['RaceList']  = "<a href='".Base_Common::getUrl('','xrace/race.stage','race.list',array('RaceStageId'=>$RaceStageInfo['RaceStageId'])) ."'>"."比赛列表(".$TotalRaceCount.")";"</a>";
					}
					else
					{
						//获取比赛列表
						$RaceList = $this->oRace->getRaceList(array("RaceStageId"=>$RaceStageInfo['RaceStageId']),"RaceId,RaceName,comment");
						//比赛数量
						$RaceCount = count($RaceList);
						$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['RaceList']  = "<a href='".Base_Common::getUrl('','xrace/race.stage','race.list',array('RaceStageId'=>$RaceStageInfo['RaceStageId'])) ."'>"."比赛列表(".$RaceCount.")";"</a>";
						$t = array();
						foreach($RaceList as $RaceId => $RaceInfo)
						{
							$t[$RaceId] = "<a href='".Base_Common::getUrl('','xrace/race.stage','race.list',array('RaceStageId'=>$RaceStageInfo['RaceStageId'])) ."'>".$RaceInfo["RaceName"]."(".count($RaceInfo['comment']['SelectedRaceGroup']).")";"</a>";
						}
						//如果检查后有至少一个有效的比赛配置
						if(count($t))
						{
							//生成页面显示的数组
							$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['SelectedGroupList'] = implode("/",$t);
						}
						else
						{
							//生成默认的入口
							$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['SelectedGroupList'] = "尚未配置";
						}
					}
					// 初始化一个临时数组
					$t = array();
					$t2 = array();
					//如果有已经选择的产品
					if(isset($RaceStageInfo['comment']['SelectedProductList']) && is_array($RaceStageInfo['comment']['SelectedProductList']))
					{
						//循环已选择的产品列表
						foreach($RaceStageInfo['comment']['SelectedProductList'] as $ProductId => $ProductConfig)
						{
							//如果缓存中没有产品数据
							if(!isset($ProductList[$ProductId]))
							{
								//获取产品数据
								$ProductInfo = $this->oProduct->getProduct($ProductId,"ProductId,ProductTypeId");
								 //如果产品获取有效
								if(isset($ProductInfo['ProductId']))
								{
									//置入缓存
									$ProductList[$ProductId] = $ProductInfo;
								}
							}
							else
							{
								$ProductInfo = $ProductList[$ProductId];
							}
							//如果获取到的产品的分类有效
							if(isset($ProductTypeList[$ProductInfo['ProductTypeId']]))
							{
								$Stock = 0;
							    //如果缓存中的产品类型有累加数量
								if(isset($t[$ProductInfo['ProductTypeId']]))
								{
									foreach($ProductConfig as $k => $v)
                                    {
                                        $Stock += $v['Stock'];
                                    }
                                    if($Stock>=0)
                                    {
                                        //数量累加
                                        $t[$ProductInfo['ProductTypeId']]['ProductCount']++;
                                    }
								}
								else
								{
									//初始化数量
									$t[$ProductInfo['ProductTypeId']] = array("ProductCount"=>1,"ProductTypeName"=>$ProductTypeList[$ProductInfo['ProductTypeId']]['ProductTypeName']);
								}
								$t2[$ProductInfo['ProductTypeId']] = $t[$ProductInfo['ProductTypeId']]['ProductTypeName']."(".$t[$ProductInfo['ProductTypeId']]['ProductCount'].")";
							}
						}
					}
                    //获取比赛结构名称
                    $RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['RaceStructureName'] = $RaceStructureList[$RaceStageInfo['comment']['RaceStructure']];
					//拼接页面显示的数量
					$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['SelectedProductList'] = count($t2)>0?implode("/", $t2):"尚未配置";
				}
				else
				{
					$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceCatalogName'] = 	"未定义";
				}
				if(isset($RaceStageInfo['RaceStageIcon']) && is_array($RaceStageInfo['RaceStageIcon']) && count($RaceStageInfo['RaceStageIcon']))
				{
                    $RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['RaceStageIconList'] = "";
				    //循环图标列表
                    foreach ($RaceStageInfo['RaceStageIcon'] as $k => $v)
					{
						//依次叠加
					    $RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['RaceStageIconList'] .= "<a href='".$RootUrl.$v['RaceStageIcon_root']."' target='_blank'>图标".$k."</a>/";
					}
					//格式化处理
					$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['RaceStageIconList'] = rtrim($RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['RaceStageIconList'], "/");
				}
				else
				{
					$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['RaceStageIconList'] = '未上传';
				}
				$RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['RaceStageStatus'] = $this->oRace->getRaceStageTimeStatus($RaceStageId,0);
                $RaceStageList[$RaceStageInfo['RaceCatalogId']]['RaceStageList'][$RaceStageId]['RaceStageName'].=($RaceStageInfo['Display'])?"":"(隐藏)";
			}
			//渲染模板
			include $this->tpl('Xrace_Race_RaceStageList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	public function raceStageResultQrAction()
    {
        include('Third/phpqrcode/phpqrcode.php');
        //分站ID
        $RaceStageId = intval($this->request->RaceStageId);
        $url = urlencode('http://register.xrace.cn/search_chip/show/index/'.$RaceStageId.'?preview=pv170420');
        include $this->tpl('Xrace_Race_QR');
    }
	//添加赛事分站填写配置页面
	public function raceStageAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceStageInsert");
		if($PermissionCheck['return'])
		{
            //获取比赛类型列表
            $RaceTypeList  = $this->oRace->getRaceTypeList("RaceTypeId,RaceTypeName");
            //特殊折扣列表
            $ApplySepcialDiscount  = $this->oRace->getApplySepcialDiscount();
			//初始化起止时间
			$StageStartDate = date("Y-m-d",time()+30*86400);
			$StageEndDate = date("Y-m-d",time()+32*86400);
            $DataPermissionListWhere = $this->manager->getDataPermissionByGroupWhere();
			//赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0,$DataPermissionListWhere);
			//比赛-分组的层级规则
			$RaceStructureList  = $this->oRace->getRaceStructure();
			//渲染模板
			include $this->tpl('Xrace_Race_RaceStageAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加新赛事分站
	public function raceStageInsertAction()
	{
		//获取 页面参数
		$bind=$this->request->from('Location','RaceStageName','RaceCatalogId','StageStartDate','StageEndDate','RaceStageComment','RaceStructure','ApplyStartTime','ApplyEndTime','PriceList','PriceDiscount','Display','SpecialDiscount','ApplyLimit','CreditRate','CreditStack','RaceTypeId');
		//获取已经选定的分组列表
		$SelectedRaceGroup = $this->request->from('SelectedRaceGroup');
		//赛事列表
		$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0);
		//分站名称不能为空
		if(trim($bind['RaceStageName'])=="")
		{
			$response = array('errno' => 1);
		}
		//必须选定一个有效的赛事ID
		elseif(!isset($RaceCatalogList[$bind['RaceCatalogId']]))
		{
			$response = array('errno' => 3);
		}
		//至少选定一个分组
		elseif(count($SelectedRaceGroup['SelectedRaceGroup'])==0)
		{
			$response = array('errno' => 4);
		}
		else
		{

			//记录分组信息
			$bind['comment']['SelectedRaceGroup'] = $SelectedRaceGroup['SelectedRaceGroup'];
			//文件上传
			$oUpload = new Base_Upload('RaceStageIcon');
			$upload = $oUpload->upload('RaceStageIcon');
			$res = $upload->resultArr;
			foreach($upload->resultArr as $iconkey=>$iconvalue)
			{
				$path = $iconvalue;
				//如果正确上传，就保存文件路径
				if(strlen($path['path'])>2)
				{
					$bind['RaceStageIcon'][$iconkey]['RaceStageIcon'] = $path['path'];
					$bind['RaceStageIcon'][$iconkey]['RaceStageIcon_root'] = $path['path_root'];
				}
			}
			//比赛结构
			$bind['comment']['RaceStructure'] = $bind['RaceStructure'];
			//删除原有数据
			unset($bind['RaceStructure']);
            //价格对应列表
            $bind['comment']['PriceList'] = $this->oRace->getPriceList(trim($bind['PriceList']),1);
            //删除原有数据
            unset($bind['PriceList']);
            //折扣对应列表
            $bind['comment']['PriceDiscount'] = $this->oRace->getPriceList(trim($bind['PriceDiscount']),1);
            //删除原有数据
            unset($bind['PriceDiscount']);
            //特别折扣对应列表
            $bind['comment']['SpecialDiscount'] = trim($bind['SpecialDiscount']);
            //删除原有数据
            unset($bind['SpecialDiscount']);
            //单次报名人数上限
            $bind['comment']['ApplyLimit'] = abs(intval($bind['ApplyLimit']));
            //删除原有数据
            unset($bind['ApplyLimit']);
            //付款金额的积分抵扣比例上下限
            $bind['comment']['CreditRate'] = array("min"=>abs($bind['CreditRate']['min']),"max"=>abs($bind['CreditRate']['max']));
            //删除原有数据
            unset($bind['CreditRate']);
            //付款金额的积分使用的最小单位
            $bind['comment']['CreditStack'] = abs(intval($bind['CreditStack']));
            //删除原有数据
            unset($bind['CreditStack']);
            //比赛类型
            $bind['comment']['RaceTypeId'] = abs(intval($bind['RaceTypeId']));
            //删除原有数据
            unset($bind['RaceTypeId']);
			//数据压缩
			$bind['comment'] = json_encode($bind['comment']);
			//图片数据压缩
			$bind['RaceStageIcon'] = json_encode($bind['RaceStageIcon']);
			//插入数据
			$res = $this->oRace->insertRaceStage($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	//修改赛事分站填写配置页面
	public function raceStageModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceStageModify");
		if($PermissionCheck['return'])
		{
            //获取比赛类型列表
            $RaceTypeList  = $this->oRace->getRaceTypeList("RaceTypeId,RaceTypeName");
            //特殊折扣列表
		    $ApplySepcialDiscount  = $this->oRace->getApplySepcialDiscount();
			//比赛-分组的层级规则
			$RaceStructureList  = $this->oRace->getRaceStructure();
			//分站ID
			$RaceStageId = intval($this->request->RaceStageId);
            $DataPermissionListWhere = $this->manager->getDataPermissionByGroupWhere();
			//赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0,$DataPermissionListWhere);
            //分站数据
            $RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,'*');
			//分组列表
			$RaceGroupList = $this->oRace->getRaceGroupList($RaceStageInfo['RaceCatalogId'],'RaceGroupId,RaceGroupName');
			//数据解包
			$RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
            //如果没有配置比赛结构 或者 比赛结构配置不在配置列表中
			if(!isset($RaceStageInfo['comment']['RaceStructure']) || !isset($RaceStructureList[$RaceStageInfo['comment']['RaceStructure']]))
			{
				//默认为分组优先
				$RaceStageInfo['comment']['RaceStructure'] = "group";
			}
            $RaceStageInfo['comment']['SearchKeyWord'] = implode("|",$RaceStageInfo['comment']['SearchKeyWord']);
			//图片数据解包
			$RaceStageInfo['RaceStageIcon'] = json_decode($RaceStageInfo['RaceStageIcon'],true);
			//循环赛事分组列表
			foreach($RaceGroupList as $RaceGroupId => $RaceGroupInfo)
			{
				//如果出现在选定的分组列表当中
				if(in_array($RaceGroupId,$RaceStageInfo['comment']['SelectedRaceGroup']))
				{
					$RaceGroupList[$RaceGroupId]['selected'] = 1;
				}
				else
				{
					$RaceGroupList[$RaceGroupId]['selected'] = 0;
				}
			}
			//获得赛事分组的图标
			$RaceStageIconList = array();
			if(isset($RaceStageInfo['RaceStageIcon']) && is_array($RaceStageInfo['RaceStageIcon']))
			{
				$RaceStageIconList = $RaceStageInfo['RaceStageIcon'];
			}
			//渲染模板
			include $this->tpl('Xrace_Race_RaceStageModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//更新赛事分站
	public function raceStageUpdateAction()
	{
		//获取 页面参数
		$bind = $this->request->from('Location','RaceStageId','RaceStageName','RaceCatalogId','StageStartDate','StageEndDate','RaceStageComment','RaceStructure','ApplyStartTime','ApplyEndTime','PriceList','PriceDiscount','SpecialDiscount','Display','ApplyLimit','CreditRate','CreditStack','SearchKeyWord','RaceTypeId');
		//获取已经选定的分组列表
		$SelectedRaceGroup = $this->request->from('SelectedRaceGroup');
		//赛事列表
		$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0);
		//分站名称不能为空
		if(trim($bind['RaceStageName'])=="")
		{
			$response = array('errno' => 1);
		}
		//赛事分站ID必须大于0
		elseif(intval($bind['RaceStageId'])<=0)
		{
			$response = array('errno' => 2);
		}
		//必须选定一个有效的赛事ID
		elseif(!isset($RaceCatalogList[$bind['RaceCatalogId']]))
		{
			$response = array('errno' => 3);
		}
		//必须选定一个有效的赛事ID
		elseif(count($SelectedRaceGroup['SelectedRaceGroup'])==0)
		{
			$response = array('errno' => 4);
		}
		else
		{
			//获取原有数据
			$RaceStageInfo = $this->oRace->getRaceStage($bind['RaceStageId']);
			//数据解包
			$bind['comment'] = json_decode($RaceStageInfo['comment'],true);
            $oldSearchKeyWord = $bind['comment']['SearchKeyWord'];

                //图片数据解包
			$bind['RaceStageIcon'] = json_decode($RaceStageInfo['RaceStageIcon'],true);
			$bind['comment']['SelectedRaceGroup'] = $SelectedRaceGroup['SelectedRaceGroup'];
			//文件上传
			$oUpload = new Base_Upload('RaceStageIcon');
			$upload = $oUpload->upload('RaceStageIcon');
			$res = $upload->resultArr;
			foreach($upload->resultArr as $iconkey=>$iconvalue)
			{
				$path = $iconvalue;
				//如果正确上传，就保存文件路径
				if(strlen($path['path'])>2)
				{
					$bind['RaceStageIcon'][$iconkey]['RaceStageIcon'] = $path['path'];
					$bind['RaceStageIcon'][$iconkey]['RaceStageIcon_root'] = $path['path_root'];
				}
			}
			$bind['comment']['RaceStructure'] = $bind['RaceStructure'];
			//删除原有数据
			unset($bind['RaceStructure']);
            //价格对应列表
            $bind['comment']['PriceList'] = $this->oRace->getPriceList(trim($bind['PriceList']),1);
            //删除原有数据
            unset($bind['PriceList']);
            //折扣对应列表
            $bind['comment']['PriceDiscount'] = $this->oRace->getPriceList(trim($bind['PriceDiscount']),1);
            //删除原有数据
            unset($bind['PriceDiscount']);
            //特别折扣对应列表
            $bind['comment']['SpecialDiscount'] = trim($bind['SpecialDiscount']);
            //删除原有数据
            unset($bind['SpecialDiscount']);
            //单次报名人数上限
            $bind['comment']['ApplyLimit'] = abs(intval($bind['ApplyLimit']));
            //删除原有数据
            unset($bind['ApplyLimit']);
            //付款金额的积分抵扣比例上下限
            $bind['comment']['CreditRate'] = array("min"=>abs($bind['CreditRate']['min']),"max"=>abs($bind['CreditRate']['max']));
            //删除原有数据
            unset($bind['CreditRate']);
            //付款金额的积分使用的最小单位
            $bind['comment']['CreditStack'] = abs(intval($bind['CreditStack']));
            //删除原有数据
            unset($bind['CreditStack']);
            //比赛类型
            $bind['comment']['RaceTypeId'] = abs(intval($bind['RaceTypeId']));
            //删除原有数据
            unset($bind['RaceTypeId']);
            //更新搜索关键字相关
            $oSearch = new Xrace_Search();
            $bind['comment']['SearchKeyWord'] = $oSearch->processKeywordText(trim($bind['SearchKeyWord']));
            $newKeyword = $bind['comment']['SearchKeyWord'];
            //删除原有数据
            unset($bind['SearchKeyWord']);
			//数据压缩
			$bind['comment'] = json_encode($bind['comment']);
			//图片数据压缩
			$bind['RaceStageIcon'] = json_encode($bind['RaceStageIcon']);
			$res = $this->oRace->updateRaceStage($bind['RaceStageId'],$bind);
            $oSearch->updateSearchKeyWordInfo($newKeyword,$oldSearchKeyWord,array("RaceStageId"=>$bind['RaceStageId']));
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	//删除赛事分站
	public function raceStageDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceStageDelete");
		if($PermissionCheck['return'])
		{
			//赛事分赞ID
			$RaceStageId = intval($this->request->RaceStageId);
			//获取赛事分站信息
			$RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,'*');
			//如果有获取到赛事分站信息
			if(isset($RaceStageInfo['RaceStageId']))
			{
                //数据解包
                $bind['comment'] = json_decode($RaceStageInfo['comment'],true);
                //更新搜索关键字相关
                $oSearch = new Xrace_Search();
                $oldSearchKeyWord = $bind['comment']['SearchKeyWord'];
                $oSearch->updateSearchKeyWordInfo(array(),$oldSearchKeyWord,array("RaceStageId"=>$RaceStageId));
				//删除
				$this->oRace->deleteRaceStage($RaceStageId);
			}
			//返回之前页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//删除赛事分站图标
	public function raceStageIconDeleteAction()
	{
		//赛事分站ID
		$RaceStageId = intval($this->request->RaceStageId);
		//图标ID
		$LogoId = intval($this->request->LogoId);
		//获取原有数据
		$RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,"RaceStageIcon");
		//图片数据解包
		$RaceStageInfo['RaceStageIcon'] = json_decode($RaceStageInfo['RaceStageIcon'],true);
		foreach($RaceStageInfo['RaceStageIcon'] as $k => $v)
		{
			if($k == $LogoId)
			{
				unset($RaceStageInfo['RaceStageIcon'][$k]);
			}
		}
		//图片数据压缩
		$RaceStageInfo['RaceStageIcon'] = json_encode($RaceStageInfo['RaceStageIcon']);
		//更新数据
		$res = $this->oRace->updateRaceStage($RaceStageId,$RaceStageInfo);
		//返回之前页面
		$this->response->goBack();
	}
	//获取赛事分站已经选择的分组列表
	public function getSelectedGroupAction()
	{
		//赛事ID
		$RaceCatalogId = intval($this->request->RaceCatalogId);
		//赛事分站ID
		$RaceStageId = intval($this->request->RaceStageId);
		//所有赛事分组列表
		$RaceGroupList = $this->oRace->getRaceGroupList($RaceCatalogId);
		//如果有传赛事分站ID
		if($RaceStageId)
		{
			//获取赛事分站信息
			$RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,'*');
			//数据解包
			$RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
		}
		else
		{
			//置为空数组
			$RaceStageInfo['comment']['SelectedRaceGroup'] = array();
		}
		//循环赛事分组列表
		foreach($RaceGroupList as $RaceGroupId => $RaceGroupInfo)
		{
			//如果有选择该赛事分组
			if(in_array($RaceGroupId,$RaceStageInfo['comment']['SelectedRaceGroup']))
			{
				//拼接单选框，并选中
				$t[$RaceGroupId] = '<input type="checkbox"  name="SelectedRaceGroup[]" value='.$RaceGroupId.' checked>'.$RaceGroupInfo['RaceGroupName'];
			}
			else
			{
				//拼接单选框，不选中
				$t[$RaceGroupId] = '<input type="checkbox"  name="SelectedRaceGroup[]" value='.$RaceGroupId.'>'.$RaceGroupInfo['RaceGroupName'];
			}
		}
		//字符串组合
		$text = implode("  ",$t);
		//如果当前没有已经选择的赛事分组列表
		$text = (trim($text!=""))?$text:"暂无分类";
		echo $text;
		die();
	}
	//比赛列表页面
	public function raceListAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
            //比赛-分组的层级规则
			$RaceStructureList  = $this->oRace->getRaceStructure();
			//赛事分站ID
			$RaceStageId = intval($this->request->RaceStageId);
			//赛事分组ID
			$RaceGroupId = intval($this->request->RaceGroupId);
			//获取当前分站信息
			$RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,'*');
			//解包压缩数组
			$RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
			//如果没有配置比赛结构 或者 比赛结构配置不在配置列表中
			if(!isset($RaceStageInfo['comment']['RaceStructure']) || !isset($RaceStructureList[$RaceStageInfo['comment']['RaceStructure']]))
			{
				//默认为分组优先
				$RaceStageInfo['comment']['RaceStructure'] = "group";
			}
			//如果当前分站未配置了当前分组
			if(!in_array($RaceGroupId,$RaceStageInfo['comment']['SelectedRaceGroup']))
			{
				$RaceGroupId = 0;
			}
			//获取赛事分组信息
			$RaceGroupInfo = $this->oRace->getRaceGroup($RaceGroupId,'*');
			//如果赛事分组尚未配置
			if(!$RaceGroupInfo['RaceGroupId'])
			{
				$RaceGroupId = 0;
			}
			//获取比赛列表
			$RaceList = $this->oRace->getRaceList(array("RaceStageId"=>$RaceStageId,"RaceGroupId"=>$RaceGroupId));
			//获取比赛类型列表
			$RaceTypeList  = $this->oRace->getRaceTypeList("RaceTypeId,RaceTypeName");
			//赛事分组列表
			$RaceGroupList = $this->oRace->getRaceGroupList($RaceStageInfo['RaceCatalogId'],'RaceGroupId,RaceGroupName');
			foreach($RaceList as $RaceId => $RaceInfo)
			{
				//获取比赛当前状态
				$RaceStatus = $this->oRace->getRaceTimeStatus($RaceInfo);
				$RaceList[$RaceId]['RaceStatus'] = $RaceStatus['RaceStatusName'];
				if($RaceStageInfo['comment']['RaceStructure'] == "group")
				{
					//获取比赛类型名称
					$RaceList[$RaceId]['RaceGroupName'] = isset($RaceGroupList[$RaceInfo['RaceGroupId']]['RaceGroupName'])?$RaceGroupList[$RaceInfo['RaceGroupId']]['RaceGroupName']:"未配置";
				}
				else
				{
					$t = array();
                    foreach($RaceInfo['comment']['SelectedRaceGroup'] as $k => $v)
					{
						if(isset($RaceGroupList[$k]) && $v['Selected'])
						{
							$t[$k] = $RaceGroupList[$k]['RaceGroupName'];
						}
					}
					$RaceList[$RaceId]['RaceGroupName'] = count($t)?implode("<br>",$t):"未配置";
				}
				//获取比赛类型名称
				$RaceList[$RaceId]['RaceTypeName'] = isset($RaceTypeList[$RaceInfo['RaceTypeId']]['RaceTypeName'])?$RaceTypeList[$RaceInfo['RaceTypeId']]['RaceTypeName']:"未配置";
			}
			//渲染模板
			include $this->tpl('Xrace_Race_RaceList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加比赛配置信息填写页面
	public function raceAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
            //团队排名/个人排名
		    $RaceResultTypeList  = $this->oRace->getResultTypeList();
            //团队排名取个人成绩/汇总成绩
            $TeamResultTypeList  = $this->oRace->getTeamResultTypeList();
		    //比赛-分组的层级规则
			$RaceStructureList  = $this->oRace->getRaceStructure();
			//赛事分站ID
			$RaceStageId = intval($this->request->RaceStageId);
			//赛事分组ID
			$RaceGroupId = intval($this->request->RaceGroupId);
			//获取赛事分站信息
			$RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,"RaceStageId,RaceStageName,RaceCatalogId,comment,ApplyStartTime,ApplyEndTime");
			//数据解包
			$RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
			//如果没有配置比赛结构 或者 比赛结构配置不在配置列表中
			if(!isset($RaceStageInfo['comment']['RaceStructure']) || !isset($RaceStructureList[$RaceStageInfo['comment']['RaceStructure']]))
			{
				//默认为分组优先
				$RaceStageInfo['comment']['RaceStructure'] = "group";
			}
			//获取当前赛事下的分组列表
			$RaceGroupList = $this->oRace->getRaceGroupList($RaceStageInfo['RaceCatalogId'],"RaceGroupName,RaceGroupId");
			//如果当前传入的分组ID没有配置
			if(!in_array($RaceGroupId,$RaceStageInfo['comment']['SelectedRaceGroup']))
			{
				//置为0
				$RaceGroupId = 0;
                //循环已经配置的分组
                foreach($RaceStageInfo['comment']['SelectedRaceGroup'] as $k => $v)
                {
                    //如果查到就保留
                    if(isset($RaceGroupList[$v]))
                    {
                        $RaceStageInfo['comment']['SelectedRaceGroup'][$k] = $RaceGroupList[$v];
                    }
                    //否则就删除
                    else
                    {
                        unset($RaceStageInfo['comment']['SelectedRaceGroup'][$k]);
                    }
                }
			}
			//获取比赛类型列表
			$RaceTypeList  = $this->oRace->getRaceTypeList("RaceTypeId,RaceTypeName");
			//获取计时数据方式
			$RaceTimingTypeList = $this->oRace->getTimingType();
			//获取计时成绩计算方式
			$RaceTimingResultTypeList = $this->oRace->getRaceTimingResultType("");
            //获取终点成绩计算方式
            $FinalResultTypeList = $this->oRace->getFinalResultType("");
			//报名时间调用分站的报名时间
			$ApplyStartTime = $RaceStageInfo['ApplyStartTime'];
			$ApplyEndTime = $RaceStageInfo['ApplyEndTime'];
			//初始化开始和结束时间
			$StartTime = date("Y-m-d H:i:s",time()+86400*15);
			$EndTime = date("Y-m-d H:i:s",time()+86400*16);
			$MaxTeamRank = 5;
			for($i=1;$i<=$MaxTeamRank;$i++)
			{$t[$i] = $i;}
            //最大每分钟处理次数
            $ProcessRate = $this->oRace->getMaxProcessRate();
            $PrecessRateList = array();
            for($i=1;$i<=$ProcessRate;$i++)
            {
                $PrecessRateList[] = $i;
            }
            //获取关联赛事下的积分类目列表
            $CreditArr = $this->oCredit->getCreditList($RaceStageInfo['RaceCatalogId']);
            $CreditArr[0] = array("CreditId"=>0,"CreditName"=>"全部","checked"=>1);
            ksort($CreditArr);
			//渲染模板
			include $this->tpl('Xrace_Race_RaceAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//修改比赛配置信息填写页面
    public function raceModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //团队排名/个人排名
            $RaceResultTypeList  = $this->oRace->getResultTypeList();
            //团队排名取个人成绩/汇总成绩
            $TeamResultTypeList  = $this->oRace->getTeamResultTypeList();
            //比赛-分组的层级规则
            $RaceStructureList  = $this->oRace->getRaceStructure();
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //比赛分组
            $RaceGroupId = intval($this->request->RaceGroupId);
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId);
            //获取赛事分站信息
            $RaceStageInfo = $this->oRace->getRaceStage($RaceInfo['RaceStageId'],"RaceStageId,RaceStageName,RaceCatalogId,comment");
            //数据解包
            $RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
            //格式化分组ID
            $RaceGroupId = in_array($RaceGroupId,array(0,$RaceInfo['RaceGroupId']))?$RaceGroupId:0;
            //如果没有配置比赛结构 或者 比赛结构配置不在配置列表中
            if(!isset($RaceStageInfo['comment']['RaceStructure']) || !isset($RaceStructureList[$RaceStageInfo['comment']['RaceStructure']]))
            {
                //默认为分组优先
                $RaceStageInfo['comment']['RaceStructure'] = "group";
            }
            //如果有获取到比赛信息
            if(isset($RaceInfo['RaceId']))
            {
                //获取比赛类型列表
                $RaceTypeList  = $this->oRace->getRaceTypeList("RaceTypeId,RaceTypeName");
                //获取计时数据方式
                $RaceTimingTypeList = $this->oRace->getTimingType();
                //获取计时成绩计算方式
                $RaceTimingResultTypeList = $this->oRace->getRaceTimingResultType("");
                //获取终点成绩计算方式
                $FinalResultTypeList = $this->oRace->getFinalResultType("");
                //数据解包
                $RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
                //解包数组
                $RaceInfo['comment']['RaceStartMicro'] = isset($RaceInfo['comment']['RaceStartMicro'])?$RaceInfo['comment']['RaceStartMicro']:0;
                //解包地图数组
                $RaceInfo['RouteInfo'] = json_decode($RaceInfo['RouteInfo'],true);
                if($RaceStageInfo['comment']['RaceStructure'] == "race")
                {
                    //获取当前赛事下的分组列表
                    $RaceGroupList = $this->oRace->getRaceGroupList($RaceStageInfo['RaceCatalogId'],"RaceGroupName,RaceGroupId");
                    //置为0
                    $RaceGroupId = 0;
                    //循环已经配置的分组
                    foreach($RaceStageInfo['comment']['SelectedRaceGroup'] as $k => $v)
                    {
                        //如果查到就保留
                        if(isset($RaceGroupList[$v]))
                        {
                            $RaceStageInfo['comment']['SelectedRaceGroup'][$k] = array_merge(isset($RaceInfo['comment']['SelectedRaceGroup'][$v])?$RaceInfo['comment']['SelectedRaceGroup'][$v]:array(),$RaceGroupList[$v]);
                            if(strtotime($RaceStageInfo['comment']['SelectedRaceGroup'][$k]['StartTime'])==0)
                            {
                                $RaceStageInfo['comment']['SelectedRaceGroup'][$k]['StartTime'] = $RaceInfo['StartTime'];
                                $RaceStageInfo['comment']['SelectedRaceGroup'][$k]['RaceStartMicro'] = $RaceInfo['comment']['RaceStartMicro'];
                                $RaceStageInfo['comment']['SelectedRaceGroup'][$k]['EndTime'] = $RaceInfo['EndTime'];
                            }
                        }
                        //否则就删除
                        else
                        {
                            unset($RaceStageInfo['comment']['SelectedRaceGroup'][$k]);
                        }
                    }
                }
                $MaxTeamRank = 5;
                for($i=1;$i<=$MaxTeamRank;$i++)
                {$t[$i] = $i;}
                //获取关联赛事下的积分类目列表
                $CreditArr = $this->oCredit->getCreditList($RaceStageInfo['RaceCatalogId']);
                $CreditArr[0] = array("CreditId"=>0,"CreditName"=>"全部");
                foreach($RaceInfo['RouteInfo']['ResultCreditList'] as $CreditId => $CreditInfo)
                {
                    if(isset($RaceInfo['RouteInfo']['ResultCreditList'][$CreditId]))
                    {
                        $CreditArr[$CreditId]['checked'] = 1;
                    }
                }
                ksort($CreditArr);
                //最大每分钟处理次数
                $ProcessRate = $this->oRace->getMaxProcessRate();
                $PrecessRateList = array();
                for($i=1;$i<=$ProcessRate;$i++)
                {
                    $PrecessRateList[] = $i;
                }
                //渲染模板
                include $this->tpl('Xrace_Race_RaceModify');
            }
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加选手信息填写页面
    public function raceUserAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //团队排名/个人排名
            $RaceResultTypeList  = $this->oRace->getResultTypeList();
            //团队排名取个人成绩/汇总成绩
            $TeamResultTypeList  = $this->oRace->getTeamResultTypeList();
            //比赛-分组的层级规则
            $RaceStructureList  = $this->oRace->getRaceStructure();
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //比赛分组
            $RaceGroupId = intval($this->request->RaceGroupId);
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId);
            //获取赛事分站信息
            $RaceStageInfo = $this->oRace->getRaceStage($RaceInfo['RaceStageId'],"RaceStageId,RaceStageName,RaceCatalogId,comment");
            //数据解包
            $RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
            //格式化分组ID
            $RaceGroupId = in_array($RaceGroupId,array(0,$RaceInfo['RaceGroupId']))?$RaceGroupId:0;
            $oUser = new Xrace_UserInfo();
            //获取性别列表
            $SexList = $oUser->getSexList();
            //获取实名认证证件类型列表
            $AuthIdTypesList = $oUser->getAuthIdType();
            //如果没有配置比赛结构 或者 比赛结构配置不在配置列表中
            if(!isset($RaceStageInfo['comment']['RaceStructure']) || !isset($RaceStructureList[$RaceStageInfo['comment']['RaceStructure']]))
            {
                //默认为分组优先
                $RaceStageInfo['comment']['RaceStructure'] = "group";
            }
            //如果有获取到比赛信息
            if(isset($RaceInfo['RaceId']))
            {
                if($RaceStageInfo['comment']['RaceStructure'] == "race")
                {
                    //获取当前赛事下的分组列表
                    $RaceGroupList = $this->oRace->getRaceGroupList($RaceStageInfo['RaceCatalogId'],"RaceGroupName,RaceGroupId");
                    //置为0
                    $RaceGroupId = 0;
                    //循环已经配置的分组
                    foreach($RaceStageInfo['comment']['SelectedRaceGroup'] as $k => $v)
                    {
                        //如果查到就保留
                        if(isset($RaceGroupList[$v]))
                        {
                            $RaceStageInfo['comment']['SelectedRaceGroup'][$k] = array_merge(isset($RaceInfo['comment']['SelectedRaceGroup'][$v])?$RaceInfo['comment']['SelectedRaceGroup'][$v]:array(),$RaceGroupList[$v]);
                        }
                        //否则就删除
                        else
                        {
                            unset($RaceStageInfo['comment']['SelectedRaceGroup'][$k]);
                        }
                    }
                }
                //渲染模板
                include $this->tpl('Xrace_Race_RaceUserAdd');
            }
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //复制比赛配置填写页面
    public function raceCopySubmitAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId,"RaceId,RaceName,RaceStageId");
            //如果有获取到比赛信息
            if(isset($RaceInfo['RaceId']))
            {
                $RaceInfo['RaceName'] = $RaceInfo['RaceName']."_副本";
                //渲染模板
                include $this->tpl('Xrace_Race_RaceCopy');
            }
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
	//添加比赛
	public function raceInsertAction()
	{
		//获取 页面参数
		$bind=$this->request->from('RaceName','RaceStageId','RaceGroupId','PriceList','ApplyStartTime','ApplyEndTime','StartTime','EndTime','SingleUser','TeamUser','SingleUserLimit','TeamLimit','TeamUserMin','TeamUserMax','SexUser','RaceTypeId','RaceComment','MustSelect','SingleSelect','TimeDB','TimePrefix','RaceTimingType','RaceTimingResultType','RaceStartMicro','SelectedRaceGroup','NoStart','TeamResultRank','ResultType','ResultNeedConfirm','CreditList','FinalResultType','ProcessRate','TeamResultRankType');
		//转化时间为时间戳
		$ApplyStartTime = strtotime(trim($bind['ApplyStartTime']));
		$ApplyEndTime = strtotime(trim($bind['ApplyEndTime']));
		$StartTime = strtotime(trim($bind['StartTime']));
		$EndTime = strtotime(trim($bind['EndTime']));
		//比赛名称不能为空
		if(trim($bind['RaceName'])=="")
		{
			$response = array('errno' => 1);
		}
		//分站ID必须大于0
		elseif(intval($bind['RaceStageId'])<=0)
		{
			$response = array('errno' => 2);
		}
		//单人报名和团队报名至少要选择一个
		elseif((intval($bind['SingleUser'])+intval($bind['TeamUser'])) == 0)
		{
			$response = array('errno' => 7);
		}
		//结束时间不能早于开始时间
		elseif($EndTime<$StartTime)
		{
			$response = array('errno' => 10);
		}
        //结束报名时间不能早于开始报名时间
        elseif ((count($bind['SelectedRaceGroup'])==0) && ($ApplyEndTime<=$ApplyStartTime))
        {
            $response = array('errno' => 11);
        }
        //结束报名时间不能晚于比赛开始时间
        elseif ((count($bind['SelectedRaceGroup'])==0) && ($ApplyEndTime>=$StartTime))
        {
            $response = array('errno' => 12);
        }
		//开放个人报名时,最大人数必须大于0
		elseif($bind['SingleUser'] == 1 && $bind['SingleUserLimit']<=0)
		{
			$response = array('errno' => 13);
		}
		//开放团队报名时,最大队伍数量必须大于0
		elseif($bind['TeamUser'] == 1 && $bind['TeamLimit']<=0)
		{
			$response = array('errno' => 14);
		}
		//开放团队报名时,队伍人数限制(最小人数必须大于0,最大人数必须大于最小人数)
		elseif($bind['TeamUser'] == 1 && ($bind['TeamUserMin']<=0 || $bind['TeamUserMin'] > $bind['TeamUserMax']))
		{
			$response = array('errno' => 15);
		}
		//未选择比赛类型
		elseif($bind['RaceTypeId'] <=0)
		{
			$response = array('errno' => 16);
		}
		else
		{
			//比赛-分组的层级规则
			$RaceStructureList  = $this->oRace->getRaceStructure();
			//获取分站信息
			$RaceStageInfo = $this->oRace->getRaceStage($bind['RaceStageId'],"RaceStageId,comment");
			//数据解包
			$RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
			//如果没有配置比赛结构 或者 比赛结构配置不在配置列表中
			if(!isset($RaceStageInfo['comment']['RaceStructure']) || !isset($RaceStructureList[$RaceStageInfo['comment']['RaceStructure']]))
			{
				//默认为分组优先
				$RaceStageInfo['comment']['RaceStructure'] = "group";
			}
			if(($RaceStageInfo['comment']['RaceStructure']=="group") && (intval($bind['RaceGroupId'])<=0))
			{
				$response = array('errno' => 3);
			}
			elseif(($RaceStageInfo['comment']['RaceStructure']=="race") && (count($bind['SelectedRaceGroup'])==0))
			{
				$response = array('errno' => 17);
			}
			else
			{
				//价格对应列表
				$bind['PriceList'] = $this->oRace->getPriceList(trim($bind['PriceList']),1);
				//将人数限制分别置入压缩数组,并删除原数据
				$bind['comment']['SingleUserLimit'] = $bind['SingleUserLimit'];
				unset($bind['SingleUserLimit']);
				$bind['comment']['TeamLimit'] = $bind['TeamLimit'];
				unset($bind['TeamLimit']);
				$bind['comment']['TeamUserMin'] = $bind['TeamUserMin'];
				unset($bind['TeamUserMin']);
				$bind['comment']['TeamUserMax'] = $bind['TeamUserMax'];
				unset($bind['TeamUserMax']);
                $bind['comment']['SexUser'] = $bind['SexUser'];
                unset($bind['SexUser']);
				$bind['comment']['RaceStartMicro'] = intval(abs($bind['RaceStartMicro']));
				$bind['comment']['RaceStartMicro'] = min(999,$bind['comment']['RaceStartMicro']);
				unset($bind['RaceStartMicro']);
                $bind['comment']['ResultNeedConfirm'] = $bind['ResultNeedConfirm'];
                unset($bind['ResultNeedConfirm']);
                //保存mylaps计时数据库名
                $bind['RouteInfo']['TimeDB'] = $bind['TimeDB'];
                unset($bind['TimeDB']);
				//保存mylaps计时数据表的前缀
				$bind['RouteInfo']['TimePrefix'] = $bind['TimePrefix'];
				unset($bind['TimePrefix']);
				//保存单个计时点的忍耐时间（在该时间范围内的将被忽略）
				$bind['RouteInfo']['TolaranceTime'] = abs(intval($bind['TolaranceTime']));
				unset($bind['TolaranceTime']);
				//成绩计算数据源
                $bind['TimingType'] = trim($bind['RaceTimingType']);
				unset($bind['RaceTimingType']);
				//成绩计算方式
                $bind['RouteInfo']['RaceTimingResultType'] = trim($bind['RaceTimingResultType']);
                unset($bind['RaceTimingResultType']);
                //终点成绩计算方式
                $bind['RouteInfo']['FinalResultType'] = trim($bind['FinalResultType']);
                unset($bind['FinalResultType']);
                //团队成绩计算方式
                $bind['comment']['TeamResultRankType'] = trim($bind['TeamResultRankType']);
                unset($bind['TeamResultRankType']);
                if($bind['RouteInfo']['FinalResultType']=='credit')
                {
                    if(isset($bind['CreditList'][0]))
                    {
                        foreach($bind['CreditList'] as $CreditId => $value)
                        {
                            if($CreditId!=0)
                            {
                                unset($bind['CreditList'][$CreditId]);
                            }
                        }
                    }
                    $bind['RouteInfo']['ResultCreditList'] = $bind['CreditList'];
                }
                else
                {
                    unset($bind['RouteInfo']['ResultCreditList']);
                }
                unset($bind['CreditList']);
                //循环选定的分组
                foreach($bind['SelectedRaceGroup'] as $Group => $GroupInfo)
                {
                    //删除未选定的元素
                    if(!isset($GroupInfo['Selected']))
                    {
                        unset($bind['SelectedRaceGroup'][$Group]);
                    }
                    else
                    {
                        $bind['SelectedRaceGroup'][$Group]['CreditRatio'] = abs($GroupInfo['CreditRatio']);
                        //获取最早的开始时间作为比赛开始时间
                        $bind['StartTime'] = date("Y-m-d H:i:s",(strtotime($bind['StartTime'])>0?min(strtotime($bind['StartTime']),strtotime($GroupInfo['StartTime'])):strtotime($GroupInfo['StartTime'])));
                        //获取最晚的结束时间作为比赛结束时间
                        $bind['EndTime'] = date("Y-m-d H:i:s",(strtotime($bind['EndTime'])>0?max(strtotime($bind['EndTime']),strtotime($GroupInfo['EndTime'])):strtotime($GroupInfo['EndTime'])));
                        //获取毫秒时间
                        $bind['comment']['RaceStartMicro'] = ($bind['StartTime']==$GroupInfo['StartTime'])?$GroupInfo['RaceStartMicro']:$bind['comment']['RaceStartMicro'];
                        //结束报名时间不能晚于比赛开始时间
                        if ($ApplyEndTime>=(strtotime($GroupInfo['StartTime'])+$GroupInfo['RaceStartMicro']/1000))
                        {
                            $response = array('errno' => 12);break;
                        }
                    }
                }
                $bind['comment']['SelectedRaceGroup'] = $bind['SelectedRaceGroup'];
                unset($bind['SelectedRaceGroup']);
				//是否包含起点
				$bind['comment']['NoStart'] = $bind['NoStart'];
				unset($bind['NoStart']);
				//团队成绩计算名次
				$bind['comment']['TeamResultRank'] = $bind['TeamResultRank'];
				unset($bind['TeamResultRank']);
                //团队成绩/个人成绩
                $bind['comment']['ResultType'] = $bind['ResultType'];
                unset($bind['ResultType']);
                //每分钟处理次数
                $bind['comment']['ProcessRate'] = $bind['ProcessRate'];
                unset($bind['ProcessRate']);
                //数据打包
				$bind['comment'] = json_encode($bind['comment']);
				//地图数据打包
				$bind['RouteInfo'] = json_encode($bind['RouteInfo']);
                if(!isset($response))
                {
                    //新增比赛
                    $AddRace = $this->oRace->addRace($bind);
                    $response = $AddRace ? array('errno' => 0) : array('errno' => 9);
                }
			}
		}
		echo json_encode($response);
		return true;
	}
	//修改比赛
	public function raceUpdateAction()
	{
		//获取 页面参数
		$bind=$this->request->from('RaceStageId','RaceName','RaceGroupId','PriceList','ApplyStartTime','ApplyEndTime','StartTime','EndTime','SingleUser','TeamUser','SingleUserLimit','TeamLimit','TeamUserMin','TeamUserMax','SexUser','RaceTypeId','RaceComment','MustSelect','SingleSelect','TimeDB','TimePrefix','RaceTimingType','RaceTimingResultType','FinalResultType','RaceStartMicro','SelectedRaceGroup','NoStart','TeamResultRank','ResultType','ResultNeedConfirm','CreditList','ProcessRate','TeamResultRankType');
        //转化时间为时间戳
		$ApplyStartTime = strtotime(trim($bind['ApplyStartTime']));
		$ApplyEndTime = strtotime(trim($bind['ApplyEndTime']));
		$StartTime = strtotime(trim($bind['StartTime']));
		$EndTime = strtotime(trim($bind['EndTime']));
		//比赛ID
		$RaceId = intval($this->request->RaceId);
		//比赛名称不能为空
		if(trim($bind['RaceName'])=="")
		{
			$response = array('errno' => 1);
		}
		//分站ID必须大于0
		elseif(intval($bind['RaceStageId'])<=0)
		{
			$response = array('errno' => 2);
		}
		//单人报名和团队报名至少要选择一个
		elseif((intval($bind['SingleUser'])+intval($bind['TeamUser'])) == 0)
		{
			$response = array('errno' => 7);
		}
		//比赛ID必须大于0
		elseif($RaceId<=0)
		{
			$response = array('errno' => 8);
		}
		//结束时间不能早于开始时间
		elseif($EndTime<$StartTime)
		{
			$response = array('errno' => 10);
		}
		//结束报名时间不能早于开始报名时间
		elseif ((count($bind['SelectedRaceGroup'])==0) && ($ApplyEndTime<=$ApplyStartTime))
		{
			$response = array('errno' => 11);
		}
		//结束报名时间不能晚于比赛开始时间
		elseif ((count($bind['SelectedRaceGroup'])==0) && ($ApplyEndTime>=$StartTime))
		{
			$response = array('errno' => 12);
		}
		//开放个人报名时,最大人数必须大于0
		elseif($bind['SingleUser'] == 1 && $bind['SingleUserLimit']<=0)
		{
			$response = array('errno' => 13);
		}
		//开放团队报名时,最大队伍数量必须大于0
		elseif($bind['TeamUser'] == 1 && $bind['TeamLimit']<=0)
		{
			$response = array('errno' => 14);
		}
		//开放团队报名时,队伍人数限制(最小人数必须大于0,最大人数必须大于最小人数)
		elseif($bind['TeamUser'] == 1 && ($bind['TeamUserMin']<=0 || $bind['TeamUserMin'] > $bind['TeamUserMax']))
		{
			$response = array('errno' => 15);
		}
		//未选择比赛类型
		elseif($bind['RaceTypeId'] <=0)
		{
			$response = array('errno' => 16);
		}
		else
		{
			//比赛-分组的层级规则
			$RaceStructureList  = $this->oRace->getRaceStructure();
			//获取分站信息
			$RaceStageInfo = $this->oRace->getRaceStage($bind['RaceStageId'],"RaceStageId,comment");
			//数据解包
			$RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
			//如果没有配置比赛结构 或者 比赛结构配置不在配置列表中
			if(!isset($RaceStageInfo['comment']['RaceStructure']) || !isset($RaceStructureList[$RaceStageInfo['comment']['RaceStructure']]))
			{
				//默认为分组优先
				$RaceStageInfo['comment']['RaceStructure'] = "group";
			}
			if(($RaceStageInfo['comment']['RaceStructure']=="group") && (intval($bind['RaceGroupId'])<=0))
			{
				$response = array('errno' => 3);
			}
			elseif(($RaceStageInfo['comment']['RaceStructure']=="race") && (count($bind['SelectedRaceGroup'])==0))
			{
				$response = array('errno' => 17);
			}
			else
			{
				//获取比赛信息
				$RaceInfo = $this->oRace->getRace($RaceId);
				//解包数组
				$bind['comment'] = json_decode($RaceInfo['comment'],true);
				//解包地图数组
				$bind['RouteInfo'] = json_decode($RaceInfo['RouteInfo'],true);
				//价格对应列表
				$bind['PriceList'] = $this->oRace->getPriceList(trim($bind['PriceList']),1);
				//将人数限制分别置入压缩数组,并删除原数据
				$bind['comment']['SingleUserLimit'] = $bind['SingleUserLimit'];
				unset($bind['SingleUserLimit']);
				$bind['comment']['TeamLimit'] = $bind['TeamLimit'];
				unset($bind['TeamLimit']);
				$bind['comment']['TeamUserMin'] = $bind['TeamUserMin'];
				unset($bind['TeamUserMin']);
				$bind['comment']['TeamUserMax'] = $bind['TeamUserMax'];
				unset($bind['TeamUserMax']);
                $bind['comment']['SexUser'] = $bind['SexUser'];
                unset($bind['SexUser']);
				$bind['comment']['RaceStartMicro'] = intval(abs($bind['RaceStartMicro']));
				$bind['comment']['RaceStartMicro'] = min(999,$bind['comment']['RaceStartMicro']);
				unset($bind['RaceStartMicro']);
                $bind['comment']['ResultNeedConfirm'] = $bind['ResultNeedConfirm'];
                unset($bind['ResultNeedConfirm']);
                //保存mylaps计时数据库名
                $bind['RouteInfo']['TimeDB'] = $bind['TimeDB'];
                unset($bind['TimeDB']);
				//保存mylaps计时数据表的前缀
				$bind['RouteInfo']['TimePrefix'] = $bind['TimePrefix'];
				unset($bind['TimePrefix']);
				//保存单个计时点的忍耐时间（在该时间范围内的将被忽略）
				$bind['RouteInfo']['TolaranceTime'] = abs(intval($bind['TolaranceTime']));
				unset($bind['TolaranceTime']);
				//成绩计算数据源
				$bind['TimingType'] = trim($bind['RaceTimingType']);
				unset($bind['RaceTimingType']);
				//成绩计算方式
				$bind['RouteInfo']['RaceTimingResultType'] = trim($bind['RaceTimingResultType']);
				unset($bind['RaceTimingResultType']);
                //终点成绩计算方式
                $bind['RouteInfo']['FinalResultType'] = trim($bind['FinalResultType']);
                unset($bind['FinalResultType']);
                //团队成绩计算方式
                $bind['comment']['TeamResultRankType'] = trim($bind['TeamResultRankType']);
                unset($bind['TeamResultRankType']);
                if($bind['RouteInfo']['FinalResultType']=='credit')
                {
                    if(isset($bind['CreditList'][0]))
                    {
                        foreach($bind['CreditList'] as $CreditId => $value)
                        {
                            if($CreditId!=0)
                            {
                                unset($bind['CreditList'][$CreditId]);
                            }
                        }
                    }
                    $bind['RouteInfo']['ResultCreditList'] = $bind['CreditList'];
                }
                else
                {
                    unset($bind['RouteInfo']['ResultCreditList']);
                }
                unset($bind['CreditList']);
                //循环选定的分组
                foreach($bind['SelectedRaceGroup'] as $Group => $GroupInfo)
                {
                    //删除未选定的元素
                    if(!isset($GroupInfo['Selected']))
                    {
                        unset($bind['SelectedRaceGroup'][$Group]);
                    }
                    else
                    {
                        //如果号码段任意一个为0，则全部置为0
                        if($GroupInfo['BibStart']*$GroupInfo['BibEnd']==0)
                        {
                            $GroupInfo['BibStart'] = 0;
                            $GroupInfo['BibEnd'] = 0;
                        }
                        else
                        {
                            //取正整数保存
                            $bind['SelectedRaceGroup'][$Group]['BibStart'] = intval(abs($GroupInfo['BibStart']));
                            $bind['SelectedRaceGroup'][$Group]['BibEnd'] = intval(abs($GroupInfo['BibEnd']));
                        }
                        //积分比例
                        $bind['SelectedRaceGroup'][$Group]['CreditRatio'] = abs($GroupInfo['CreditRatio']);
                        //获取最早的开始时间作为比赛开始时间
                        $bind['StartTime'] = date("Y-m-d H:i:s",(strtotime($bind['StartTime'])>0?min(strtotime($bind['StartTime']),strtotime($GroupInfo['StartTime'])):strtotime($GroupInfo['StartTime'])));
                        //获取最晚的结束时间作为比赛结束时间
                        $bind['EndTime'] = date("Y-m-d H:i:s",(strtotime($bind['EndTime'])>0?max(strtotime($bind['EndTime']),strtotime($GroupInfo['EndTime'])):strtotime($GroupInfo['EndTime'])));
                        //获取毫秒时间
                        $bind['comment']['RaceStartMicro'] = ($bind['StartTime']==$GroupInfo['StartTime'])?$GroupInfo['RaceStartMicro']:$bind['comment']['RaceStartMicro'];
                        //结束报名时间不能晚于比赛开始时间
                        if ($ApplyEndTime>=(strtotime($GroupInfo['StartTime'])+$GroupInfo['RaceStartMicro']/1000))
                        {
                            $response = array('errno' => 12);break;
                        }
                    }
                }
				$bind['comment']['SelectedRaceGroup'] = $bind['SelectedRaceGroup'];
				unset($bind['SelectedRaceGroup']);
				//是否包含起点
				$bind['comment']['NoStart'] = $bind['NoStart'];
				unset($bind['NoStart']);
				//团队成绩计算名次
				$bind['comment']['TeamResultRank'] = $bind['TeamResultRank'];
				unset($bind['TeamResultRank']);
                //团队成绩/个人成绩
                $bind['comment']['ResultType'] = $bind['ResultType'];
                unset($bind['ResultType']);
                //每分钟处理次数
                $bind['comment']['ProcessRate'] = $bind['ProcessRate'];
                unset($bind['ProcessRate']);
				//数据打包
				$bind['comment'] = json_encode($bind['comment']);
				//地图数据打包
				$bind['RouteInfo'] = json_encode($bind['RouteInfo']);
                if(!isset($response))
                {
                    //更新比赛
                    $UpdateRace = $this->oRace->updateRace($RaceId,$bind);
                    $response = $UpdateRace ? array('errno' => 0) : array('errno' => 9);
                }
			}
		}
		echo json_encode($response);
		return true;
	}
    //复制比赛
    public function raceCopyAction()
    {
        //获取 页面参数
        $bind=$this->request->from('RaceName');
        //比赛ID
        $RaceId = intval($this->request->RaceId);
        //比赛名称不能为空
        if(trim($bind['RaceName'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId);
            $RaceInfo['RaceName'] = $bind['RaceName'];
            //解包数组
            $RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
            unset($RaceInfo['comment']['DetailList']);
            //数据打包
            $RaceInfo['comment'] = json_encode($RaceInfo['comment']);
            if(!isset($response))
            {
                //删除主键
                unset($RaceInfo['RaceId']);
                //更新比赛
                $CopyRace = $this->oRace->insertRace($RaceInfo);
                $response = $CopyRace ? array('errno' => 0) : array('errno' => 9);
            }

        }
        echo json_encode($response);
        return true;
    }
    //删除比赛
    public function raceDeleteAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceDelete");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId,'RaceId');
            //如果有获取到比赛信息
            if(isset($RaceInfo['RaceId']))
            {
                //删除
                $this->oRace->deleteRaceInfo($RaceId);
            }
            //返回之前页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //删除比赛
    public function raceResultUpdateAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId,'RaceId');
            //如果有获取到比赛信息
            if(isset($RaceInfo['RaceId']))
            {
                $bind = array("ToProcess"=>1);
                $update = $this->oRace->updateRace($RaceId,$bind);
            }
            //返回之前页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
	//比赛详情页面
	public function raceDetailAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//赛事分组ID
			$RaceGroupId = intval($this->request->RaceGroupId);
			//获取比赛信息
			$RaceInfo = $this->oRace->getRace($RaceId,"RaceId,RaceName,RaceStageId,RaceGroupId,comment");
			//如果有获取到比赛信息
			if(isset($RaceInfo['RaceId']))
			{
				//获取当前分站信息
				$RaceStageInfo = $this->oRace->getRaceStage($RaceInfo['RaceStageId'],'RaceStageId,RaceStageName,comment,RaceCatalogId');
                //获取关联赛事下的积分类目列表
                $CreditArr = $this->oCredit->getCreditList($RaceStageInfo['RaceCatalogId']);
                //解包压缩数组
				$RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
				//数据解包
				$RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
				//如果当前分站未配置了当前分组
				if(!in_array($RaceGroupId,$RaceStageInfo['comment']['SelectedRaceGroup']))
				{
					$RaceGroupId = 0;
				}
				//获取赛事分组信息
				$RaceGroupInfo = $this->oRace->getRaceGroup($RaceGroupId,'*');
				//如果赛事分组尚未配置
				if(!$RaceGroupInfo['RaceGroupId'])
				{
					$RaceGroupId = 0;
				}
				//初始运动类型信息列表
				$RaceInfo['comment']['DetailList'] = isset($RaceInfo['comment']['DetailList'])?$RaceInfo['comment']['DetailList']:array();
				$this->oSports = new Xrace_Sports();
				//获取运动类型列表
				$SportTypeList = $this->oSports->getAllSportsTypeList();
                $i = 0;

				//循环运动类型列表
                foreach($RaceInfo['comment']['DetailList'] as $Key => $RaceSportsInfo)
				{
					//如果运动类型已经配置
					if(isset($SportTypeList[$RaceSportsInfo['SportsTypeId']]))
					{
					    //初始化统计信息
						$RaceInfo['comment']['DetailList'][$Key]['Total'] = array('Distance'=>0,'ChipCount'=>0);
						//获取运动类型名称
						$RaceInfo['comment']['DetailList'][$Key]['SportsTypeName'] = $SportTypeList[$RaceSportsInfo['SportsTypeId']]['SportsTypeName'];
						//如果有配置计时点ID 则获取计时点信息
						$RaceInfo['comment']['DetailList'][$Key]['TimingDetailList'] = isset($RaceInfo['comment']['DetailList'][$Key]['TimingId'])?$this->oRace->getTimingDetail($RaceInfo['comment']['DetailList'][$Key]['TimingId']):array();
						//数据解包
						$RaceInfo['comment']['DetailList'][$Key]['TimingDetailList']['comment'] = isset($RaceInfo['comment']['DetailList'][$Key]['TimingDetailList']['comment'])?json_decode($RaceInfo['comment']['DetailList'][$Key]['TimingDetailList']['comment'],true):array();
						//计时点排序
						ksort($RaceInfo['comment']['DetailList'][$Key]['TimingDetailList']['comment']);
						//循环计时点列表
						foreach($RaceInfo['comment']['DetailList'][$Key]['TimingDetailList']['comment'] as $tid => $tinfo)
						{
                            if($tinfo['Round']==1)
                            {
                                $RaceInfo['comment']['DetailList'][$Key]['TimingDetailList']['comment'][$tid]['key'] = sprintf("% 8s",$i + 1);
                            }
                            else
                            {
                                $RaceInfo['comment']['DetailList'][$Key]['TimingDetailList']['comment'][$tid]['key'] = sprintf("% 8s",(($i + 1)."~".($i + $tinfo['Round'])));
                            }
                            $i+=$tinfo['Round'];
						    //累加里程,如果距离为正数
							$RaceInfo['comment']['DetailList'][$Key]['Total']['Distance'] += (($tinfo['ToPrevious']>0)?($tinfo['ToPrevious']):0)*	$tinfo['Round'];
							//累加计时点数量
							$RaceInfo['comment']['DetailList'][$Key]['Total']['ChipCount'] += $tinfo['Round'];
                            //如果包含积分配置
                            if(count($tinfo['CreditList']))
                            {
                                //循环积分配置列表
                                foreach($tinfo['CreditList'] as $CreditId => $CreditInfo)
                                {
                                    //如果在总表中有找到
                                    if(isset($CreditArr[$CreditId]))
                                    {
                                        //保存积分名称
                                        $RaceInfo['comment']['DetailList'][$Key]['TimingDetailList']['comment'][$tid]['CreditList'][$CreditId]['CreditName'] = $CreditArr[$CreditId]['CreditName'];
                                    }
                                    else
                                    {
                                        //删除该积分配置
                                        unset($RaceInfo['comment']['DetailList'][$Key]['TimingDetailList']['comment'][$tid]['CreditList'][$CreditId]);
                                    }
                                }
                            }
						}
					}
					else
					{
						//从列表中删除
						unset($RaceInfo['comment']['DetailList'][$Key]);
					}
				}
				//如果已经配置计时点
				if(count($RaceInfo['comment']['DetailList']))
                {
                    $RaceSegmentList = $this->oRace->getSegmentList(array("RaceId"=>$RaceId));
                    //获取计时成绩计算方式
                    $RaceTimingResultTypeList = $this->oRace->getRaceTimingResultType("");
                }
                //渲染模板
				include $this->tpl('Xrace_Race_RaceDetail');
			}
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加运动类型分段
	public function raceSportsTypeInsertAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
		if($PermissionCheck['return'])
		{
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//运动类型ID
			$SportsTypeId = intval($this->request->SportsTypeId);
			//需要添加的运动类型置于哪个位置之后，默认为开头
			$After = isset($this->request->After)?intval($this->request->After):-1;
			$this->oSports = new Xrace_Sports();
			//获取运动类型信息
			$SportsTypeInfo = $this->oSports->getSportsType($SportsTypeId,'*');
			//如果未获取到有效的运动类型
			if(!isset($SportsTypeInfo['SportsTypeId']))
			{
				$response = array('errno' => 3);
			}
			else
			{
				//获取比赛信息
				$RaceInfo = $this->oRace->getRace($RaceId);
				//如果有获取到比赛信息 并且 赛事分站ID和赛事分组ID相符
				if(isset($RaceInfo['RaceId']))
				{
					//数据解包
					$RaceInfo['comment'] = isset($RaceInfo['comment'])?json_decode($RaceInfo['comment'],true):array();
					//初始运动类型信息列表
					$RaceInfo['comment']['DetailList'] = isset($RaceInfo['comment']['DetailList'])?$RaceInfo['comment']['DetailList']:array();
					//运动类型列表排序
					ksort($RaceInfo['comment']['DetailList']);
					//如果添加在某个元素之后 且 元素下标不越界
					if($After>=0 && $After <= count($RaceInfo['comment']['DetailList']))
					{
						//添加元素
						$RaceInfo['comment']['DetailList'] = Base_Common::array_insert($RaceInfo['comment']['DetailList'],array('SportsTypeId' => $SportsTypeId),$After+1);
					}
					//如果在头部添加
					elseif($After == -1)
					{
						//添加元素
						$RaceInfo['comment']['DetailList'] = Base_Common::array_insert($RaceInfo['comment']['DetailList'],array('SportsTypeId' => $SportsTypeId),$After+1);
					}
					else
					{
						//默认为在表尾部添加元素
						$RaceInfo['comment']['DetailList'][count($RaceInfo['comment']['DetailList'])] = array('SportsTypeId' => $SportsTypeId);
					}
					//数据打包
					$RaceInfo['comment'] = json_encode($RaceInfo['comment']);
					//更新比赛
					$res = $this->oRace->updateRace($RaceId,$RaceInfo);
					$response = $res ? array('errno' => 0) : array('errno' => 9);
				}
			}
			echo json_encode($response);
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加动类型提交页面
	public function raceSportsTypeAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//需要添加的运动类型置于哪个位置之后，默认为开头
			$After = isset($this->request->After)?intval($this->request->After):-1;
			$this->oSports = new Xrace_Sports();
			//获取运动类型列表
			$SportsTypeList = $this->oSports->getAllSportsTypeList('SportsTypeId,SportsTypeName');
			//获取比赛信息
			$RaceInfo = $this->oRace->getRace($RaceId);
			//如果有获取到比赛信息 并且 赛事分站ID和赛事分组ID相符
			if(isset($RaceInfo['RaceId']))
			{
				//数据解包
				$RaceInfo['comment'] = isset($RaceInfo['comment'])?json_decode($RaceInfo['comment'],true):array();
				//初始运动类型信息列表
				$RaceInfo['comment']['DetailList'] = isset($RaceInfo['comment']['DetailList'])?$RaceInfo['comment']['DetailList']:array();
				//运动类型列表排序
				ksort($RaceInfo['comment']['DetailList']);
				//循环运动类型列表
				foreach($RaceInfo['comment']['DetailList'] as $Key => $SportsTypeInfo)
				{
					//获取运动类型名称
					$RaceInfo['comment']['DetailList'][$Key]['SportsTypeName'] = $SportsTypeList[$SportsTypeInfo['SportsTypeId']]['SportsTypeName'];
				}
				//如果位置为负数
				if($After<0)
				{
					$After = -1;
				}
				//如果添加在某个元素之后 且 元素下标不越界
				elseif( $After >= count($RaceInfo['comment']['DetailList']))
				{
					$After = count($RaceInfo['comment']['DetailList'])-1;
				}
				//渲染模板
				include $this->tpl('Xrace_Race_RaceSportsTypeAdd');
			}
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//删除动类型提交
	public function raceSportsTypeDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
		if($PermissionCheck['return'])
		{
			//分站ID
			$RaceStageId = intval($this->request->RaceStageId);
			//分组ID
			$RaceGroupId = intval($this->request->RaceGroupId);
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//运动类型ID
			$SportsTypeId = intval($this->request->SportsTypeId);
			//获取当前分站信息
			$RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,'*');
			//解包压缩数组
			$RaceStageInfo ['comment'] = json_decode($RaceStageInfo['comment'],true);
			//获取比赛信息
			$RaceInfo = $this->oRace->getRace($RaceId);
			//如果有获取到比赛信息 并且 赛事分站ID和赛事分组ID相符//if(isset($RaceInfo['RaceId']) && ($RaceStageId == $RaceInfo['RaceStageId']) && ($RaceGroupId == $RaceInfo['RaceGroupId']))
            if(isset($RaceInfo['RaceId']) && ($RaceStageId == $RaceInfo['RaceStageId']))
            {
			    //数据解包
				$RaceInfo['comment'] = isset($RaceInfo['comment'])?json_decode($RaceInfo['comment'],true):array();
				//初始运动类型信息列表
				$RaceInfo['comment']['DetailList'] = isset($RaceInfo['comment']['DetailList'])?$RaceInfo['comment']['DetailList']:array();
				//运动类型列表排序
				ksort($RaceInfo['comment']['DetailList']);
				//已删除标签为0
				$deleted = 0;
				//循环运动类型列表
				foreach($RaceInfo['comment']['DetailList'] as $Key => $SportsTypeInfo)
				{
					//如果匹配到需要删除的数据
					if($Key == $SportsTypeId)
					{
						//删除数据
						unset($RaceInfo['comment']['DetailList'][$Key]);
						//已删除标签为1
						$deleted = 1;
					}
					//如果已删除，且有后续数据
					if($deleted == 1 && isset($RaceInfo['comment']['DetailList'][$Key+1]))
					{
						//后续数据复制到前一位
						$RaceInfo['comment']['DetailList'][($Key)] = $RaceInfo['comment']['DetailList'][$Key+1];
						//删除后续数据
						unset($RaceInfo['comment']['DetailList'][$Key+1]);
					}
				}
				//数据打包
				$RaceInfo['comment'] = json_encode($RaceInfo['comment']);
				//更新比赛
				$res = $this->oRace->updateRace($RaceId,$RaceInfo);
			}
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加比赛计时点
	public function timingPointInsertAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
		if($PermissionCheck['return'])
		{
			//分站ID
			$RaceStageId = intval($this->request->RaceStageId);
			//分组ID
			$RaceGroupId = intval($this->request->RaceGroupId);
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//运动类型ID
			$SportsTypeId = intval($this->request->SportsTypeId);
			//需要添加的运动类型置于哪个位置之后，默认为开头
			$After = isset($this->request->After)?intval($this->request->After):-1;
			//获取 页面参数
			$bind = $this->request->from('TName','ToPrevious','BaiduMapX','BaiduMapY','TencentX','TencentY','Round','ChipId','TolaranceTime');
			//添加计时点
			$AddTimingPoint = $this->oRace->addTimingPoint($RaceId,$SportsTypeId,$After,$bind);
			$response = $AddTimingPoint ? array('errno' => 0) : array('errno' => $AddTimingPoint);
			echo json_encode($response);
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加比赛计时点提交页面
	public function timingPointAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
		if($PermissionCheck['return'])
		{
			//比赛ID
			$RaceId = intval($this->request->RaceId);
            //运动类型ID
			$SportsTypeId = intval($this->request->SportsTypeId);
			//需要添加的运动类型置于哪个位置之后，默认为开头
			$After = isset($this->request->After)?intval($this->request->After):-1;
			$this->oSports = new Xrace_Sports();
			//获取运动类型列表
			$SportsTypeList = $this->oSports->getAllSportsTypeList('SportsTypeId,SportsTypeName');
            //获取比赛信息
			$RaceInfo = $this->oRace->getRace($RaceId);
            //如果有获取到比赛信息 并且 赛事分站ID和赛事分组ID相符
			if(isset($RaceInfo['RaceId']))
			{
			    //数据解包
				$RaceInfo['comment'] = isset($RaceInfo['comment'])?json_decode($RaceInfo['comment'],true):array();
				//获取运动类型信息
				$SportsTypeInfo = $RaceInfo['comment']['DetailList'][$SportsTypeId];
				//获取运动类型名称
				$SportsTypeInfo['SportsTypeName'] = $SportsTypeList[$SportsTypeInfo['SportsTypeId']]['SportsTypeName'];
				//初始化计时点列表
				$SportsTypeInfo['TimingDetailList'] = isset($SportsTypeInfo['TimingId'])?$this->oRace->getTimingDetail($SportsTypeInfo['TimingId']):array();
				//解包数据
				$SportsTypeInfo['TimingDetailList']['comment'] = isset($SportsTypeInfo['TimingDetailList']['comment'])?json_decode($SportsTypeInfo['TimingDetailList']['comment'],true):array();
				//计时点信息排序
				ksort($SportsTypeInfo['TimingDetailList']['comment']);
				//如果计时点位置为负数
				if($After<0)
				{
					$After = -1;
				}
				//如果添加在某个元素之后 且 元素下标不越界
				elseif( $After >= count($SportsTypeInfo['TimingDetailList']['comment']))
				{
					$After = count($SportsTypeInfo['TimingDetailList']['comment'])-1;
				}
				//渲染模板
				include $this->tpl('Xrace_Race_TimingPointAdd');
			}
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//修改计时点数据提交页面
	public function timingPointModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
		if($PermissionCheck['return'])
		{
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//运动类型ID
			$SportsTypeId = intval($this->request->SportsTypeId);
			//计时点ID
			$TimingId = isset($this->request->TimingId)?intval($this->request->TimingId):0;
			$this->oSports = new Xrace_Sports();
			//获取运动类型列表
			$SportsTypeList = $this->oSports->getAllSportsTypeList('SportsTypeId,SportsTypeName');
			//获取比赛信息
			$RaceInfo = $this->oRace->getRace($RaceId);
			//如果有获取到比赛信息 并且 赛事分站ID和赛事分组ID相符
			if(isset($RaceInfo['RaceId']))
			{
				//数据解包
				$RaceInfo['comment'] = isset($RaceInfo['comment'])?json_decode($RaceInfo['comment'],true):array();
				//获取运动类型信息
				$SportsTypeInfo = $RaceInfo['comment']['DetailList'][$SportsTypeId];
				//获取运动类型名称
				$SportsTypeInfo['SportsTypeName'] = $SportsTypeList[$SportsTypeInfo['SportsTypeId']]['SportsTypeName'];
				//初始化计时点列表
				$SportsTypeInfo['TimingDetailList'] = isset($SportsTypeInfo['TimingId'])?$this->oRace->getTimingDetail($SportsTypeInfo['TimingId']):array();
				//解包数据
				$SportsTypeInfo['TimingDetailList']['comment'] = isset($SportsTypeInfo['TimingDetailList']['comment'])?json_decode($SportsTypeInfo['TimingDetailList']['comment'],true):array();
				//获取计时点信息
				$TimingInfo = $SportsTypeInfo['TimingDetailList']['comment'][$TimingId];
				//渲染模板
				include $this->tpl('Xrace_Race_TimingPointModify');
			}
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//修改计时点数据
	public function timingPointUpdateAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
		if($PermissionCheck['return'])
		{
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//运动类型ID
			$SportsTypeId = intval($this->request->SportsTypeId);
			//计时点ID
			$TimingId = isset($this->request->TimingId)?intval($this->request->TimingId):0;
			//获取 页面参数
			$bind = $this->request->from('TName','ToPrevious','BaiduMapX','BaiduMapY','TencentX','TencentY','Round','ChipId','TolaranceTime');
			//更新计时点
			$UpdateTimingPoint = $this->oRace->updateTimingPoint($RaceId,$SportsTypeId,$TimingId,$bind);
			$response = $UpdateTimingPoint ? array('errno' => 0) : array('errno' => 9);
			echo json_encode($response);
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//删除计时点数据
	public function timingPointDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
		if($PermissionCheck['return'])
		{
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//运动类型ID
			$SportsTypeId = intval($this->request->SportsTypeId);
			//计时点ID
			$TimingId = isset($this->request->TimingId)?intval($this->request->TimingId):0;
			//删除计时点
			$DeleteTimingPoint = $this->oRace->deleteTimingPoint($RaceId,$SportsTypeId,$TimingId);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //添加计时点积分数据提交页面
    public function timingPointCreditAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //运动类型ID
            $SportsTypeId = intval($this->request->SportsTypeId);
            //计时点ID
            $TimingId = isset($this->request->TimingId)?intval($this->request->TimingId):0;
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId,"RaceId,RaceStageId");
            //如果有获取到比赛信息 并且 赛事分站ID和赛事分组ID相符
            if(isset($RaceInfo['RaceId']))
            {
                $RaceStageInfo = $this->oRace->getRaceStage($RaceInfo['RaceStageId'],"RaceCatalogId,RaceStageId");
                //获取关联赛事下的积分类目列表
                $CreditArr = $this->oCredit->getCreditList($RaceStageInfo['RaceCatalogId']);
                //渲染模板
                include $this->tpl('Xrace_Race_TimingPointCreditAdd');
            }
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加计时点积分配置数据
    public function timingPointCreditInsertAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //运动类型ID
            $SportsTypeId = intval($this->request->SportsTypeId);
            //计时点ID
            $TimingId = isset($this->request->TimingId)?intval($this->request->TimingId):0;
            //获取 页面参数
            $bind = $this->request->from('CreditRule','CreditRoundList','CreditId');
            //更新计时点
            $UpdateTimingPoint = $this->oRace->insertTimingPointCredit($RaceId,$SportsTypeId,$TimingId,$bind);
            $response = $UpdateTimingPoint ? array('errno' => 0) : array('errno' => $UpdateTimingPoint);
            echo json_encode($response);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //更新计时点积分数据提交页面
    public function timingPointCreditModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //运动类型ID
            $SportsTypeId = intval($this->request->SportsTypeId);
            //计时点ID
            $TimingId = isset($this->request->TimingId)?intval($this->request->TimingId):0;
            //积分ID
            $CreditId = isset($this->request->CreditId)?intval($this->request->CreditId):0;
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId,"RaceId,RaceStageId,comment");
            //如果有获取到比赛信息 并且 赛事分站ID和赛事分组ID相符
            if(isset($RaceInfo['RaceId']))
            {
                //数据解包
                $RaceInfo['comment'] = isset($RaceInfo['comment'])?json_decode($RaceInfo['comment'],true):array();
                //获取关联的赛事分站信息
                $RaceStageInfo = $this->oRace->getRaceStageInfo($RaceInfo['RaceStageId'],"RaceCatalogId,RaceStageId");
                //获取关联赛事下的积分类目列表
                $CreditArr = $this->oCredit->getCreditList($RaceStageInfo['RaceCatalogId']);
                //获取运动类型信息
                $SportsTypeInfo = $RaceInfo['comment']['DetailList'][$SportsTypeId];
                //初始化计时点列表
                $SportsTypeInfo['TimingDetailList'] = isset($SportsTypeInfo['TimingId'])?$this->oRace->getTimingDetail($SportsTypeInfo['TimingId']):array();
                //解包数据
                $SportsTypeInfo['TimingDetailList']['comment'] = isset($SportsTypeInfo['TimingDetailList']['comment'])?json_decode($SportsTypeInfo['TimingDetailList']['comment'],true):array();
                //获取计时点信息
                $TimingInfo = $SportsTypeInfo['TimingDetailList']['comment'][$TimingId];
                //获取积分信息
                $CreditInfo = $TimingInfo['CreditList'][$CreditId];
                //渲染模板
                include $this->tpl('Xrace_Race_TimingPointCreditModify');
            }
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //删除计时点积分数据页面
    public function timingPointCreditDeleteAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //运动类型ID
            $SportsTypeId = intval($this->request->SportsTypeId);
            //计时点ID
            $TimingId = isset($this->request->TimingId)?intval($this->request->TimingId):0;
            //积分ID
            $CreditId = isset($this->request->CreditId)?intval($this->request->CreditId):0;
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId,"RaceId");
            //如果有获取到比赛信息 并且 赛事分站ID和赛事分组ID相符
            if(isset($RaceInfo['RaceId']))
            {
                //更新计时点
                $DeleteTimingPoint = $this->oRace->deleteTimingPointCredit($RaceId,$SportsTypeId,$TimingId,$CreditId);
                $this->response->goBack();
            }
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
	//更新分站相关的产品列表信息填写页面
	public function productModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductModify");
		if($PermissionCheck['return'])
		{
			//赛事分站ID
			$RaceStageId  = isset($this->request->RaceStageId)?intval($this->request->RaceStageId):0;
			//获取赛站信息
			$RaceStageInfo = $this->oRace->getRaceStage($RaceStageId);
			//解包赛站数组
			$RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
			//初始化已选定的产品列表
			$SelectedProductList = array();
			//如果有已经选定的产品列表
			if(isset($RaceStageInfo['comment']['SelectedProductList']) && is_array($RaceStageInfo['comment']['SelectedProductList']))
			{
				$SelectedProductList = $RaceStageInfo['comment']['SelectedProductList'];
			}
			//商品类型列表
			$ProductTypeList = $this->oProduct->getProductTypeList($RaceStageInfo['RaceCatalogId'], 'ProductTypeId,ProductTypeName');
			//初始化空的商品列表
			$ProductList = array();
			//获取所有产品的列表
			$ProductList = $this->oProduct->getAllProductList(0, 'ProductTypeId,ProductId,ProductName');
			//根据产品分类循环列表
			foreach($ProductList as $ProductTypeId => $TypeProductList)
			{
				//如果商品分类已存在
				if(isset($ProductTypeList[$ProductTypeId]))
				{
					//产品列表存入
					$ProductTypeList[$ProductTypeId]['ProductList'] = $TypeProductList;
					//循环其下的产品列表
					foreach($ProductTypeList[$ProductTypeId]['ProductList'] as $ProductId => $ProductInfo)
					{
						$ProductSkuList = $this->oProduct->getAllProductSkuList($ProductId);
						foreach($ProductSkuList[$ProductId] as $ProductSkuId => $ProductSkuInfo)
						{
							if(isset($SelectedProductList[$ProductId][$ProductSkuId]))
							{
								$ProductSkuList[$ProductId][$ProductSkuId]['Stock'] = 	$SelectedProductList[$ProductId][$ProductSkuId]['Stock'];
								$ProductSkuList[$ProductId][$ProductSkuId]['ProductPrice'] = 	$SelectedProductList[$ProductId][$ProductSkuId]['ProductPrice'];
								$ProductSkuList[$ProductId][$ProductSkuId]['ProductLimit'] = 	$SelectedProductList[$ProductId][$ProductSkuId]['ProductLimit'];
							}
							else
							{
								$ProductSkuList[$ProductId][$ProductSkuId]['Stock'] = 	0;
								$ProductSkuList[$ProductId][$ProductSkuId]['ProductPrice'] = 	0;
								$ProductSkuList[$ProductId][$ProductSkuId]['ProductLimit'] = 	0;
							}
						}
						$ProductTypeList[$ProductTypeId]['ProductList'][$ProductId]['ProductSkuList'] = $ProductSkuList[$ProductId];
					}
				}
				else
				{
					//删除该数据出错的分类
					unset($ProductList[$ProductTypeId]);
				}
			}
			//渲染模板
			include $this->tpl('Xrace_Race_ProductModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //更新分站相关的产品列表信息
	public function productUpdateAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductModify");
		if($PermissionCheck['return'])
		{
			//赛事ID
			$RaceStageId = intval($this->request->RaceStageId);
			//获取赛站信息
			$RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,"RaceStageId,comment");
			//解包数组
			$bind['comment'] = json_decode($RaceStageInfo['comment'],true);
			if($RaceStageInfo['RaceStageId'])
			{
				//获取已经选定的商品列表
				//$CheckedProduct = $this->request->from('ProductChecked');
				//获取已经选定的商品数据
				$ProductPrice = $this->request->from('ProductPrice');
				//循环已选择的产品列表
				foreach($ProductPrice['ProductPrice'] as $ProductId => $ProductSkuList)
				{

					foreach($ProductSkuList as $ProductSkuId => $ProductSkuInfo)
					{
						if(intval($ProductSkuInfo['Stock'])<=0 && ($ProductSkuInfo['ProductPrice']<=0))
						{
							unset($ProductPrice['ProductPrice'][$ProductId][$ProductSkuId]);
						}
						else
						{
							$ProductPrice['ProductPrice'][$ProductId][$ProductSkuId]['ProductLimit'] = intval($ProductSkuInfo['ProductLimit'])>=3?3:intval($ProductSkuInfo['ProductLimit']);
						}
					}
				}
			}
			//存入数组中
			$bind['comment']['SelectedProductList'] = $ProductPrice['ProductPrice'];
			//数据打包
			$bind['comment'] = json_encode($bind['comment']);
			//更新赛事分站信息
			$UpdateRaceStage = $this->oRace->updateRaceStage($RaceStageId, $bind);
			$response = $UpdateRaceStage ? array('errno' => 0) : array('errno' => 9);
			echo json_encode($response);
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//比赛选手列表 批量更新BIB和计时芯片ID
	public function raceUserListAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
		    $RaceStatus = strlen(trim($this->request->RaceStatus))?trim($this->request->RaceStatus):"all";
            //页面返回模式
		    $ReturnType = intval($this->request->ReturnType);
		    //比赛ID
            $AutoAsign = intval($this->request->AutoAsign);
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//比赛分组
			$RaceGroupId = intval($this->request->RaceGroupId);
            //是否下载
            $Download = intval($this->request->Download);
			//获取比赛信息
			$RaceInfo = $this->oRace->getRace($RaceId);
			$RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
			//格式化分组ID
			$RaceGroupId = (in_array($RaceGroupId,array(0,$RaceInfo['RaceGroupId'])) || in_array($RaceGroupId,$RaceInfo['comment']['SelectedRaceGroup']))?$RaceGroupId:0;
            foreach($RaceInfo['comment']['SelectedRaceGroup'] as $G => $GInfo)
            {
                $RaceInfo['comment']['SelectedRaceGroup'][$G]["RaceGroupInfo"] = $this->oRace->getRaceGroup($G,"RaceGroupId,RaceGroupName");
            }
			$DownloadUrl = "<a href='" . Base_Common::getUrl('', 'xrace/race.stage', 'race.user.list', array('RaceId' => $RaceId, 'RaceGroupId' => $RaceGroupId,'Download'=>1,'RaceStatus'=>$RaceStatus)) . "'> 下载</a>";
			//生成查询条件
			$params = array('RaceId'=>$RaceInfo['RaceId']);
			$oUser = new Xrace_UserInfo();
            $UserApplyStatusList = $oUser->getUserApplyStatusList();
            //获取选手名单
            $params = array('RaceId'=>$RaceInfo['RaceId'],"RaceGroupId"=>$RaceGroupId,"RaceStatus"=>$RaceStatus,"TeamId"=>0,"Cache"=>0);
            $RaceUserList = $oUser->getRaceUserListByRace($params);
			if($AutoAsign==1)
            {
                $RaceUserList = $this->oRace->autoAsignBIB($RaceId,$RaceUserList);
            }
            if($Download==1)
            {
                $oExcel = new Third_Excel();
                $FileName= $RaceInfo['RaceName'];
                $oExcel->download($FileName)->addSheet('详情');
                //循环选手列表
                foreach($RaceUserList['RaceUserList'] as $aid => $ApplyInfo)
                {
                        //生成单行数据
                        $t = array();
                        $t['Name'] = $ApplyInfo['Name'];
                        $t['RaceGroupName'] = $ApplyInfo['RaceGroupName'];
                        $t['TeamName'] = $ApplyInfo['TeamName'];
                        $t['BIB'] = $ApplyInfo['BIB'];
                        $t['ChipId'] = $ApplyInfo['ChipId'];
                        $t['Code'] = 'http://register.xrace.cn/chip/operation/u/s/'.$ApplyInfo['RaceId']."/".$ApplyInfo['RaceUserId'];
                        $oExcel->addRows(array($t));
                }
                $oExcel->closeSheet()->close();
            }
			//渲染模板
			include $this->tpl('Xrace_Race_RaceUserList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//批量更新比赛选手列表
	public function raceUserListUpdateAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//获取BIB号码列表
			$UserList = $this->request->from('UserList');
			$oUser = new Xrace_UserInfo();
			//循环号码牌列表
			foreach($UserList['UserList'] as $Id => $UserInfo)
			{
				//根据报名记录ID获取用户报名信息
				$RaceUserInfo = $oUser->getRaceApplyUserInfo($UserInfo['ApplyId']);
				//复制到待更新数据
				$bind = $RaceUserInfo;
				//数据解包
				$bind['comment'] = json_decode($bind['comment'],true);
				//BIB
				$bind['BIB'] = trim($UserInfo['BIB']);
				//计时芯片ID
				$bind['ChipId'] = trim($UserInfo['ChipId']);
                //分组ID
                $bind['RaceGroupId'] = intval($UserInfo['RaceGroupId']);
				//数据打包
				$bind['comment'] = json_encode($bind['comment']);
				//更新报名记录
				$oUser->updateRaceUserApply($UserInfo['ApplyId'],$bind);
			}
			//返回之前页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//报名记录上传提交页面
	public function raceUserUploadSubmitAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//比赛分组
			$RaceGroupId = intval($this->request->RaceGroupId);
			//获取比赛信息
			$RaceInfo = $this->oRace->getRace($RaceId);
			$RaceGroupId = in_array($RaceGroupId,array(0,$RaceInfo['RaceGroupId']))?$RaceGroupId:0;
			//如果有获取到比赛信息
			if(isset($RaceInfo['RaceId']))
			{
				//渲染模板
				include $this->tpl('Xrace_Race_RaceUserUpload');
			}
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//报名记录上传
	public function raceUserUploadAction()
	{
        $NameErrorUser = array();
	    //检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//比赛分组
			$RaceGroupId = intval($this->request->RaceGroupId);
            //获取比赛信息
			$RaceInfo = $this->oRace->getRace($RaceId);
            $RaceGroupId = in_array($RaceGroupId,array(0,$RaceInfo['RaceGroupId']))?$RaceGroupId:0;
            //如果有获取到比赛信息
			if(isset($RaceInfo['RaceId']))
			{
				//获取当前时间
				$CurrentTime = date("Y-m-d H:i:s",time());
				//获取赛事信息
				$RaceStageInfo = $this->oRace->getRaceStage($RaceInfo['RaceStageId'],"RaceStageId,RaceCatalogId");
				//文件上传
				$oUpload = new Base_Upload('RaceUserList');
				$upload = $oUpload->upload('RaceUserList');
				$res = $upload->resultArr;
				//打开文件
				$handle = fopen($res['1']['path'], 'r');
				$content = '';
				$ApplyCount = 0;
				$oUser = new Xrace_UserInfo();
				$oTeam = new Xrace_Team();
				$SexList = $oUser->getSexList();
				$IdTypeList = $oUser->getAuthIdType();
				//循环到文件结束
				while(!feof($handle))
				{
				    //获取每行信息
					$content= fgets($handle, 8080);
					//以,为分隔符解开
					$t = explode(",",$content);
					if($RaceInfo['RaceGroupId']==0)
                    {
                        if(!isset($RaceGroupList[trim($t[1])]))
                        {
                            $RaceGroupInfo = $this->oRace->getRaceGroupByName(trim($t[1]),$RaceStageInfo['RaceCatalogId'],"RaceGroupId,RaceGroupName");
                            if($RaceGroupInfo['RaceGroupId'])
                            {
                                $RaceGroupList[$RaceGroupInfo['RaceGroupName']] = $RaceGroupInfo;
                            }
                        }
                    }
                    else
                    {
                        $RaceGroupInfo = array("RaceGroupId"=>$RaceInfo['RaceGroupId']);
                    }
                    if(trim($t['8'])=="")
                    {
                        $TeamId = 0;
                    }
                    else
                    {
                        if(isset($TeamList[trim($t['8'])]))
                        {
                            $TeamId = $TeamList[trim($t['8'])];
                        }
                        else
                        {
                            $TeamInfo = $oTeam->getTeamInfoByName(trim($t['8']),"TeamId,TeamName");
                            if(isset($TeamInfo['TeamId']))
                            {
                                $TeamList[trim($t['8'])] = $TeamInfo['TeamId'];
                                $TeamId = $TeamInfo['TeamId'];
                            }
                            else
                            {
                                //获取当前时间
                                $Time = date("Y-m-d H:i:s", time());
                                //生成队伍的数组
                                $bind['TeamName'] = trim($t['8']);
                                $bind['TeamComment'] = $bind['TeamName'];
                                $bind['CreateUserId'] = 0;
                                $bind['CreateTime'] = $Time;
                                $bind['LastUpdateTime'] = $Time;
                                //创建队伍信息
                                $InsertTeam = $oTeam->insertTeam($bind);
                                if($InsertTeam)
                                {
                                    $TeamId = $InsertTeam;
                                    $TeamList[trim($t['8'])] = $InsertTeam;
                                }
                            }
                        }
                    }
                    //根据证件号码获取用户信息
                    $UserInfo = $oUser->getUserByColumn("IdNo",trim($t['6']));
                    //如果找到
                    if(isset($UserInfo['UserId']))
                    {
                        //如果关联比赛用户
                        if($UserInfo['RaceUserId']>0)
                        {
                            $RaceUserInfo = $oUser->getRaceUser($UserInfo['RaceUserId'],"ContactMobile");
                            //初始化新报名记录的信息
                            $ApplyInfo = array("ApplyTime"=>$CurrentTime,"RaceUserId"=>$UserInfo['RaceUserId'],"ApplyRaceUserId"=>$UserInfo['RaceUserId'],"RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceGroupId"=>$RaceGroupInfo['RaceGroupId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceId"=>$RaceInfo['RaceId'],"BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                            //如果存在，则更新部分信息
                            $ApplyUpdateInfo = array("BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                            $Apply = $oUser->insertRaceApplyUserInfo($ApplyInfo,$ApplyUpdateInfo);
                            //如果创建成功
                            if($Apply)
                            {
                                //写入签到记录
                                $oUser->insertUserCheckInInfo(array("RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceUserId"=>$UserInfo['RaceUserId'],"ApplyRaceUserId"=>$UserInfo['RaceUserId'],"Mobile"=>$RaceUserInfo['ContactMobile']));
                                $ApplyCount++;
                            }
                        }
                        else
                        {
                            //根据证件号码获取比赛用户信息
                            $RaceUserInfo = $oUser->getRaceUserByColumn("IdNo",trim($t['6']));
                            //如果已经被占用
                            if(isset($RaceUserInfo['RaceUserId']))
                            {
                                $NewUserInfo = array("RaceUserId"=>$RaceUserInfo['RaceUserId']);
                                $update = $oUser->updateUser($UserInfo['UserId'],$NewUserInfo);
                                if($update)
                                {
                                    //初始化新报名记录的信息
                                    $ApplyInfo = array("ApplyTime"=>$CurrentTime,"RaceUserId"=>$RaceUserInfo['RaceUserId'],"ApplyRaceUserId"=>$RaceUserInfo['RaceUserId'],"RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceGroupId"=>$RaceGroupInfo['RaceGroupId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceId"=>$RaceInfo['RaceId'],"BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                                    //如果存在，则更新部分信息
                                    $ApplyUpdateInfo = array("BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                                    $Apply = $oUser->insertRaceApplyUserInfo($ApplyInfo,$ApplyUpdateInfo);
                                    //echo "apply002:".$Apply."<br>";
                                    //如果创建成功
                                    if($Apply)
                                    {
                                        //写入签到记录
                                        $oUser->insertUserCheckInInfo(array("RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceUserId"=>$RaceUserInfo['RaceUserId'],"ApplyRaceUserId"=>$RaceUserInfo['RaceUserId'],"Mobile"=>$RaceUserInfo['ContactMobile']));
                                        $ApplyCount++;
                                    }
                                }
                                else
                                {
                                    continue;
                                }
                            }
                            else
                            {
                                //根据用户创建比赛用户
                                $RaceUserId = $oUser->createRaceUserByUserInfo($UserInfo['UserId']);
                                //如果创建成功
                                if($RaceUserId)
                                {
                                    $RaceUserInfo = $oUser->getRaceUser($RaceUserId,"ContactMobile");
                                    //初始化新报名记录的信息
                                    $ApplyInfo = array("ApplyTime"=>$CurrentTime,"RaceUserId"=>$RaceUserId,"ApplyRaceUserId"=>$RaceUserId,"RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceGroupId"=>$RaceGroupInfo['RaceGroupId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceId"=>$RaceInfo['RaceId'],"BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                                    //如果存在，则更新部分信息
                                    $ApplyUpdateInfo = array("BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                                    $Apply = $oUser->insertRaceApplyUserInfo($ApplyInfo,$ApplyUpdateInfo);
                                    //echo "apply003:".$Apply."<br>";
                                    //如果创建成功
                                    if($Apply)
                                    {
                                        //写入签到记录
                                        $oUser->insertUserCheckInInfo(array("RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceUserId"=>$RaceUserId,"ApplyRaceUserId"=>$RaceUserId,"Mobile"=>$RaceUserInfo['ContactMobile']));
                                        $ApplyCount++;
                                    }
                                }
                                else
                                {
                                    continue;
                                }
                            }
                        }

                    }
                    else
                    {
                        //根据证件号码获取比赛用户信息
                        $RaceUserInfo = $oUser->getRaceUserByColumn("IdNo",trim($t['6']));
                        //如果已经被占用
                        if(isset($RaceUserInfo['RaceUserId']))
                        {
                            //初始化新报名记录的信息
                            $ApplyInfo = array("ApplyTime"=>$CurrentTime,"RaceUserId"=>$RaceUserInfo['RaceUserId'],"ApplyRaceUserId"=>$RaceUserInfo['RaceUserId'],"RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceGroupId"=>$RaceGroupInfo['RaceGroupId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceId"=>$RaceInfo['RaceId'],"BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                            //如果存在，则更新部分信息
                            $ApplyUpdateInfo = array("BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                            //print_R($ApplyInfo);
                            $Apply = $oUser->insertRaceApplyUserInfo($ApplyInfo,$ApplyUpdateInfo);
                            //echo "apply004:".$Apply."<br>";
                            //如果创建成功
                            if($Apply)
                            {
                                //写入签到记录
                                $oUser->insertUserCheckInInfo(array("RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceUserId"=>$RaceUserInfo['RaceUserId'],"ApplyRaceUserId"=>$RaceUserInfo['RaceUserId'],"Mobile"=>$RaceUserInfo['ContactMobile']));
                                $ApplyCount++;
                            }
                        }
                        else
                        {
                            $Sex = array_search($t[3],$SexList);
                            $IdType = array_search($t[5],$IdTypeList);
                            //生成用户信息
                            $UserInfo = array('CreateUserId'=>0,'Name'=>$t['2'],'Sex'=>$Sex,'Birthday'=>"",'ContactMobile'=>$t['7'],'IdNo'=>trim($t['6']),'IdType'=>$IdType,'Available'=>0,'RegTime'=>date("Y-m-d H:i:s",time()));
                            if($IdType==1)
                            {
                                $UserInfo['Birthday'] = substr(trim($t['6']),6,4)."-".substr(trim($t['6']),10,2)."-".substr(trim($t['6']),12,2);
                                $UserInfo['Sex'] = $Sex==0?$Sex:(intval(substr(trim($t['6']),16,1))%2==0?2:1);
                            }
                            $CreateUser = 1;
                            //创建用户
                            $CreateUser = $oUser->insertRaceUser($UserInfo);
                            //如果创建成功
                            if($CreateUser)
                            {
                                //初始化新报名记录的信息
                                $ApplyInfo = array("ApplyTime"=>$CurrentTime,"RaceUserId"=>$CreateUser,"ApplyRaceUserId"=>$CreateUser,"RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceGroupId"=>$RaceGroupInfo['RaceGroupId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceId"=>$RaceInfo['RaceId'],"BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                                //如果存在，则更新部分信息
                                $ApplyUpdateInfo = array("BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                                $Apply = $oUser->insertRaceApplyUserInfo($ApplyInfo,$ApplyUpdateInfo);
                                //echo "apply005:".$Apply."<br>";
                                //如果创建成功
                                if($Apply)
                                {
                                    //写入签到记录
                                    $oUser->insertUserCheckInInfo(array("RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceUserId"=>$CreateUser,"ApplyRaceUserId"=>$CreateUser,"Mobile"=>$UserInfo['ContactMobile']));
                                    $ApplyCount++;
                                }
                            }
                            else
                            {
                                continue;
                            }
                        }
                    }
                    continue;
				}
			}
			echo json_encode(array('errno' => 0,'ApplyCount'=>$ApplyCount,'NameErrorUserCount'=>count($NameErrorUser),'NameErrorUser'=>count($NameErrorUser)>0?implode(",",$NameErrorUser):"无"));
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //报名记录添加
    public function raceUserInsertAction()
    {
        $NameErrorUser = array();
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //获取 页面参数
            $bind=$this->request->from('RaceId','RaceGroupId','BIB','ChipId','Name','Sex','IdType','IdNo','ContactMobile','TeamName');
            print_r($bind);
            die();
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //比赛分组
            $RaceGroupId = intval($this->request->RaceGroupId);
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId);
            $RaceGroupId = in_array($RaceGroupId,array(0,$RaceInfo['RaceGroupId']))?$RaceGroupId:0;
            //如果有获取到比赛信息
            if(isset($RaceInfo['RaceId']))
            {
                //获取当前时间
                $CurrentTime = date("Y-m-d H:i:s",time());
                //获取赛事信息
                $RaceStageInfo = $this->oRace->getRaceStage($RaceInfo['RaceStageId'],"RaceStageId,RaceCatalogId");
                //文件上传
                $oUpload = new Base_Upload('RaceUserList');
                $upload = $oUpload->upload('RaceUserList');
                $res = $upload->resultArr;
                //打开文件
                $handle = fopen($res['1']['path'], 'r');
                $content = '';
                $ApplyCount = 0;
                $oUser = new Xrace_UserInfo();
                $oTeam = new Xrace_Team();
                $SexList = $oUser->getSexList();
                $IdTypeList = $oUser->getAuthIdType();
                //循环到文件结束
                while(!feof($handle))
                {
                    //获取每行信息
                    $content= fgets($handle, 8080);
                    //以,为分隔符解开
                    $t = explode(",",$content);
                    if($RaceInfo['RaceGroupId']==0)
                    {
                        if(!isset($RaceGroupList[trim($t[1])]))
                        {
                            $RaceGroupInfo = $this->oRace->getRaceGroupByName(trim($t[1]),$RaceStageInfo['RaceCatalogId'],"RaceGroupId,RaceGroupName");
                            if($RaceGroupInfo['RaceGroupId'])
                            {
                                $RaceGroupList[$RaceGroupInfo['RaceGroupName']] = $RaceGroupInfo;
                            }
                        }
                    }
                    else
                    {
                        $RaceGroupInfo = array("RaceGroupId"=>$RaceInfo['RaceGroupId']);
                    }
                    if(trim($t['8'])=="")
                    {
                        $TeamId = 0;
                    }
                    else
                    {
                        if(isset($TeamList[trim($t['8'])]))
                        {
                            $TeamId = $TeamList[trim($t['8'])];
                        }
                        else
                        {
                            $TeamInfo = $oTeam->getTeamInfoByName(trim($t['8']),"TeamId,TeamName");
                            if(isset($TeamInfo['TeamId']))
                            {
                                $TeamList[trim($t['8'])] = $TeamInfo['TeamId'];
                                $TeamId = $TeamInfo['TeamId'];
                            }
                            else
                            {
                                //获取当前时间
                                $Time = date("Y-m-d H:i:s", time());
                                //生成队伍的数组
                                $bind['TeamName'] = trim($t['8']);
                                $bind['TeamComment'] = $bind['TeamName'];
                                $bind['CreateUserId'] = 0;
                                $bind['CreateTime'] = $Time;
                                $bind['LastUpdateTime'] = $Time;
                                //创建队伍信息
                                $InsertTeam = $oTeam->insertTeam($bind);
                                if($InsertTeam)
                                {
                                    $TeamId = $InsertTeam;
                                    $TeamList[trim($t['8'])] = $InsertTeam;
                                }
                            }
                        }
                    }
                    //根据证件号码获取用户信息
                    $UserInfo = $oUser->getUserByColumn("IdNo",trim($t['6']));
                    //如果找到
                    if(isset($UserInfo['UserId']))
                    {
                        //如果关联比赛用户
                        if($UserInfo['RaceUserId']>0)
                        {
                            $RaceUserInfo = $oUser->getRaceUser($UserInfo['RaceUserId'],"ContactMobile");
                            //初始化新报名记录的信息
                            $ApplyInfo = array("ApplyTime"=>$CurrentTime,"RaceUserId"=>$UserInfo['RaceUserId'],"ApplyRaceUserId"=>$UserInfo['RaceUserId'],"RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceGroupId"=>$RaceGroupInfo['RaceGroupId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceId"=>$RaceInfo['RaceId'],"BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                            //如果存在，则更新部分信息
                            $ApplyUpdateInfo = array("BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                            $Apply = $oUser->insertRaceApplyUserInfo($ApplyInfo,$ApplyUpdateInfo);
                            //如果创建成功
                            if($Apply)
                            {
                                //写入签到记录
                                $oUser->insertUserCheckInInfo(array("RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceUserId"=>$UserInfo['RaceUserId'],"ApplyRaceUserId"=>$UserInfo['RaceUserId'],"Mobile"=>$RaceUserInfo['ContactMobile']));
                                $ApplyCount++;
                            }
                        }
                        else
                        {
                            //根据证件号码获取比赛用户信息
                            $RaceUserInfo = $oUser->getRaceUserByColumn("IdNo",trim($t['6']));
                            //如果已经被占用
                            if(isset($RaceUserInfo['RaceUserId']))
                            {
                                $NewUserInfo = array("RaceUserId"=>$RaceUserInfo['RaceUserId']);
                                $update = $oUser->updateUser($UserInfo['UserId'],$NewUserInfo);
                                if($update)
                                {
                                    //初始化新报名记录的信息
                                    $ApplyInfo = array("ApplyTime"=>$CurrentTime,"RaceUserId"=>$RaceUserInfo['RaceUserId'],"ApplyRaceUserId"=>$RaceUserInfo['RaceUserId'],"RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceGroupId"=>$RaceGroupInfo['RaceGroupId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceId"=>$RaceInfo['RaceId'],"BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                                    //如果存在，则更新部分信息
                                    $ApplyUpdateInfo = array("BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                                    $Apply = $oUser->insertRaceApplyUserInfo($ApplyInfo,$ApplyUpdateInfo);
                                    //echo "apply002:".$Apply."<br>";
                                    //如果创建成功
                                    if($Apply)
                                    {
                                        //写入签到记录
                                        $oUser->insertUserCheckInInfo(array("RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceUserId"=>$RaceUserInfo['RaceUserId'],"ApplyRaceUserId"=>$RaceUserInfo['RaceUserId'],"Mobile"=>$RaceUserInfo['ContactMobile']));
                                        $ApplyCount++;
                                    }
                                }
                                else
                                {
                                    continue;
                                }
                            }
                            else
                            {
                                //根据用户创建比赛用户
                                $RaceUserId = $oUser->createRaceUserByUserInfo($UserInfo['UserId']);
                                //如果创建成功
                                if($RaceUserId)
                                {
                                    $RaceUserInfo = $oUser->getRaceUser($RaceUserId,"ContactMobile");
                                    //初始化新报名记录的信息
                                    $ApplyInfo = array("ApplyTime"=>$CurrentTime,"RaceUserId"=>$RaceUserId,"ApplyRaceUserId"=>$RaceUserId,"RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceGroupId"=>$RaceGroupInfo['RaceGroupId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceId"=>$RaceInfo['RaceId'],"BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                                    //如果存在，则更新部分信息
                                    $ApplyUpdateInfo = array("BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                                    $Apply = $oUser->insertRaceApplyUserInfo($ApplyInfo,$ApplyUpdateInfo);
                                    //echo "apply003:".$Apply."<br>";
                                    //如果创建成功
                                    if($Apply)
                                    {
                                        //写入签到记录
                                        $oUser->insertUserCheckInInfo(array("RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceUserId"=>$RaceUserId,"ApplyRaceUserId"=>$RaceUserId,"Mobile"=>$RaceUserInfo['ContactMobile']));
                                        $ApplyCount++;
                                    }
                                }
                                else
                                {
                                    continue;
                                }
                            }
                        }

                    }
                    else
                    {
                        //根据证件号码获取比赛用户信息
                        $RaceUserInfo = $oUser->getRaceUserByColumn("IdNo",trim($t['6']));
                        //如果已经被占用
                        if(isset($RaceUserInfo['RaceUserId']))
                        {
                            //初始化新报名记录的信息
                            $ApplyInfo = array("ApplyTime"=>$CurrentTime,"RaceUserId"=>$RaceUserInfo['RaceUserId'],"ApplyRaceUserId"=>$RaceUserInfo['RaceUserId'],"RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceGroupId"=>$RaceGroupInfo['RaceGroupId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceId"=>$RaceInfo['RaceId'],"BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                            //如果存在，则更新部分信息
                            $ApplyUpdateInfo = array("BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                            //print_R($ApplyInfo);
                            $Apply = $oUser->insertRaceApplyUserInfo($ApplyInfo,$ApplyUpdateInfo);
                            //echo "apply004:".$Apply."<br>";
                            //如果创建成功
                            if($Apply)
                            {
                                //写入签到记录
                                $oUser->insertUserCheckInInfo(array("RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceUserId"=>$RaceUserInfo['RaceUserId'],"ApplyRaceUserId"=>$RaceUserInfo['RaceUserId'],"Mobile"=>$RaceUserInfo['ContactMobile']));
                                $ApplyCount++;
                            }
                        }
                        else
                        {
                            $Sex = array_search($t[3],$SexList);
                            $IdType = array_search($t[5],$IdTypeList);
                            //生成用户信息
                            $UserInfo = array('CreateUserId'=>0,'Name'=>$t['2'],'Sex'=>$Sex,'Birthday'=>"",'ContactMobile'=>$t['7'],'IdNo'=>trim($t['6']),'IdType'=>$IdType,'Available'=>0,'RegTime'=>date("Y-m-d H:i:s",time()));
                            if($IdType==1)
                            {
                                $UserInfo['Birthday'] = substr(trim($t['6']),6,4)."-".substr(trim($t['6']),10,2)."-".substr(trim($t['6']),12,2);
                                $UserInfo['Sex'] = $Sex==0?$Sex:(intval(substr(trim($t['6']),16,1))%2==0?2:1);
                            }
                            $CreateUser = 1;
                            //创建用户
                            $CreateUser = $oUser->insertRaceUser($UserInfo);
                            //如果创建成功
                            if($CreateUser)
                            {
                                //初始化新报名记录的信息
                                $ApplyInfo = array("ApplyTime"=>$CurrentTime,"RaceUserId"=>$CreateUser,"ApplyRaceUserId"=>$CreateUser,"RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceGroupId"=>$RaceGroupInfo['RaceGroupId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceId"=>$RaceInfo['RaceId'],"BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                                //如果存在，则更新部分信息
                                $ApplyUpdateInfo = array("BIB"=>trim($t[0]),"ChipId"=>trim($t[4]),"TeamId"=>$TeamId);
                                $Apply = $oUser->insertRaceApplyUserInfo($ApplyInfo,$ApplyUpdateInfo);
                                //echo "apply005:".$Apply."<br>";
                                //如果创建成功
                                if($Apply)
                                {
                                    //写入签到记录
                                    $oUser->insertUserCheckInInfo(array("RaceCatalogId"=>$RaceStageInfo['RaceCatalogId'],"RaceStageId"=>$RaceInfo['RaceStageId'],"RaceUserId"=>$CreateUser,"ApplyRaceUserId"=>$CreateUser,"Mobile"=>$UserInfo['ContactMobile']));
                                    $ApplyCount++;
                                }
                            }
                            else
                            {
                                continue;
                            }
                        }
                    }
                    continue;
                }
            }
            echo json_encode(array('errno' => 0,'ApplyCount'=>$ApplyCount,'NameErrorUserCount'=>count($NameErrorUser),'NameErrorUser'=>count($NameErrorUser)>0?implode(",",$NameErrorUser):"无"));
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
	//用户退出比赛
	public function userRaceDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
			$oUser = new Xrace_User();
			//报名记录ID
			$ApplyId = intval($this->request->ApplyId);
			//更新数据
			$res = $oUser->deleteUserRace($ApplyId);
			//返回之前页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //用户DNF填写页面
    public function userRaceDnfApplyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $manager_name = $this->manager->name;
            $oUser = new Xrace_UserInfo();
            //报名记录ID
            $ApplyId = intval($this->request->ApplyId);
            //获取报名记录
            $UserRaceApplyInfo = $oUser->getRaceApplyUserInfo($ApplyId,"RaceId,RaceStageId,RaceGroupId");
            //页面渲染
            include $this->tpl('Xrace_Race_RaceUserDNF');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //用户DNF/DNS状态回复
    public function userRaceStatusRestoreAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oUser = new Xrace_UserInfo();

            //报名记录ID
            $ApplyId = intval($this->request->ApplyId);
            //更新数据
            $res = $oUser->UserRaceStatusRestore($ApplyId);
            //返回之前页面
            $this->response->goBack();
            return true;
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //用户DNF
    public function userRaceDnfAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oUser = new Xrace_UserInfo();

            //报名记录ID
            $ApplyId = intval($this->request->ApplyId);
            $Reason = trim($this->request->Reason);
            //更新数据
            $res = $oUser->UserRaceDNF($ApplyId, $Reason, $this->manager->id);
            //返回之前页面
            $response = $res ? array('errno' => 0) : array('errno' => 9);
            echo json_encode($response);
            return true;
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //用户DNS填写页面
    public function userRaceDnsApplyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $manager_name = $this->manager->name;
            $oUser = new Xrace_UserInfo();
            //报名记录ID
            $ApplyId = intval($this->request->ApplyId);
            //获取报名记录
            $UserRaceApplyInfo = $oUser->getRaceApplyUserInfo($ApplyId,"RaceId,RaceStageId,RaceGroupId");
            //页面渲染
            include $this->tpl('Xrace_Race_RaceUserDNS');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //用户DNS
    public function userRaceDnsAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oUser = new Xrace_UserInfo();

            //报名记录ID
            $ApplyId = intval($this->request->ApplyId);
            $Reason = trim($this->request->Reason);
            //更新数据
            $res = $oUser->UserRaceDNS($ApplyId, $Reason, $this->manager->id);
            //返回之前页面
            $response = $res ? array('errno' => 0) : array('errno' => 9);
            echo json_encode($response);
            return true;
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
		//整场比赛用户退出比赛
	public function userRaceDeleteByRaceAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
			$oUser = new Xrace_User();
			//比赛ID
			$RaceId = intval($this->request->RaceId);
			//分组ID
			$RaceGroupId = intval($this->request->RaceGroupId);
			//更新数据
			$res = $oUser->deleteUserRaceByRace($RaceId,$RaceGroupId);
			//返回之前页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//单场比赛的成绩单
	public function raceResultListAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			$oUser = new Xrace_UserInfo();
			//比赛ID
			$RaceId = intval($this->request->RaceId);
            //用户ID
			$RaceUserId = intval($this->request->RaceUserId);
            //是否下载
            $Download = intval($this->request->Download);
            //获取用户信息
			$UserInfo = $oUser->getRaceUser($RaceUserId,'RaceUserId,Name');
            //获取比赛信息
			$RaceInfo = $this->oRace->getRace($RaceId);
			//数据解包
			$RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
            //数据解包
            $RaceInfo['RouteInfo'] = json_decode($RaceInfo['RouteInfo'],true);
			//获取成绩列表
            if(count($RaceInfo['comment']['SelectedRaceGroup']))
            {
                //分组ID
                if(count($RaceInfo['comment']['SelectedRaceGroup'])>1)
                {
                    $RaceGroupId = intval($this->request->RaceGroupId)>=0?intval($this->request->RaceGroupId):key($RaceInfo['comment']['SelectedRaceGroup']);

                }
                else
                {
                    $RaceGroupId = intval($this->request->RaceGroupId)?intval($this->request->RaceGroupId):key($RaceInfo['comment']['SelectedRaceGroup']);
                }
                //获取成绩列表
                $RaceResultList = $this->oRace->getRaceResult($RaceId,$RaceGroupId);
                //下载链接
                $DownloadUrl = "<a href='" . Base_Common::getUrl('', 'xrace/race.stage', 'race.result.list', array('RaceId' => $RaceId, 'RaceGroupId' => $RaceGroupId,'Download'=>1)) . "'> 下载</a>";
                //指定了用户就不需要分组列表选择
                if(!$UserInfo['RaceUserId'])
                {
                    //循环比赛已经开设的分组列表
                    foreach($RaceInfo['comment']['SelectedRaceGroup'] as $GroupId => $GroupInfo)
                    {
                        $RaceGroupInfo = $this->oRace->getRaceGroup($GroupId,"RaceGroupId,RaceGroupName");
                        if($RaceGroupInfo['RaceGroupId'])
                        {
                            $RaceGroupList[$GroupId]["RaceGroupInfo"] = $RaceGroupInfo;
                            if($RaceGroupId == $RaceGroupInfo['RaceGroupId'])
                            {
                                $RaceGroupList[$GroupId]['DownloadUrl'] = $RaceGroupInfo['RaceGroupName'].$DownloadUrl;
                            }
                            else
                            {
                                $RaceGroupList[$GroupId]['DownloadUrl'] = "<a href='" . Base_Common::getUrl('', 'xrace/race.stage', 'race.result.list', array('RaceId' => $RaceId, 'RaceGroupId' => $GroupId)) . "'>" . $RaceGroupInfo['RaceGroupName'] . "</a>";
                            }
                        }
                    }
                    $DownloadUrl = "<a href='" . Base_Common::getUrl('', 'xrace/race.stage', 'race.result.list', array('RaceId' => $RaceId, 'RaceGroupId' => 0,'Download'=>1)) . "'> 下载</a>";

                    if(count($RaceInfo['comment']['SelectedRaceGroup'])>1)
                    {
                        if($RaceGroupId == 0)
                        {
                            $RaceGroupList[0]['DownloadUrl'] = "全场".$DownloadUrl;
                        }
                        else
                        {
                            $RaceGroupList[0]['DownloadUrl'] = "<a href='" . Base_Common::getUrl('', 'xrace/race.stage', 'race.result.list', array('RaceId' => $RaceId, 'RaceGroupId' => 0)) . "'>" . "全场" . "</a>";
                        }
                        ksort($RaceGroupList);
                    }

                }
            }
            else
            {
                //分组ID
                $RaceGroupId = intval($this->request->RaceGroupId);
                //获取成绩列表
                $RaceResultList = $this->oRace->getRaceResult($RaceId,$RaceGroupId);
            }
            if($Download==1)
            {
                set_time_limit(0);
                $oExcel = new Third_Excel();
                $FileName= $RaceInfo['RaceName']."-".(($RaceGroupId>0)?$RaceGroupList[$RaceGroupId]["RaceGroupInfo"]['RaceGroupName']:"全场");
                $oExcel->download($FileName);
                /*
                $oExcel->addSheet('详情');
                //循环运动类型列表
                foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                {
                    //循环其下的计时点列表
                    foreach($SportsInfo['TimingPointList'] as $tinfo => $tid)
                    {
                        //生成单行数据
                        $t = array();
                        $t['SportsTypeName'] = $SportsInfo['SportsTypeInfo']['SportsTypeName'];
                        $t['PointName'] = $RaceResultList['UserRaceInfo']['Point'][$tid]['TName'];
                        $t['CurrentDistance'] = $RaceResultList['UserRaceInfo']['Point'][$tid]['CurrentDistance'];
                        $t['Name'] = "姓名";

                        //循环选手信息
                        foreach($RaceResultList['UserRaceInfo']['Point'][$tid]['UserList'] as $U => $UserInfo)
                        {
                                $t[] = $UserInfo['Name'];
                        }
                        $oExcel->addRows(array($t));
                        unset($t);
                        //生成单行数据
                        $t = array();
                        $t['1'] = "";
                        $t['2'] = "";
                        $t['3'] = "";
                        $t['TeamName'] = "队伍名";
                        //}
                        //循环选手信息
                        foreach($RaceResultList['UserRaceInfo']['Point'][$tid]['UserList'] as $U => $UserInfo)
                        {
                                $t[] = $UserInfo['TeamName'];
                        }
                        $oExcel->addRows(array($t));
                        unset($t);
                        //生成单行数据
                        $t = array();
                        $t['1'] = "";
                        $t['2'] = "";
                        $t['3'] = "";
                        $t['Name'] = "分组";
                        //循环选手信息
                        foreach($RaceResultList['UserRaceInfo']['Point'][$tid]['UserList'] as $U => $UserInfo)
                        {
                            $t[] = $RaceGroupList[$UserInfo['RaceGroupId']]["RaceGroupInfo"]['RaceGroupName'];
                        }
                        $oExcel->addRows(array($t));
                        //生成单行数据
                        $t = array();
                        $t['1'] = "";
                        $t['2'] = "";
                        $t['3'] = "";
                        $t['Name'] = "BIB";
                        //循环选手信息
                        foreach($RaceResultList['UserRaceInfo']['Point'][$tid]['UserList'] as $U => $UserInfo)
                        {
                            $t[] = $UserInfo['BIB'];
                        }
                        $oExcel->addRows(array($t));
                        unset($t);
                        //生成单行数据
                        $t = array();
                        $t['1'] = "";
                        $t['2'] = "";
                        $t['3'] = "";
                        if($RaceInfo['RouteInfo']['RaceTimingResultType']=="gunshot")
                        {
                            $t['Name'] = "总时间";
                        }
                        else
                        {
                            $t['Name'] = "净时间";
                        }
                        //循环选手信息
                        foreach($RaceResultList['UserRaceInfo']['Point'][$tid]['UserList'] as $U => $UserInfo)
                        {
                            if($RaceInfo['RouteInfo']['RaceTimingResultType']=="gunshot")
                            {
                                $t[] = Base_Common::parthTimeLag($UserInfo['TotalTime']);
                            }
                            else
                            {
                                $t[] = Base_Common::parthTimeLag($UserInfo['TotalNetTime']);
                            }
                        }
                        $oExcel->addRows(array($t));
                        //生成单行数据
                        $t = array();
                        $t['1'] = "";
                        $t['2'] = "";
                        $t['3'] = "";
                        $t['Name'] = "分段时间";
                        //循环选手信息
                        foreach($RaceResultList['UserRaceInfo']['Point'][$tid]['UserList'] as $U => $UserInfo)
                        {

                            $t[] = Base_Common::parthTimeLag($UserInfo['PointTime']);
                        }
                        $oExcel->addRows(array($t));
                    }
                }
                //生成单行数据
                $t = array();
                $t['1'] = "总时间";
                $t['2'] = "";
                $t['3'] = "";
                if($RaceInfo['comment']['ResultType']=="Team")
                {
                    $t['Name'] = "队伍名";
                }
                else
                {
                    $t['Name'] = "姓名";
                }
                //循环选手信息
                foreach($RaceResultList['UserRaceInfo']['Total'] as $U => $UserInfo)
                {
                    if($RaceInfo['comment']['ResultType']=="Team")
                    {
                        $t[] = $UserInfo['TeamName'];
                    }
                    else
                    {
                        $t[] = $UserInfo['Name'];
                    }
                }
                $oExcel->addRows(array($t));
                //生成单行数据
                $t = array();
                $t['1'] = "";
                $t['2'] = "";
                $t['3'] = "";
                $t['Name'] = "分组";
                //循环选手信息
                foreach($RaceResultList['UserRaceInfo']['Total'] as $U => $UserInfo)
                {
                    $t[] = $RaceGroupList[$UserInfo['RaceGroupId']]["RaceGroupInfo"]['RaceGroupName'];
                }
                $oExcel->addRows(array($t));
                //生成单行数据
                $t = array();
                $t['1'] = "";
                $t['2'] = "";
                $t['3'] = "";
                $t['4'] = "BIB";
                //循环选手信息
                foreach($RaceResultList['UserRaceInfo']['Total'] as $U => $UserInfo)
                {
                    $t[] = $UserInfo['BIB'];
                }
                $oExcel->addRows(array($t));
                //生成单行数据
                $t = array();
                $t['1'] = "";
                $t['2'] = "";
                $t['3'] = "";
                if($RaceInfo['RouteInfo']['RaceTimingResultType']=="gunshot")
                {
                    $t['Name'] = "总时间";
                }
                else
                {
                    $t['Name'] = "净时间";
                }
                //循环选手信息
                foreach($RaceResultList['UserRaceInfo']['Total'] as $U => $UserInfo)
                {
                    if($RaceInfo['RouteInfo']['RaceTimingResultType']=="gunshot")
                    {
                        $t[] = Base_Common::parthTimeLag($UserInfo['TotalTime']);
                    }
                    else
                    {
                        $t[] = Base_Common::parthTimeLag($UserInfo['TotalNetTime']);
                    }
                }
                $oExcel->addRows(array($t));
                //生成单行数据
                $t = array();
                $t['1'] = "";
                $t['2'] = "";
                $t['3'] = "";
                $t['4'] = "分组排名/全场排名";
                //循环选手信息
                foreach($RaceResultList['UserRaceInfo']['Total'] as $U => $UserInfo)
                {
                    $t[] = $UserInfo['GroupRank']."/".$UserInfo['Rank'];
                }
                $oExcel->addRows(array($t));
                $oExcel->closeSheet();
                */
                $oExcel->addSheet('个人详情');
                $t = array();
                $t["BIB"] = "BIB";$t["Name"] = "姓名";$t["Group"] = "组别";$t["Team"] = "队伍";
                //循环运动类型列表
                foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                {
                    $t[$sid] = $SportsInfo['SportsTypeInfo']['SportsTypeName'];
                }
                foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                {
                    $t[$sid."_Speed"] = $SportsInfo['SportsTypeInfo']['SportsTypeName']."_均速";
                }
                $t["TotalTime"] = "总时间";$t["Lag"] = "落后";$t["Rank"] = "总排名";$t["GroupRank"] = "分组排名";$t['DNS'] = "DNS";$t["DNF"] = "DNF";
                $oExcel->addRows(array($t));
                unset($t);
                /*
                if($RaceInfo['comment']['ResultType'] == "Team")
                {
                    foreach($RaceResultList['UserRaceInfo']['Team'] as $G => $GroupInfo)
                    {
                        if($RaceGroupId == 0 || $RaceGroupId==$G)
                        {
                            //循环选手信息
                            foreach($GroupInfo as $U => $UserInfo)
                            {
                                $t = array();
                                $t["BIB"] = $UserInfo['BIB'];
                                $t["Name"] = $UserInfo['TeamName'];
                                $t["GroupName"] = $RaceGroupList[$UserInfo['RaceGroupId']]["RaceGroupInfo"]['RaceGroupName'];
                                $t["TeamName"] = $UserInfo['TeamName'];
                                $UserRaceInfo = $this->oRace->getRaceResult($RaceId, $UserInfo['RaceGroupId'],$UserInfo['RaceUserId']);
                                //循环运动类型列表
                                foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                                {
                                    $point = $SportsInfo['TimingPointList'][count($SportsInfo['TimingPointList'])-1];
                                    $t[$point] = Base_Common::parthTimeLag($UserRaceInfo['UserRaceInfo']['Point'][$point]['SportsTime']);
                                }
                                foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                                {
                                    $point = $SportsInfo['TimingPointList'][count($SportsInfo['TimingPointList'])-1];
                                    $t[$point."_Speed"] = $UserRaceInfo['UserRaceInfo']['Point'][$point]['PointSpeed'];
                                }
                                if($RaceInfo['RouteInfo']['RaceTimingResultType']=="gunshot")
                                {
                                    $t["Time"] = Base_Common::parthTimeLag($UserRaceInfo['UserRaceInfo']['Point'][count($UserRaceInfo['UserRaceInfo']['Point'])]['TotalTime']);
                                    $t["TimeLag"] = ($UserInfo['CurrentPosition']==$RaceResultList['UserRaceInfo']['Total'][0]['CurrentPosition'])?(Base_Common::parthTimeLag($UserInfo['TotalTime'] - $RaceResultList['UserRaceInfo']['Total'][0]['TotalTime'])):"";
                                }
                                else
                                {
                                    $t["Time"] = Base_Common::parthTimeLag($UserRaceInfo['UserRaceInfo']['Point'][count($UserRaceInfo['UserRaceInfo']['Point'])]['TotalNetTime']);
                                    $t["TimeLag"] = ($UserInfo['CurrentPosition']==$RaceResultList['UserRaceInfo']['Total'][0]['CurrentPosition'])?(Base_Common::parthTimeLag($UserInfo['TotalNetTime'] - $RaceResultList['UserRaceInfo']['Total'][0]['TotalNetTime'])):"";

                                }
                                $t["Rank"] = $UserInfo['Rank'];
                                $t["GroupRank"] = $UserInfo['GroupRank'];
                                $t["DNS"] = $UserInfo['CurrentPosition']>1?"否":"是";
                                $t["DNF"] = $UserInfo['Finished']>0?"否":"是";
                                $oExcel->addRows(array($t));
                                unset($t);
                            }
                        }
                    }
                }
                else
                {*/
                    //循环选手信息
                    foreach($RaceResultList['UserRaceInfo']['Total'] as $U => $UserInfo)
                    {
                        $t = array();
                        $t["BIB"] = $UserInfo['BIB'];
                        $t["Name"] = $UserInfo['Name'];
                        $t["GroupName"] = $RaceGroupList[$UserInfo['RaceGroupId']]["RaceGroupInfo"]['RaceGroupName'];
                        $t["TeamName"] = $UserInfo['TeamName'];

                        $UserRaceInfo = $this->oRace->getRaceResult($RaceId, $UserInfo['RaceGroupId'],$UserInfo['RaceUserId']);
                        //循环运动类型列表

                        foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                        {
                            $point = $SportsInfo['TimingPointList'][count($SportsInfo['TimingPointList'])-1];
                            $t[$point] = Base_Common::parthTimeLag($UserRaceInfo['UserRaceInfo']['Point'][$point]['SportsTime']);
                        }

                        foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                        {
                            $point = $SportsInfo['TimingPointList'][count($SportsInfo['TimingPointList'])-1];
                            $t[$point."_Speed"] = $UserRaceInfo['UserRaceInfo']['Point'][$point]['SportsSpeed'];
                        }
                        if($RaceInfo['RouteInfo']['RaceTimingResultType']=="gunshot")
                        {
                            $t["Time"] = Base_Common::parthTimeLag($UserRaceInfo['UserRaceInfo']['Point'][count($UserRaceInfo['UserRaceInfo']['Point'])]['TotalTime']);
                            if(!isset($FirstTime))
                            {
                                $FirstTime = $UserRaceInfo['UserRaceInfo']['Point'][count($UserRaceInfo['UserRaceInfo']['Point'])]['TotalTime'];
                                $FirstPosition = $UserInfo['CurrentPosition'];
                            }
                            $t["TimeLag"] = ($UserInfo['CurrentPosition']==$FirstPosition)?(Base_Common::parthTimeLag($UserInfo['TotalTime'] - $FirstTime)):"n/a";
                        }
                        else
                        {
                            $t["Time"] = Base_Common::parthTimeLag($UserRaceInfo['UserRaceInfo']['Point'][count($UserRaceInfo['UserRaceInfo']['Point'])]['TotalNetTime']);
                            if(!isset($FirstTime))
                            {
                                $FirstTime = $UserRaceInfo['UserRaceInfo']['Point'][count($UserRaceInfo['UserRaceInfo']['Point'])]['TotalNetTime'];
                                $FirstPosition = $UserInfo['CurrentPosition'];
                            }
                            $t["TimeLag"] = ($UserInfo['CurrentPosition']==$FirstPosition)?(Base_Common::parthTimeLag($UserInfo['TotalNetTime'] - $FirstTime)):"n/a";
                        }
                        $t["Rank"] = $UserInfo['Rank'];
                        $t["GroupRank"] = $UserInfo['GroupRank'];
                        $t["DNS"] = $UserInfo['CurrentPosition']>1?"否":"是";
                        $t["DNF"] = $UserInfo['Finished']>0?"否":"是";

                        $oExcel->addRows(array($t));
                        unset($t);
                    }
                //}
                $oExcel->closeSheet();
                //循环比赛已经开设的分组列表
                foreach($RaceInfo['comment']['SelectedRaceGroup'] as $GroupId => $GroupInfo)
                {
                    $RaceGroupInfo = $this->oRace->getRaceGroup($GroupId,"RaceGroupId,RaceGroupName");
                    if($RaceGroupInfo['RaceGroupId'])
                    {
                        if($RaceGroupId == $RaceGroupInfo['RaceGroupId'] || $RaceGroupId == 0)
                        {
                            $oExcel->addSheet('团队详情-'.$RaceGroupInfo['RaceGroupName']);
                            //如果取前几人的成绩
                            if($RaceInfo['comment']['TeamResultRankType'] == "Top")
                            {
                                $t = array();
                                $t["Team"] = "队伍";$t["BIB"] = "BIB";$t["Name"] = "姓名";
                                //循环运动类型列表
                                foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                                {
                                    $t[$sid] = $SportsInfo['SportsTypeInfo']['SportsTypeName'];
                                }
                                foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                                {
                                    $t[$sid."_Speed"] = $SportsInfo['SportsTypeInfo']['SportsTypeName']."_均速";
                                }
                                $t["TotalTime"] = "总时间";$t["Lag"] = "落后";
                                $oExcel->addRows(array($t));
                                //循环选手信息
                                foreach($RaceResultList['UserRaceInfo']['Team'][$GroupId] as $U => $UserInfo)
                                {
                                    $t = array();
                                    $t["TeamName"] = $UserInfo['TeamName'];
                                    $t["BIB"] = $UserInfo['BIB'];
                                    $t["Name"] = $UserInfo['Name'];
                                    $UserRaceInfo = $this->oRace->getRaceResult($RaceId, $UserInfo['RaceGroupId'],$UserInfo['RaceUserId']);
                                    //循环运动类型列表
                                    foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                                    {
                                        $point = $SportsInfo['TimingPointList'][count($SportsInfo['TimingPointList'])-1];
                                        $t[$point] = Base_Common::parthTimeLag($UserRaceInfo['UserRaceInfo']['Point'][$point]['SportsTime']);
                                    }
                                    foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                                    {
                                        $point = $SportsInfo['TimingPointList'][count($SportsInfo['TimingPointList'])-1];
                                        $t[$point."_Speed"] = $UserRaceInfo['UserRaceInfo']['Point'][$point]['PointSpeed'];
                                    }
                                    if($RaceInfo['RouteInfo']['RaceTimingResultType']=="gunshot")
                                    {
                                        $t["Time"] = Base_Common::parthTimeLag($UserRaceInfo['UserRaceInfo']['Point'][count($UserRaceInfo['UserRaceInfo']['Point'])]['TotalTime']);
                                        $t["TimeLag"] = Base_Common::parthTimeLag($UserInfo['TimeLag']);
                                        }
                                    else
                                    {
                                        $t["Time"] = Base_Common::parthTimeLag($UserRaceInfo['UserRaceInfo']['Point'][count($UserRaceInfo['UserRaceInfo']['Point'])]['TotalNetTime']);
                                        $t["TimeLag"] = Base_Common::parthTimeLag($UserInfo['NetTimeLag']);
                                    }
                                    $oExcel->addRows(array($t));
                                    unset($t);
                                }
                            }
                            //如果取前几人的成绩
                            elseif($RaceInfo['comment']['TeamResultRankType'] == "Sum")
                            {
                                $t = array();
                                $t["Team"] = "队伍";$t["TotalTime"] = "总时间";$t["Lag"] = "落后";$t["BIB"] = "BIB";$t["Name"] = "姓名";
                                //循环运动类型列表
                                foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                                {
                                    $t[$sid] = $SportsInfo['SportsTypeInfo']['SportsTypeName'];
                                }
                                foreach($RaceResultList['UserRaceInfo']['Sports'] as $sid => $SportsInfo)
                                {
                                    $t[$sid."_Speed"] = $SportsInfo['SportsTypeInfo']['SportsTypeName']."_均速";
                                }
                                $oExcel->addRows(array($t));
                                //循环选手信息
                                foreach($RaceResultList['UserRaceInfo']['Team'][$GroupId] as $U => $TeamInfo)
                                {
                                    $t = array();$i = 1;
                                    foreach($TeamInfo['UserList'] as $k => $UserInfo)
                                    {
                                        if($i == 1)
                                        {
                                            $t["TeamName"] = $TeamInfo['TeamName'];
                                            if($RaceInfo['RouteInfo']['RaceTimingResultType']=="gunshot")
                                            {
                                                $t["Time"] = Base_Common::parthTimeLag($TeamInfo['TotalTime']);
                                                $t["TimeLag"] = Base_Common::parthTimeLag($TeamInfo['TimeLag']);
                                            }
                                            else
                                            {
                                                $t["Time"] = Base_Common::parthTimeLag($TeamInfo['TotalNetTime']);
                                                $t["TimeLag"] = Base_Common::parthTimeLag($TeamInfo['NetTimeLag']);
                                            }
                                        }
                                        else
                                        {
                                            $t["TeamName"] = "";
                                            $t["Time"] = "";
                                            $t["TimeLag"] = "";
                                        }
                                        $i++;
                                        $t["BIB"] = $UserInfo['BIB'];
                                        $t["Name"] = $UserInfo['Name'];
                                        if($RaceInfo['RouteInfo']['RaceTimingResultType']=="gunshot")
                                        {
                                            $t["UserTime"] = Base_Common::parthTimeLag($UserInfo['TotalTime']);
                                        }
                                        else
                                        {
                                            $t["UserTime"] = Base_Common::parthTimeLag($UserInfo['TotalNetTime']);
                                        }
                                        $oExcel->addRows(array($t));
                                        unset($t);
                                    }
                                }
                            }
                            $oExcel->closeSheet();
                        }
                    }
                }
                $oExcel->close();
            }
            //渲染模板
			include $this->tpl('Xrace_Race_RaceResultList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//套餐添加填写页面
	public function raceCombinationAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
			//分站ID
			$RaceStageId = intval($this->request->RaceStageId);
			//获取比赛信息
			$RaceStageInfo = $this->oRace->getRaceStage($RaceStageId);
			//获取比赛列表
			$RaceList = $this->oRace->getRaceList(array("RaceStageId"=>$RaceStageInfo['RaceStageId']),'RaceId,RaceName,RaceGroupId,RaceTypeId');
			//获取比赛类型列表
			$RaceTypeList  = $this->oRace->getRaceTypeList("RaceTypeId,RaceTypeName");
			//赛事分组列表
			$RaceGroupList = $this->oRace->getRaceGroupList($RaceStageInfo['RaceCatalogId'],'RaceGroupId,RaceGroupName');
			//循环比赛列表
			foreach($RaceList as $RaceId => $RaceInfo)
			{
				//获取比赛类型名称
				$RaceList[$RaceId]['RaceGroupName'] = isset($RaceGroupList[$RaceInfo['RaceGroupId']]['RaceGroupName'])?$RaceGroupList[$RaceInfo['RaceGroupId']]['RaceGroupName']:"未配置";
				//获取比赛类型名称
				$RaceList[$RaceId]['RaceTypeName'] = isset($RaceTypeList[$RaceInfo['RaceTypeId']]['RaceTypeName'])?$RaceTypeList[$RaceInfo['RaceTypeId']]['RaceTypeName']:"未配置";
			}
			//解包数组
			$RaceStageInfo['comment'] = isset($RaceStageInfo['comment']) ? json_decode($RaceStageInfo['comment'], true) : array();
			if (isset($RaceStageInfo['comment']['SelectedProductList']))
			{
				//初始化一个空的产品列表
				$ProductList = array();
				//循环产品列表
				foreach ($RaceStageInfo['comment']['SelectedProductList'] as $ProductId => $ProductSkuList)
				{
					//如果产品列表中没有此产品
					if (!isset($ProductList[$ProductId]))
					{
						//获取产品信息
						$ProductInfo = $this->oProduct->getProduct($ProductId, "ProductId,ProductName,comment");
						//如果产品信息获取到
						if(isset($ProductInfo['ProductId']))
						{
							$SkuList = $this->oProduct->getAllProductSkuList($ProductId);
							$t = array();
							foreach($SkuList[$ProductId] as $k => $v)
							{
								if(isset($ProductSkuList[$k]))
								{
									$t[$k] = $v['ProductSkuName'];
								}
							}
							if(count($SkuList[$ProductId])>=1)
							{
								//存入产品名称
								$RaceStageInfo['comment']['SelectedProductList'][$ProductId] = array('ProductName' => $ProductInfo['ProductName'],'ProductSkuList'=>implode("/",$t));
							}
							else
							{
								unset($RaceStageInfo['comment']['SelectedProductList'][$ProductId]);
							}
						}
						else
						{
							unset($RaceStageInfo['comment']['SelectedProductList'][$ProductId]);
						}
					}
					else
					{
						continue;
					}
				}
			}
			//渲染模板
			include $this->tpl('Xrace_Race_RaceCombinationAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//套餐添加
	public function raceCombinationInsertAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
			//获取 页面参数
			$bind=$this->request->from('RaceStageId','RaceCombinationName','PriceList');
			//获取已经选定的比赛列表
			$RaceList = $this->request->from('RaceList');
			//获取已经选定的产品列表
			$ProductList = $this->request->from('ProductList');
			//套餐名称不能为空
			if(trim($bind['RaceCombinationName'])=="")
			{
				$response = array('errno' => 1);
			}
			else
			{
				//获取当前分站信息
				$RaceStageInfo = $this->oRace->getRaceStage($bind['RaceStageId'],'*');
				//如果有获取到分站信息
				if(!isset($RaceStageInfo['RaceStageId']))
				{
					$response = array('errno' => 2);
				}
				else
				{
					//套餐内的比赛数量
					$RaceCount = count($RaceList['RaceList']);
					foreach($ProductList['ProductList'] as $ProductId => $ProductInfo)
					{
						if($ProductInfo['ProductCount']<1)
						{
							unset($ProductList['ProductList'][$ProductId]);
						}
					}
					//套餐内产品数量
					$ProductCount = count($ProductList['ProductList']);
					//如果产品与比赛数量小于2
					if(($RaceCount+$ProductCount)<2)
					{
						$response = array('errno' => 3);
					}
					else
					{
						$bind['ProductList'] = json_encode($ProductList['ProductList']);
						$bind['RaceList'] = json_encode($RaceList['RaceList']);
						$bind['RaceCatalogId'] = $RaceStageInfo['RaceCatalogId'];
						//插入数据
						$res = $this->oRace->insertRaceCombination($bind);
						$response = $res ? array('errno' => 0) : array('errno' => 9);
					}
				}
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
	//比赛列表页面
	public function raceCombinationListAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
			//赛事分站ID
			$RaceStageId = intval($this->request->RaceStageId);
			//获取当前分站信息
			$RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,'RaceStageId,RaceStageName,comment');
			//解包压缩数组
			$RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
			$params = array('RaceStageId'=>$RaceStageId);
			//获取报名套餐列表
			$RaceCombinationList = $this->oRace->getRaceCombinationList($params);
			//循环套餐列表
			foreach($RaceCombinationList as $RaceCombinationId => $RaceCombinationInfo)
			{
				//解压缩比赛列表和产品列表
				$RaceCombinationList[$RaceCombinationId]['RaceList'] = json_decode($RaceCombinationInfo['RaceList'],true);
				//如果有配置比赛
				if(count($RaceCombinationList[$RaceCombinationId]['RaceList']))
				{
					//循环比赛列表
					foreach($RaceCombinationList[$RaceCombinationId]['RaceList'] as $RaceId => $Race)
					{
						//获取比赛信息
						$RaceInfo = $this->oRace->getRace($RaceId,"RaceId,RaceName,RaceGroupId");
						//如果有获取到
						if(isset($RaceInfo['RaceId']))
						{
                            //如果是比赛分组模式
                            if($RaceStageInfo['comment']['RaceStructure']=="race")
                            {
                                $RaceInfo['RaceGroupInfo'] = array("RaceGroupName"=>"多个分组");
                            }
                            else
                            {
                                //获取分组信息
                                $RaceGroupInfo = $this->oRace->getRaceGroup($RaceInfo['RaceGroupId'],"RaceGroupId,RaceGroupName");
                                //如果获取到
                                if(isset($RaceGroupInfo['RaceGroupId']))
                                {
                                    $RaceInfo['RaceGroupInfo'] = $RaceGroupInfo;
                                }
                            }

							//保存比赛信息
							$RaceCombinationList[$RaceCombinationId]['RaceList'][$RaceId] = $RaceInfo;
						}
						else
						{
							//否则删除数据
							unset($RaceCombinationList[$RaceCombinationId]['RaceList'][$RaceId]);
						}
					}
				}
				$RaceCombinationList[$RaceCombinationId]['ProductList'] = json_decode($RaceCombinationInfo['ProductList'],true);
				//如果有配置比赛
				if(count($RaceCombinationList[$RaceCombinationId]['ProductList']))
				{
					//循环比赛列表
					foreach($RaceCombinationList[$RaceCombinationId]['ProductList'] as $ProductId => $Product)
					{
						if($Product['ProductCount']>=1)
						{
							//获取比赛信息
							$ProductInfo = $this->oProduct->getProduct($ProductId,"ProductId,ProductName");
							//如果有获取到
							if(isset($ProductInfo['ProductId']))
							{
								//如果有找到配置的SKU列表
								if(isset($RaceStageInfo['comment']['SelectedProductList'][$ProductId]))
								{
									$t = array();
									//获取产品的SKU列表
									$SkuList = $this->oProduct->getAllProductSkuList($ProductId);
									//循环分站已配置的SKU列表
									foreach($RaceStageInfo['comment']['SelectedProductList'][$ProductId] as $SkuId => $SkuInfo)
									{
										//如果SKU存在
										if(isset($SkuList[$ProductId][$SkuId]))
										{
											$t[] = $SkuList[$ProductId][$SkuId]['ProductSkuName'];
											//保存SKU名称
											$RaceStageInfo['comment']['SelectedProductList'][$ProductId][$SkuId]['SkuName'] = $SkuList[$ProductId][$SkuId]['ProductSkuName'];
										}
									}
									//生成显示用的SKU列表
									$ProductInfo['SkuListText'] = "(".implode("/",$t).")";
									//保存产品信息
									$ProductInfo['SkuList'] = $RaceStageInfo['comment']['SelectedProductList'][$ProductId];
								}
								$ProductInfo['ProductCount'] = $Product['ProductCount'];
								//保存比赛信息
								$RaceCombinationList[$RaceCombinationId]['ProductList'][$ProductId] = $ProductInfo;
							}
							else
							{
								//否则删除数据
								unset($RaceCombinationList[$RaceCombinationId]['ProductList'][$ProductId]);
							}
						}
						else
						{
							//否则删除数据
							unset($RaceCombinationList[$RaceCombinationId]['ProductList'][$ProductId]);
						}
					}
				}
			}
			//渲染模板
			include $this->tpl('Xrace_Race_RaceCombinationList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	public function raceCombinationModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
			//分站ID
			$RaceCombinationId = intval($this->request->RaceCombinationId);
			//获取套餐信息
			$RaceCombinationInfo = $this->oRace->getRaceCombination($RaceCombinationId);
            //解包比赛列表
			$RaceCombinationInfo['RaceList'] = json_decode($RaceCombinationInfo['RaceList'],true);
			//解包产品列表
			$RaceCombinationInfo['ProductList'] = json_decode($RaceCombinationInfo['ProductList'],true);
			//获取比赛信息
			$RaceStageInfo = $this->oRace->getRaceStage($RaceCombinationInfo['RaceStageId'],"RaceStageId,RaceStageName,comment");
            //解包数组
            $RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
            //获取比赛列表
			$RaceList = $this->oRace->getRaceList(array("RaceStageId"=>$RaceCombinationInfo['RaceStageId'],"InRun"=>0),'RaceId,RaceName,RaceGroupId,RaceTypeId,comment');
            //获取比赛类型列表
			$RaceTypeList  = $this->oRace->getRaceTypeList("RaceTypeId,RaceTypeName");
			//赛事分组列表
			$RaceGroupList = $this->oRace->getRaceGroupList($RaceStageInfo['RaceCatalogId'],'RaceGroupId,RaceGroupName');
            //如果是比赛分组模式
            if($RaceStageInfo['comment']['RaceStructure']=="race")
            {
                //循环比赛列表
                foreach($RaceList as $RaceId => $RaceInfo)
                {
                    $RaceList[$RaceId]['selected'] = isset($RaceCombinationInfo['RaceList'][$RaceId])?1:0;
                    //获取比赛分组名称
                    $RaceList[$RaceId]['RaceGroupName'] = "多个分组";
                    //获取比赛类型名称
                    $RaceList[$RaceId]['RaceTypeName'] = isset($RaceTypeList[$RaceInfo['RaceTypeId']]['RaceTypeName'])?$RaceTypeList[$RaceInfo['RaceTypeId']]['RaceTypeName']:"未配置";
                }
            }
            else
            {
                //循环比赛列表
                foreach($RaceList as $RaceId => $RaceInfo)
                {
                    $RaceList[$RaceId]['selected'] = isset($RaceCombinationInfo['RaceList'][$RaceId])?1:0;
                    //获取比赛类型名称
                    $RaceList[$RaceId]['RaceGroupName'] = isset($RaceGroupList[$RaceInfo['RaceGroupId']]['RaceGroupName'])?$RaceGroupList[$RaceInfo['RaceGroupId']]['RaceGroupName']:"未配置";
                    //获取比赛类型名称
                    $RaceList[$RaceId]['RaceTypeName'] = isset($RaceTypeList[$RaceInfo['RaceTypeId']]['RaceTypeName'])?$RaceTypeList[$RaceInfo['RaceTypeId']]['RaceTypeName']:"未配置";
                }
            }

			//解包数组
			$RaceStageInfo['comment'] = isset($RaceStageInfo['comment']) ? json_decode($RaceStageInfo['comment'], true) : array();
			if (isset($RaceStageInfo['comment']['SelectedProductList']))
			{
				//初始化一个空的产品列表
				$ProductList = array();
				//循环产品列表
				foreach ($RaceStageInfo['comment']['SelectedProductList'] as $ProductId => $ProductSkuList)
				{
					//如果产品列表中没有此产品
					if (!isset($ProductList[$ProductId]))
					{
						//获取产品信息
						$ProductInfo = $this->oProduct->getProduct($ProductId, "ProductId,ProductName,comment");
						//如果产品信息获取到
						if(isset($ProductInfo['ProductId']))
						{
							$SkuList = $this->oProduct->getAllProductSkuList($ProductId);
							$t = array();
							foreach($SkuList[$ProductId] as $k => $v)
							{
								if(isset($ProductSkuList[$k]))
								{
									$t[$k] = $v['ProductSkuName'];
								}
							}
							if(count($SkuList[$ProductId])>=1)
							{
								//存入产品名称
								$RaceStageInfo['comment']['SelectedProductList'][$ProductId] = array('ProductName' => $ProductInfo['ProductName'],'ProductSkuList'=>implode("/",$t),'ProductCount'=>isset($RaceCombinationInfo['ProductList'][$ProductId])?$RaceCombinationInfo['ProductList'][$ProductId]['ProductCount']:0);
							}
							else
							{
								unset($RaceStageInfo['comment']['SelectedProductList'][$ProductId]);
							}
						}
						else
						{
							unset($RaceStageInfo['comment']['SelectedProductList'][$ProductId]);
						}
					}
					else
					{
						continue;
					}
				}
			}
			//渲染模板
			include $this->tpl('Xrace_Race_RaceCombinationModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//套餐添加
	public function raceCombinationUpdateAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
		if($PermissionCheck['return'])
		{
			//获取 页面参数
			$bind=$this->request->from('RaceCombinationId','RaceCombinationName','PriceList');
			//获取已经选定的比赛列表
			$RaceList = $this->request->from('RaceList');
			//获取已经选定的产品列表
			$ProductList = $this->request->from('ProductList');
			//套餐名称不能为空
			if(trim($bind['RaceCombinationName'])=="")
			{
				$response = array('errno' => 1);
			}
			else
			{
				//获取套餐信息
				$RaceCombinationInfo = $this->oRace->getRaceCombination($bind['RaceCombinationId']);
				//获取当前分站信息
				$RaceStageInfo = $this->oRace->getRaceStage($RaceCombinationInfo['RaceStageId'],'*');
				//套餐内的比赛数量
				$RaceCount = count($RaceList['RaceList']);
				foreach($ProductList['ProductList'] as $ProductId => $ProductInfo)
				{
					if($ProductInfo['ProductCount']<1)
					{
						unset($ProductList['ProductList'][$ProductId]);
					}
				}
				//套餐内产品数量
				$ProductCount = count($ProductList['ProductList']);
				//如果产品与比赛数量小于2
				if(($RaceCount+$ProductCount)<2)
				{
					$response = array('errno' => 3);
				}
				else
				{
					$bind['ProductList'] = json_encode($ProductList['ProductList']);
					$bind['RaceList'] = json_encode($RaceList['RaceList']);
					$bind['RaceCatalogId'] = $RaceStageInfo['RaceCatalogId'];
					//插入数据
					$res = $this->oRace->updateRaceCombination($bind['RaceCombinationId'],$bind);
					$response = $res ? array('errno' => 0) : array('errno' => 9);
				}

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
    //用户签到信息列表
    public function raceStageUserCheckInStatusAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //分站ID
            $RaceStageId = intval($this->request->RaceStageId);
            //签到状态
            $UserCheckInStatus = intval($this->request->UserCheckInStatus);
            //分站数据
            $RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,'RaceStageId,RaceStageName');
            $oUser = new Xrace_UserInfo();
            $params = array('RaceStageId'=>$RaceStageInfo['RaceStageId'],'CheckinStatus'=>$UserCheckInStatus);
            //获取选手签到状态列表
            $UserCheckInStatusList = $oUser->getRaceUserCheckInList($params);
            $CheckInStatus = array();
            //获取签到状态列表
            $UserCheckInStatus = $oUser->getStageUserCheckInStatus();
            foreach($UserCheckInStatus as $Status => $StatusName)
            {
                $CheckInStatus[$Status]['UserCount'] = 0;
                $CheckInStatus[$Status]['CheckInStatusName'] = $StatusName;
                $CheckInStatus[$Status]['StatusUrl'] = "<a href='".Base_Common::getUrl('','xrace/race.stage','user.stage.check.in.status',array('RaceStageId'=>$RaceStageInfo['RaceStageId'],'UserCheckInStatus'=>$Status)) ."'>".$StatusName."</a>";
            }
            //获取签到短信发送状态列表
            $UserCheckInSmsSentStatus = $oUser->getUserCheckInSmsSentStatus();
            foreach($UserCheckInStatusList as $key => $CheckInInfo)
            {
                //获取用户信息
                $RaceUserInfo = $oUser->getRaceUser($CheckInInfo['RaceUserId'],"RaceUserId,Name");
                //如果未获取到用户信息
                if(!isset($RaceUserInfo['RaceUserId']))
                {
                    unset($UserCheckInStatusList[$key]);
                }
                else
                {
                    $CheckInStatus[$CheckInInfo['CheckinStatus']]['UserCount'] ++;
                    $CheckInStatus[0]['UserCount'] ++;
                    $UserCheckInStatusList[$key]['RaceUserInfo'] = $RaceUserInfo;
                }
                //签到状态
                $UserCheckInStatusList[$key]['CheckInStatusName'] = $UserCheckInStatus[$CheckInInfo['CheckinStatus']];
                //签到短信状态
                $UserCheckInStatusList[$key]['CheckInSmsSentStatusName'] = $UserCheckInSmsSentStatus[$CheckInInfo['SmsSentStatus']];
            }
            $CheckInStatus = $oUser->getRaceUserCheckInStatusCountList($params);
            foreach($CheckInStatus as $Status => $StatusInfo)
            {
                $CheckInStatus[$Status]['StatusUrl'] = $StatusInfo['StatusName'].":"."<a href='".Base_Common::getUrl('','xrace/race.stage','race.stage.user.check.in.status',array('RaceStageId'=>$RaceStageInfo['RaceStageId'],'UserCheckInStatus'=>$Status)) ."'>".$StatusInfo['UserCount']."人</a>";
            }
            $CheckInByCodeUrl = "<a href='".Base_Common::getUrl('','xrace/race.stage','race.stage.user.check.in',array('CheckInType'=>'Code','RaceStageId'=>$RaceStageInfo['RaceStageId'])) ."'>扫码签到</a>";
            $CheckInByIdUrl = "<a href='".Base_Common::getUrl('','xrace/race.stage','race.stage.user.check.in',array('CheckInType'=>'Id','RaceStageId'=>$RaceStageInfo['RaceStageId'])) ."'>证件号签到</a>";
            $CheckInByBIBUrl = "<a href='".Base_Common::getUrl('','xrace/race.stage','race.stage.user.check.in',array('CheckInType'=>'BIB','RaceStageId'=>$RaceStageInfo['RaceStageId'])) ."'>BIB签到</a>";

            //渲染模板
            include $this->tpl('Xrace_Race_RaceStageUserCheckInList');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //用户签到信息列表
    public function raceCheckInStatusAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //检录状态
            $CheckInStatus = intval($this->request->CheckInStatus);
            //分站数据
            $RaceInfo = $this->oRace->getRace($RaceId,'RaceId,RaceName');
            $oUser = new Xrace_UserInfo();
            $params = array('RaceId'=>$RaceInfo['RaceId'],"CheckInStatus"=>$CheckInStatus);
            //获取选手签到状态列表
            $UserCheckInStatusList = $oUser->getRaceUserList($params,array("ApplyId","RaceUserId","RaceId","RaceGroupId","BIB","ChipId","CheckInStatus,CheckInTime"));
            $CheckInStatus = array();
            //获取签到状态列表
            $UserCheckInStatus = $oUser->getRaceUserCheckInStatus();
            foreach($UserCheckInStatus as $Status => $StatusName)
            {
                $CheckInStatus[$Status]['UserCount'] = 0;
                $CheckInStatus[$Status]['CheckInStatusName'] = $StatusName;
                $CheckInStatus[$Status]['StatusUrl'] = "<a href='".Base_Common::getUrl('','xrace/race.stage','race.user.check.in.status',array('RaceId'=>$RaceInfo['RaceId'],'UserCheckInStatus'=>$Status)) ."'>".$StatusName."</a>";
            }
            //获取签到短信发送状态列表
            $UserCheckInSmsSentStatus = $oUser->getUserCheckInSmsSentStatus();
            foreach($UserCheckInStatusList as $key => $CheckInInfo)
            {
                //获取用户信息
                $RaceUserInfo = $oUser->getRaceUser($CheckInInfo['RaceUserId'],"RaceUserId,Name");
                //如果未获取到用户信息
                if(!isset($RaceUserInfo['RaceUserId']))
                {
                    unset($UserCheckInStatusList[$key]);
                }
                else
                {
                    $CheckInStatus[$CheckInInfo['CheckInStatus']]['UserCount'] ++;
                    $CheckInStatus[0]['UserCount'] ++;
                    $UserCheckInStatusList[$key]['RaceUserInfo'] = $RaceUserInfo;
                }
                //签到状态
                $UserCheckInStatusList[$key]['CheckInStatusName'] = $UserCheckInStatus[$CheckInInfo['CheckInStatus']];
            }
            foreach($CheckInStatus as $Status => $StatusInfo)
            {
                $CheckInStatus[$Status]['StatusUrl'] = $StatusInfo['CheckInStatusName'].":"."<a href='".Base_Common::getUrl('','xrace/race.stage','race.check.in.status',array('RaceId'=>$RaceInfo['RaceId'],'CheckInStatus'=>$Status)) ."'>".$StatusInfo['UserCount']."人</a>";
            }
            $CheckInUrl = "<a href='".Base_Common::getUrl('','xrace/race.stage','race.check.in.submit',array('RaceId'=>$RaceInfo['RaceId'])) ."'>去检录</a>";
            //渲染模板
            include $this->tpl('Xrace_Race_RaceUserCheckInList');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //用户签到提交页面
    public function raceStageUserCheckInAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $CheckInType = trim($this->request->CheckInType);
            //比赛ID
            $RaceStageId = intval($this->request->RaceStageId);
            //分站数据
            $RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,'RaceStageId,RaceStageName');
            //签到状态列表
            $CheckInStatusUrl = "<a href='".Base_Common::getUrl('','xrace/race.stage','race.stage.user.check.in.status',array('RaceStageId'=>$RaceStageInfo['RaceStageId'])) ."'>返回</a>";
            //渲染模板
            include $this->tpl('Xrace_Race_RaceStageUserCheckIn');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //用户签到提交页面
    public function raceStageCheckInUserInfoAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oUser = new Xrace_UserInfo();
            $bind=$this->request->from('CheckInCode','RaceStageId','IdNo','CheckInType','RaceUserId','BIB');
            if($bind['CheckInType']=="Code")
            {
                //用户ID
                $RaceUserId = hexdec(trim($bind['CheckInCode']));
                //获取用户信息
                $RaceUserInfo = $oUser->getRaceUser($RaceUserId);
            }
            elseif($bind['CheckInType']=="IdNo")
            {
                //根据证件号码获取比赛用户信息
                $RaceUserInfo = $oUser->getRaceUserByColumn("IdNo",trim($bind['IdNo']));
            }
            elseif($bind['CheckInType']=="BIB")
            {
                //根据用户的BIB获取比赛报名信息
                $UserApplyInfo = $oUser->getRaceUserList(array("RaceStageId"=>$bind['RaceStageId'],"BIB"=>$bind['BIB']),array("ApplyId","RaceUserId"));
                //获取用户信息
                $RaceUserInfo = $oUser->getRaceUser($UserApplyInfo['0']['RaceUserId']);
            }
            else
            {
                //获取用户信息
                $RaceUserInfo = $oUser->getRaceUser($bind['RaceUserId']);
            }
            //获取实名认证证件类型列表
            $AuthIdTypesList = $oUser->getAuthIdType();
            $RaceUserInfo['IdTypeName'] = isset($AuthIdTypesList[$RaceUserInfo['IdType']])?$AuthIdTypesList[$RaceUserInfo['IdType']]:$AuthIdTypesList[1];
            //获取性别列表
            $SexList = $oUser->getSexList();
            $RaceUserInfo['Sex'] = isset($SexList[$RaceUserInfo['Sex']])?$SexList[$RaceUserInfo['Sex']]:$SexList[1];
            //渲染模板
            include $this->tpl('Xrace_Race_RaceStageCheckInUserInfo');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    public function userCheckInAction()
    {
        //获取 页面参数
        $bind=$this->request->from('RaceStageId','CheckInType','RaceUserId');
        $CheckIn = $this->oRace->RaceStageCheckInByRaceUserId($bind['RaceStageId'],$bind['RaceUserId']);
        if($CheckIn)
        {
            $response = array('errno' => 0,'RaceUserId'=>$CheckIn);
        }
        else
        {
            $response = array('errno' => 1);
        }
        echo json_encode($response);
        return true;
    }
    //比赛选手列表 批量更新BIB和计时芯片ID
    public function raceStageUserCheckInBibAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //用户ID
            $CheckInType = trim($this->request->CheckInType);
            //用户ID
            $RaceUserId = intval($this->request->RaceUserId);
            //比赛分站
            $RaceStageId = intval($this->request->RaceStageId);
            //分站数据
            $RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,'RaceStageId,RaceStageName,RaceCatalogId,comment');
            //数据解包
            $RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
            $oUser = new Xrace_UserInfo();
            //获取用户信息
            $RaceUserInfo = $oUser->getRaceUser($RaceUserId,'RaceUserId,Name');
            $params = array('RaceStageId'=>$RaceStageId,'RaceUserId'=>$RaceUserId);
            //获取选手报名记录
            $UserRaceList = $oUser->getRaceUserList($params);
            //获取报名记录来源列表
            $ApplySourceList = $oUser->getRaceApplySourceList();
            //获取分组列表
            $RaceGroupList = $this->oRace->getRaceGroupList($RaceStageInfo['RaceCatalogId'],'RaceGroupId,RaceGroupName');
            //获取比赛列表
            $RaceList = $this->oRace->getRaceList(array("RaceStageId"=>$RaceStageInfo['RaceStageId']),"RaceId,RaceName,comment");
            foreach($UserRaceList as $key => $ApplyInfo)
            {
                //循环已经选中的分组列表
                foreach($RaceList[$ApplyInfo['RaceId']]['comment']['SelectedRaceGroup'] as $GroupId => $GroupInfo)
                {
                    //依次获取分组信息
                    $UserRaceList[$key]["RaceGroupList"][$GroupId] = $this->oRace->getRaceGroup($GroupId, "RaceGroupId,RaceGroupName");
                }
                $UserRaceList[$key]["ApplySourceName"] = $ApplySourceList[$ApplyInfo['ApplySource']];
                $UserRaceList[$key]["RaceGroupName"] = $RaceGroupList[$ApplyInfo['RaceGroupId']]['RaceGroupName'];
                $UserRaceList[$key]["RaceName"] = $RaceList[$ApplyInfo['RaceId']]['RaceName'];
            }
            //获取签到信息
            $UserCheckInInfo = $oUser->getUserCheckInInfo($RaceUserId,$RaceStageId);
            //数组解码
            $UserCheckInInfo['comment'] = json_decode($UserCheckInInfo['comment'],true);
            //签到状态列表
            $CheckInupUrl = "<a href='".Base_Common::getUrl('','xrace/race.stage','race.user.check.in',array('RaceStageId'=>$RaceStageInfo['RaceStageId'])) ."'>返回</a>";
            //渲染模板
            include $this->tpl('Xrace_Race_RaceStageUserCheckInBIB');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //批量更新比赛选手列表
    public function userRaceListUpdateAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //分站ID
            $RaceStageId = intval($this->request->RaceStageId);
            //比赛用户ID
            $RaceUserId = intval($this->request->RaceUserId);
            //补给代码列表
            $AidCodeList = urldecode(trim($this->request->AidCodeList));
            //获取BIB号码列表
            $UserRaceList = $this->request->from('UserRaceList');
            $oUser = new Xrace_UserInfo();
            //循环号码牌列表
            foreach($UserRaceList['UserRaceList'] as $Id => $UserRaceInfo)
            {
                //BIB
                $bind['BIB'] = trim($UserRaceInfo['BIB']);
                //计时芯片ID
                $bind['ChipId'] = trim($UserRaceInfo['ChipId']);
                //分组Id
                $bind['RaceGroupId'] = intval($UserRaceInfo['RaceGroupId']);
                //更新报名记录
                $oUser->updateRaceUserApply($UserRaceInfo['ApplyId'],$bind);
            }
            //如果代表列表不为空
            if(strlen($AidCodeList)>=3)
            {
                //获取比赛用户信息
                $RaceUserInfo = $oUser->getRaceUser($RaceUserId,"RaceUserId");
                //如果找到
                if(isset($RaceUserInfo['RaceUserId']))
                {
                    $oAidStation = new Xrace_AidStation();
                    //分解代码列表
                    $t = explode(",",$AidCodeList);
                    $SuccessList = array();
                    foreach($t as $key => $code)
                    {
                        //分配到人
                        $Apply = $oAidStation->applyAidCodeToUser($RaceUserId,$code,$RaceStageId);
                        if($Apply)
                        {
                            $SuccessList[] = $code;
                        }
                    }
                    //如果有成功的
                    if(count($SuccessList)>=1)
                    {
                        //获取签到信息
                        $UserCheckInInfo = $oUser->getUserCheckInInfo($RaceUserId,$RaceStageId);
                        //数组解码
                        $UserCheckInInfo['comment'] = json_decode($UserCheckInInfo['comment'],true);
                        //将分配成功的补给代码存入
                        $UserCheckInInfo['comment']['AidCodeList'] = $SuccessList;
                        //数组解码
                        $UserCheckInInfo['comment'] = json_encode($UserCheckInInfo['comment']);
                        //更新数据
                        $oUser->updateUserCheckInInfo($RaceUserId,$RaceStageId,array('comment'=>$UserCheckInInfo['comment']));
                    }
                }
            }

            $response = array('errno' => 0);
            echo json_encode($response);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //根据比赛记录更新选手的积分记录
    public function updateCreditByRaceResultAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId);
            //数据解包
            $RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
            //根据比赛结果更新积分
            $update = $this->oRace->updateCreditByRaceResult($RaceId);
            //返回之前页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //获取赛事获取分站列表
    public function getStageByCatalogAction()
    {
        //赛事ID
        $RaceCatalogId = intval($this->request->RaceCatalogId);
        //分组ID
        $RaceGroupId = intval($this->request->RaceGroupId);
        //所有赛事分站列表
        $RaceStageArr = $this->oRace->getRaceStageList($RaceCatalogId,'RaceStageId,RaceStageName');
        $text = '';
        //循环赛事分站列表
        foreach($RaceStageArr as $StageId => $RaceStageInfo)
        {
            //初始化选中状态
            $selected = "";
            //如果分站ID与传入的分站ID相符
            if($RaceStageInfo['RaceStageId'] == $StageId)
            {
                //选中拼接
                $selected = 'selected="selected"';
            }
            //字符串拼接
            $text .= '<option value="'.$RaceStageInfo['RaceStageId'].'">'.$RaceStageInfo['RaceStageName'].'</option>';
        }
        echo $text;
        die();
    }
    //根据赛事分站获取下一级列表
    public function getSecondLevelByStageAction()
    {
        //赛事分站ID
        $RaceStageId = intval($this->request->RaceStageId);
        //分站数据
        $RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,'RaceStageId,comment');
        //数据解包
        $RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
        //如果赛事结构为分组-比赛模式
        if($RaceStageInfo['comment']['RaceStructure'] == "group")
        {
            //比赛ID
            $RaceGroupId = intval($this->request->RaceGroupId);
            $text = '<option value= -1>全部</option>';;
            //循环已经选中的分组列表
            foreach($RaceStageInfo['comment']['SelectedRaceGroup'] as $GroupId)
            {
                //依次获取分组信息
                $RaceGroupInfo = $this->oRace->getRaceGroup($GroupId,"RaceGroupId,RaceGroupName");
                //初始化选中状态
                $selected = "";
                //如果比赛ID与传入的比赛ID相符
                if($RaceGroupInfo['RaceGroupId'] == $RaceGroupId)
                {
                    //选中拼接
                    $selected = 'selected="selected"';
                }
                //字符串拼接
                $text .= '<option value="'.$RaceGroupInfo['RaceGroupId'].'">'.$RaceGroupInfo['RaceGroupName'].'</option>';
            }
            echo json_encode(array("text"=>$text,"S"=>"group"));
        }
        elseif($RaceStageInfo['comment']['RaceStructure'] == "race")
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //所有比赛列表
            $RaceArr = $this->oRace->getRaceList(array('RaceStageId'=>$RaceStageId),'RaceId,RaceName');
            $text = '<option value= -1>全部</option>';;
            //循环比赛列表
            foreach($RaceArr as $RId => $RaceInfo)
            {
                //初始化选中状态
                $selected = "";
                //如果比赛ID与传入的比赛ID相符
                if($RaceInfo['RaceId'] == $RaceId)
                {
                    //选中拼接
                    $selected = 'selected="selected"';
                }
                //字符串拼接
                $text .= '<option value="'.$RaceInfo['RaceId'].'">'.$RaceInfo['RaceName'].'</option>';
            }
            echo json_encode(array("text"=>$text,"S"=>"race"));
        }
        die();
    }
    //根据情况获取分站第三级列表
    public function getThirdLevelByStageAction()
    {
        //赛事分站ID
        $RaceStageId = intval($this->request->RaceStageId);
        //分站数据
        $RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,'RaceStageId,comment');
        //数据解包
        $RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
        //如果赛事结构为分组-比赛模式
        if($RaceStageInfo['comment']['RaceStructure'] == "race")
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId,"RaceId,comment");
            //数据解包
            $RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
            //比赛分组ID
            $RaceGroupId = intval($this->request->RaceGroupId);
            $text = '<option value= 0>全部</option>';
            //循环已经选中的分组列表
            foreach($RaceInfo['comment']['SelectedRaceGroup'] as $GroupId => $GroupInfo)
            {
                //依次获取分组信息
                $RaceGroupInfo = $this->oRace->getRaceGroup($GroupId,"RaceGroupId,RaceGroupName");
                //初始化选中状态
                $selected = "";
                //如果比赛ID与传入的比赛ID相符
                if($RaceGroupInfo['RaceGroupId'] == $RaceGroupId)
                {
                    //选中拼接
                    $selected = 'selected="selected"';
                }
                //字符串拼接
                $text .= '<option value="'.$RaceGroupInfo['RaceGroupId'].'">'.$RaceGroupInfo['RaceGroupName'].'</option>';
            }
            echo json_encode(array("text"=>$text,"S"=>"race"));
        }
        elseif($RaceStageInfo['comment']['RaceStructure'] == "group")
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //比赛ID
            $RaceGroupId = intval($this->request->RaceGroupId);
            if($RaceGroupId>0)
            {
                //所有比赛列表
                $RaceArr = $this->oRace->getRaceList(array('RaceGroupId'=>$RaceGroupId),'RaceId,RaceName');
            }
            else
            {
                //所有比赛列表
                $RaceArr = array();
            }

            $text = '<option value= 0>全部</option>';
            //循环比赛列表
            foreach($RaceArr as $RId => $RaceInfo)
            {
                //初始化选中状态
                $selected = "";
                //如果比赛ID与传入的比赛ID相符
                if($RaceInfo['RaceId'] == $RaceId)
                {
                    //选中拼接
                    $selected = 'selected="selected"';
                }
                //字符串拼接
                $text .= '<option value="'.$RaceInfo['RaceId'].'">'.$RaceInfo['RaceName'].'</option>';
            }
            echo json_encode(array("text"=>$text,"S"=>"group"));
        }
        die();
    }
    //用户检录提交页面
    public function raceCheckInSubmitAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //比赛数据
            $RaceInfo = $this->oRace->getRace($RaceId,'RaceId,RaceName');
            //签到状态列表
            $CheckInStatusUrl = "<a href='".Base_Common::getUrl('','xrace/race.stage','race.check.in.status',array('RaceId'=>$RaceInfo['RaceId'])) ."'>检录状态</a>";
            //渲染模板
            include $this->tpl('Xrace_Race_RaceCheckIn');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //用户检录提交
    public function raceCheckInAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //检录代码（芯片）
            $CheckInCode = trim($this->request->CheckInCode);
            //比赛数据
            $RaceInfo = $this->oRace->getRace($RaceId,'RaceStageId,RaceId,RaceName');
            //用户检录状态
            $RaceUserCheckInfo = $this->oRace->getUserCheckStatus($RaceInfo['RaceStageId'],$RaceInfo['RaceId'],$CheckInCode);
            //如果正确找到用户报名记录信息
            if($RaceUserCheckInfo['return']==1)
            {
                $oUser = new Xrace_UserInfo();
                $oTeam = new Xrace_Team();
                //获取性别列表
                $SexList = $oUser->getSexList();
                //获取实名认证证件类型列表
                $AuthIdTypesList = $oUser->getAuthIdType();
                //用户性别
                $RaceUserCheckInfo['RaceUserInfo']['Sex'] = isset($SexList[$RaceUserCheckInfo['RaceUserInfo']['Sex']])?$SexList[$RaceUserCheckInfo['RaceUserInfo']['Sex']]:"保密";
                //证件生日
                $RaceUserCheckInfo['RaceUserInfo']['Birthday'] = !is_null($RaceUserCheckInfo['RaceUserInfo']['Birthday'])?$RaceUserCheckInfo['RaceUserInfo']['Birthday']:"未知";
                //实名认证证件类型
                $RaceUserCheckInfo['RaceUserInfo']['AuthIdType'] = isset($AuthIdTypesList[intval($RaceUserCheckInfo['RaceUserInfo']['IdType'])])?$AuthIdTypesList[intval($RaceUserCheckInfo['RaceUserInfo']['IdType'])]:"未知";
                //分组信息
                $RaceGroupInfo = $this->oRace->getRaceGroup($RaceUserCheckInfo['ApplyInfo']['RaceGroupId'],"RaceGroupId,RaceGroupName");
                $RaceUserCheckInfo['ApplyInfo']['RaceGroupName'] = isset($RaceGroupInfo['RaceGroupId'])?$RaceGroupInfo['RaceGroupName']:"未知组别";
                //队伍信息
                $TeamInfo = $RaceUserCheckInfo['ApplyInfo']['TeamId']>0?($oTeam->getTeam($RaceUserCheckInfo['ApplyInfo']['TeamId'],"TeamId,TeamName")):array("TeamId"=>0,"TeamName"=>"个人");
                $RaceUserCheckInfo['ApplyInfo']['TeamName'] = isset($TeamInfo['TeamId'])?$TeamInfo['TeamName']:"个人";
            }
            //渲染模板
            include $this->tpl('Xrace_Race_RaceCheckInSubmit');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //报名记录检录
    public function raceUserCheckInAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //报名记录ID
            $ApplyId = intval($this->request->ApplyId);
            //执行检录
            $CheckIn = $this->oRace->RaceCheckIn($ApplyId);
            $CheckInUrl = Base_Common::getUrl('','xrace/race.stage','race.check.in.submit',array('RaceId'=>$CheckIn['RaceId']));
            //返回之前页面
            $this->response->redirect($CheckInUrl);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //报名记录检录
    public function raceResultConfirmAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //执行检录
            $RaceResultConfirm = $this->oRace->RaceResultConfirm($RaceId, $this->manager->id);
            //返回之前页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //补给点列表
    public function aidStationListAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            $oAidStation = new Xrace_AidStation();
            //报名记录ID
            $RaceStageId = intval($this->request->RaceStageId);
            //分站数据
            $RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,'RaceStageId,RaceStageName');
            //执行检录
            $AidStationList = $oAidStation->getAidStationIdList(array("RaceStageId"=>$RaceStageId));
            //渲染模板
            include $this->tpl('Xrace_Race_AidStationList');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //删除补给点
    public function aidStationDeleteAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oAidStation = new Xrace_AidStation();
            //比赛ID
            $AidStationId = intval($this->request->AidStationId);
            //删除
            $oAidStation->deleteAidStation($AidStationId);
            //返回之前页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改比赛配置信息填写页面
    public function aidStationAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oAidStation = new Xrace_AidStation();
            //补给点ID
            $RaceStageId = intval($this->request->RaceStageId);
            //渲染模板
            include $this->tpl('Xrace_Race_AidStationAdd');

        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改补给点配置信息填写页面
    public function aidStationModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oAidStation = new Xrace_AidStation();
            //补给点ID
            $AidStationId = intval($this->request->AidStationId);
            //获取比赛信息
            $AidStationInfo = $oAidStation->getAidStation($AidStationId);
            //渲染模板
            include $this->tpl('Xrace_Race_AidStationModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改补给点
    public function aidStationUpdateAction()
    {
        //获取 页面参数
        $bind=$this->request->from('AidStationId','AidStationName','AidStationComment');
        if($bind['AidStationName']=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            $oAidStation = new Xrace_AidStation();
            //获取分站信息
            $AidStationInfo = $oAidStation->getAidStation($bind['AidStationId']);
            //更新比赛
            $UpdateAidStation = $oAidStation->updateAidStation($bind['AidStationId'],$bind);
            $response = $UpdateAidStation ? array('errno' => 0,'RaceStageId'=>$AidStationInfo['RaceStageId']) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //添加补给点
    public function aidStationInsertAction()
    {
        //获取 页面参数
        $bind=$this->request->from('RaceStageId','AidStationName','AidStationComment');
        if($bind['AidStationName']=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            $oAidStation = new Xrace_AidStation();
            //获取分站信息
            $AidStationInfo = $oAidStation->getAidStation($bind['AidStationId']);
            //更新比赛
            $UpdateAidStation = $oAidStation->insertAidStation($bind);
            $response = $UpdateAidStation ? array('errno' => 0,'RaceStageId'=>$bind['RaceStageId']) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //修改补给点权限配置信息填写页面
    public function aidStationPermissionModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oAidStation = new Xrace_AidStation();
            //补给点ID
            $AidStationId = intval($this->request->AidStationId);
            //获取补给点信息
            $AidStationInfo = $oAidStation->getAidStation($AidStationId);
            //数据解包
            $AidStationInfo['comment'] = json_decode($AidStationInfo['comment'],true);
            //获取分类列表
            $AidCodeTypeList = $oAidStation->getAidCodeTypeByStage($AidStationInfo['RaceStageId']);
            foreach($AidStationInfo['comment']["AidCodeTypeList"] as $AidCodeTypeId => $AidCount)
            {
                $AidCodeTypeList[$AidCodeTypeId]['selected'] = 1;
                $AidCodeTypeList[$AidCodeTypeId]['AidCount'] = $AidCount;
            }

            //获取分站信息
            $RaceStageInfo = $this->oRace->getRaceStage($AidStationInfo['RaceStageId'],"RaceStageId,comment");
            //数据解包
            $RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
            if($RaceStageInfo['comment']['RaceStructure'] != "race")
            {
                $RaceList = array();
                //获取比赛列表
                $RaceArr = $this->oRace->getRaceList(array("RaceStageId"=>$AidStationInfo['RaceStageId']),"RaceId,RaceName,RaceGroupId");
                foreach($RaceArr as $RaceId => $RaceInfo)
                {
                    if(!isset($RaceGroupList[$RaceInfo['RaceGroupId']]))
                    {
                        $RaceList[$RaceInfo['RaceGroupId']]['RaceGroupInfo'] = $this->oRace->getRaceGroup($RaceInfo['RaceGroupId'],"RaceGroupId,RaceGroupName");
                    }
                    $RaceList[$RaceInfo['RaceGroupId']]['RaceList'][$RaceId] = $RaceInfo;
                    if(isset($AidStationInfo['comment']['RaceList'][$RaceId][$RaceInfo['RaceGroupId']]))
                    {
                        $RaceList[$RaceInfo['RaceGroupId']]['RaceList'][$RaceId]['selected'] = 1;
                        $RaceList[$RaceInfo['RaceGroupId']]['RaceList'][$RaceId]['AidCount'] = $AidStationInfo['comment']['RaceList'][$RaceId][$RaceInfo['RaceGroupId']];
                    }

                }
            }
            else
            {
                //获取比赛列表
                $RaceList = $this->oRace->getRaceList(array("RaceStageId"=>$AidStationInfo['RaceStageId']),"RaceId,RaceName,RaceGroupId,comment");
                $RaceGroupList = array();
                foreach($RaceList as $RaceId => $RaceInfo)
                {
                    foreach($RaceInfo['comment']['SelectedRaceGroup'] as $RaceGroupId => $RaceGroup)
                    {
                        if(!isset($RaceGroupList[$RaceGroupId]))
                        {
                            $RaceGroupList[$RaceGroupId] = $this->oRace->getRaceGroup($RaceGroupId,"RaceGroupId,RaceGroupName");
                        }
                        if(isset($AidStationInfo['comment']['RaceList'][$RaceId][$RaceGroupId]))
                        {
                            $RaceList[$RaceId]['comment']['SelectedRaceGroup'][$RaceGroupId]['selected'] = 1;
                            $RaceList[$RaceId]['comment']['SelectedRaceGroup'][$RaceGroupId]['AidCount'] = $AidStationInfo['comment']['RaceList'][$RaceId][$RaceGroupId];
                        }
                        $RaceList[$RaceId]['comment']['SelectedRaceGroup'][$RaceGroupId]['RaceGroupName'] = $RaceGroupList[$RaceGroupId]["RaceGroupName"];
                    }
                }
            }
            //渲染模板
            include $this->tpl('Xrace_Race_AidStationPermissionModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改补给点权限配置信息填写页面
    public function aidStationPermissionUpdateAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oAidStation = new Xrace_AidStation();
            //补给点ID
            $AidStationId = intval($this->request->AidStationId);
            //比赛-分组列表
            $AidCodeTypeList = $this->request->AidCodeTypeList;
            //获取补给点信息
            $AidStationInfo = $oAidStation->getAidStation($AidStationId);
            //数据解包
            $AidStationInfo['comment'] = json_decode($AidStationInfo['comment'],true);
            //初始化选中的比赛列表
            $AidStationInfo['comment']['AidCodeTypeList'] = array();
            foreach($AidCodeTypeList as $AidCodeTypeId => $AidCodeTypeInfo)
            {
                if($AidCodeTypeInfo['Selected'] && $AidCodeTypeInfo['AidCount']>0)
                {
                    $AidStationInfo['comment']['AidCodeTypeList'][$AidCodeTypeId] = $AidCodeTypeInfo['AidCount'];
                }
            }
            $AidStationInfo['comment'] = json_encode($AidStationInfo['comment']);
            $UpdateAidStation = $oAidStation->updateAidStation($AidStationId,$AidStationInfo);
            //返回之前页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //补给代码列表页面
    public function aidCodeListAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //赛事分站ID
            $RaceStageId = intval($this->request->RaceStageId);
            //使用状态
            $AidCodeStatus = isset($this->request->AidCodeStatus)?$this->request->AidCodeStatus:"-1";
            //分类
            $AidCodeTypeId = isset($this->request->AidCodeTypeId)?$this->request->AidCodeTypeId:"0";
            //分页参数
            $Page = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
            $PageSize = 20;
            $params = array("RaceStageId"=>$RaceStageId,"Used"=>$AidCodeStatus,"AidCodeTypeId"=>$AidCodeTypeId,"Page"=>$Page,"PageSize"=>$PageSize);
            $oAidStation = new Xrace_AidStation();
            //获取分类列表
            $AidCodeTypeList = $oAidStation->getAidCodeTypeByStage($params['RaceStageId']);
            $AidCodeList = $oAidStation->getAidCodeByStage(array_merge($params,array("GetCount"=>1)));
            $RaceUserList = array();
            $oUser = new Xrace_UserInfo();
            foreach($AidCodeList['AidCodeList'] as $key => $value)
            {
                $AidCodeList['AidCodeList'][$key]['AidCodeTypeName'] = $AidCodeTypeList[$value['AidCodeTypeId']]['AidCodeTypeName'];
                //如果因分配给人
                if($value['RaceUserId'])
                {
                    //如果缓存列表中已有
                    if(!isset($RaceUserList[$value['RaceUserId']]))
                    {
                        //获取选手信息
                        $RaceUserList[$value['RaceUserId']] = $oUser->getRaceUser($value['RaceUserId'],"RaceUserId,Name");
                    }
                    //保存用户
                    $AidCodeList['AidCodeList'][$key]['UserName'] = $RaceUserList[$value['RaceUserId']]['RaceUserId']?$RaceUserList[$value['RaceUserId']]['Name']:"未知用户";
                }
            }
            $AidCodeStatusList = $oAidStation->getAidCodeStatus();
            $page_url = Base_Common::getUrl('','xrace/race.stage','aid.code.list',$params)."&Page=~page~";
            $page_content =  base_common::multi($AidCodeList['AidCodeCount'], $page_url, $Page, $PageSize, 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //导出EXCEL链接
            $Unused_export_var = "<a href =".(Base_Common::getUrl('','xrace/race.stage','aid.code.list.download',array("RaceStageId"=>$RaceStageId,"AidCodeStatus"=>0)))."><导出所有未使用></a>";
            //渲染模板
            include $this->tpl('Xrace_Aid_AidCodeList');

        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //补给代码下载
    public function aidCodeListDownloadAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //赛事分站ID
            $RaceStageId = intval($this->request->RaceStageId);
            //使用状态
            $AidCodeStatus = isset($this->request->AidCodeStatus)?$this->request->AidCodeStatus:"-1";
            //分页参数
            $Page = 1;
            $PageSize = 100;
            $oAidStation = new Xrace_AidStation();
            $filename = 'xxx.txt';
            header("Content-Type: application/octet-stream");
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $Count = 1;
            do
            {
                $params = array("RaceStageId"=>$RaceStageId,"Used"=>$AidCodeStatus,"Page"=>$Page,"PageSize"=>$PageSize,"GetCount"=>0);
                $AidCodeList = $oAidStation->getAidCodeByStage($params);
                $Count = count($AidCodeList['AidCodeList']);
                foreach($AidCodeList['AidCodeList'] as $key => $value)
                {
                    $t = array($value['AidCode']);
                    echo implode(",",$t)."\r\n";
                }
                $Page++;
            }
            while($Count>0);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //补给代码生成填写页面
    public function aidCodeGenAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //赛事分站ID
            $RaceStageId = intval($this->request->RaceStageId);
            $CountArr = array(10,100,1000);
            $oAidStation = new Xrace_AidStation();
            $AidCodeTypeList = $oAidStation->getAidCodeTypeByStage($RaceStageId);
            //渲染模板
            include $this->tpl('Xrace_Aid_AidCodeGen');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //补给代码生成
    public function aidCodeGenSubmitAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //赛事分站ID
            $RaceStageId = intval($this->request->RaceStageId);
            //生成数量
            $AidCodeCount = intval($this->request->AidCodeCount);
            //分类
            $AidCodeTypeId = intval($this->request->AidCodeTypeId);
            //分页参数
            $oAidStation = new Xrace_AidStation();
            //生成代码
            $Gen = $oAidStation->genAidCode($RaceStageId,$AidCodeTypeId,$AidCodeCount);
            $response = array('errno' => 0,"Gen"=>$Gen);
            echo json_encode($response);
            return true;
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //补给代码分类创建填写页面
    public function aidCodeTypeAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //赛事分站ID
            $RaceStageId = intval($this->request->RaceStageId);
            //渲染模板
            include $this->tpl('Xrace_Aid_AidCodeTypeAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //补给代码生成
    public function aidCodeTypeInsertAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //获取 页面参数
            $bind=$this->request->from('RaceStageId','AidCodeTypeName');
            //分类名称不能为空
            if(trim($bind['AidCodeTypeName'])=="")
            {
                $response = array('errno' => 1);
            }
            else
            {
                $oAidStation = new Xrace_AidStation();
                //生成分类
                $res = $oAidStation->insertAidCodeType($bind);
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

    public function chipReturnStatusAction()
    {
        $oUser = new Xrace_UserInfo();
        //分站ID
        $RaceStageId = intval($this->request->RaceStageId);
        //分站ID
        $ReturnStatus = strlen(trim($this->request->ReturnStatus))?trim($this->request->ReturnStatus):"all";
        //分页参数
        $Page = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
        $PageSize = 20;
        //获取分站信息
        $RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,"RaceStageId,RaceStageName");
        //获取分站的芯片归还信息列表
        $ReturnStatusList = $oUser->getChipReturnStatus($RaceStageId,$ReturnStatus,$Page,$PageSize);
        foreach($ReturnStatusList["StatusList"] as $key => $value)
        {
            $ReturnStatusList["StatusList"][$key]['StatusUrl'] = "<a href='".Base_Common::getUrl('','xrace/race.stage','chip.return.status',array('RaceStageId'=>$RaceStageInfo['RaceStageId'],'ReturnStatus'=>$key)) ."'>".$value['ChipCount']."</a>";
        }
        //初始化空的比赛列表，分组列表，选手列表
        $RaceList = array();
        $RaceGroupList = array();
        $RaceUserList = array();
        foreach($ReturnStatusList["StatusList"] as $key => $value)
        {
            $ReturnStatusList["StatusList"][$key]['StatusUrl'] = "<a href='".Base_Common::getUrl('','xrace/race.stage','chip.return.status',array('RaceStageId'=>$RaceStageInfo['RaceStageId'],'ReturnStatus'=>$key)) ."'>".$value['ChipCount']."</a>";
        }
        foreach($ReturnStatusList["ChipList"] as $Status => $StatusInfo)
        {
            foreach($StatusInfo as $ChipId => $ChipInfo)
            {
                foreach($ChipInfo as $ApplyId => $ApplyInfo)
                {
                    if(!isset($RaceGroupList[$ApplyInfo['RaceGroupId']]))
                    {
                        $RaceGroupList[$ApplyInfo['RaceGroupId']] = $this->oRace->getRaceGroup($ApplyInfo['RaceGroupId'],"RaceGroupId,RaceGroupName");
                    }
                    if(!isset($RaceList[$ApplyInfo['RaceId']]))
                    {
                        $RaceList[$ApplyInfo['RaceId']] = $this->oRace->getRace($ApplyInfo['RaceId'],"RaceId,RaceName");
                    }
                    if(!isset($RaceUserList[$ApplyInfo['RaceUserId']]))
                    {
                        $RaceUserList[$ApplyInfo['RaceUserId']] = $oUser->getRaceUser($ApplyInfo['RaceUserId'],"RaceUserId,Name");
                    }
                    $ReturnStatusList["ChipList"][$Status][$ChipId][$ApplyId]['RaceGroupName'] = $RaceGroupList[$ApplyInfo['RaceGroupId']]['RaceGroupName'];
                    $ReturnStatusList["ChipList"][$Status][$ChipId][$ApplyId]['RaceName'] = $RaceList[$ApplyInfo['RaceId']]['RaceName'];
                    $ReturnStatusList["ChipList"][$Status][$ChipId][$ApplyId]['Name'] = $RaceUserList[$ApplyInfo['RaceUserId']]['Name'];

                }
            }
        }
        $params = array("RaceStageId"=>$RaceStageId,"ReturnStatus"=>$ReturnStatus,"PageSize"=>$PageSize);
        $page_url = Base_Common::getUrl('','xrace/race.stage','chip.return.status',$params)."&Page=~page~";
        $page_content =  base_common::multi($ReturnStatusList["StatusList"][$ReturnStatus]["ChipCount"], $page_url, $Page, $PageSize, 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
        //渲染模板
        include $this->tpl('Xrace_Race_RaceStageChipReturnList');
    }
    public function chipStatusAction()
    {
        //芯片ID
        $ChipId = trim($this->request->ChipId);
        //分站ID
        $RaceStageId = intval($this->request->RaceStageId);
        $oUser = new Xrace_UserInfo();
        //获取报名记录
        $UserRaceApplyList = $oUser->getRaceUserList(array("RaceStageId"=>$RaceStageId,"Chip"=>1,"ChipId"=>$ChipId));
        foreach($UserRaceApplyList as $ApplyId => $ApplyInfo)
        {
            if(!isset($RaceGroupList[$ApplyInfo['RaceGroupId']]))
            {
                $RaceGroupList[$ApplyInfo['RaceGroupId']] = $this->oRace->getRaceGroup($ApplyInfo['RaceGroupId'],"RaceGroupId,RaceGroupName");
            }
            if(!isset($RaceList[$ApplyInfo['RaceId']]))
            {
                $RaceList[$ApplyInfo['RaceId']] = $this->oRace->getRace($ApplyInfo['RaceId'],"RaceId,RaceName");
            }
            if(!isset($RaceUserList[$ApplyInfo['RaceUserId']]))
            {
                $RaceUserList[$ApplyInfo['RaceUserId']] = $oUser->getRaceUser($ApplyInfo['RaceUserId'],"RaceUserId,Name");
            }
            $UserRaceApplyList[$ApplyId]['RaceGroupName'] = $RaceGroupList[$ApplyInfo['RaceGroupId']]['RaceGroupName'];
            $UserRaceApplyList[$ApplyId]['RaceName'] = $RaceList[$ApplyInfo['RaceId']]['RaceName'];
            $UserRaceApplyList[$ApplyId]['Name'] = $RaceUserList[$ApplyInfo['RaceUserId']]['Name'];
        }
        //渲染模板
        include $this->tpl('Xrace_Race_RaceStageChipStatus');
    }
    public function chipReturnAction()
    {
        //获取要操作的报名记录列表
        $ApplyList = $this->request->ApplyList;
        $oUser = new Xrace_UserInfo();
        $Success = 0;
        //循环列表
        foreach($ApplyList as $ApplyId => $ApplyInfo)
        {
            //依次归还
            $return = $oUser->ChipReturn($ApplyId);
            //如果成功
            if($return)
            {
                //成功次数累加
                $Success++;
            }
        }
        $response = $Success ? array('errno' => 0,'Success'=>$Success) : array('errno' => 9);
        echo json_encode($response);
    }
    //单场比赛的成绩单
    public function timingDetailListAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            $oUser = new Xrace_UserInfo();
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //芯片ID
            $ChipId = trim(urldecode($this->request->ChipId));
            //用户
            $RaceUserId = intval($this->request->RaceUserId);
            //页码
            $Page= intval($this->request->Page)?intval($this->request->Page):1;
            //分页大小
            $PageSize= intval($this->request->PageSize)?intval($this->request->PageSize):20;
            //是否下载
            $Download = intval($this->request->Download);
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId);
            //print_R($RaceInfo);
            //数据解包
            $RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
            //解包路径相关的信息
            $RaceInfo['RouteInfo'] = json_decode($RaceInfo['RouteInfo'],true);
            //获取成绩列表
            if(count($RaceInfo['comment']['SelectedRaceGroup']))
            {
                //分组ID
                if(count($RaceInfo['comment']['SelectedRaceGroup'])>1)
                {
                    $RaceGroupId = intval($this->request->RaceGroupId)>=0?intval($this->request->RaceGroupId):key($RaceInfo['comment']['SelectedRaceGroup']);

                }
                else
                {
                    $RaceGroupId = intval($this->request->RaceGroupId)?intval($this->request->RaceGroupId):key($RaceInfo['comment']['SelectedRaceGroup']);
                }
                $oUser = new Xrace_UserInfo();
                //获取选手名单
                $params = array('RaceId'=>$RaceInfo['RaceId'],"RaceGroupId"=>$RaceGroupId);
                $RaceUserList = $oUser->getRaceUserListByRace($params);
                if($RaceInfo["TimingType"] == "mylaps")
                {
                    //初始化空的芯片列表
                    $ChipList = array();
                    $UserUrlList = array();
                    $UserUrlList[""] = "<a href='" . Base_Common::getUrl('','xrace/race.stage','timing.detail.list',array("RaceId"=>$RaceId,"RaceGroupId"=>$RaceGroupId,"ChipId"=>"")) . "'>全部</a> ";
                    //循环报名记录
                    foreach ($RaceUserList['RaceUserList'] as $ApplyId => $ApplyInfo)
                    {
                        //如果有配置芯片数据和BIB
                        if (trim($ApplyInfo['ChipId']) && trim($ApplyInfo['BIB']))
                        {
                            //拼接字符串加入到芯片列表
                            $ChipList[$ApplyInfo['ChipId']] = "'" . $ApplyInfo['ChipId'] . "'";
                            //分别保存用户的ID,姓名和BIB
                            $UserList[$ApplyInfo['ChipId']] = $ApplyInfo;
                            $UserUrlList[$ApplyInfo['ChipId']] = $ChipId==$ApplyInfo['ChipId']?$ApplyInfo['Name']." ":"<a href='" . Base_Common::getUrl('','xrace/race.stage','timing.detail.list',array("RaceId"=>$RaceId,"RaceGroupId"=>$RaceGroupId,"ChipId"=>urldecode($ApplyInfo['ChipId']))) . "'> ".$ApplyInfo['Name']."</a> ";
                        }
                    }
                    $ChipList = isset($UserList[$ChipId])?array("'" . $ChipId . "'"):$ChipList;
                    //拼接获取计时数据的参数，注意芯片列表为空时的数据拼接
                    $params = array(
                        'StartTime'=>$RaceGroupId>0?date("Y-m-d H:i:s",strtotime($RaceInfo['comment']['SelectedRaceGroup'][$RaceGroupId]['StartTime'])+0*3600):date("Y-m-d H:i:s",strtotime($RaceInfo['StartTime'])+0*3600),
                        'EndTime'=>$RaceGroupId>0?date("Y-m-d H:i:s",strtotime($RaceInfo['comment']['SelectedRaceGroup'][$RaceGroupId]['EndTime'])+0*3600):date("Y-m-d H:i:s",strtotime($RaceInfo['EndTime'])+0*3600),
                        'prefix'=>$RaceInfo['RouteInfo']['TimePrefix'], 'pageSize'=>$PageSize,'Page'=>$Page, 'ChipList'=>count($ChipList) ? implode(",",$ChipList):"-1",
                        'sorted'=>1,'revert'=>1,'getCount'=>1);
                    $oMylaps = new Xrace_Mylaps();
                    //获取计时数据
                    $TimingList = $oMylaps->getTimingData($params);
                    foreach($TimingList['Record'] as $RecordId => $Record)
                    {
                        $TimingList['Record'][$RecordId]['time'] = date("Y-m-d H:i:s",sprintf("%0.3f", $Record['time'])-0*3600).strstr($Record['time'], '.');
                        $TimingList['Record'][$RecordId]['Name'] = $UserList[$Record['Chip']]['Name'];
                    }
                }
                else
                {
                    //初始化空的芯片列表
                    $UserList= array();
                    $UserUrlList = array();
                    $UserUrlList[""] = "<a href='" . Base_Common::getUrl('','xrace/race.stage','timing.detail.list',array("RaceId"=>$RaceId,"RaceGroupId"=>$RaceGroupId,"RaceUserId"=>0)) . "'>全部</a> ";
                    //循环报名记录
                    foreach ($RaceUserList['RaceUserList'] as $ApplyId => $ApplyInfo)
                    {
                        //如果有配置芯片数据和BIB
                        if (trim($ApplyInfo['BIB']))
                        {
                            //拼接字符串加入到芯片列表
                            $UserList[$ApplyInfo['RaceUserId']] = "'" . $ApplyInfo['RaceUserId'] . "'";
                            //分别保存用户的ID,姓名和BIB
                            $RaceUserList[$ApplyInfo['RaceUserId']] = $ApplyInfo;
                            $UserUrlList[$ApplyInfo['RaceUserId']] = $RaceUserId==$ApplyInfo['RaceUserId']?$ApplyInfo['Name']." ":"<a href='" . Base_Common::getUrl('','xrace/race.stage','timing.detail.list',array("RaceId"=>$RaceId,"RaceGroupId"=>$RaceGroupId,"RaceUserId"=>$ApplyInfo['RaceUserId'])) . "'> ".$ApplyInfo['Name']."</a> ";
                        }
                    }
                    $UserList = isset($UserList[$RaceUserId])?array("'" . $RaceUserId . "'"):$UserList;
                    //拼接获取计时数据的参数，注意芯片列表为空时的数据拼接
                    $params = array(
                        'StartTime'=>$RaceGroupId>0?date("Y-m-d H:i:s",strtotime($RaceInfo['comment']['SelectedRaceGroup'][$RaceGroupId]['StartTime'])+0*3600):date("Y-m-d H:i:s",strtotime($RaceInfo['StartTime'])+0*3600),
                        'EndTime'=>$RaceGroupId>0?date("Y-m-d H:i:s",strtotime($RaceInfo['comment']['SelectedRaceGroup'][$RaceGroupId]['EndTime'])+0*3600):date("Y-m-d H:i:s",strtotime($RaceInfo['EndTime'])+0*3600),
                        'prefix'=>$RaceInfo['RouteInfo']['TimePrefix'], 'pageSize'=>$PageSize,'Page'=>$Page, 'UserList'=>count($UserList) ? implode(",",$UserList):"-1",
                        'sorted'=>1,'revert'=>1,'getCount'=>1);
                    $oWechatTiming = new Xrace_WechatTiming();
                    //获取计时数据
                    $TimingList = $oWechatTiming->getTimingData($params);
                    $ManagerList = array();
                    foreach($TimingList['Record'] as $RecordId => $Record)
                    {
                        $TimingList['Record'][$RecordId]['time'] = date("Y-m-d H:i:s",sprintf("%0.3f", $Record['time'])).strstr($Record['time'], '.');
                        $TimingList['Record'][$RecordId]['Name'] = $RaceUserList[$Record['RaceUserId']]['Name'];
                        //数据解包
                        $Record["comment"] = json_decode($Record["comment"],true);
                        if(intval($Record["comment"]["ManagerId"]) == 0)
                        {
                            $TimingList['Record'][$RecordId]["ScanSource"] = "选手自主扫描";
                        }
                        else
                        {
                            if(!isset($ManagerList[$Record["comment"]["ManagerId"]]))
                            {
                                $ManagerInfo = $this->manager->get($Record["comment"]["ManagerId"]);
                                $ManagerList[$Record["comment"]["ManagerId"]] = $ManagerInfo;
                            }
                            else
                            {
                                $ManagerInfo = $ManagerList[$Record["comment"]["ManagerId"]];
                            }
                            $TimingList['Record'][$RecordId]["ScanSource"] = "管理员：".$ManagerInfo["name"];
                        }
                    }
                }
                $page_url = Base_Common::getUrl('','xrace/race.stage','timing.detail.list',array("RaceId"=>$RaceId,"RaceGroupId"=>$RaceGroupId,"Page"=>$Page,"PageSize"=>$PageSize,"ChipId"=>urldecode($ChipId)))."&Page=~page~";
                $page_content =  base_common::multi($TimingList['RecordCount'], $page_url, $Page, $PageSize, 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
                //下载链接
                $DownloadUrl = "<a href='" . Base_Common::getUrl('', 'xrace/race.stage', 'race.result.list', array('RaceId' => $RaceId, 'RaceGroupId' => $RaceGroupId,'Download'=>1)) . "'> 下载</a>";
                //循环比赛已经开设的分组列表
                foreach($RaceInfo['comment']['SelectedRaceGroup'] as $GroupId => $GroupInfo)
                {
                    $RaceGroupInfo = $this->oRace->getRaceGroup($GroupId,"RaceGroupId,RaceGroupName");
                    if($RaceGroupInfo['RaceGroupId'])
                    {
                        $RaceGroupList[$GroupId]["RaceGroupInfo"] = $RaceGroupInfo;
                        if($RaceGroupId == $RaceGroupInfo['RaceGroupId'])
                        {
                            $RaceGroupList[$GroupId]['DownloadUrl'] = $RaceGroupInfo['RaceGroupName'].$DownloadUrl;
                        }
                        else
                        {
                            $RaceGroupList[$GroupId]['DownloadUrl'] = "<a href='" . Base_Common::getUrl('', 'xrace/race.stage', 'timing.detail.list', array('RaceId' => $RaceId, 'RaceGroupId' => $GroupId)) . "'>" . $RaceGroupInfo['RaceGroupName'] . "</a>";
                        }
                    }
                }
                $DownloadUrl = "<a href='" . Base_Common::getUrl('', 'xrace/race.stage', 'race.result.list', array('RaceId' => $RaceId, 'RaceGroupId' => 0,'Download'=>1)) . "'> 下载</a>";
                if(count($RaceInfo['comment']['SelectedRaceGroup'])>1)
                {
                    if($RaceGroupId == 0)
                    {
                        $RaceGroupList[0]['DownloadUrl'] = "全场".$DownloadUrl;
                    }
                    else
                    {
                        $RaceGroupList[0]['DownloadUrl'] = "<a href='" . Base_Common::getUrl('', 'xrace/race.stage', 'timing.detail.list', array('RaceId' => $RaceId, 'RaceGroupId' => 0)) . "'>" . "全场" . "</a>";
                    }
                    ksort($RaceGroupList);
                }
            }
            else
            {
                //分组ID
                $RaceGroupId = intval($this->request->RaceGroupId);
                $oUser = new Xrace_UserInfo();
                //获取选手名单
                $params = array('RaceId'=>$RaceInfo['RaceId'],"RaceGroupId"=>$RaceGroupId);
                $RaceUserList = $oUser->getRaceUserListByRace($params);
                //初始化空的芯片列表
                $ChipList = array();
                $UserUrlList = array();
                $UserUrlList[0] = "<a href='" . Base_Common::getUrl('','xrace/race.stage','timing.detail.list',array("RaceId"=>$RaceId,"RaceGroupId"=>$RaceGroupId,"ChipId"=>urldecode($ApplyInfo['ChipId']))) . "'>全部</a> ";
                //循环报名记录
                foreach ($RaceUserList['RaceUserList'] as $ApplyId => $ApplyInfo)
                {
                    //如果有配置芯片数据和BIB
                    if (trim($ApplyInfo['ChipId']) && trim($ApplyInfo['BIB']))
                    {
                        //拼接字符串加入到芯片列表
                        $ChipList[] = "'" . $ApplyInfo['ChipId'] . "'";
                        //分别保存用户的ID,姓名和BIB
                        $UserList[$ApplyInfo['ChipId']] = $ApplyInfo;
                        $UserUrlList[$ApplyInfo['ChipId']] = $ChipId==$ApplyInfo['ChipId']?$ApplyInfo['Name']." ":"<a href='" . Base_Common::getUrl('','xrace/race.stage','timing.detail.list',array("RaceId"=>$RaceId,"RaceGroupId"=>$RaceGroupId,"ChipId"=>urldecode($ApplyInfo['ChipId']))) . "'> ".$ApplyInfo['Name']."</a> ";
                    }
                }
                $ChipList = isset($UserList[$ChipId])?array("'" . $ChipId . "'"):$ChipList;
                $oMylaps = new Xrace_Mylaps();
                //拼接获取计时数据的参数，注意芯片列表为空时的数据拼接
                $params = array(
                    'StartTime'=>date("Y-m-d H:i:s",strtotime($RaceInfo['StartTime'])+0*3600),
                    'EndTime'=>date("Y-m-d H:i:s",strtotime($RaceInfo['EndTime'])+0*3600),
                    'prefix'=>$RaceInfo['RouteInfo']['TimePrefix'],  'pageSize'=>$PageSize,'Page'=>$Page,
                    'ChipList'=>count($ChipList) ? implode(",",$ChipList):"-1",
                    'sorted'=>1,'revert'=>1,'getCount'=>1);
                //获取计时数据
                $TimingList = $oMylaps->getTimingData($params);
                $ManagerList = array();
                foreach($TimingList['Record'] as $RecordId => $Record)
                {
                    $TimingList['Record'][$RecordId]['time'] = date("Y-m-d H:i:s",sprintf("%0.3f", $Record['time'])-0*3600).strstr($Record['time'], '.');
                    $TimingList['Record'][$RecordId]['Name'] = $UserList[$Record['Chip']]['Name'];
                }
                $page_url = Base_Common::getUrl('','xrace/race.stage','timing.detail.list',array("RaceId"=>$RaceId,"Page"=>$Page,"PageSize"=>$PageSize,"ChipId"=>urldecode($ChipId)))."&Page=~page~";
                $page_content =  base_common::multi($TimingList['RecordCount'], $page_url, $Page, $PageSize, 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            }
            if($RaceInfo["TimingType"] == "mylaps")
            {
                //渲染模板
                include $this->tpl('Xrace_Race_TimingDetailMylaps');
            }
            else
            {
                //渲染模板
                include $this->tpl('Xrace_Race_TimingDetailWechat');
            }

        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //批量DNF
    public function raceDnfAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oUser = new Xrace_UserInfo();
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //获取成绩列表
            $RaceResultList = $this->oRace->getRaceResult($RaceId);
            $Success = 0;
            //循环成绩总表
            foreach($RaceResultList['UserRaceInfo']['Total'] as $key => $UserInfo)
            {
                //如果用户未完赛
                if($UserInfo['Finished'] == 0)
                {
                    //获取用户报名记录
                    $UserApplyInfo = $oUser->getRaceApplyUserInfoByUser($RaceId, $UserInfo['RaceUserId']);
                    //更新数据
                    $res = $oUser->UserRaceDNF($UserApplyInfo['ApplyId'], "未在结束时间前完赛", $this->manager->id);
                }
            }
            //返回之前页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //批量DNS
    public function raceDnsAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oUser = new Xrace_UserInfo();
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //获取成绩列表
            $RaceResultList = $this->oRace->getRaceResult($RaceId);
            //获取选手列表
            $RaceUserList = $oUser->getRaceUserListByRace(array("RaceId"=>$RaceId,"Cache"=>0));
            $Success = 0;
            foreach($RaceUserList['RaceUserList'] as $key_1 => $RaceUserInfo)
            {
                //循环成绩总表
                foreach($RaceResultList['UserRaceInfo']['Total'] as $key => $UserInfo)
                {
                    if($RaceUserInfo['RaceUserId'] == $UserInfo['RaceUserId'])
                    {
                        unset($RaceUserList['RaceUserList'][$key_1]);
                        break;
                    }
                }
            }
            foreach($RaceUserList['RaceUserList'] as $key_1 => $RaceUserInfo)
            {
                //获取用户报名记录
                $UserApplyInfo = $oUser->getRaceApplyUserInfoByUser($RaceId, $RaceUserInfo['RaceUserId']);
                //更新数据
                $res = $oUser->UserRaceDNS($UserApplyInfo['ApplyId'], "未参加比赛", $this->manager->id);
            }
            //返回之前页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //套餐添加填写页面
    public function raceStageDataAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //分站ID
            $RaceStageId = intval($this->request->RaceStageId);
            $RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,"RaceStageId,RaceStageName");
            $DataList = $this->oRace->getRaceStageDataByUrl($RaceStageId);
            include('Third/fusion/Includes/FusionCharts_Gen.php');

            $FC4 = new FusionCharts("Pie2d",'100%','400');
            $FC4->setSWFPath( '../Charts/');
            $strParam="caption='比赛人数统计';baseFontSize=12;numberPrefix=;numberSuffix=人次;decimalPrecision=0;showValues=1;formatNumberScale=0;rotateNames=0;showAlternateHGridColor=1;alternateHGridAlpha=5;alternateHGridColor='CC3300';hoverCapSepChar=，";
            $FC4->setChartParams($strParam);
            $FC4->addDataset("人数统计");
            foreach($DataList['Data']['DataList']['RaceList'] as $RaceId => $RaceInfo)
            {
                $FC4->addChartData($RaceInfo['Data']['RaceUser'],"name=".$RaceInfo['RaceName']);
            }
            # Create Multiseries ColumnD chart object using FusionCharts PHP Class
            $FC = new FusionCharts("MSLine",'100%','400');
            # Set the relative path of the swf file
            $FC->setSWFPath( '../Charts/');
            $Step=1;
            # Store chart attributes in a variable
            $strParam="caption='用户身份类型统计';xAxisName='比赛';baseFontSize=12;numberPrefix=;numberSuffix=次;decimalPrecision=0;showValues=0;formatNumberScale=0;labelStep=".$Step.";rotateNames=1;yAxisMinValue=0;yAxisMaxValue=100;numDivLines=9;showAlternateHGridColor=1;alternateHGridAlpha=5;alternateHGridColor='CC3300';hoverCapSepChar=，";
            foreach($DataList['Data']['DataList']['RaceList'] as $RaceId => $RaceInfo)
            {
                $FC->addCategory($RaceInfo['RaceName']);
            }
            $FC->addDataset("到场比例");
            foreach($DataList['Data']['DataList']['RaceList'] as $RaceId => $RaceInfo)
            {
                $RaceRate = sprintf("%3.2f",$RaceInfo['Data']['RacedUser']/$RaceInfo['Data']['RaceUser']*100);
                $FC->addChartData($RaceRate);
                $DataList['Data']['DataList']['RaceList'][$RaceId]['Data']['RaceRate'] = $RaceRate."%";
            }
            $FC->addDataset("完赛比例");
            foreach($DataList['Data']['DataList']['RaceList'] as $RaceId => $RaceInfo)
            {
                $FinishRate = sprintf("%3.2f",$RaceInfo['Data']['FinishedUser']/$RaceInfo['Data']['RacedUser']*100);
                $FC->addChartData($FinishRate);
                $DataList['Data']['DataList']['RaceList'][$RaceId]['Data']['FinishRate'] = $FinishRate."%";
            }
            foreach($DataList['Data']['DataList']['RaceList'] as $RaceId => $RaceInfo)
            {
                foreach($RaceInfo['RaceGroupList'] as $RaceGroupId => $RaceGroupInfo)
                {
                    $RaceRate = sprintf("%3.2f",$RaceGroupInfo['Data']['RacedUser']/$RaceGroupInfo['Data']['RaceUser']*100);
                    $DataList['Data']['DataList']['RaceList'][$RaceId]['RaceGroupList'][$RaceGroupId]['Data']['RaceRate'] = $RaceRate."%";
                    $FinishRate = sprintf("%3.2f",$RaceGroupInfo['Data']['FinishedUser']/$RaceGroupInfo['Data']['RacedUser']*100);
                    $DataList['Data']['DataList']['RaceList'][$RaceId]['RaceGroupList'][$RaceGroupId]['Data']['FinishRate'] = $FinishRate."%";
                }
            }
            $RaceRate = sprintf("%3.2f",$DataList['Data']['DataList']['Total']['RacedUser']/$DataList['Data']['DataList']['Total']['RaceUser']*100);
            $DataList['Data']['DataList']['Total']['RaceRate'] = $RaceRate."%";
            $FinishRate = sprintf("%3.2f",$DataList['Data']['DataList']['Total']['FinishedUser']/$DataList['Data']['DataList']['Total']['RacedUser']*100);
            $DataList['Data']['DataList']['Total']['FinishRate'] = $FinishRate."%";
            //渲染模板
            include $this->tpl('Xrace_Race_RaceStageData');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //更新分站的统计数据
    public function updateStageDataAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //分站ID
            $RaceStageId = intval($this->request->RaceStageId);
            $update = $this->oRace->updateRaceStageDataByUrl($RaceStageId);
            //返回原有页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //报名记录转换提交页面
    public function raceTransferSubmitAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oUser = new Xrace_UserInfo();
            //报名记录ID
            $ApplyId = intval($this->request->ApplyId);
            //获取报名记录
            $ApplyInfo = $oUser->getRaceApplyUserInfo($ApplyId);
            //获取比赛列表
            $RaceList = $this->oRace->getRaceList(array("RaceStageId"=>$ApplyInfo['RaceStageId']),"RaceId,RaceName,comment");
            $RaceGroupList = array();
            //循环已经选中的分组列表
            foreach($RaceList[$ApplyInfo['RaceId']]['comment']['SelectedRaceGroup'] as $GroupId => $GroupInfo)
            {
                //依次获取分组信息
                $RaceGroupList[$GroupId] = $this->oRace->getRaceGroup($GroupId, "RaceGroupId,RaceGroupName");
            }
            //渲染模板
            include $this->tpl('Xrace_Race_RaceTransfer');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //报名记录转换
    public function raceTransferAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            $oUser = new Xrace_UserInfo();
            //报名记录ID
            $ApplyId = intval($this->request->ApplyId);
            //获取报名记录
            $ApplyInfo = $oUser->getRaceApplyUserInfo($ApplyId);
            //复制到待更新数据
            $bind = $ApplyInfo;
            //BIB
            $bind['BIB'] = trim($this->request->BIB);
            //计时芯片ID
            $bind['ChipId'] = trim($this->request->ChipId);
            //分组ID
            $bind['RaceGroupId'] = intval($this->request->RaceGroupId);
            //比赛ID
            $bind['RaceId'] = intval($this->request->RaceId);
            //更新报名记录
            $res = $oUser->updateRaceUserApply($ApplyId,$bind);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
            echo json_encode($response);
            return true;
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    public function wechatTimingQrAction()
    {
        include('Third/phpqrcode/phpqrcode.php');
        //比赛ID
        $RaceId = intval($this->request->RaceId);
        //计时点标识
        $Location = trim($this->request->Location);
        $url = urlencode('http://register.xrace.cn/chip/operation/location/timing?RaceId='.$RaceId.'&Location='.$Location);
        include $this->tpl('Xrace_Race_QR');
    }
    //计时点赛段提交页面
    public function raceSegmentAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //获取计时成绩计算方式
            $RaceTimingResultTypeList = $this->oRace->getRaceTimingResultType("");
            //渲染模板
            include $this->tpl('Xrace_Race_SegmentAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加比赛分段
    public function raceSegmentInsertAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $RaceId = intval($this->request->RaceId);
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId);
            //如果有获取到比赛信息
            if(isset($RaceInfo['RaceId']))
            {
                //获取 页面参数
                $bind = $this->request->from('RaceId','StartId','EndId','SegmentName','ResultType','NeedFinish');
                $bind['comment'] = array("NeedFinish"=>$bind["NeedFinish"]);
                $bind['comment'] = json_encode($bind['comment']);
                unset($bind['NeedFinish']);
                //新增赛段
                $res = $this->oRace->insertSegement($bind);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
            echo json_encode($response);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改比赛分段提交页面
    public function raceSegmentModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
        if($PermissionCheck['return'])
        {
            //赛段ID
            $SegmentId = intval($this->request->SegmentId);
            //获取赛段信息
            $SegmentInfo = $this->oRace->getSegment($SegmentId);
            //如果有获取到比赛信息
            if(isset($SegmentInfo['SegmentId']))
            {
                //数组解压
                $SegmentInfo["comment"] = json_decode($SegmentInfo["comment"],true);
                //获取计时成绩计算方式
                $RaceTimingResultTypeList = $this->oRace->getRaceTimingResultType("");
            }
            //渲染模板
            include $this->tpl('Xrace_Race_SegmentModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加比赛分段
    public function raceSegmentUpdateAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("TimingPointModify");
        if($PermissionCheck['return'])
        {
            //比赛ID
            $SegmentId = intval($this->request->SegmentId);
            //获取比赛信息
            $SegmentInfo = $this->oRace->getSegment($SegmentId);
            //如果有获取到比赛信息
            if(isset($SegmentInfo['SegmentId']))
            {
                //获取 页面参数
                $bind = $this->request->from('StartId','EndId','SegmentName','ResultType','NeedFinish');
                $bind['comment'] = array("NeedFinish"=>$bind["NeedFinish"]);
                $bind['comment'] = json_encode($bind['comment']);
                unset($bind['NeedFinish']);
                //更新赛段
                $res = $this->oRace->updateSegment($SegmentId,$bind);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
            echo json_encode($response);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}

<?php
/**
 * 赛事管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Xrace_RaceCatalogController extends AbstractController
{
	/**赛事相关:RaceCatalog
	 * @var string
	 */
	protected $sign = '?ctl=xrace/race.catalog';
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
	//赛事列表页面
	public function indexAction()
	{
	    //检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
            //$upload = \App\Libraries\Oss\OssClientFile::uploadMatchCdn($content_pic_s,"public/Public/image/".$content_pic_s);
		    $DataPermissionListWhere = $this->manager->getDataPermissionByGroupWhere();
			//当前站点根域名
			$RootUrl = "http://".$_SERVER['HTTP_HOST'];
			//获取赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0,$DataPermissionListWhere);
			foreach($RaceCatalogList as $RaceCatalogId => $RaceCatalogInfo)
			{
			    $RaceCatalogList[$RaceCatalogId]['RaceCatalogName'].=($RaceCatalogInfo['Display'])?"":"(隐藏)";
				$RaceCatalogList[$RaceCatalogId]['RankingListUrl'] = "<a href='".Base_Common::getUrl('','xrace/race.catalog','ranking.list',array('RaceCatalogId'=>$RaceCatalogId)) ."'>排名</a>";

            }
			//渲染模板
			include $this->tpl('Xrace_Race_RaceCatalogList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加赛事填写配置页面
	public function raceCatalogAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceCatalogInsert");
		if($PermissionCheck['return'])
		{
			//渲染模板
			include $this->tpl('Xrace_Race_RaceCatalogAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加新赛事
	public function raceCatalogInsertAction()
	{
		//检查权限
		$bind=$this->request->from('RaceCatalogId','RaceCatalogName','RaceCatalogComment','Display');
		//赛事名称不能为空
		if(trim($bind['RaceCatalogName'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			//文件上传
			$oUpload = new Base_Upload('RaceCatalogIcon');
			$upload = $oUpload->upload('RaceCatalogIcon');
			$res[1] = $upload->resultArr;
			$path = $res[1][1];

			//如果正确上传，就保存文件路径
			if(strlen($path['path'])>2)
			{
				$bind['comment']['RaceCatalogIcon'] = $path['path'];
				$bind['comment']['RaceCatalogIcon_root'] = $path['path_root'];
			}
			//数据压缩
			$bind['comment'] = json_encode($bind['comment']);
			//添加赛事记录
			$res = $this->oRace->insertRaceCatalog($bind);
            if($res)
            {
                $flag = $this->manager->insertDataPermission(0,$res);
            }
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改赛事信息页面
	public function raceCatalogModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceCatalogModify");
		if($PermissionCheck['return'])
		{
			//赛事ID
			$RaceCatalogId = trim($this->request->RaceCatalogId);
			//获取赛事信息
			$RaceCatalogInfo = $this->oRace->getRaceCatalog($RaceCatalogId,'*',0);
			//渲染模板
			include $this->tpl('Xrace_Race_RaceCatalogModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//更新赛事信息
	public function raceCatalogUpdateAction()
	{
		//获取页面参数
		$bind=$this->request->from('RaceCatalogId','RaceCatalogName','RaceCatalogComment','Display');
		//赛事名称不能为空
		if(trim($bind['RaceCatalogName'])=="")
		{
			$response = array('errno' => 1);
		}
		//赛事ID必须为正数
		elseif(intval($bind['RaceCatalogId'])<=0)
		{
			$response = array('errno' => 2);
		}
		else
		{
			//获取原有数据
			$RaceCatalogInfo = $this->oRace->getRaceCatalog($bind['RaceCatalogId'],'*',0);
			//数据解包
			$bind['comment'] = json_decode($RaceCatalogInfo['comment'],true);
			//文件上传
			$oUpload = new Base_Upload('RaceCatalogIcon');
			$upload = $oUpload->upload('RaceCatalogIcon');
			$res[1] = $upload->resultArr;
			$path = isset( $res[1][1] ) ? $res[1][1]:array('path'=>"");
            /*
			include('Third/oss/OssClientFile.php');
            $oss = app\lib\Third\oss\ossClientFile::uploadMatchCdn($path['path_root'],$path['path'],$this->config->oss);
            $url = $oss['info']['url'];
            */
			//如果正确上传，就保存文件路径
			if(strlen($path['path'])>2)
			{
				$bind['comment']['RaceCatalogIcon'] = $path['path'];
				$bind['comment']['RaceCatalogIcon_root'] = $path['path_root'];
			}
			//数据压缩
			$bind['comment'] = json_encode($bind['comment']);
			//修改赛事记录
			$res = $this->oRace->updateRaceCatalog($bind['RaceCatalogId'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	//更新赛事信息
	public function raceCatalogDisclaimerUpdateAction()
	{
		//获取页面参数
		$bind=$this->request->from('RaceCatalogId','Disclaimer');
		$bind['Disclaimer'] = urlencode($bind['Disclaimer']);
		//修改赛事记录
		$res = $this->oRace->updateRaceCatalog($bind['RaceCatalogId'],$bind);
		$response = $res ? array('errno' => 0) : array('errno' => 9);
		echo json_encode($response);
		return true;
	}
	//修改赛事免责声明页面
	public function raceCatalogDisclaimerModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceCatalogModify");
		if($PermissionCheck['return'])
		{
			//赛事ID
			$RaceCatalogId = trim($this->request->RaceCatalogId);
			//获取赛事信息
			$RaceCatalogInfo = $this->oRace->getRaceCatalog($RaceCatalogId,'RaceCatalogId,RaceCatalogName,Disclaimer',0);
			//渲染模板
			include $this->tpl('Xrace_Race_RaceCatalogDisclaimerModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//删除赛事
	public function raceCatalogDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceCatalogDelete");
		if($PermissionCheck['return'])
		{
			//赛事ID
			$RaceCatalogId = intval($this->request->RaceCatalogId);
			//删除赛事记录
			$this->oRace->deleteRaceCatalog($RaceCatalogId);
			//返回原有页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //排名列表
    public function rankingListAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceCatalogModify");
        if($PermissionCheck['return'])
        {
            $oRanking = new Xrace_Ranking();
            //获取排名计算方式
            $RankingTypeList = $oRanking->getTotalRankingType();
            //赛事ID
            $RaceCatalogId = intval($this->request->RaceCatalogId);
            //获取赛事信息
            $RaceCatalogInfo = $this->oRace->getRaceCatalog($RaceCatalogId,'*',0);
            //获取赛事下的排名列表
            $RankingList = $oRanking->getRankingList(array("RaceCatalogId"=>$RaceCatalogId));
            foreach($RankingList as $RankingId => $RankingInfo)
            {
                $RankingList[$RankingId]['RankingTypeName'] = $RankingTypeList[$RankingInfo['RankingType']];
                $RankingList[$RankingId]['RankingRaceListUrl'] = "<a href='".Base_Common::getUrl('','xrace/race.catalog','ranking.race.list',array('RankingId'=>$RankingId)) ."'>详情</a>";

            }
            //渲染模板
            include $this->tpl('Xrace_Race_RankingList');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改排名信息页面
    public function rankingModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceCatalogModify");
        if($PermissionCheck['return'])
        {
            $oRanking = new Xrace_Ranking();
            //获取排名计算方式
            $RankingTypeList = $oRanking->getTotalRankingType();
            //赛事ID
            $RankingId = trim($this->request->RankingId);
            //获取排名信息
            $RankingInfo = $oRanking->getRanking($RankingId);
            //渲染模板
            include $this->tpl('Xrace_Race_RankingModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加排名信息页面
    public function rankingAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceCatalogModify");
        if($PermissionCheck['return'])
        {
            $oRanking = new Xrace_Ranking();
            //赛事ID
            $RaceCatalogId = trim($this->request->RaceCatalogId);
            //获取排名计算方式
            $RankingTypeList = $oRanking->getTotalRankingType();
            //渲染模板
            include $this->tpl('Xrace_Race_RankingAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //更新赛事信息
    public function rankingInsertAction()
    {
        //获取页面参数
        $bind=$this->request->from('RankingName','RaceCatalogId','RankingComment',"RankingType");
        //赛事名称不能为空
        if(trim($bind['RankingName'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            $oRanking = new Xrace_Ranking();
            //修改赛事记录
            $res = $oRanking->insertRanking($bind);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //更新排名信息
    public function rankingUpdateAction()
    {
        //获取页面参数
        $bind=$this->request->from('RankingName','RankingId','RankingComment',"RankingType");
        //赛事名称不能为空
        if(trim($bind['RankingName'])=="")
        {
            $response = array('errno' => 1);
        }
        //赛事ID必须为正数
        elseif(intval($bind['RankingId'])<=0)
        {
            $response = array('errno' => 2);
        }
        else
        {
            $oRanking = new Xrace_Ranking();
            //获取原有数据
            $RankingInfo = $oRanking->getRanking($bind['RankingId']);
            //修改排名记录
            $res = $oRanking->updateRanking($bind['RankingId'],$bind);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //删除赛事
    public function rankingDeleteAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceCatalogModify");
        if($PermissionCheck['return'])
        {
            $oRanking = new Xrace_Ranking();
            //赛事ID
            $RankingId = intval($this->request->RankingId);
            //删除赛事记录
            $oRanking->deleteRanking($RankingId);
            //返回原有页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改排名对应比赛-分组列表页面
    public function rankingRaceListAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceCatalogModify");
        if($PermissionCheck['return'])
        {
            $DataPermissionListWhere = $this->manager->getDataPermissionByGroupWhere();
            $oRanking = new Xrace_Ranking();
            //赛事ID
            $RankingId = trim($this->request->RankingId);
            //获取赛事信息
            $RankingInfo = $oRanking->getRanking($RankingId);
            $RankingListUrl = "<a href='".Base_Common::getUrl('','xrace/race.catalog','ranking.list',array('RaceCatalogId'=>$RankingInfo['RaceCatalogId'])) ."'>返回</a>";
            //赛事分站列表
            $RaceStageList = $this->oRace->getRaceStageList($RankingInfo['RaceCatalogId'],"RaceStageId,RaceStageName,RaceCatalogId,StageStartDate,StageEndDate,comment",0,$DataPermissionListWhere);
            //比赛-分组的层级规则
            $RaceStructureList  = $this->oRace->getRaceStructure();
            //赛事分组列表
            $RaceGroupList = $this->oRace->getRaceGroupList($RankingInfo['RaceCatalogId'],'RaceGroupId,RaceGroupName');
            //获取比赛列表
            $OldRaceList = $oRanking->getRankingRaceList(array("RankingId"=>$RankingId));
            foreach($RaceStageList as $RaceStageId => $RaceStageInfo)
            {
                //获取比赛结构名称
                $RaceStageList[$RaceStageId]['RaceStructureName'] = $RaceStructureList[$RaceStageInfo['comment']['RaceStructure']];
                if($RaceStageInfo['comment']['RaceStructure']=="race")
                {
                    //获取比赛列表
                    $RaceList  =  $this->oRace->getRaceList(array("RaceStageId"=>$RaceStageId),"RaceId,RaceName,comment");
                    foreach($RaceList as $RaceId => $RaceInfo)
                    {
                        $RaceStageList[$RaceStageId]['RaceList'][$RaceId] = array("RaceId"=>$RaceId,"RaceName"=>$RaceInfo['RaceName'],"RaceGroupList"=>array());
                        if(count($RaceInfo['comment']['SelectedRaceGroup'])>0)
                        {
                            foreach($RaceInfo['comment']['SelectedRaceGroup'] as $RaceGroupId => $RaceGroupInfo)
                            {
                                if($RaceGroupInfo['Selected']>0)
                                {
                                    if($RankingInfo["RankingType"]=="credit")
                                    {
                                        $RaceStageList[$RaceStageId]['RaceList'][$RaceId]['RaceGroupList'][$RaceGroupId] = array("RaceGroupId"=>$RaceGroupId,"RaceGroupName"=>$RaceGroupList[$RaceGroupId]['RaceGroupName'],"RankingType"=>isset($OldRaceList[$RaceId][$RaceGroupId])?$OldRaceList[$RaceId][$RaceGroupId]["RankingType"]:"","selected"=>isset($OldRaceList[$RaceId][$RaceGroupId])?$OldRaceList[$RaceId][$RaceGroupId]["RankingTypeId"]:0);
                                    }
                                    elseif($RankingInfo["RankingType"]=="time")
                                    {
                                        $RaceStageList[$RaceStageId]['RaceList'][$RaceId]['RaceGroupList'][$RaceGroupId] = array("RaceGroupId"=>$RaceGroupId,"RaceGroupName"=>$RaceGroupList[$RaceGroupId]['RaceGroupName'],"RankingType"=>isset($OldRaceList[$RaceId][$RaceGroupId])?$OldRaceList[$RaceId][$RaceGroupId]["RankingType"]:"","selected"=>isset($OldRaceList[$RaceId][$RaceGroupId])?1:0);
                                    }
                                }
                            }
                        }
                        else
                        {
                            unset($RaceStageList[$RaceStageId]['RaceList'][$RaceId]);
                        }
                    }
                }
                else
                {
                    //获取比赛列表
                    $RaceList  =  $this->oRace->getRaceList(array("RaceStageId"=>$RaceStageId),"RaceId,RaceGroupId,RaceName,comment");
                    foreach($RaceList as $RaceId => $RaceInfo)
                    {
                        if(!isset($RaceStageList[$RaceStageId]['RaceGroupList'][$RaceInfo['RaceGroupId']]))
                        {
                            $RaceStageList[$RaceStageId]['RaceGroupList'][$RaceInfo['RaceGroupId']] = array("RaceGroupId"=>$RaceInfo['RaceGroupId'],"RaceGroupName"=>$RaceGroupList[$RaceInfo['RaceGroupId']]['RaceGroupName']);
                        }
                        $RaceStageList[$RaceStageId]['RaceGroupList'][$RaceInfo['RaceGroupId']]['RaceList'][$RaceId] = array("RaceId"=>$RaceId,"RaceName"=>$RaceInfo['RaceName'],"RankingType"=>isset($OldRaceList[$RaceId][$RaceInfo['RaceGroupId']])?$OldRaceList[$RaceId][$RaceInfo['RaceGroupId']]:"","selected"=>isset($OldRaceList[$RaceId][$RaceInfo['RaceGroupId']])?1:0);
                    }
                }
            }
            if($RankingInfo["RankingType"]=="credit")
            {
                $oCredit = new Xrace_Credit();
                $CreditList = $oCredit->getCreditList($RankingInfo['RaceCatalogId']);
            }
            elseif($RankingInfo["RankingType"]=="time")
            {
                //获取排名计算方式
                $RankingTypeList = $oRanking->getRankingType();
                $RankingTypeList["time"][""] = "无";
            }
            //渲染模板
            include $this->tpl('Xrace_Race_RankingRaceList'.$RankingInfo['RankingType']);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //更新排名信息
    public function rankingRaceListModifyAction()
    {
        //获取页面参数
        $bind=$this->request->from('RankingId','RaceList');
        $oRanking = new Xrace_Ranking();
        $update = $oRanking->updateRaceListByRanking($bind['RankingId'],$bind['RaceList']);
        $response = array('errno' => 0);
        echo json_encode($response);
        return true;
    }
    //更新总排名的用户数据
    public function updateRaceUserListByRankingAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceCatalogModify");
        if($PermissionCheck['return'])
        {
            $oRanking = new Xrace_Ranking();
            //排名ID
            $RankingId = trim($this->request->RankingId);
            //更新总排名数据
            $update = $oRanking->updateRaceUserListByRanking($RankingId);
            //返回原有页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }

    //总排名对应的成绩单
    public function getRaceUserListByRankingAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceCatalogModify");
        if($PermissionCheck['return'])
        {
            $oRanking = new Xrace_Ranking();
            //排名ID
            $RankingId = trim($this->request->RankingId);
            //获取赛事信息
            $RankingInfo = $oRanking->getRanking($RankingId,"RankingType,RankingId");
            //获取排名数据
            $RaceUserList = $oRanking->getRaceInfoByRanking($RankingId);
            //初始化空的比赛和分组列表
            $RaceList = array();$RaceGroupList = array();$RaceStageList = array();
            //循环用户数据
            foreach($RaceUserList['RaceUserList']['UserList'] as $key => $UserInfo)
            {
                //循环比赛列表
                foreach($UserInfo['RaceList'] as $key2 => $Race)
                {
                    if(!isset($RaceList[$UserInfo['RaceId']]))
                    {
                        //获取比赛数据
                        $RaceList[$Race['RaceId']] = $this->oRace->getRace($Race['RaceId'],"RaceId,RaceName");
                    }
                    if(!isset($RaceGroupList[$UserGroupInfo['RaceGroupId']]))
                    {
                        //获取分组数据
                        $RaceGroupList[$Race['RaceGroupId']] = $this->oRace->getRaceGroup($Race['RaceGroupId'],"RaceGroupId,RaceGroupName");
                    }
                    if(!isset($RaceStageList[$Race['RaceStageId']]))
                    {
                        //获取分组数据
                        $RaceStageList[$Race['RaceStageId']] = $this->oRace->getRaceStage($Race['RaceStageId'],"RaceStageId,RaceStageName");
                    }
                }
            }
            if($RankingInfo['RankingType']=="time")
            {
                $module = "ResultListRankingTime";
            }
            elseif($RankingInfo['RankingType']=="credit")
            {
                $module = "ResultListRankingCredit";
            }
            //渲染模板
            include $this->tpl('Xrace_Race_'.$module);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}

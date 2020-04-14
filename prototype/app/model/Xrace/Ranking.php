<?php
/**
 * 赛事排名配置相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_Ranking extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_total_ranking';
    protected $table_race = 'config_ranking_race';
    protected $rankingType = array('time'=>array('gunshot'=>'发枪时间','net'=>'净时间'),'credit'=>'');
    protected $totalRankingType = array("time"=>"时间","credit"=>"积分");
    public function getTotalRankingType()
    {
        return $this->totalRankingType;
    }

    public function getRankingType()
    {
        return $this->rankingType;
    }
	//更新单个排名
	public function updateRanking($RankingId, array $bind)
	{
        $RankingId = intval($RankingId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`RankingId` = ?', $RankingId);
	}
    //更新单个排名对应的比赛
    public function updateRankingRace($RankingId,$RaceId,$RaceGroupId, array $bind)
    {
        $RankingId = intval($RankingId);
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        return $this->db->update($table_to_process, $bind, '`RankingId` = ? and `RaceId` = ? and `RaceGroupId` = ?', array($RankingId,$RaceId,$RaceGroupId));
    }
	//添加单个排名
	public function insertRanking(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}
    //添加单个排名对应的比赛
    public function insertRankingRace(array $bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        return $this->db->insert($table_to_process, $bind);
    }
	//删除单个排名
	public function deleteRanking($RankingId)
	{
		$RankingId = intval($RankingId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`RankingId` = ?', $RankingId);
	}
    //删除单个排名
    public function deleteRankingRace($RankingId,$RaceId,$RaceGroupId)
    {
        $RankingId = intval($RankingId);
        $RaceGroupId = intval($RaceGroupId);
        $RaceId = intval($RaceId);
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        return $this->db->delete($table_to_process, '`RankingId` = ? and `RaceId` = ? and `RaceGroupId` = ?', array($RankingId,$RaceId,$RaceGroupId));
    }
	//获取排名列表
	public function getRankingList($params,$fields = "*")
	{
		//初始化查询条件
        $whereCatalog = (isset($params['RaceCatalogId']) && ($params['RaceCatalogId'] >0))?(" RaceCatalogId = '".$params['RaceCatalogId'])."'":"";
		$whereCondition = array($whereCatalog);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
        $table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . "  where 1 ".$where." ORDER BY RaceCatalogId desc,RankingId asc";
		$return = $this->db->getAll($sql);
		$RankingList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
			    $RankingList[$value['RankingId']] = $value;
			}
		}
		return $RankingList;
	}
	//获取单个赛事组别的信息
	public function getRanking($RankingId, $fields = '*')
	{
		$RankingId = intval($RankingId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`RankingId` = ?', $RankingId);
	}
    //获取比赛可能关联的排名数据
    public function getRankingByRace($RaceId,$RaceGroupId, $fields = '*')
    {
        $RaceGroupId = intval($RaceGroupId);
        $RaceId = intval($RaceId);
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        return $this->db->select($table_to_process, $fields, '`RaceId` = ? and `RaceGroupId` = ?', array($RaceId,$RaceGroupId));
    }
    //获取排名对应的比赛列表
    public function getRankingRaceList($params,$fields = "*")
    {
        //初始化查询条件
        $whereRanking = (isset($params['RankingId']) && ($params['RankingId'] >0))?(" RankingId = '".$params['RankingId'])."'":"";
        $whereCondition = array($whereRanking);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        $sql = "SELECT $fields FROM " . $table_to_process . "  where 1 ".$where." ORDER BY RaceId,RaceGroupId asc";
        $return = $this->db->getAll($sql);
        $RankingList = array();
        if(count($return))
        {
            foreach($return as $key => $value)
            {
                $RaceList[$value['RaceId']][$value['RaceGroupId']] = array("RankingType"=>$value['RankingType'],"RankingTypeId"=>$value["RankingTypeId"]);
            }
        }
        return $RaceList;
    }
    //更新某个排名对应的比赛列表
    public function updateRaceListByRanking($RankingId,$RaceList)
    {
        //获取排名信息
        $RankingInfo = $this->getRanking($RankingId);
        //获取原有数据
        $OldRaceList = $this->getRankingRaceList(array("RankingId"=>$RankingId));
        $ToDelete = array();$ToInsert = array();
        foreach($OldRaceList as $RaceId => $RaceInfo)
        {
            foreach($RaceInfo as $RaceGroupId => $value)
            {
                $found = 0;
                foreach($RaceList as $R1 => $R1Info)
                {
                    foreach($R1Info as $R2 => $v2)
                    {
                        if($R1==$RaceId && $R2 == $RaceGroupId && $v2['selected']==1)
                        {
                            $found = 1;
                            break;break;
                        }
                    }
                }
                if($found == 0)
                {
                    $ToDelete[$RaceId][$RaceGroupId] = 1;
                }
            }
        }
        foreach($RaceList as $RaceId => $RaceInfo)
        {
            foreach($RaceInfo as $RaceGroupId => $value)
            {
                $found = 0;
                foreach($OldRaceList as $R1 => $R1Info)
                {
                    foreach($R1Info as $R2 => $v2)
                    {
                        if(($R1==$RaceId) && ($R2 == $RaceGroupId) && ($value['selected']==1))
                        {
                            $found = 1;
                            if($RankingInfo["RankingType"]=="time")
                            {
                                if($value['RankingType'] != $v2['RankingType'])
                                {
                                    $ToModify[$RaceId][$RaceGroupId] = $value;
                                }
                                break;break;
                            }
                            elseif($RankingInfo["RankingType"]=="credit")
                            {
                                $value["RankingType"] = "credit";
                                if(($value['RankingType'] != $v2['RankingType']) || ($value['RankingTypeId'] != $v2['RankingTypeId']))
                                {
                                    $ToModify[$RaceId][$RaceGroupId] = $value;
                                }
                                break;break;
                            }

                        }
                    }
                }
                if($found == 0  && $value['selected']==1)
                {
                    $ToInsert[$RaceId][$RaceGroupId] = $value;
                }
            }
        }
        foreach($ToInsert as $R => $RInfo)
        {
            foreach($RInfo as $G => $v)
            {
                if($RankingInfo["RankingType"]=="time")
                {
                    if($v['RankingType'] != "")
                    {
                        $this->insertRankingRace(array("RankingId"=>$RankingId,"RaceId"=>$R,"RaceGroupId"=>$G,"RankingType"=>$v['RankingType'],"RankingTypeId"=>0));
                    }
                }
                elseif($RankingInfo["RankingType"]=="credit")
                {
                    $this->insertRankingRace(array("RankingId"=>$RankingId,"RaceId"=>$R,"RaceGroupId"=>$G,"RankingType"=>"credit","RankingTypeId"=>$v['RankingTypeId']));
                }
            }
        }
        foreach($ToModify as $R => $RInfo)
        {
            foreach($RInfo as $G => $v)
            {
                $this->updateRankingRace($RankingId,$R,$G,array("RankingId"=>$RankingId,"RaceId"=>$R,"RaceGroupId"=>$G,"RankingType"=>"credit"));
            }
        }
        foreach($ToDelete as $R => $RInfo)
        {
            foreach($RInfo as $G => $v)
            {
                $this->deleteRankingRace($RankingId,$R,$G);
            }
        }
        return true;
    }
    //获取报名记录符合某个排名的选手列表
    public function updateRaceInfoByRanking($RankingId)
    {
        $oUser = new Xrace_UserInfo();
        $oRace = new Xrace_Race();
        $RaceStatusList = $oUser->getUserApplyStatusList();
        //获取排名信息
        $RankingInfo = $this->getRanking($RankingId,"RankingType,RankingId");
        //获取原有数据
        $RaceList = $this->getRankingRaceList(array("RankingId"=>$RankingId));
        $i = 1;$UserList = array();
        foreach($RaceList as $RaceId => $RaceInfo)
        {
            foreach($RaceInfo as $RaceGroupId => $value)
            {
                $RaceUserList = $oUser->getRaceUserList(array("RaceId"=>$RaceId,"RaceGroupId"=>$RaceGroupId));
                foreach($RaceUserList as $key => $UserInfo)
                {
                    $RaceUserInfo = $oUser->getRaceUser($UserInfo['RaceUserId'],"Name,RaceUserId");
                    if($UserInfo['RaceGroupId']==$RaceGroupId)
                    {
                        if(isset($UserList[$UserInfo['RaceUserId']]))
                        {
                            //$UserList[$UserInfo['RaceUserId']]['RaceList'][]= array("RaceId"=>$RaceId,"RaceGroupId"=>$RaceGroupId,"RankingType"=>$value["RankingType"]);
                        }
                        else
                        {
                            $UserList[$UserInfo['RaceUserId']]["RaceUserInfo"]=$RaceUserInfo;
                        }
                        $UserList[$UserInfo['RaceUserId']]['RaceList'][]= array("RaceStageId"=>$UserInfo["RaceStageId"],"RaceId"=>$RaceId,"RaceGroupId"=>$RaceGroupId,"RankingType"=>$value["RankingType"],"RankingTypeId"=>$value["RankingTypeId"]);
                    }
                }
            }
        }
        if($RankingInfo["RankingType"]=="time")
        {
            foreach($UserList as $RaceUserId => $RaceInfo)
            {
                if(count($RaceInfo['RaceList'])!=count($RaceList))
                {
                    unset($UserList[$RaceUserId]);
                }
                else
                {
                    $UserList[$RaceUserId]["Total"]['RaceCount'] = count($RaceInfo['RaceList']);
                    $UserList[$RaceUserId]["Total"]["RaceStatus"] = 1;
                    $UserList[$RaceUserId]["Total"]["TotalTime"] = 0;
                    foreach($RaceInfo['RaceList'] as $key => $Detail)
                    {
                        $filePath = __APP_ROOT_DIR__."Timing"."/".$Detail['RaceId']."_Data/"."UserList"."/";
                        $fileName = $RaceUserId.".php";
                        //载入预生成的配置文件
                        $RaceUserInfo =  Base_Common::loadConfig($filePath,$fileName);
                        $RaceUserInfo['Total']['RankingType'] = $RaceList[$Detail['RaceId']][$Detail['RaceGroupId']]['RankingType'];
                        $RaceUserInfo['Total']['RaceId'] = $Detail['RaceId'];
                        if(isset($RaceUserInfo['Total']['Finished']))
                        {
                            $UserList[$RaceUserId]['RaceDetail'][$Detail["RaceStageId"]]["RaceList"][$key] = $RaceUserInfo['Total'];
                        }
                        else
                        {
                            $UserList[$RaceUserId]['RaceDetail'][$Detail["RaceStageId"]]["RaceList"][$key] = array("Finished"=>0,"RaceStatus"=>1,"RaceId"=>$Detail["RaceId"],"RaceGroupId"=>$Detail['RaceGroupId'],"RankingType"=>$RaceList[$Detail['RaceId']][$Detail['RaceGroupId']]['RankingType']);
                        }
                        if($RaceUserInfo['Total']['Finished']==1)
                        {
                            if($RaceList[$RaceId][$Detail['RaceGroupId']]['RankingType'] == "gunshot")
                            {
                                $UserList[$RaceUserId]['Total']['TotalTime'] += $RaceUserInfo['Total']['TotalTime'];
                            }
                            elseif($RaceList[$RaceId][$Detail['RaceGroupId']]['RankingType'] == "net")
                            {
                                $UserList[$RaceUserId]['Total']['TotalTime'] += $RaceUserInfo['Total']['TotalNetTime'];
                            }
                            $t1[$RaceUserId] = $UserList[$RaceUserId]['Total']['TotalTime'];
                        }
                        else
                        {
                            $UserList[$RaceUserId]['RaceDetail'][$Detail["RaceStageId"]]["RaceList"][$key]["RaceStatusName"] = $RaceStatusList[$UserList[$RaceUserId]['RaceDetail'][$Detail["RaceStageId"]]["RaceList"][$key]['RaceStatus']];
                            $RaceInfo = $oRace->getRace($Detail['RaceId'],"RaceId,comment");
                            $RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
                            if(time()<strtotime($RaceInfo['comment']['SelectedRaceGroup'][$Detail['RaceGroupId']]['StartTime']))
                            {
                                echo "比赛尚未开始<br>";
                            }
                            else
                            {
                                $UserList[$RaceUserId]['Total']['RaceStatus'] = 0;
                                $t1[$RaceUserId] = 0;
                            }
                        }
                        $t0[$RaceUserId] = $UserList[$RaceUserId]['Total']['RaceStatus'];
                    }
                }
            }
            array_multisort($t0,SORT_DESC,$t1,SORT_ASC, $UserList);
        }
        elseif($RankingInfo["RankingType"]=="credit")
        {
            foreach($UserList as $RaceUserId => $RaceInfo)
            {
                $UserList[$RaceUserId]["Total"]["RaceStatus"] = 1;
                $UserList[$RaceUserId]["Total"]["TotalCredit"] = 0;
                $UserList[$RaceUserId]["Total"]['RaceCount'] = 0;

                foreach($RaceInfo['RaceList'] as $key => $Detail)
                {
                    $filePath = __APP_ROOT_DIR__."Timing"."/".$Detail['RaceId']."_Data/"."UserList"."/";
                    $fileName = $RaceUserId.".php";
                    //载入预生成的配置文件
                    $RaceUserInfo =  Base_Common::loadConfig($filePath,$fileName);

                    $RaceUserInfo['Total']['RankingType'] = $RaceList[$Detail['RaceId']][$Detail['RaceGroupId']]['RankingType'];
                    $RaceUserInfo['Total']['RankingTypeId'] = $RaceList[$Detail['RaceId']][$Detail['RaceGroupId']]['RankingTypeId'];
                    $RaceUserInfo['Total']['RaceId'] = $Detail['RaceId'];
                    if(isset($RaceUserInfo['Total']["Credit"][$Detail['RankingTypeId']]))
                    {
                        $UserList[$RaceUserId]['RaceDetail'][$Detail["RaceStageId"]]["RaceList"][$key] = $RaceUserInfo['Total'];
                        $UserList[$RaceUserId]['Total']['TotalCredit'] += $RaceUserInfo['Total']["Credit"][$Detail['RankingTypeId']]["Credit"];
                        $t2[$RaceUserId] = intval($UserList[$RaceUserId]['Total']['TotalCredit']);
                        $UserList[$RaceUserId]["Total"]['RaceCount'] ++;
                    }
                }
                if(count($UserList[$RaceUserId]['RaceDetail'])==0)
                {
                    unset($UserList[$RaceUserId]);
                }
                else
                {
                    echo "RaceUserId:".$RaceUserId."<br>";
                }
            }
            print_R($t2);
            echo count($t2)."-".count($UserList);
            array_multisort($t2,SORT_DESC, $UserList);
        }
        $filePath = __APP_ROOT_DIR__."Ranking"."/".$RankingId."/";
        $fileName = "Ranking.php";
        //生成配置文件
        Base_Common::rebuildConfig($filePath,$fileName,array("UserList"=>$UserList),"Ranking");
        return true;
    }
    //获取报名记录符合某个排名的选手列表
    public function updateRaceUserListByRanking($RankingId)
    {
        $url = $this->config->apiUrl.Base_Common::getUrl('','xrace.config','update.race.user.list.by.ranking',array('RankingId'=>$RankingId));
        $return = Base_Common::do_post($url);
    }
    //获取报名记录符合某个排名的选手列表
    public function getRaceInfoByRanking($RankingId)
    {
        $url = $this->config->apiUrl.Base_Common::getUrl('','xrace.config','get.race.user.list.by.ranking',array('RankingId'=>$RankingId));
        $return = Base_Common::do_post($url);
        return json_decode($return,true);
    }
    public function getRaceInfoByRankingByFile($RankingId)
    {
        $filePath = __APP_ROOT_DIR__."Ranking"."/";
        $fileName = $RankingId."/Ranking.php";
        //生成配置文件
        return Base_Common::loadConfig($filePath,$fileName);
    }
}

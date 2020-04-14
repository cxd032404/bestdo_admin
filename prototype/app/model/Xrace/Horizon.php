<?php
/**
 * 地平线对接相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_Horizon extends Base_Widget
{
	protected $ApiUrl = 'api.dpxian.cc';
    //声明所用到的表
	protected $table_race = 'config_race';

	protected $partnerId = "XR001";

	protected $transCount = 1;

    //获取单个分站的比赛配置信息
    public function getRaceStageInfo($RaceStageName)
    {
        $oRace = new Xrace_Race();
        //获取分站信息
        $RaceStageInfo = $oRace->getRaceStageByName($RaceStageName);
        //如果找到
        if(isset($RaceStageInfo["RaceStageId"]))
        {
            $ReturnArr = array();
            $ReturnArr["id"] = $RaceStageInfo["RaceStageId"];
            $ReturnArr["startDate"] = $RaceStageInfo["StageStartDate"];
            $ReturnArr["endDate"] = $RaceStageInfo["StageEndDate"];
            $ReturnArr["name"] = $RaceStageInfo["RaceStageName"];
            //数组解包
            $RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
            //比赛分组模式
            if($RaceStageInfo['comment']['RaceStructure']=="race")
            {
                //初始化空的比赛列表
                $RaceArr = array();
                //初始化空的分组列表
                $RaceGroupList = array();
                //获取比赛列表
                $RaceList = $oRace->getRaceList(array("RaceStageId"=>$RaceStageInfo['RaceStageId']));
                //循环比赛列表
                foreach($RaceList as $RaceId => $RaceInfo)
                {
                    //数据解包
                    $RaceInfo['RouteInfo'] = json_decode($RaceInfo['RouteInfo'],true);
                    foreach($RaceInfo['comment']["SelectedRaceGroup"] as $RaceGroupId => $GroupInfo)
                    {
                        if (!isset($RaceGroupList[$RaceGroupId]))
                        {
                            $RaceGroupList[$RaceGroupId] = $oRace->getRaceGroup($RaceGroupId, "RaceGroupId,RaceGroupName");
                        }
                        $RaceArr[] = array("hasDepartureTP" => $RaceInfo['comment']["NoStart"] == 0 ? "T" : "F",
                            "id" => $RaceId."_".$RaceGroupId,
                            "name" => $RaceInfo["RaceName"]."-".$RaceGroupList[$RaceGroupId]["RaceGroupName"],
                            "raceCategoryName" => $RaceInfo["RaceName"],
                            "raceCategoryUnitType" => $RaceInfo['comment']['ResultType'] == "Individual" ? "INDIVIDUAL" : "TEAM",
                            "startDate"=>date("Y-m-d", strtotime($GroupInfo["StartTime"])),
                            "startTime"=>date("H:i:s", strtotime($GroupInfo["StartTime"])),
                        );
                    }
                }
            }
            $ReturnArr["matches"] = $RaceArr;
            return $ReturnArr;
        }
        else
        {
            return false;
        }
    }
    //获取单个分站的比赛配置信息
    public function getAthleteList($RaceStageName)
    {
        $oRace = new Xrace_Race();
        $oUser = new Xrace_UserInfo();
        $oTeam = new Xrace_Team();
        //获取分站信息
        $RaceStageInfo = $oRace->getRaceStageByName($RaceStageName);
        //如果找到
        if(isset($RaceStageInfo["RaceStageId"]))
        {
            $ReturnArr = array();
            //数组解包
            $RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
            //比赛分组模式
            if($RaceStageInfo['comment']['RaceStructure']=="race")
            {
                //初始化空的比赛列表
                $AthleteArr = array();
                //初始化空的分组列表
                $RaceGroupList = array();
                //获取比赛列表
                $RaceList = $oRace->getRaceList(array("RaceStageId"=>$RaceStageInfo["RaceStageId"]));
                $SexList = $oUser->getSexList();
                $TeamList = array();
                $TeamArr = array();
                //循环比赛列表
                foreach($RaceList as $RaceId => $RaceInfo)
                {
                    //数据解包
                    $RaceInfo['RouteInfo'] = json_decode($RaceInfo['RouteInfo']);
                    $RaceUserList = $oUser->getRaceUserList(array("RaceStageId"=>$RaceStageInfo["RaceStageId"],"RaceId"=>$RaceId));
                    foreach($RaceUserList as $key => $ApplyInfo)
                    {
                        if (!isset($RaceGroupList[$ApplyInfo["RaceGroupId"]]))
                        {
                            $RaceGroupList[$ApplyInfo["RaceGroupId"]] = $oRace->getRaceGroup($ApplyInfo["RaceGroupId"], "RaceGroupId,RaceGroupName");
                        }
                        $RaceUserInfo = $oUser->getRaceUser($ApplyInfo['RaceUserId']);
                        //单人模式
                        if($RaceInfo['comment']['ResultType'] == "Individual")
                        {
                            if($ApplyInfo['TeamId']>0)
                            {
                                //如果在队伍列表中有获取到队伍信息
                                if(!isset($TeamInfo['TeamId']))
                                {
                                    $TeamList[$ApplyInfo['TeamId']] = $oTeam->getTeamInfo($ApplyInfo['TeamId'],'TeamId,TeamName');
                                }
                            }
                            $AthleteArr[] = array("gender"=>$SexList[$RaceUserInfo["Sex"]],
                                "name"=>$RaceUserInfo["Name"],
                                "id"=>$RaceUserInfo['RaceUserId'],
                                "matchName"=>$RaceInfo["RaceName"]."-".$RaceGroupList[$ApplyInfo["RaceGroupId"]]["RaceGroupName"],
                                "startNum"=>$ApplyInfo["BIB"],
                                "mobile"=>$RaceUserInfo['ContactMobile'],
                                "club"=>$ApplyInfo['TeamId']>0?$TeamList[$ApplyInfo['TeamId']]["TeamName"]:"");
                        }
                        else
                        {
                            $RaceUserInfo = $oUser->getRaceUser($ApplyInfo['RaceUserId']);
                            if(isset($TeamArr[$ApplyInfo["TeamId"]]))
                            {
                                $TeamArr[$ApplyInfo["TeamId"]]["name"] .= ",".$RaceUserInfo["Name"];
                            }
                            else
                            {
                                //如果在队伍列表中有获取到队伍信息
                                if(!isset($TeamInfo['TeamId']))
                                {
                                    $TeamList[$ApplyInfo['TeamId']] = $oTeam->getTeamInfo($ApplyInfo['TeamId'],'TeamId,TeamName');
                                }
                                $TeamArr[$ApplyInfo["TeamId"]] =
                                    array(
                                    "name"=>$RaceUserInfo["Name"],
                                    "id"=>$RaceUserInfo['RaceUserId'],
                                    "matchName"=>$RaceGroupList[$ApplyInfo["RaceGroupId"]]["RaceGroupName"],
                                    "startNum"=>$ApplyInfo["BIB"],
                                    "mobile"=>$RaceUserInfo['ContactMobile'],
                                    "club"=>$TeamList[$ApplyInfo['TeamId']]["TeamName"],
                                    "teamName"=>$TeamList[$ApplyInfo['TeamId']]["TeamName"]);
                            }
                        }
                    }
                }
                foreach($TeamArr as $key => $TeamInfo)
                {
                    $AthleteArr[] =  $TeamInfo;
                }
            }
            $ReturnArr = array(
                "raceId"=>$RaceStageInfo["RaceStageId"],
                "raceName"=>$RaceStageInfo['RaceStageName'],
                "athletes"=>$AthleteArr
            );
            return $ReturnArr;
        }
        else
        {
            return false;
        }

    }
    //获取单个分站的计时点配置信息
    public function getTimingPointList($RaceStageName)
    {
        $oRace = new Xrace_Race();
        $oSports = new Xrace_Sports();
        //获取分站信息
        $RaceStageInfo = $oRace->getRaceStageByName($RaceStageName);
        //如果找到
        if(isset($RaceStageInfo["RaceStageId"]))
        {
            $ReturnArr = array("raceId"=>$RaceStageInfo["RaceStageId"],"raceName"=>$RaceStageInfo['RaceStageName'],"timingPoints"=>array());
            //获取比赛列表
            $RaceList = $oRace->getRaceList(array("RaceStageId"=>$RaceStageInfo["RaceStageId"]));
            //初始化空的运动类型列表
            $SportsTypeList = array();
            //循环比赛列表
            foreach($RaceList as $RaceId => $RaceInfo)
            {
                $i = 1;
                //循环计时分段列表
                foreach($RaceInfo['comment']['DetailList'] as $key => $Sports)
                {
                    //如果在运动类型列表中没有
                    if(!isset($SportsTypeList[$Sports['SportsTypeId']]))
                    {
                        //获取运动信息
                        $SportsTypeInfo = $oSports->getSportsType($Sports['SportsTypeId'],"*");
                        //数据解包
                        $SportsTypeInfo['comment'] = json_decode($SportsTypeInfo['comment'],true);
                        $SportsTypeList[$Sports['SportsTypeId']] = $SportsTypeInfo;
                    }
                    //获取计时点详情信息
                    $TimingInfo = $oRace->getTimingDetail($Sports['TimingId']);
                    //如果计时点数据获取成功
                    if(isset($TimingInfo['TimingId']))
                    {
                        //数据解包
                        $TimingInfo['comment'] = json_decode($TimingInfo['comment'],true);
                        //如果解包后有计时点数据
                        if(count($TimingInfo['comment']))
                        {
                            //循环计时点数据
                            foreach ($TimingInfo['comment'] as $TimingPoint)
                            {
                                for($j = 0;$j<$TimingPoint['Round'];$j++)
                                {
                                    //第一次通过不需要下标
                                    $tName = $TimingPoint['TName'].(($j==0)?"":"*".($j+1));
                                    $TimingPointInfo = array(
                                        "distance" => $TimingPoint['ToPrevious']/1000,
                                        "gameName" => $SportsTypeList[$Sports['SportsTypeId']]['comment']['HorizonSign'],
                                        "displayName" => $tName,
                                        "raceCategoryName" => $RaceInfo["RaceName"],
                                        "name" => $tName,
                                        "latitude" => $TimingPoint['BaiduMapX'],
                                        "longitude" => $TimingPoint['BaiduMapY'],
                                        "seqNo" => $i++
                                    );
                                }
                                $ReturnArr["timingPoints"][] = $TimingPointInfo;
                            }
                        }
                    }
                }
            }
            return $ReturnArr;
        }
        else
        {
            return false;
        }
    }
    public function uploadTiming($RaceId,$syncTime)
    {
        $oRace = new Xrace_Race();
        //获取比赛信息
        $RaceInfo = $oRace->getRace($RaceId,"RaceId,RaceName,RaceStageId,RouteInfo");
        $RaceInfo["RouteInfo"] = json_decode($RaceInfo["RouteInfo"],true);
        //获取选手名单
        $RaceUserList = $oRace->getRaceUserListByFile($RaceId);
        $UserList = array();
        //循环选手名单
        foreach($RaceUserList["RaceUserList"] as $key => $UserInfo)
        {
            //将选手按照分组存放
            $UserList[$UserInfo['RaceGroupId']][$UserInfo['RaceUserId']] = 1;
        }
        //初始化结果数组
        $returnArr = array(
            "partnerId" => $this->partnerId,
            "raceId"=> $RaceInfo['RaceStageId'],
            "syncTimestamp" => $syncTime,
            "matchId" =>"",
            "athleteTimings" => array()
        );
        //循环分组列表
        foreach($UserList as $RaceGroupId => $GroupUserList)
        {
            $returnArr["matchId"] = $RaceId."_".$RaceGroupId;
            $this->uploadTimingBegin($returnArr["matchId"],$returnArr["raceId"],$syncTime);
            $arr = array();
            //循环分组下的选手列表
            foreach($GroupUserList as $RaceUserId => $value)
            {
                //获取选手的比赛记录
                $UserRaceInfo = $oRace->getUserRaceInfo($RaceId,$RaceUserId);
                //如果起点有记录
                if($UserRaceInfo["Point"][1]["inTime"] > 0)
                {
                    $TimingArr = array(
                        "startNum" => $UserRaceInfo['RaceUserInfo']['BIB'],
                        "startTime" => date("h:i:s",$UserRaceInfo["Point"][1]["inTime"]),
                        "timingPointTimings" => array()
                    );
                    $arr = array();
                    //循环计时点
                    foreach($UserRaceInfo["Point"] as $P => $pInfo)
                    {
                       if($pInfo["inTime"] != "0")
                       {
                           if($RaceInfo["RouteInfo"]["FinalResultType"]=="gunshot")
                           {
                               $TotalTime = $pInfo["TotalTime"];
                           }
                           elseif($RaceInfo["RouteInfo"]["FinalResultType"]=="net")
                           {
                               $TotalTime = $pInfo["TotalNetTime"];
                           }
                           $arr[] = array(
                               "arrivalTime" => date("h:i:s",intval($pInfo["inTime"])),
                               "costTime" => Base_common::parthTimeLag($pInfo["PointTime"]),
                               "overallCostTime" => Base_common::parthTimeLag($TotalTime),
                               "overallRank" => $pInfo["GroupRank"],
                               "tpName" => $pInfo["TName"],
                               "tpSeqNo" => $P,
                           );
                           echo $returnArr["matchId"]."-".$UserRaceInfo['RaceUserInfo']['BIB']."-".$P."-".$pInfo["inTime"]."-".date("h:i:s",intval($pInfo["inTime"]))."\n";
                       }

                    }
                    $TimingArr["timingPointTimings"] = $arr;
                }
                $returnArr["athleteTimings"][] = $TimingArr;
                //if(count($returnArr["athleteTimings"])>=$this->transCount)
                if(count($returnArr["athleteTimings"])>=1)
                {
                    echo "count:".count($returnArr["athleteTimings"])."/n";
                    echo "transfer!/n";
                    print_r($this->uploadTimingBatch($returnArr));

                    $Text = json_encode($returnArr)."\n";
                    $filePath = __APP_ROOT_DIR__."log/Horizon/";
                    $fileName = date("Y-m-d",time()).".log";
                    //写入日志文件
                    Base_Common::appendLog($filePath,$fileName,$Text);
                    $returnArr["athleteTimings"] = array();
                }
            }
            if(count( $returnArr["athleteTimings"]))
            {
                echo 'transfer!/n';
                print_r($this->uploadTimingBatch($returnArr));
                $Text = json_encode($returnArr)."\n";
                $filePath = __APP_ROOT_DIR__."log/Horizon/";
                $fileName = date("Y-m-d",time()).".log";
                //写入日志文件
                Base_Common::appendLog($filePath,$fileName,$Text);
                $returnArr["athleteTimings"] = array();
            }
            $this->uploadTimingEnd($returnArr["matchId"],$returnArr["raceId"],$syncTime);
        }
    }
    public function uploadTimingBatch($data)
    {
        print_R($data);sleep(1);
        $url = $this->ApiUrl.'/liveIntegration/timing/sync/live/batch';
        print_r(Base_Common::http_post_json($url,json_encode($data)));

    }
    public function uploadTimingBegin($matchId,$raceId,$syncTimestamp)
    {
        $returnArr = array("matchId"=>$matchId,
            "partnerId"=>$this->partnerId,
            "raceId"=>$raceId,
            "syncTimestamp"=>$syncTimestamp);
        $url = $this->ApiUrl.'/liveIntegration/timing/sync/begin';
        print_r(Base_Common::http_post_json($url,json_encode($returnArr)));

    }
    public function uploadTimingEnd($matchId,$raceId,$syncTimestamp)
    {
        $returnArr = array("matchId"=>$matchId,
            "partnerId"=>$this->partnerId,
            "raceId"=>$raceId,
            "syncTimestamp"=>$syncTimestamp);
        $url = $this->ApiUrl.'/liveIntegration/timing/sync/end';
        print_r(Base_Common::http_post_json($url,json_encode($returnArr)));
    }

}

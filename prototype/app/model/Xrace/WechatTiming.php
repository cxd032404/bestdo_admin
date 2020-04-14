<?php
/**
 * 微信打卡计时相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_WechatTiming extends Base_Widget
{
    //声明所用到的表
    protected $table = 'wechat_times';
    protected $table_sorted = 'wechat_times_sorted';
    //新增单个计时记录
    public function insertTiming($table,array $bind)
    {
        return $this->db->insert($table, $bind);
    }
    //新增一条计时记录
    public function insertTimingLog(array $Timing)
    {
        $oRace = new Xrace_Race();
        //获取比赛信息
        $RaceInfo = $oRace->getRace($Timing['RaceId']);
        //如果获取到比赛信息
        if(isset($RaceInfo['RaceId']))
        {
            //获取成绩总表
            $UserRaceInfo = $oRace->GetUserRaceTimingInfo($RaceInfo['RaceId']);
            //如果未获取到
            if(!isset($UserRaceInfo['Point']))
            {
                //重建计时数据
                $oRace->genRaceLogToText($RaceInfo['RaceId']);
            }
            $oUser = new Xrace_UserInfo();
            if($Timing["RaceUserId"]>0)
            {
                //获取比赛用户信息
                $RaceUserInfo = $oUser->getRaceUser($Timing["RaceUserId"],"RaceUserId,Name");
                //如果找到
                if(isset($RaceUserInfo['RaceUserId']))
                {
                    $RaceUserId = $RaceUserInfo['RaceUserId'];
                }
                else
                {
                    //返回错误
                    return array("return"=>-2);
                }
            }
            else
            {
                //获取用户信息
                $UserInfo = $oUser->getUserByColumn("WechatId",$Timing['OpenId']);
                //如果获取到用户信息
                if(isset($UserInfo['UserId']))
                {
                    //如果有关联的比赛用户信息
                    if($UserInfo['RaceUserId']>0)
                    {
                        $RaceUserId = $UserInfo['RaceUserId'];
                    }
                    else
                    {
                        //返回错误
                        return array("return"=>-1);
                    }
                }
                else
                {
                    //返回错误
                    return array("return"=>-2);
                }
            }

            //获取报名记录
            $RaceUserList = $oRace->getRaceUserListByFile($RaceInfo["RaceId"]);
            //初始化标识
            $found = 0;
            //循环报名记录
            foreach($RaceUserList['RaceUserList'] as $key => $RaceApplyLog)
            {
                //如果找到
                if($RaceApplyLog["RaceUserId"] == $RaceUserId)
                {
                    $found = 1;
                    break;
                }
            }
            //如果最终没找到
            if($found == 0)
            {
                //获取当前分站当前用户的报名记录
                $applyList = $oUser->getRaceUserList(array('RaceStageId'=>$RaceInfo['RaceStageId'],'RaceUserId'=>$RaceUserId));
                if(count($applyList)>0)
                {
                    //重写比赛ID
                    $Timing['RaceId'] = $applyList['0']['RaceId'];
                    //获取比赛信息
                    $RaceInfo = $oRace->getRace($Timing['RaceId']);
                }
                else
                {
                    return array("return"=>-4);
                }
		//return array("return"=>-4);
            }
            else
            {
                //获取计时记录
                $UserRaceInfo = $oRace->getUserRaceInfoByFile($RaceInfo["RaceId"],$RaceUserId);
                $RaceGroupId = $UserRaceInfo["RaceUesrInfo"]["RaceGroupId"];
                //拼接获取计时数据的参数，注意芯片列表为空时的数据拼接
                $RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
                $RaceInfo['RouteInfo'] = json_decode($RaceInfo['RouteInfo'],true);

                $params = array(
                    'StartTime'=>$RaceGroupId>0?date("Y-m-d H:i:s",strtotime($RaceInfo['comment']['SelectedRaceGroup'][$RaceGroupId]['StartTime'])+0*3600):date("Y-m-d H:i:s",strtotime($RaceInfo['StartTime'])+0*3600),
                    'EndTime'=>$RaceGroupId>0?date("Y-m-d H:i:s",strtotime($RaceInfo['comment']['SelectedRaceGroup'][$RaceGroupId]['EndTime'])+0*3600):date("Y-m-d H:i:s",strtotime($RaceInfo['EndTime'])+0*3600),
                    'prefix'=>$RaceInfo['RouteInfo']['TimePrefix'], 'RaceUser'=>$RaceUserId,'sorted'=>1,'pageSize'=>10000,'Page'=>1);

                //获取计时数据
                $TimingList = $this->getTimingData($params);
                //循环计时点
                $t = array();$t2 = array();
				foreach($UserRaceInfo["Point"] as $Point => $PointInfo)
                {
                    $UserRaceInfo["Point"][$Point]["inTime"] = 0;
                    foreach($PointInfo as $key => $value)
                    {
                        $t[$Point] = $PointInfo['TolaranceTime'] + $PointInfo['inTime'];
						$t2[$Point] = $PointInfo['inTime'];
						if(!in_array($key,array ("inTime","TName","TencentX","TencentY","ChipId")))
                        {
                            unset($UserRaceInfo["Point"][$Point][$key]);
                        }
						
                    }
					//如果前一个点的过线时间+等待时间  大于 当前过线时间
					if($t[$Point-1]>$Timing["Time"])
					{
					            //无需重复打卡
                                return array("return"=>-8,"time" => $t[$Point-1]-$Timing["Time"]);	
					}
					foreach($TimingList["Record"] as $Location => $TimingLog)
                    {
						if($PointInfo["ChipId"] == $TimingLog["Location"])
                        {
                            $UserRaceInfo["Point"][$Point]["inTime"] = $TimingLog["time"];
                            break;
                        }
                    }
					
                }
                $Pointound = 0;
                //循环计时点
                foreach($UserRaceInfo["Point"] as $Point => $PointInfo)
                {
                    if($PointInfo["ChipId"] == $Timing["Location"])
                    {
                        $Pointound = 1;
						if($PointInfo["inTime"]<($t[$Point-1]))
						{
							$PointInfo["inTime"] = 0;
						}
                        //如果已经经过
                        if($PointInfo["inTime"]>0)
                        {
                            if(isset($UserRaceInfo["Point"][$Point+1]))
                            {
                                $NextPoint = $UserRaceInfo["Point"][$Point+1];
                                $NextPointInfo = array("TName"=>$NextPoint["TName"],"TencentX"=>$NextPoint["TencentX"],"TencentY"=>$NextPoint["TencentY"],"ToPrevious"=>$NextPoint["ToPrevious"]);
                                //无需重复打卡
                                return array("return"=>-7,"NextPoint"=>$NextPointInfo,"TimingLog"=>$UserRaceInfo["Point"]);
                            }
                            else
                            {
                                //无需重复打卡
                                return array("return"=>-7,"TimingLog"=>$UserRaceInfo["Point"]);
                            }
                        }
                        else
                        {
                            break;
                        }
                    }
                }
                if($Pointound == 0)
                {
                    return array("return"=>-5);
                }
                $Distance =  Base_Common::getDistance($Timing["TencentX"],$Timing["TencentY"],$PointInfo["TencentX"],$PointInfo["TencentY"]);

                /*
                if($Distance >= 100)
                {
                    return array("return"=>-6,"Distance"=>$Distance);
                }
                */

            }

            if($found == 1 && $Pointound == 1)
            {
                //数据解包
                //$RaceInfo["RouteInfo"] = json_decode($RaceInfo["RouteInfo"],true);
                //获取需要用到的表名
                $table_to_process = Base_Widget::getDbTable($this->table);
                $table_to_process = str_replace($this->table,$RaceInfo["RouteInfo"]["TimePrefix"]."_".$this->table,$table_to_process);
                //$table_timing = $this->db->createTable($this->table,$RaceInfo["RouteInfo"]["TimePrefix"]);
                $table_timing = $this->db->copyTable($this->table,$table_to_process);
                $comment = array("Position"=>array("X"=>$Timing["TencentX"],"Y"=>$Timing["TencentY"]));
                if($Timing["ManagerId"]>0)
                {
                    $comment["ManagerId"] = $Timing["ManagerId"];
                }
                //初始化计时记录
                $TimingLog = array("time"=>$Timing["Time"],"Location"=>$Timing["Location"],"RaceUserId"=>$RaceUserId,"comment"=>json_encode($comment));
                //加入计时记录
                $Id = $this->insertTiming($table_timing,$TimingLog);
                $NextPoint = $UserRaceInfo["Point"][$Point+1];
                $NextPointInfo = array("TName"=>$NextPoint["TName"],"TencentX"=>$NextPoint["TencentX"],"TencentY"=>$NextPoint["TencentY"],"ToPrevious"=>$NextPoint["ToPrevious"]);
                $UserRaceInfo["Point"][$Point]["inTime"] = $Timing["Time"];
                return array("return"=>1,"Distance"=>$Distance,"NextPoint"=>$NextPointInfo,"TimingLog"=>$UserRaceInfo["Point"]);
            }
        }
        else
        {
            //返回错误
            return array("return"=>-3);
        }
    }
    //获取打卡成绩
    public function getTimingData($params,$fields=array("*"))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        if($params['sorted']!=1)
        {
            $table_to_process = str_replace($this->table,$params['prefix'].$this->table,$table_to_process);
        }
        else
        {
            $table_to_process = str_replace($this->table,$params['prefix']."_".$this->table,$table_to_process);
        }
        //获得用户ID
        $whereUser = isset($params['RaceUser'])?" RaceUserId = '".$params['RaceUser']."' ":"";
        if(isset($params['UserList']))
        {
            if($params['UserList'] == "-1")
            {
                $whereUserList = "0";
            }
            else
            {
                $whereUserList = " RaceUserId in (".$params['UserList'].") ";
            }
        }
        $whereStart = isset($params['LastId'])?" Id >".$params['LastId']." ":"";
        $whereStartTime = isset($params['StartTime'])?" time >".strtotime($params['StartTime'])." ":"";
        $whereEndTime = isset($params['EndTime'])?" time <=".strtotime($params['EndTime'])." ":"";
        $Limit = " limit 0,".$params['pageSize'];
        //所有查询条件置入数组
        $whereCondition = array($whereUser,$whereUserList,$whereStart,$whereStartTime,$whereEndTime);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by Id asc".$Limit;
		$return = $this->db->getAll($sql);
        if(isset($params['getCount']) && $params['getCount']==1)
        {
            $RecordCount = $this->getTimingDataCount($params);
            return array("Record"=>$return,"RecordCount"=>$RecordCount['RecordCount'],"sql"=>$sql);
        }
        else
        {
            return array("Record"=>$return,"sql"=>$sql);
        }
    }
    public function getTimingDataCount($params,$sorted=1)
    {
        $fields = array("RecordCount"=>"count(1)");
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        if($params['sorted']!=1)
        {
            $table_to_process = str_replace($this->table,$params['prefix'].$this->table,$table_to_process);
        }
        else
        {
            $table_to_process = str_replace($this->table,$params['prefix']."_".$this->table,$table_to_process);
        }
        //获得用户ID
        $whereUser = isset($params['RaceUser'])?" RaceUserId = '".$params['RaceUser']."' ":"";
        if(isset($params['UserList']))
        {
            if($params['UserList'] == "-1")
            {
                $whereUserList = "0";
            }
            else
            {
                $whereUserList = " RaceUserId in (".$params['UserList'].") ";
            }
        }
        $whereStart = isset($params['LastId'])?" Id >".$params['LastId']." ":"";
        $whereStartTime = isset($params['StartTime'])?" time >".strtotime($params['StartTime'])." ":"";
        $whereEndTime = isset($params['EndTime'])?" time <=".strtotime($params['EndTime'])." ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereUser,$whereUserList,$whereStart,$whereStartTime,$whereEndTime);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        $return = $this->db->getOne($sql);
        return array("RecordCount"=>$return);
    }
    //根据比赛ID生成该场比赛的MYLAPS计时数据
    public function genTimingInfo($RaceId,$Force = 0,$Cache = 0)
    {
        //echo "Force:".$Force."<br>";
        //die();
        $oUser = new Xrace_UserInfo();
        $oRace = new Xrace_Race();
        $oCredit = new Xrace_Credit();
        //获取积分总表
        $CreditList = $oCredit->getCreditList(0,"CreditId,CreditName");
        //总记录数量
        $TotalCount = 0;
        //程序执行开始时间
        $GenStart = microtime(true);
        //获取比赛信息
        $RaceInfo = $oRace->getRace($RaceId);
        //如果是待处理的比赛
        if($RaceInfo['ToProcess']==1)
        {
            //更新状态
            $update = $oRace->updateRace($RaceId,array("ToProcess"=>0));
        }
        //解包压缩的数据
        $RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
        //解包路径相关的信息
        $RaceInfo['RouteInfo'] = json_decode($RaceInfo['RouteInfo'],true);
        //从文件载入计时信息
        $UserRaceTimingInfo = $oRace->GetRaceTimingOriginalInfo($RaceId,0);
        //检查记录变动情况，数量变动或者强制更新，则重建排序后的数据
        //echo "\n\n";
        //echo "Prefix:".$RaceInfo['RouteInfo']['TimePrefix']."\n\n";
        $RecordCheck = $this->checkTimingRecord($RaceInfo['RouteInfo']['TimePrefix'],$Force);
        //die();
        //如果检查失败
        if($RecordCheck['return']==false)
        {
            echo "data error";
            die();
        }
        if($UserRaceTimingInfo['LastId'] >0)
        {
            //检查记录顺序情况
            $RecordSequenceCheck = $this->checkTimingRecordSqeuence($RaceInfo['RouteInfo']['TimePrefix'],$UserRaceTimingInfo['LastId'],$UserRaceTimingInfo['LastTime'],$RecordCheck['rebuild']==1?0:1);
            //顺序错误导致重建
            if($RecordSequenceCheck['restart'] == 1)
            {
                //重新生成选手的mylaps排名数据
                $oRace->genRaceLogToText($RaceId);
            }
        }
		
        //如果强制重新更新计时数
        if($Force==1)
        {

            //重新生成选手的mylaps排名数据
            $oRace->genRaceLogToText($RaceId);
        }
		//获取选手和车队名单
        $RaceUserList = $oRace->getRaceUserListByFile($RaceId);
		echo "包含用户列表：".count($RaceUserList['RaceUserList'])."人\n";
	
		
		//如果获取不到
		if(!isset($RaceUserList['RaceUserList']))
		{
			echo "找不到用户记录，重建/n";
			//重新生成选手的mylaps排名数据
            $oRace->genRaceLogToText($RaceId);
		}
		
        $UserRaceTimingInfo = $oRace->GetRaceTimingOriginalInfo($RaceId,0);
        //初始化比赛时间列表
        $TimeList = array();
        //如果有配置各个分组的比赛时间
        if(isset($RaceInfo['comment']['SelectedRaceGroup']))
        {
            //循环分组列表
            foreach($RaceInfo['comment']['SelectedRaceGroup'] as $RaceGroupId => $RaceGroupInfo)
            {
                //保存比赛时间
                $TimeList[$RaceGroupId]['RaceStartTime'] = strtotime($RaceGroupInfo['StartTime']) + $RaceGroupInfo['RaceStartMicro']/1000;
                $TimeList[$RaceGroupId]['RaceEndTime'] = strtotime($RaceGroupInfo['EndTime']);
                $TimeList[$RaceGroupId]['CreditRatio'] = ($RaceGroupInfo['CreditRatio']);
            }
        }
        else
        {
            //预存比赛的开始和结束时间
            $TimeList[$RaceInfo['RaceGroupId']]['RaceStartTime'] = strtotime($RaceInfo['StartTime']) + $RaceInfo['comment']['RaceStartMicro']/1000;
            $TimeList[$RaceInfo['RaceGroupId']]['RaceEndTime'] = strtotime($RaceInfo['EndTime']);
        }
        //车队排名的名次数据
        $RaceInfo['comment']['TeamResultRank'] = isset($RaceInfo['comment']['TeamResultRank'])?$RaceInfo['comment']['TeamResultRank']:3;
        //预存比赛的开始和结束时间
        $RaceStartTime = strtotime($RaceInfo['StartTime']) + $RaceInfo['comment']['RaceStartMicro']/1000;
        $RaceEndTime = strtotime($RaceInfo['EndTime']);echo "RaceEndTime:".$RaceEndTime;

        //初始化单个计时点的最大等待时间（超过这个时间才认为是新的一次进入）
        $RaceInfo['RouteInfo']['TolaranceTime'] = isset($RaceInfo['RouteInfo']['TolaranceTime'])?$RaceInfo['RouteInfo']['TolaranceTime']:30;
        //初始化计时点成绩计算的方式（发枪时刻/第一次经过起始点）
        $ResultType = ((isset($RaceInfo['RouteInfo']['RaceTimingResultType']) && ($RaceInfo['RouteInfo']['RaceTimingResultType']=="gunshot"))||!isset($RaceInfo['RouteInfo']['RaceTimingResultType']))?"gunshot":"net";
        //初始化计时点成绩计算的方式（发枪时刻/第一次经过起始点/积分）
        $FinalResultType = isset($RaceInfo['RouteInfo']['FinalResultType'])?$RaceInfo['RouteInfo']['FinalResultType']:"gunshot";
        echo "final:".$FinalResultType."<br>";
        echo "计时点计算:".$oRace->getRaceTimingResultType($ResultType)."\n<br>";
        echo "总成绩计算:".$oRace->getFinalResultType($FinalResultType)."\n<br>";
        //获取选手和车队名单
        $RaceUserList = $oRace->getRaceUserListByFile($RaceId);
        //初始化空的芯片列表
        $ChipList = array();
        //初始化空的用户列表
        $UserList = array();
        //循环报名记录
		foreach ($RaceUserList['RaceUserList'] as $ApplyId => $ApplyInfo)
        {
            //如果有配置芯片数据和BIB
            if (trim($ApplyInfo['BIB']))
            {
                //拼接字符串加入到芯片列表
                $UserIdList[$ApplyInfo['RaceUserId']] = "'" . $ApplyInfo['RaceUserId'] . "'";
                //分别保存用户的ID,姓名和BIB
                $UserList[$ApplyInfo['RaceUserId']] = $ApplyInfo;
            }
        }
        echo "比赛时间：".$RaceInfo['StartTime'].".".sprintf("%03d",isset($RaceInfo['comment']['RaceStartMicro'])?$RaceInfo['comment']['RaceStartMicro']:0)."~".$RaceInfo['EndTime']."<br>\n";
        echo "选手列表：".implode(",",$UserIdList)."\n";
        //获取文件最后的
        $LastId = $UserRaceTimingInfo['LastId'];
        //单页记录数量
        $pageSize = 1000;
        //默认第一次有获取到
        $Count = $pageSize;
        //初始化当前芯片（选手）
        $currentUser = "";
        while ($Count == $pageSize)
        {
            //拼接获取计时数据的参数，注意芯片列表为空时的数据拼接
            $params = array('sorted'=>1,'StartTime'=>date("Y-m-d H:i:s",strtotime($RaceInfo['StartTime'])+0*3600),'EndTime'=>date("Y-m-d H:i:s",strtotime($RaceInfo['EndTime'])+0*3600),'prefix'=>$RaceInfo['RouteInfo']['TimePrefix'],'LastId'=>$LastId, 'pageSize'=>$pageSize, 'UserList'=>count($UserIdList) ? implode(",",$UserIdList):"-1");
            //获取计时数据
            $TimingList = $this->getTimingData($params);
            //依次循环计时数据
            foreach ($TimingList['Record'] as $Key => $TimingInfo)
            {
                //最后获取到的记录ID
                $LastId = $TimingInfo['Id'];
                //时间进行累加
                $inTime = sprintf("%0.2f", $TimingInfo['time']);
                $ChipTime = $inTime;
                //如果时间在比赛的开始时间和结束时间之内
                $RaceStartTime = $TimeList[$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']]['RaceStartTime'];
                $RaceEndTime = $TimeList[$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']]['RaceEndTime'];
                //如果当前芯片 和 循环到的计时数据不同 （说明已经结束了上一个选手的循环）
                if ($currentUser != $TimingInfo['RaceUserId'])
                {
                    $num=1;
                    //将当前位置置为循环到的计时点
                    $currentUser = $TimingInfo['RaceUserId'];
                    //调试信息
                    if(isset($UserList[$TimingInfo['RaceUserId']]))
                    {
                        //echo "芯片:".$currentUser . ",用户ID:".$UserList[$TimingInfo['RaceUserId']]['RaceUserId'].",姓名:".$UserList[$TimingInfo['RaceUserId']]['Name'] .",队伍:" . $UserList[$TimingInfo['RaceUserId']]['TeamName'] ."-号码:" . $UserList[$TimingInfo['RaceUserId']]['BIB']."用户分组:".$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']."\n<br>";
                    }
                    else
                    {
                        //echo "芯片:".$currentUser . ",用户 Undifined:". "\n";
                    }
                    $UserRaceStatusInfo = $oUser->getRaceApplyUserInfo($UserList[$TimingInfo['RaceUserId']]['ApplyId'],"RaceStatus,comment");
                }
                //比赛前数据
                if ($ChipTime < $RaceStartTime)
                {
                    echo $num."-".$TimingInfo['Location']."-".($ChipTime)."-".date("Y-m-d H:i:s", $TimingInfo['time']).".".(substr($miliSec,2))."赛前数据跳过<br>\n";
                }
                else
                {
                    //比赛中数据 超时判断
                    if ($ChipTime <= $RaceEndTime)
                    {
                        echo $num."-".$TimingInfo['Location']."-".($ChipTime)."-".date("Y-m-d H:i:s", $TimingInfo['time'])."<br>\n";
                        //获取选手的比赛信息（计时）
                        $UserRaceInfo = $oRace->getUserRaceTimingOriginalInfo($RaceId, $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],$Cache);
                        //如果没有标记当前位置（第一个点）
                        if (!isset($UserRaceInfo['CurrentPoint']))
                        {
                            //初始位置为1号点
                            $i = 1;
                            //获取第一个点的数据
                            $FirstPointInfo = $UserRaceInfo['Point'][$i];
                            //比对第一个点的芯片ID和当前获得点的ID是否符合
                            if ($FirstPointInfo['ChipId'] == $TimingInfo['Location'])
                            {
                                //记录当前经过的点的位置
                                $UserRaceInfo['CurrentPoint'] = $i;
                                //记录经过时间
                                $UserRaceInfo['Point'][$i]['inTime'] = $inTime;
                                //如果前一点的距离为非负数，则取当前时间和前一点差值作为经过时间，否则不计时
                                //aaa
                                if($ResultType=="gunshot")
                                {
                                    $UserRaceInfo['Point'][$i]['PointTime'] = sprintf("%0.2f",$UserRaceInfo['Point'][$i]['inTime']-$RaceStartTime);
                                }
                                else
                                {
                                    $UserRaceInfo['Point'][$i]['PointTime'] = isset($UserRaceInfo['Point'][$i]['ToPrevious'])&&intval($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?(isset($UserRaceInfo['Point'][$i-1]['inTime'])?sprintf("%0.2f",($UserRaceInfo['Point'][$i]['inTime']-$UserRaceInfo['Point'][$i-1]['inTime'])):0):0;
                                }
                                $UserRaceInfo['Point'][$i]['PointTime'] = isset($UserRaceInfo['Point'][$i]['ToPrevious'])&&intval($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?(isset($UserRaceInfo['Point'][$i-1]['inTime'])?sprintf("%0.2f",($UserRaceInfo['Point'][$i]['inTime']-$UserRaceInfo['Point'][$i-1]['inTime'])):0):0;
                                $UserRaceInfo['Point'][$i]['PointSpeed'] = isset($UserRaceInfo['Point'][$i])?(Base_Common::speedDisplayParth($UserRaceInfo['Point'][$i]['SpeedDisplayType'],$UserRaceInfo['Point'][$i]['PointTime'],($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?$UserRaceInfo['Point'][$i]['ToPrevious']:0)):"";


                                $TotalNetTime = isset($UserRaceInfo['Point'][$i-1]['TotalNetTime'])?sprintf("%0.2f",($UserRaceInfo['Point'][$i-1]['TotalNetTime']+$UserRaceInfo['Point'][$i]['PointTime'])):sprintf("%0.2f",$UserRaceInfo['Point'][$i]['PointTime']);
                                if($ResultType!=="gunshot")
                                {
                                    $TotalNetTime = $TotalNetTime-$UserRaceInfo['Point'][$i]['PointTime'];
                                }


                                if($i==1)
                                {
                                    $TotalTime = sprintf("%0.2f",$UserRaceInfo['Point'][$i]['inTime']-$RaceStartTime);
                                }
                                else
                                {
                                    $TotalTime = sprintf("%0.2f",($UserRaceInfo['Point'][$i-1]['TotalTime'])?$UserRaceInfo['Point'][$i-1]['TotalTime']+$UserRaceInfo['Point'][$i]['PointTime']:$UserRaceInfo['Point'][$i]['PointTime']);
                                }
                                if($ResultType=="gunshot")
                                {
                                    $UserRaceInfo['Point'][$i]['SportsTime'] = $TotalTime;
                                }
                                else
                                {
                                    $UserRaceInfo['Point'][$i]['SportsTime'] = $TotalNetTime;
                                }

                                $UserRaceInfo['Point'][$i]['SportsSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$i]['SpeedDisplayType'],$UserRaceInfo['Point'][$i]['SportsTime'],$UserRaceInfo['Point'][$i]['CurrentDistance']);
                                $UserRaceInfo['Point'][$i]['TotalTime'] = $TotalTime;
                                $UserRaceInfo['Point'][$i]['TotalNetTime'] = $TotalNetTime;

                                unset($UserRaceInfo['Point'][$i]['UserList']);
                                //保存个人当前计时点的信息
                                $oRace->UserTimgingDataSave($RaceInfo['RaceId'],$UserList[$TimingInfo['RaceUserId']]['RaceUserId'],$UserRaceInfo,$Cache);
                                //获取所有选手的比赛信息（计时）
                                $UserRaceInfoList = $oRace->GetRaceTimingOriginalInfo($RaceId,$Cache);
                                //echo "SaveDataCount1:".count($UserRaceInfoList)."<br>";
                                //将当前计时点最小的过线记录保存
                                $UserRaceInfoList['Point'][$i]['inTime'] = $UserRaceInfoList['Point'][$i]['inTime'] == 0 ? $inTime : sprintf("%0.2f", min($UserRaceInfoList['Point'][$i]['inTime'], $ChipTime));
                                //新增当前点的过线记录
                                $UserRaceInfoList['Point'][$i]['UserList'][count($UserRaceInfoList['Point'][$i]['UserList'])] = array("PointTime"=>$UserRaceInfo['Point'][$i]['PointTime'],"PointSpeed"=>$UserRaceInfo['Point'][$i]['PointSpeed'] ,"TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'], "TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],"BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                //初始化一个空的数组
                                $t = array();
                                //循环每个人的过线记录
                                foreach ($UserRaceInfoList['Point'][$i]['UserList'] as $k => $v)
                                {
                                    //计算每个人的过线记录和当前点最早记录的时间差
                                    $UserRaceInfoList['Point'][$i]['UserList'][$k]['TimeLag'] = sprintf("%0.2f",abs(sprintf("%0.2f", $UserRaceInfoList['Point'][$i]['inTime']) - sprintf("%0.2f", $v['inTime'])));
                                    //生成排序数组
                                    $t[$k] = $UserRaceInfoList['Point'][$i]['UserList'][$k]['TimeLag'];
                                }
                                //根据时间差进行升序排序
                                array_multisort($t, SORT_ASC, $UserRaceInfoList['Point'][$i]['UserList']);
                                //初始化一个空的分组排名数组
                                $DivisionList = array();
                                //循环当前计时点排名
                                foreach($UserRaceInfoList['Point'][$i]['UserList'] as $key => $UserInfo)
                                {
                                    // 生成总排名
                                    $UserRaceInfoList['Point'][$i]['UserList'][$key]['Rank'] = $key+1;
                                    //依次填入分组数据
                                    $DivisionList[$UserInfo['RaceGroupId']][] = $UserInfo['BIB'];
                                    //排名保存
                                    $UserRaceInfoList['Point'][$i]['UserList'][$key]['GroupRank'] = count($DivisionList[$UserInfo['RaceGroupId']]);
                                    $UserInfo['GroupRank'] = $UserRaceInfoList['Point'][$i]['UserList'][$key]['GroupRank'];
                                    //清除原来的积分
                                    unset($UserRaceInfoList['Point'][$i]['UserList'][$key]['Credit']);
                                    if(isset($UserRaceInfoList['Point'][$i]['CreditList']))
                                    {
                                        //循环积分列表
                                        foreach($UserRaceInfoList['Point'][$i]['CreditList'] as $CreditId => $CreditInfo)
                                        {
                                            //生成积分序列
                                            $CreditSequence = Base_Common::ParthSequence($CreditInfo['CreditRule']);
                                            //如果名次匹配
                                            if(isset($CreditSequence[$UserInfo['GroupRank']]))
                                            {
                                                //积分相应累加
                                                $UserRaceInfoList['Point'][$i]['UserList'][$key]['Credit'][$CreditId] = array("Credit"=>round($CreditSequence[$UserRaceInfoList['Point'][$i]['UserList'][$key]['GroupRank']]*$TimeList[$UserInfo['RaceGroupId']]['CreditRatio']),"CreditName"=>$CreditList[$CreditId]['CreditName']);
                                            }
                                        }
                                    }

                                }
                                //如果现在已有总排名数组
                                if (isset($UserRaceInfoList['Total']) && count($UserRaceInfoList['Total']))
                                {
                                    //初始设定为未找到
                                    $found = 0;
                                    //循环已有的排名数据
                                    foreach ($UserRaceInfoList['Total'] as $k => $v)
                                    {
                                        //依次比对现在的用户ID，如果找到了，则更新
                                        if ($v['RaceUserId'] == $UserList[$TimingInfo['RaceUserId']]['RaceUserId'])
                                        {
                                            $UserRaceInfoList['Total'][$k] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'], "TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'], "BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['RaceUserId']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                            $found = 1;
                                            break;
                                        }
                                    }
                                    //如果未找到，则新增
                                    if ($found == 0)
                                    {
                                        $UserRaceInfoList['Total'][count($UserRaceInfoList['Total'])] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'],"TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],"BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['RaceUserId']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                    }
                                }
                                //新建排名数据
                                else
                                {
                                    $UserRaceInfoList['Total'][0] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'], "TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'], "BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['RaceUserId']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                }
                                $t = array();
                                $t0 = array();
                                $t1 = array();
                                $t2 = array();
                                $t3 = array();
                                //再次循环排名数组，依次取出当前位置，总时间，总净时间做排序依据
                                foreach ($UserRaceInfoList['Total'] as $k => $v)
                                {
                                    $t0[$k] = $v['RaceStatus'];
                                    $t1[$k] = $v['CurrentPosition'];
                                    $t2[$k] = $v['TotalTime'];
                                    $t3[$k] = $v['TotalNetTime'];
                                }
                                //根据不同的计时类型进行排序
                                if($FinalResultType=="gunshot")
                                {
                                    array_multisort($t0,SORT_ASC,$t1, SORT_DESC, $t2, SORT_ASC, $UserRaceInfoList['Total']);
                                }
                                else
                                {
                                    array_multisort($t0,SORT_ASC,$t1, SORT_DESC, $t3, SORT_ASC, $UserRaceInfoList['Total']);
                                }
                                $DivisionList = array();
                                foreach($UserRaceInfoList['Total'] as $key => $UserInfo)
                                {
                                    $DivisionList[$UserInfo['RaceGroupId']][] = $UserInfo['BIB'];
                                    $UserRaceInfoList['Total'][$key]['GroupRank'] = count($DivisionList[$UserInfo['RaceGroupId']]);
                                    // 生成总排名
                                    $UserRaceInfoList['Total'][$key]['Rank'] = $key+1;
                                }
                                $UserRaceInfoList['LastId'] = $TimingInfo['Id'];
                                $UserRaceInfoList['LastTime'] = $TimingInfo['time'];

                                //$UserRaceInfoList['LastUpdateRecordCount'] = $TimingRecordTable['RecordCount'];
                                //保存配置文件
                                $oRace->TimgingDataSave($RaceInfo['RaceId'],$UserRaceInfoList,$Cache);
                                //echo "getdataCount1:".count($UserRaceInfoList)."<br>";
                                $num++;
                            }
                            else
                            {

                                //ToDo 首个点未匹配上
                                //如果比赛配置为无起点
                                if(isset($RaceInfo['comment']['NoStart']) && ($RaceInfo['comment']['NoStart']==1))
                                {
                                    {
                                        $ChipTimeOld = $ChipTime;
                                        //记录当前经过的点的位置
                                        $UserRaceInfo['CurrentPoint'] = $i;
                                        //将经过时间统一写成比赛开始时间
                                        $ChipTime=$RaceStartTime;
                                        //记录经过时间
                                        $UserRaceInfo['Point'][$i]['inTime'] = $ChipTime;
                                        //如果前一点的距离为非负数，则取当前时间和前一点差值作为经过时间，否则不计时
                                        $UserRaceInfo['Point'][$i]['PointTime'] = intval($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?(sprintf("%0.2f",($UserRaceInfo['Point'][$i-1]['inTime'])?$UserRaceInfo['Point'][$i]['inTime']-$UserRaceInfo['Point'][$i-1]['inTime']:0)):0;
                                        $UserRaceInfo['Point'][$i]['PointSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$i]['SpeedDisplayType'],$UserRaceInfo['Point'][$i]['PointTime'],($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?$UserRaceInfo['Point'][$i]['ToPrevious']:0);
                                        $UserRaceInfo['Point'][$i]['SportsTime'] = sprintf("%0.2f",$UserRaceInfo['Point'][$i]['CurrentDistance']==$UserRaceInfo['Point'][$i]['ToPrevious']?$UserRaceInfo['Point'][$i]['PointTime']:($UserRaceInfo['Point'][$i]['PointTime']+$UserRaceInfo['Point'][$i-1]['SportsTime']));

                                        $UserRaceInfo['Point'][$i]['SportsSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$i]['SpeedDisplayType'],$UserRaceInfo['Point'][$i]['SportsTime'],$UserRaceInfo['Point'][$i]['CurrentDistance']);
                                        $TotalNetTime = isset($UserRaceInfo['Point'][$i-1]['TotalNetTime'])?sprintf("%0.2f",($UserRaceInfo['Point'][$i-1]['TotalNetTime']+$UserRaceInfo['Point'][$i]['PointTime'])):sprintf("%0.2f",$UserRaceInfo['Point'][$i]['PointTime']);
                                        if($i==1)
                                        {
                                            $TotalTime = sprintf("%0.2f",$UserRaceInfo['Point'][$i]['inTime']-$RaceStartTime);
                                        }
                                        else
                                        {
                                            $TotalTime= sprintf("%0.2f",($UserRaceInfo['Point'][$i-1]['TotalTime'])?$UserRaceInfo['Point'][$i-1]['TotalTime']+$UserRaceInfo['Point'][$i]['PointTime']:$UserRaceInfo['Point'][$i]['PointTime']);
                                        }
                                        $UserRaceInfo['Point'][$i]['TotalTime'] = $TotalTime;
                                        $UserRaceInfo['Point'][$i]['TotalNetTime'] = $TotalNetTime;
                                        unset($UserRaceInfo['Point'][$i]['UserList']);
                                        //保存个人当前计时点的信息
                                        $oRace->UserTimgingDataSave($RaceInfo['RaceId'],$UserList[$TimingInfo['RaceUserId']]['RaceUserId'],$UserRaceInfo,$Cache);
                                        //获取所有选手的比赛信息（计时）
                                        $UserRaceInfoList = $oRace->GetRaceTimingOriginalInfo($RaceId,$Cache);
                                        //echo "SavePointCount3:".count($UserRaceInfoList)."<br>";
                                        //将当前计时点最小的过线记录保存
                                        $UserRaceInfoList['Point'][$i]['inTime'] = $UserRaceInfoList['Point'][$i]['inTime'] == 0 ? $inTime : sprintf("%0.2f", min($UserRaceInfoList['Point'][$i]['inTime'], $ChipTime));
                                        //新增当前点的过线记录
                                        $UserRaceInfoList['Point'][$i]['UserList'][count($UserRaceInfoList['Point'][$i]['UserList'])] = array("PointTime"=>$UserRaceInfo['Point'][$i]['PointTime'],"PointSpeed"=>$UserRaceInfo['Point'][$i]['PointSpeed'] ,"TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],"BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                        //初始化一个空的数组
                                        $t = array();
                                        //循环每个人的过线记录
                                        foreach ($UserRaceInfoList['Point'][$i]['UserList'] as $k => $v)
                                        {
                                            //计算每个人的过线记录和当前点最早记录的时间差
                                            $UserRaceInfoList['Point'][$i]['UserList'][$k]['TimeLag'] = sprintf("%0.2f",abs(sprintf("%0.2f", $UserRaceInfoList['Point'][$i]['inTime']) - sprintf("%0.2f", $v['inTime'])));
                                            //生成排序数组
                                            $t[$k] = $UserRaceInfoList['Point'][$i]['UserList'][$k]['TimeLag'];
                                        }
                                        //根据时间差进行升序排序
                                        array_multisort($t, SORT_ASC, $UserRaceInfoList['Point'][$i]['UserList']);
                                        //初始化一个空的分组排名数组
                                        $DivisionList = array();
                                        //循环当前计时点排名
                                        foreach($UserRaceInfoList['Point'][$i]['UserList'] as $key => $UserInfo)
                                        {
                                            // 生成总排名
                                            $UserRaceInfoList['Point'][$i]['UserList'][$key]['Rank'] = $key+1;
                                            //依次填入分组数据
                                            $DivisionList[$UserInfo['RaceGroupId']][] = $UserInfo['BIB'];
                                            //排名保存
                                            $UserRaceInfoList['Point'][$i]['UserList'][$key]['GroupRank'] = count($DivisionList[$UserInfo['RaceGroupId']]);
                                            $UserInfo['GroupRank'] = $UserRaceInfoList['Point'][$i]['UserList'][$key]['GroupRank'];
                                            //清除原来的积分
                                            unset($UserRaceInfoList['Point'][$i]['UserList'][$key]['Credit']);
                                            //循环积分列表
                                            foreach($UserRaceInfoList['Point'][$i]['CreditList'] as $CreditId => $CreditInfo)
                                            {
                                                //生成积分序列
                                                $CreditSequence = Base_Common::ParthSequence($CreditInfo['CreditRule']);
                                                //如果名次匹配
                                                if(isset($CreditSequence[$UserInfo['GroupRank']]))
                                                {
                                                    //积分相应累加
                                                    $UserRaceInfoList['Point'][$i]['UserList'][$key]['Credit'][$CreditId] = array("Credit"=>round($CreditSequence[$UserRaceInfoList['Point'][$i]['UserList'][$key]['GroupRank']]*$TimeList[$UserInfo['RaceGroupId']]['CreditRatio']),"CreditName"=>$CreditList[$CreditId]['CreditName']);;
                                                }
                                            }
                                        }
                                        unset($UserRaceInfo['Total']['Credit']);
                                        foreach($UserRaceInfo['Point'] as $p => $pInfo)
                                        {
                                            if(isset($pInfo['Credit']))
                                            {
                                                foreach($pInfo['Credit'] as $c => $cInfo)
                                                {
                                                    if(isset($UserRaceInfo['Total']['Credit'][$c]))
                                                    {
                                                        $UserRaceInfo['Total']['Credit'][$c]['Credit'] +=  $cInfo['Credit'];
                                                    }
                                                    else
                                                    {
                                                        $UserRaceInfo['Total']['Credit'][$c] = $cInfo;
                                                    }
                                                }
                                            }
                                        }
                                        //如果现在已有总排名数组
                                        if (isset($UserRaceInfoList['Total']) && count($UserRaceInfoList['Total']))
                                        {
                                            //初始设定为未找到
                                            $found = 0;
                                            //循环已有的排名数据
                                            foreach ($UserRaceInfoList['Total'] as $k => $v)
                                            {
                                                //依次比对现在的用户ID，如果找到了，则更新
                                                if ($v['RaceUserId'] == $UserList[$TimingInfo['RaceUserId']]['RaceUserId'])
                                                {
                                                    $UserRaceInfoList['Total'][$k] = array("CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'], "TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],"BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['RaceUserId']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                                    $found = 1;
                                                    break;
                                                }
                                            }
                                            //如果未找到，则新增
                                            if ($found == 0)
                                            {
                                                $UserRaceInfoList['Total'][count($UserRaceInfoList['Total'])] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'],"TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],"BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['RaceUserId']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                            }
                                        }
                                        //新建排名数据
                                        else
                                        {
                                            $UserRaceInfoList['Total'][0] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'], "TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'], "BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['RaceUserId']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                        }
                                        $t = array();
                                        $t0 = array();$t1 = array();$t2 = array();$t3 = array();
                                        //再次循环排名数组，依次取出当前位置，总时间，总净时间做排序依据
                                        foreach ($UserRaceInfoList['Total'] as $k => $v)
                                        {
                                            $t0[$k] = $v['RaceStatus'];
                                            $t1[$k] = $v['CurrentPosition'];
                                            $t2[$k] = $v['TotalTime'];
                                            $t3[$k] = $v['TotalNetTime'];
                                        }
                                        //根据不同的计时类型进行排序
                                        if($FinalResultType=="gunshot")
                                        {
                                            array_multisort($t0,SORT_ASC,$t1, SORT_DESC, $t2, SORT_ASC, $UserRaceInfoList['Total']);
                                        }
                                        else
                                        {
                                            array_multisort($t0,SORT_ASC,$t1, SORT_DESC, $t3, SORT_ASC, $UserRaceInfoList['Total']);
                                        }
                                        $DivisionList = array();
                                        foreach($UserRaceInfoList['Total'] as $key => $UserInfo)
                                        {
                                            $DivisionList[$UserInfo['RaceGroupId']][] = $UserInfo['BIB'];
                                            $UserRaceInfoList['Total'][$key]['GroupRank'] = count($DivisionList[$UserInfo['RaceGroupId']]);
                                            // 生成总排名
                                            $UserRaceInfoList['Total'][$key][$key]['Rank'] = $key+1;
                                        }
                                        $UserRaceInfoList['LastId'] = $TimingInfo['Id'];
                                        $UserRaceInfoList['LastTime'] = $TimingInfo['time'];
                                        //保存配置文件
                                        $oRace->TimgingDataSave($RaceInfo['RaceId'],$UserRaceInfoList,$Cache);
                                        //echo "getdataCount2:".count($UserRaceInfoList)."<br>";

                                        $num++;
                                    }
                                    $i++;
                                    {
                                        //记录当前经过的点的位置
                                        $UserRaceInfo['CurrentPoint'] = $i;
                                        //将经过时间统一写成比赛开始时间
                                        $ChipTime = $ChipTimeOld;
                                        //记录经过时间
                                        $UserRaceInfo['Point'][$i]['inTime'] = $ChipTime;
                                        //如果前一点的距离为非负数，则取当前时间和前一点差值作为经过时间，否则不计时
                                        $UserRaceInfo['Point'][$i]['PointTime'] = intval($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?(sprintf("%0.2f",($UserRaceInfo['Point'][$i-1]['inTime'])?$UserRaceInfo['Point'][$i]['inTime']-$UserRaceInfo['Point'][$i-1]['inTime']:0)):0;
                                        $UserRaceInfo['Point'][$i]['PointSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$i]['SpeedDisplayType'],$UserRaceInfo['Point'][$i]['PointTime'],($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?$UserRaceInfo['Point'][$i]['ToPrevious']:0);
                                        $UserRaceInfo['Point'][$i]['SportsTime'] = sprintf("%0.2f",$UserRaceInfo['Point'][$i]['CurrentDistance']==$UserRaceInfo['Point'][$i]['ToPrevious']?$UserRaceInfo['Point'][$i]['PointTime']:($UserRaceInfo['Point'][$i]['PointTime']+$UserRaceInfo['Point'][$i-1]['SportsTime']));

                                        $UserRaceInfo['Point'][$i]['SportsSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$i]['SpeedDisplayType'],$UserRaceInfo['Point'][$i]['SportsTime'],$UserRaceInfo['Point'][$i]['CurrentDistance']);
                                        $TotalNetTime = isset($UserRaceInfo['Point'][$i-1]['TotalNetTime'])?sprintf("%0.2f",($UserRaceInfo['Point'][$i-1]['TotalNetTime']+$UserRaceInfo['Point'][$i]['PointTime'])):sprintf("%0.2f",$UserRaceInfo['Point'][$i]['PointTime']);
                                        if($i==1)
                                        {
                                            $TotalTime = sprintf("%0.2f",$UserRaceInfo['Point'][$i]['inTime']-$RaceStartTime);
                                        }
                                        else
                                        {
                                            $TotalTime= sprintf("%0.2f",($UserRaceInfo['Point'][$i-1]['TotalTime'])?$UserRaceInfo['Point'][$i-1]['TotalTime']+$UserRaceInfo['Point'][$i]['PointTime']:$UserRaceInfo['Point'][$i]['PointTime']);
                                        }
                                        $UserRaceInfo['Point'][$i]['TotalTime'] = $TotalTime;
                                        $UserRaceInfo['Point'][$i]['TotalNetTime'] = $TotalNetTime;
                                        unset($UserRaceInfo['Point'][$i]['UserList']);
                                        //保存个人当前计时点的信息
                                        $oRace->UserTimgingDataSave($RaceInfo['RaceId'],$UserList[$TimingInfo['RaceUserId']]['RaceUserId'],$UserRaceInfo,$Cache);
                                        //获取所有选手的比赛信息（计时）
                                        $UserRaceInfoList = $oRace->GetRaceTimingOriginalInfo($RaceId,$Cache);
                                        //echo "SavePointCount3:".count($UserRaceInfoList)."<br>";
                                        //将当前计时点最小的过线记录保存
                                        $UserRaceInfoList['Point'][$i]['inTime'] = $UserRaceInfoList['Point'][$i]['inTime'] == 0 ? $inTime : sprintf("%0.2f", min($UserRaceInfoList['Point'][$i]['inTime'], $ChipTime));
                                        //新增当前点的过线记录
                                        $UserRaceInfoList['Point'][$i]['UserList'][count($UserRaceInfoList['Point'][$i]['UserList'])] = array("PointTime"=>$UserRaceInfo['Point'][$i]['PointTime'],"PointSpeed"=>$UserRaceInfo['Point'][$i]['PointSpeed'] ,"TotalTime" => $TotalTime,"TotalNetTime"=>$TotalNetTime, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],"BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                        //初始化一个空的数组
                                        $t = array();
                                        //循环每个人的过线记录
                                        foreach ($UserRaceInfoList['Point'][$i]['UserList'] as $k => $v)
                                        {
                                            //计算每个人的过线记录和当前点最早记录的时间差
                                            $UserRaceInfoList['Point'][$i]['UserList'][$k]['TimeLag'] = sprintf("%0.2f",abs(sprintf("%0.2f", $UserRaceInfoList['Point'][$i]['inTime']) - sprintf("%0.2f", $v['inTime'])));
                                            //生成排序数组
                                            $t[$k] = $UserRaceInfoList['Point'][$i]['UserList'][$k]['TimeLag'];
                                        }
                                        //根据时间差进行升序排序
                                        array_multisort($t, SORT_ASC, $UserRaceInfoList['Point'][$i]['UserList']);
                                        //初始化一个空的分组排名数组
                                        $DivisionList = array();
                                        //循环当前计时点排名
                                        foreach($UserRaceInfoList['Point'][$i]['UserList'] as $key => $UserInfo)
                                        {
                                            // 生成总排名
                                            $UserRaceInfoList['Point'][$i]['UserList'][$key]['Rank'] = $key+1;
                                            //依次填入分组数据
                                            $DivisionList[$UserInfo['RaceGroupId']][] = $UserInfo['BIB'];
                                            //排名保存
                                            $UserRaceInfoList['Point'][$i]['UserList'][$key]['GroupRank'] = count($DivisionList[$UserInfo['RaceGroupId']]);
                                            $UserInfo['GroupRank'] = $UserRaceInfoList['Point'][$i]['UserList'][$key]['GroupRank'];
                                            //清除原来的积分
                                            unset($UserRaceInfoList['Point'][$i]['UserList'][$key]['Credit']);
                                            //循环积分列表
                                            foreach($UserRaceInfoList['Point'][$i]['CreditList'] as $CreditId => $CreditInfo)
                                            {
                                                //生成积分序列
                                                $CreditSequence = Base_Common::ParthSequence($CreditInfo['CreditRule']);
                                                //如果名次匹配
                                                if(isset($CreditSequence[$UserInfo['GroupRank']]))
                                                {
                                                    //积分相应累加
                                                    $UserRaceInfoList['Point'][$i]['UserList'][$key]['Credit'][$CreditId] = array("Credit"=>round($CreditSequence[$UserRaceInfoList['Point'][$i]['UserList'][$key]['GroupRank']]*$TimeList[$UserInfo['RaceGroupId']]['CreditRatio']),"CreditName"=>$CreditList[$CreditId]['CreditName']);;
                                                }
                                            }
                                        }
                                        unset($UserRaceInfo['Total']['Credit']);
                                        foreach($UserRaceInfo['Point'] as $p => $pInfo)
                                        {
                                            if(isset($pInfo['Credit']))
                                            {
                                                foreach($pInfo['Credit'] as $c => $cInfo)
                                                {
                                                    if(isset($UserRaceInfo['Total']['Credit'][$c]))
                                                    {
                                                        $UserRaceInfo['Total']['Credit'][$c]['Credit'] +=  $cInfo['Credit'];
                                                    }
                                                    else
                                                    {
                                                        $UserRaceInfo['Total']['Credit'][$c] = $cInfo;
                                                    }
                                                }
                                            }
                                        }
                                        //如果现在已有总排名数组
                                        if (isset($UserRaceInfoList['Total']) && count($UserRaceInfoList['Total']))
                                        {
                                            //初始设定为未找到
                                            $found = 0;
                                            //循环已有的排名数据
                                            foreach ($UserRaceInfoList['Total'] as $k => $v)
                                            {
                                                //依次比对现在的用户ID，如果找到了，则更新
                                                if ($v['RaceUserId'] == $UserList[$TimingInfo['RaceUserId']]['RaceUserId'])
                                                {
                                                    $UserRaceInfoList['Total'][$k] = array("CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'], "TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],"BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['RaceUserId']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                                    $found = 1;
                                                    break;
                                                }
                                            }
                                            //如果未找到，则新增
                                            if ($found == 0)
                                            {
                                                $UserRaceInfoList['Total'][count($UserRaceInfoList['Total'])] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'],"TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],"BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['RaceUserId']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                            }
                                        }
                                        //新建排名数据
                                        else
                                        {
                                            $UserRaceInfoList['Total'][0] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'], "TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'], "BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['RaceUserId']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                        }
                                        $t = array();
                                        $t0 = array();$t1 = array();$t2 = array();$t3 = array();
                                        //再次循环排名数组，依次取出当前位置，总时间，总净时间做排序依据
                                        foreach ($UserRaceInfoList['Total'] as $k => $v)
                                        {
                                            $t0[$k] = isset($v['RaceStatus'])?$v['RaceStatus']:5;
                                            $t1[$k] = $v['CurrentPosition'];
                                            $t2[$k] = $v['TotalTime'];
                                            $t3[$k] = $v['TotalNetTime'];
                                        }
                                        //根据不同的计时类型进行排序
                                        if($FinalResultType=="gunshot")
                                        {
                                            array_multisort($t0,SORT_ASC,$t1, SORT_DESC, $t2, SORT_ASC, $UserRaceInfoList['Total']);
                                        }
                                        else
                                        {
                                            array_multisort($t0,SORT_ASC,$t1, SORT_DESC, $t3, SORT_ASC, $UserRaceInfoList['Total']);
                                        }
                                        $DivisionList = array();
                                        foreach($UserRaceInfoList['Total'] as $key => $UserInfo)
                                        {
                                            $DivisionList[$UserInfo['RaceGroupId']][] = $UserInfo['BIB'];
                                            $UserRaceInfoList['Total'][$key]['GroupRank'] = count($DivisionList[$UserInfo['RaceGroupId']]);
                                            // 生成总排名
                                            $UserRaceInfoList['Total'][$key][$key]['Rank'] = $key+1;
                                        }
                                        $UserRaceInfoList['LastId'] = $TimingInfo['Id'];
                                        $UserRaceInfoList['LastTime'] = $TimingInfo['time'];
                                        //保存配置文件
                                        $oRace->TimgingDataSave($RaceInfo['RaceId'],$UserRaceInfoList,$Cache);
                                        //echo "getdataCount2:".count($UserRaceInfoList)."<br>";

                                        $num++;
                                    }

                                }
                                else
                                {

                                }
                            }
                        }
                        else
                        {
                            //净时间为如果首次过线时间存在则用，否则去比赛统一开始时间
                            $StartTime = $UserRaceInfo['Point'][1]['inTime']>0?$UserRaceInfo['Point'][1]['inTime']:$RaceStartTime;
                            //保存当前点的位置
                            $c = $UserRaceInfo['CurrentPoint'];
                            //循环
                            do {
                                //如果当前点存在 且 下一点也存在
                                if (isset($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]) && isset($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']+1]) && ($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']+1]['ChipId']==$TimingInfo['Location']))
                                {
                                    //暂存当前点信息
                                    $CurrentPointInfo = $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']];
                                    //计算本条计时信息和当前点过线时间的时间差
                                    $timeLag = sprintf("%0.2f", ($CurrentPointInfo['inTime'] - $ChipTime));
                                    //如果时间差小于配置的容忍时间（短时间内多次过线）
                                    $CurrentPointInfo['TolaranceTime'] = isset($CurrentPointInfo['TolaranceTime'])?$CurrentPointInfo['TolaranceTime']:$RaceInfo['RouteInfo']['TolaranceTime'];
                                    if (abs($timeLag) <= $CurrentPointInfo['TolaranceTime'])
                                    {
                                        echo $CurrentPointInfo['TolaranceTime']." Second TimeOut Pass\n";
                                        //本条记录废除
                                        break;
                                    }
                                }
                                else
                                {
                                    //echo "Reach The Buttom<br>";
                                    //循环结束
                                    break;
                                }
                            }
                            while
                            (
                                //(如果芯片的位置不符合 或 （位置符合 且 已经记录的过线时间为空） 且 向上累加)
                                (($CurrentPointInfo['ChipId'] != $TimingInfo['Location']) || (($CurrentPointInfo['ChipId'] == $TimingInfo['Location']) && ($CurrentPointInfo['inTime'] != ""))) && ($UserRaceInfo['CurrentPoint']++)
                            );
                            //如果当前点信息内有包含芯片ID(位置合法 且 当前点位置和循环查找之前的不相同（确认移位）)
                            if (isset($CurrentPointInfo) && $CurrentPointInfo['ChipId'] && $c != $UserRaceInfo['CurrentPoint'])
                            {
                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['inTime'] = sprintf("%0.2f",$ChipTime);
                                //如果前一点的距离为非负数，则取当前时间和前一点差值作为经过时间，否则不计时
                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime'] = ($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['ToPrevious'])>=0?(sprintf("%0.2f",($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['inTime'])?$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['inTime']-$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['inTime']:0)):0;
                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['SpeedDisplayType'],$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime'],($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['ToPrevious'])>=0?$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['ToPrevious']:0);
                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['SportsTime'] = sprintf("%0.2f",$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['SportsTypeId']!=$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['SportsTypeId']?$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime']:($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime']+$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['SportsTime']));

                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['SportsSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['SpeedDisplayType'],$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['SportsTime'],$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['CurrentDistance']);
                                $TotalTime = sprintf("%0.2f",($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['TotalTime'])?$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['TotalTime']+$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime']:$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime']);
                                $TotalNetTime = isset($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['TotalNetTime'])?sprintf("%0.2f",($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['TotalNetTime']+$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime'])):sprintf("%0.2f",$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime']);
                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['TotalTime'] = $TotalTime;
                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['TotalNetTime'] = $TotalNetTime;

                                unset($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['UserList']);
                                //保存个人当前计时点的信息
                                $oRace->UserTimgingDataSave($RaceInfo['RaceId'],$UserList[$TimingInfo['RaceUserId']]['RaceUserId'],$UserRaceInfo,$Cache);

                                //如果原过线时间有数值 则获取与现过线时间的较小值，否则就用现过线时间
                                $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['inTime'] = $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['inTime'] == 0 ? sprintf("%0.2f",$ChipTime) : min(sprintf("%0.2f", $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['inTime']), sprintf("%0.2f",$ChipTime));
                                //如果当前点的过线选手列表存在 且 已经有选手过线

                                if (isset($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList']) && count($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList']))
                                {
                                    $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][count($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'])] = array("PointTime"=>$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime'],"PointSpeed"=>$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointSpeed'] ,"TotalTime" => $TotalTime,"TotalNetTime" => $TotalNetTime, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],"BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                }
                                else
                                {
                                    $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][0] = array("PointTime"=>$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime'],"PointSpeed"=>$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointSpeed'],"TotalTime" => $TotalTime,"TotalNetTime" => $TotalNetTime, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],"BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                }


                                //初始化一个空数组
                                $t1 = array();
                                $t2 = array();
                                //循环当前计时点的过线数据
                                foreach ($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'] as $k => $v)
                                {
                                    //计算与本计时点第一位的时间差
                                    $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$k]['TimeLag'] = sprintf("%0.2f",abs($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['inTime'] - $v['inTime']));
                                    //将时间差放入排序用的数组
                                    $t1[$k] = $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$k]['TotalTime'];
                                    $t2[$k] = $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$k]['TotalNetTime'];
                                }
                                if($ResultType=="gunshot")
                                {
                                    array_multisort($t1, SORT_ASC, $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList']);
                                }
                                else
                                {
                                    array_multisort($t2, SORT_ASC, $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList']);
                                }
                                //初始化一个空的分组排名数组
                                $DivisionList = array();
                                //循环当前计时点排名
                                foreach($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'] as $key => $UserInfo)
                                {
                                    // 生成总排名
                                    $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$key]['Rank'] = $key+1;
                                    //依次填入分组数据
                                    $DivisionList[$UserInfo['RaceGroupId']][] = $UserInfo['BIB'];
                                    //排名保存
                                    $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$key]['GroupRank'] = count($DivisionList[$UserInfo['RaceGroupId']]);
                                    $UserInfo['GroupRank'] = $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$key]['GroupRank'];
                                    //清除原来的积分
                                    unset($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$key]['Credit']);
                                    //循环积分列表
                                    if(isset($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['CreditList']))
                                    {
                                        foreach($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['CreditList'] as $CreditId => $CreditInfo)
                                        {

                                            //生成积分序列
                                            $CreditSequence = Base_Common::ParthSequence($CreditInfo['CreditRule']);
                                            //如果名次匹配
                                            if(isset($CreditSequence[$UserInfo['GroupRank']]))
                                            {
                                                //积分相应累加
                                                $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$key]['Credit'][$CreditId] = array("Credit"=>round($CreditSequence[$UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$key]['GroupRank']]*$TimeList[$UserInfo['RaceGroupId']]['CreditRatio']),"CreditName"=>$CreditList[$CreditId]['CreditName']);
                                            }
                                        }
                                    }

                                }
                                //循环总成绩数组
                                if (isset($UserRaceInfoList['Total']) && count($UserRaceInfoList['Total']))
                                {
                                    //初始设定为未找到
                                    $found = 0;
                                    //循环已有的排名数据
                                    foreach ($UserRaceInfoList['Total'] as $k => $v)
                                    {
                                        //依次比对现在的用户ID，如果找到了，则更新
                                        if ($v['RaceUserId'] == $UserList[$TimingInfo['RaceUserId']]['RaceUserId'])
                                        {
                                            $UserRaceInfoList['Total'][$k] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => $UserRaceInfo['CurrentPoint'], "Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&($UserRaceInfo['CurrentPoint']==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['TName'],"TotalTime" => $TotalTime,"TotalNetTime" => $TotalNetTime, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],"BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['RaceUserId']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                            $found = 1;
                                            break;
                                        }
                                    }
                                    //如果未找到，则新增
                                    if ($found == 0)
                                    {
                                        $UserRaceInfoList['Total'][count($UserRaceInfoList['Total'])] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => $UserRaceInfo['CurrentPoint'],"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&($UserRaceInfo['CurrentPoint']==count($UserRaceInfoList['Point'])))?1:0, "CurrentPositionName" => $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['TName'],"TotalTime" => $TotalTime,"TotalNetTime" => $TotalNetTime, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'], "BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['RaceUserId']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                    }
                                }
                                //新建排名数据
                                else
                                {
                                    $UserRaceInfoList['Total'][0] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => $UserRaceInfo['CurrentPoint'], "Finished"=>$UserRaceInfo['CurrentPoint']==count($UserRaceInfoList['Point'])?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['TName'],"TotalTime" => $TotalTime,"TotalNetTime" => $TotalNetTime, "Name" => $UserList[$TimingInfo['RaceUserId']]['Name'],"TeamName"=>$UserList[$TimingInfo['RaceUserId']]['TeamName'], "BIB" => $UserList[$TimingInfo['RaceUserId']]['BIB'], "inTime" => sprintf("%0.2f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['RaceUserId']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['RaceUserId']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['RaceUserId']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['RaceUserId']]['RaceGroupId']);
                                }
                                $t0 = array();
                                $t1 = array();
                                $t2 = array();
                                $t3 = array();
                                foreach ($UserRaceInfoList['Total'] as $k => $v)
                                {
                                    $t0[$k] = isset($v['RaceStatus'])?$v['RaceStatus']:5;
                                    $t1[$k] = $v['CurrentPosition'];
                                    $t2[$k] = $v['TotalTime'];
                                    $t3[$k] = $v['TotalNetTime'];
                                }
                                //根据不同的计时类型进行排序
                                if($FinalResultType=="gunshot")
                                {
                                    array_multisort($t0,SORT_ASC,$t1, SORT_DESC, $t2, SORT_ASC, $UserRaceInfoList['Total']);
                                }
                                else
                                {
                                    array_multisort($t0,SORT_ASC,$t1, SORT_DESC, $t3, SORT_ASC, $UserRaceInfoList['Total']);
                                }
                                foreach ($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'] as $k => $v)
                                {
                                    if($k!=0)
                                    {
                                        $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$k]['NetTimeLag']= sprintf("%0.2f",$v['TotalNetTime']-$UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][1]['TotalNetTime']);
                                    }
                                    else
                                    {
                                        $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$k]['NetTimeLag']= 0;
                                    }
                                }
                                $num++;
                                $DivisionList = array();
                                foreach($UserRaceInfoList['Total'] as $key => $UserInfo)
                                {
                                    $DivisionList[$UserInfo['RaceGroupId']][] = $UserInfo['BIB'];
                                    $UserRaceInfoList['Total'][$key]['GroupRank'] = count($DivisionList[$UserInfo['RaceGroupId']]);
                                    // 生成总排名
                                    $UserRaceInfoList['Total'][$key]['Rank'] = $key+1;
                                }
                                $UserRaceInfoList['LastId'] = $TimingInfo['Id'];
                                $UserRaceInfoList['LastTime'] = $TimingInfo['time'];
                                //保存配置文件
                                $oRace->TimgingDataSave($RaceInfo['RaceId'],$UserRaceInfoList,$Cache);
                                //echo "getdataCount3:".count($UserRaceInfoList)."<br>";

                                $UserRaceInfo['NextPoint'] = $UserRaceInfo['CurrentPoint'];
                            }
                        }
                    }
                    else
                    {
                        echo $num."-".$TimingInfo['Location']."-".($ChipTime)."-".date("Y-m-d H:i:s", $TimingInfo['time'])."超时跳过<br>\n";
                    }
                }

            }
            $Count = count($TimingList['Record']);
            $Text = "";
            $Text.= "Sql:".$TimingList['sql']."\n";
            $Text.= "RecordCount:".$Count."\n";
            $TotalCount+=$Count;
        }
        //重新获取比赛详情
        $TeamRankList = array();
        $UserRaceTimingInfo = $oRace->GetRaceTimingOriginalInfo($RaceId,$Cache);
        foreach($UserRaceTimingInfo['Total'] as $k => $v)
        {
            $UInfo = $oRace->getUserRaceTimingOriginalInfo($RaceId, $v['RaceUserId'],$Cache);
            foreach($UserRaceInfoList['Point'] as $Point => $PointInfo)
            {
                unset($UInfo['Point'][$Point]['Credit']);
                foreach($PointInfo['UserList'] as $R => $RInfo)
                {
                    if(($RInfo['RaceUserId']==$v['RaceUserId']) && isset($RInfo['Credit']))
                    {
                        $UInfo['Point'][$Point]['Credit'] = $RInfo['Credit'];
                        foreach($RInfo['Credit'] as $P => $PInfo)
                        {
                            if(isset($UserRaceTimingInfo['Total'][$k]['Credit'][$P]))
                            {
                                $UserRaceTimingInfo['Total'][$k]['Credit'][$P]['Credit'] += $PInfo['Credit'];
                            }
                            else
                            {
                                $UserRaceTimingInfo['Total'][$k]['Credit'][$P] = array("Credit"=>$PInfo['Credit'],"CreditName"=>$PInfo['CreditName']);
                            }

                            if(isset($RaceInfo['RouteInfo']['ResultCreditList'][$P]) || isset($RaceInfo['RouteInfo']['ResultCreditList'][0]))
                            {
                                if(isset($UserRaceTimingInfo['Total'][$k]['TotalCredit']))
                                {
                                    $UserRaceTimingInfo['Total'][$k]['TotalCredit'] += $PInfo['Credit'];
                                }
                                else
                                {
                                    $UserRaceTimingInfo['Total'][$k]['TotalCredit'] = $PInfo['Credit'];
                                }

                            }
                        }
                        $UInfo['Total']['Credit'] = $UserRaceTimingInfo['Total'][$k]['Credit'];
                    }
                    if(($RInfo['RaceUserId']==$v['RaceUserId']))
                    {
                        $UInfo['Point'][$Point]['Rank'] = $RInfo['Rank'];
                        $UInfo['Point'][$Point]['GroupRank'] = $RInfo['GroupRank'];
                    }
                }
            }
            foreach($UserRaceTimingInfo['Total'] as $u => $uInfo)
            {
                if($uInfo['RaceUserId']==$v['RaceUserId'])
                {
                    $UInfo['Total'] = $uInfo;
                    break;
                }
            }
            //保存个人当前计时点的信息
            $oRace->UserTimgingDataSave($RaceInfo['RaceId'],$v['RaceUserId'],$UInfo,0);
            if(($v['TeamId']>0) && ($v['CurrentPosition'] == count($UserRaceTimingInfo['Point'])))
            {
                if(isset($TeamRankList[$v['RaceGroupId']][$v['TeamId']]) && count($TeamRankList[$v['RaceGroupId']][$v['TeamId']]['UserList'])<$RaceInfo['comment']['TeamResultRank'])
                {
                    $TeamRankList[$v['RaceGroupId']][$v['TeamId']]['UserList'][count($TeamRankList[$v['RaceGroupId']][$v['TeamId']]['UserList'])+1] = $UserRaceTimingInfo['Total'][$k];
                }
                else
                {
                    $TeamRankList[$v['RaceGroupId']][$v['TeamId']]['TeamName'] = $v['TeamName'];
                    $TeamRankList[$v['RaceGroupId']][$v['TeamId']]['UserList'][1] = $UserRaceTimingInfo['Total'][$k];
                }
            }
        }
        if($FinalResultType=="credit")
        {
            $t0 = array();
            $t1 = array();
            $t2 = array();
            $t3 = array();
            foreach ($UserRaceTimingInfo['Total'] as $k => $v)
            {
                $t0[$k] = $v['TotalCredit'];
                $t1[$k] = $v['CurrentPosition'];
                $t2[$k] = $v['TotalTime'];
                $t3[$k] = $v['TotalNetTime'];
            }
            //根据不同的计时类型进行排序
            if($ResultType=="gunshot")
            {
                array_multisort($t0,SORT_DESC,$t1, SORT_DESC, $t2, SORT_ASC, $UserRaceTimingInfo['Total']);
            }
            else
            {
                array_multisort($t0,SORT_DESC,$t1, SORT_DESC, $t3, SORT_ASC, $UserRaceTimingInfo['Total']);
            }
        }
        $TeamRank = array();$i=1;
        $t1 = array();$t2=array();$t3=array();
        foreach($TeamRankList as $GroupId => $GroupInfo)
        {
            foreach($GroupInfo as $k => $v)
            {
                if(isset($TeamRankList[$GroupId][$k]['UserList'][$RaceInfo['comment']['TeamResultRank']]))
                {
                    $TeamRank[$GroupId][$i] = $TeamRankList[$GroupId][$k]['UserList'][$RaceInfo['comment']['TeamResultRank']];
                    $t1[$GroupId][$k] = $TeamRankList[$GroupId][$k]['UserList'][$RaceInfo['comment']['TeamResultRank']]['TotalTime'];
                    $t2[$GroupId][$k] = $TeamRankList[$GroupId][$k]['UserList'][$RaceInfo['comment']['TeamResultRank']]['TotalNetTime'];
                    $t3[$GroupId][$k] = isset($TeamRankList[$GroupId][$k]['UserList'][$RaceInfo['comment']['TeamResultRank']]['TotalCredit'])?$TeamRankList[$GroupId][$k]['UserList'][$RaceInfo['comment']['TeamResultRank']]['TotalCredit']:0;
                    //根据不同的计时类型进行排序
                    if($FinalResultType=="gunshot")
                    {
                        array_multisort( $t1[$GroupId], SORT_ASC, $TeamRank[$GroupId]);
                    }
                    elseif($FinalResultType=="net")
                    {
                        array_multisort( $t2[$GroupId], SORT_ASC, $TeamRank[$GroupId]);
                    }
                    else
                    {
                        array_multisort( $t3[$GroupId], SORT_DESC, $TeamRank[$GroupId]);
                    }
                    $i++;
                }
            }
        }
        foreach($TeamRank as $GroupId => $GroupInfo)
        {
            foreach($GroupInfo as $k => $v)
            {
                if($k>0)
                {
                    //根据不同的计时类型进行排序
                    if($FinalResultType=="gunshot")
                    {
                        $TeamRank[$GroupId][$k]['TimeLag'] = sprintf("%0.2f",$TeamRank[$GroupId][$k]['TotalTime']- $TeamRank[$GroupId][0]['TotalTime']);
                    }
                    elseif($FinalResultType=="net")
                    {
                        $TeamRank[$GroupId][$k]['NetTimeLag'] = sprintf("%0.2f",$TeamRank[$GroupId][$k]['TotalNetTime']- $TeamRank[$GroupId][0]['TotalNetTime']);
                    }
                    else
                    {
                        $TeamRank[$GroupId][$k]['CreditLag'] = $TeamRank[$GroupId][0]['TotalCredit']- $TeamRank[$GroupId][$k]['TotalCredit'];
                    }
                }
                else
                {
                    //根据不同的计时类型进行排序
                    if($FinalResultType=="gunshot")
                    {
                        $TeamRank[$GroupId][0]['TimeLag'] = 0;
                    }
                    elseif($FinalResultType=="net")
                    {
                        $TeamRank[$GroupId][0]['NetTimeLag'] = 0;
                    }
                    else
                    {
                        $TeamRank[$GroupId][0]['CreditLag'] = 0;
                    }
                }
            }
        }
        $UserRaceTimingInfo['Team'] = $TeamRank;
        //$UserRaceInfoList['LastId'] = $TimingInfo['Id'];
        $num++;
        //保存配置文件
        $GenEnd = microtime(true);
        $UserRaceTimingInfo['ProcessingTime'] = Base_Common::parthTimeLag($GenEnd-$GenStart);
        $oRace->TimgingDataSave($RaceInfo['RaceId'],$UserRaceTimingInfo,0);



        $Text.= "TotalCount:".$TotalCount."\n";
        $Text.= date("Y-m-d H:i:s",$GenStart)."~~".date("Y-m-d H:i:s",$GenEnd)."\n";
        $Text.="TotalCost:".$UserRaceTimingInfo['ProcessingTime']."\n";

        $filePath = __APP_ROOT_DIR__ . "Timing" . "/" . $RaceInfo['RaceId'] . "";
        $fileDestinationPath = __APP_ROOT_DIR__ . "Timing" . "/" . $RaceInfo['RaceId'] . "_Data";
        Base_Common::copy_dir($filePath,$fileDestinationPath);

        $filePath = __APP_ROOT_DIR__."log/Timing/";
        $fileName = date("Y-m-d",$GenStart).".log";
        //写入日志文件
        Base_Common::appendLog($filePath,$fileName,$Text);
    }
    //检查计时记录的数量是否变动
    public function checkTimingRecord($TableName,$Force)
    {
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        $table_to_process = str_replace($this->table,$TableName."_".$this->table,$table_to_process);

        //检查需要有可能需要重建的表名（排序后）
        $table_to_copy = $this->db->copyTable($this->table_sorted,$table_to_process."_sorted");
        //原始记录数量
        $RecordCount = $this->db->getTableRecoudCount($table_to_process);
        //排序后的记录数量
        $RecordCountSorted = $this->db->getTableRecoudCount($table_to_copy);
        //如果排序后记录数量不一致  或 要求强制更新
        print_R($RecordCount);
        print_R($RecordCountSorted);
        if(($RecordCount['count'] != $RecordCountSorted['count']) || ($Force == 1))
        {
            echo "StartToRebuild<br>/n";
            //事务开始
            $this->db->begin();
            //清空数据
            $truncate = $this->db->query("truncate ".$table_to_copy);
            //排序/重建数据
            $rebuild_sql = "insert into ".$table_to_copy." (RaceUserId,Location,time,comment) select RaceUserId,Location,time,comment from ".$table_to_process." order by time";
            $rebuild = $this->db->query($rebuild_sql);
            //如果都成功
            if($truncate && $rebuild)
            {
                //返回成功
                $this->db->commit();
                //重新获取表数据
                echo "RebuildFinished<br>/n";
                return array('return'=>true,'rebuild'=>1);
            }
            else
            {
                //返回失败
                $this->db->rollBack();
                return array('return'=>false);
            }
        }
        else
        {
            //返回失败
            return array('return'=>true);
        }
    }
    //新增一条计时记录
    public function getTiming(array $Timing)
    {
        $oRace = new Xrace_Race();
        //获取比赛信息
        $RaceInfo = $oRace->getRace($Timing['RaceId']);
        //如果获取到比赛信息
        if(isset($RaceInfo['RaceId']))
        {
            //获取成绩总表
            $UserRaceInfo = $oRace->GetUserRaceTimingInfo($RaceInfo['RaceId']);
            //如果未获取到
            if(empty($UserRaceInfo))
            {
                //重建计时数据
                $oRace->genRaceLogToText($RaceInfo['RaceId']);
            }
            $oUser = new Xrace_UserInfo();

            if($Timing["RaceUserId"] >0)
            {
                //获取比赛用户信息
                $RaceUserInfo = $oUser->getRaceUser($Timing["RaceUserId"],"RaceUserId,Name");
                //如果找到
                if(isset($RaceUserInfo['RaceUserId']))
                {
                    $RaceUserId = $RaceUserInfo['RaceUserId'];
                }
                else
                {
                    //返回错误
                    return array("return"=>-2);
                }
                //获取报名记录
                $RaceUserList = $oRace->getRaceUserListByFile($RaceInfo["RaceId"]);
                //初始化标识
                $found = 0;
                //循环报名记录
                foreach($RaceUserList['RaceUserList'] as $key => $RaceApplyLog)
                {
                    //如果找到
                    if($RaceApplyLog["RaceUserId"] == $RaceUserId)
                    {
                        $found = 1;
                        break;
                    }
                }
                //如果最终没找到
                if($found == 0)
                {
                    return array("return"=>-4);
                }
                else
                {
                    //获取计时记录
                    $UserRaceInfo = $oRace->getUserRaceInfoByFile($RaceInfo["RaceId"],$RaceUserId);
                    $RaceGroupId = $UserRaceInfo["RaceUserInfo"]["RaceGroupId"];
                    //拼接获取计时数据的参数，注意芯片列表为空时的数据拼接
                    $RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
                    $RaceInfo['RouteInfo'] = json_decode($RaceInfo['RouteInfo'],true);

                    $params = array(
                        'StartTime'=>$RaceGroupId>0?date("Y-m-d H:i:s",strtotime($RaceInfo['comment']['SelectedRaceGroup'][$RaceGroupId]['StartTime'])+0*3600):date("Y-m-d H:i:s",strtotime($RaceInfo['StartTime'])+0*3600),
                        'EndTime'=>$RaceGroupId>0?date("Y-m-d H:i:s",strtotime($RaceInfo['comment']['SelectedRaceGroup'][$RaceGroupId]['EndTime'])+0*3600):date("Y-m-d H:i:s",strtotime($RaceInfo['EndTime'])+0*3600),
                        'prefix'=>$RaceInfo['RouteInfo']['TimePrefix'], 'RaceUser'=>$RaceUserId,'sorted'=>1,'pageSize'=>10000,'Page'=>1);

                    //获取计时数据
                    $TimingList = $this->getTimingData($params);
                    //循环计时点
                    foreach($UserRaceInfo["Point"] as $Point => $PointInfo)
                    {
                        $UserRaceInfo["Point"][$Point]["inTime"] = 0;
                        foreach($PointInfo as $key => $value)
                        {
                            if(!in_array($key,array ("inTime","TName","TencentX","TencentY","ChipId")))
                            {
                                unset($UserRaceInfo["Point"][$Point][$key]);
                            }
                        }
                        $t = 1;
                        $UserRaceInfo["RaceUserInfo"]["Finished"] = 0;
                        foreach($TimingList["Record"] as $Location => $TimingLog)
                        {
                            if($PointInfo["ChipId"] == $TimingLog["Location"])
                            {
                                $UserRaceInfo["Point"][$Point]["inTime"] = $TimingLog["time"];
                                $t = $t * $TimingLog["time"];
                                if($TimingLog["time"]>0)
                                {
                                    $LastInTime = $TimingLog["time"];
                                    if($t > 0)
                                    {
                                        $UserRaceInfo["RaceUserInfo"]["Finished"] = 1;
                                    }
                                    else
                                    {
                                        $UserRaceInfo["RaceUserInfo"]["Finished"] = 0;
                                    }
                                }
                                else
                                {
                                    break;
                                }

                            }
                        }
                    }
                    $StartTime = strtotime($RaceInfo['comment']['SelectedRaceGroup'][$RaceGroupId]['StartTime'])+0*3600;
                    $UserRaceInfo["RaceUserInfo"]["TotalTime"] = $LastInTime - $StartTime;//-16*3600;
                    $UserRaceInfo["RaceUserInfo"]["RaceName"] = $UserRaceInfo["RaceInfo"]["RaceName"];
                    $UserRaceInfo["RaceUserInfo"]["RaceStageId"] = $RaceInfo["RaceStageId"];
                    $UserRaceInfo["RaceUserInfo"]["ResultType"] = $RaceInfo['comment']["ResultType"];
                    $t = $Timing;
                    unset($t["RaceUserId"]);
                    $t['RaceGroupId'] = $RaceGroupId;
                    $Total = $this->getTiming($t);
                    foreach($Total["RaceUserList"] as $key => $UserInfo)
                    {
                        if($UserInfo["RaceUserId"] == $Timing["RaceUserId"])
                        {
                            $UserRaceInfo["RaceUserInfo"]["Rank"] = $key+1;
                        }
                    }
                    return array("return"=>1,"RaceUserInfo"=>$UserRaceInfo["RaceUserInfo"],"TimingLog"=>$UserRaceInfo["Point"]);
                }
            }
            else
            {
                $oRedis = new Base_Cache_Redis("xrace");
                $key = "WechatTiming_".$Timing["RaceId"]."_".$Timing["RaceGroupId"];
                $m = json_decode($key,true);
                if(count($m['RaceUserList']))
                {
                    return array_merge(array("return"=>1,$m,array("cache"=>1)));
                }
                else
                {
                    $RaceUserList = $oRace->getRaceUserListByFile($Timing["RaceId"]);
                    $UserList = array();
                    $Total = array();
                    foreach($RaceUserList["RaceUserList"] as $key => $ApplyInfo)
                    {
                        if($Timing["RaceGroupId"] > 0 )
                        {
                            if($ApplyInfo["RaceGroupId"] == $Timing["RaceGroupId"])
                            {
                                $UserList[$ApplyInfo["RaceUserId"]] = $ApplyInfo["RaceUserId"];
                                $Total[$ApplyInfo["RaceUserId"]] = array("BIB"=>$ApplyInfo["BIB"],"Name"=>$ApplyInfo["Name"],"TeamName"=>$ApplyInfo["TeamName"]);

                            }
                        }
                        else
                        {
                            $UserList[$ApplyInfo["RaceUserId"]] = $ApplyInfo["RaceUserId"];
                            $Total[$ApplyInfo["RaceUserId"]] = array("BIB"=>$ApplyInfo["BIB"],"Name"=>$ApplyInfo["Name"],"TeamName"=>$ApplyInfo["TeamName"],"Finished"=>0,"TotalTime"=>0,"CurrentPoint"=>0);

                        }
                    }
                    //拼接获取计时数据的参数，注意芯片列表为空时的数据拼接
                    $RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
                    $RaceInfo['RouteInfo'] = json_decode($RaceInfo['RouteInfo'],true);
                    $params = array(
                        'StartTime'=>$Timing["RaceGroupId"]>0?date("Y-m-d H:i:s",strtotime($RaceInfo['comment']['SelectedRaceGroup'][$Timing["RaceGroupId"]]['StartTime'])+0*3600):date("Y-m-d H:i:s",strtotime($RaceInfo['StartTime'])+0*3600),
                        'EndTime'=>$Timing["RaceGroupId"]>0?date("Y-m-d H:i:s",strtotime($RaceInfo['comment']['SelectedRaceGroup'][$Timing["RaceGroupId"]]['EndTime'])+0*3600):date("Y-m-d H:i:s",strtotime($RaceInfo['EndTime'])+0*3600),
                        'prefix'=>$RaceInfo['RouteInfo']['TimePrefix'], 'UserList'=>implode(",",$UserList),'sorted'=>1,'pageSize'=>10000,'Page'=>1);
                    //获取计时数据
                    $TimingList = $this->getTimingData($params);
                    $TimingLogList = array();
                    foreach($TimingList["Record"] as $Location => $TimingLog)
                    {
                        $TimingLogList[$TimingLog["RaceUserId"]][$TimingLog["Id"]] = $TimingLog;
                    }
                    //获取计时记录
                    $UserRaceInfo = $oRace->getUserRaceInfoByFile($RaceInfo["RaceId"],current(array_keys($UserList)));
                    foreach($UserList as $key => $RaceUserId)
                    {
                        //循环计时点
                        foreach($UserRaceInfo["Point"] as $Point => $PointInfo)
                        {
                            foreach($TimingLogList[$RaceUserId] as $Location => $TimingLog)
                            {
                                if($PointInfo["ChipId"]==$TimingLog["Location"])
                                {
                                    $Total[$RaceUserId]["RaceUserId"] = $RaceUserId;
                                    $Total[$RaceUserId]["CurrentPoint"] = $Point;
                                    $Total[$RaceUserId]["inTime"] = $TimingLog["time"];
                                    $Total[$RaceUserId]["Finished"] = 0;
                                    $StartTime = strtotime($RaceInfo['comment']['SelectedRaceGroup'][$Timing["RaceGroupId"]]['StartTime'])+0*3600;
                                    $Total[$RaceUserId]["TotalTime"] = $TimingLog["time"] - $StartTime;//; - 16*3600;
                                    if($Point == count($UserRaceInfo["Point"]))
                                    {
                                        $Total[$RaceUserId]["Finished"] = 1;
                                    }
                                }

                            }
                        }
                    }
                    $T1 = array();$T2 = array();$T3 = array();
                    foreach($Total as $RaceUserId => $RaceUserInfo)
                    {
                        if($RaceUserInfo["TotalTime"]>0)
                        {
                            //是否完赛
                            $T1[$RaceUserId] = $RaceUserInfo["Finished"];
                            //当前位置
                            $T3[$RaceUserId] = $RaceUserInfo["CurrentPoint"];
                            //总时间
                            $T2[$RaceUserId] = $RaceUserInfo["TotalTime"];
                        }
                        else
                        {
                            unset($Total[$RaceUserId]);
                        }
                    }

                    //根据时间差进行升序排序
                    //array_multisort($T1, SORT_DESC,$T3,SORT_DESC,$T2,SORT_ASC,$Total);
                    array_multisort($T3,SORT_DESC,$T2,SORT_ASC,$Total);

                    $RaceGroupInfo = $oRace->getRaceGroup($Timing["RaceGroupId"],"RaceGroupId,RaceGroupName");
                    $m = array("RaceUserList"=>$Total,"RaceInfo"=>array("RaceName"=>$RaceInfo["RaceName"],"RaceGroupName"=>$RaceGroupInfo["RaceGroupName"],"ResultType"=>$RaceInfo['comment']["ResultType"]));

                    //写入缓存
                    $oRedis -> set($key,json_encode($m),60);
                    return array_merge(array("return"=>1),$m,array("cache"=>0));
                }

            }
        }
        else
        {
            //返回错误
            return array("return"=>-3);
        }
    }
}

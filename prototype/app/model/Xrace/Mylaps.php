<?php
/**
 * 订单管理相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_Mylaps extends Base_Widget
{
	//声明所用到的表
	protected $table = 'times';
    protected $table_sorted = 'times_sorted';

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
            $table_to_process = str_replace($this->table,$params['prefix'].$this->table,$table_to_process)."_sorted";
        }
        //获得芯片ID
        $whereChip = isset($params['Chip'])?" Chip = '".$params['Chip']."' ":"";
        if(isset($params['ChipList']))
        {
            if($params['ChipList'] == "-1")
            {
                $whereChipList = "0";
            }
            else
            {
                $whereChipList = " Chip in (".$params['ChipList'].") ";
            }
        }
        $whereStart = isset($params['LastId'])?" Id >".$params['LastId']." ":"";
        $whereStartTime = isset($params['StartTime'])?" ChipTime >'".$params['StartTime']."' ":"";
        $whereEndTime = isset($params['EndTime'])?" ChipTime <='".$params['EndTime']."' ":"";
        $Limit = " limit 0,".$params['pageSize'];
        //所有查询条件置入数组
        $whereCondition = array($whereChip,$whereChipList,$whereStart,$whereStartTime,$whereEndTime);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by Id ".($params['revert']==1?"desc":"asc").$Limit;
        echo $sql."<br>";
		$return = $this->db->getAll($sql);
        if($params['getCount']==1)
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
            $table_to_process = str_replace($this->table,$params['prefix'].$this->table,$table_to_process)."_sorted";
        }
        //获得芯片ID
        $whereChip = isset($params['Chip'])?" Chip = '".$params['Chip']."' ":"";
        $whereChipList = isset($params['ChipList']) && $params['ChipList'] != "0"?" Chip in (".$params['ChipList'].") ":"0";
        $whereStart = isset($params['LastId'])?" Id >".$params['LastId']." ":"";
        $whereStartTime = isset($params['StartTime'])?" ChipTime >'".$params['StartTime']."' ":"";
        $whereEndTime = isset($params['EndTime'])?" ChipTime <='".$params['EndTime']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereChip,$whereChipList,$whereStart,$whereStartTime,$whereEndTime);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        $return = $this->db->getOne($sql);
        return array("RecordCount"=>$return);
    }
	//根据比赛ID生成该场比赛的MYLAPS计时数据
	public function genMylapsTimingInfo($RaceId,$Force = 0,$Cache = 0)
	{
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
        $RecordCheck = $this->checkTimingRecord($RaceInfo['RouteInfo']['TimePrefix'],$Force);
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
			if (trim($ApplyInfo['ChipId']) && trim($ApplyInfo['BIB']))
			{
				//拼接字符串加入到芯片列表
				$ChipList[] = "'" . $ApplyInfo['ChipId'] . "'";
				//分别保存用户的ID,姓名和BIB
				$UserList[$ApplyInfo['ChipId']] = $ApplyInfo;
			}
		}
        echo "比赛时间：".$RaceInfo['StartTime'].".".sprintf("%03d",isset($RaceInfo['comment']['RaceStartMicro'])?$RaceInfo['comment']['RaceStartMicro']:0)."~".$RaceInfo['EndTime']."<br>\n";
		echo "芯片列表：".implode(",",$ChipList)."\n";
        //获取文件最后的
        $LastId = $UserRaceTimingInfo['LastId'];
        //单页记录数量
		$pageSize = 1000;
		//默认第一次有获取到
		$Count = $pageSize;
		//初始化当前芯片（选手）
		$currentChip = "";
		while ($Count == $pageSize)
		{
		    //拼接获取计时数据的参数，注意芯片列表为空时的数据拼接
			$params = array('sorted'=>1,'StartTime'=>date("Y-m-d H:i:s",strtotime($RaceInfo['StartTime'])+0*3600),'EndTime'=>date("Y-m-d H:i:s",strtotime($RaceInfo['EndTime'])+0*3600),'prefix'=>$RaceInfo['RouteInfo']['TimePrefix'],'LastId'=>$LastId, 'pageSize'=>$pageSize, 'ChipList'=>count($ChipList) ? implode(",",$ChipList):"-1");
			//获取计时数据
			$TimingList = $this->getTimingData($params);
			//依次循环计时数据
			foreach ($TimingList['Record'] as $Key => $TimingInfo)
			{
                //最后获取到的记录ID
			    $LastId = $TimingInfo['Id'];
				//mylaps系统中生成的时间一直比当前时间晚8小时，修正
				$TimingInfo['ChipTime'] = strtotime($TimingInfo['ChipTime']) - 0*3600;
				//对于毫秒数据进行四舍五入
				$miliSec = substr($TimingInfo['MilliSecs'], -3) / 1000;
				//计算实际的时间
				$TimingInfo['ChipTime'] = $miliSec>=0.5?($TimingInfo['ChipTime']-1):$TimingInfo['ChipTime'];
				//时间进行累加
                $inTime = sprintf("%0.3f", $TimingInfo['time'])-0*3600;
                $ChipTime = $inTime;
                //如果时间在比赛的开始时间和结束时间之内
                $RaceStartTime = $TimeList[$UserList[$TimingInfo['Chip']]['RaceGroupId']]['RaceStartTime'];
                $RaceEndTime = $TimeList[$UserList[$TimingInfo['Chip']]['RaceGroupId']]['RaceEndTime'];
                //如果当前芯片 和 循环到的计时数据不同 （说明已经结束了上一个选手的循环）
                if ($currentChip != $TimingInfo['Chip'])
                {
                    $num=1;
                    //将当前位置置为循环到的计时点
                    $currentChip = $TimingInfo['Chip'];
                    //调试信息
                    if(isset($UserList[$TimingInfo['Chip']]))
                    {
                        //echo "芯片:".$currentChip . ",用户ID:".$UserList[$TimingInfo['Chip']]['RaceUserId'].",姓名:".$UserList[$TimingInfo['Chip']]['Name'] .",队伍:" . $UserList[$TimingInfo['Chip']]['TeamName'] ."-号码:" . $UserList[$TimingInfo['Chip']]['BIB']."用户分组:".$UserList[$TimingInfo['Chip']]['RaceGroupId']."\n<br>";
                    }
                    else
                    {
                        //echo "芯片:".$currentChip . ",用户 Undifined:". "\n";
                    }
                    $UserRaceStatusInfo = $oUser->getRaceApplyUserInfo($UserList[$TimingInfo['Chip']]['ApplyId'],"RaceStatus,comment");
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
                        echo $num."-".$TimingInfo['Location']."-".($ChipTime)."-".date("Y-m-d H:i:s", $TimingInfo['time']).".".(substr($miliSec,2))."<br>\n";
                        //获取选手的比赛信息（计时）
                        $UserRaceInfo = $oRace->getUserRaceTimingOriginalInfo($RaceId, $UserList[$TimingInfo['Chip']]['RaceUserId'],$Cache);
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
                                    $UserRaceInfo['Point'][$i]['PointTime'] = sprintf("%0.3f",$UserRaceInfo['Point'][$i]['inTime']-$RaceStartTime);
                                }
                                else
                                {
                                    $UserRaceInfo['Point'][$i]['PointTime'] = isset($UserRaceInfo['Point'][$i]['ToPrevious'])&&intval($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?(isset($UserRaceInfo['Point'][$i-1]['inTime'])?sprintf("%0.3f",($UserRaceInfo['Point'][$i]['inTime']-$UserRaceInfo['Point'][$i-1]['inTime'])):0):0;
                                }
                                $UserRaceInfo['Point'][$i]['PointTime'] = isset($UserRaceInfo['Point'][$i]['ToPrevious'])&&intval($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?(isset($UserRaceInfo['Point'][$i-1]['inTime'])?sprintf("%0.3f",($UserRaceInfo['Point'][$i]['inTime']-$UserRaceInfo['Point'][$i-1]['inTime'])):0):0;
                                $UserRaceInfo['Point'][$i]['PointSpeed'] = isset($UserRaceInfo['Point'][$i])?(Base_Common::speedDisplayParth($UserRaceInfo['Point'][$i]['SpeedDisplayType'],$UserRaceInfo['Point'][$i]['PointTime'],($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?$UserRaceInfo['Point'][$i]['ToPrevious']:0)):"";


                                $TotalNetTime = isset($UserRaceInfo['Point'][$i-1]['TotalNetTime'])?sprintf("%0.3f",($UserRaceInfo['Point'][$i-1]['TotalNetTime']+$UserRaceInfo['Point'][$i]['PointTime'])):sprintf("%0.3f",$UserRaceInfo['Point'][$i]['PointTime']);
                                if($ResultType!=="gunshot")
                                {
                                    $TotalNetTime = $TotalNetTime-$UserRaceInfo['Point'][$i]['PointTime'];
                                }


                                if($i==1)
                                {
                                    $TotalTime = sprintf("%0.3f",$UserRaceInfo['Point'][$i]['inTime']-$RaceStartTime);
                                }
                                else
                                {
                                    $TotalTime = sprintf("%0.3f",($UserRaceInfo['Point'][$i-1]['TotalTime'])?$UserRaceInfo['Point'][$i-1]['TotalTime']+$UserRaceInfo['Point'][$i]['PointTime']:$UserRaceInfo['Point'][$i]['PointTime']);
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
                                $oRace->UserTimgingDataSave($RaceInfo['RaceId'],$UserList[$TimingInfo['Chip']]['RaceUserId'],$UserRaceInfo,$Cache);
                                //获取所有选手的比赛信息（计时）
                                $UserRaceInfoList = $oRace->GetRaceTimingOriginalInfo($RaceId,$Cache);
                                //echo "SaveDataCount1:".count($UserRaceInfoList)."<br>";
                                //将当前计时点最小的过线记录保存
                                $UserRaceInfoList['Point'][$i]['inTime'] = $UserRaceInfoList['Point'][$i]['inTime'] == 0 ? $inTime : sprintf("%0.3f", min($UserRaceInfoList['Point'][$i]['inTime'], $ChipTime));
                                //新增当前点的过线记录
                                $UserRaceInfoList['Point'][$i]['UserList'][count($UserRaceInfoList['Point'][$i]['UserList'])] = array("PointTime"=>$UserRaceInfo['Point'][$i]['PointTime'],"PointSpeed"=>$UserRaceInfo['Point'][$i]['PointSpeed'] ,"TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['Chip']]['Name'], "TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'],"BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                //初始化一个空的数组
                                $t = array();
                                //循环每个人的过线记录
                                foreach ($UserRaceInfoList['Point'][$i]['UserList'] as $k => $v)
                                {
                                    //计算每个人的过线记录和当前点最早记录的时间差
                                    $UserRaceInfoList['Point'][$i]['UserList'][$k]['TimeLag'] = sprintf("%0.3f",abs(sprintf("%0.3f", $UserRaceInfoList['Point'][$i]['inTime']) - sprintf("%0.3f", $v['inTime'])));
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
                                        if ($v['RaceUserId'] == $UserList[$TimingInfo['Chip']]['RaceUserId'])
                                        {
                                            $UserRaceInfoList['Total'][$k] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'], "TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'], "BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['Chip']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['Chip']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                            $found = 1;
                                            break;
                                        }
                                    }
                                    //如果未找到，则新增
                                    if ($found == 0)
                                    {
                                        $UserRaceInfoList['Total'][count($UserRaceInfoList['Total'])] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'],"TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'],"BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['Chip']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['Chip']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                    }
                                }
                                //新建排名数据
                                else
                                {
                                    $UserRaceInfoList['Total'][0] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'], "TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'], "BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['Chip']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['Chip']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
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
                                            $UserRaceInfo['Point'][$i]['PointTime'] = intval($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?(sprintf("%0.3f",($UserRaceInfo['Point'][$i-1]['inTime'])?$UserRaceInfo['Point'][$i]['inTime']-$UserRaceInfo['Point'][$i-1]['inTime']:0)):0;
                                            $UserRaceInfo['Point'][$i]['PointSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$i]['SpeedDisplayType'],$UserRaceInfo['Point'][$i]['PointTime'],($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?$UserRaceInfo['Point'][$i]['ToPrevious']:0);
                                            $UserRaceInfo['Point'][$i]['SportsTime'] = sprintf("%0.3f",$UserRaceInfo['Point'][$i]['CurrentDistance']==$UserRaceInfo['Point'][$i]['ToPrevious']?$UserRaceInfo['Point'][$i]['PointTime']:($UserRaceInfo['Point'][$i]['PointTime']+$UserRaceInfo['Point'][$i-1]['SportsTime']));

                                            $UserRaceInfo['Point'][$i]['SportsSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$i]['SpeedDisplayType'],$UserRaceInfo['Point'][$i]['SportsTime'],$UserRaceInfo['Point'][$i]['CurrentDistance']);
                                            $TotalNetTime = isset($UserRaceInfo['Point'][$i-1]['TotalNetTime'])?sprintf("%0.3f",($UserRaceInfo['Point'][$i-1]['TotalNetTime']+$UserRaceInfo['Point'][$i]['PointTime'])):sprintf("%0.3f",$UserRaceInfo['Point'][$i]['PointTime']);
                                            if($i==1)
                                            {
                                                $TotalTime = sprintf("%0.3f",$UserRaceInfo['Point'][$i]['inTime']-$RaceStartTime);
                                            }
                                            else
                                            {
                                                $TotalTime= sprintf("%0.3f",($UserRaceInfo['Point'][$i-1]['TotalTime'])?$UserRaceInfo['Point'][$i-1]['TotalTime']+$UserRaceInfo['Point'][$i]['PointTime']:$UserRaceInfo['Point'][$i]['PointTime']);
                                            }
                                            $UserRaceInfo['Point'][$i]['TotalTime'] = $TotalTime;
                                            $UserRaceInfo['Point'][$i]['TotalNetTime'] = $TotalNetTime;
                                            unset($UserRaceInfo['Point'][$i]['UserList']);
                                            //保存个人当前计时点的信息
                                            $oRace->UserTimgingDataSave($RaceInfo['RaceId'],$UserList[$TimingInfo['Chip']]['RaceUserId'],$UserRaceInfo,$Cache);
                                            //获取所有选手的比赛信息（计时）
                                            $UserRaceInfoList = $oRace->GetRaceTimingOriginalInfo($RaceId,$Cache);
                                            //echo "SavePointCount3:".count($UserRaceInfoList)."<br>";
                                            //将当前计时点最小的过线记录保存
                                            $UserRaceInfoList['Point'][$i]['inTime'] = $UserRaceInfoList['Point'][$i]['inTime'] == 0 ? $inTime : sprintf("%0.3f", min($UserRaceInfoList['Point'][$i]['inTime'], $ChipTime));
                                            //新增当前点的过线记录
                                            $UserRaceInfoList['Point'][$i]['UserList'][count($UserRaceInfoList['Point'][$i]['UserList'])] = array("PointTime"=>$UserRaceInfo['Point'][$i]['PointTime'],"PointSpeed"=>$UserRaceInfo['Point'][$i]['PointSpeed'] ,"TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'],"BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                            //初始化一个空的数组
                                            $t = array();
                                            //循环每个人的过线记录
                                            foreach ($UserRaceInfoList['Point'][$i]['UserList'] as $k => $v)
                                            {
                                                //计算每个人的过线记录和当前点最早记录的时间差
                                                $UserRaceInfoList['Point'][$i]['UserList'][$k]['TimeLag'] = sprintf("%0.3f",abs(sprintf("%0.3f", $UserRaceInfoList['Point'][$i]['inTime']) - sprintf("%0.3f", $v['inTime'])));
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
                                                    if ($v['RaceUserId'] == $UserList[$TimingInfo['Chip']]['RaceUserId'])
                                                    {
                                                        $UserRaceInfoList['Total'][$k] = array("CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'], "TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'],"BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['Chip']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['Chip']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                                        $found = 1;
                                                        break;
                                                    }
                                                }
                                                //如果未找到，则新增
                                                if ($found == 0)
                                                {
                                                    $UserRaceInfoList['Total'][count($UserRaceInfoList['Total'])] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'],"TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'],"BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['Chip']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['Chip']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                                }
                                            }
                                            //新建排名数据
                                            else
                                            {
                                                $UserRaceInfoList['Total'][0] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'], "TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'], "BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['Chip']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['Chip']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
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
                                            $UserRaceInfo['Point'][$i]['PointTime'] = intval($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?(sprintf("%0.3f",($UserRaceInfo['Point'][$i-1]['inTime'])?$UserRaceInfo['Point'][$i]['inTime']-$UserRaceInfo['Point'][$i-1]['inTime']:0)):0;
                                            $UserRaceInfo['Point'][$i]['PointSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$i]['SpeedDisplayType'],$UserRaceInfo['Point'][$i]['PointTime'],($UserRaceInfo['Point'][$i]['ToPrevious'])>=0?$UserRaceInfo['Point'][$i]['ToPrevious']:0);
                                            $UserRaceInfo['Point'][$i]['SportsTime'] = sprintf("%0.3f",$UserRaceInfo['Point'][$i]['CurrentDistance']==$UserRaceInfo['Point'][$i]['ToPrevious']?$UserRaceInfo['Point'][$i]['PointTime']:($UserRaceInfo['Point'][$i]['PointTime']+$UserRaceInfo['Point'][$i-1]['SportsTime']));

                                            $UserRaceInfo['Point'][$i]['SportsSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$i]['SpeedDisplayType'],$UserRaceInfo['Point'][$i]['SportsTime'],$UserRaceInfo['Point'][$i]['CurrentDistance']);
                                            $TotalNetTime = isset($UserRaceInfo['Point'][$i-1]['TotalNetTime'])?sprintf("%0.3f",($UserRaceInfo['Point'][$i-1]['TotalNetTime']+$UserRaceInfo['Point'][$i]['PointTime'])):sprintf("%0.3f",$UserRaceInfo['Point'][$i]['PointTime']);
                                            if($i==1)
                                            {
                                                $TotalTime = sprintf("%0.3f",$UserRaceInfo['Point'][$i]['inTime']-$RaceStartTime);
                                            }
                                            else
                                            {
                                                $TotalTime= sprintf("%0.3f",($UserRaceInfo['Point'][$i-1]['TotalTime'])?$UserRaceInfo['Point'][$i-1]['TotalTime']+$UserRaceInfo['Point'][$i]['PointTime']:$UserRaceInfo['Point'][$i]['PointTime']);
                                            }
                                            $UserRaceInfo['Point'][$i]['TotalTime'] = $TotalTime;
                                            $UserRaceInfo['Point'][$i]['TotalNetTime'] = $TotalNetTime;
                                            unset($UserRaceInfo['Point'][$i]['UserList']);
                                            //保存个人当前计时点的信息
                                            $oRace->UserTimgingDataSave($RaceInfo['RaceId'],$UserList[$TimingInfo['Chip']]['RaceUserId'],$UserRaceInfo,$Cache);
                                            //获取所有选手的比赛信息（计时）
                                            $UserRaceInfoList = $oRace->GetRaceTimingOriginalInfo($RaceId,$Cache);
                                            //echo "SavePointCount3:".count($UserRaceInfoList)."<br>";
                                            //将当前计时点最小的过线记录保存
                                            $UserRaceInfoList['Point'][$i]['inTime'] = $UserRaceInfoList['Point'][$i]['inTime'] == 0 ? $inTime : sprintf("%0.3f", min($UserRaceInfoList['Point'][$i]['inTime'], $ChipTime));
                                            //新增当前点的过线记录
                                            $UserRaceInfoList['Point'][$i]['UserList'][count($UserRaceInfoList['Point'][$i]['UserList'])] = array("PointTime"=>$UserRaceInfo['Point'][$i]['PointTime'],"PointSpeed"=>$UserRaceInfo['Point'][$i]['PointSpeed'] ,"TotalTime" => $TotalTime,"TotalNetTime"=>$TotalNetTime, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'],"BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                            //初始化一个空的数组
                                            $t = array();
                                            //循环每个人的过线记录
                                            foreach ($UserRaceInfoList['Point'][$i]['UserList'] as $k => $v)
                                            {
                                                //计算每个人的过线记录和当前点最早记录的时间差
                                                $UserRaceInfoList['Point'][$i]['UserList'][$k]['TimeLag'] = sprintf("%0.3f",abs(sprintf("%0.3f", $UserRaceInfoList['Point'][$i]['inTime']) - sprintf("%0.3f", $v['inTime'])));
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
                                                    if ($v['RaceUserId'] == $UserList[$TimingInfo['Chip']]['RaceUserId'])
                                                    {
                                                        $UserRaceInfoList['Total'][$k] = array("CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'], "TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'],"BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['Chip']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['Chip']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                                        $found = 1;
                                                        break;
                                                    }
                                                }
                                                //如果未找到，则新增
                                                if ($found == 0)
                                                {
                                                    $UserRaceInfoList['Total'][count($UserRaceInfoList['Total'])] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'],"TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'],"BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['Chip']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['Chip']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                                }
                                            }
                                            //新建排名数据
                                            else
                                            {
                                                $UserRaceInfoList['Total'][0] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => 1,"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&(1==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][1]['TName'], "TotalTime" => $TotalTime,"TotalNetTime"=>0, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'], "BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['Chip']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['Chip']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
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
                                    $timeLag = sprintf("%0.3f", ($CurrentPointInfo['inTime'] - $ChipTime));
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
                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['inTime'] = sprintf("%0.3f",$ChipTime);
                                //如果前一点的距离为非负数，则取当前时间和前一点差值作为经过时间，否则不计时
                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime'] = ($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['ToPrevious'])>=0?(sprintf("%0.3f",($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['inTime'])?$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['inTime']-$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['inTime']:0)):0;
                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['SpeedDisplayType'],$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime'],($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['ToPrevious'])>=0?$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['ToPrevious']:0);
                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['SportsTime'] = sprintf("%0.3f",$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['SportsTypeId']!=$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['SportsTypeId']?$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime']:($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime']+$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['SportsTime']));

                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['SportsSpeed'] = Base_Common::speedDisplayParth($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['SpeedDisplayType'],$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['SportsTime'],$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['CurrentDistance']);
                                $TotalTime = sprintf("%0.3f",($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['TotalTime'])?$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['TotalTime']+$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime']:$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime']);
                                $TotalNetTime = isset($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['TotalNetTime'])?sprintf("%0.3f",($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']-1]['TotalNetTime']+$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime'])):sprintf("%0.3f",$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime']);
                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['TotalTime'] = $TotalTime;
                                $UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['TotalNetTime'] = $TotalNetTime;

                                unset($UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['UserList']);
                                //保存个人当前计时点的信息
                                $oRace->UserTimgingDataSave($RaceInfo['RaceId'],$UserList[$TimingInfo['Chip']]['RaceUserId'],$UserRaceInfo,$Cache);

                                //如果原过线时间有数值 则获取与现过线时间的较小值，否则就用现过线时间
                                $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['inTime'] = $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['inTime'] == 0 ? sprintf("%0.3f",$ChipTime) : min(sprintf("%0.3f", $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['inTime']), sprintf("%0.3f",$ChipTime));
                                //如果当前点的过线选手列表存在 且 已经有选手过线

                                if (isset($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList']) && count($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList']))
                                {
                                    $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][count($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'])] = array("PointTime"=>$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime'],"PointSpeed"=>$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointSpeed'] ,"TotalTime" => $TotalTime,"TotalNetTime" => $TotalNetTime, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'],"BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                }
                                else
                                {
                                    $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][0] = array("PointTime"=>$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointTime'],"PointSpeed"=>$UserRaceInfo['Point'][$UserRaceInfo['CurrentPoint']]['PointSpeed'],"TotalTime" => $TotalTime,"TotalNetTime" => $TotalNetTime, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'],"BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                }


                                //初始化一个空数组
                                $t1 = array();
                                $t2 = array();
                                //循环当前计时点的过线数据
                                foreach ($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'] as $k => $v)
                                {
                                    //计算与本计时点第一位的时间差
                                    $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$k]['TimeLag'] = sprintf("%0.3f",abs($UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['inTime'] - $v['inTime']));
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
                                        if ($v['RaceUserId'] == $UserList[$TimingInfo['Chip']]['RaceUserId'])
                                        {
                                            $UserRaceInfoList['Total'][$k] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => $UserRaceInfo['CurrentPoint'], "Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&($UserRaceInfo['CurrentPoint']==count($UserRaceInfoList['Point'])))?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['TName'],"TotalTime" => $TotalTime,"TotalNetTime" => $TotalNetTime, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'],"BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => sprintf("%0.3f",$ChipTime), 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['Chip']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['Chip']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                            $found = 1;
                                            break;
                                        }
                                    }
                                    //如果未找到，则新增
                                    if ($found == 0)
                                    {
                                        $UserRaceInfoList['Total'][count($UserRaceInfoList['Total'])] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => $UserRaceInfo['CurrentPoint'],"Finished"=>(($UserRaceStatusInfo['RaceStatus']==0)&&($UserRaceInfo['CurrentPoint']==count($UserRaceInfoList['Point'])))?1:0, "CurrentPositionName" => $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['TName'],"TotalTime" => $TotalTime,"TotalNetTime" => $TotalNetTime, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'], "BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => $TimingInfo['ChipTime'] + $miliSec, 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['Chip']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['Chip']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
                                    }
                                }
                                //新建排名数据
                                else
                                {
                                    $UserRaceInfoList['Total'][0] = array("RaceStatus"=>$UserRaceStatusInfo['RaceStatus'],"CurrentPosition" => $UserRaceInfo['CurrentPoint'], "Finished"=>$UserRaceInfo['CurrentPoint']==count($UserRaceInfoList['Point'])?1:0,"CurrentPositionName" => $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['TName'],"TotalTime" => $TotalTime,"TotalNetTime" => $TotalNetTime, "Name" => $UserList[$TimingInfo['Chip']]['Name'],"TeamName"=>$UserList[$TimingInfo['Chip']]['TeamName'], "BIB" => $UserList[$TimingInfo['Chip']]['BIB'], "inTime" => $TimingInfo['ChipTime'] + $miliSec, 'RaceUserId' => $UserList[$TimingInfo['Chip']]['RaceUserId'],'TeamName'=>$UserList[$TimingInfo['Chip']]['TeamName'],'TeamId'=>$UserList[$TimingInfo['Chip']]['TeamId'],'RaceGroupId'=>$UserList[$TimingInfo['Chip']]['RaceGroupId']);
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
                                        $UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][$k]['NetTimeLag']= sprintf("%0.3f",$v['TotalNetTime']-$UserRaceInfoList['Point'][$UserRaceInfo['CurrentPoint']]['UserList'][1]['TotalNetTime']);
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
                        echo $num."-".$TimingInfo['Location']."-".($ChipTime)."-".date("Y-m-d H:i:s", $TimingInfo['ChipTime']).".".(substr($miliSec,2))."超时跳过<br>\n";
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
        //echo "SavePointCount3:".count($UserRaceInfoList)."<br>";

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
//			    if(isset($TeamRankList[$v['RaceGroupId']][$v['TeamId']]) && count($TeamRankList[$v['RaceGroupId']][$v['TeamId']]['UserList'])<$RaceInfo['comment']['TeamResultRank'])
                    if(isset($TeamRankList[$v['RaceGroupId']][$v['TeamId']]))

                    {
					$TeamRankList[$v['RaceGroupId']][$v['TeamId']]['UserList'][count($TeamRankList[$v['RaceGroupId']][$v['TeamId']]['UserList'])] = $UserRaceTimingInfo['Total'][$k];
				}
				else
				{
					$TeamRankList[$v['RaceGroupId']][$v['TeamId']]['TeamName'] = $v['TeamName'];
					$TeamRankList[$v['RaceGroupId']][$v['TeamId']]['UserList'][0] = $UserRaceTimingInfo['Total'][$k];
				}
			}
		}
		//根据积分排名排序总表
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
        //团队排名处理
		$TeamRank = array();
		$t1 = array();$t2=array();$t3=array();
        //循环每个分组
		foreach($TeamRankList as $GroupId => $GroupInfo)
        {
            //循环分组下各个队伍
            foreach($GroupInfo as $k => $v)
            {

                //如果人数够
                if(count($TeamRankList[$GroupId][$k]['UserList'])>=$RaceInfo['comment']['TeamResultRank'])
                //if(isset($TeamRankList[$GroupId][$k]['UserList'][$RaceInfo['comment']['TeamResultRank']]))
                {
                    $tt1 = $tt2 = $tt3 = array();
                    foreach($v['UserList'] as $Uid => $UInfo)
                    {
                        $tt1[$Uid] = $UInfo['TotalTime'];
                        $tt2[$Uid] = $UInfo['TotalNetTime'];
                        $tt3[$Uid] = intval($UInfo['TotalCredit']);
                    }
                    //根据不同的计时类型进行排序
                    if($FinalResultType=="gunshot")
                    {
                        array_multisort( $tt1, SORT_ASC, $TeamRankList[$GroupId][$k]['UserList']);
                    }
                    elseif($FinalResultType=="net")
                    {
                        array_multisort( $tt2, SORT_ASC, $TeamRankList[$GroupId][$k]['UserList']);
                    }
                    else
                    {
                        array_multisort( $tt3, SORT_DESC, $TeamRankList[$GroupId][$k]['UserList']);
                    }

                    if($RaceInfo['comment']['TeamResultRankType'] == "Top")
                    {
                        $TeamRank[$GroupId][$k] = $TeamRankList[$GroupId][$k]['UserList'][$RaceInfo['comment']['TeamResultRank']-1];
                        $t1[$GroupId][$k] = $TeamRankList[$GroupId][$k]['UserList'][$RaceInfo['comment']['TeamResultRank']-1]['TotalTime'];
                        $t2[$GroupId][$k] = $TeamRankList[$GroupId][$k]['UserList'][$RaceInfo['comment']['TeamResultRank']-1]['TotalNetTime'];
                        $t3[$GroupId][$k] = isset($TeamRankList[$GroupId][$k]['UserList'][$RaceInfo['comment']['TeamResultRank']-1]['TotalCredit'])?$TeamRankList[$GroupId][$k]['UserList'][$RaceInfo['comment']['TeamResultRank']-1]['TotalCredit']:0;
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
                    }
                    elseif($RaceInfo['comment']['TeamResultRankType'] == "Sum")
                    {
                        //累加所需选手的数据
                        for($n=0;$n<$RaceInfo['comment']['TeamResultRank'];$n++)
                        {
                            $TeamRank[$GroupId][$k]['TeamName'] = isset($TeamRank[$GroupId][$k]['TeamName'])?$TeamRank[$GroupId][$k]['TeamName']:$v['TeamName'];
                            $TeamRank[$GroupId][$k]['UserList'][$n] = $TeamRankList[$GroupId][$k]['UserList'][$n];
                            $TeamRank[$GroupId][$k]['TotalTime'] += $TeamRankList[$GroupId][$k]['UserList'][$n]['TotalTime'];
                            $TeamRank[$GroupId][$k]['TotalNetTime'] += $TeamRankList[$GroupId][$k]['UserList'][$n]['TotalNetTime'];
                            $TeamRank[$GroupId][$k]['TotalCredit'] += isset($TeamRankList[$GroupId][$k]['UserList'][$n]['TotalCredit'])?$TeamRankList[$GroupId][$k]['UserList'][$n]['TotalCredit']:0;


                            $t1[$GroupId][$k] = $TeamRank[$GroupId][$k]['TotalTime'];
                            $t2[$GroupId][$k] = $TeamRank[$GroupId][$k]['TotalNetTime'];
                            $t3[$GroupId][$k] = $TeamRank[$GroupId][$k]['TotalCredit'];
                        }
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
                    }
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
                        $TeamRank[$GroupId][$k]['TimeLag'] = sprintf("%0.3f",$TeamRank[$GroupId][$k]['TotalTime']- $TeamRank[$GroupId][0]['TotalTime']);
                    }
                    elseif($FinalResultType=="net")
                    {
                        $TeamRank[$GroupId][$k]['NetTimeLag'] = sprintf("%0.3f",$TeamRank[$GroupId][$k]['TotalNetTime']- $TeamRank[$GroupId][0]['TotalNetTime']);
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
        $num++;
		//保存配置文件
        $GenEnd = microtime(true);
        $UserRaceTimingInfo['ProcessingTime'] = Base_Common::parthTimeLag($GenEnd-$GenStart);
        $oRace->TimgingDataSave($RaceInfo['RaceId'],$UserRaceTimingInfo,0);
        //获取赛段列表
        $SegmentList = $oRace->getSegmentListByFile($RaceInfo['RaceId']);
        $t1 = array();$t2 = array();$t3 = array();
        //循环赛段列表
        foreach($SegmentList['SegmentList'] as $SegmentId => $SegmentInfo)
        {
            //如果起始点存在
            if(isset($UserRaceTimingInfo["Point"][$SegmentInfo["StartId"]]))
            {
                for($i = $SegmentInfo["StartId"];$i<=$SegmentInfo["EndId"];$i++)
                {
                    foreach($UserRaceTimingInfo["Point"][$i]["UserList"] as $key => $UserInfo)
                    {
                        if($i == $SegmentInfo["StartId"])
                        {
                            $SegmentList['SegmentList'][$SegmentId]['Time'][$UserInfo["RaceGroupId"]]['UserList'][$UserInfo["RaceUserId"]] = array("Name"=>$UserInfo["Name"],"BIB"=>$UserInfo["BIB"],"TeamName"=>$UserInfo["TeamName"],"inTime"=>$UserInfo["inTime"],"SegmentTime"=>0,"CurrentPosition" => $i);
                            $t3[$UserInfo["RaceGroupId"]][$UserInfo["RaceUserId"]] = $UserInfo["inTime"];
                            $t1[$UserInfo["RaceGroupId"]][$UserInfo["RaceUserId"]] = $i;
                            $t2[$UserInfo["RaceGroupId"]][$UserInfo["RaceUserId"]] = 0;
                        }
                        else
                        {
                            if($SegmentInfo['comment']['NeedFinish'] == 1)
                            {
                                foreach($UserRaceTimingInfo["Total"] as $k => $U)
                                {
                                    if(in_array($U['RaceStatus'],array(1,2)))
                                    {
                                        unset($SegmentList['SegmentList'][$SegmentId]['Time'][$U["RaceGroupId"]]['UserList'][$U["RaceUserId"]]);
                                        unset($t1[$U["RaceGroupId"]][$U["RaceUserId"]]);
                                        unset($t2[$U["RaceGroupId"]][$U["RaceUserId"]]);
                                        unset($t3[$U["RaceGroupId"]][$U["RaceUserId"]]);
                                    }
                                }
                            }
                            if(isset($SegmentList['SegmentList'][$SegmentId]['Time'][$UserInfo["RaceGroupId"]]['UserList'][$UserInfo["RaceUserId"]]))
                            {
                                $SegmentTime = $UserInfo["inTime"]- $SegmentList['SegmentList'][$SegmentId]['Time'][$UserInfo["RaceGroupId"]]['UserList'][$UserInfo["RaceUserId"]]["inTime"];
                                $SegmentList['SegmentList'][$SegmentId]['Time'][$UserInfo["RaceGroupId"]]['UserList'][$UserInfo["RaceUserId"]]["CurrentPosition"] = $i;
                                $SegmentList['SegmentList'][$SegmentId]['Time'][$UserInfo["RaceGroupId"]]['UserList'][$UserInfo["RaceUserId"]]["SegmentTime"] = $SegmentTime;
                                $t1[$UserInfo["RaceGroupId"]][$UserInfo["RaceUserId"]]=$i;
                                $t2[$UserInfo["RaceGroupId"]][$UserInfo["RaceUserId"]]=$SegmentTime;
                            }
                        }
                    }
                }
            }
            foreach($SegmentList['SegmentList'][$SegmentId]['Time'] as $G => $GInfo)
            {
                array_multisort( $t1[$G], SORT_DESC, $t2[$G], SORT_ASC,$t3[$G], SORT_ASC,$SegmentList['SegmentList'][$SegmentId]['Time'][$G]['UserList']);
            }
        }
        //数据保存
        $oRace->SegmentListSave($RaceInfo['RaceId'],$SegmentList);


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
        $table_to_process = str_replace($this->table,$TableName.$this->table,$table_to_process);
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
            $rebuild_sql = "insert into ".$table_to_copy." (chip,chiptime,chiptype,pc,reader,Antenna,MilliSecs,Location,LapRaw,time) select chip,chiptime,chiptype,pc,reader,Antenna,MilliSecs,Location,LapRaw,time from ".$table_to_process." order by time";
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
    //检查计时记录的顺序是否正确
    public function checkTimingRecordSqeuence($TableName,$LastId,$LastTime,$rebuild=0)
    {
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        $table_to_process = str_replace($this->table,$TableName.$this->table,$table_to_process);
        //检查需要有可能需要重建的表名（排序后）
        $table_to_copy = $this->db->copyTable($this->table_sorted,$table_to_process."_sorted");
        //如果有更新到某条记录
        if($LastId>0)
        {
            //获取之后添加的记录的最小更新时间
            $CurrentLastRecord = $this->db->getTableRecoudCount($table_to_copy,$LastId);
        }
        if($rebuild == 0)
        {
            //新记录的最小时间小于已更新记录的最大时间（时间错位）
            if($CurrentLastRecord['LastTime']<$LastTime)
            {
                //返回错误
                return array('return'=>false,'restart'=>1);
            }
            else
            {
                //返回成功
                return array('return'=>true);
            }
        }
        else
        {
            //新记录的最小时间小于已更新记录的最大时间（时间错位）
            if($CurrentLastRecord['LastTime']<$LastTime)
            {
                echo "StartToRebuild<br>/n";
                //事务开始
                $this->db->begin();
                //清空数据
                $truncate = $this->db->query("truncate ".$table_to_copy);
                //排序/重建数据
                $rebuild_sql = "insert into ".$table_to_copy." (chip,chiptime,chiptype,pc,reader,Antenna,MilliSecs,Location,LapRaw,time) select chip,chiptime,chiptype,pc,reader,Antenna,MilliSecs,Location,LapRaw,time from ".$table_to_process." order by time";
                $rebuild = $this->db->query($rebuild_sql);
                //如果都成功
                if($truncate && $rebuild)
                {
                    //返回成功
                    $this->db->commit();
                    echo "RebuildFinished<br>/n";
                    return array('return'=>true,'rebuild'=>1,'restart'=>1);
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
    }
    public function popMylapsPassingMessage($Text)
    {
        //发送开始记号
        $StartFlag = 'jjj@Passing';
        $PassingHead = '@c=';
        //如果在头部找到
        if(is_numeric(stripos($Text,$StartFlag)))
        {
            //将开始符号截掉
            $Text = substr($Text,strlen($StartFlag)+1);
            //echo $Text;
            $TempText = $Text;
        }
        else
        {
            //将头部无用数据截掉
            $nextStart = stripos($Text,$PassingHead);
            $TempText = substr($Text,$nextStart+1);
        }
        //再次寻找下一个开始符
        $nextStart = stripos($TempText,$PassingHead);
        //如果找到
        if($nextStart>0)
        {
            $PassingMessage = substr($TempText,0,$nextStart);
            //$PassingMessage = substr($PassingMessage,0,stripos($PassingMessage,"@"));
            return array("Text" =>substr($TempText,$nextStart),"PassingMessage"=>$PassingMessage);
        }
        else
        {
            if(substr($Text,-1)=="$" || substr($Text,-1)=="@")
            {
                //$PassingMessage = substr($Text,0,$nextStart);
                //echo $TempText."<br>";
                //echo str
                //$t = explode("@",$TempText);
                //print_R($t);
                //die();
                $PassingMessage = substr($TempText,0,stripos($TempText,"@"));
                //$PassingMessage = substr($TempText,0,strlen($t[1])+1);

                return array("Text" =>"","PassingMessage"=>$PassingMessage);
            }
            else
            {
                return array("Text" =>$Text,"PassingMessage"=>"");
            }
        }
    }
}

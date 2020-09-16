<?php
/**
 * 动作相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Race_Team extends Base_Widget
{
	//声明所用到的表
	protected $table = 'user_team';

	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getTeamList($params,$fields = "*")
	{
        $whereRace = (isset($params['race_id']) && $params['race_id']>0)?" race_id = ".$params['race_id']:"";
        //初始化查询条件
		$whereCondition = array($whereRace);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY group_id,seed,team_id desc";
        $return = $this->db->getAll($sql);
		$TeamList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$TeamList[$value['team_id']] = $value;
			}
		}
		return $TeamList;
	}
	/**
	 * 获取单条记录
	 * @param integer $team_id
	 * @param string $fields
	 * @return array
	 */
	public function getTeam($team_id, $fields = '*')
	{
		$team_id = intval($team_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`team_id` = ?', $team_id);
	}
	/**
	 * 更新
	 * @param integer $team_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateTeam($team_id, array $bind)
	{
		$team_id = intval($team_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`team_id` = ?', $team_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertTeam(array $bind)
	{
        $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $team_id
	 * @return boolean
	 */
	public function deleteTeam($team_id)
	{
		$team_id = intval($team_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`team_id` = ?', $team_id);
	}
    //清除所有分组信息
	public function clearGroup($race_id)
    {
        $updated = 0;
        //获取队伍列表
        $teamList = $this->getTeamList(['race_id'=>$race_id]);
        if(count($teamList))
        {
            foreach($teamList as $team_id => $teamInfo)
            {
                //清除分组
                $update = $this->updateTeam($team_id,['group_id'=>0]);
                if($update)
                {
                    $updated++;
                }
            }
        }
        return $updated;
    }
    //清除所有分组信息
    public function reGroup($race_id,$detailType)
    {
        $updated = 0;
        //获取队伍列表
        $teamList = $this->getTeamList(['race_id'=>$race_id]);
        //初始化种子分组前和分组后列表
        $seedList = [];
        $groupSeedList = [];
        //将各队按照种子分配情况，放到分配前数组
        for($seed = 3;$seed>=0;$seed--)
        {
            foreach($teamList as $team_id => $teamInfo)
            {
                if($teamInfo['seed'] == $seed)
                {
                    $seedList[$seed][] = $teamInfo;
                    unset($teamList[$team_id]);
                }
            }
        }
        //循环各个种子批次
        foreach($seedList as $seed => $teamList)
        {
            if($seed ==0)
            {
                $teamList = $seedList[0];
            }
            //依次用分组
            for($i = 1;$i<=$detailType['group'];$i++)
            {
                //如果队伍列表变空
                if(count($teamList)==0)
                {
                    if($i == 1)
                    {
                        //跳出循环
                        break;
                    }
                    else
                    {
                        //如果数量不足就从非种子中借取
                        if(($seed != 0) && (count($seedList[0])>0))
                        {
                            $rand = rand(0,count($seedList[0])-1);
                            $groupSeedList[$seed][] = array_merge($seedList[0][$rand],['group_id'=>$i]);
                            unset($seedList[0][$rand]);
                            $seedList[0] = array_values($seedList[0]);
                        }
                        else
                        {
                            break;
                        }
                    }
                }
                else
                {
                    //循环随机获取一个队伍加入
                    $rand = rand(0,count($teamList)-1);
                    $groupSeedList[$seed][] = array_merge($teamList[$rand],['group_id'=>$i]);
                    //从原有组中删除这个队伍，并重排
                    unset($teamList[$rand]);
                    $teamList = array_values($teamList);
                }
                //如果所有组循环了一次
                if($i == $detailType['group'])
                {
                    //回头重来
                    $i = 0;
                }
            }
        }
        //循环各个种子批次
        foreach($groupSeedList as $seed => $teamList)
        {
            foreach($teamList as $key => $teamInfo)
            {
                $update =
                $this->updateTeam($teamInfo['team_id'],['group_id'=>$teamInfo['group_id']]);
                if($update)
                {
                    $updated++;
                }
            }
        }
        return $updated;
    }
    public function schedule_cup_32_8($race_id)
    {
        $inserted = 0;
        $oSchedual = new Hj_Race_Schedual();
        //获取队伍列表
        $teamList = $this->getTeamList(['race_id'=>$race_id]);
        $groupTeamList = [];
        $matchList = [];
        foreach($teamList as $team_id => $teamInfo)
        {
            $groupTeamList[$teamInfo['group_id']][count($groupTeamList[$teamInfo['group_id']]??[])+1] = $team_id;
        }
        $maxGroup = Base_Common::generateGroups(count($groupTeamList));
        //创建小组赛
        foreach($groupTeamList as $group_id => $teamList)
        {
            $matchList = Base_Common::generateGroupLeague($teamList);
            foreach($matchList as $round => $roundMatchList)
            {
                foreach($roundMatchList as $key => $matchInfo)
                {
                    $match = ['vs'=>['0'=>$matchInfo['home'],'1'=>$matchInfo['away']],
                        'group_id'=>$group_id,
                        'race_id'=>$race_id,
                        'round'=>$round,
                        'team'=>1,
                        'phase'=>1,
                        'match_name'=>"小组赛".$maxGroup[$group_id]."组第".$round."轮第".$key."场"];
                    $insert = $oSchedual->insertSchedual($match);
                    if($insert)
                    {
                        $inserted++;
                    }
                }
            }
        }
        //16进8
        $remainTeamCount = count($groupTeamList)*2;
        $knockoutMatchList = [];
        foreach($groupTeamList as $group_id => $teamList)
        {
            if($group_id%2==1)
            {
                $match = ['vs'=>['0'=>['from_group'=>$group_id,'from_group_rank'=>1],
                                '1'=>['from_group'=>$group_id+1,'from_group_rank'=>2]],
                    'phase'=>2,
                    'race_id'=>$race_id,
                    'team'=>1,
                    'match_name'=>$remainTeamCount."强淘汰赛:".$maxGroup[$group_id]."组第1 VS ".$maxGroup[$group_id+1]."组第2"];
                $insert = $oSchedual->insertSchedual($match);
                if($insert)
                {
                    $knockoutMatchList['1'][count($knockoutMatchList['1'])+1] = $insert;
                    $inserted++;
                }
                $match = ['vs'=>['0'=>['from_group'=>$group_id,'from_group_rank'=>2],
                                '1'=>['from_group'=>$group_id,'from_group_rank'=>1]],
                    'phase'=>2,
                    'race_id'=>$race_id,
                    'team'=>1,
                    'match_name'=>$remainTeamCount."强淘汰赛:".$maxGroup[$group_id+1]."组第1 VS ".$maxGroup[$group_id]."组第2"];
                $insert = $oSchedual->insertSchedual($match);
                if($insert)
                {
                    $knockoutMatchList['1'][count($knockoutMatchList['1'])+1] = $insert;
                    $inserted++;
                }
            }
        }
        //8进4
        $remainTeamCount = count($knockoutMatchList[1]);
        $i = 1;
        foreach($knockoutMatchList[1] as $key => $match_id)
        {
            if($key%2==1)
            {
                $match = ['vs'=>['0'=>['from_race'=>$match_id],
                                '1'=>['from_race'=>$knockoutMatchList[1][$key+1]]],
                    'phase'=>3,
                    'race_id'=>$race_id,
                    'team'=>1,
                    'match_name'=>$remainTeamCount."强淘汰赛:第".$i."场"];
                $i++;
                $insert = $oSchedual->insertSchedual($match);
                if($insert)
                {
                    $knockoutMatchList['2'][count($knockoutMatchList['2'])+1] = $insert;
                    $inserted++;
                }
            }
        }
        //4进2
        $remainTeamCount = count($knockoutMatchList[2]);
        $i = 1;
        foreach($knockoutMatchList[2] as $key => $match_id)
        {
            if($key%2==1)
            {
                $match = ['vs'=>['0'=>['from_race'=>$match_id],
                                '1'=>['from_race'=>$knockoutMatchList[2][$key+1]]],
                    'phase'=>4,
                    'race_id'=>$race_id,
                    'team'=>1,
                    'match_name'=>"1/2决赛第".$i."场"];
                $i++;
                $insert = $oSchedual->insertSchedual($match);
                if($insert)
                {
                    $knockoutMatchList['3'][count($knockoutMatchList['3'])+1] = $insert;
                    $inserted++;
                }
            }
        }
        //决赛
        $match = ['vs'=>['0'=>['from_race'=>$knockoutMatchList[3][1]],
                        '1'=>['from_race'=>$knockoutMatchList[3][2]]],
            'phase'=>5,
            'race_id'=>$race_id,
            'team'=>1,
            'match_name'=>"冠军亚决赛"];
        $insert = $oSchedual->insertSchedual($match);
        if($insert)
        {
            $knockoutMatchList['4'][count($knockoutMatchList['4'])+1] = $insert;
            $inserted++;
        }
        //3/4名决赛
        $match = ['vs'=>['0'=>['from_race'=>$knockoutMatchList[3][1],'winner'=>0],
                        '1'=>['from_race'=>$knockoutMatchList[3][2],'winner'=>0]],
            'phase'=>5,
            'race_id'=>$race_id,
            'team'=>1,
            'match_name'=>"3/4名决赛"];
        $insert = $oSchedual->insertSchedual($match);
        if($insert)
        {
            $knockoutMatchList['4'][count($knockoutMatchList['4'])+1] = $insert;
            $inserted++;
        }
        return $inserted;
    }
    public function schedule_cup_16_4($race_id)
    {
        $inserted = 0;
        $oSchedual = new Hj_Race_Schedual();
        //获取队伍列表
        $teamList = $this->getTeamList(['race_id'=>$race_id]);
        $groupTeamList = [];
        $matchList = [];
        foreach($teamList as $team_id => $teamInfo)
        {
            $groupTeamList[$teamInfo['group_id']][count($groupTeamList[$teamInfo['group_id']]??[])+1] = $team_id;
        }
        $maxGroup = Base_Common::generateGroups(count($groupTeamList));
        //创建小组赛
        foreach($groupTeamList as $group_id => $teamList)
        {
            $matchList = Base_Common::generateGroupLeague($teamList);
            foreach($matchList as $round => $roundMatchList)
            {
                foreach($roundMatchList as $key => $match)
                {
                    $match['group_id'] = $group_id;
                    $match['race_id'] = $race_id;
                    $match['round'] = $round;
                    $match['team'] = 1;
                    $match['phase'] = 1;
                    $match['match_name'] = "小组赛".$maxGroup[$group_id]."组第".$round."轮第".$key."场";
                    $insert = $oSchedual->insertSchedual($match);
                    if($insert)
                    {
                        $inserted++;
                    }
                }
            }
        }
        //8进4
        $remainTeamCount = count($groupTeamList)*2;
        $knockoutMatchList = [];
        foreach($groupTeamList as $group_id => $teamList)
        {
            if($group_id%2==1)
            {
                $match = ['race_id'=>$race_id,'team'=>1,'from_group_home'=>$group_id,'from_group_rank_home'=>1,'from_group_away'=>$group_id+1,'from_group_rank_away'=>2,'phase'=>2];
                $match['match_name'] = $remainTeamCount."强淘汰赛:".$maxGroup[$group_id]."组第1 VS ".$maxGroup[$group_id+1]."组第2";
                $insert = $oSchedual->insertSchedual($match);
                if($insert)
                {
                    $knockoutMatchList['1'][count($knockoutMatchList['1'])+1] = $insert;
                    $inserted++;
                }
                $match = ['race_id'=>$race_id,'team'=>1,'from_group_home'=>$group_id,'from_group_rank_home'=>2,'from_group_away'=>$group_id,'from_group_rank_away'=>1,'phase'=>2];
                $match['match_name'] = $remainTeamCount."强淘汰赛:".$maxGroup[$group_id+1]."组第1 VS ".$maxGroup[$group_id]."组第2";
                $insert = $oSchedual->insertSchedual($match);
                if($insert)
                {
                    $knockoutMatchList['1'][count($knockoutMatchList['1'])+1] = $insert;
                    $inserted++;
                }
            }
        }
        //4进2
        $remainTeamCount = count($knockoutMatchList[2]);
        $i = 1;
        foreach($knockoutMatchList[1] as $key => $match_id)
        {
            if($key%2==1)
            {
                $match = ['race_id'=>$race_id,'team'=>1,'from_race_home'=>$match_id,'from_race_away'=>$knockoutMatchList[1][$key+1],'phase'=>3];
                $match['match_name'] = "1/2决赛第".$i."场";
                $i++;
                $insert = $oSchedual->insertSchedual($match);
                if($insert)
                {
                    $knockoutMatchList['2'][count($knockoutMatchList['2'])+1] = $insert;
                    $inserted++;
                }
            }
        }
        //决赛
        $match = ['race_id'=>$race_id,'team'=>1,'from_race_home'=>$knockoutMatchList[2][1],'from_race_away'=>$knockoutMatchList[2][2],'phase'=>4];
        $match['match_name'] = "冠军决赛";
        $insert = $oSchedual->insertSchedual($match);
        if($insert)
        {
            $knockoutMatchList['3'][count($knockoutMatchList['3'])+1] = $insert;
            $inserted++;
        }
        //3/4名决赛
        $match = ['race_id'=>$race_id,'team'=>1,'from_race_home'=>$knockoutMatchList[2][1],'from_race_away'=>$knockoutMatchList[2][2],'from_race_winner'=>0,'phase'=>4];
        $match['match_name'] = "3/4名决赛";
        $insert = $oSchedual->insertSchedual($match);
        if($insert)
        {
            $knockoutMatchList['3'][count($knockoutMatchList['3'])+1] = $insert;
            $inserted++;
        }
        return $inserted;
    }

}

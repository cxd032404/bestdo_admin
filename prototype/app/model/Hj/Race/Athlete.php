<?php
/**
 * 动作相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Race_Athlete extends Base_Widget
{
	//声明所用到的表
	protected $table = 'user_athlete';

	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAthleteList($params,$fields = "*")
	{
        $whereRace = (isset($params['race_id']) && $params['race_id']>0)?" race_id = ".$params['race_id']:"";
        //初始化查询条件
		$whereCondition = array($whereRace);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY group_id,seed,athlete_id desc";
        $return = $this->db->getAll($sql);
		$AthleteList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AthleteList[$value['athlete_id']] = $value;
			}
		}
		return $AthleteList;
	}
	/**
	 * 获取单条记录
	 * @param integer $athlete_id
	 * @param string $fields
	 * @return array
	 */
	public function getAthlete($athlete_id, $fields = '*')
	{
		$athlete_id = intval($athlete_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`athlete_id` = ?', $athlete_id);
	}
	/**
	 * 更新
	 * @param integer $athlete_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateAthlete($athlete_id, array $bind)
	{
		$athlete_id = intval($athlete_id);
        $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`athlete_id` = ?', $athlete_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertAthlete(array $bind)
	{
        $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $athlete_id
	 * @return boolean
	 */
	public function deleteAthlete($athlete_id)
	{
		$athlete_id = intval($athlete_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`athlete_id` = ?', $athlete_id);
	}
    //清除所有分组信息
    public function clearGroup($race_id)
    {
        $updated = 0;
        //获取选手列表
        $athleteList = $this->getAthleteList(['race_id'=>$race_id]);
        if(count($athleteList))
        {
            foreach($athleteList as $athlete_id => $athlete_info)
            {
                //清除分组
                $update = $this->updateAthlete($athlete_id,['group_id'=>0]);
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
        //获取选手列表
        $athleteList = $this->getAthleteList(['race_id'=>$race_id]);
        //初始化种子分组前和分组后列表
        $seedList = [];
        $groupSeedList = [];
        //将各人按照种子分配情况，放到分配前数组
        for($seed = 3;$seed>=0;$seed--)
        {
            foreach($athleteList as $athlete_id => $athlete_info)
            {
                if($athlete_info['seed'] == $seed)
                {
                    $seedList[$seed][] = $athlete_info;
                    unset($athleteList[$athlete_id]);
                }
            }
        }
        //循环各个种子批次
        foreach($seedList as $seed => $athleteList)
        {
            if($seed ==0)
            {
                $athleteList = $seedList[0];
            }
            //依次用分组
            for($i = 1;$i<=$detailType['group'];$i++)
            {
                //如果选手列表变空
                if(count($athleteList)==0)
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
                    //循环随机获取一个选手加入
                    $rand = rand(0,count($athleteList)-1);
                    $groupSeedList[$seed][] = array_merge($athleteList[$rand],['group_id'=>$i]);
                    //从原有组中删除这个选手，并重排
                    unset($athleteList[$rand]);
                    $athleteList = array_values($athleteList);
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
        foreach($groupSeedList as $seed => $athleteList)
        {
            foreach($athleteList as $key => $athleteInfo)
            {
                $update =
                    $this->updateAthlete($athleteInfo['athlete_id'],['group_id'=>$athleteInfo['group_id']]);
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
        $athleteList = $this->getAthleteList(['race_id'=>$race_id]);
        $groupAthleteList = [];
        $matchList = [];
        foreach($athleteList as $athlete_id => $athleteInfo)
        {
            $groupAthleteList[$athleteInfo['group_id']][count($groupAthleteList[$athleteInfo['group_id']]??[])+1] = $athlete_id;
        }
        $maxGroup = Base_Common::generateGroups(count($groupAthleteList));
        //创建小组赛
        foreach($groupAthleteList as $group_id => $athleteList)
        {
            $matchList = Base_Common::generateGroupLeague($athleteList);
            foreach($matchList as $round => $roundMatchList)
            {
                foreach($roundMatchList as $key => $matchInfo)
                {
                    $match = ['vs'=>['0'=>['id'=>$matchInfo['home']],
                                    '1'=>['id'=>$matchInfo['away']]],
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
        $remainCount = count($groupAthleteList)*2;
        $knockoutMatchList = [];
        foreach($groupAthleteList as $group_id => $teamList)
        {
            if($group_id%2==1)
            {
                $match = ['vs'=>['0'=>['from_group'=>$group_id,'from_group_rank'=>1],
                                '1'=>['from_group'=>$group_id+1,'from_group_rank'=>2]],
                    'phase'=>2,
                    'race_id'=>$race_id,
                    'team'=>1,
                    'match_name'=>$remainCount."强淘汰赛:".$maxGroup[$group_id]."组第1 VS ".$maxGroup[$group_id+1]."组第2"];
                $insert = $oSchedual->insertSchedual($match);
                if($insert)
                {
                    $knockoutMatchList['1'][count($knockoutMatchList['1'])+1] = $insert;
                    $inserted++;
                }
                $match = ['vs'=>['0'=>['from_group'=>$group_id,'from_group_rank'=>2],
                    '1'=>['from_group'=>$group_id+1,'from_group_rank'=>1]],
                    'phase'=>2,
                    'race_id'=>$race_id,
                    'team'=>1,
                    'match_name'=>$remainCount."强淘汰赛:".$maxGroup[$group_id+1]."组第1 VS ".$maxGroup[$group_id]."组第2"];
                $insert = $oSchedual->insertSchedual($match);
                if($insert)
                {
                    $knockoutMatchList['1'][count($knockoutMatchList['1'])+1] = $insert;
                    $inserted++;
                }
            }
        }
        //8进4
        $remainCount = count($knockoutMatchList[1]);
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
                    'match_name'=>$remainCount."强淘汰赛:第".$i."场"];
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
        $remainCount = count($knockoutMatchList[2]);
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
        $athleteList = $this->getAthleteList(['race_id'=>$race_id]);
        $groupAthleteList = [];
        $matchList = [];
        foreach($athleteList as $athlete_id => $athleteInfo)
        {
            $groupAthleteList[$athleteInfo['group_id']][count($groupAthleteList[$athleteInfo['group_id']]??[])+1] = $athlete_id;
        }
        $maxGroup = Base_Common::generateGroups(count($groupAthleteList));
        //创建小组赛
        foreach($groupAthleteList as $group_id => $athleteList)
        {
            $matchList = Base_Common::generateGroupLeague($athleteList);
            foreach($matchList as $round => $roundMatchList)
            {
                foreach($roundMatchList as $key => $matchInfo)
                {
                    $match = ['vs'=>['0'=>['id'=>$matchInfo['home']],
                                    '1'=>['id'=>$matchInfo['away']]],
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
        //8进4
        $remainCount = count($groupAthleteList)*2;
        $knockoutMatchList = [];
        foreach($groupAthleteList as $group_id => $teamList)
        {
            if($group_id%2==1)
            {
                $match = ['vs'=>['0'=>['from_group'=>$group_id,'from_group_rank'=>1],
                                '1'=>['from_group'=>$group_id+1,'from_group_rank'=>2]],
                    'phase'=>2,
                    'race_id'=>$race_id,
                    'team'=>1,
                    'match_name'=>$remainCount."强淘汰赛:".$maxGroup[$group_id]."组第1 VS ".$maxGroup[$group_id+1]."组第2"];
                $insert = $oSchedual->insertSchedual($match);
                if($insert)
                {
                    $knockoutMatchList['1'][count($knockoutMatchList['1'])+1] = $insert;
                    $inserted++;
                }
                $match = ['vs'=>['0'=>['from_group'=>$group_id,'from_group_rank'=>2],
                    '1'=>['from_group'=>$group_id+1,'from_group_rank'=>1]],
                    'phase'=>2,
                    'race_id'=>$race_id,
                    'team'=>1,
                    'match_name'=>$remainCount."强淘汰赛:".$maxGroup[$group_id+1]."组第1 VS ".$maxGroup[$group_id]."组第2"];
                $insert = $oSchedual->insertSchedual($match);
                if($insert)
                {
                    $knockoutMatchList['1'][count($knockoutMatchList['1'])+1] = $insert;
                    $inserted++;
                }
            }
        }
        //4进2
        $remainCount = count($knockoutMatchList[2]);
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
                    'match_name' => "1/2决赛第".$i."场"];

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
        $match = ['vs'=>['0'=>['from_race'=>$knockoutMatchList[2][1]],
                        '1'=>['from_race'=>$knockoutMatchList[2][2]]],
            'phase'=>4,
            'race_id'=>$race_id,
            'team'=>1,
            'match_name' => "冠军决赛"];
        $insert = $oSchedual->insertSchedual($match);
        if($insert)
        {
            $knockoutMatchList['3'][count($knockoutMatchList['3'])+1] = $insert;
            $inserted++;
        }
        //3/4名决赛
        $match = ['vs'=>['0'=>['from_race'=>$knockoutMatchList[2][1],'winner'=>0],
            '1'=>['from_race'=>$knockoutMatchList[2][2],'winer'=>0]],
            'phase'=>4,
            'race_id'=>$race_id,
            'team'=>1,
            'match_name' => "3/4名决赛"];
        $insert = $oSchedual->insertSchedual($match);
        if($insert)
        {
            $knockoutMatchList['3'][count($knockoutMatchList['3'])+1] = $insert;
            $inserted++;
        }
        return $inserted;
    }
    
}

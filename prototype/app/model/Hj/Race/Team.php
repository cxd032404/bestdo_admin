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
        //获取队伍列表
        $teamList = $this->getTeamList(['race_id'=>$race_id]);
        $groupTeamList = [];
        $matchList = [];
        foreach($teamList as $team_id => $teamInfo)
        {
            $groupTeamList[$teamInfo['group_id']][count($groupTeamList[$teamInfo['group_id']]??[])+1] = $team_id;
        }
        foreach($groupTeamList as $group_id => $teamList)
        {
            $matchList = Base_Common::generateGroupLeague($teamList);
            //$matchList['1_1'] = ['home'=>1,'away'=>2];

        }
    }

}

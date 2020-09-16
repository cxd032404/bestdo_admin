<?php
/**
 * 动作相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Race_Schedual extends Base_Widget
{
	//声明所用到的表
	protected $table = 'user_race_schedual';

	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getSchedualList($params,$fields = "*")
	{
        $whereRace = (isset($params['race_id']) && $params['race_id']>0)?" race_id = ".$params['race_id']:"";
        $wherePhase = (isset($params['phase']) && $params['phase']>0)?" phase = ".$params['phase']:"";
        //初始化查询条件
		$whereCondition = array($whereRace,$wherePhase);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY phase,group_id,round,id asc";
		$return = $this->db->getAll($sql);
		$SchedualList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$SchedualList[$value['id']] = $value;
			}
		}
		return $SchedualList;
	}
	/**
	 * 获取单条记录
	 * @param integer $schedual_id
	 * @param string $fields
	 * @return array
	 */
	public function getSchedual($schedual_id, $fields = '*')
	{
		$schedual_id = intval($schedual_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`id` = ?', $schedual_id);
	}
	/**
	 * 更新
	 * @param integer $schedual_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateSchedual($schedual_id, array $bind)
	{
		$schedual_id = intval($schedual_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`id` = ?', $schedual_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertSchedual(array $bind)
	{
        if(isset($bind['vs']) && is_array($bind['vs']))
        {
            $bind['vs'] = json_encode($bind['vs']);
        }
	    $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $schedual_id
	 * @return boolean
	 */
	public function deleteSchedual($schedual_id)
	{
		$schedual_id = intval($schedual_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`id` = ?', $schedual_id);
	}
    //清除所有分组信息
	public function clearRace($race_id)
    {
        $deleted = 0;
        //获取队伍列表
        $SchedualList = $this->getSchedualList(['race_id'=>$race_id],'id');
        if(count($SchedualList))
        {
            foreach($SchedualList as $schedual_id => $SchedualInfo)
            {
                //清除分组
                $update = $this->deleteSchedual($schedual_id);
                if($update)
                {
                    $deleted++;
                }
            }
        }
        return $deleted;
    }
    public function schedule_cup_32_8($race_id)
    {
        //获取队伍列表
        $SchedualList = $this->getSchedualList(['race_id'=>$race_id]);
        $groupSchedualList = [];
        $matchList = [];
        foreach($SchedualList as $schedual_id => $SchedualInfo)
        {
            $groupSchedualList[$SchedualInfo['group_id']][count($groupSchedualList[$SchedualInfo['group_id']]??[])+1] = $schedual_id;
        }
        foreach($groupSchedualList as $group_id => $SchedualList)
        {
            $matchList = Base_Common::generateGroupLeague($SchedualList);
            print_R($matchList);
        }
        die();
    }

}

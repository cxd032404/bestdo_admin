<?php
/**
 * 场地相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_Arena extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_arena';
    protected $Weekday = array("0"=>"周日","1"=>"周一","2"=>"周二","3"=>"周三","4"=>"周四","5"=>"周五","6"=>"周六");

    public function getWeekdayList()
    {
        return $this->Weekday;
    }

    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAllArenaList($fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " ORDER BY ArenaId ASC";
		$return = $this->db->getAll($sql);
		$ArenaList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$ArenaList[$value['ArenaId']] = $value;
			}
		}
		return $ArenaList;
	}
	/**
	 * 获取单条记录
	 * @param integer $AppId
	 * @param string $fields
	 * @return array
	 */
	public function getArena($ArenaId, $fields = '*')
	{
		$ArenaId = intval($ArenaId);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`ArenaId` = ?', $ArenaId);
	}
	/**
	 * 更新场地
	 * @param integer $AppId
	 * @param array $bind
	 * @return boolean
	 */
	public function updateArena($ArenaId, array $bind)
	{
		$ArenaId = intval($ArenaId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`ArenaId` = ?', $ArenaId);
	}
	/**
	 * 新增场地
	 * @param array $bind
	 * @return boolean
	 */
	public function insertArena(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除场地
	 * @param integer $AppId
	 * @return boolean
	 */
	public function deleteArena($ArenaId)
	{
		$ArenaId = intval($ArenaId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`ArenaId` = ?', $ArenaId);
	}
    /**
     * 根据预设的每日列表，生成指定天数内可选的日期列表
     * @param array $Weekday 每日列表
     * @param integer $dayCount 天数
     * @return boolean
     */
    public function genWeekdayList($Weekday,$dayCount = 10)
    {
        $DateList = array();
        //获取当前时间
        $Time = time();
        //天数在0和指定天数之内循环
        for($i=0;$i<$dayCount;$i++)
        {
            //如果与每日规律符合
            if(in_array(date("w",$Time+$i*86400),$Weekday))
            {
                //保存日期
                $DateList[] = date("Y-m-d",$Time+$i*86400);
            }
        }
        return $DateList;
    }

}

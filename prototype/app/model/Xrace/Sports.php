<?php
/**
 * 运动相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_Sports extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_sports_type';
    //速度显示规则列表
    protected $speedDisplayList = array('km/h'/*每小时公里数*/,'mile/h'/*每小时英里数*/,'time/100m'/*每100米耗时*/,'time/km'/*每公里耗时*/,'time/mile'/*每公里耗时*/);

    public function getSpeedDisplayList()
    {
        return $this->speedDisplayList;
    }
    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAllSportsTypeList($fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " ORDER BY SportsTypeId ASC";
		$return = $this->db->getAll($sql);
		$SportsTypeList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$SportsTypeList[$value['SportsTypeId']] = $value;
			}
		}
		return $SportsTypeList;
	}
	/**
	 * 获取单条记录
	 * @param integer $AppId
	 * @param string $fields
	 * @return array
	 */
	public function getSportsType($SportsTypeId, $fields = '*')
	{
		$SportsTypeId = intval($SportsTypeId);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`SportsTypeId` = ?', $SportsTypeId);
	}
	/**
	 * 更新
	 * @param integer $AppId
	 * @param array $bind
	 * @return boolean
	 */
	public function updateSportsType($SportsTypeId, array $bind)
	{
		$SportsTypeId = intval($SportsTypeId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`SportsTypeId` = ?', $SportsTypeId);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertSportsType(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $AppId
	 * @return boolean
	 */
	public function deleteSportsType($SportsTypeId)
	{
		$SportsTypeId = intval($SportsTypeId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`SportsTypeId` = ?', $SportsTypeId);
	}

}

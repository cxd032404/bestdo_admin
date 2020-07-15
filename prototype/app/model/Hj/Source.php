<?php
/**
 * 活动相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Source extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_source';
	
	/**
	 * 获取单条记录
	 * @param integer $source_id
	 * @param string $fields
	 * @return array
	 */
	public function getSource($source_id, $fields = '*')
	{
		$source_id = intval($source_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`source_id` = ?', $source_id);
	}
	/**
	 * 更新
	 * @param integer $source_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateSource($source_id, array $bind)
	{
		$source_id = intval($source_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`source_id` = ?', $source_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertSource(array $bind)
	{
		$bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $source_id
	 * @return boolean
	 */
	public function deleteSource($source_id)
	{
		$source_id = intval($source_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`source_id` = ?', $source_id);
	}

}

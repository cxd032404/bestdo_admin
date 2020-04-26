<?php
/**
 * 页面元素类型配置相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_ElementType extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_element';

    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getElementTypeList($params = [],$fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
        $whereCondition = array();
        $where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY update_time DESC";
		$return = $this->db->getAll($sql);
		$ElementTypeList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$ElementTypeList[$value['element_type']] = $value;
			}
		}
		return $ElementTypeList;
	}
	/**
	 * 获取单条记录
	 * @param string $element_type
	 * @param string $fields
	 * @return array
	 */
	public function getElementType($element_type, $fields = '*')
	{
        $element_type = trim($element_type);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`element_type` = ?', $element_type);
	}
	/**
	 * 更新
	 * @param integer $element_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updatePageElement($element_id, array $bind)
	{
		$element_id = intval($element_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`element_id` = ?', $element_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertPageElement(array $bind)
	{
		$bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $element_id
	 * @return boolean
	 */
	public function deletePage($page_id)
	{
		$element_id = intval($element_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`element_id` = ?', $element_id);
	}

}

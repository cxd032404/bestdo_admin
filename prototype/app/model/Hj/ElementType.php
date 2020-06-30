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
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY element_type_name DESC";
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
}

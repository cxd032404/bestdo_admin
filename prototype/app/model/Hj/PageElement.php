<?php
/**
 * 页面元素相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_PageElement extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_page_element';

    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getElementList($params = [],$fields = "*")
	{
	    $table_to_process = Base_Widget::getDbTable($this->table);
	    $wherePage = (isset($params['page_id']) && $params['page_id']>0)?" page_id = ".$params['page_id']:"";
        $whereSign = (isset($params['element_sign']) && trim($params['element_sign'])!="")?" element_sign = '".$params['element_sign']."'":"";
        $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" element_id != ".$params['exclude_id']:"";
        $whereCondition = array($wherePage,$whereSign,$whereExclude);
        $where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY element_id ASC";
		$return = $this->db->getAll($sql);
		$ElementList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$ElementList[$value['element_id']] = $value;
			}
		}
		return $ElementList;
	}
    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getElementCount($params = [])
	{
		$fields = ["elementCount"=>'count(1)'];
		        //生成查询列
        $fields = Base_common::getSqlFields($fields);
		$table_to_process = Base_Widget::getDbTable($this->table);
	    $wherePage = (isset($params['page_id']) && $params['page_id']>0)?" page_id = ".$params['page_id']:"";
        $whereCondition = array($wherePage);
        $where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where;
		$return = $this->db->getOne($sql);
		return $return;
	}
	/**
	 * 获取单条记录
	 * @param integer $element_id
	 * @param string $fields
	 * @return array
	 */
	public function getPageElement($element_id, $fields = '*')
	{
		$element_id = intval($element_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`element_id` = ?', $element_id);
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
	public function deletePageElement($element_id)
	{
		$element_id = intval($element_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`element_id` = ?', $element_id);
	}

}

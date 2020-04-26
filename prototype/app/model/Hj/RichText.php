<?php
/**
 * 动作相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_RichText extends Base_Widget
{
	//声明所用到的表
	protected $table = 'rich_text_text';

	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function get($id = 1,$fields = "*")
	{
		$whereId  = "id = $id";
		//初始化查询条件
		$whereCondition = array($whereId);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY id ASC";
        $return = $this->db->getAll($sql);
        $returnData = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
                $returnData[$value['id']] = $value;
			}
		}
		return $returnData;
	}
	public function save($id = 1,$text)
    {
        $id = intval($id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, ['text'=>$text], '`id` = ?', $id);
    }

}

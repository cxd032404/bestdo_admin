<?php
/**
 * 提问相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Question extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_question';

    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getQuestionList($params = [],$fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
	    $whereActivity = (isset($params['acitivity_id']) && $params['acitivity_id']>0)?" acitivity_id = ".$params['acitivity_id']:"";
        $whereActivitys = (isset($params['acitivity_ids']) && is_array($params['acitivity_ids']))?" acitivity_id in (".implode(",",$params['acitivity_id']).")":"";
        $whereCondition = array($whereActivity,$whereActivitys);
        $where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY question_id ASC";
		$return = $this->db->getAll($sql);
		$questionList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
                $questionList[$value['question_id']] = $value;
			}
		}
		return $questionList;
	}
	/**
	 * 获取单条记录
	 * @param integer $question_id
	 * @param string $fields
	 * @return array
	 */
	public function getQuestion($question_id, $fields = '*')
	{
		$question_id = intval($question_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`question_id` = ?', $question_id);
	}
	/**
	 * 更新
	 * @param integer $question_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateQuestion($question_id, array $bind)
	{
		$question_id = intval($question_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`question_id` = ?', $question_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertQuestion(array $bind)
	{
		$bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $question_id
	 * @return boolean
	 */
	public function deleteQuestion($question_id)
	{
		$question_id = intval($question_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`question_id` = ?', $question_id);
	}

}

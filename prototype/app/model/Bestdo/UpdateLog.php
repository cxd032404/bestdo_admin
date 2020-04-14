<?php
/**
 * 系统更新记录相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_UpdateLog extends Base_Widget
{
	//声明所用到的表
	protected $table = 'UpdateLog';

    //性别列表
    protected $LogTypeList = array('update'=>"更新",'new'=>"新增");

    //获取性别列表
    public function getLogTypeList()
    {
        return $this->LogTypeList;
    }

	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getUpdateLogList($Page=0,$PageSize=0,$fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
        $limit  = ($Page>0)&&($PageSize>0)?" limit ".($Page-1)*$PageSize.",".$PageSize." ":"";
        $sql = "SELECT $fields FROM " . $table_to_process . " ORDER BY UpdateDate desc,UpdateLogId asc".$limit;
        $return = $this->db->getAll($sql);
		$UpdateLogList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$UpdateLogList[$value['UpdateDate']][$value['LogType']][$value['UpdateLogId']] = $value;
			}
		}
		return $UpdateLogList;
	}
	/**
	 * 获取单条记录
	 * @param integer $UpdateLogId
	 * @param string $fields
	 * @return array
	 */
	public function getUpdateLog($UpdateLogId, $fields = '*')
	{
		$UpdateLogId = intval($UpdateLogId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`UpdateLogId` = ?', $UpdateLogId);
	}
	/**
	 * 更新单条记录
	 * @param integer $UpdateLogId
	 * @param array $bind
	 * @return boolean
	 */
	public function updateUpdateLog($UpdateLogId, array $bind)
	{
		$UpdateLogId = intval($UpdateLogId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`UpdateLogId` = ?', $UpdateLogId);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertUpdateLog(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}
	/**
	 * 删除
	 * @param integer $UpdateLogId
	 * @return boolean
	 */
	public function deleteUpdateLog($UpdateLogId)
	{
		$UpdateLogId = intval($UpdateLogId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`UpdateLogId` = ?', $UpdateLogId);
	}
}

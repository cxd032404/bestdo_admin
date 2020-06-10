<?php
/**
 * 登录日志
 * @author <cxd032404@hotmail.com>
 * $Id: LogsManager.php 15195 2014-07-23 07:18:26Z 334746 $
 */
class Log_LogsManager extends Base_Widget
{
	protected $table = 'config_logs_manager';
	
	protected $logArr = array();

	/**
	 * 初始化表名
	 * @return string
	 */
	public function init()
	{
		parent::init();
		$this->table = $this->getDbTable($this->table);
	}
	
	public function insert()
	{
//		return true;
		return $this->db->insert($this->table, $this->logArr);
	}
	
	public function pushLog($key, $value)
	{
		$this->logArr[$key] = $value;
	}

	/**
	 * 查询类型游戏
	 * @param $app_class
	 * @param $fields
	 * @return array
	 */
	public function getNameAll($name, $fields = "*")
	{
		$sql = "SELECT $fields FROM {$this->table} WHERE `name` = ? ORDER BY addtime DESC";
		return $this->db->getAll($sql, $name);
	}
	
	/**
	 * 单条数据
	 * @param string $name
	 * @return int
	 */
	public function getCount($name, $fields = "count(*)")
	{
		$sql = "SELECT $fields FROM {$this->table}";
		if(!empty($name))
		{
			$sql .= " WHERE `name` in ($name)";
		}
		return $this->db->getOne($sql);
	}
	
	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAll($name, $number = 20, $offset = 0, $fields = "*")
	{
		$where = "";
		if(!empty($name))
		{
			$where = " WHERE `name` in ($name)";
		}
		$sql = "SELECT $fields FROM {$this->table} $where  ORDER BY id DESC";
		return $this->db->limitQuery($sql, array(), $offset, $number);
	}	
}
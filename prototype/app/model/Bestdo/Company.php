<?php
/**
 * 企业相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Bestdo_Company extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_company';

    public function getSpeedDisplayList()
    {
        return $this->speedDisplayList;
    }
    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAllCompanyList($fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " ORDER BY company_id ASC";
		$return = $this->db->getAll($sql);
		$CompanyList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$CompanyList[$value['company_id']] = $value;
			}
		}
		return $CompanyList;
	}
	/**
	 * 获取单条记录
	 * @param integer $CompanyId
	 * @param string $fields
	 * @return array
	 */
	public function getCompany($CompanyId, $fields = '*')
	{
		$CompanyId = intval($CompanyId);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`company_id` = ?', $CompanyId);
	}
	/**
	 * 更新
	 * @param integer $CompanyId
	 * @param array $bind
	 * @return boolean
	 */
	public function updateCompany($CompanyId, array $bind)
	{
		$CompanyId = intval($CompanyId);
		$bind['update_time'] = time();
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`company_id` = ?', $CompanyId);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertCompany(array $bind)
	{
		$bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $CompanyId
	 * @return boolean
	 */
	public function deleteCompany($CompanyId)
	{
		$CompanyId = intval($CompanyId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`company_id` = ?', $CompanyId);
	}

}

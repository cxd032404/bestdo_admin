<?php
/**
 * 企业协议相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */

class Hj_Protocal extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_protocal';
	protected $protocal_type = ["privacy"=>"隐私政策","user"=>"用户协议"];

	public function getPrototcalType()
    {
        return $this->protocal_type;
    }

    /**
     * 获取单条记录
     * @param integer $protocal_id
     * @param string $fields
     * @return array
     */
    public function getProtocalByType($company_id,$type="privacy", $fields = '*')
    {
        $company_id = intval($company_id);
        $type = trim($type);

        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`company_id` = ? and `type` = ?', [$company_id,$type]);
    }

	/**
	 * 获取单条记录
	 * @param integer $protocal_id
	 * @param string $fields
	 * @return array
	 */
	public function getProtocal($protocal_id, $fields = '*')
	{
		$protocal_id = intval($protocal_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`protocal_id` = ?', $protocal_id);
	}
	/**
	 * 更新
	 * @param integer $protocal_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateProtocal($protocal_id, array $bind)
	{
		$protocal_id = intval($protocal_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`protocal_id` = ?', $protocal_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertProtocal(array $bind)
	{
		$bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $protocal_id
	 * @return boolean
	 */
	public function deleteProtocal($protocal_id)
	{
		$protocal_id = intval($protocal_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`protocal_id` = ?', $protocal_id);
	}

}

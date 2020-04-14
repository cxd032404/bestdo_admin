<?php
/**
 * 用户数据相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_Chip extends Base_Widget
{
	//声明所用到的表
    protected $table = 'ChipList';

    //登录方式列表
    protected $ChipTypeList = array('1'=>"Mylaps芯片");

        //获取性别列表
	public function getChipTypeList()
	{
		return $this->ChipTypeList;
	}

    /**
     * 新增单个芯片记录
     * @param array $bind 所要添加的数据列
     * @return boolean
     */
    public function insertChip($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 获取单个芯片的信息
     * @param string $ChipId 芯片ID
     * @return array
     */
    public function getChipInfo($ChipId)
    {
        $ChipId = trim($ChipId);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, "*", '`ChipId` = ?', $ChipId);
    }
    /**
     * 更新单个芯片记录
     * @param string $ChipId 芯片ID
     * @param array $bind 所要更新的数据列
     * @return boolean
     */
    public function updateChipInfo($ChipId,$bind)
    {
        $ChipId = trim($ChipId);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->update($table_to_process, $bind, '`ChipId` = ?', $ChipId);
    }
    /**
     * 删除单个芯片
     * @param string $ChipId 芯片ID
     * @return boolean
     */
    public function deleteChip($ChipId)
    {
        $ChipId = trim($ChipId);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->delete($table_to_process, '`ChipId` = ?', $ChipId);
    }
    //获取芯片列表
    public function getChipList($params,$fields = array('*'))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        //获得用户ID
        $whereUser = (isset($params['UserId']) && $params['UserId']!="0")?" UserId = '".$params['UserId']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereUser);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by ChipId desc";
        $return = $this->db->getAll($sql);
        foreach($return as $key => $ChipInfo)
        {
            $ReturnArr[$ChipInfo["ChipId"]] = $ChipInfo;
        }
        return $ReturnArr;
    }
}

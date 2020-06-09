<?php
/**
 * 俱乐部相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Club extends Base_Widget
{
    //声明所用到的表
    protected $table = 'config_club';

    /**
     * 查询全部
     * @param $fields
     * @return array
     */
    public function getClubList($params = [],$fields = "*")
    {
        $table_to_process = Base_Widget::getDbTable($this->table);
        $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereSign = (isset($params['club_sign']) && trim($params['club_sign'])!="")?" club_sign = '".$params['club_sign']."'":"";
        $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" club_id != ".$params['exclude_id']:"";
        $whereCondition = array($whereCompany,$whereSign,$whereExclude);
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY club_id ASC";
        $return = $this->db->getAll($sql);
        $ClubList = array();
        if(count($return))
        {
            foreach($return as $key => $value)
            {
                $ClubList[$value['club_id']] = $value;
            }
        }
        return $ClubList;
    }
    /**
     * 获取单条记录
     * @param integer $club_id
     * @param string $fields
     * @return array
     */
    public function getClub($club_id, $fields = '*')
    {
        $club_id = intval($club_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`club_id` = ?', $club_id);
    }
    /**
     * 更新
     * @param integer $club_id
     * @param array $bind
     * @return boolean
     */
    public function updateClub($club_id, array $bind)
    {
        $club_id = intval($club_id);
        $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->update($table_to_process, $bind, '`club_id` = ?', $club_id);
    }
    /**
     * 插入
     * @param array $bind
     * @return boolean
     */
    public function insertClub(array $bind)
    {
        $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->insert($table_to_process, $bind);
    }

    /**
     * 删除
     * @param integer $club_id
     * @return boolean
     */
    public function deleteClub($club_id)
    {
        $club_id = intval($club_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->delete($table_to_process, '`club_id` = ?', $club_id);
    }

}

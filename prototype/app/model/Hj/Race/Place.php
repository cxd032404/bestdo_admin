<?php
/**
 * 场地相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Race_Place extends Base_Widget
{
    //声明所用到的表
    protected $table = 'config_place';

    /**
     * 查询全部
     * @param $fields
     * @return array
     */
    public function getPlaceList($params,$fields = "*")
    {
        $whereRace = (isset($params['race_id']) && $params['race_id']>0)?" race_id = ".$params['race_id']:"";
        //初始化查询条件
        $whereCondition = array($whereRace);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $table_to_process = Base_Widget::getDbTable($this->table);
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY place_id desc";
        $return = $this->db->getAll($sql);
        $PlaceList = array();
        if(count($return))
        {
            foreach($return as $key => $value)
            {
                $PlaceList[$value['place_id']] = $value;
            }
        }
        return $PlaceList;
    }
    /**
     * 获取单条记录
     * @param integer $place_id
     * @param string $fields
     * @return array
     */
    public function getPlace($place_id, $fields = '*')
    {
        $place_id = intval($place_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`place_id` = ?', $place_id);
    }
    /**
     * 更新
     * @param integer $place_id
     * @param array $bind
     * @return boolean
     */
    public function updatePlace($place_id, array $bind)
    {
        $place_id = intval($place_id);
        $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->update($table_to_process, $bind, '`place_id` = ?', $place_id);
    }
    /**
     * 插入
     * @param array $bind
     * @return boolean
     */
    public function insertPlace(array $bind)
    {
        $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->insert($table_to_process, $bind);
    }

    /**
     * 删除
     * @param integer $place_id
     * @return boolean
     */
    public function deletePlace($place_id)
    {
        $place_id = intval($place_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->delete($table_to_process, '`place_id` = ?', $place_id);
    }
}

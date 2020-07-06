<?php
/**
 * 配置相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Widget_Config extends Base_Widget
{
    //声明所用到的表
    protected $table = 'config_default';
    protected $config_type =
        ["source"=>"资源",
            "char"=>"字符",
            "int"=>"整形"
        ];
    //获取完成后操作的类型
    public function getConfigType()
    {
        return $this->config_type;
    }
    /**
     * 查询全部
     * @param $fields
     * @return array
     */
    public function getConfigList($params = [],$fields = "*")
    {
        $table_to_process = Base_Widget::getDbTable($this->table);
        $whereType = (isset($params['config_type']) && trim($params['config_type'])!="")?" config_type = '".$params['config_type']."'":"";
        $whereName = (isset($params['config_name']) && trim($params['config_name'])!="")?" config_name = '".$params['config_name']."'":"";
        $whereSign = (isset($params['config_sign']) && trim($params['config_sign'])!="")?" config_sign = '".$params['config_sign']."'":"";
        $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" config_sign != '".$params['exclude_id']."'":"";
        $whereCondition = array($whereType,$whereName,$whereExclude,$whereSign);
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY update_time ASC";
        $return = $this->db->getAll($sql);
        $ConfigList = array();
        if(count($return))
        {
            foreach($return as $key => $value)
            {
                $ConfigList[$value['config_sign']] = $value;
            }
        }
        return $ConfigList;
    }
    /**
     * 获取单条记录
     * @param integer $config_sign
     * @param string $fields
     * @return array
     */
    public function getConfig($config_sign, $fields = '*')
    {
        $config_sign = trim($config_sign);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`config_sign` = ?', $config_sign);
    }
    /**
     * 更新
     * @param integer $config_sign
     * @param array $bind
     * @return boolean
     */
    public function updateConfig($config_sign, array $bind)
    {
        $config_sign = trim($config_sign);
        $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->update($table_to_process, $bind, '`config_sign` = ?', $config_sign);
    }
    /**
     * 插入
     * @param array $bind
     * @return boolean
     */
    public function insertConfig(array $bind)
    {
        $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->insert($table_to_process, $bind);
    }

    /**
     * 删除
     * @param integer $config_sign
     * @return boolean
     */
    public function deleteConfig($config_sign)
    {
        $config_sign = trim($config_sign);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->delete($table_to_process, '`config_sign` = ?', $config_sign);
    }

}

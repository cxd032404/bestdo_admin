<?php
/**
 * 页面相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Page extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_page';

    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getPageList($params = [],$fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
	    $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereSign = (isset($params['page_sign']) && trim($params['page_sign'])!="")?" page_sign = '".$params['page_sign']."'":"";
        $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" page_id != ".$params['exclude_id']:"";
        $whereCondition = array($whereCompany,$whereSign,$whereExclude);
        $where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY update_time DESC";
		$return = $this->db->getAll($sql);
		$PageList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$PageList[$value['page_id']] = $value;
			}
		}
		return $PageList;
	}
	/**
	 * 获取单条记录
	 * @param integer $page_id
	 * @param string $fields
	 * @return array
	 */
	public function getPage($page_id, $fields = '*')
	{
		$page_id = intval($page_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`page_id` = ?', $page_id);
	}
	/**
	 * 更新
	 * @param integer $page_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updatePage($page_id, array $bind)
	{
		$page_id = intval($page_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`page_id` = ?', $page_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertPage(array $bind)
	{
		$bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $page_id
	 * @return boolean
	 */
	public function deletePage($page_id)
	{
		$page_id = intval($page_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`page_id` = ?', $page_id);
	}
    /**
     * 解包页面参数数组
     * @param string $string
     * @return array
     */
	public function unpackPageParams($string = "")
    {
        $return = [];
        $string = trim($string);
        if ($string != "")
        {
            $return = [];
            $t1 = explode("|", $string);
            foreach ($t1 as $k => $v)
            {
                $t2 = explode(":", trim($v));
                if (isset($t2[0]) && $t2[0] != "")
                {
                    $return[] = ['name' => trim($t2[0]), 'type' => trim($t2[1] ?? "int"),'default' => trim($t2[2] ?? 1)];
                }
            }

        }
        return $return;
    }
    /**
     * 打包页面参数数组
     * @param array $array
     * @return string
     */
    public function packPageParams($array = [])
    {
        $return = "";
        $t = [];
        if(count($array)>=1)
        {
            foreach($array as $k => $v)
            {
                $t[] = implode(":",$v);
            }
        }
        $return = implode("|",$t);
        return $return;
    }
    /**
     * 打包页面参数数组
     * @param array $array
     * @return string
     */
    public function processDefaultParams($array = [])
    {
        $return = "";
        $t = [];
        if(count($array)>=1)
        {
            foreach($array as $k => $v)
            {
                $t[$v['name']] = ($v['type']=="int")?intval($v['default']??1):$v['default']??1;
            }
        }
        $return = (count($t)>0)?"params=".urlencode(json_encode($t)):"";
        return $return;
    }

}

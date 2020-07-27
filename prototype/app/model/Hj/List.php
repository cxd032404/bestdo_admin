<?php
/**
 * 活动相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_List extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_list';
    protected $list_type =
    ["text"=>['name'=>"文本",'textarea'=>1],
        "pic"=>['name'=>"图片",'upload'=>['pic'=>10],'textarea'=>1],
        "video"=>['name'=>"视频",'upload'=>['video'=>3],'textarea'=>1],
        "rankingFromKudo"=>['name'=>"点赞排名"],
        "rankingFromUpload"=>['name'=>"用户上传排名",'upload'=>['txt'=>1]],
    ];
    protected $after_action =
        [   "0"=>['name'=>"无动作"],
            "self"=>['name'=>"本人提交列表"],
            "this"=>['name'=>"当前详情"],
            "all"=>['name'=>"当前列表"],
        ];
    protected $specified_list =
        [   "honor"=>"荣誉堂",
            "boutique"=>"精品课",
        ];
    //获取列表类型
    public function getListType()
    {
        return $this->list_type;
    }
    //获取列表类型
    public function getSpecifiedListType()
    {
        return $this->specified_list;
    }
    //获取完成后操作的类型
    public function getAfterAction()
    {
        return $this->after_action;
    }
    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getListList($params = [],$fields = "*")
	{
	    $table_to_process = Base_Widget::getDbTable($this->table);
        $wherePermission = isset($params['permissionList'])?(
        count($params['permissionList'])>0?
            ( " company_id in (".implode(",",array_column($params['permissionList'],"company_id")).") ")
            :" 0 "):"";
	    $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereAcitvity = (isset($params['activity_id']) && $params['activity_id']!="0")?" activity_id = '".$params['activity_id']."'":"";
        $whereType = (isset($params['list_type']) && $params['list_type']!="0")?" list_type = '".$params['list_type']."'":"";
        $whereName = (isset($params['list_name']) && trim($params['list_name'])!="")?" list_name = '".trim($params['list_name'])."'":"";
        $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" list_id != ".$params['exclude_id']:"";
        $whereIn = (isset($params['id_in']) && count($params['id_in'])>0)?" list_id in ( ".implode(",",$params['id_in']).")":"";
        $whereNotIn = (isset($params['id_not_in']) && count($params['id_not_in'])>0)?" list_id not in ( ".implode(",",$params['id_not_in']).")":"";
        $whereCondition = array($wherePermission,$whereCompany,$whereAcitvity,$whereExclude,$whereName,$whereType,$whereIn,$whereNotIn);
        $where = Base_common::getSqlWhere($whereCondition);
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $listCount = $this->getListCount($params);
        }
        else
        {
            $listCount = 0;
        }
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY list_id ASC ".$limit;
        $return = $this->db->getAll($sql);
		$List = ['ListCount'=>$listCount,'ListList'=>[]];
		if(count($return))
		{
			foreach($return as $key => $value)
			{
                $List['ListList'][$value['list_id']] = $value;
			}
		}
		return $List;
	}
    /**
     * 获取记录数量
     * @param $where  查询条件
     * @return integer
     */
        public function getListCount($params)
        {
            //获取需要用到的表名
            $table_to_process = Base_Widget::getDbTable($this->table);
            //生成查询列
            $fields = Base_common::getSqlFields(array("ListCount"=>"count(list_id)"));
            $wherePermission = isset($params['permissionList'])?(
            count($params['permissionList'])>0?
                ( " company_id in (".implode(",",array_column($params['permissionList'],"company_id")).") ")
                :" 0 "):"";
            $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
            $whereAcitvity = (isset($params['activity_id']) && $params['activity_id']!="0")?" activity_id = '".$params['activity_id']."'":"";
            $whereType = (isset($params['list_type']) && $params['list_type']!="0")?" list_type = '".$params['list_type']."'":"";
            $whereName = (isset($params['list_name']) && trim($params['list_name'])!="")?" list_name = '".trim($params['list_name'])."'":"";
            $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" list_id != ".$params['exclude_id']:"";
            $whereIn = (isset($params['id_in']) && count($params['id_in'])>0)?" list_id in ( ".implode(",",$params['id_in']).")":"";
            $whereNotIn = (isset($params['id_not_in']) && count($params['id_not_in'])>0)?" list_id not in ( ".implode(",",$params['id_not_in']).")":"";
            $whereCondition = array($wherePermission,$whereCompany,$whereAcitvity,$whereExclude,$whereName,$whereType,$whereIn,$whereNotIn);
            $where = Base_common::getSqlWhere($whereCondition);
            $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
            return $this->db->getOne($sql);
        }
	/**
	 * 获取单条记录
	 * @param integer $list_id
	 * @param string $fields
	 * @return array
	 */
	public function getList($list_id, $fields = '*')
	{
		$list_id = intval($list_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`list_id` = ?', $list_id);
	}
	/**
	 * 更新
	 * @param integer $list_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateList($list_id, array $bind)
	{
        $list_id = intval($list_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`list_id` = ?', $list_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertList(array $bind)
	{
		$bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $list_id
	 * @return boolean
	 */
	public function deleteList($list_id)
	{
		$list_id = intval($list_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`list_id` = ?', $list_id);
	}

	/*
	 * 获取单条list(查询参数activity_id)
	 */
    public function getlistsWithActivityId($activity_id,$fields = 'list_id,list_name'){
        $where = ' activity_id = '.$activity_id;
        $table_to_process = Base_Widget::getDbTable($this->table);
        $sql ="select $fields from $table_to_process where $where";
        return $this->db->getAll($sql);
    }

}

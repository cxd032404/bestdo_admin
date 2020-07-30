<?php
/**
 * 提交文章相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Posts extends Base_Widget
{
	//声明所用到的表
	protected $table = 'user_posts';

    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getPostsList($params = [],$fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
	    $whereList = (isset($params['list_id']) && $params['list_id']>0)?" list_id = ".$params['list_id']:"";
        $whereCondition = array($whereList);
        $where = Base_common::getSqlWhere($whereCondition);
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY post_id DESC ".$limit;
		$return = $this->db->getAll($sql);
		$List = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
                $List[$value['post_id']] = $value;
			}
		}
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $postsCount = $this->getPostCount($where);
        }
        else
        {
            $postsCount = 0;
        }
		return ['postsList'=>$List,'postsCount'=>$postsCount];
	}
    /**
     * 获取记录数量
     * @param $where  查询条件
     * @return integer
     */
    public function getPostCount($where)
    {
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        //生成查询列
        $fields = Base_common::getSqlFields(array("postsCount"=>"count(post_id)"));
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    /**
     * 获取单个列表下的记录数量
     * @param $list_id  列表ID
     * @return integer
     */
    public function getPostCountByList($list_id)
    {
        $list_id = intval($list_id);
        $whereList = " list_id = ".$list_id;
        $whereCondition = array($whereList);
        $where = Base_common::getSqlWhere($whereCondition);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        //生成查询列
        $fields = Base_common::getSqlFields(array("postsCount"=>"count(post_id)"));
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }

	/**
	 * 获取单条记录
	 * @param integer $post_id
	 * @param string $fields
	 * @return array
	 */
	public function getPosts($post_id, $fields = '*')
	{
        $post_id = intval($post_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`post_id` = ?', $post_id);
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
	 * 条件查询post
	 */
    public function getPostWithList($list_id,$fields = '*'){
        $where = 'list_id ='.$list_id.' and visible = 1';
        $table_to_process = Base_Widget::getDbTable($this->table);
        $sql = "select count(1) as postCount,sum(kudos) as kudosCount,user_id from $table_to_process where $where group by user_id order by kudosCount desc ";
        return $this->db->getAll($sql);
    }

}

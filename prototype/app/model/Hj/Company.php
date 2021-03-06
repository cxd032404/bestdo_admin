<?php
/**
 * 企业相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Company extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_company';
    protected $table_access = 'config_company_access';

    protected $banner_list =
        [   "stepBanner"=>"健步走banner",
            "clubBanner"=>"俱乐部banner",
            "wtBanner"=>"文体汇banner",
            "indexBanner"=>"首页banner",
        ];
    protected $app_list =
        [
            101 => "文体之窗",
            201 => "健步走",
            202 => "俱乐部",
        ];
    //获取列表类型
    public function getBannerList()
    {
        return $this->banner_list;
    }
    //获取列表类型
    public function getAppList()
    {
        return $this->app_list;
    }

    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getCompanyList($params = [],$fields = "*")
	{
	    $table_to_process = Base_Widget::getDbTable($this->table);
        $whereName = (isset($params['company_name']) && trim($params['company_name'])!="")?" company_name = '".$params['company_name']."'":"";
        $whereParent = (isset($params['parent_id'])&& $params['parent_id']>0)?" parent_id = ".$params['parent_id']:"";
        $whereExclude = (isset($params['exclude_id'])&& $params['exclude_id']>0)?" company_id != ".$params['exclude_id']:"";

        $wherePermission = isset($params['permissionList'])?(
            count($params['permissionList'])>0?
                ( " company_id in (".implode(",",array_column($params['permissionList'],"company_id")).") ")
                :" 0 "):"";
        $whereCondition = array($wherePermission,$whereParent,$whereName,$whereExclude);
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY company_id ASC";
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
	 * @param integer $company_id
	 * @param string $fields
	 * @return array
	 */
	public function getCompany($company_id, $fields = '*')
	{
		$company_id = intval($company_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`company_id` = ?', $company_id);
	}
	/**
	 * 更新
	 * @param integer $company_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateCompany($company_id, array $bind)
	{
		$company_id = intval($company_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`company_id` = ?', $company_id);
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
	 * @param integer $company_id
	 * @return boolean
	 */
	public function deleteCompany($company_id)
	{
		$company_id = intval($company_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`company_id` = ?', $company_id);
	}
    /**
     * 获取单条记录
     * @param integer $company_id
     * @param string $fields
     * @return array
     */
    public function getAccessByCompany($company_id, $fields = '*')
    {
        $table_to_process = Base_Widget::getDbTable($this->table_access);
        $whereCompany = " company_id = ".$company_id ;
        $whereCondition = array($whereCompany);
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY app_id ASC";
        $return = $this->db->getAll($sql);
        $accessList = array();
        if(count($return))
        {
            foreach($return as $key => $value)
            {
                $accessList[$value['app_id']] = $value;
            }
        }
        return $accessList;
    }
    /**
     * 插入权限
     * @param array $bind
     * @return boolean
     */
    public function insertCompanyAccess(array $bind)
    {
        $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table_access);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 删除权限
     * @param integer $company_id
     * @param integer $app_id
     * @return boolean
     */
    public function deleteCompanyAccess($company_id,$app_id)
    {
        $company_id = intval($company_id);
        $app_id = intval($app_id);
        $table_to_process = Base_Widget::getDbTable($this->table_access);
        return $this->db->delete($table_to_process, '`company_id` = ? and `app_id` = ? ', [$company_id,$app_id]);
    }

}

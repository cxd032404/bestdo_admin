<?php
/**
 * 活动相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_ListSeries extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_list_series';
    protected $table_detail = 'config_list_series_detail';

    public $max_series = 4;

    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getSeriesList($params = [],$fields = "*")
	{
	    $table_to_process = Base_Widget::getDbTable($this->table);
        $wherePermission = isset($params['permissionList'])?(
        count($params['permissionList'])>0?
            ( " company_id in (".implode(",",array_column($params['permissionList'],"company_id")).") ")
            :" 0 "):"";
	    $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereName = (isset($params['series_name']) && trim($params['series_name'])!="")?" series_name = '".trim($params['series_name'])."'":"";
        $whereSign = (isset($params['series_sign']) && trim($params['series_sign'])!="")?" series_sign = '".trim($params['series_sign'])."'":"";
        $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" series_id != ".$params['exclude_id']:"";
        $whereCondition = array($wherePermission,$whereCompany,$whereExclude,$whereName,$whereSign);
        $where = Base_common::getSqlWhere($whereCondition);
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $listCount = $this->getSeriesCount($params);
        }
        else
        {
            $listCount = 0;
        }
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY series_id ASC ".$limit;
        $return = $this->db->getAll($sql);
		$List = ['SeriesCount'=>$listCount,'SeriesList'=>[]];
		if(count($return))
		{
			foreach($return as $key => $value)
			{
                $List['SeriesList'][$value['series_id']] = $value;
			}
		}
		return $List;
	}
    /**
     * 获取记录数量
     * @param $where  查询条件
     * @return integer
     */
        public function getSeriesCount($params)
        {
            //获取需要用到的表名
            $table_to_process = Base_Widget::getDbTable($this->table);
            //生成查询列
            $fields = Base_common::getSqlFields(array("SeriesCount"=>"count(series_id)"));
            $wherePermission = isset($params['permissionList'])?(
            count($params['permissionList'])>0?
                ( " company_id in (".implode(",",array_column($params['permissionList'],"company_id")).") ")
                :" 0 "):"";
            $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
            $whereName = (isset($params['series_name']) && trim($params['series_name'])!="")?" series_name = '".trim($params['series_name'])."'":"";
            $whereSign = (isset($params['series_sign']) && trim($params['series_sign'])!="")?" series_sign = '".trim($params['series_sign'])."'":"";
            $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" series_id != ".$params['exclude_id']:"";
            $whereCondition = array($wherePermission,$whereCompany,$whereExclude,$whereName,$whereSign);
            $where = Base_common::getSqlWhere($whereCondition);
            $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
            return $this->db->getOne($sql);
        }
	/**
	 * 获取单条记录
	 * @param integer $series_id
	 * @param string $fields
	 * @return array
	 */
	public function getSeries($series_id, $fields = '*')
	{
		$series_id = intval($series_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`series_id` = ?', $series_id);
	}
	/**
	 * 更新
	 * @param integer $series_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateSeries($series_id, array $bind)
	{
        $series_id = intval($series_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`series_id` = ?', $series_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertSeries(array $bind)
	{
		$bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $series_id
	 * @return boolean
	 */
	public function deleteSeries($series_id)
	{
		$series_id = intval($series_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`series_id` = ?', $series_id);
	}
    /**
     * 查询全部
     * @param $fields
     * @return array
     */
    public function getSeriesDetailList($series_id,$params = [],$fields = "*")
    {
        $table_to_process = Base_Widget::getDbTable($this->table_detail);
        $whereSeries = (isset($params['series_id']) && $params['series_id']>0)?" series_id = ".$params['series_id']:"";
        $whereCondition = array($whereSeries);
        $where = Base_common::getSqlWhere($whereCondition);
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $listCount = $this->getSeriesDetailCount($params);
        }
        else
        {
            $listCount = 0;
        }
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY detail_id ASC ".$limit;
        $return = $this->db->getAll($sql);
        $List = ['SeriesDetailCount'=>$listCount,'SeriesDetailList'=>[]];
        if(count($return))
        {
            foreach($return as $key => $value)
            {
                $List['SeriesDetailList'][$value['detail_id']] = $value;
            }
        }
        return $List;
    }
    /**
     * 获取记录数量
     * @param $where  查询条件
     * @return integer
     */
    public function getSeriesDetailCount($params)
    {
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_detail);
        //生成查询列
        $fields = Base_common::getSqlFields(array("DetailCount"=>"count(detail)"));
        $whereSeries = (isset($params['series_id']) && $params['series_id']>0)?" series_id = ".$params['series_id']:"";
        $whereCondition = array($whereSeries);
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    /**
     * 获取单条记录
     * @param integer $detail_id
     * @param string $fields
     * @return array
     */
    public function getSeriesDetail($detail_id, $fields = '*')
    {
        $detail_id = intval($detail_id);
        $table_to_process = Base_Widget::getDbTable($this->table_detail);
        return $this->db->selectRow($table_to_process, $fields, '`detail_id` = ?', $detail_id);
    }
    /**
     * 插入
     * @param array $bind
     * @return boolean
     */
    public function insertSeriesDetail(array $bind)
    {
        $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table_detail);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 更新
     * @param integer $detail_id
     * @param array $bind
     * @return boolean
     */
    public function updateSeriesDetail($detail_id, array $bind)
    {
        $detail_id = intval($detail_id);
        $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table_detail);
        return $this->db->update($table_to_process, $bind, '`detail_id` = ?', $detail_id);
    }
    /**
     * 删除
     * @param integer $detail_id
     * @return boolean
     */
    public function deleteSeriesDetail($detail_id)
    {
        $detail_id = intval($detail_id);
        $table_to_process = Base_Widget::getDbTable($this->table_detail);
        return $this->db->delete($table_to_process, '`detail_id` = ?', $detail_id);
    }

}

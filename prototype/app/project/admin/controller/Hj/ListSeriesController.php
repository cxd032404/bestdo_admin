<?php
/**
 * 系列管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_ListSeriesController extends AbstractController
{
	/**系列:Acitvity
	 * @var string
	 */
	protected $sign = '?ctl=hj/list.series';
    protected $ctl = 'hj/list.series';

    /**
	 * game对象
	 * @var object
	 */
	protected $oListSeries;
	protected $oCompany;
    protected $oList;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oListSeries = new Hj_ListSeries();
		$this->oCompany = new Hj_Company();
        $this->oList = new Hj_List();
    }
	//系列配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
		if($PermissionCheck['return'])
		{
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //企业ID
            $params['company_id'] = intval($this->request->company_id??0);
            //分页参数
            $params['Page'] = abs(intval($this->request->Page??1));
            $params['PageSize'] = 20;
            $params['getCount'] = 1;
            //获取企业列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
            //获取系列列表
            $seriesList = $this->oListSeries->getSeriesList(array_merge($params,["permissionList"=>$totalPermission]));
            $userList = [];
            //循环系列列表
			foreach($seriesList['SeriesList'] as $key => $seriesInfo)
            {
                //数据解包
                $seriesList['SeriesList'][$key]['detail'] = json_decode($seriesInfo['detail'],true);
				$seriesList['SeriesList'][$key]['company_name'] = ($seriesInfo['company_id']==0)?"无对应":($companyList[$seriesInfo['company_id']]['company_name']??"未知");

                //$seriesList['SeriesList'][$key]['series_count'] = $list_info['ListCount'];
            }
            $page_url = Base_Common::getUrl('',$this->ctl,'index',$params)."&Page=~page~";
            $page_content =  base_common::multi($seriesList['SeriesCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //渲染模版
			include $this->tpl('Hj_List_SeriesList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加系列填写配置页面
	public function seriesAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addSeries",$this->sign);
		if($PermissionCheck['return'])
		{
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //获取顶级系列列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
			$max_series = $this->oListSeries->max_series;
			$countList = [];
			for($i=1;$i<=$max_series;$i++)
            {
                $countList[] = $i;
            }
			//渲染模版
			include $this->tpl('Hj_List_SeriesAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//添加新系列
	public function seriesInsertAction()
	{
        //检查权限
        $bind=$this->request->from('series_name','company_id','series_sign','series_count','detail');
        //系列名称不能为空
        if(trim($bind['series_name'])=="")
        {
            $response = array('errno' => 1);
        }
        elseif(trim($bind['series_sign'])=="")
        {
            $response = array('errno' => 4);
        }
        else
        {
            $nameExists = $this->oListSeries->getSeriesList(["company_id"=>$bind['company_id'],"series_name"=>$bind["series_name"]]);
            if(count($nameExists['SeriesList'])>0)
            {
                $response = array('errno' => 2);
            }
            else
            {
                $signExists = $this->oListSeries->getSeriesList(["company_id"=>$bind['company_id'],"series_sign"=>$bind["series_sign"]]);
                if(count($signExists['SeriesList'])>0)
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    //数据打包
                    $bind['detail'] = json_encode($bind['detail']);
                    //添加系列
                    $res = $this->oListSeries->insertSeries($bind);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
            }
        }
		echo json_encode($response);
		return true;
	}

	//修改系列信息系列
	public function seriesModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateSeries",$this->sign);
		if($PermissionCheck['return'])
		{
			//系列ID
			$series_id= intval($this->request->series_id);
			//获取系列信息
			$seriesInfo = $this->oListSeries->getSeries($series_id,'*');
			//数据解包
            $seriesInfo['detail'] = json_decode($seriesInfo['detail'],true);
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //获取企业列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
            $max_series = $this->oListSeries->max_series;
            $countList = [];
            for($i=1;$i<=$max_series;$i++)
            {
                $countList[] = $i;
            }
			//渲染模版
			include $this->tpl('Hj_List_SeriesModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//更新系列信息
	public function seriesUpdateAction()
	{
	    //接收系列参数
        $bind=$this->request->from('series_id','series_name','company_id','series_sign','series_count','detail');
        //系列名称不能为空
        if(trim($bind['series_name'])=="")
        {
            $response = array('errno' => 1);
        }
        elseif(trim($bind['series_sign'])=="")
        {
            $response = array('errno' => 4);
        }
		else
		{
            $nameExists = $this->oListSeries->getSeriesList(["company_id"=>$bind['company_id'],"series_name"=>$bind["series_name"],'exclude_id'=>$bind['series_id']]);
            if(count($nameExists['SeriesList'])>0)
            {
                $response = array('errno' => 2);
            }
            else
            {
                $signExists = $this->oListSeries->getSeriesList(["company_id"=>$bind['company_id'],"series_sign"=>$bind["series_sign"],'exclude_id'=>$bind['series_id']]);
                if(count($signExists['SeriesList'])>0)
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    //数据打包
                    $bind['detail'] = json_encode($bind['detail']);
                    //修改系列
                    $res = $this->oListSeries->updateSeries($bind['series_id'],$bind);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
            }
		}
		echo json_encode($response);
		return true;
	}

	//删除系列
	public function seriesDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteSeries",$this->sign);
		if($PermissionCheck['return'])
		{
			//系列ID
			$series_id = trim($this->request->series_id);
			//删除系列
			$this->oListSeries->deleteSeries($series_id);
			//返回之前的系列
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//修改系列详情（元素列表）系列
	public function seriesDetailAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateSeries",$this->sign);
		if($PermissionCheck['return'])
		{
			//系列ID
			$series_id= intval($this->request->series_id);
            //分页参数
            $params['Page'] = abs(intval($this->request->Page??1));
            $params['PageSize'] = 20;
            $params['getCount'] = 1;
            //获取系列信息
            $seriesInfo = $this->oListSeries->getSeries($series_id,'*');
            //获取列表
            $ListList = $this->oList->getListList(['company_id'=>$seriesInfo['company_id']],"list_id,list_name");
			//获取元素信息
			$seriesDetailList = $this->oListSeries->getSeriesDetailList($series_id,$params,"*");
			foreach ($seriesDetailList['SeriesDetailList'] as $key => $detailInfo)
            {
                $detailInfo['detail'] = json_decode($detailInfo['detail'],true);
                foreach($detailInfo['detail']['list_id'] as $key_2 => $list_id)
                {
                    $detailInfo['detail']['list_id'][$key_2] = $ListList['ListList'][$list_id]['list_name']??"未定义";
                }
                $seriesDetailList['SeriesDetailList'][$key] = $detailInfo;
            }
            $max_series = $seriesInfo['series_count'];
            $countList = [];
            for($i=1;$i<=$max_series;$i++)
            {
                $countList[] = $i;
            }
            //渲染模版
			include $this->tpl('Hj_List_SeriesDetail');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //添加系列填写配置页面
    public function seriesDetailAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateSeries",$this->sign);
        if($PermissionCheck['return'])
        {
            //系列ID
            $series_id= intval($this->request->series_id);
            //获取系列信息
            $seriesInfo = $this->oListSeries->getSeries($series_id,'*');
            //数据解包
            $seriesInfo['detail'] = json_decode($seriesInfo['detail'],true);
            //获取列表
            $ListList = $this->oList->getListList(['company_id'=>$seriesInfo['company_id']],"list_id,list_name");
            $countList = [];
            for($i=1;$i<=$seriesInfo['series_count'];$i++)
            {
                $countList[] = $i;
            }
            //渲染模版
            include $this->tpl('Hj_List_SeriesDetailAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }

    //添加新系列
    public function seriesDetailInsertAction()
    {
        //检查权限
        $bind=$this->request->from('series_id','detail_name','detail');
        //系列名称不能为空
        if(trim($bind['detail_name'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            //数据打包
            $bind['detail'] = json_encode($bind['detail']);
            //添加系列
            $res = $this->oListSeries->insertSeriesDetail($bind);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //添加系列填写配置页面
    public function seriesDetailModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateSeries",$this->sign);
        if($PermissionCheck['return'])
        {
            //详情ID
            $detail_id= intval($this->request->detail_id);
            //获取系列详情信息
            $detailInfo = $this->oListSeries->getSeriesDetail($detail_id,'*');
            //数据解包
            $detailInfo['detail'] = json_decode($detailInfo['detail'],true);
            //获取列表
            //获取系列信息
            $seriesInfo = $this->oListSeries->getSeries($detailInfo['series_id'],'series_id,series_count');
            $ListList = $this->oList->getListList(['company_id'=>$seriesInfo['company_id']],"list_id,list_name");
            $countList = [];
            for($i=1;$i<=$seriesInfo['series_count'];$i++)
            {
                $countList[] = $i;
            }
            //渲染模版
            include $this->tpl('Hj_List_SeriesDetailModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改系列
    public function seriesDetailUpdateAction()
    {
        //检查权限
        $bind=$this->request->from('detail_id','detail_name','detail');
        //系列名称不能为空
        if(trim($bind['detail_name'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            //数据打包
            $bind['detail'] = json_encode($bind['detail']);
            //添加系列
            $res = $this->oListSeries->updateSeriesDetail($bind['detail_id'],$bind);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
}

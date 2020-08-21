<?php
/**
 * 企业管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_CompanyController extends AbstractController
{
	/**企业:SportsType
	 * @var string
	 */
	protected $sign = '?ctl=hj/company';
    protected $ctl = 'hj/company';

    /**
	 * game对象
	 * @var object
	 */
	protected $oCompany;
    protected $oProtocal;
    protected $oSource;

    /**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oCompany = new Hj_Company();
        $this->oProtocal = new Hj_Protocal();
        $this->oSource = new Hj_Source();



    }
	//企业配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
            $currentPage = urlencode($_SERVER['QUERY_STRING']);
		    //企业ID
            $type= trim($this->request->type??"");
		    $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //获取企业列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission]);
			//循环企业列表
			foreach($companyList as $key => $companyInfo)
            {
                //数据解包
                $companyList[$key]['detail'] = json_decode($companyInfo['detail'],true);
				$companyList[$key]['parent_name'] = ($companyInfo['parent_id']==0)?"无上级":($companyList[$companyInfo['parent_id']]['company_name']??"未知");
				$companyList[$key]['display_name'] = ($companyInfo['display']==0)?"隐藏":"显示";
				$companyList[$key]['sort'] = $companyInfo['parent_id']==0?($companyInfo['company_id']."_0"):($companyInfo["parent_id"]."_".$companyInfo['company_id']);
                $companyList[$key]['reg_url'] = $this->config->siteUrl.'/'.$this->config->api['site']['company_user_reg']."?company_id=".$companyInfo['company_id'];
            }
            array_multisort(array_column($companyList, "sort"),$companyList);
			//渲染模版
            include $this->tpl('Hj_Company_CompanyList');

		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //企业配置列表页面
    public function bannerAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateBanner");
        if($PermissionCheck['return'])
        {
            $currentPage = urlencode($_SERVER['QUERY_STRING']);
            //企业ID
            $type= trim($this->request->type??"");
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission]);
            //循环企业列表
            foreach($companyList as $key => $companyInfo)
            {
                //数据解包
                $companyList[$key]['detail'] = json_decode($companyInfo['detail'],true);
                $companyList[$key]['parent_name'] = ($companyInfo['parent_id']==0)?"无上级":($companyList[$companyInfo['parent_id']]['company_name']??"未知");
                $companyList[$key]['display_name'] = ($companyInfo['display']==0)?"隐藏":"显示";
                $companyList[$key]['sort'] = $companyInfo['parent_id']==0?($companyInfo['company_id']."_0"):($companyInfo["parent_id"]."_".$companyInfo['company_id']);
                $companyList[$key]['reg_url'] = $this->config->siteUrl.'/'.$this->config->api['site']['company_user_reg']."?company_id=".$companyInfo['company_id'];
            }
            array_multisort(array_column($companyList, "sort"),$companyList);
            //渲染模版
            include $this->tpl('Hj_Company_CompanyListBanner');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
	//添加企业类型填写配置页面
	public function companyAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addCompany",$this->sign);
		if($PermissionCheck['return'])
		{
		    $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
			//获取顶级企业列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission,'parent_id'=>0],"company_id,company_name");
			//渲染模版
			include $this->tpl('Hj_Company_CompanyAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//添加新企业
	public function companyInsertAction()
	{
		//检查权限
		$bind=$this->request->from('company_name','detail','parent_id','display','member_limit');
		//企业名称不能为空
		if(trim($bind['company_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            $companyExists = $this->oCompany->getCompanyList(['company_name'=>$bind['company_name']],'company_id,company_name');
            if(count($companyExists)>0)
            {
                $response = array('errno' => 3);
            }
            else
            {
                $oUpload = new Base_Upload('upload_img');
                $upload = $oUpload->upload('upload_img',$this->config->oss);
                $oss_urls = array_column($upload->resultArr,'oss');
                $bind['icon'] = implode("",$oss_urls);
                if(trim($bind['icon'])=="")
                {
                    $response = array('errno' => 2);
                }
                else
                {
                    //数据打包
                    $bind['detail'] = json_encode($bind['detail']);
                    $companyInfo['member_limit'] = intval(abs($bind['member_limit']));
                    //添加企业
                    $res = $this->oCompany->insertCompany($bind);
                    //$this->manager->insertDataPermission($manager);
                    Base_Common::refreshCache($this->config,"company",$res);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
            }
		}
		echo json_encode($response);
		return true;
	}
	
	//修改企业信息页面
	public function companyModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateCompany",$this->sign);
		if($PermissionCheck['return'])
		{
			//企业ID
			$company_id= intval($this->request->company_id);
			//获取企业信息
			$companyInfo = $this->oCompany->getCompany($company_id,'*');
			//数据解包
			$companyInfo['detail'] = json_decode($companyInfo['detail'],true);
			//获取顶级企业列表
			$companyList = $this->oCompany->getCompanyList(['parent_id'=>0],"company_id,company_name");
            //获取页面列表
            $ListList = (new Hj_List())->getListList(['company_id'=>$company_id],"list_id,list_name");
            //渲染模版
			include $this->tpl('Hj_Company_CompanyModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//更新企业信息
	public function companyUpdateAction()
	{
	    //接收页面参数
		$bind=$this->request->from('company_id','company_name','parent_id','detail','display','member_limit');
        //企业名称不能为空
		if(trim($bind['company_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            $companyExists = $this->oCompany->getCompanyList(['company_name'=>$bind['company_name'],'exclude_id'=>$bind['company_id']],'company_id,company_name');
            if(count($companyExists)>0)
            {
                $response = array('errno' => 3);
            }
            else
            {
                $oUpload = new Base_Upload('upload_img');
                $upload = $oUpload->upload('upload_img',$this->config->oss);
                $oss_urls = array_column($upload->resultArr,'oss');

                $bind['icon'] = implode("",$oss_urls);
                if(trim($bind['icon']) == "")
                {
                    unset($bind['icon']);
                }
                //获取企业信息
                $companyInfo = $this->oCompany->getCompany($bind['company_id'],'*');
                //数据解包
                $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
                $companyInfo['member_limit'] = intval(abs($bind['member_limit']));
                //数据打包
                $bind['detail'] = json_encode(array_merge($companyInfo['detail'],$bind['detail']));
                //修改企业
                $res = $this->oCompany->updateCompany($bind['company_id'],$bind);
                Base_Common::refreshCache($this->config,"company",$bind['company_id']);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
		}
		echo json_encode($response);
		return true;
	}
	
	//删除企业
	public function companyDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteCompany",$this->sign);
		if($PermissionCheck['return'])
		{
			//企业ID
			$company_id = trim($this->request->company_id);
			//获取下属企业列表
			$companyList = $this->oCompany->getCompanyList(['parent_id'=>$company_id],"company_id");
			//循环删除下属企业
			if(count($companyList)>0)
			{
				foreach($companyList as $companyInfo)
				{
					$this->oCompany->deleteCompany($companyInfo['company_id']);
				}
			}
			//删除企业
			$this->oCompany->deleteCompany($company_id);
			//返回之前的页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //修改协议信息页面
    public function protocalModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany",$this->sign);
        if($PermissionCheck['return'])
        {
            //企业ID
            $company_id = intval($this->request->company_id);
            //类型
            $type = trim($this->request->type??'privacy');
            $protocalTypeList = $this->oProtocal->getPrototcalType();
            $type = isset($protocalTypeList[$type])?$type:"user";
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($company_id,'*');
            //获取协议信息
            $protocal = $this->oProtocal->getProtocalByType($company_id,$type,'*');
            //渲染模版
            include $this->tpl('Hj_Company_ProtocalModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }

    }
    //更新协议信息
    public function protocalUpdateAction()
    {
        //接收页面参数
        $bind=$this->request->from('company_id','type','content');
        $protocalTypeList = $this->oProtocal->getPrototcalType();
        $bind['type'] = isset($protocalTypeList[$bind['type']])?$bind['type']:"user";

        //获取协议信息
        $protocal = $this->oProtocal->getProtocalByType($bind['company_id'],$bind['type'],'*');
        if(!isset($protocal['protocal_id']))
        {
            //新增
            $res = $this->oProtocal->insertProtocal($bind);
        }
        else
        {
            //更新
            $res = $this->oProtocal->updateProtocal($protocal['protocal_id'],$bind);
        }
        $response = $res ? array('errno' => 0) : array('errno' => 9);
        echo json_encode($response);
        return true;
    }
    //特殊列表配置页面
    public function listAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany",$this->sign);
        if($PermissionCheck['return'])
        {
            //企业ID
            $company_id= intval($this->request->company_id);
            //列表类型
            $type= trim($this->request->type);
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($company_id,'*');
            //数据解包
            $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
            $listType = (new Hj_List())->getSpecifiedListType();
            $typeName = $listType[$type];
            //获取页面列表
            $ListList = (new Hj_List())->getListList(['company_id'=>$company_id],"list_id,list_name");
            foreach($ListList['ListList'] as $key => $listInfo)
            {
                if(isset($companyInfo['detail'][$type][$listInfo['list_id']]))
                {
                    $ListList['ListList'][$key]['checked'] = 1;
                }
                else
                {
                    $ListList['ListList'][$key]['checked'] = 0;
                }
            }
            //渲染模版
            include $this->tpl('Hj_Company_List');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }

    }
    //特殊列表修改
    public function listUpdateAction()
    {
        //接收页面参数
		$bind=$this->request->from('company_id','list','type');
		if(count($bind['list'])==0)
        {
            $response = array('errno' => 1);
        }
        else
        {
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($bind['company_id'],'company_id,company_name,detail');
            //数据解包
            $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
            $companyInfo['detail'][$bind['type']] = $bind['list'];
            //数据打包
            $companyInfo['detail'] = json_encode($companyInfo['detail']);
            //修改企业
            $res = $this->oCompany->updateCompany($bind['company_id'],$companyInfo);
            Base_Common::refreshCache($this->config,"company",$bind['company_id']);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
		echo json_encode($response);
		return true;
    }
    //健步走自定义时间段列表
    public function stepDateRangeAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany",$this->sign);
        if($PermissionCheck['return'])
        {
            //企业ID
            $params['company_id'] = intval($this->request->company_id);
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($params['company_id'],'*');
            //数据解包
            $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
            //获取日期信息
            $oDateRange = new Hj_StepDateRange();
            //分页参数
            $params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
            $params['PageSize'] = 20;
            $params['getCount'] = 1;
            $DateRange = $oDateRange->getDateList($params);
            foreach($DateRange['DateList'] as $key => $value)
            {
                $DateRange['DateList'][$key]['detail'] = json_decode($value['detail'],true);
            }
            //翻页参数
            $page_url = Base_Common::getUrl('',$this->ctl,'step.date.range',$params)."&Page=~page~";
            $page_content =  base_common::multi($DateRange['DateCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //渲染模版
            include $this->tpl('Hj_Company_StepDateRange');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加健步走日期段页面
    public function stepDateRangeAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany",$this->sign);
        if($PermissionCheck['return'])
        {
            $currentTime = time();
            //企业ID
            $company_id= intval($this->request->company_id);
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($company_id,'*');
            $startDate  = date("Y-m-d",$currentTime+3*86400);
            $endDate  = date("Y-m-d",$currentTime+(3+30)*86400);
            //渲染模版
            include $this->tpl('Hj_Company_StepDateRangeAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加健步走banner
    public function stepDateRangeInsertAction()
    {
        //检查权限
        $bind=$this->request->from('company_id','detail','start_date','end_date');
        //日期校验
        if(strtotime($bind['start_date'])==0 || strtotime($bind['end_date'])==0 || (strtotime($bind['start_date']) > strtotime($bind['end_date'])))
        {
            $response = array('errno' => 1);
        }
        else
        {
            //获取时间冲突的日期信息
            $oDateRange = new Hj_StepDateRange();
            $exists = $oDateRange->checkDateExist($bind);
            if($exists>0)
            {
                $response = array('errno' => 3);
            }
            else
            {
                //数据打包
                $bind['detail'] = json_encode($bind['detail']);
                //添加日期段
                $res = $oDateRange->insertDateRange($bind);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
        }
        echo json_encode($response);
        return true;
    }
    //添加健步走日期段页面
    public function stepDateRangeModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany",$this->sign);
        if($PermissionCheck['return'])
        {
            //记录ID
            $date_id= intval($this->request->date_id);
            $oDateRange = new Hj_StepDateRange();
            $dateRangeInfo = $oDateRange->getDateRange($date_id);
            $dateRangeInfo['detail'] = json_decode($dateRangeInfo['detail'],true);
            //渲染模版
            include $this->tpl('Hj_Company_StepDateRangeModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //更新健步走banner
    public function stepDateRangeUpdateAction()
    {
        //检查权限
        $bind=$this->request->from('company_id','date_id','detail','start_date','end_date');
        //日期校验
        if(strtotime($bind['start_date'])==0 || strtotime($bind['end_date'])==0 || (strtotime($bind['start_date']) > strtotime($bind['end_date'])))
        {
            $response = array('errno' => 1);
        }
        else
        {
            //获取时间冲突的日期信息
            $oDateRange = new Hj_StepDateRange();
            $exists = $oDateRange->checkDateExist($bind);
            if($exists>0)
            {
                $response = array('errno' => 3);
            }
            else
            {
                //数据打包
                $bind['detail'] = json_encode($bind['detail']);
                //添加日期段
                $res = $oDateRange->updateDateRange($bind['date_id'],$bind);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
        }
        echo json_encode($response);
        return true;
    }
    //删除日期
    public function stepDateRangeDeleteAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany",$this->sign);
        if($PermissionCheck['return'])
        {
            $oDateRange = new Hj_StepDateRange();
            //记录ID
            $date_id= intval($this->request->date_id);
            //删除俱乐部
            $oDateRange->deleteDateRange($date_id);
            //返回之前的俱乐部
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //俱乐部banner列表
    public function bannerListAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateBanner",$this->request->currentPage);
        if($PermissionCheck['return'])
        {
            $currentPage = urlencode($this->request->currentPage);
            //企业ID
            $company_id= intval($this->request->company_id);
            //banner类型
            $banner_type= trim($this->request->banner_type)??"clubBanner";
            $bannerType = (new Hj_Company())->getBannerList();
            $typeName = $bannerType[$banner_type];
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($company_id,'*');
            //数据解包
            $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
            if(isset($companyInfo['detail'][$banner_type]))
            {
                foreach($companyInfo['detail'][$banner_type] as $key => $value)
                {
                    if(!is_array($value))
                    {
                        $companyInfo['detail'][$banner_type][$key] = $this->oSource->getSource($value);
                    }
                }
            }
            $bannerList = $companyInfo['detail'][$banner_type];
            //渲染模版
            include $this->tpl('Hj_Company_BannerList');
        }
        else
        {
            $home = "?".$this->request->currentPage;
            include $this->tpl('403');
        }

    }
    //俱乐部banner页面
    public function bannerAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateBanner",$this->request->currentPage);
        if($PermissionCheck['return'])
        {
            $currentPage = urlencode($this->request->currentPage);
            //banner类型
            $banner_type= trim($this->request->banner_type)??"clubBanner";
            //企业ID
            $company_id= intval($this->request->company_id);
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($company_id,'*');
            $start_time = date("Y-m-d H:i:s",time()+86400);
            $end_time = date("Y-m-d H:i:s",time()+86400*30);
            //渲染模版
            include $this->tpl('Hj_Company_BannerAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加banner
    public function bannerInsertAction()
    {
        //企业ID
        $company_id = intval($this->request->company_id);
        //banner类型
        $banner_type= trim($this->request->banner_type)??"clubBanner";
        $detail = $this->request->detail;
        $companyInfo = $this->oCompany->getCompany($company_id,"company_id,detail");
        $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
        //上传图片
        $oUpload = new Base_Upload('upload_img');
        $upload = $oUpload->upload('upload_img',$this->config->oss);
        $oss_urls = array_column($upload->resultArr,'oss');
        //如果没有成功上传
        if(!isset($oss_urls['0']) || $oss_urls['0'] == "")
        {
            $response = array('errno' => 1);
        }
        else
        {
            $companyInfo['detail'][$banner_type] = $companyInfo['detail'][$banner_type]??[];
            $imgData = [
                'img_url'=> $oss_urls['0'],
                'img_jump_url'=>trim($detail['img_jump_url']??""),
                'text'=>trim($detail['text']??""),
                'title'=>trim($detail['title']??""),
                'sort'=>trim($detail['sort']??""),
                'start_time'=>trim($detail['start_time']??""),
                'end_time'=>trim($detail['end_time']??""),
            ];
            $imgData = array_merge($imgData,['type'=>"company","type_id"=>$company_id,"sub_type"=>$banner_type]);
            $source_id = $this->oSource->insertSource($imgData);
            if($source_id)
            {
                $companyInfo['detail'][$banner_type][] = $source_id;
                $companyInfo['detail'] = json_encode($companyInfo['detail']);
                $res = $this->oCompany->updateCompany($company_id,$companyInfo);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
            else
            {
                $response = array('errno' => 9);
            }
            Base_Common::refreshCache($this->config,"company",$company_id);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //俱乐部Banner页面
    public function bannerModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateBanner",$this->request->currentPage);
        if($PermissionCheck['return'])
        {
            //banner类型
            $banner_type= trim($this->request->banner_type)??"clubBanner";
            $currentPage = urlencode($this->request->currentPage);
            //元素ID
            $company_id = intval($this->request->company_id);
            $pos = intval($this->request->pos??0);
            $companyInfo = $this->oCompany->getCompany($company_id,"company_id,detail");
            $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
            $bannerInfo = $companyInfo['detail'][$banner_type][$pos];
            if(!is_array($bannerInfo))
            {
                $bannerInfo = $this->oSource->getSource($bannerInfo);
            }
            //渲染模版
            include $this->tpl('Hj_Company_BannerModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //更新俱乐部banner详情
    public function bannerUpdateAction()
    {
        //banner类型
        $banner_type= trim($this->request->banner_type)??"clubBanner";
        //企业ID
        $company_id = intval($this->request->company_id);
        $detail = $this->request->detail;
        $companyInfo = $this->oCompany->getCompany($company_id,"company_id,detail");
        $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
        $pos = intval($this->request->pos??0);
        //上传图片
        $oUpload = new Base_Upload('upload_img');
        $upload = $oUpload->upload('upload_img',$this->config->oss);
        $oss_urls = array_column($upload->resultArr,'oss');
        if(is_array($companyInfo['detail'][$banner_type][$pos]))
        {
            $origin = $companyInfo['detail'][$banner_type][$pos];
            $toCreate = 1;
        }
        else
        {
            $origin_id = $companyInfo['detail'][$banner_type][$pos];
            $origin = $this->oSource->getSource($origin_id);
            $toUpdate = 1;
        }
        //如果以前没上传过且这次也没有成功上传
        if((!isset($origin['img_url']) || $origin['img_url']=="") && (!isset($oss_urls['0']) || $oss_urls['0'] == ""))
        {
            $response = array('errno' => 2);
        }
        else
        {
            $imgData = [
                'img_url'=> (isset($oss_urls['0']) && $oss_urls['0']!="")?($oss_urls['0']):($origin['img_url']),
                'img_jump_url'=>trim($detail['img_jump_url']??""),
                'text'=>trim($detail['text']??""),
                'title'=>trim($detail['title']??""),
                'sort'=>trim($detail['sort']??""),
                'start_time'=>trim($detail['start_time']??""),
                'end_time'=>trim($detail['end_time']??""),
            ];
            if($toCreate == 1)
            {
                $imgData = array_merge($imgData,['type'=>"company","type_id"=>$company_id,"sub_type"=>$banner_type]);
                $source_id = $this->oSource->insertSource($imgData);
                if($source_id)
                {
                    $companyInfo['detail'][$banner_type][$pos] = $source_id;
                    $companyInfo['detail'] = json_encode($companyInfo['detail']);
                    $res = $this->oCompany->updateCompany($company_id,$companyInfo);
                }
                else
                {
                    $res = 9;
                }
            }
            else
            {
                $res = $this->oSource->updateSource($origin_id,$imgData);
            }
            Base_Common::refreshCache($this->config,"company",$company_id);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //删除banner详情
    public function bannerDeleteAction()
    {
        //banner类型
        $banner_type= trim($this->request->banner_type)??"clubBanner";
        //企业ID
        $company_id = intval($this->request->company_id);
        $detail = $this->request->detail;
        $companyInfo = $this->oCompany->getCompany($company_id,"company_id,detail");
        $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
        $pos = intval($this->request->pos??0);
        if(isset($companyInfo['detail'][$banner_type][$pos]))
        {
            unset($companyInfo['detail'][$banner_type][$pos]);
            $companyInfo['detail'][$banner_type] = array_values($companyInfo['detail'][$banner_type]);
            $companyInfo['detail'] = json_encode($companyInfo['detail']);
            $res = $this->oCompany->updateCompany($company_id,$companyInfo);
            Base_Common::refreshCache($this->config,"company",$company_id);
        }
        $this->response->goBack();
    }
    public function regPageQrAction()
    {
        include('Third/phpqrcode/phpqrcode.php');
        //企业ID
        $company_id = intval($this->request->company_id);
        $url = $this->config->siteUrl.'/'.$this->config->api['site']['company_user_reg']."?company_id=".$company_id;
        $url = urlencode($url);
        include $this->tpl('Hj_Company_RegQR');
    }
    public function regPageQrMiniprogramAction()
    {
        //include('Third/phpqrcode/phpqrcode.php');
        //企业ID
        $company_id = intval($this->request->id);
        $filePath = "upload/RegQR-".$company_id.".png";
        $fileExist = file_exists($filePath);
        if(!$fileExist)
        {
            $url = $url = $this->config->adminUrl.'/callback/miniprogram.php?company_id='.$company_id;
            file_get_contents($url);
        }
        $img_url = $this->config->adminUrl.'/'.$filePath;
        include $this->tpl('Hj_Company_RegQRM');
    }
    public function getAuthByCompanyAction()
    {
        $company_id = intval($this->request->company_id);
        //获取企业信息
        $companyInfo = $this->oCompany->getCompany($company_id,'*');
        //数据解包
        $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
        $companyUserAuthType = (new Hj_UserInfo())->getCompanyUserAuthType();
        $currentAuthType = $companyInfo['detail']['authType']??"";
        $text = "";
        foreach($companyUserAuthType as $authType => $authTypeName)
        {
            if($currentAuthType==$authType)
            {
                $text .= ('<input name="auth_type" id="auth_type" type="radio" value="'.$authType.'" checked /> '.$authTypeName);
            }
            else
            {
                if(isset($companyUserAuthType[$currentAuthType]))
                {
                    $text .= ('<input name="auth_type" id="auth_type" type="radio" value="'.$authType.'" disabled /> '.$authTypeName);
                }
                else
                {
                    $text .= ('<input name="auth_type" id="auth_type" type="radio" value="'.$authType.'"/> '.$authTypeName);
                }
            }
        }
        echo $text;
    }
    //修改企业权限页面
    public function companyAccessModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany",$this->sign);
        if($PermissionCheck['return'])
        {
            //企业ID
            $company_id= intval($this->request->company_id);
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($company_id,'*');
            //获取企业权限列表
            $companyAccess = $this->oCompany->getAccessByCompany($company_id);
            //客户端列表
            $appList = $this->oCompany->getAppList();
            $accessList = [];
            foreach($appList as $key => $value)
            {
                $accessList[$key] = ['access'=>isset($companyAccess[$key])?1:0,"name"=>$value];
            }
            //渲染模版
            include $this->tpl('Hj_Company_CompanyAccessModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //权限修改
    public function companyAccessUpdateAction()
    {
        //接收页面参数
        $bind=$this->request->from('company_id','access');
        //获取企业权限列表
        $companyAccess = $this->oCompany->getAccessByCompany($bind['company_id']);
        $toAdd = [];
        $toDelete = [];
        foreach($bind['access'] as $app_id => $value)
        {
            //现在有，原来没有
            if(!isset($companyAccess[$app_id]))
            {
                $toAdd[] = $app_id;
            }
        }
        foreach($companyAccess as $app_id => $value)
        {
            if(!isset($bind['access'][$app_id]))
            {
                $toDelete[] = $app_id;
            }
        }
        foreach($toAdd as $app_id)
        {
            $this->oCompany->insertCompanyAccess(['company_id'=>$bind['company_id'],'app_id'=>$app_id]);
        }
        foreach($toDelete as $app_id)
        {
            $this->oCompany->deleteCompanyAccess($bind['company_id'],$app_id);
        }
        $response = array('errno' => 0);
        echo json_encode($response);
        return true;
    }
}

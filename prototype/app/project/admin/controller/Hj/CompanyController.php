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


    }
	//企业配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList();
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
	//添加企业类型填写配置页面
	public function companyAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addCompany");
		if($PermissionCheck['return'])
		{
			//获取顶级企业列表
			$companyList = $this->oCompany->getCompanyList(['parent_id'=>0],"company_id,company_name");
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
		$bind=$this->request->from('company_name','detail','parent_id','display');
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
                    //添加企业
                    $res = $this->oCompany->insertCompany($bind);
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
		$PermissionCheck = $this->manager->checkMenuPermission("updateCompany");
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
            $listList = (new Hj_List())->getListList(['company_id'=>$company_id],"list_id,list_name");
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
		$bind=$this->request->from('company_id','company_name','parent_id','detail','display');
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
		$PermissionCheck = $this->manager->checkMenuPermission("deleteCompany");
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
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany");
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
    //精品课配置页面
    public function boutiqueAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany");
        if($PermissionCheck['return'])
        {
            //企业ID
            $company_id= intval($this->request->company_id);
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($company_id,'*');
            //数据解包
            $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
            //获取页面列表
            $listList = (new Hj_List())->getListList(['company_id'=>$company_id],"list_id,list_name");
            foreach($listList as $key => $listInfo)
            {
                if(isset($companyInfo['detail']['boutique'][$listInfo['list_id']]))
                {
                    $listList[$key]['checked'] = 1;
                }
                else
                {
                    $listList[$key]['checked'] = 0;
                }
            }
            //渲染模版
            include $this->tpl('Hj_Company_Boutique');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }

    }
    //精品课列表修改
    public function boutiqueUpdateAction()
    {
        //接收页面参数
		$bind=$this->request->from('company_id','boutique');
        if(count($bind['boutique'])==0)
        {
            $response = array('errno' => 1);
        }
        else
        {
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($bind['company_id'],'company_id,company_name,detail');
            //数据解包
            $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
            $companyInfo['detail']['boutique'] = $bind['boutique'];
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
    //健步走banner列表
    public function stepBannerAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany");
        if($PermissionCheck['return'])
        {
            //企业ID
            $company_id= intval($this->request->company_id);
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($company_id,'*');
            //数据解包
            $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
            //渲染模版
            include $this->tpl('Hj_Company_StepBanner');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加健步走banner页面
    public function stepBannerAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany");
        if($PermissionCheck['return'])
        {
            //企业ID
            $company_id= intval($this->request->company_id);
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($company_id,'*');
            //渲染模版
            include $this->tpl('Hj_Company_StepBannerAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加健步走banner
    public function stepBannerInsertAction()
    {
        //企业ID
        $company_id = intval($this->request->company_id);
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
            $companyInfo['detail']['stepBanner'] = $companyInfo['detail']['stepBanner']??[];
            $companyInfo['detail']['stepBanner'][] = ['img_url'=>$oss_urls['0'],'img_jump_url'=>$detail['img_jump_url'],'text'=>trim($detail['text']??""),'title'=>trim($detail['title']??"")];
            $companyInfo['detail'] = json_encode($companyInfo['detail']);
            $res = $this->oCompany->updateCompany($company_id,$companyInfo);
            Base_Common::refreshCache($this->config,"company",$company_id);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //修改健步走Banner页面
    public function stepBannerModifyAction()
    {
        //元素ID
        $company_id = intval($this->request->company_id);
        $pos = intval($this->request->pos??0);
        $companyInfo = $this->oCompany->getCompany($company_id,"company_id,detail");
        $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
        $bannerInfo = $companyInfo['detail']['stepBanner'][$pos];
        //渲染模版
        include $this->tpl('Hj_Company_StepBannerModify');
    }
    //更新健步走banner详情
    public function stepBannerUpdateAction()
    {
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
        //如果以前没上传过且这次也没有成功上传
        if((!isset($companyInfo['detail']['stepBanner'][$pos]['img_url']) || $companyInfo['detail']['stepBanner'][$pos]['img_url']=="") && (!isset($oss_urls['0']) || $oss_urls['0'] == ""))
        {
            $response = array('errno' => 2);
        }
        else
        {
            //这次传成功了就用这次，否则维持
            $companyInfo['detail']['stepBanner'][$pos]['img_url'] = (isset($oss_urls['0']) && $oss_urls['0']!="")?($oss_urls['0']):($companyInfo['detail']['stepBanner'][$pos]['img_url']);
            //保存跳转链接
            $companyInfo['detail']['stepBanner'][$pos]['img_jump_url'] = trim($detail['img_jump_url']??"");
            $companyInfo['detail']['stepBanner'][$pos]['text'] = trim($detail['text']??"");
            $companyInfo['detail']['stepBanner'][$pos]['title'] = trim($detail['title']??"");
            $companyInfo['detail'] = json_encode($companyInfo['detail']);

            $res = $this->oCompany->updateCompany($company_id,$companyInfo);
            Base_Common::refreshCache($this->config,"company",$company_id);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //删除健步走banner详情
    public function stepBannerDeleteAction()
    {
        //企业ID
        $company_id = intval($this->request->company_id);
        $detail = $this->request->detail;
        $companyInfo = $this->oCompany->getCompany($company_id,"company_id,detail");
        $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
        $pos = intval($this->request->pos??0);
        if(isset($companyInfo['detail']['stepBanner'][$pos]))
        {
            unset($companyInfo['detail']['stepBanner'][$pos]);
            $companyInfo['detail']['stepBanner'] = array_values($companyInfo['detail']['stepBanner']);
            $companyInfo['detail'] = json_encode($companyInfo['detail']);
            $res = $this->oCompany->updateCompany($company_id,$companyInfo);
            Base_Common::refreshCache($this->config,"company",$company_id);
        }
        $this->response->goBack();
    }
    //健步走自定义时间段列表
    public function stepDateRangeAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany");
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
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany");
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
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany");
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
    public function dateRangeDeleteAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany");
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
    public function clubBannerAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany");
        if($PermissionCheck['return'])
        {
            //企业ID
            $company_id= intval($this->request->company_id);
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($company_id,'*');
            //数据解包
            $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
            //渲染模版
            include $this->tpl('Hj_Company_ClubBanner');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加俱乐部banner页面
    public function clubBannerAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateCompany");
        if($PermissionCheck['return'])
        {
            //企业ID
            $company_id= intval($this->request->company_id);
            //获取企业信息
            $companyInfo = $this->oCompany->getCompany($company_id,'*');
            //渲染模版
            include $this->tpl('Hj_Company_ClubBannerAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加俱乐部banner
    public function clubBannerInsertAction()
    {
        //企业ID
        $company_id = intval($this->request->company_id);
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
            $companyInfo['detail']['clubBanner'] = $companyInfo['detail']['clubBanner']??[];
            $companyInfo['detail']['clubBanner'][] = ['img_url'=>$oss_urls['0'],'img_jump_url'=>$detail['img_jump_url'],'text'=>trim($detail['text']??""),'title'=>trim($detail['title']??"")];
            $companyInfo['detail'] = json_encode($companyInfo['detail']);
            $res = $this->oCompany->updateCompany($company_id,$companyInfo);
            Base_Common::refreshCache($this->config,"company",$company_id);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //修改俱乐部Banner页面
    public function clubBannerModifyAction()
    {
        //元素ID
        $company_id = intval($this->request->company_id);
        $pos = intval($this->request->pos??0);
        $companyInfo = $this->oCompany->getCompany($company_id,"company_id,detail");
        $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
        $bannerInfo = $companyInfo['detail']['clubBanner'][$pos];
        //渲染模版
        include $this->tpl('Hj_Company_ClubBannerModify');
    }
    //更新俱乐部banner详情
    public function clubBannerUpdateAction()
    {
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
        //如果以前没上传过且这次也没有成功上传
        if((!isset($companyInfo['detail']['clubBanner'][$pos]['img_url']) || $companyInfo['detail']['clubBanner'][$pos]['img_url']=="") && (!isset($oss_urls['0']) || $oss_urls['0'] == ""))
        {
            $response = array('errno' => 2);
        }
        else
        {
            //这次传成功了就用这次，否则维持
            $companyInfo['detail']['clubBanner'][$pos]['img_url'] = (isset($oss_urls['0']) && $oss_urls['0']!="")?($oss_urls['0']):($companyInfo['detail']['stepBanner'][$pos]['img_url']);
            //保存跳转链接
            $companyInfo['detail']['clubBanner'][$pos]['img_jump_url'] = trim($detail['img_jump_url']??"");
            $companyInfo['detail']['clubBanner'][$pos]['text'] = trim($detail['text']??"");
            $companyInfo['detail']['clubBanner'][$pos]['title'] = trim($detail['title']??"");
            $companyInfo['detail'] = json_encode($companyInfo['detail']);

            $res = $this->oCompany->updateCompany($company_id,$companyInfo);
            Base_Common::refreshCache($this->config,"company",$company_id);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //删除俱乐部banner详情
    public function clubBannerDeleteAction()
    {
        //企业ID
        $company_id = intval($this->request->company_id);
        $detail = $this->request->detail;
        $companyInfo = $this->oCompany->getCompany($company_id,"company_id,detail");
        $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
        $pos = intval($this->request->pos??0);
        if(isset($companyInfo['detail']['clubBanner'][$pos]))
        {
            unset($companyInfo['detail']['clubBanner'][$pos]);
            $companyInfo['detail']['clubBanner'] = array_values($companyInfo['detail']['clubBanner']);
            $companyInfo['detail'] = json_encode($companyInfo['detail']);
            $res = $this->oCompany->updateCompany($company_id,$companyInfo);
            Base_Common::refreshCache($this->config,"company",$company_id);
        }
        $this->response->goBack();
    }
}

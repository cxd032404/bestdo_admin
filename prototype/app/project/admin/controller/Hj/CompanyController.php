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
                $companyList[$key]['comment'] = json_decode($companyInfo['comment'],true);
				$companyList[$key]['parent_name'] = ($companyInfo['parent_id']==0)?"无上级":($companyList[$companyInfo['parent_id']]['company_name']??"未知");
				$companyList[$key]['display_name'] = ($companyInfo['display']==0)?"隐藏":"显示";
				$companyList[$key]['sort'] = $companyInfo['parent_id']==0?($companyInfo['company_id']."_0"):($companyInfo["parent_id"]."_".$companyInfo['company_id']);
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
		$bind=$this->request->from('company_name','comment','parent_id','display');
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
                    $bind['comment'] = json_encode($bind['comment']);
                    //添加企业
                    $res = $this->oCompany->insertCompany($bind);
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
			$companyInfo['comment'] = json_decode($companyInfo['comment'],true);
			//获取顶级企业列表
			$companyList = $this->oCompany->getCompanyList(['parent_id'=>0],"company_id,company_name");
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
		$bind=$this->request->from('company_id','company_name','parent_id','comment','display');
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
                //数据打包
                $bind['comment'] = json_encode($bind['comment']);
                //修改企业
                $res = $this->oCompany->updateCompany($bind['company_id'],$bind);
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
}

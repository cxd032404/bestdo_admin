<?php
/**
 * 页面管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Bestdo_PageController extends AbstractController
{
	/**企业:SportsType
	 * @var string
	 */
	protected $sign = '?ctl=bestdo/page';
    protected $ctl = 'bestdo/page';

    /**
	 * game对象
	 * @var object
	 */
	protected $oPage;
	protected $oCompany;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oPage = new Bestdo_Page();
		$this->oCompany = new Bestdo_Company();

	}
	//企业配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//页面ID
			$company_id = intval($this->request->company_id??0);
			//获取企业列表
			$pageList = $this->oPage->getPageList(['company_id'=>$company_id]);
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
			//循环企业列表
			foreach($pageList as $key => $pageInfo)
            {
                //数据解包
                $pageList[$key]['comment'] = json_decode($pageInfo['comment'],true);
				$pageList[$key]['company_name'] = ($pageInfo['company_id']==0)?"无对应":($companyList[$pageInfo['company_id']]['company_name']??"未知");
            }
			//渲染模版
			include $this->tpl('Bestdo_Page_PageList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加企业类型填写配置页面
	public function pageAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addPage");
		if($PermissionCheck['return'])
		{
			//获取顶级企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
			//渲染模版
			include $this->tpl('Bestdo_Page_PageAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//添加新企业
	public function pageInsertAction()
	{
		//检查权限
		$bind=$this->request->from('page_name','page_url','company_id');
		//企业名称不能为空
		if(trim($bind['page_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			if(trim($bind['page_url'])=="")
			{
				$response = array('errno' => 2);
			}
			else
			{
				$bind['comment'] = [];
				//数据打包
	            $bind['comment'] = json_encode($bind['comment']);
			    //添加企业
				$res = $this->oPage->insertPage($bind);
				$response = $res ? array('errno' => 0,'company_id'=>$bind['company_id']) : array('errno' => 9);
			}

		}
		echo json_encode($response);
		return true;
	}
	
	//修改企业信息页面
	public function pageModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updatePage");
		if($PermissionCheck['return'])
		{
			//企业ID
			$page_id= intval($this->request->page_id);
			//获取企业信息
			$pageInfo = $this->oPage->getPage($page_id,'*');			
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //渲染模版
			include $this->tpl('Bestdo_Page_PageModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//更新企业信息
	public function pageUpdateAction()
	{
	    //接收页面参数
		$bind=$this->request->from('page_id','page_name','company_id','page_url');
        //企业名称不能为空
		if(trim($bind['page_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			if(trim($bind['page_url'])=="")
			{
				$response = array('errno' => 2);
			}
			else
			{
	            //数据打包
	            $bind['comment'] = json_encode([]);
				$currentPageInfo = $this->oPage->getPage($bind['page_id'],"page_id,company_id");
				//修改企业
				$res = $this->oPage->updatePage($bind['page_id'],$bind);
				$response = $res ? array('errno' => 0,'company_id'=>$bind['company_id']) : array('errno' => 9,'company_id'=>$currentPageInfo['company_id']);	
			}
		}
		echo json_encode($response);
		return true;
	}
	
	//删除企业
	public function pageDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deletePage");
		if($PermissionCheck['return'])
		{
			//页面ID
			$page_id = trim($this->request->page_id);
			//删除页面
			$this->oPage->deletePage($page_id);
			//返回之前的页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
}

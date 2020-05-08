<?php
/**
 * 页面管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_ListController extends AbstractController
{
	/**页面:Acitvity
	 * @var string
	 */
	protected $sign = '?ctl=hj/list';
    protected $ctl = 'hj/list';

    /**
	 * game对象
	 * @var object
	 */
	protected $oList;
	protected $oCompany;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oList = new Hj_List();
		$this->oCompany = new Hj_Company();

	}
	//页面配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//企业ID
			$company_id = intval($this->request->company_id??0);
            //列表分类
            $list_type = trim($this->request->list_type??0);
			//获取页面列表
			$listList = $this->oList->getListList(['company_id'=>$company_id]);
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //获取列表类型列表
            $listTypeList = $this->oList->getListType();
			//循环页面列表
			foreach($listList as $key => $listInfo)
            {
                //数据解包
                $listList[$key]['detail'] = json_decode($listInfo['detail'],true);
                $listList[$key]['company_name'] = ($listInfo['company_id']==0)?"无对应":($companyList[$listInfo['company_id']]['company_name']??"未知");
                $listList[$key]['list_type_name'] = ($listInfo['company_id']==0)?"无对应":($listTypeList[$listInfo['list_type']]??"未知");
            }
			//渲染模版
			include $this->tpl('Hj_List_List');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加列表填写配置
	public function listAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addList");
		if($PermissionCheck['return'])
		{
			//获取顶级页面列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //获取列表类型列表
            $listTypeList = $this->oList->getListType();
			//渲染模版
			include $this->tpl('Hj_List_ListAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//添加新页面
	public function listInsertAction()
	{
		//检查权限
		$bind=$this->request->from('list_name','company_id','list_type','detail');
		//页面名称不能为空
		if(trim($bind['list_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            $listExists = $this->oList->getListList(['company_id'=>$bind['company_id'],'list_name'=>trim($bind['list_name'])],'list_id');
            if(count($listExists)>0)
            {
                $response = array('errno' => 2);
            }
            else
            {
                //数据打包
                $bind['detail'] = json_encode($bind['detail']);
                //添加列表
                $res = $this->oList->insertList($bind);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }

		}
		echo json_encode($response);
		return true;
	}

	//修改列表信息页面
	public function listModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateList");
		if($PermissionCheck['return'])
		{
			//列表ID
			$list_id= intval($this->request->list_id);
			//获取列表信息
			$listInfo = $this->oList->getList($list_id,'*');
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //获取列表类型列表
            $listTypeList = $this->oList->getListType();
            //数据解包
            $listInfo['detail'] = json_decode($listInfo['detail'],true);
            //渲染模版
			include $this->tpl('Hj_List_ListModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//更新列表信息
	public function listUpdateAction()
	{
	    //接收页面参数
        $bind=$this->request->from('list_id','list_name','company_id','list_type','detail');
        //页面名称不能为空
		if(trim($bind['list_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            $listExists = $this->oList->getListList(['company_id'=>$bind['company_id'],'list_name'=>$bind['list_name'],'exclude_id'=>$bind['list_id']],'list_id');
            if(count($listExists)>0)
            {
                $response = array('errno' => 2);
            }
            else
            {
                //数据打包
                $bind['detail'] = json_encode($bind['detail']);
                //修改页面
                $res = $this->oList->updateList($bind['list_id'],$bind);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
		}
		echo json_encode($response);
		return true;
	}

	//删除页面
	public function listDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteList");
		if($PermissionCheck['return'])
		{
			//页面ID
			$list_id = trim($this->request->list_id);
			//删除页面
			$this->oList->deleteList($list_id);
			//返回之前的页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //添加页面元素单个详情页面
    public function postAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateList");
        if($PermissionCheck['return'])
        {
            //列表ID
            $list_id = intval($this->request->list_id);
            //获取元素类型列表
            $listInfo = $this->oList->getList($list_id);
            $listInfo['detail'] = json_decode($listInfo['detail'],true);
            $postUrl = $this->config->api['root'].$this->config->api['list']['post'];
            //渲染模版
            include $this->tpl('Hj_List_Post');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}

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
        $this->oPosts = new Hj_Posts();
        $this->oActivity = new Hj_Activity();



    }
	//列表页面
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
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups);
            //获取企业列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name,detail");
            $boutiqueList = [];
			foreach($companyList as $key => $companyInfo)
            {
                if($company_id==0 || $company_id == $companyInfo['company_id'])
                {
                    $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
                    $boutiqueList = array_merge($boutiqueList,$companyInfo['detail']['boutique']??[]);
                }
            }
            //获取页面列表
            $listList = $this->oList->getListList(['company_id'=>$company_id,'list_type'=>$list_type,'id_not_in'=>$boutiqueList]);
			//获取列表类型列表
            $listTypeList = $this->oList->getListType();
            //初始化空的活动列表
            $activityList = [];
			//循环页面列表
			foreach($listList as $key => $listInfo)
            {
                //数据解包
                $listList[$key]['detail'] = json_decode($listInfo['detail'],true);
                $listList[$key]['company_name'] = ($listInfo['company_id']==0)?"无对应":($companyList[$listInfo['company_id']]['company_name']??"未知");
                $listList[$key]['list_type_name'] = ($listInfo['company_id']==0)?"无对应":($listTypeList[$listInfo['list_type']]['name']??"未知");
                $listList[$key]['posts_count'] = $this->oPosts->getPostCountByList($listInfo['list_id']);
                if($listInfo['activity_id']==0)
                {
                    $listList[$key]['activity_name'] = "未指定";
                }
                else
                {
                    if(!isset($activityList[$listInfo['activity_id']]))
                    {
                        $activityInfo = $this->oActivity->getActivity($listInfo['activity_id'],"activity_name,activity_id");
                        if(isset($activityInfo['activity_id']))
                        {
                            $activityList[$listInfo['activity_id']] = $activityInfo;
                        }
                    }
                    $listList[$key]['activity_name'] = $activityList[$listInfo['activity_id']]['activity_name']??"未指定";
                }

            }
			//渲染模版
			include $this->tpl('Hj_List_index');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //列表页面
    public function boutiqueAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //企业ID
            $company_id = intval($this->request->company_id??0);
            //列表分类
            $list_type = trim($this->request->list_type??0);
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups);
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name,detail");
            $boutiqueList = [];
            foreach($companyList as $key => $companyInfo)
            {
                if($company_id==0 || $company_id == $companyInfo['company_id'])
                {
                    $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
                    $boutiqueList = array_merge($boutiqueList,$companyInfo['detail']['boutique']??[]);
                }
            }
            //获取页面列表
            $listList = $this->oList->getListList(['company_id'=>$company_id,'list_type'=>$list_type,'id_in'=>$boutiqueList]);
            //获取列表类型列表
            $listTypeList = $this->oList->getListType();
            //初始化空的活动列表
            $activityList = [];
            //循环页面列表
            foreach($listList as $key => $listInfo)
            {
                //数据解包
                $listList[$key]['detail'] = json_decode($listInfo['detail'],true);
                $listList[$key]['company_name'] = ($listInfo['company_id']==0)?"无对应":($companyList[$listInfo['company_id']]['company_name']??"未知");
                $listList[$key]['list_type_name'] = ($listInfo['company_id']==0)?"无对应":($listTypeList[$listInfo['list_type']]['name']??"未知");
                $listList[$key]['posts_count'] = $this->oPosts->getPostCountByList($listInfo['list_id']);
                if($listInfo['activity_id']==0)
                {
                    $listList[$key]['activity_name'] = "未指定";
                }
                else
                {
                    if(!isset($activityList[$listInfo['activity_id']]))
                    {
                        $activityInfo = $this->oActivity->getActivity($listInfo['activity_id'],"activity_name,activity_id");
                        if(isset($activityInfo['activity_id']))
                        {
                            $activityList[$listInfo['activity_id']] = $activityInfo;
                        }
                    }
                    $listList[$key]['activity_name'] = $activityList[$listInfo['activity_id']]['activity_name']??"未指定";
                }

            }
            //渲染模版
            include $this->tpl('Hj_List_Boutique');
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
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups);
            //获取顶级页面列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
            //获取列表类型列表
            $listTypeList = $this->oList->getListType();
            //是否精品课
            $boutique = trim($this->request->boutique??0);
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
		$bind=$this->request->from('list_name','company_id','activity_id','list_type','detail','type','boutique');
		//页面名称不能为空
		if(trim($bind['list_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            //获取列表类型列表
            $listTypeList = $this->oList->getListType();
            if(($bind['detail']['limit']['pic'] + $bind['detail']['limit']['video'] +$bind['detail']['limit']['textarea'])==0)
            {
                $response = array('errno' => 3);
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
                    $bind['activity_id'] = $bind['activity_id']??0;
                    $bind['detail']['type'] = $bind['type'];
                    $boutique = $bind['boutique'];
                    unset($bind['boutique']);
                    unset($bind['type']);
                    //数据打包
                    $bind['detail'] = json_encode($bind['detail']);
                    //添加列表
                    $res = $this->oList->insertList($bind);
                    if($res)
                    {
                        if($boutique>0)
                        {
                            $companyInfo = $this->oCompany->getCompany($bind['company_id'],"company_id,detail");
                            $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
                            $boutiqueList = $companyInfo['detail']['boutique']??[];
                            $boutiqueList[$res] = $res;
                            $companyInfo['detail']['boutique'] = $boutiqueList;
                            $companyInfo['detail'] = json_encode($companyInfo['detail']);
                            $this->oCompany->updateCompany($companyInfo['company_id'],$companyInfo);
                            //刷新企业
                            Base_Common::refreshCache($this->config,"company",$companyInfo['company_id']);
                            //刷新列表
                            Base_Common::refreshCache($this->config,"list",$res);
                            $response = array('errno' => 0,'ac' => 'boutique');
                        }
                        else
                        {
                            //刷新列表
                            Base_Common::refreshCache($this->config,"list",$res);
                            $response = array('errno' => 0,'ac' => 'index');
                        }
                    }
                    else
                    {
                        if($boutique>0)
                        {
                            $response = array('errno' => 9,'ac'=>'boutique');

                        }
                        else
                        {
                            $response = array('errno' => 9,'ac'=>'index');
                        }
                    }
                }
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
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups);
            //获取企业列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
            //获取列表类型列表
            $listTypeList = $this->oList->getListType();
            //数据解包
            $listInfo['detail'] = json_decode($listInfo['detail'],true);
            //获取活动列表
            $activityList = (new Hj_Activity())->getActivityList(['company_id'=>$listInfo['company_id']],"activity_id,activity_name");
            //本企业下的列表
            $listList = $this->oList->getListList(['company_id'=>$listInfo['company_id']],'list_id,list_name');
            //提交后跳转
            $afterActionList = $this->oList->getAfterAction();
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
        $bind=$this->request->from('list_id','list_name','company_id','activity_id','list_type','detail','type');
        //页面名称不能为空
		if(trim($bind['list_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            //获取列表类型列表
            $listTypeList = $this->oList->getListType();
            if(($bind['detail']['limit']['pic'] + $bind['detail']['limit']['video'] +$bind['detail']['limit']['textarea'])==0)
            {
                $response = array('errno' => 3);
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
                    if($bind['detail']['connect'] == 0)
                    {
                        $bind['detail']['connect_name'] = "";
                    }
                    $bind['detail']['type'] = $bind['type'];
                    //获取列表信息
                    $listInfo = $this->oList->getList($bind['list_id'],'*');
                    $listInfo['detail'] = json_decode($listInfo['detail'],true);

                    //上传图片
                    $oUpload = new Base_Upload('upload_img');
                    $upload = $oUpload->upload('upload_img',$this->config->oss);
                    $oss_urls = array_column($upload->resultArr,'oss');
                    if(isset($oss_urls['0']) && $oss_urls['0']!="")
                    {
                        $bind['detail']['header_url'] = $oss_urls['0'];
                    }
                    else
                    {
                        $bind['detail']['header_url'] = $listInfo['detail']['header_url'];
                    }
                    unset($bind['type']);
                    //数据打包
                    $bind['detail'] = json_encode($bind['detail']);
                    //修改页面
                    $res = $this->oList->updateList($bind['list_id'],$bind);
                    $companyInfo = $this->oCompany->getCompany($listInfo['company_id'],"company_id,detail");
                    $companyInfo["detail"] = json_decode($companyInfo['detail'],true);
                    $boutiqueList = $companyInfo['detail']['boutique']??[];
                    if(!in_array($listInfo['list_id'],$boutiqueList))
                    {
                        $ac="index";
                        //刷新列表
                        Base_Common::refreshCache($this->config,"list",$res);

                    }
                    else
                    {
                        $ac="boutique";
                        //刷新企业
                        Base_Common::refreshCache($this->config,"company",$companyInfo['company_id']);
                        //刷新列表
                        Base_Common::refreshCache($this->config,"list",$res);
                    }
                    $response = $res ? array('errno' => 0,'ac'=>$ac) : array('errno' => 9,'ac'=>$ac);
                }
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
            $listInfo = $this->oList->getList($list_id,'list_id,company_id');
			//删除页面
			$this->oList->deleteList($list_id);
            //刷新企业
            Base_Common::refreshCache($this->config,"company",$listInfo['company_id']);
            //刷新列表
            Base_Common::refreshCache($this->config,"list",$list_id);
			//返回之前的页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //删除页面
    public function headerImgRemoveAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateList");
        if($PermissionCheck['return'])
        {
            //页面ID
            $list_id = trim($this->request->list_id);
            //获取列表信息
            $listInfo = $this->oList->getList($list_id,'list_id,detail');
            $listInfo['detail'] = json_decode($listInfo['detail'],true);
            unset($listInfo['detail']['header_url']);
            //数据打包
            $listInfo['detail'] = json_encode($listInfo['detail']);
            //修改页面
            $res = $this->oList->updateList($list_id,$listInfo);
            //刷新列表
            Base_Common::refreshCache($this->config,"list",$list_id);
            //返回之前的页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //提交文章
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
            //获取列表类型列表
            $listTypeList = $this->oList->getListType();
            $typeInfo  = $listTypeList[$listInfo['list_type']];
            $max_files = 10;
            $postUrl = $this->config->apiUrl.$this->config->api['api']['post'];
            $token = (new Hj_UserInfo())->getTokenForManager($this->manager->id);
            //渲染模版
            include $this->tpl('Hj_List_Post');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //列表页面
    public function listAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //列表ID
            $list_id = intval($this->request->list_id??0);
            //分页参数
            $params['Page'] = abs(intval($this->request->Page??1));
            $params['PageSize'] = 20;
            //获取列表时需要获得记录总数
            $params['getCount'] = 1;
            //获取列表信息
            $listInfo = $this->oList->getList($list_id);
            $listInfo['detail'] = json_decode($listInfo['detail'],true);
            $companyInfo = $this->oCompany->getCompany($listInfo['company_id'],"company_id,detail");
            $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
            $boutiqueList = $companyInfo['detail']['boutique']??[];
            if(!in_array($list_id,$boutiqueList))
            {
                $return_url = Base_Common::getUrl('',$this->ctl,'index',['company_id'=>$listInfo['company_id']]);

            }
            else
            {
                $return_url = Base_Common::getUrl('',$this->ctl,'boutique',['company_id'=>$listInfo['company_id']]);
            }
            $params['list_id'] = $listInfo['list_id'];
            //获取文章列表
            $list = $this->oPosts->getPostsList($params);
            $userList = [];
            //循环页面列表
            foreach($list['postsList'] as $key => $listDetail)
            {
                //数据解包
                if(!isset($userList[$listDetail['user_id']]))
                {
                    $userInfo = (new Hj_UserInfo())->getUser($listDetail['user_id'],'user_id,true_name');
                    if(isset($userInfo['user_id']))
                    {
                        $userList[$listDetail['user_id']] = $userInfo;
                        $userList[$listDetail['user_id']] = $userInfo;
                    }
                }
                $list['postsList'][$key]['user_name'] = $userList[$listDetail['user_id']]['true_name']??"未知用户";
                $list['postsList'][$key]['visible_name'] = $listDetail['visible']==1?"可见":"隐藏";
            }
            $display_url = $this->config->apiUrl.$this->config->api['api']['display'];
            $page_url = Base_Common::getUrl('',$this->ctl,'list',$params)."&Page=~page~";
            $page_content =  base_common::multi($list['postsCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //渲染模版
            include $this->tpl('Hj_List_List');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //列表页面
    public function postsDetailAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            $token = (new Hj_UserInfo())->getTokenForManager($this->manager->id);
            //文章ID
            $post_id = intval($this->request->post_id??0);
            //获取文章详情
            $postsInfo = $this->oPosts->getPosts($post_id);
            //数据解包
            $postsInfo['source'] = json_decode($postsInfo['source'],true);
            //获取列表数据
            $listInfo = $this->oList->getList($postsInfo['list_id'],'list_id,list_type,detail');
            //数据解包
            $listInfo['detail'] = json_decode($listInfo['detail'],true);
            $max_files = 10;
            //获取列表类型列表
            $listTypeList = $this->oList->getListType();
            $typeInfo  = $listTypeList[$listInfo['list_type']];
            $postUrl = $this->config->apiUrl.$this->config->api['api']['post'];
            $sourceRemoveUrl = $this->config->apiUrl.$this->config->api['api']['source_remove'];
            $video_suffix = "?x-oss-process=video/snapshot,t_1000,f_jpg,w_300,h_300,m_fast";
            //渲染模版
            include $this->tpl('Hj_List_PostsDetail');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}

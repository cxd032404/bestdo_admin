<?php
/**
 * 活动管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_ListController extends AbstractController
{
	/**活动:Acitvity
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
	//活动配置列表活动
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
			//获取活动列表
			$listList = $this->oList->getActivityList(['company_id'=>$company_id]);
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //获取企业列表
            $listTypeList = $this->oList->getListType();
			//循环活动列表
			foreach($listList as $key => $listInfo)
            {
                //数据解包
                $listList[$key]['comment'] = json_decode($listInfo['comment'],true);
				$listList[$key]['company_name'] = ($listInfo['company_id']==0)?"无对应":($companyList[$listInfo['company_id']]['company_name']??"未知");
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
	//添加活动类型填写配置活动
	public function activityAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addActivity");
		if($PermissionCheck['return'])
		{
			//获取顶级活动列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
			//渲染模版
			include $this->tpl('Hj_List_ActivityAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//添加新活动
	public function activityInsertAction()
	{
		//检查权限
		$bind=$this->request->from('activity_name','company_id','activity_sign','start_time','end_time','apply_start_time','apply_end_time');
		//活动名称不能为空
		if(trim($bind['activity_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            if(trim($bind['activity_sign'])=="")
            {
                $response = array('errno' => 3);
            }
            else
            {
                $listExists = $this->oList->getActivityList(['company_id'=>$bind['company_id'],'activity_sign'=>$bind['activity_sign']],'activity_id,activity_sign');
                if(count($listExists)>0)
                {
                    $response = array('errno' => 4);
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
                        $bind['comment'] = [];
                        //数据打包
                        $bind['comment'] = json_encode($bind['comment']);
                        //添加活动
                        $res = $this->oList->insertActivity($bind);
                        $response = $res ? array('errno' => 0) : array('errno' => 9);
                    }
                }
            }
		}
		echo json_encode($response);
		return true;
	}

	//修改活动信息活动
	public function activityModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateActivity");
		if($PermissionCheck['return'])
		{
			//活动ID
			$list_id= intval($this->request->activity_id);
			//获取活动信息
			$listInfo = $this->oList->getActivity($list_id,'*');
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //渲染模版
			include $this->tpl('Hj_List_ActivityModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//更新活动信息
	public function activityUpdateAction()
	{
	    //接收活动参数
        $bind=$this->request->from('activity_id','activity_name','company_id','activity_sign','start_time','end_time','apply_start_time','apply_end_time');
        //活动名称不能为空
		if(trim($bind['activity_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            if(trim($bind['activity_sign'])=="")
            {
                $response = array('errno' => 3);
            }
            else
            {
                $listExists = $this->oList->getActivityList(['company_id'=>$bind['company_id'],'activity_sign'=>$bind['activity_sign'],'exclude_id'=>$bind['activity_id']],'activity_id,activity_sign');
                if(count($listExists)>0)
                {
                    $response = array('errno' => 4);
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
                    $bind['comment'] = json_encode([]);
                    //修改活动
                    $res = $this->oList->updateActivity($bind['activity_id'],$bind);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
            }
		}
		echo json_encode($response);
		return true;
	}

	//删除活动
	public function activityDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteActivity");
		if($PermissionCheck['return'])
		{
			//活动ID
			$list_id = trim($this->request->activity_id);
			//删除活动
			$this->oList->deleteActivity($list_id);
			//返回之前的活动
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//修改活动详情（元素列表）活动
	public function activityDetailAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updatePage");
		if($PermissionCheck['return'])
		{
			//活动ID
			$list_id= intval($this->request->activity_id);
			//获取活动信息
			$listInfo = $this->oList->getPage($list_id,'*');
			//获取元素信息
			$listElementList = $this->oListElement->getElementList(['activity_id'=>$list_id]);
			//获取元素类型列表
			$elementTypeList = $this->oElementType->getElementTypeList();
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            foreach ($listElementList as $elementSign => $elementInfo)
            {
            	$listElementList[$elementSign]['element_type_name'] = $elementTypeList[$elementInfo['element_type']]['element_type_name']??"未知类型";
            }
            //渲染模版
			include $this->tpl('Hj_List_ActivityDetail');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加活动元素信息活动
	public function activityElementAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updatePage");
		if($PermissionCheck['return'])
		{
			//活动ID
			$list_id= intval($this->request->activity_id);
			//获取元素类型列表
			$elementTypeList = $this->oElementType->getElementTypeList([],"element_type,element_type_name");
            //渲染模版
			include $this->tpl('Hj_List_ActivityElementAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加新活动元素
	public function activityElementInsertAction()
	{
		//检查权限
		$bind=$this->request->from('element_name','element_type','activity_id','element_sign');
		//活动元素名称不能为空
		if(trim($bind['element_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			//活动元素名称不能为空
			if(trim($bind['element_sign'])=="")
			{
				$response = array('errno' => 2);
			}
			else
			{
                $listElementExists = $this->oListElement->getElementList(['activity_id'=>$bind['activity_id'],'element_sign'=>$bind['element_sign']],'element_id,element_sign');
                if(count($listElementExists))
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    $bind['detail'] = json_encode([]);
                    //添加活动元素
                    $res = $this->oListElement->insertPageElement($bind);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
			}
		}
		echo json_encode($response);
		return true;
	}
	//修改活动元素信息活动
	public function activityElementModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updatePage");
		if($PermissionCheck['return'])
		{
			//活动ID
			$element_id= intval($this->request->element_id);
			//获取元素类型列表
			$elementInfo = $this->oListElement->getPageElement($element_id);
			//获取元素类型列表
			$elementTypeList = $this->oElementType->getElementTypeList([],"element_type,element_type_name");
            //渲染模版
			include $this->tpl('Hj_List_ActivityElementModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//修改活动元素
	public function activityElementUpdateAction()
	{
		//检查权限
		$bind=$this->request->from('element_id','element_name','element_type','element_sign');
		//活动元素名称不能为空
		if(trim($bind['element_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			//活动元素名称不能为空
			if(trim($bind['element_sign'])=="")
			{
				$response = array('errno' => 2);
			}
			else
			{
                $elementInfo = $this->oListElement->getPageElement($bind['element_id'],"element_id,activity_id");
			    $listElementExists = $this->oListElement->getElementList(['activity_id'=>$elementInfo['activity_id'],'element_sign'=>$bind['element_sign'],'exclude_id'=>$bind['element_id']],'element_id,element_sign');
                if(count($listElementExists))
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    $bind['detail'] = json_encode([]);
                    //添加活动元素
                    $res = $this->oListElement->updatePageElement($bind['element_id'],$bind);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
			}

		}
		echo json_encode($response);
		return true;
	}
	//修改活动元素信息活动
	public function activityElementDetailAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updatePage");
		if($PermissionCheck['return'])
		{
		    //元素ID
			$element_id= intval($this->request->element_id);
			//获取元素类型列表
			$elementInfo = $this->oListElement->getPageElement($element_id);
			$elementInfo['detail'] = json_decode($elementInfo['detail'],true);
			$t = [];
			if($elementInfo['element_type'] == "slideNavi")
            {
                foreach($elementInfo['detail']['jump_urls'] as $k => $d)
                {
                    $t[] = $k."|".$d;
                }
                $t = implode(',&#10;',$t);
            }
			$elementTypeInfo = $this->oElementType->getElementType($elementInfo['element_type']);
			//渲染模版
			include $this->tpl('Hj_List_ActivityElement_'.$elementInfo['element_type']);
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//修改活动元素
	public function activityElementDetailUpdateAction()
	{
		//元素ID
		$element_id = intval($this->request->element_id);
		$detail = $this->request->detail;
	    $elementDetail = $this->oListElement->getPageElement($element_id,"detail,element_type");
	    $elementDetail['detail'] = json_decode($elementDetail['detail'],true);
	    if(in_array($elementDetail['element_type'],['singlePic','backgroundPic']))
	    {
	        //上传图片
	   		$oUpload = new Base_Upload('upload_img');
	        $upload = $oUpload->upload('upload_img',$this->config->oss);
	        $oss_urls = array_column($upload->resultArr,'oss');
	        //如果以前没上传过且这次也没有成功上传
	        if((!isset($elementDetail['detail']['img_url']) || $elementDetail['detail']['img_url']=="") && (!isset($oss_urls['0']) || $oss_urls['0'] == ""))
	        {
				$response = array('errno' => 2);
	        }
	        else
	        {
	        	//这次传成功了就用这次，否则维持
	        	$elementDetail['detail']['img_url'] = (isset($oss_urls['0']) && $oss_urls['0']!="")?($oss_urls['0']):($elementDetail['detail']['img_url']);
	        	//保存跳转链接
	        	$elementDetail['detail']['img_jump_url'] = $detail['img_jump_url'];
	        }
	    }
	    elseif(in_array($elementDetail['element_type'],['slideNavi']))
        {
            //上传图片
            $oUpload = new Base_Upload('upload_img');
            $upload = $oUpload->upload('upload_img',$this->config->oss);
            $oss_urls = array_column($upload->resultArr,'oss');
            //如果以前没上传过且这次也没有成功上传
            if((!isset($elementDetail['detail']['img_url']) || $elementDetail['detail']['img_url']=="") && (!isset($oss_urls['0']) || $oss_urls['0'] == ""))
            {
                $response = array('errno' => 2);
            }
            else
            {
                //这次传成功了就用这次，否则维持
                $elementDetail['detail']['img_url'] = (isset($oss_urls['0']) && $oss_urls['0']!="")?($oss_urls['0']):($elementDetail['detail']['img_url']);
            }
            //这次传成功了就用这次，否则维持
            $elementDetail['detail']['selected_img_url'] = (isset($oss_urls['1']) && $oss_urls['1']!="")?($oss_urls['1']):($elementDetail['detail']['selected_img_url']);
            $t = explode(',',$detail['jump_urls']);
            $a = [];
            foreach($t as $key => $value)
            {
                $t2 = explode("|",$value);
                if(trim($t2[0]!=""))
                {
                    $a[trim($t2[0])] = trim($t2[1]);
                }
            }
            $elementDetail['detail']['jump_urls'] = $a;
        }
        elseif(in_array($elementDetail['element_type'],['richText']))
        {
            $text = $this->request->text;
            $elementDetail['detail']['text'] = $text;
        }
	    if(!isset($response))
	    {
	        $elementDetail['detail'] = json_encode($elementDetail['detail']);
		    $res = $this->oListElement->updatePageElement($element_id,$elementDetail);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
	    }
		echo json_encode($response);
		return true;
	}
	//删除活动元素
	public function activityElementDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updatePage");
		if($PermissionCheck['return'])
		{
			//活动元素ID
			$element_id = trim($this->request->element_id);
			//删除活动元素
			$this->oListElement->deletePageElement($element_id);
			//返回之前的活动
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //添加活动元素单个详情活动
    public function activityElementSingleDetailAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updatePage");
        if($PermissionCheck['return'])
        {
            //元素ID
            $element_id= intval($this->request->element_id);
            //获取元素类型列表
            $elementInfo = $this->oListElement->getPageElement($element_id,"element_type,element_id");
            //渲染模版
            include $this->tpl('Hj_List_ActivityElementDetail_Add_'.$elementInfo['element_type']);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加活动单个元素详情
    public function activityElementSingleDetailInsertAction()
    {
        //元素ID
        $element_id = intval($this->request->element_id);
        $detail = $this->request->detail;
        $elementDetail = $this->oListElement->getPageElement($element_id,"detail,element_type");
        $elementDetail['detail'] = json_decode($elementDetail['detail'],true);
        if(in_array($elementDetail['element_type'],['slidePic']))
        {
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
                $elementDetail['detail'][] = ['img_url'=>$oss_urls['0'],'img_jump_url'=>$detail['img_jump_url']];
                $elementDetail['detail'] = json_encode($elementDetail['detail']);
                $res = $this->oListElement->updatePageElement($element_id,$elementDetail);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
        }
        echo json_encode($response);
        return true;
    }
    //修改活动单个元素详情活动
    public function activityElementSingleDetailModifyAction()
    {
        //元素ID
        $element_id = intval($this->request->element_id);
        $pos = intval($this->request->pos??0);
        $elementDetail = $this->oListElement->getPageElement($element_id,"detail,element_type");
        $elementDetail['detail'] = json_decode($elementDetail['detail'],true);
        if(in_array($elementDetail['element_type'],['slidePic']))
        {
            $elementDetailInfo = $elementDetail['detail'][$pos];
            //渲染模版
            include $this->tpl('Hj_List_ActivityElementDetail_Modify_'.$elementDetail['element_type']);
        }
    }
    //删除活动单个元素详情
    public function activityElementSingleDetailDeleteAction()
    {
        //元素ID
        $element_id = intval($this->request->element_id);
        $pos = intval($this->request->pos??0);
        $elementDetail = $this->oListElement->getPageElement($element_id,"detail,element_type");
        $elementDetail['detail'] = json_decode($elementDetail['detail'],true);
        if(in_array($elementDetail['element_type'],['slidePic']))
        {
            if(isset($elementDetail['detail'][$pos]))
            {
                unset($elementDetail['detail'][$pos]);
                $elementDetail['detail'] = array_values($elementDetail['detail']);
                $elementDetail['detail'] = json_encode($elementDetail['detail']);
                $res = $this->oListElement->updatePageElement($element_id,$elementDetail);
            }
        }
        $this->response->goBack();
    }
    //添加活动元素详情
    public function activityElementSingleDetailUpdateAction()
    {
        //元素ID
        $element_id = intval($this->request->element_id);
        $detail = $this->request->detail;
        $elementDetail = $this->oListElement->getPageElement($element_id,"detail,element_type");
        $elementDetail['detail'] = json_decode($elementDetail['detail'],true);
        if(in_array($elementDetail['element_type'],['slidePic']))
        {
            $pos = intval($this->request->pos??0);
            //上传图片
            $oUpload = new Base_Upload('upload_img');
            $upload = $oUpload->upload('upload_img',$this->config->oss);
            $oss_urls = array_column($upload->resultArr,'oss');
            //如果以前没上传过且这次也没有成功上传
            if((!isset($elementDetail['detail'][$pos]['img_url']) || $elementDetail['detail'][$pos]['img_url']=="") && (!isset($oss_urls['0']) || $oss_urls['0'] == ""))
            {
                $response = array('errno' => 2);
            }
            else
            {
                //这次传成功了就用这次，否则维持
                $elementDetail['detail'][$pos]['img_url'] = (isset($oss_urls['0']) && $oss_urls['0']!="")?($oss_urls['0']):($elementDetail['detail'][$pos]['img_url']);
                //保存跳转链接
                $elementDetail['detail'][$pos]['img_jump_url'] = $detail['img_jump_url'];
                $elementDetail['detail'] = json_encode($elementDetail['detail']);
                $res = $this->oListElement->updatePageElement($element_id,$elementDetail);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
        }
        echo json_encode($response);
        return true;
    }
}

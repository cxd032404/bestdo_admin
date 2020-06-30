<?php
/**
 * 页面管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_PageController extends AbstractController
{
    /**页面:Page
     * @var string
     */
    protected $sign = '?ctl=hj/page';
    protected $ctl = 'hj/page';

    /**
     * game对象
     * @var object
     */
    protected $oPage;
    protected $oCompany;
    protected $oPageElement;

    /**
     * 初始化
     * (non-PHPdoc)
     * @see AbstractController#init()
     */
    public function init()
    {
        parent::init();
        $this->oPage = new Hj_Page();
        $this->oCompany = new Hj_Company();
        $this->oPageElement = new Hj_PageElement();
        $this->oElementType = new Hj_ElementType();

    }
    //页面配置列表页面
    public function indexAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //页面ID
            $company_id = intval($this->request->company_id??0);
            //获取页面列表
            $pageList = $this->oPage->getPageList(['company_id'=>$company_id]);
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //循环页面列表
            foreach($pageList as $key => $pageInfo)
            {
                //数据解包
                $pageList[$key]['company_name'] = ($pageInfo['company_id']==0)?"无对应":($companyList[$pageInfo['company_id']]['company_name']??"未知");
                $pageList[$key]['element_count'] = $this->oPageElement->getElementCount(['page_id'=>$pageInfo['page_id']]);
                $pageList[$key]['detail'] = json_decode($pageInfo['detail'],true);
                $pageList[$key]['page_url'] = $this->config->siteUrl."/".$pageInfo['page_sign'];
                $pageList[$key]['test_url'] = $this->config->apiUrl.$this->config->api['api']['get_page']."".$pageInfo['company_id']."/".$pageInfo['page_sign'];
                $pageList[$key]['page_params'] = $this->oPage->processDefaultParams($pageList[$key]['detail']['params']??[]);
            }
            //渲染模版
            include $this->tpl('Hj_Page_PageList');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加页面类型填写配置页面
    public function pageAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("addPage");
        if($PermissionCheck['return'])
        {
            //获取顶级页面列表
            $companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //渲染模版
            include $this->tpl('Hj_Page_PageAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }

    //添加新页面
    public function pageInsertAction()
    {
        //检查权限
        $bind=$this->request->from('page_name','page_url','company_id','page_sign','need_login','detail');
        //页面名称不能为空
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
                if(trim($bind['page_sign'])=="")
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    $pageExists = $this->oPage->getPageList(['company_id'=>$bind['company_id'],'page_sign'=>$bind['page_sign']],'page_id,page_sign');
                    if(count($pageExists)>0)
                    {
                        $response = array('errno' => 4);
                    }
                    else
                    {
                        //处理页面必备参数
                        $bind['detail']['params'] = $this->oPage->unpackPageParams($bind['detail']['params']);
                        //数据打包
                        $bind['detail'] = json_encode($bind['detail']);
                        //添加页面
                        $res = $this->oPage->insertPage($bind);
                        Base_Common::refreshCache($this->config,"page",$res);
                        $response = $res ? array('errno' => 0) : array('errno' => 9);
                    }
                }
            }

        }
        echo json_encode($response);
        return true;
    }

    //修改页面信息页面
    public function pageModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updatePage");
        if($PermissionCheck['return'])
        {
            //页面ID
            $page_id= intval($this->request->page_id);
            //获取页面信息
            $pageInfo = $this->oPage->getPage($page_id,'*');
            //数据解包
            $pageInfo['detail'] = json_decode($pageInfo['detail'],true);
            $pageInfo['detail']['params'] = $this->oPage->packPageParams($pageInfo['detail']['params']);
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //渲染模版
            include $this->tpl('Hj_Page_PageModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }

    //更新页面信息
    public function pageUpdateAction()
    {
        //接收页面参数
        $bind=$this->request->from('page_id','page_name','company_id','page_url','need_login','page_sign','detail');
        //页面名称不能为空
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
                if(trim($bind['page_sign'])=="")
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    $pageExists = $this->oPage->getPageList(['company_id'=>$bind['company_id'],'page_sign'=>$bind['page_sign'],'exclude_id'=>$bind['page_id']],'page_id,page_sign');
                    if(count($pageExists)>0)
                    {
                        $response = array('errno' => 4);
                    }
                    else
                    {
                        //处理页面必备参数
                        $bind['detail']['params'] = $this->oPage->unpackPageParams($bind['detail']['params']);
                        //数据打包
                        $bind['detail'] = json_encode($bind['detail']);
                        $currentPageInfo = $this->oPage->getPage($bind['page_id'],"page_id,company_id");
                        //修改页面
                        $res = $this->oPage->updatePage($bind['page_id'],$bind);
                        Base_Common::refreshCache($this->config,"page",$bind['page_id']);
                        $response = $res ? array('errno' => 0) : array('errno' => 9,'company_id'=>$currentPageInfo['company_id']);
                    }
                }
            }
        }
        echo json_encode($response);
        return true;
    }

    //删除页面
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

    //修改页面详情（元素列表）页面
    public function pageDetailAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updatePage");
        if($PermissionCheck['return'])
        {
            //页面ID
            $page_id= intval($this->request->page_id);
            //获取页面信息
            $pageInfo = $this->oPage->getPage($page_id,'*');
            //获取元素信息
            $pageElementList = $this->oPageElement->getElementList(['page_id'=>$page_id]);
            //获取元素类型列表
            $elementTypeList = $this->oElementType->getElementTypeList();
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            foreach ($pageElementList as $elementSign => $elementInfo)
            {
                $pageElementList[$elementSign]['element_type_name'] = $elementTypeList[$elementInfo['element_type']]['element_type_name']??"未知类型";
                $pageElementList[$elementSign]['editable'] = $elementTypeList[$elementInfo['element_type']]['editable'];
            }
            //渲染模版
            include $this->tpl('Hj_Page_PageDetail');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加页面元素信息页面
    public function pageElementAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updatePage");
        if($PermissionCheck['return'])
        {
            //页面ID
            $page_id= intval($this->request->page_id);
            //获取元素类型列表
            $elementTypeList = $this->oElementType->getElementTypeList([],"element_type,element_type_name");
            //渲染模版
            include $this->tpl('Hj_Page_PageElementAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加新页面元素
    public function pageElementInsertAction()
    {
        //检查权限
        $bind=$this->request->from('element_name','element_type','page_id','element_sign');
        //页面元素名称不能为空
        if(trim($bind['element_name'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            //页面元素名称不能为空
            if(trim($bind['element_sign'])=="")
            {
                $response = array('errno' => 2);
            }
            else
            {
                $pageElementExists = $this->oPageElement->getElementList(['page_id'=>$bind['page_id'],'element_sign'=>$bind['element_sign']],'element_id,element_sign');
                if(count($pageElementExists))
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    $bind['detail'] = json_encode([]);
                    //添加页面元素
                    $res = $this->oPageElement->insertPageElement($bind);
                    Base_Common::refreshCache($this->config,"page",$bind['page_id']);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
            }
        }
        echo json_encode($response);
        return true;
    }
    //修改页面元素信息页面
    public function pageElementModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updatePage");
        if($PermissionCheck['return'])
        {
            //页面ID
            $element_id= intval($this->request->element_id);
            //获取元素类型列表
            $elementInfo = $this->oPageElement->getPageElement($element_id);
            //获取元素类型列表
            $elementTypeList = $this->oElementType->getElementTypeList([],"element_type,element_type_name");
            //渲染模版
            include $this->tpl('Hj_Page_PageElementModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改页面元素
    public function pageElementUpdateAction()
    {
        //检查权限
        $bind=$this->request->from('element_id','element_name','element_type','element_sign');
        //页面元素名称不能为空
        if(trim($bind['element_name'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            //页面元素名称不能为空
            if(trim($bind['element_sign'])=="")
            {
                $response = array('errno' => 2);
            }
            else
            {
                $elementInfo = $this->oPageElement->getPageElement($bind['element_id'],"element_id,page_id");
                $pageElementExists = $this->oPageElement->getElementList(['page_id'=>$elementInfo['page_id'],'element_sign'=>$bind['element_sign'],'exclude_id'=>$bind['element_id']],'element_id,element_sign');
                if(count($pageElementExists))
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    //添加页面元素
                    $res = $this->oPageElement->updatePageElement($bind['element_id'],$bind);
                    Base_Common::refreshCache($this->config,"page",$elementInfo['page_id']);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
            }

        }
        echo json_encode($response);
        return true;
    }
    //修改页面元素信息页面
    public function pageElementDetailAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updatePage");
        if($PermissionCheck['return'])
        {
            //元素ID
            $element_id= intval($this->request->element_id);
            //获取元素类型列表
            $elementInfo = $this->oPageElement->getPageElement($element_id);
            $elementInfo['detail'] = json_decode($elementInfo['detail'],true);
            //获取页面信息
            $pageInfo = $this->oPage->getPage($elementInfo['page_id'],"page_id,company_id");
            if($elementInfo['element_type'] == "slideNavi")
            {
                $activityList = (new Hj_Activity())->getActivityList(['company_id'=>$pageInfo['company_id']],"activity_id");
                $voteList = (new Hj_Vote())->getVoteList(['activity_ids'=>array_column($activityList,'activity_id')],'vote_id,vote_name');
                $t = [];
                foreach($elementInfo['detail']['jump_urls'] as $k => $d)
                {
                    $t[] = $k."|".$d;
                }
                $t = implode(',&#10;',$t);
            }
            elseif(in_array($elementInfo['element_type'] ,[ "list","rankByKudos","post"]))
            {
                //获取列表列表
                $listList = (new Hj_List())->getListList(['company_id'=>$pageInfo['company_id']],"list_id,list_name");
            }
            elseif(in_array($elementInfo['element_type'] ,[ "clubInfo","clubApply","clubPermission","clubMemberList","clubMemberLog"]))
            {
                //获取俱乐部列表
                $clubList = (new Hj_Club())->getClubList(['company_id'=>$pageInfo['company_id']],"club_id,club_name");
            }
            elseif(in_array($elementInfo['element_type'] ,[ "activityApply","activityInfo","activityUpdate","managedActivityList","activityMemberList"]))
            {
                //获取活动列表
                $activityList = (new Hj_Activity())->getActivityList(['company_id'=>$pageInfo['company_id']],"activity_id,activity_name");
            }
            $elementTypeInfo = $this->oElementType->getElementType($elementInfo['element_type']);
            //渲染模版
            include $this->tpl('Hj_Page_PageElement_'.$elementInfo['element_type']);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改页面元素
    public function pageElementDetailUpdateAction()
    {
        //元素ID
        $element_id = intval($this->request->element_id);
        $detail = $this->request->detail;
        $elementDetail = $this->oPageElement->getPageElement($element_id,"page_id,detail,element_type");
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
            if($detail['source_from'] == "from_vote")
            {
                $elementDetail['detail']['type'] = $detail['type'];
                $elementDetail['detail']['source_from'] = "from_vote";
                $elementDetail['detail']['vote_id'] = $detail['vote_id'];
                unset($elementDetail['detail']['jump_urls']);
            }
            else
            {
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
                $elementDetail['detail']['type'] = $detail['type'];
                $elementDetail['detail']['source_from'] = "from_text";
                $elementDetail['detail']['jump_urls'] = $a;
                unset($elementDetail['detail']['vote_id']);
            }
        }
        elseif(in_array($elementDetail['element_type'],['richText']))
        {
            $text = $this->request->text;
            $elementDetail['detail']['text'] = $text;
        }
        elseif(in_array($elementDetail['element_type'],['activityApply']))
        {
            if($detail['id_from']=="from_params")
            {
                $elementDetail['detail']['from_params'] = $detail['from_params'];
                unset($elementDetail['detail']['activity_id']);
            }
            elseif($detail['id_from']=="from_id")
            {
                $elementDetail['detail']['activity_id'] = $detail['activity_id'];
                unset($elementDetail['detail']['from_params']);
            }
            //上传图片
            $oUpload = new Base_Upload('upload_img');
            $upload = $oUpload->upload('upload_img',$this->config->oss);
            $oss_urls = array_column($upload->resultArr,'oss');
            //如果以前没上传过且这次也没有成功上传
            if((!isset($elementDetail['detail']['apply_img_url']) || $elementDetail['detail']['apply_img_url']=="") && (!isset($oss_urls['0']) || $oss_urls['0'] == ""))
            {
                $response = array('errno' => 2);
            }
            else
            {
                //这次传成功了就用这次，否则维持
                $elementDetail['detail']['apply_img_url'] = (isset($oss_urls['0']) && $oss_urls['0']!="")?($oss_urls['0']):($elementDetail['detail']['apply_img_url']);
            }
            //这次传成功了就用这次，否则维持
            $elementDetail['detail']['applied_img_url'] = (isset($oss_urls['1']) && $oss_urls['1']!="")?($oss_urls['1']):($elementDetail['detail']['applied_img_url']);
            $elementDetail['detail']['applied_url'] = $detail['applied_url']??0;
            $elementDetail['detail']['auto'] = $detail['auto']??0;
        }
        elseif(in_array($elementDetail['element_type'],['list','rankByKudos','post']))
        {
            if($detail['id_from']=="from_params")
            {
                $elementDetail['detail']['from_params'] = $detail['from_params'];
                unset($elementDetail['detail']['list_id']);
            }
            elseif($detail['id_from']=="from_id")
            {
                $elementDetail['detail']['list_id'] = $detail['list_id'];
                unset($elementDetail['detail']['from_params']);
            }
        }
        elseif(in_array($elementDetail['element_type'],['clubInfo',"clubApply",'clubPermission','clubMemberList','clubMemberLog']))
        {
            if($detail['id_from']=="from_params")
            {
                $elementDetail['detail']['from_params'] = $detail['from_params'];
                unset($elementDetail['detail']['club_id']);
            }
            elseif($detail['id_from']=="from_id")
            {
                $elementDetail['detail']['club_id'] = $detail['club_id'];
                unset($elementDetail['detail']['from_params']);
            }
        }
        elseif(in_array($elementDetail['element_type'],['activityInfo','activityUpdate',"managedActivityList","activityMemberList"]))
        {
            if($detail['id_from']=="from_params")
            {
                $elementDetail['detail']['from_params'] = $detail['from_params'];
                unset($elementDetail['detail']['activity_id']);
            }
            elseif($detail['id_from']=="from_id")
            {
                $elementDetail['detail']['activity_id'] = $detail['activity_id'];
                unset($elementDetail['detail']['from_params']);
            }
        }
        elseif(in_array($elementDetail['element_type'],['postsDetail']))
        {
            $elementDetail['detail']['from_params'] = $detail['from_params'];
        }
        if(!isset($response))
        {
            $elementDetail['detail'] = json_encode($elementDetail['detail']);
            $res = $this->oPageElement->updatePageElement($element_id,$elementDetail);
            Base_Common::refreshCache($this->config,"page",$elementDetail['page_id']);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //删除页面元素
    public function pageElementDeleteAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updatePage");
        if($PermissionCheck['return'])
        {
            //页面元素ID
            $element_id = trim($this->request->element_id);
            $elementDetail = $this->oPageElement->getPageElement($element_id,"page_id");
            //删除页面元素
            $this->oPageElement->deletePageElement($element_id);
            Base_Common::refreshCache($this->config,"page",$elementDetail['page_id']);
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
    public function pageElementSingleDetailAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updatePage");
        if($PermissionCheck['return'])
        {
            //元素ID
            $element_id= intval($this->request->element_id);
            //获取元素类型列表
            $elementInfo = $this->oPageElement->getPageElement($element_id,"element_type,element_id");
            //渲染模版
            include $this->tpl('Hj_Page_PageElementDetail_Add_'.$elementInfo['element_type']);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加页面单个元素详情
    public function pageElementSingleDetailInsertAction()
    {
        //元素ID
        $element_id = intval($this->request->element_id);
        $detail = $this->request->detail;
        $elementDetail = $this->oPageElement->getPageElement($element_id,"page_id,detail,element_type");
        $elementDetail['detail'] = json_decode($elementDetail['detail'],true);
        if(in_array($elementDetail['element_type'],['slidePic','picList']))
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
                $elementDetail['detail'][] = ['img_url'=>$oss_urls['0'],'img_jump_url'=>$detail['img_jump_url'],'text'=>trim($detail['text']??""),'title'=>trim($detail['title']??"")];
                $elementDetail['detail'] = json_encode($elementDetail['detail']);
                $res = $this->oPageElement->updatePageElement($element_id,$elementDetail);
                Base_Common::refreshCache($this->config,"page",$elementDetail['page_id']);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
        }
        echo json_encode($response);
        return true;
    }
    //修改页面单个元素详情页面
    public function pageElementSingleDetailModifyAction()
    {
        //元素ID
        $element_id = intval($this->request->element_id);
        $pos = intval($this->request->pos??0);
        $elementDetail = $this->oPageElement->getPageElement($element_id,"detail,element_type");
        $elementDetail['detail'] = json_decode($elementDetail['detail'],true);
        if(in_array($elementDetail['element_type'],['slidePic','picList']))
        {
            $elementDetailInfo = $elementDetail['detail'][$pos];
            //渲染模版
            include $this->tpl('Hj_Page_PageElementDetail_Modify_'.$elementDetail['element_type']);
        }
    }
    //删除页面单个元素详情
    public function pageElementSingleDetailDeleteAction()
    {
        //元素ID
        $element_id = intval($this->request->element_id);
        $pos = intval($this->request->pos??0);
        $elementDetail = $this->oPageElement->getPageElement($element_id,"page_id,detail,element_type");
        $elementDetail['detail'] = json_decode($elementDetail['detail'],true);
        if(in_array($elementDetail['element_type'],['slidePic','picList']))
        {
            if(isset($elementDetail['detail'][$pos]))
            {
                unset($elementDetail['detail'][$pos]);
                $elementDetail['detail'] = array_values($elementDetail['detail']);
                $elementDetail['detail'] = json_encode($elementDetail['detail']);
                $res = $this->oPageElement->updatePageElement($element_id,$elementDetail);
                Base_Common::refreshCache($this->config,"page",$elementDetail['page_id']);
            }
        }
        $this->response->goBack();
    }
    //更新页面元素详情
    public function pageElementSingleDetailUpdateAction()
    {
        //元素ID
        $element_id = intval($this->request->element_id);
        $detail = $this->request->detail;
        $elementDetail = $this->oPageElement->getPageElement($element_id,"page_id,detail,element_type");
        $elementDetail['detail'] = json_decode($elementDetail['detail'],true);
        if(in_array($elementDetail['element_type'],['slidePic','picList']))
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
                $elementDetail['detail'][$pos]['img_jump_url'] = trim($detail['img_jump_url']??"");
                $elementDetail['detail'][$pos]['text'] = trim($detail['text']??"");
                $elementDetail['detail'][$pos]['title'] = trim($detail['title']??"");
                $elementDetail['detail'] = json_encode($elementDetail['detail']);
                $res = $this->oPageElement->updatePageElement($element_id,$elementDetail);
                Base_Common::refreshCache($this->config,"page",$elementDetail['page_id']);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
        }
        echo json_encode($response);
        return true;
    }
}


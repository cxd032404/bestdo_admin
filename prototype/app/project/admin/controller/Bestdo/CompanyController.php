<?php
//ALTER TABLE `config_sports_type` ADD `SpeedDisplayType` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '速度显示单位' AFTER `SportsTypeName`;

/**
 * 运动管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Bestdo_CompanyController extends AbstractController
{
	/**企业:SportsType
	 * @var string
	 */
	protected $sign = '?ctl=bestdo/company';
    protected $ctl = 'bestdo/company';

    /**
	 * game对象
	 * @var object
	 */
	protected $oCompany;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oCompany = new Bestdo_Company();

	}
	//企业配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//获取企业列表
			$SportTypeList = $this->oCompany->getAllSportsTypeList();
			//循环企业列表
			foreach($SportTypeList as $key => $SportsTypeInfo)
            {
                //数据解包
                $SportTypeList[$key]['comment'] = json_decode($SportsTypeInfo['comment'],true);
            }
			//渲染模版
			include $this->tpl('Bestdo_Company_CompanyList');
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
			//渲染模版
			include $this->tpl('Bestdo_Company_CompanyAdd');
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
		$bind=$this->request->from('company_name','comment');
		//企业名称不能为空
		if(trim($bind['company_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            $oUpload = new Base_Upload('upload_img');
	        $upload = $oUpload->upload('upload_img',$this->config->oss);
	        $oss_urls = array_column($upload->resultArr,'oss');
	        
	        $bind['icon'] = implode("",$oss_urls);
            //数据打包
            $bind['comment'] = json_encode($bind['comment']);
		    //添加企业
			$res = $this->oCompany->insertCompany($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改企业信息页面
	public function sportsTypeModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("SportsTypeModify");
		if($PermissionCheck['return'])
		{
			//企业ID
			$SportsTypeId = intval($this->request->SportsTypeId);
			//获取企业信息
			$SportsTypeInfo = $this->oCompany->getSportsType($SportsTypeId,'*');
			//数据解包
			$SportsTypeInfo['comment'] = json_decode($SportsTypeInfo['comment'],true);
			//获取速度显示单位
            $SpeedDisplayTypeList = $this->oCompany->getSpeedDisplayList();
            //渲染模版
			include $this->tpl('Bestdo_Sports_SportsTypeModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//更新企业信息
	public function sportsTypeUpdateAction()
	{
	    //接收页面参数
		$bind=$this->request->from('SportsTypeId','SportsTypeName','SpeedDisplayType');
        //企业名称不能为空
		if(trim($bind['SportsTypeName'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            //数据打包
            $bind['comment'] = json_encode($bind['comment']);
			//修改企业
			$res = $this->oCompany->updateSportsType($bind['SportsTypeId'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除企业
	public function sportsTypeDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("SportsTypeDelete");
		if($PermissionCheck['return'])
		{
			//企业ID
			$SportsTypeId = trim($this->request->SportsTypeId);
			//删除企业
			$this->oCompany->deleteSportsType($SportsTypeId);
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

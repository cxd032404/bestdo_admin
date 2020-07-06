<?php
/**
 * 配置管理
 * @author Chen<cxd032404@hotmail.com>
 */

class ConfigController extends AbstractController
{
	/**配置
	 * @var string
	 */
	protected $sign = '?ctl=config';
    protected $ctl = 'config';

    /**
	 * game对象
	 * @var object
	 */
	protected $oConfig;

    /**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oConfig = new Widget_Config();


    }
	//配置列表页面
	public function indexAction()
	{
	    //检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
            $configTypeList = $this->oConfig->getConfigType();
            //配置ID
            $config_type= trim($this->request->config_type??"");
            //获取配置列表
			$configList = $this->oConfig->getConfigList(['config_type'=>$config_type]);
			foreach($configList as $key => $configInfo)
            {
                $configList[$key]['config_type_name'] = $configTypeList[$configInfo['config_type']]??"未知类型";
            }
			//渲染模版
			include $this->tpl('Config_DefaultConfigList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加配置类型填写配置页面
	public function configAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateConfig");
		if($PermissionCheck['return'])
		{
            $configTypeList = $this->oConfig->getConfigType();
			//渲染模版
			include $this->tpl('Config_ConfigAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//添加新配置
	public function configInsertAction()
	{
		//检查权限
		$bind=$this->request->from('config_name','content','config_sign','config_type');
		//配置名称不能为空
		if(trim($bind['config_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            $configExists = $this->oConfig->getConfigList(['config_name'=>$bind['config_name']],'config_sign,config_name');
            if(count($configExists)>0)
            {
                $response = array('errno' => 3);
            }
            else
            {
                $configExists = $this->oConfig->getConfigList(['config_sign'=>$bind['config_sign']],'config_sign,config_name');
                if(count($configExists)>0)
                {
                    $response = array('errno' => 2);
                }
                else
                {
                    //数据打包
                    $bind['content'] = "";
                    //添加配置
                    $res = $this->oConfig->insertConfig($bind);
                    //Base_Common::refreshCache($this->config,"config",$res);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
            }
		}
		echo json_encode($response);
		return true;
	}
	
	//修改配置信息页面
	public function configModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateConfig");
		if($PermissionCheck['return'])
		{
			//配置ID
			$config_sign= trim($this->request->config_sign);
			//获取配置信息
			$configInfo = $this->oConfig->getConfig($config_sign,'*');
            //配置ID
            $configTypeList = $this->oConfig->getConfigType();
            $configInfo["config_type_name"] =$configTypeList[$configInfo["config_type"]]??"未知类型";
            if($configInfo['config_type']=="source")
            {
                //渲染模版
                include $this->tpl('Config_ConfigModifySource');
            }
            else
            {
                //渲染模版
                include $this->tpl('Config_ConfigModify');
            }

		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//更新配置信息
	public function configUpdateAction()
	{
	    //接收页面参数
		$bind=$this->request->from('config_sign','config_name','content');
        //配置名称不能为空
		if(trim($bind['config_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            $configExists = $this->oConfig->getConfigList(['config_name'=>$bind['config_name'],'exclude_id'=>$bind['config_sign']],'config_sign,config_name');
            if(count($configExists)>0)
            {
                $response = array('errno' => 3);
            }
            else
            {
                //获取配置信息
                $configInfo = $this->oConfig->getConfig($bind['config_sign'],'*');
                if($configInfo['config_type']=="source")
                {
                    unset($bind['content']);
                }
                elseif($configInfo['config_type']=="int")
                {
                    $bind['content'] = intval($bind['content']);
                }
                elseif($configInfo['config_type']=="char")
                {
                    $bind['content'] = trim($bind['content']);
                }
                //修改配置
                $res = $this->oConfig->updateConfig($bind['config_sign'],$bind);
                //Base_Common::refreshCache($this->config,"config",$bind['config_sign']);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
		}
		echo json_encode($response);
		return true;
	}
	
	//删除配置
	public function configDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteConfig");
		if($PermissionCheck['return'])
		{
			//配置ID
			$config_sign = trim($this->request->config_sign);
			//获取下属配置列表
			$configList = $this->oConfig->getConfigList(['parent_id'=>$config_sign],"config_sign");
			//循环删除下属配置
			if(count($configList)>0)
			{
				foreach($configList as $configInfo)
				{
					$this->oConfig->deleteConfig($configInfo['config_sign']);
				}
			}
			//删除配置
			$this->oConfig->deleteConfig($config_sign);
			//返回之前的页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //资源详情列表
    public function sourceDetailAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateConfig");
        if($PermissionCheck['return'])
        {
            //配置ID
            $config_sign= trim($this->request->config_sign);
            //获取配置信息
            $configInfo = $this->oConfig->getConfig($config_sign,'*');
            //数据解包
            $configInfo['content'] = json_decode($configInfo['content'],true);
            //渲染模版
            include $this->tpl('Config_SourceList');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加健步走banner页面
    public function sourceDetailAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateConfig");
        if($PermissionCheck['return'])
        {
            //配置ID
            $config_sign= trim($this->request->config_sign);
            //获取配置信息
            $configInfo = $this->oConfig->getConfig($config_sign,'*');
            //渲染模版
            include $this->tpl('Config_SourceAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加健步走banner
    public function sourceDetailInsertAction()
    {
        //配置ID
        $config_sign = trim($this->request->config_sign);
        $detail = $this->request->detail;
        $configInfo = $this->oConfig->getConfig($config_sign,"config_sign,content");
        $configInfo['content'] = json_decode($configInfo['content'],true);
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
            $configInfo['content'] = $configInfo['content']??[];
            $configInfo['content'][] = ['img_url'=>$oss_urls['0'],'img_jump_url'=>$detail['img_jump_url'],'text'=>trim($detail['text']??""),'title'=>trim($detail['title']??"")];
            $configInfo['content'] = json_encode($configInfo['content']);
            $res = $this->oConfig->updateConfig($config_sign,$configInfo);
            //Base_Common::refreshCache($this->config,"config",$config_sign);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //修改健步走Banner页面
    public function sourceDetailModifyAction()
    {
        //元素ID
        $config_sign = trim($this->request->config_sign);
        $pos = trim($this->request->pos??0);
        $configInfo = $this->oConfig->getConfig($config_sign,"config_sign,content");
        $configInfo['content'] = json_decode($configInfo['content'],true);
        $sourceInfo = $configInfo['content'][$pos];
        //渲染模版
        include $this->tpl('Config_SourceModify');
    }
    //更新健步走banner详情
    public function sourceDetailUpdateAction()
    {
        //配置ID
        $config_sign = trim($this->request->config_sign);
        $detail = $this->request->detail;
        $configInfo = $this->oConfig->getConfig($config_sign,"config_sign,content");
        $configInfo['content'] = json_decode($configInfo['content'],true);
        $pos = trim($this->request->pos??0);
        //上传图片
        $oUpload = new Base_Upload('upload_img');
        $upload = $oUpload->upload('upload_img',$this->config->oss);
        $oss_urls = array_column($upload->resultArr,'oss');
        //如果以前没上传过且这次也没有成功上传
        if((!isset($configInfo['content'][$pos]['img_url']) || $configInfo['content'][$pos]['img_url']=="") && (!isset($oss_urls['0']) || $oss_urls['0'] == ""))
        {
            $response = array('errno' => 2);
        }
        else
        {
            //这次传成功了就用这次，否则维持
            $configInfo['content'][$pos]['img_url'] = (isset($oss_urls['0']) && $oss_urls['0']!="")?($oss_urls['0']):($configInfo['content'][$pos]['img_url']);
            //保存跳转链接
            $configInfo['content'][$pos]['img_jump_url'] = trim($detail['img_jump_url']??"");
            $configInfo['content'][$pos]['text'] = trim($detail['text']??"");
            $configInfo['content'][$pos]['title'] = trim($detail['title']??"");
            $configInfo['content'] = json_encode($configInfo['content']);

            $res = $this->oConfig->updateConfig($config_sign,$configInfo);
            //Base_Common::refreshCache($this->config,"config",$config_sign);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //删除健步走banner详情
    public function sourceDetailDeleteAction()
    {
        //配置ID
        $config_sign = trim($this->request->config_sign);
        $detail = $this->request->detail;
        $configInfo = $this->oConfig->getConfig($config_sign,"config_sign,content");
        $configInfo['content'] = json_decode($configInfo['content'],true);
        $pos = trim($this->request->pos??0);
        if(isset($configInfo['content'][$pos]))
        {
            unset($configInfo['content'][$pos]);
            $configInfo['content'] = array_values($configInfo['content']);
            $configInfo['content'] = json_encode($configInfo['content']);
            $res = $this->oConfig->updateConfig($config_sign,$configInfo);
            //Base_Common::refreshCache($this->config,"config",$config_sign);
        }
        $this->response->goBack();
    }
}

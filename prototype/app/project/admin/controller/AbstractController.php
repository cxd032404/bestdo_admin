<?php
/**
 * 后台管理控制层基类
 * @author Chen <cxd032404@hotmail.com>
 * $Id: AbstractController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

abstract class AbstractController extends Base_Controller_Action
{

	/**
	 * 配置
	 * @var Base_Config
	 */
	protected $config;

	/**
	 * 管理员
	 * @var Widget_Manager
	 */
	protected $manager;

	/**
	 * 是否需要登录后访问
	 * @var boolean
	 */
	protected $needLogin = true;

	/**
	 * 提示
	 * @var Base_Notice
	 */
	protected $notice = null;
	
	protected $oLogManager;

	public function init()
	{
		$this->manager = new Widget_Manager();
		$this->oLogManager = new Log_LogsManager();
		$this->oLogManager->pushLog('ip', $this->request->getIp());
		$this->oLogManager->pushLog('addtime', time());
		$this->oLogManager->pushLog('manager_id', $this->manager->id);
		$this->oLogManager->pushLog('name', $this->manager->name);
		$this->oLogManager->pushLog('url', $this->request->getServer('REQUEST_URI'));
		$this->oLogManager->pushLog('referer', $this->request->getReferer() ? $this->request->getReferer() : 'referer');
		$this->oLogManager->pushLog('agent', $this->request->getAgent());
				$this->oLogManager->pushLog('log', "");
		$config = (@include Base_Common::$config['config_file']);
		//$appConfig = (@include dirname(dirname(__FILE__)) . '/etc/config.php');
		//is_array($appConfig) && $config = $config + $appConfig;
		$this->config = Base_Config::factory($config);
		$this->notice = Base_Notice::getInstance();
        if ($this->needLogin && !$this->manager->isLogin()) 
        {	        
             $this->response->redirect($this->manager->loginUrl);
        }

		//@todo:这里的强制修改密码最好能在登录成功的时候执行 
		if (!($this instanceof ModifyPwdController) && (!$this instanceof LoginController))
		{
//			if($this->manager->reset_password)
//			{
//				$this->response->redirect('?ctl=modify.pwd&ac=compel.repwd');
//				exit;
//			}
		}

	}

	public function __destruct()
	{
		if($this->manager->id)
		{
			$this->oLogManager->insert();
		}
	}
}

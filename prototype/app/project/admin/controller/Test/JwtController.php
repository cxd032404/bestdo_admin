<?php
/**
 * 赛事管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Test_JwtController extends AbstractController
{
	/**测试：短信
	 * @var string
	 */
	protected $sign = '?ctl=test/jwt';
	/**
	 * race对象
	 * @var object
	 */

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();

	}
	//上传页面
	public function indexAction()
	{
	    //检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("jwttest");
		if($PermissionCheck['return'])
		{
			$oJwt = new Third_Jwt();
			$rand = rand(0,100);
			$token = $oJwt::getToken($rand);
			echo $rand."-".$token;
			$unpace = $oJwt::getUserId($token);
			echo "-------666--------";
			var_dump($unpace);
		    //渲染模板
			//include $this->tpl('Test_sms');
		}
		else
		{
			$home = $this->sign;
			//include $this->tpl('403');
		}
	}
}

<?php
/**
 * 赛事管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Test_SmsController extends AbstractController
{
	/**测试：短信
	 * @var string
	 */
	protected $sign = '?ctl=test/sms';
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
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//渲染模板
			include $this->tpl('Test_sms');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//短信发送
	public function sendAction()
	{
        //手机号码
        $mobile = trim($this->request->mobile)??'18621758237';
        $oSms = new Third_aliyun_sms_SmsClient();
        $oSms->sendSms($mobile = [$mobile],$template = "reg",$params = ["code"=>sprintf("%06d",rand(1,999999))],$this->config->sms);
        //die($mobile);
        $response = ["errno"=>0,"success"=>1];
        echo json_encode($response);
		return true;
	}
}

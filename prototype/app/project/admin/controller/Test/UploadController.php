<?php
/**
 * 赛事管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Test_UploadController extends AbstractController
{
	/**赛事相关:RaceCatalog
	 * @var string
	 */
	protected $sign = '?ctl=test/upload';
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
			include $this->tpl('Test_Upload');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//添加新赛事
	public function uploadAction()
	{
	    //文件上传
        $oUpload = new Base_Upload('upload_img');
        $upload = $oUpload->upload('upload_img');
        $res[1] = $upload->resultArr;
        $path = $res[1][1];
        $res[1] = $upload->resultArr;
        $path = isset( $res[1][1] ) ? $res[1][1]:array('path'=>"");
        include('Third/oss/OssClientFile.php');
        $oss = app\lib\Third\oss\ossClientFile::uploadMatchCdn($path['path_root'],$path['path'],$this->config->oss);
        $url = $oss['info']['url'];
        $response = ["ossUrl"=>$url,'errno'=>0];
		echo json_encode($response);
		return true;
	}
}

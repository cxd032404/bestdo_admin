<?php
/**
 * 赛事管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Test_UploadController extends AbstractController
{
	/**测试：上传
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

	//上传图片
	public function uploadAction()
	{
	    $oUpload = new Base_Upload('upload_img');
        $upload = $oUpload->upload('upload_img',$this->config->oss);
        $oss_urls = array_column($upload->resultArr,'oss');
        $response = array('errno' => 0,'url'=>implode("<br>",$oss_urls));
        echo json_encode($response);
		return true;
	}
    //上传页面
    public function ckAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            $oText = new Hj_RichText();
            $record = $oText->get();
            $comment = json_decode($record[1]['text']);
            $record2 = $oText->get(2);
            $comment2 = json_decode($record2[2]['text']);
                //渲染模板
            include $this->tpl('Test_ck');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //富文本上传图片
    public function richUploadAction()
    {

        $oText = new Hj_RichText();
        $comment = $this->request->comment;
        $comment = json_encode($comment);
        $save = $oText->save(1,$comment);

        $comment2 = $this->request->comment2;
        $comment2 = json_encode($comment2);
        $save2 = $oText->save(2,$comment2);
        if($save || $save2)
        {
            $response = ['errno'=>0];
        }
        else
        {
            $response = ['errno'=>1];
        }
        echo json_encode($response);
        return true;
    }
}

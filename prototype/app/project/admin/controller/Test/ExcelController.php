<?php
/**
 * 赛事管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Test_ExcelController extends AbstractController
{
	/**测试：上传
	 * @var string
	 */
	protected $sign = '?ctl=test/excel';
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
			include $this->tpl('Test_excel');
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
        $oUpload = new Base_Upload('upload_txt');
        $upload = $oUpload->upload('upload_txt');
        $upload = $upload->resultArr;
        $file_path = $upload[1]['path'];
        $data = Base_Common::readDataFromExcel($file_path);
        print_R($data);
        die();
        echo json_encode($response);
		return true;
	}
    //上传页面
    public function excelAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
                //渲染模板
            include $this->tpl('Test_excel');
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

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
        $oExcel = new PHPExcel_Reader_Excel2007();
        //$sheetList = $oExcel->listWorksheetNames($upload[1]['path']);
        $sheetInfo = $oExcel->listWorksheetInfo($upload[1]['path']);
        $sheet = $oExcel->load($upload[1]['path']);
        echo "<pre>";

        foreach($sheetInfo as $key => $value)
        {
            $currentSheet = $sheet->getSheet($key);
            $maxColumn = $value['lastColumnLetter'];
            $maxRow = $value['lastColumnIndex']+1;
            $data = array();
            for($rowIndex=1;$rowIndex<=$maxRow;$rowIndex++){        //循环读取每个单元格的内容。注意行从1开始，列从A开始
                for($colIndex='A';$colIndex<=$maxColumn;$colIndex++){
                    $addr = $colIndex.$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){ //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $data[$rowIndex][$colIndex] = $cell;
                }
            }
            print_R($data);
        }
        print_R($sheetInfo);
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

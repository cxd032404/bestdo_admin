<?php
/**
 *
 * 
 */
class XraceAppConfigController extends AbstractController
{
    /**
     *对象声明
     */
    protected $oApp;
    /**
     * 初始化
     * (non-PHPdoc)
     * @see AbstractController#init()
     */
    public function init()
    {
        parent::init();
        $this->oApp = new Xrace_App();
    }
    /*
    * 获取最新版本信息
    */
    public function getNewestAppVersionAction()
    {
        //APP系统ID
        $AppOSId = abs(intval($this->request->AppOSId));
        //APP类型ID
        $AppTypeId = abs(intval($this->request->AppTypeId));
        //APP类型ID
        $AppVersion = trim($this->request->AppVersion);
        //获取最新版本信息
        $NewestAppVersionList = $this->oApp->getNewestVersionList(1);
        //如果有获取到最新版本信息
        if(count($NewestAppVersionList))
        {
            //如果有记录当前分类的最新版本
            if(isset($NewestAppVersionList[$AppOSId][$AppTypeId]))
            {
                //如果当前最高版本高于传入的版本
                if($NewestAppVersionList[$AppOSId][$AppTypeId]["AppVersion"]>Base_Common::ParthVersionToInt($AppVersion))
                {
                    //抽出数据
                    $NewestAppVersionInfo = $NewestAppVersionList[$AppOSId][$AppTypeId];
                    //格式化版本信息
                    $NewestAppVersionInfo['AppVersion'] = Base_Common::ParthIntToVersion($NewestAppVersionInfo['AppVersion']);
                    //解压缩下载路径
                    $NewestAppVersionInfo['AppDownloadUrl'] = urldecode($NewestAppVersionInfo['AppDownloadUrl']);
                    //结果数组
                    $result = array("return"=>1,"NewestAppVersionInfo" => $NewestAppVersionInfo);
                }
                else
                {
                    //结果数组
                    $result = array("return"=>0,"comment"=>"当前版本为最新");
                }
            }
            else
            {
                //结果数组
                $result = array("return"=>0,"comment"=>"版本信息获取错误");
            }
        }
        else
        {
            //结果数组
            $result = array("return"=>0,"comment"=>"版本信息获取错误");
        }
        echo json_encode($result);
    }
}
<?php
/**
 *
 * 
 */
class XraceSearchController extends AbstractController
{
    /**
     *对象声明
     */
    protected $oSearch;
    protected $oRace;


    /**
     * 初始化
     * (non-PHPdoc)
     * @see AbstractController#init()
     */
    public function init()
    {
        parent::init();
        $this->oSearch = new Xrace_Search();
        $this->oRace = new Xrace_Race();

    }

    /**
     *重建搜索关键字缓存
     */
    public function rebuildSearchKeywordAction()
    {
        //是否强制更新
        $Force = isset($this->request->Force) ? abs(intval($this->request->Force)) : 0;
        //是否调用缓存
        $this->oSearch->rebuildSearchWord($Force);
    }
    /**
     *获取所有关键字列表
     */
    public function getAllSearchKeywordAction()
    {
        //获取所有关键字列表
        $AllSearchKeyword = $this->oSearch->getAllSearchKeyword();
        $KeywordList = array();
        foreach($AllSearchKeyword['KeywordList'] as $key => $value)
        {
            $KeywordList[] = $value['Keyword'];
        }
        //结果数组 如果列表中有数据则返回成功，否则返回失败
        $result = array("return" => count($KeywordList) ? 1 : 0, "KeywordList" => $KeywordList);
        echo json_encode($result);
    }
    public function searchByKeywordAction()
    {
        $Text = trim(urldecode($this->request->Text));
        //获取所有关键字列表
        $AllSearchKeyword = $this->oSearch->getAllSearchKeyword();
        $SearchResultArr = array("RaceStageList"=>array());
        foreach($AllSearchKeyword['KeywordList'] as $key => $value)
        {
            //如果包含关键字
            if(is_numeric(stripos($Text,$value['Keyword'])))
            {
                //数据解包
                $value['SearchParams'] = json_decode($value['SearchParams'],true);
                //保存关联的内容
                foreach($value['SearchParams']["RaceStageList"] as $key => $value)
                {
                    $SearchResultArr["RaceStageList"][$key] = 1;
                }
            }
        }
        //初始化空的赛事列表
        $RaceCatalogList = array();
        foreach($SearchResultArr["RaceStageList"] as $key => $value)
        {
            //获得分站信息
            $SearchResultArr["RaceStageList"][$key] = $this->oRace->getRaceStage($key,"RaceStageId,RaceCatalogId,RaceStageName");
            //如果未获取过
            if(!isset($RaceCatalogList[$SearchResultArr["RaceStageList"][$key]['RaceCatalogId']]))
            {
                $RaceCatalogList[$SearchResultArr["RaceStageList"][$key]['RaceCatalogId']] = $this->oRace->getRaceCatalog($SearchResultArr["RaceStageList"][$key]['RaceCatalogId'],"RaceCatalogId,RaceCatalogName");
            }
            $SearchResultArr["RaceStageList"][$key]['RaceCatalogName'] = $RaceCatalogList[$SearchResultArr["RaceStageList"][$key]['RaceCatalogId']]['RaceCatalogName'];

        }
        $result = array("return"=>1,"SearchResultArr"=>$SearchResultArr);
        echo json_encode($result);

    }
}
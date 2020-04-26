<?php
/**
 * 搜索管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Xrace_SearchController extends AbstractController
{
	/**赛事分站:
	 * @var string
	 */
	protected $sign = '?ctl=xrace/search';
	/**
	 * race对象
	 * @var object
	 */
	protected $oRace;
    protected $oSearch;

    /**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oRace = new Xrace_Race();
        $this->oSearch = new Xrace_Search();


    }
	//赛事分站列表页面
	public function indexAction()
	{
	    //检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//关键字
            $Keyword = isset($this->request->Keyword)?trim($this->request->Keyword):"";
            $params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
            $params['PageSize'] = 10;
			$KeywordList = $this->oSearch->getAllKeyword(array("Keyword"=>$Keyword,"Page"=>$params['Page'],"PageSize"=>$params['PageSize']));
			//初始化空的分站列表
			$RaceStageList = array();
			//循环关键字列表
			foreach($KeywordList['KeywordList'] as $KeywordId => $KeywordInfo)
            {
                //数据解包
                $KeywordInfo['SearchParams'] = json_decode($KeywordInfo['SearchParams'],true);
                foreach($KeywordInfo['SearchParams']['RaceStageList'] as $RaceStageId => $key)
                {
                    //如果在分站列表中存在
                    if(isset($RaceStageList[$RaceStageId]))
                    {

                    }
                    else
                    {
                        //获取分站信息
                        $RaceStageList[$RaceStageId] = $this->oRace->getRaceStage($RaceStageId,"RaceStageId,RaceStageName");
                    }
                    $KeywordList['KeywordList'][$KeywordId]['RaceStageList'][$RaceStageId]['RaceStageInfo'] = $RaceStageList[$RaceStageId];
                }
            }
			$page_url = Base_Common::getUrl('','xrace/search','index',$params)."&Page=~page~";
            $page_content =  base_common::multi($KeywordList['KeywordCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
			//渲染模板
			include $this->tpl('Xrace_Search_KeywordList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
}

<?php
/**
 *
 * 
 */
class HorizonController extends AbstractController
{
    /**
     *对象声明
     */
    protected $oRace;
    protected $oHorizon;
    protected $oUser;

    /**
     * 初始化
     * (non-PHPdoc)
     * @see AbstractController#init()
     */
    public function init()
    {
        parent::init();
        $this->oHorizon = new Xrace_Horizon();
        $this->oRace = new Xrace_Race();
        $this->oUser = new Xrace_UserInfo();
    }

    /**
     *获取某个分站的信息的列表
     */
    public function getRaceInfoAction()
    {
        //格式化分站ID,默认为0
        $raceName = trim(urldecode($this->request->raceName));
        $RaceInfo = $this->oHorizon->getRaceStageInfo($raceName);
        echo json_encode($RaceInfo);
    }
    /**
     *获取某个分站的选手列表
     */
    public function getAthleteListAction()
    {
        //格式化分站ID,默认为0
        $raceName = trim(urldecode($this->request->raceName));
        $AthleteList = $this->oHorizon->getAthleteList($raceName);
        echo json_encode($AthleteList);
    }
    /**
     *获取某个分站的计时点列表
     */
    public function getTimingPointListAction()
    {
        //格式化分站ID,默认为0
        $raceName = trim(urldecode($this->request->raceName));
        $TimingPointList = $this->oHorizon->getTimingPointList($raceName);
        echo json_encode($TimingPointList);
    }
    /**
     *获取某个分站的计时点列表
     */
    public function uploadTimingAction()
    {
        //格式化分站ID,默认为0
        $RaceStageId = isset($this->request->RaceStageId) ? abs(intval($this->request->RaceStageId)) : 0;
        $RaceId = isset($this->request->RaceId) ? abs(intval($this->request->RaceId)) : 0;
        echo "here";
        $upload = $this->oHorizon->uploadTiming($RaceId);
        //echo json_encode($TimingPointList);
    }


}
<?php
/**
 *
 * 
 */
class XraceMapController extends AbstractController
{
    /**
     *对象声明
     */
    protected $oMap;
    protected $oRace;
    protected $oSports;
    /**
     * 初始化
     * (non-PHPdoc)
     * @see AbstractController#init()
     */
    public function init()
    {
        parent::init();
        $this->oRace = new Xrace_Race();
        $this->oSports = new Xrace_Sports();
        $this->oMap = new Xrace_Map();
    }
    /**
     *获取所有赛事的列表
     */
    public function getQxTraceAction()
    {
        //比赛ID
        $RaceId = isset($this->request->RaceId)?abs(intval($this->request->RaceId)):0;
        //用户ID
        $UserId = isset($this->request->UserId)?abs(intval($this->request->UserId)):0;
        //获取比赛信息
        $RaceInfo = $this->oRace->getRace($RaceId);
        //检测主键存在,否则值为空
        if(isset($RaceInfo['RaceId']))
        {
            //获取当前时间
            $currentTime = time();
            //比赛尚未开始
            if(($currentTime+1800)<=strtotime($RaceInfo['StartTime']))
            {
                //全部置为空
                $result = array("return"=>0,"RaceInfo"=>array(),"comment"=>"比赛尚未开始");
            }
            //比赛尚未开始
            elseif(($currentTime-1800)>=strtotime($RaceInfo['EndTime']))
            {
                //全部置为空
                $result = array("return"=>0,"RaceInfo"=>array(),"comment"=>"比赛已经结束");
            }
            else
            {
                //获取追踪信息
                $traceInfo  = $this->oMap->getQxTrace($RaceId,$UserId);
                $result = array("return"=>0,"trace"=>$traceInfo);
            }
        }
        else
        {
            //全部置为空
            $result = array("return"=>0,"RaceInfo"=>array(),"comment"=>"请指定一个有效的比赛ID");
        }
        echo json_encode($result);
    }
}
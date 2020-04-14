<?php
/**
 *
 * 
 */
class XraceArenaController extends AbstractController
{
    /**
     *对象声明
     */
    protected $oArena;

    /**
     * 初始化
     * (non-PHPdoc)
     * @see AbstractController#init()
     */
    public function init()
    {
        parent::init();
        $this->oArena = new Xrace_Arena();
    }

    /**
     *获取所有场地列表
     */
    public function getArenaListAction()
    {
        //获取场地列表
        $ArenaList = $this->oArena->getAllArenaList("ArenaName,ArenaId");
        //结果数组 如果列表中有数据则返回成功，否则返回失败
        $result = array("return" => 1, "ArenaList" => $ArenaList);
        echo json_encode($result);
    }
    /**
     *获取单个场地的详情
     */
    public function getArenaInfoAction()
    {
        //场地ID
        $ArenaId = abs(intval($this->request->ArenaId));
        //是否获取可选的时间段详情
        $RaceTimeDetail = isset($this->request->RaceTimeDetail) ? abs(intval($this->request->RaceTimeDetail)) : 0;
        if($RaceTimeDetail>0)
        {
            //获取场地信息
            $ArenaInfo = $this->oArena->getArena($ArenaId,'*');
            //如果找到
            if(isset($ArenaInfo['ArenaId']))
            {
                //数据解包
                $ArenaInfo['comment'] = json_decode($ArenaInfo['comment'],true);
                //如果有且有元素
                if(isset($ArenaInfo['comment']['RaceTimeList']) && count($ArenaInfo['comment']['RaceTimeList'])>0)
                {
                    //循环结果数组
                    foreach($ArenaInfo['comment']['RaceTimeList'] as $key => $TimeInfo)
                    {
                        $ArenaInfo['comment']['RaceTimeList'][$key]['DateList'] = $this->oArena->genWeekdayList($TimeInfo['Weekday'],30);
                    }
                }
                //返回失败
                $result = array("return" => 1, "ArenaInfo" => $ArenaInfo);
            }
            else
            {
                //返回失败
                $result = array("return" => 0, "comment" => "无此场地");
            }
        }
        else
        {
            //获取场地信息
            $ArenaInfo = $this->oArena->getArena($ArenaId,'ArenaId,ArenaName');
            //如果找到
            if(isset($ArenaInfo['ArenaId']))
            {
                //返回失败
                $result = array("return" => 1, "ArenaInfo" => $ArenaInfo);
            }
            else
            {
                //返回失败
                $result = array("return" => 0, "comment" => "无此场地");
            }
        }
        echo json_encode($result);
    }
}
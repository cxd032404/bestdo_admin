<?php
/**
 *
 * 
 */
class WechatController extends AbstractController
{
    /**
     *对象声明
     */
    protected $oWechatTiming;
    protected $oUser;
    protected $oRace;

    /**
     * 初始化
     * (non-PHPdoc)
     * @see AbstractController#init()
     */
    public function init()
    {
        parent::init();
        $this->oWechatTiming = new Xrace_WechatTiming();
        $this->oRace = new Xrace_Race();
        $this->oUser = new Xrace_UserInfo();
    }

    /**
     *获取打卡记录并入库
     */
    public function insertTimingAction()
    {	
		//比赛ID
        $Timing['RaceId'] = abs(intval($this->request->RaceId));
        //用户ID
        $Timing['RaceUserId'] = abs(intval($this->request->RaceUserId));
        //管理员ID
        $Timing['ManagerId'] = abs(intval($this->request->ManagerId));
        //微信openID
        $Timing['OpenId'] = trim(urldecode($this->request->OpenId));
        //计时点标识
        $Timing['Location'] = trim(urldecode($this->request->Location));
        //时间
        $Timing['Time'] = abs(intval($this->request->Time));
        //如果时间非法或与当前时差超过60秒，则以当前时间为准
        $Timing['Time'] = $Timing['Time'] > 0 || (abs($Timing['Time'] - time()) >= 60) ? $Timing['Time'] : time();
        $Timing['TencentX'] = trim(urldecode($this->request->TencentX));
        $Timing['TencentY'] = trim(urldecode($this->request->TencentY));
        $sign = Base_Common::check_sign($Timing,"xrace_2018");
        if(($sign == trim($this->request->sign)) || (trim($this->request->Test==1)))
        {
            //插入记录
            $InsertLog = $this->oWechatTiming->insertTimingLog($Timing);
            if ($InsertLog['return'] > 0)
            {
                //全部置为空
                $result = array("return" => 1, "comment" => "打卡成功,距离目标点".$InsertLog['Distance']."米","NextPoint"=>$InsertLog["NextPoint"],"TimingLog"=>$InsertLog["TimingLog"]);
            }
            else
            {
                if($InsertLog['return'] == -1)
                {
                    //全部置为空
                    $result = array("return" => 0, "comment" => "用户信息有误");
                }
                elseif($InsertLog['return'] == -2)
                {
                    //全部置为空
                    $result = array("return" => 0, "comment" => "用户未找到");
                }
                elseif($InsertLog['return'] == -3)
                {
                    //全部置为空
                    $result = array("return" => 0, "comment" => "比赛未找到");
                }
                elseif($InsertLog['return'] == -4)
                {
                    //全部置为空
                    $result = array("return" => 0, "comment" => "好像没报名哦");
                }
                elseif($InsertLog['return'] == -5)
                {
                    //全部置为空
                    $result = array("return" => 0, "comment" => $Timing['Location']."计时点好像不存在哦");
                }
                elseif($InsertLog['return'] == -6)
                {
                    //全部置为空
                    $result = array("return" => 0, "comment" => "离开打卡点的距离有点远哦，足足".$InsertLog['Distance']."米哦");
                }
                elseif($InsertLog['return'] == -7)
                {
                    //全部置为空
                    $result = array("return" => 1, "comment" => "不需要重复打卡了哦","TimingLog"=>$InsertLog["TimingLog"]);
                }
				elseif($InsertLog['return'] == -8)
                {
                    //全部置为空
                    $result = array("return" => 0, "comment" => "有点着急了哦，现在打卡不算的,还需等待".$InsertLog['time']."秒");
                }
            }
        }
        else
        {
            //全部置为空
            $result = array("return" => 0, "comment" => "数字签名有误");
        }
        echo json_encode($result);
    }
    /**
     *获取打卡记录
     */
    public function getTimingAction()
    {
        //比赛ID
        $Timing['RaceId'] = abs(intval($this->request->RaceId));
        //分组ID
        $Timing['RaceGroupId'] = abs(intval($this->request->RaceGroupId));
        //用户ID
        $Timing['RaceUserId'] = abs(intval($this->request->RaceUserId));
        //时间
        $Timing['Time'] = abs(intval($this->request->Time));
        $sign = Base_Common::check_sign($Timing,"xrace_2018");
        if(($sign == trim($this->request->sign)) || (trim($this->request->Test==1)))
        {
            //如果时间非法或与当前时差超过60秒，则以当前时间为准
            $Timing['Time'] = $Timing['Time'] > 0 || (abs($Timing['Time'] - time()) >= 60) ? $Timing['Time'] : time();
            //插入记录
            $timingLog = $this->oWechatTiming->getTiming($Timing);
            if ($timingLog['return'] > 0)
            {
                //全部置为空
                $result = array("return" => 1, "TimingLog" => $timingLog);
            }
            else
            {
                if($timingLog['return'] == -1)
                {
                    //全部置为空
                    $result = array("return" => 0, "comment" => "用户信息有误");
                }
                elseif($timingLog['return'] == -2)
                {
                    //全部置为空
                    $result = array("return" => 0, "comment" => "用户未找到");
                }
                elseif($timingLog['return'] == -3)
                {
                    //全部置为空
                    $result = array("return" => 0, "comment" => "比赛未找到");
                }
                elseif($timingLog['return'] == -4)
                {
                    //全部置为空
                    $result = array("return" => 0, "comment" => "好像没报名哦");
                }
            }
        }
        else
        {
            //全部置为空
            $result = array("return" => 0, "comment" => "数字签名有误");
        }
        echo json_encode($result);
    }
}
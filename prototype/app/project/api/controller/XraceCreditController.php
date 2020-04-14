<?php
/**
 *
 * 
 */
class XraceCreditController extends AbstractController
{
    /**
     *对象声明
     */
    protected $oUser;
    protected $oAction;
    protected $oCredit;

    /**
     * 初始化
     * (non-PHPdoc)
     * @see AbstractController#init()
     */
    public function init()
    {
        parent::init();
        $this->oUser = new Xrace_UserInfo();
        $this->oAction = new Xrace_Action();
        $this->oCredit = new Xrace_Credit();
    }

    /**
     *根据动作给用户更新积分
     */
    public function creditByActionAction()
    {
        //动作
        $Action = trim($this->request->Action) ? trim($this->request->Action) : "";
        //用户ID
        $UserId = isset($this->request->UserId) ? abs(intval($this->request->UserId)) : 0;
        //结果数组 如果列表中有数据则返回成功，否则返回失败
        $CreditList = $this->oAction->CreditByAction($Action,$UserId);
        if(count($CreditList)>0)
        {
            $result = array("return" => 1,"CreditList"=>$CreditList,"comment" => "成功！");
        }
        else
        {
            $result = array("return" => 0,"comment" => "失败！");
        }
        echo json_encode($result);
    }
    /**
     *获取用户的积分类目详情
     */
    public function getUserCreditLogAction()
    {
        //用户ID
        $params['UserId'] = isset($this->request->UserId) ? abs(intval($this->request->UserId)) : 0;
        //页码，默认为1
        $params['Page'] = isset($this->request->Page) ? abs(intval($this->request->Page)) : 1;
        //每页数量
        $params['PageSize'] = isset($this->request->PageSize) ? abs(intval($this->request->PageSize)) : 5;
        //获取记录数量
        $params['getCount'] = 1;
        //结果数组 如果列表中有数据则返回成功，否则返回失败
        $CreditLog = $this->oCredit->getCreditLog($params);
        foreach($CreditLog['CreditLog'] as $Id => $LogInfo)
        {
            //如果在积分列表里面没有该记录
            if (!isset($CreditList[$LogInfo['CreditId']]))
            {
                //重新获取积分信息
                $CreditInfo = $this->oCredit->getCredit($LogInfo['CreditId'], "CreditId,CreditName");
                //如果获取到
                if (isset($CreditInfo['CreditId']))
                {
                    //保存到积分列表中
                    $CreditList[$LogInfo['CreditId']] = $CreditInfo;
                }
            }
            //保存积分名称
            $CreditLog['CreditLog'][$Id]['CreditName'] = isset($CreditList[$LogInfo['CreditId']])?$CreditList[$LogInfo['CreditId']]['CreditName']:"未知";
        }
        $result = array("return" => 1,"CreditLog" => $CreditLog);
        echo json_encode($result);
    }
    /**
 *获取用户的积分类目详情
 */
    public function getUserCreditAction()
    {
        //用户ID
        $UserId = isset($this->request->UserId) ? abs(intval($this->request->UserId)) : 0;
        //用户ID
        $params['Money'] = intval($this->request->Money);
        //页码，默认为1
        $params['Page'] = isset($this->request->Page) ? abs(intval($this->request->Page)) : 1;
        //每页数量
        $params['PageSize'] = isset($this->request->PageSize) ? abs(intval($this->request->PageSize)) : 5;
        //结果数组 如果列表中有数据则返回成功，否则返回失败
        $UserCreditList = $this->oCredit->getUserCredit($UserId,$params);
        $TotalAmount = 0;
        foreach($UserCreditList['CreditList'] as $CreditId => $Credit)
        {
            //如果在积分列表里面没有该记录
            if (!isset($CreditList[$Credit['CreditId']]))
            {
                //重新获取积分信息
                $CreditInfo = $this->oCredit->getCredit($CreditId,"CreditId,CreditName,CreditRate");
                //如果获取到
                if (isset($CreditInfo['CreditId']))
                {
                    if(($CreditInfo['CreditRate']>0 && $params['Money']>0) || ($CreditInfo['CreditRate']==0 && $params['Money']<0) || $params['Money']==0)
                    {
                        //保存到积分列表中
                        $CreditList[$Credit['CreditId']] = $CreditInfo;
                        $CreditList[$Credit['CreditId']]['Credit'] = $Credit['Credit'];
                        $TotalAmount += $CreditInfo['CreditRate']+$Credit['Credit'];

                    }
                    else
                    {
                        unset($UserCreditList['CreditList'][$CreditId]);
                    }
                }
            }
            //保存积分名称
            $UserCreditList['CreditList'][$CreditId]['CreditName'] = isset($CreditList[$Credit['CreditId']])?$CreditList[$Credit['CreditId']]['CreditName']:"未知";
        }
        $result = array("return" => 1,"TotalAmount"=>$TotalAmount,"CreditList" => $CreditList);
        echo json_encode($result);
    }
    /**
     *对用户的订单进行积分抵扣
     */
    public function creditExchangeByOrderAction()
    {
        //用户ID
        $UserId = isset($this->request->UserId) ? abs(intval($this->request->UserId)) : 0;
        //订单ID
        $OrderId = isset($this->request->OrderId) ? trim($this->request->OrderId): "";
        //需要抵扣的订单金额
        $ExchangeAmount = abs(intval($this->request->ExchangeAmount));
        //最小使用单位
        $CreditStack = abs(intval($this->request->CreditStack));
        //兑换积分
        $Exchange = $this->oCredit->exchangeCreditByOrder($UserId,$OrderId,$ExchangeAmount,$CreditStack);
        $result = array("return" => $Exchange['Amount']>0?1:0,"Exchange" => $Exchange);
        echo json_encode($result);
    }
    /**
     *对用户订单的积分抵扣进行回滚操作
     */
    public function creditExchangeByOrderRevertAction()
    {
        //用户ID
        $UserId = isset($this->request->UserId) ? abs(intval($this->request->UserId)) : 0;
        //订单ID
        $OrderId = isset($this->request->OrderId) ? trim($this->request->OrderId): "";
        //兑换积分
        $Revert = $this->oCredit->exchangeCreditByOrderRevert($UserId,$OrderId);
        //返回结果
        $result = array("return" => $Revert['success']>0?1:0,"RevertList"=>$Revert['CreditList']);
        echo json_encode($result);
    }
    /**
     *对用户订单的积分抵扣进行回滚操作
     */
    public function creditExchangeByOrderConfirmAction()
    {
        //用户ID
        $UserId = isset($this->request->UserId) ? abs(intval($this->request->UserId)) : 0;
        //订单ID
        $OrderId = isset($this->request->OrderId) ? trim($this->request->OrderId): "";
        //兑换积分
        $Confirm = $this->oCredit->exchangeCreditByOrderConfirm($UserId,$OrderId);
        //返回结果
        $result = array("return" => $Confirm?1:0);
        echo json_encode($result);
    }
}
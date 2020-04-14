<?php

$openid = isset($_GET['openid']) ? $_GET['openid'] : '';
$time = isset($_GET['time']) ? $_GET['time'] : time();
$sign = isset($_GET['sign']) ? $_GET['sign'] : '';
include dirname(dirname(__FILE__)) . '/init.php';
$data = array("openid"=>$openid,"time"=>$time);
$p_sign = Base_Common::check_sign($data,"xrace_2018");
if($sign == $p_sign)
{
    if(abs(time()-$time)>=60)
    {
        //返回错误
        $result = array("return" => 0, "comment" =>"接口超时，联系管理员");
    }
    else
    {
        $oManager = new Widget_Manager();
        $managerInfo = $oManager->getRowByOpenId($openid,"id,name,data_groups");
        if($managerInfo["id"])
        {
            $PermissionList = array();
            $groupList = explode(',',$managerInfo['data_groups']);
            foreach($groupList as $key => $group_id)
            {
                $totalPermission = $oManager->getPermissionList($group_id);
                foreach($totalPermission as $key2 => $RaceCatalogInfo)
                {
                    if(isset($RaceCatalogInfo['RaceCatalogId']))
                    {
                        $PermissionList[$RaceCatalogInfo['RaceCatalogId']] = 1;
                    }
                }

            }
            //返回管理员数据
            $result = array("return" => 1, "ManagerInfo" => json_encode($managerInfo),"PermissionList" => $PermissionList);
        }
        else
        {
            //返回错误
            $result = array("return" => 0, "comment" =>"你好像没绑定过微信到后台哦，联系管理员");
        }
    }
}
else
{
    //返回错误
    $result = array("return" => 0, "comment" =>"签名验证不通过，联系管理员");
}
echo json_encode($result);



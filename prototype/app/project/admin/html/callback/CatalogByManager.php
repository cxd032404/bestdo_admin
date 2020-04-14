<?php

$id = isset($_GET['id']) ? $_GET['id'] : '';
$time = isset($_GET['time']) ? $_GET['time'] : time();
$sign = isset($_GET['sign']) ? $_GET['sign'] : '';
//是否显示说明注释 默认为1
$GetComment = isset($_GET['GetComment']) ? abs(intval($_GET['GetComment'])) : 1;
include dirname(dirname(__FILE__)) . '/init.php';
$data = array("id"=>$id,"time"=>$time,"GetComment"=>$GetComment);
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
        $oRace = new Xrace_Race();
        $Config = include(dirname(dirname(dirname(__FILE__)))."/etc/config.php");
        $managerInfo = $oManager->getRow($id,"id,name,data_groups");
        if($managerInfo["id"])
        {
            $DataPermissionListWhere = $oManager->getDataPermissionByGroupWhere($managerInfo["data_groups"]);
            //获得赛事列表
            $RaceCatalogList = $oRace->getRaceCatalogList(1,"*",0,$DataPermissionListWhere);
            //如果没有返回值,默认为空数组
            if (!is_array($RaceCatalogList))
            {
                $RaceCatalogList = array();
            }
            //循环赛事列表数组
            foreach ($RaceCatalogList as $RaceCatalogId => $RaceCatalogInfo)
            {
                //如果有输出赛事图标的绝对路径
                if (isset($RaceCatalogInfo['comment']['RaceCatalogIcon']))
                {
                    //删除
                    unset($RaceCatalogList[$RaceCatalogId]['comment']['RaceCatalogIcon']);
                }
                //如果有输出赛事图标的相对路径
                if (isset($RaceCatalogInfo['comment']['RaceCatalogIcon_root']))
                {
                    //拼接上ADMIN站点的域名
                    $RaceCatalogList[$RaceCatalogId]['comment']['RaceCatalogIcon'] = $Config['adminUrl'] . $RaceCatalogList[$RaceCatalogId]['comment']['RaceCatalogIcon_root'];
                    //删除原有数据
                    unset($RaceCatalogList[$RaceCatalogId]['comment']['RaceCatalogIcon_root']);
                }
                //如果参数不显示说明文字
                if ($GetComment != 1)
                {
                    //则删除该字段
                    unset($RaceCatalogList[$RaceCatalogId]['RaceCatalogComment']);
                }
            }
            //结果数组 如果列表中有数据则返回成功，否则返回失败
            $result = array("return" => count($RaceCatalogList) ? 1 : 0, "RaceCatalogList" => $RaceCatalogList);
        }
        else
        {
            //返回错误
            $result = array("return" => 0, "comment" =>"用户ID不正确哦，联系管理员");
        }
    }
}
else
{
    //返回错误
    $result = array("return" => 0, "comment" =>"签名验证不通过，联系管理员");
}
echo json_encode($result);



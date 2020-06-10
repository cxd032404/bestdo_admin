<?php
include dirname(dirname(__FILE__)) . '/init.php';

$openid = isset($_GET['openid']) ? $_GET['openid'] : '';
$id = $_GET['id'];

if(empty($openid))
{
    $config  = (@include dirname(dirname(__FILE__)) . '/../etc/config.php');
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $path = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url = $config['apiUrl']."/wechat/bind4Manager?redirect=".urlencode($path);
    header("Location: $url");
}
else
{
    $oManager = new Widget_Manager();
    $oUser = new Hj_UserInfo();

    $managerInfo = $oManager->getRow($id);
    $userInfo = $oUser->getUserByWechat($openid,'user_id,nick_name,manager_id');
    if(!$userInfo['user_id'])
    {
        echo "此微信尚未绑定某个用户，请先注册绑定后再关联管理员";
    }
    else
    {
        if($managerInfo["openid"]=="")
        {
            $managerInfo["openid"] = trim($openid);
            $updateManager = $oManager->update($id,$managerInfo);
            $updateUser = $oUser->updateUser($userInfo['user_id'],['manager_id'=>$id]);
            if($updateManager && $updateUser)
            {
                echo "用户".$userInfo['nick_name']."绑定成功";
            }
            else
            {
                echo "用户".$userInfo['nick_name']."绑定失败";
            }
        }
        elseif($managerInfo["openid"]==trim($openid))
        {
            $managerInfo["openid"] = "";
            $updateManager = $oManager->update($id,$managerInfo);
            $updateUser = $oUser->updateUser($userInfo['user_id'],['manager_id'=>0]);
            if($updateManager && $updateUser)
            {
                echo "用户".$userInfo['nick_name']."解绑成功";
            }
            else
            {
                echo "用户".$userInfo['nick_name']."解绑失败";
            }
        }
        else
        {
            echo "非本人不能操作";
        }
    }

}


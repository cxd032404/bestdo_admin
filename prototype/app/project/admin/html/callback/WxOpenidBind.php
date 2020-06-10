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
    $managerInfo = $oManager->getRow($id);
    if($managerInfo["openid"]=="")
    {
        $managerInfo["openid"] = trim($openid);
        $update = $oManager->update($id,$managerInfo);
        if($update)
        {
            echo "绑定成功";
        }
        else
        {
            echo "绑定失败";
        }
    }
    elseif($managerInfo["openid"]==trim($openid))
    {
        $managerInfo["openid"] = "";
        $update = $oManager->update($id,$managerInfo);
        if($update)
        {
            echo "解绑成功";
        }
        else
        {
            echo "解绑失败";
        }
    }
    else
    {
        echo "非本人不能操作";
    }
}


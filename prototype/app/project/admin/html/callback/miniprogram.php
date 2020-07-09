<?php
echo "here";
test();
function test(){
    echo "666";
    $appid = 'wx52f654781e1775e7';
    $secret = '732c040be5cf8a47de04d93bf2bb4de2';
    $filename = './upload/test.png';
    $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
    //开启session
    session_start();
    // 保存2小时
    $lifeTime = 2 * 3600;
    setcookie(session_name(), session_id(), time() + $lifeTime, "/");
    // echo $url;
    $access_token = $_SESSION['access_token'];
    if(empty($access_token)){
        $access_token_data = getJson($url);
        $access_token = $access_token_data['access_token'];
        $_SESSION['access_token'] = $access_token;
    }
    if(!empty($access_token)){
        $url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token='.$access_token;
        $data['path'] = 'pages/index/index';
        $data['scene'] = 'jobId=222';//(string类型,必须是数字)
        $data['width'] = 430;
        $result = curlPost($url,$data,'POST');
        // p($result);
        $ret = file_put_contents('../upload/test.png', $result, true);
        print_R($ret);
        echo '成功';
    }else{
        echo 'string';
    }
}
function getJson($url,$data=array(),$method='GET'){
    $ch = curl_init();//1.初始化
    curl_setopt($ch, CURLOPT_URL, $url);//2.请求地址
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);//3.请求方式
    //4.参数如下
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    if($method=="POST"){//5.post方式的时候添加数据
        $data = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, true);
}

function curlPost($url,$data,$method){
    $ch = curl_init();   //1.初始化
    curl_setopt($ch, CURLOPT_URL, $url); //2.请求地址
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);//3.请求方式
    //4.参数如下
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//https
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');//模拟浏览器
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER,array('Accept-Encoding: gzip, deflate'));//gzip解压内容
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

    if($method=="POST"){//5.post方式的时候添加数据
        $data = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $tmpInfo = curl_exec($ch);//6.执行

    if (curl_errno($ch)) {//7.如果出错
        return curl_error($ch);
    }
    curl_close($ch);//8.关闭
    return $tmpInfo;
}
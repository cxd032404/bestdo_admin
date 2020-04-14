<?php
session_start();
error_reporting(0);
/**
 * ���������ͱ���
 *
 * @author     ���� 344505721@qq.com
 * @version    1.0, 2012-10-10
 */ 
define('APPID',100); //APPID��ϷID
define('PARTNERID',1); //PARTNERID����ID
define('RETURNTYPE',1); //RETURNTYPE����ֵ��ʽ
define('SERVERID',100001001); //��Ϸ������

//���ϲ��Ի���
if(strstr($_SERVER['SERVER_NAME'],"test.wjyx.com") || strstr($_SERVER['SERVER_NAME'],"test.limaogame.com")){
    define('USER_API','http://usercenter.test.limaogame.com/'); //�û��ӿ�Ĭ������
    define('LOGIN_API','http://login.test.limaogame.com/'); //��¼�ӿ�Ĭ������
    define('PAYMENT_API','http://payment.test.limaogame.com/'); //֧���ӿ�Ĭ������
    define('CONFIG_API','http://config.test.limaogame.com/'); //�ӿ�Ĭ������
    define('COMMON_API','http://common.test.limaogame.com/'); //ͨ�ýӿ�Ĭ������
    
    define('ACT_API','http://event.test.wjyx.com/'); //��ӿ�Ĭ������
    define('PASSPORT_API','http://passport.test.limaogame.com/'); //�û����Ľӿ�Ĭ������    
    define('LUNTAN_API','http://www.test.wjyx.com/board/'); //��̳��ַ
}
//������ʽ����
else if(strstr($_SERVER['SERVER_NAME'],"wjyx.com") || strstr($_SERVER['SERVER_NAME'],"limaogame.com")){
    define('USER_API','http://usercenter.limaogame.com/'); //�û��ӿ�Ĭ������
    define('LOGIN_API','http://login.limaogame.com/'); //��¼�ӿ�Ĭ������
    define('PAYMENT_API','http://payment.limaogame.com/'); //֧���ӿ�Ĭ������
    define('CONFIG_API','http://config.limaogame.com/'); //�ӿ�Ĭ������
    define('COMMON_API','http://common.limaogame.com/'); //ͨ�ýӿ�Ĭ������
    
    define('ACT_API','http://event.wjyx.com/'); //��ӿ�Ĭ������
    define('PASSPORT_API','http://passport.limaogame.com/'); //�û����Ľӿ�Ĭ������    
    define('LUNTAN_API','http://www.wjyx.com/board/'); //��̳��ַ
}

define('MAIL_QUEUE','mail_queue'); //����������ݿ�

//�Ƹ�ͨKEY
define('TENPAY_KEY','028036528b7376b9cefb0470b7bc4e67');

//֧����KEY
define('ALIPAY_KEY','qaqj14j20i31oblu9620q8t42o1gwu9p');

/**
 * ��ȡ��¼�洢��Ϣ
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_login_ajax'))
{
	function get_login_ajax($user_info,$type)
	{
        $ajax = '<script type="text/javascript" src="'.ACT_API.'commentlogin.php?UserId='.$user_info["UserId"].'&UserName='.$user_info['UserName'].'&LoginId='.$user_info['LoginId'].'&type='.$type.'" reload="1"></script>';
        $ajax .= '<script type="text/javascript" src="'.PASSPORT_API.'commentlogin.php?UserId='.$user_info["UserId"].'&UserName='.$user_info['UserName'].'&LoginId='.$user_info['LoginId'].'&type='.$type.'" reload="1"></script>';
        $ajax .= '<script type="text/javascript" src="'.LUNTAN_API.'commentlogin.php?UserId='.$user_info["UserId"].'&UserName='.$user_info['UserName'].'&LoginId='.$user_info['LoginId'].'&type='.$type.'" reload="1"></script>';
        
        return $ajax;
    }
}

/**
 * ��ȡ���ݿ���������
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_db_config'))
{
    /**
     * ishot = 0 ���� 
     * ishot = 1 ����
    */
	function get_db_config($isHost)
	{
        /*�������ݿ�*/
        if($isHost == 1){
            return array(
                            array(
                                  'host' => '192.168.10.11',
                                  'user' => 'user_center',
                                  'password' => 'lm12#$userdb',
                                  'port' => 3306,
                            ),
                        );
        }
                    
        /*�������ݿ�*/
        if($isHost == 0){
            return array(
                    array('host' => '192.168.20.230',
                    	  'user' => 'user',
                    	  'password' => 'limaogame',
                    	  'port' => 3306,
                    ),
                );
        }
    }
}

/**
 * ��ȡ���ݿ�����
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_db_link'))
{
    /**
     * ishot = 0 ���� 
     * ishot = 1 ����
    */
	function get_db_link()
	{
	   $isHost = 0;
       $isWhat = 0;
       
	   if(strstr($_SERVER['SERVER_NAME'],"limaogame.com")){
	       $isHost = 1;
	   }
       
	   $db = get_db_config($isHost);
	    if($isHost == 1){
            $config['hostname'] = $db[$isWhat]['host'];
            $config['username'] = $db[$isWhat]['user'];
            $config['password'] = $db[$isWhat]['password'];
        }
        
	    if($isHost == 0){
	        $config['hostname'] = $db[$isWhat]['host'];
            $config['username'] = $db[$isWhat]['user'];
            $config['password'] = $db[$isWhat]['password'];
	    }
	    
        $config['database'] = "";
        $config['dbdriver'] = "mysql";
        $config['dbprefix'] = "";
        $config['pconnect'] = FALSE;
        $config['db_debug'] = TRUE;
        $config['cache_on'] = FALSE;
        $config['cachedir'] = "";
        $config['char_set'] = "utf8";
        $config['dbcollat'] = "utf8_general_ci";
        
        return $config;
	}
}

/**
 * ����sign��֤
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('set_sign'))
{
	function set_sign($array = array(),$pkey = null)
	{
	    $Signarray = array();
        
	    foreach($array as $k=>$v){         
           if((strlen(trim($v))==0)||(($v==0)&&(is_numeric($v)))){
                unset($array[$k]);
           }else{
                $Signarray[$k] = $v;
           }           
	    }
		ksort($Signarray);
        return md5(implode("|",$Signarray).'|'.$pkey);
	}
}

/**
 * ������sign
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
 if ( ! function_exists('test_sign'))
{
    function test_sign($array = array(),$pkey = null)
    {
        $Signarray = array();
        
        foreach($array as $k=>$v){         
           if((strlen(trim($v))==0)||(($v==0)&&(is_numeric($v)))){
                unset($array[$k]);
           }else{
                $Signarray[$k] = $v;
           }           
        }
    	ksort($Signarray);
        return (implode("|",$Signarray).'|'.$pkey);
    }
}

/**
 * ������ƴ�ӳ�URL
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('splice_url'))
{
	function splice_url($array = array())
	{
	    $url = '';

		foreach($array as $k=>$v){   
           $url .= $k . '=' . $v . '&';
	    }

        return substr($url,0,strlen($url)-1);
	}
}

/**
 * ��ȡ��Ϸ�б�
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_app_list'))
{
	function get_app_list()
	{
	    $array['Time'] = time();
        $array['ReturnType'] = 1;        
        $array['sign'] = set_sign($array,'lm');
        
        $res = splice_url($array);
        
        $return = file_get_contents(app_url('config/app','get.app.list',$res));
        
        return json_decode($return,true);
	}
}

/**
 * ��ȡ��Ϸ�����б�
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_partner_list'))
{
	function get_partner_list($appid)
	{
	    $array['AppId'] = $appid;
	    $array['Time'] = time();
        $array['ReturnType'] = 1;        
        $array['sign'] = set_sign($array,'lm');
        
        $res = splice_url($array);
        
        $return = file_get_contents(app_url('config/partner','get.partner.app.list',$res));
        
        $partner_list = json_decode($return,true);
        
        if($partner_list['return'] == 1){            
            foreach($partner_list['PartnerAppList'] as $k=>$v){
                $return .= "<option value='".$v['PartnerId']."'>".$v['name']."</option>";            
            }
        }
        
        return $return;
	}
}

/**
 * ��ȡ֧����ʽ
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_payment'))
{
    function get_payment()
    {
        $array['Time'] = time();
        $array['ReturnType'] = 1;        
        $array['sign'] = set_sign($array,'lm');
        
        $res = splice_url($array);
        
        $return = file_get_contents(app_url('config/passage','get.passage.list',$res));
        
        return json_decode($return,true);
    }
}

/**
 * ��ȡserverid
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_server_id'))
{
    function get_server_id($AppId , $PartnerId)
    {
        $array['AppId'] = $_POST['AppId'];
        $array['PartnerId'] = $_POST['PartnerId'];
        $array['ReturnType'] = RETURNTYPE;
        $array['Time'] = time();
        $array['sign'] = set_sign($array,'lm');

        $res = splice_url($array);
        $return = json_decode(file_get_contents(app_url('config/server','get.server.list',$res)),true);
        
        if($return['return'] == 1){
            $server_id = $return['ServerList'][0]['ServerId'];
        }else{
            $server_id = SERVERID;
        }
        
        return $server_id;
    }
}

/**
 * ���ýӿں���
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('app_url'))
{
	function app_url($url,$ctl,$ac,$res)
	{
        if(($ctl=="") || ($ac==""))
        {
            $config_api = $url."?$res";

        }
            else
            {
                $config_api = $url."?ctl=$ctl&ac=$ac&$res";

            }

	    return $config_api;
	}
}

/**
 * ��ȡ�û������Ϣ
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_user_communication'))
{
	function get_user_communication($user_info)
	{
	    if(empty($user_info)){
	       header("Location:http://my.test.com/?c=login");
           exit;
	    }
        //��ȡ�û������Ϣ       
        $arrayC['UserId'] = $user_info['UserId'];
        $arrayC['Time'] = time();
        $arrayC['PartnerId'] = PARTNERID;
        $arrayC['ReturnType'] = RETURNTYPE;
        $arrayC['sign'] = set_sign($arrayC,'lm');

        $resC = splice_url($arrayC);        
        $user_communication_info = json_decode(file_get_contents(app_url('user','get.user.communication',$resC)),true);
        
        foreach($user_communication_info['UserCommunicationInfo'] as $k=>$v){
            $user_info[$k] = $v;
        }
        
        return $user_info;
	}
}

/**
 * ��ȡ�û�������Ϣ
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_user_base'))
{
	function get_user_base($user_info)
	{  
	   if(empty($user_info)){	       
	       header("Location:http://my.test.com/?c=login");
           exit;
	    }        
        //��ȡ�û�������Ϣ
        $array['UserId'] = $user_info['UserId'];
        $array['Time'] = time();
        $array['PartnerId'] = PARTNERID;
        $array['ReturnType'] = RETURNTYPE;
        $array['sign'] = set_sign($array,'lm');
        
        $res = splice_url($array);        
        $user_base_info = json_decode(file_get_contents(app_url('user','get.user.base.info',$res)),true);
        
        foreach($user_base_info['UserInfo'] as $k=>$v){
            $user_info[$k] = $v;
        }
        
        return $user_info;
	}
}

/**
 * ��ȡ�ͻ�������IP
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_onlineip'))
{
    function get_onlineip(){
        $ip=false;
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
                
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }
}

/**
 * ��ȡ�û��ܱ���
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_answer'))
{
    function get_answer($user_info){
        //��ȡ�û�������Ϣ
        $array['UserId'] = $user_info['UserId'];
        $array['Time'] = time();
        $array['PartnerId'] = PARTNERID;
        $array['ReturnType'] = RETURNTYPE;
        $array['sign'] = set_sign($array,'lm');
        
        $res = splice_url($array);        
        return json_decode(file_get_contents(app_url('user','get.user.answer',$res)),true);
    }
}

/**
 * ��ȡ��������
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_order_info'))
{
    function get_order_info($order_id){
        $array['OrderId'] = $order_id;
        $array['ReturnType'] = RETURNTYPE;
        $array['Time'] = time();
        $array['sign'] = set_sign($array,'lm');        
        $res = splice_url($array);
	    return json_decode(file_get_contents(app_url('order','get.order',$res)),true);
    }
}

/**
 * ��ȡ�û�����
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_user_mail'))
{
    function get_user_mail($user_info){
        $array['UserId'] = $user_info['UserId'];
        $array['PartnerId'] = PARTNERID;
        $array['Time'] = time();
        $array['ReturnType'] = RETURNTYPE;
        $array['sign'] = set_sign($array,'lm');
        
        $res = splice_url($array);        
        $user_mail = json_decode(file_get_contents(app_url('user','get.user.mail',$res)),true);
        
        return $user_mail;
    }
}

/**
 * ��֤�û��Ƿ���ڣ���ȡ�û�ID
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_user_id'))
{
    function get_user_id($UserName){
        $array['Time'] = time();
	    $array['UserName'] = $UserName;
        $array['PartnerId'] = PARTNERID;    
        $array['ReturnType'] = RETURNTYPE;        
        $array['sign'] = set_sign($array,'lm');
        
        $res = splice_url($array);

	    $return = file_get_contents(app_url('user','check.user.exist',$res));        
        return $return;
    }
}

/**
 * ��֤�����Ƿ���ڣ���ȡ�û�ID
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_uid_femail'))
{
    function get_uid_femail($UserMail){
        $array['Time'] = time();
	    $array['UserMail'] = $UserMail;  
        $array['ReturnType'] = RETURNTYPE;        
        $array['sign'] = set_sign($array,'lm');
        
        $res = splice_url($array);

	    $return = file_get_contents(app_url('user','check.mail.exist',$res));        
        return $return;
    }
}

/**
 * �û��齱
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('UseLoto'))
{
    function UseLoto($user_info,$lotoid) {
        $array['UserId'] = $user_info['UserId'];
        $array['LotoTime'] = time();
        $array['LotoId'] = $lotoid;
        $array['ReturnType'] = RETURNTYPE;
        $array['sign'] = set_sign($array,'loto');
        
        $res = splice_url($array);        
        $return = json_decode(file_get_contents(app_url('loto','loto',$res)),true);
        
        return $return;
    }
}

/**
 * �Ƿ�μӹ��齱
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('getLotoLog'))
{
    function getLotoLog($user_info,$lotoid) {
        $array['UserId'] = $user_info['UserId'];
        $array['LotoTime'] = time();
        $array['LotoId'] = $lotoid;
        $array['ReturnType'] = RETURNTYPE;
        $array['sign'] = set_sign($array,'loto');
        
        $res = splice_url($array);        
        $return = json_decode(file_get_contents(app_url('loto','get.loto.log',$res)),true);
        
        return $return;
    }
}

/**
 * �齱��Ϣ
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('GetLotoInfo'))
{
    function GetLotoInfo($lotoid = 1) {
        $array['Time'] = time();
        $array['LotoId'] = $lotoid;
        $array['ReturnType'] = RETURNTYPE;
        $array['sign'] = set_sign($array,'loto');
        
        $res = splice_url($array);        
        $return = json_decode(file_get_contents(app_url('loto','get.loto.info',$res)),true);
        
        return $return;
    }
}

/**
 * �콱
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('GetPrize'))
{
    function GetPrize($user_info, $lotoinfo ,$lotoid = 1) {
        $array['UserId'] = $user_info['UserId'];
        $array['GetPrizeTime'] = time();
        $array['LotoId'] = $lotoid;
        $array['LotoLogId'] = $lotoinfo['LotoLogId'];
        $array['ReturnType'] = RETURNTYPE;
        $array['sign'] = set_sign($array,'loto');
        
        $res = splice_url($array);        
        $return = json_decode(file_get_contents(app_url('action','get.prize',$res)),true);
        
        return $return;
    }
}

/**
 * ��ȡ��������
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('getResearchList'))
{
    function getResearchList($ResearchId) {
        $array['ResearchId'] = $ResearchId;
        $array['Time'] = time();
        $array['ReturnType'] = RETURNTYPE;
        $array['sign'] = set_sign($array,'lm');
        
        $res = splice_url($array);        
        $return = json_decode(file_get_contents(app_url('research','get.research',$res)),true);
        
        return $return;
    }
}

/**
 * ������лش�
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('AnswerResearch'))
{
    function AnswerResearch($userinfo,$answerinfo,$Research) {        
        $array['ResearchId'] = $Research;
        $array['UserId'] = $userinfo["UserId"];
        $array['AnswerTime'] = time();
        $array['Answer'] = json_encode($answerinfo);
        $array['ReturnType'] = RETURNTYPE;
        $array['sign'] = set_sign($array,'lm');
        $array['Answer'] = urlencode($array['Answer']);
        
        $res = splice_url($array);
        $return = file_get_contents(app_url('research','answer.research',$res));
        
        return $return;
    }
}

/**
 * �Զ�ƴ�������ѡ��
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('AutoResearchType'))
{
    function AutoResearchType($array,$contr,$GetPrize,$isnum = true,$type = "html") {
        $html = array();
        $javascript = "";
        $i = 0;
        $javascript .= "var error = '';"."\n";
        foreach($array["QuestionInfo"] as $k=>$v){
            $answer = explode("/",$v["Answer"]);
                        
            if($isnum){
                $html[$i]["title"] = '<li class="two_span"><span class="wj_wt">'.($i+1)."��".$v["QuestionContent"].'</span>';
            }else{
                $html[$i]["title"] = '<li class="two_span"><span class="wj_wt">'.$v["QuestionContent"].'</span>';
            }
            
            if($v["AnswerType"] == "checkbox"){
                $html[$i]["comment"] = '<span class="wj_xuan chklist">';
            }elseif($v["AnswerType"] == "radio"){
                $html[$i]["comment"] = '<span class="wj_xuan radiolist">';
            }else{
                $html[$i]["comment"] = '<span class="wj_xuan">';
            }
            
            foreach($answer as $k1=>$v1){
                $name = "";
                if($v["AnswerType"] == "checkbox" || $v["AnswerType"] == "radio"){
                    $name = $v1;
                }
                
                if($v["AnswerType"] == "text"){
                    $v1 = "";
                }
                
                if($v1 == "#other#" && $v["AnswerType"] == "checkbox"){
                    $html[$i]["comment"] .= "<input type='".$v["AnswerType"]."' name='".$v["ResearchId"]."_".$v["QuestionId"]."[]' othername='".$v["ResearchId"]."_".$v["QuestionId"]."' class='other' value='".($i+1)."��".$v["QuestionContent"]."' /><label>����</label>
                    <input type='text' id='other_".$v["ResearchId"]."_".$v["QuestionId"]."' class=\"qita\" />";
                }else{
                    if($v["AnswerType"] == "radio" || $v["AnswerType"] == "checkbox"){
                        $html[$i]["comment"] .= "<input type='".$v["AnswerType"]."' name='".$v["ResearchId"]."_".$v["QuestionId"]."[]' value='".$v1."' /><label>$name</label>";
                    }else{
                        $html[$i]["comment"] .= "<input type='".$v["AnswerType"]."' name='".$v["ResearchId"]."_".$v["QuestionId"]."' value='".$v1."' class=\"qita\" style='margin:0 0 0 10px;' /><label>$name</label>";
                    }                    
                }
            }
            
            $html[$i]["comment"] .= '</span></li>';
            
            //�õ������µĴ𰸶���
            $javascript .= "var input_".$v["ResearchId"]."_".$v["QuestionId"]." = $(\"input[name^='".$v["ResearchId"]."_".$v["QuestionId"]."']\");"."\n";
            
            //������ѡ���Ϊѡ�����            
            $javascript .= "var error_".$v["ResearchId"]."_".$v["QuestionId"]." = 0;\n";
            
            /* ��֤�Ƿ�ѡ���ش������JS */
            
            //�ж������ѡ������          
            if($v["AnswerType"] == "checkbox" || $v["AnswerType"] == "radio"){
                $javascript .= "for(var i=0;i<input_".$v["ResearchId"]."_".$v["QuestionId"].".length;i++){"."\n";
                $javascript .= "\tif(input_".$v["ResearchId"]."_".$v["QuestionId"]."[i].checked != true){"."\n";
                $javascript .= "\t\terror_".$v["ResearchId"]."_".$v["QuestionId"]."++;"."\n";
                $javascript .= "\t}"."\n";
                $javascript .= "}"."\n";
            }else if($v["AnswerType"] == "text"){
                $javascript .= "\tif(input_".$v["ResearchId"]."_".$v["QuestionId"]."[0].value == ''){"."\n";
                $javascript .= "\t\terror_".$v["ResearchId"]."_".$v["QuestionId"]."++;"."\n";
                $javascript .= "\t}"."\n";
            }
            
            //��ʾδ�ش��ѡ����Ϣ
            $javascript .= "if(error_".$v["ResearchId"]."_".$v["QuestionId"]." == input_".$v["ResearchId"]."_".$v["QuestionId"].".length){"."\n";
            $javascript .= "\terror+='".($i+1)."��".$v["QuestionContent"]."'+\"\\n\";"."\n";
            $javascript .= "}"."\n";
            $i++;
        }
        
        $javascript .= "if(error != ''){"."\n";
        $javascript .= "\terror+=\"\\n\"+'��ѡ��ͻش������ϵ�����!';"."\n";
        $javascript .= "\talert(error);return false;"."\n";
        $javascript .= "}"."\n";
        
        //�ж����������Ƿ�ѡ���ش�
        $javascript .= "var error = '';"."\n";
        $javascript .= "var other = $('.other')"."\n";
        $javascript .= "for(var i=0;i<other.length;i++){"."\n";
        $javascript .= "\tvar orhername = 'other_'+other[i].attributes['othername'].nodeValue;";
        $javascript .= "\tif(other[i].checked == true && $('#'+orhername).val() == ''){";
        $javascript .= "\terror += other[i].value+'��ѡ��������������δ�ش�'+\"\\n\";"."\n";
        $javascript .= "\t}else{"."\n";
        $javascript .= "\t\tother[i].value = $('#'+orhername).val();"."\n";
        $javascript .= "\t}"."\n";
        $javascript .= "}"."\n";        
        $javascript .= "if(error != ''){alert(error);return false;}"."\n";
        
        if($type == "html"){
            $javascript .= '$.post("'.site_url("d=$contr&c=index&m=$GetPrize").'", $("#'.$array["ResearchInfo"]["ResearchId"].'_form").serialize() ,function(msg){
                            $.dialog({id:"GameActivationDialog"}).close();
                            $.dialog({
                                id: "GameActivationDialog",
                                max: false,
                                min: false,
                                fixed: true,
                                lock: true,
                                drag: false,
                                content: msg,
                                title : false
                            });                        
                        });';
        }
        
        if($type == "jsonp"){
            $javascript .= 'var inputstr = "";';
            $javascript .= 'var form_input = $("#'.$array["ResearchInfo"]["ResearchId"].'_form input");';
            $javascript .= 'var form_count = $("#'.$array["ResearchInfo"]["ResearchId"].'_form input").length;';
            $javascript .= 'for(var i=0;i<form_count;i++){      
                                if((form_input[i].type == "checkbox" || form_input[i].type == "radio") && form_input[i].checked == true){
                                    inputstr += "&"+form_input[i].name+"="+form_input[i].value;
                                }
                                
                                if(form_input[i].type == "text" && form_input[i].className != "qita xuan"){
                                    inputstr += "&"+form_input[i].name+"="+form_input[i].value;
                                }
                            }';
            $javascript .= '$.ajax({
                                type: "get",
                                dataType: "jsonp",
                                url: "'.site_url("d=$contr&c=index&m=$GetPrize&callback=func_".$array["ResearchInfo"]["ResearchId"]."_form").'"+inputstr,
                                jsonp:"func_'.$array["ResearchInfo"]["ResearchId"].'_form"
                            });';
        }        
        
        $onclick = "$('.other').click(function(){
            var othername = 'other_'+$(this).attr('othername');
            if($(this).attr('checked') == 'checked'){
                $('#'+othername).removeAttr('disabled');
            }else{
                $('#'+othername).attr('disabled','disabled');
                $('#'+othername).val('');
            }
        });";        
                
        $return['html'] = $html; 
        $return['javascript'] = $javascript;
        $return['onclick'] = $onclick;
        
        $ResearchHtml = '
        <form id="'.$array["ResearchInfo"]["ResearchId"].'_form" method="post">
        <ul>';
        foreach($return['html'] as $k=>$v){
            $ResearchHtml .= $v["title"];
            $ResearchHtml .= $v["comment"];
        }
        $ResearchHtml .= '<li class="wj_btn" onclick="check_'.$array["ResearchInfo"]["ResearchId"].'()"><center><a href="javascript:void(0)"></a></center></li>'."\n";
        $ResearchHtml .= '</ul>'."\n";
        $ResearchHtml .= '</form>'."\n";
        $ResearchHtml .= '<script type="text/javascript">'."\n";
        $ResearchHtml .= 'function check_'.$array["ResearchInfo"]["ResearchId"].'(){'."\n";
        $ResearchHtml .= $return['javascript'];
        $ResearchHtml .= '}'."\n";
        $ResearchHtml .= $return['onclick'];
        $ResearchHtml .= 'function func_'.$array["ResearchInfo"]["ResearchId"].'_form(msg)
                            {
                                wj_close();    
                                if(msg == 1){
                                    getNextResearchbyone();
                                }else if(msg == 2){
                                    getNextResearchbytwo();
                                }   
                            }';
        $ResearchHtml .= '</script>'."\n";
        
        return $ResearchHtml;
    }
}

/**
 * ��ȡ֧����ʽ����
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_passage_info'))
{
    function get_passage_info($passage_id){
        $array['Time'] = time();
	    $array['PassageId'] = $passage_id;
        $array['ReturnType'] = RETURNTYPE;        
        $array['sign'] = set_sign($array,'lm');
        
        $res = splice_url($array);

	    $return = json_decode(file_get_contents(app_url('config/passage','get.passage.info',$res)),true);        
        return $return;
    }
}

/**
 * ��ȡ�û������б�����
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_user_order_list'))
{
    function get_user_order_list($config = array()){
        $array['UserId'] = $config["UserId"];
	    $array['StartDate'] = isset($config["StartDate"])?$config["StartDate"]:(date("Y-m-d",strtotime('-3 months')));
        $array['EndDate'] = isset($config["EndDate"])?$config["EndDate"]:date("Y-m-d",time());
        $array['AppId'] = isset($config["AppId"])?$config["AppId"]:0;
        $array['PartnerId'] = isset($config["PartnerId"])?$config["PartnerId"]:0;
        $array['ServerId'] = isset($config["ServerId"])?$config["ServerId"]:0;
        $array['OrderStatus'] = isset($config["OrderStatus"])?$config["OrderStatus"]:4;
        $array['Page'] = isset($config["Page"])?$config["Page"]:1;
        $array['PageSize'] = isset($config["PageSize"])?$config["PageSize"]:13;
        $array['Time'] = time();
        $array['ReturnType'] = RETURNTYPE;      
        $array['sign'] = set_sign($array,'lm');
        
        $res = splice_url($array);
        
	    $return = json_decode(file_get_contents(app_url('order','get.user.order.list',$res)),true);        
        return $return;
    }
}

/**
 * ��ȡFAQ�����б�
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_faq_type_list'))
{
    function get_faq_type_list() {
        $array['Time'] = time();
        $array['ReturnType'] = RETURNTYPE;
        $array['sign'] = set_sign($array,'lm');
        
        $res = splice_url($array);
        $return = json_decode(file_get_contents(app_url('config/faq','get.faq.type.list',$res)),true);
        return $return;
    }
}

/**
 * ��ȡFAQ�б�
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_faq_list'))
{    
    function get_faq_list($FaqTypeId = 0 , $KeyWord = "" , $per_page = 1) {
        $array['FaqTypeId'] = $FaqTypeId;
        $array['KeyWord'] = $KeyWord;
        $array['Start'] = ($per_page < 1)?($per_page*10):($per_page-1)*10;
        $array['Count'] = ($per_page == 0)?0:10;
        $array['Time'] = time();
        $array['ReturnType'] = RETURNTYPE;
        $array['sign'] = set_sign($array,'lm');
        $array['KeyWord'] = urlencode($array['KeyWord']);
        
        $res = splice_url($array);
        $return = json_decode(file_get_contents(app_url('config/faq','get.faq.list',$res)),true);
        return $return;
    }
}

/**
 * ��ȡ�����֤��
 *
 * @access	private
 * @param	array
 * @param	bool
 * @return	string
 */
if ( ! function_exists('get_Vcode'))
{
    function get_Vcode(){
        
        $srand = rand(0,3);        
        $one = rand(1,9);
        $two = rand(1,9);
            
        if($one > $two && $one != $two && $srand == 1){
            $return['topic'] = "$one - $two = ?";
            $return['answer'] = $one-$two;
        }else if($srand == 2){
            $return['topic'] = "$one * $two = ?";
            $return['answer'] = $one*$two;
        }else if($srand == 3){
            $answer = rand(1,9);
            
            $return['topic'] = ($answer*$one)." / $one = ?";
            $return['answer'] = $answer;
        }else{
            $return['topic'] = "$one + $two = ?";
            $return['answer'] = $one+$two;
        }
                
        return $return;
    }
}

if ( ! function_exists('getHttpRes'))
{
    function getHttpRes($url, $input_charset = '', $time_out = "300") {
    	$urlarr     = parse_url($url);
    	$errno      = "";
    	$errstr     = "";
    	$transports = "";
    	$responseText = "";
    	if($urlarr["scheme"] == "https") {
    		$transports = "ssl://";
    		$urlarr["port"] = "443";
    	} else {
    		$transports = "tcp://";
    		$urlarr["port"] = "80";
    	}
    
    	$fp=@fsockopen($transports . $urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
    	if(!$fp) {
    		die("ERROR: $errno - $errstr<br />\n");
    	} else {
    		if (trim($input_charset) == '') {
    			fputs($fp, "POST ".$urlarr["path"]." HTTP/1.1\r\n");
    		}
    		else {
    			fputs($fp, "POST ".$urlarr["path"].'?_input_charset='.$input_charset." HTTP/1.1\r\n");
    		}
    		fputs($fp, "Host: ".$urlarr["host"]."\r\n");
    		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
    		fputs($fp, "Content-length: ".strlen($urlarr["query"])."\r\n");
    		fputs($fp, "Connection: close\r\n\r\n");
    		fputs($fp, $urlarr["query"] . "\r\n\r\n");
    		while(!feof($fp)) {
    			$responseText .= @fgets($fp, 1024);
    		}
    		fclose($fp);
    		$responseText = trim(stristr($responseText,"\r\n\r\n"),"\r\n");
    		
    		return $responseText;
    	}
    }
}
/* End of file common_helper.php */
/* Location: ./application/helpers/common_helper.php */
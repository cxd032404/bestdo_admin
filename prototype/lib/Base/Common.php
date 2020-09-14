<?php
/**
 * 通用类
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Common.php 15499 2014-12-18 09:16:24Z 334746 $
 */


class Base_Common
{
    /**
     * 配置数据
     * @var array
     */
	public static $config = array(
		'autoload' => true,
		'private_key' => '$s3#4f%6j&9s^',
	    'timezone' => 'Asia/Shanghai',
	    'root_dir' => '',
	    'tpl_dir' => '',
	    'var_dir' => '',
	    'file_dir' => '',
	    'file_url' => '',
	    'exception' => true,
	    'config_file' => '',
	    'database_file' => '',
	    'table_file' => '',
	);

	/**
	 * 环境初始化
	 * @param array $config
	 * @return void
	 */
	public static function init(array $config = null)
	{
		if (is_array($config)) {
			self::$config = array_merge(self::$config, $config);
		}

		/**
		 * 设置自动载入函数
		 */
		if (self::$config['autoload']) {
		    if (function_exists('__autoload')) {
		        spl_autoload_register('__autoload');
		    }

		    spl_autoload_register(array('Base_Common', 'autoload'));
		}

		/**
		 * GPC
		 */
		if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
			$_GET = self::stripslashesRecursive($_GET);
			$_POST = self::stripslashesRecursive($_POST);
			$_COOKIE = self::stripslashesRecursive($_COOKIE);

			reset($_GET);
			reset($_POST);
			reset($_COOKIE);
		}

		/**
		 * 设置异常抛出
		 */
		set_exception_handler(array('Base_Common', 'exceptionHandle'));

		/**
		 * 设置时区
		 */
		date_default_timezone_set(self::$config['timezone']);
	}
	
	/**
	 * 通过curl命令执行请求
	 * @param string $url
	 */
	public static function execCurl($url)
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return @file_get_contents($url);
		}

		$handle = popen('curl "' . $url . '" -L', 'r');
		$str = @stream_get_contents($handle);
		pclose($handle);

		return $str;
	}

	/**
	 * 递归去除转义
	 * @param mixed $value
	 * @return mixed
	 */
	public static function stripslashesRecursive($value)
	{
	    $value = is_array($value) ?
	        array_map(array('Base_Common','stripslashesRecursive'), $value) :
	        stripslashes($value);
	    return $value;
	}

	/**
	 * 递归添加转义
	 * @param mixed $value
	 * @return mixed
	 */
	public static function addslashesRecursive($value)
	{
	    $value = is_array($value) ?
	        array_map(array('Base_Common','addslashesRecursive'), $value) :
	        stripslashes($value);
	    return $value;
	}

	/**
	 * 自动载入方法
	 * @param string $class
	 * @return void
	 */
	public static function autoload($class)
	{
	   	//echo str_replace('_', '/', $class) . '.php<br>';
	    include_once str_replace('_', '/', $class) . '.php';
	}

	/**
	 * 异常处理
	 * @param $e
	 * @return void
	 */
	public static function exceptionHandle($e)
	{
		@ob_end_clean();
		ob_start();
		header('Content-Type: text/html; charset=utf-8', true);

		if (self::$config['exception']) {

			$code = $e->getCode();
			if (is_numeric($code) && $code > 200) {
				Base_Controller_Response_Http::setStatus($code);
			}

			echo '<pre>';
			echo nl2br($e->getMessage());
			echo "\n\n";
			//print_r($e);
		} else {
			self::error($e);
		}

		exit;
	}

	/**
	 * 错误
	 * @param object $e
	 * @return void
	 */
	public static function error($e)
	{
		$isException = is_object($e);

		if ($isException) {
			$code = $e->getCode();
			$message = $e->getMessage();
		} else {
			$code = $e;
		}

		if ($isException && $e instanceof Base_Db_Exception) {
			$code = 500;
			$message = 'Database Server Error';
		} else {
			switch ($code) {
				case 500:
					$message = 'Internal Server Error!';
					break;
				case 404:
					$message = 'Not Found!';
					break;
				case 403:
					$message = 'Forbidden';
					break;
				default:
					$message = 'Error!';
			}
		}

		if (is_numeric($code) && $code > 200) {
			Base_Controller_Response_Http::setStatus($code);
		}

		$message = nl2br($message);

		print <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{$code}</title>

    <style type="text/css">
        body {
            background: #f7fbe9;
            font-family: "Lucida Grande","Lucida Sans Unicode",Tahoma,Verdana;
        }

        #error {
            background: #333;
            width: 360px;
            margin: 0 auto;
            margin-top: 100px;
            color: #fff;
            padding: 10px;

            -moz-border-radius-topleft: 4px;
            -moz-border-radius-topright: 4px;
            -moz-border-radius-bottomleft: 4px;
            -moz-border-radius-bottomright: 4px;
            -webkit-border-top-left-radius: 4px;
            -webkit-border-top-right-radius: 4px;
            -webkit-border-bottom-left-radius: 4px;
            -webkit-border-bottom-right-radius: 4px;

            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        h1 {
            padding: 10px;
            margin: 0;
            font-size: 36px;
        }

        p {
            padding: 0 20px 20px 20px;
            margin: 0;
            font-size: 12px;
        }

        img {
            padding: 0 0 5px 260px;
        }
    </style>
</head>
<body>
    <div id="error">
        <h1>{$code}</h1>
        <p>{$message}</p>
    </div>
</body>
</html>

EOF;
	}

	/**
	 * 转换文件大小
	 * @param integer $filesize
	 * @return string
	 */
    public static function humanSize($filesize)
    {
        if($filesize >= 1073741824)  {
            return round($filesize / 1073741824 * 100) / 100 . ' GB';
        } elseif($filesize >= 1048576) {
            return round($filesize / 1048576 * 100) / 100 . ' MB';
        } elseif($filesize >= 1024) {
            return round($filesize / 1024 * 100) / 100 . ' KB';
        } else {
            return $filesize . ' Bytes';
        }
    }

    /**
     * 模板调用
     * @param $tpl
     * @return string
     */
    public static function tpl($tpl)
    {
        return Base_Template::factory($tpl)->get();
    }

	/**
	 * 分页导航
	 * @param integer $count 记录数
	 * @param string $urlmod url地址模板
	 * @param integer $page 当前页码
	 * @param integer $pagesize 每页包含记录数
	 * @param integer $size 页码数目
	 * @param integer $maxpage 最大页码
	 * @param string $prevWord 上一页
	 * @param string $nextWord 下一页
	 * @return string
	 */
	public static function multi($count, $urlmod, $page, $pagesize = 20, $size = 10, $maxpage = 0, $prevWord = '&laquo;', $nextWord = '&raquo;')
	{
		$multi = '';
		$holder = '~page~';

		if ($count > $pagesize) {
			$offset = ceil($size / 2) - 1;
			$pages = max(1, ceil($count / $pagesize));
			if ($maxpage > 0) {
				$pages = min($pages, $maxpage);
			}
			$page = min($page, $pages);

			if ($size > $pages) {
				$from = 1;
				$to = $pages;
			} else {
				$from = $page - $offset;
				$to = $from + $size - 1;
				if( $from < 1) {
					$to = $page + 1 - $from;
					$from = 1;
					if($to - $from < $size)
					{
						$to = $size;
					}
				} elseif($to > $pages) {
					$from = $pages - $size + 1;
					$to = $pages;
				}
			}

			if ($page > 1) {
				$multi .= '<a href="' . str_replace($holder, $page - 1, $urlmod)
				. '" title="Prev">' . $prevWord . '</a> ';
			}
			if ($page - $offset > 1 && $pages > $size) {
				$multi .= ' <a href="' . str_replace($holder, 1, $urlmod) . '">1</a> ';
				if($page - $offset > 2) {
					$multi .= ' <a href="' . str_replace($holder, 2, $urlmod) . '">2</a> ';
				}
				$multi .= '<span>...</span>';
			}

			for ($i = $from; $i <= $to; $i++) {
				$multi .= $i == $page ? '<strong>' . $i . '</strong>'
				: ' <a href="' . str_replace($holder, $i, $urlmod) . '">' . $i . '</a> ';
			}

			if ($to < $pages) {
				$multi .= '<span>...</span>';

				if ($to < $pages - 1) {
					$multi .= ' <a href="' . str_replace($holder, $pages - 1, $urlmod)
					.'">'. ($pages - 1) . '</a> ';
				}
				$multi .= ' <a href="' . str_replace($holder, $pages, $urlmod) . '">' . $pages.'</a> ';

			}
			if ($page < $pages) {
				$multi .= ' <a href="' . str_replace($holder, $page + 1, $urlmod)
				. '" title="Next">' . $nextWord . '</a> ';
			}
		}

		return $multi ? '<div class="pages">' . $multi . ' 记录总数：'.$count.' </div>' : '';
	}

	/**
	 * 获取文件名后缀
	 * @param string $fileName
	 * @return string
	 */
	public static function fileSuffix($fileName)
	{
		return trim(substr(strrchr($fileName, '.'), 1, 8));
	}

	/**
	 * 获取当前url地址
	 */
	public static function getCurrentUrl()
	{
	    return $_SERVER["REQUEST_URI"];
	}
	/**
	 * 生成数据库查询用的字段列
	 *
	 * 别名＝>列运算符
	 * @param array $fields
	 * @return string
	 */
	public static function getSqlFields($fields)
	{
		foreach($fields as $key => $value)
		{
			if(!is_int($key))
			{
				$fields[$key] = $value." as ".$key;
			}
		}
		$fields = implode(',',$fields);
		return $fields;
	}
	/**
	 * 生成数据库查询用的条件
	 *
	 * @param array $whereCondition
	 * @return string
	 */
	public static function getArrList($Array)
	{
		if(is_array($Array))
		{
			foreach($Array as $key => $value)
			{
				
				$t[$key]=$key;
			}
			$text = implode(",",$t);
		}
		else
		{
			$text = 0; 	
		}
		return $text;
	}

	/**
	 * 生成数据库查询用的条件
	 *
	 * @param array $whereCondition
	 * @return string
	 */
	public static function getSqlWhere($whereCondition)
	{
		foreach($whereCondition as $key => $value)
		{
			if($value=='')
			{
				unset($whereCondition[$key]);
			}
			else
			{
				$t = explode(" ",trim($value));
                if($t[0] != "or")
                {
                    $whereCondition[$key] = 'and '.$value;
                }
                else
                {
                    $whereCondition[$key] = $value;
                }
			}
		}
		$where = implode(' ',$whereCondition);
		return $where;
	}

	static public function Array2CSVAction($array)
	{
		/***
		 * 导出到excel的CSV格式
		 */
		//输出列头
		foreach($array as $k=>$v)
		{
			foreach($v as $k2=>$v2)
			{
				echo iconv ( 'UTF-8', 'GBK', "$k2\t");
			}
			break;
		}
		echo "\n";

		//输出正文
		foreach($array as $k=>$v)
		{
			foreach($v as $k2=>$v2)
			{
				echo iconv ( 'UTF-8', 'GBK', "\"$v2\"\t");
			}
			echo "\n";
		}
	}


	static public function Array2CSVNonHeaderAction($array)
	{
		/***
		 * 导出到excel的CSV格式
		 */

		foreach($array as $k=>$v)
		{
			foreach($v as $k2=>$v2)
			{
				echo iconv ( 'UTF-8', 'GBK', "\"$v2\"\t");
			}
			echo "\n";
		}
	}

	/**
	 * 生成数据库查询用的groupby条件
	 *
	 * @param array $group_fields
	 * @return string
	 */
	public static function getGroupBy($group_fields)
	{
		$return = "";
		if(is_array($group_fields))
		{
			$return = implode(",",$group_fields);
			$return = " group by ".$return;
		}
		return $return;
	}
	/**
	 * 生成带参数的查询连接
	 *
	 * @param array $Params
	 * @param $mod
	 * @param $ctl
	 * @param $action	 
	 * @return string
	 */
	public static function getUrl($mod,$ctl,$action = "index",$p = [])
	{
	    $Params['mod'] = $mod?$mod:"";
		$Params['ctl'] = $ctl?$ctl:"";
		$Params['ac'] = $action?$action:"index";
		$Params = array_merge($Params,$p);
		if(count($Params))
		{
		    foreach($Params as $key => $value)
			{
				if(strlen($value)>0)
				{
					if(!in_array($key,array('ctl','ac')))
					{
						$pArr[] = $key . "=" . urlencode($value);

					}
					else
					{
						$pArr[] = $key . "=" . ($value);
					}
				}
			}
			if(!empty($pArr))
			{
				$P = implode($pArr,"&");
			}
		}
		return "?".$P;
	}
	
	//push到数据中心
	static public function pputHttpSQS($name, $data, $charset='utf-8')
	{
		$httpsqs_host = (include __APP_ROOT_DIR__ . 'etc/httpsqs.php');
		foreach ($httpsqs_host as $v)
		{
			$host=$v['host'];
			$port=$v['port'];
			if(self::postHttpSQS($host,$port,$name, $data, $charset))
				break;
		}
	}
	
	static public function postHttpSQS($host, $port, $name, $data, $charset='utf-8')
	{
		$context['http'] = array
		(
			'method' => 'POST',
			'timeout' => 1,
			'content' => json_encode($data),
		);
		$result=@file_get_contents("http://$host:$port/?charset=".$charset."&name=".$name."&opt=put",
			false, stream_context_create($context));
		if ($result == "HTTPSQS_PUT_OK")
			return true;
		return false;
	}
	//根据用户名获取在PASSPORT分表结构中的位置
	static public function getUserDataPositionByName($username)
	{
		$arr = array();		
		$username = strtolower($username);
		$m = md5($username);
		$arr['db_fix'] = (substr($m,0,1));
		$arr['tb_fix'] = (substr($m,1,1));
		$arr['fix'] = hexdec($arr['db_fix'])*16+hexdec($arr['tb_fix']);
		return $arr;
	}
	//根据用户ID获取在PASSPORT分表结构中的位置
	static public function getUserDataPositionById($userid)
	{
		$UserPostionFix = substr($userid,-3);
		$arr = array();		
//		$username = strtolower($username);
//		$m = md5($username);
		$arr['tb_fix'] = fmod($UserPostionFix,16);
		$arr['db_fix'] = intval($UserPostionFix/16);
		$arr['fix'] = $arr['db_fix']*16+$arr['tb_fix'];
		$arr['tb_fix'] = dechex($arr['tb_fix']);
		$arr['db_fix'] = dechex($arr['db_fix']);
		return $arr;
	}
	//根据在PASSPORT分表结构中的位置获得表名
	public function getUserTable($table,$Position)
	{
		$table_name = Base_Widget::getDbTable($table);
		$table_arr = explode('.',$table_name);
		$StatArr['db'] = $table_arr[0]."_".$Position['db_fix'];
		$StatArr['tb'] = $table_arr[1]."_".$Position['tb_fix'];
		$table_name = implode(".",$StatArr);
		return $table_name;
	}
	
	//处理格式化的递进数组结构
	public function arr_process($text)
	{
		$text_arr = explode("_",$text);
		foreach($text_arr as $key => $value)
		{
			$text_arr_2 = explode(",",$value);
			$arr[$text_arr_2[0]] = 	$text_arr_2[1];
		}
		krsort($arr);
		return $arr;
	}
	
	//根据数据分层结构获得数据所在的层级
	public function get_level($num,$rateArr)
	{
		krsort($rateArr);
		foreach($rateArr as $key => $rate) {
			if($num > $key) {
					$rate = $rate?$rate:0;
					break;
			}
		}
		return $key;
	}
	public function hex2bin($hexdata) 
	{
	    $bindata = '';
	    for($i=0; $i < strlen($hexdata); $i += 2) {
	        $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
	    }
	    return $bindata;
	}
	public function strToHex($string)  
	{  
      $hex="";  
      for   ($i=0;$i<strlen($string);$i++)  
      $hex.=dechex(ord($string[$i]));  
      $hex=strtoupper($hex);  
      return   $hex;  
  }  
  public function hexToStr($hex)  
  {  
    $string="";  
    for   ($i=0;$i<strlen($hex)-1;$i+=2)  
    $string.=chr(hexdec($hex[$i].$hex[$i+1]));  
    return   $string;  
	}
	//对输入的数组p_sign计算得出sign
	public static function check_sign($arr,$p_sign)
	{
	    foreach($arr as $key => $value)
		{
			if((strlen(trim($value))==0)||(($value==0)&&(is_numeric($value))))
			{
				unset($arr[$key]);	
			}
		}		
		ksort($arr);
		$text_arr = implode("|",$arr);
		$text_arr = $text_arr."|".$p_sign;
		$sign = md5($text_arr);
		return $sign;
	}

    function checkAdult($Birthday)
    {
			if($Birthday!="")
			{
				$CurrentDate = date("Y-m-d",time());
				$AdultDate = date("Y-m-d",strtotime("$CurrentDate -18 year"));
				if(strtotime($AdultDate)>=strtotime($Birthday))
				{
					//return 0;
					return 1;
				}
				else
				{
					return 1; 	
				}	
			}
			else
			{
				//return 2;
				return 1;	
			}		
    }
		function ip2long($ip)
		{
				list($a, $b, $c, $d) = explode('.', $ip);
				$ip_long = (($a * 256 + $b) * 256 + $c) * 256 + $d;
				
				return $ip_long;
		}
	function my_authcode($string, $operation = 'DECODE', $key = 'limaogame', $expiry = 0) 
	{
		$ckey_length = 4;
	
		$key = md5($key);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
	
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
	
		$result = '';
		$box = range(0, 255);
	
		$rndkey = array();
		for($i = 0; $i <= 255; $i++) 
		{
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
	
		for($j = $i = 0; $i < 256; $i++) 
		{
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
	
		for($a = $j = $i = 0; $i < $string_length; $i++) 
		{
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
	
		if($operation == 'DECODE') 
		{
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} 
			else 
			{
				return '';
			}
		} 
		else 
		{
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	
	}
	function DateDiff($Date_1,$Date_2)
	{
		$DateDiff = abs(intval(strtotime($Date_1)-strtotime($Date_2))/86400);
			
		return $DateDiff;
	}
	//根据两个时间计算时间差,并据此生成文本串
	function timeLagToText($StartTime,$EndTime)
	{
		$Lag = $EndTime-$StartTime;
		if($Lag < 0)
		{
		    $prefix = "";
		    $suffix = "前";    
		}
		elseif($Lag > 0)
		{
            $prefix = "" ;
            $suffix = "后";   
        }
        else
        {
            $prefix = "" ;
            $suffix = "";              
        }
        $Lag = abs($Lag);
        if($Lag >= 3600*24*365)
        {
            $text = intval($Lag/(3600*24*365))."年";    
        }
        elseif($Lag >= 3600*24*30)
        {
            $text = intval($Lag/(3600*24*30))."个月";      
        }
        elseif ($Lag >= 3600*24*7) 
        {
            $text = intval($Lag/(3600*24*7))."周";
        }
        elseif ($Lag >= 3600*24)
        {
            $text = intval($Lag/(3600*24))."天";
        }  
        elseif ($Lag >= 3600)
        {
            $text = intval($Lag/(3600))."小时";
        }
        elseif ($Lag >= 60)
        {
            $text = intval($Lag/(60))."分钟";
        }
        elseif ($Lag >= 1)
        {
            $text = intval($Lag)."秒";
        }
        else 
        {
            $text = "当场";    
        }                
		return $prefix." ".$text.$suffix;
	}
	function parthTimeLag($TimeLag)
	{
	    $T = explode(".",$TimeLag);
		if(intval($T[0])>=3600)
		{
			$Text['Hour'] = intval($T[0]/3600);
		}
		else
		{
			//$Text['Hour'] = "00";
		}
		if(intval($T[0])>60)
		{
			$Text['Minute'] = sprintf("%02d",(intval($T[0])%3600)/60);
		}
		else
		{
			$Text['Minute'] = "00";
		}
		if($T[0]>=10)
		{
			$Text['Second'] = sprintf("%02d",intval($T[0])%60);
		}
		else
		{
			$Text['Second'] = sprintf("%02d",intval($T[0])%60);
		}
		return implode(":",$Text).(isset($T[1])?".".substr($T[1],0,3):"");
	}
    function parthTime($Time)
    {
        $T = explode(".",$Time);
        if(intval($T[0])>=3600)
        {
            $Text['Hour'] = intval($T[0]/3600);
        }
        else
        {
            //$Text['Hour'] = "00";
        }
        if(intval($T[0])>60)
        {
            $Text['Minute'] = sprintf("%02d",(intval($T[0])%3600)/60);
        }
        else
        {
            $Text['Minute'] = "00";
        }
        if($T[0]>=10)
        {
            $Text['Second'] = sprintf("%02d",intval($T[0])%60);
        }
        else
        {
            $Text['Second'] = sprintf("%02d",intval($T[0])%60);
        }
        return date("Y-m-d H:i:s",$T[0]).".".substr($T[1],0,3);
    }
	function cutstr($str,$len,$replace = '...')
	{
		$ascLen=strlen($str);
		$i = 0;
		$l = 0;
		for($i;$i<$ascLen;$i++)
		{
			if($l < 2*$len)
			{            
				$c=ord(substr($str,0,1));
				if($c>=127)
				{
					$ll = 2;    
				}
				else
				{
					$ll = 1;    
				}
				if(ord(substr($str,0,1)) >252){$p = 5;}elseif($c > 248){$p = 4;}elseif($c > 240){$p = 3;}elseif($c > 224){$p = 2;}elseif($c > 192){$p = 1;}else{$p = 0;}
			
				$truekey=substr($str,0,$p+1);
				
				if($truekey===false)
				{break;}       
			
				$splikey[]=$truekey;
				
				$str=substr($str,$p+1);
				$l+=$ll;             
			}
			else
			{
				break;    
			}        
		}
		if(strlen($str)>0)
		{
			return implode("",$splikey).$replace;             
		}
		else
		{
			return implode("",$splikey);
		}
	}
	//进行POST请求
	function do_post($url, $data,$outTime=120) 
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, $outTime);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}
    //进行POST和json进行请求
    function http_post_json($url, $jsonStr)
    {
        echo $url.'\n';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array($httpCode, $response);
    }

    //获取本机IP
    function getLocalIP() 
    {
        $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
        //获取操作系统为win2000/xp、win7的本机IP真实地址
        exec("ipconfig", $out, $stats);
        if (!empty($out)) 
        {
            foreach ($out AS $row) 
            {
                if (strstr($row, "IP") && strstr($row, ":") && !strstr($row, "IPv6")) 
                {
                    $tmpIp = explode(":", $row);
                    if (preg_match($preg, trim($tmpIp[1]))) 
                    {
                        return trim($tmpIp[1]);
                    }
                }
            }
        }
    //获取操作系统为linux类型的本机IP真实地址
        exec("ifconfig", $out, $stats);
        if (!empty($out)) 
        {
            if (isset($out[1]) && strstr($out[1], 'addr:')) 
            {
                $tmpArray = explode(":", $out[1]);
                $tmpIp = explode(" ", $tmpArray[1]);
                if (preg_match($preg, trim($tmpIp[0]))) 
                {
                    return trim($tmpIp[0]);
                }
            }
        }
        return '127.0.0.1';
    }
	function convertPhoneNum($num)
	{
		if(strlen($num)==11)
		{
			$str[1] = substr($num,0,3);
			$str[2] = substr($num,3,4);
			$str[3] = substr($num,7,4);
			return $str[1]."-".$str[2]."-".$str[3];
		}
		else
		{
			return $num;
		}
	}
	/**
	 * 得到系统操作日志编号
	 * @return  string
	 */
	function get_log_sn()
	{
		return date('YmdHis').sprintf("%04d",rand(1,9999));
		/* 选择一个随机的方案 */
		mt_srand((double) microtime() * 1000000);

		return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
	}
	/**
	 * 向数组中添加一个元素
	 * @return  string
	 */
	function array_insert($array,$value,$position=0)
	{
		$fore=($position==0)?array():array_splice($array,0,$position);
		$fore[]=$value;
		$ret=array_merge($fore,$array);
		return $ret;
	}

	/**
	 * 将一个数组写入配置文件
	 * @param string $fields
	 */
	function rebuildConfig($filePath,$fileName,$dataArr,$arrName)
	{
	    $dataArr['LastUpdateTime'] = sprintf("%0.3f",microtime(true));
        $var = var_export($dataArr,true);
		$text ='<?php $'.$arrName.'='.$var.'; return $'.$arrName.';?>';
		if (!is_dir($filePath))
		{
			$res=mkdir($filePath,0777,true);
		}
		file_put_contents($filePath.$fileName,$text);
	}
	/**
	 * 将一个数组写入配置文件
	 * @param string $fields
	 */
	function appendLog($filePath,$fileName,$logText)
	{
		if (!is_dir($filePath))
		{
			$res=mkdir($filePath,0777,true);
		}
		file_put_contents($filePath.$fileName,$logText,FILE_APPEND);
	}
	/**
	 * 将一个配置文件载入数组
	 * @param string $fields
	 */
	function loadConfig($filePath,$fileName)
	{
		$return = @include($filePath.$fileName);
		return $return;
	}
	/**
	 * 返回一个比较符号的数组
	 * @param string $fields
	 */
	function equalList()
	{
		$return = array("!=","=",">",">=","<=","<");
		return $return;
	}
	/**
	 * 返回一个比较符号的数组
	 * @param string $fields
	 */
	function actionList()
	{
		$return = array('add'=>"添加",
			'update'=>"更新",
			'delete'=>"删除");
		return $return;
	}
	/**
	 * 格式化版本信息到长整形
	 * @param string $fields
	 */
	function ParthVersionToInt($Version,$count = 3)
	{
		$t = explode(".",$Version);
		if(count($t)<$count)
		{
			for($i=count($t);$i<$count;$i++)
			{
				$t[$i] = 0;
			}
		}
		elseif(count($t)>$count)
		{
			foreach($t as $k => $v)
			{
				if($k>=$count)
				{
					unset($t[$k]);
				}
			}
		}
		$Version = implode(".",$t);
		if($count <4)
		{
			for($i=0;$i<=(count($t)-$count);$i++)
			{
				$Version = "0.".$Version;
			}
		}
		return ip2long($Version);
	}
	/**
	 * 格式化长整形到版本信息
	 * @param string $fields
	 */
	function ParthIntToVersion($Version,$count = 3)
	{
		$Version = long2ip($Version);
		$t = explode(".",$Version);

		for($i=0;$i<4-$count;$i++)
		{
			unset($t[$i]);
		}
		krsort($t);
		foreach($t as $k => $v)
		{
			/*
			if($v!="0")
			{
				break;
			}
			else
			{
				unset($t[$k]);
			}
			*/
		}
		ksort($t);
		$Version = implode(".",$t);
		return $Version;
	}
	function speedDisplayParth($SpeedDisplayType,$Time,$Distance)
    {
        if($SpeedDisplayType=="km/h")
        {
            return (($Time>0)?(sprintf("%0.2f",$Distance*3600/1000/$Time)):0)."km/h";
        }
        elseif($SpeedDisplayType=="mile/h")
        {
            return (($Time>0)?(sprintf("%0.2f",$Distance*3600/1000/1.60934/$Time)):0)."mile/h";
        }
        elseif($SpeedDisplayType=="time/100m")
        {
            return Base_Common::parthTimeLag(($Distance>0)?(intval($Time/$Distance*100)):0)."/100m";
        }
        elseif($SpeedDisplayType=="time/km")
        {
            return Base_Common::parthTimeLag(($Distance>0)?(intval($Time/$Distance*1000)):0)."/km";
        }
        elseif($SpeedDisplayType=="time/mile")
        {
            return Base_Common::parthTimeLag(($Distance>0)?(intval($Time/$Distance*1000*1.60934)):0)."/mile";
        }
        else
        {
            return "";
        }
    }
    function dayuSMS($params)
    {
        //print_R($params);
        include('Third/dayu/TopSdk.php');
        $t1 = microtime(true);
        $c = new TopClient;
        $c->appkey = "23327292";//$appkey;
        $c->secretKey = "b54062e4e60366134595c4c527df308b";//$secret;
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend("123456");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName("淘赛体育");
        $req->setSmsParam(json_encode($params['smsContent']));
        $req->setRecNum($params['Mobile']);
        $req->setSmsTemplateCode(Base_Common::getSMSCode($params['SMSCode']));
        $resp = $c->execute($req);
        //print_R($resp);

        $Log = json_encode(array("return"=>$resp,"TimeLag" =>microtime(true)-$t1 ));

        return $Log;
    }
    function getSMSCode($CodeName)
    {
        $SmsCodeList = array(
        //发送短信验证码
             "SMS_Validate_Code"=>"SMS_5910467",
            "SMS_Reset_Password"=>"SMS_10385744",
            "ValidateCode"=>"SMS_71010201",

        );
        return $SmsCodeList[$CodeName];
    }
    function ParthSequence($text)
    {
        //第一层解包
        $t1 = explode("|",$text);
        //循环第一层数据
        foreach($t1 as $k1 => $v1)
        {
            //第二层解包
            $t2[$k1] = explode(":",$v1);
            //如果包含_
            if(is_numeric(stripos($t2[$k1][0],'_')))
            {
                //获取键值的起始范围
                $key = explode("_",$t2[$k1][0]);
                //获取数值的起始范围
                $value = explode("_",$t2[$k1][1]);
                //键值差
                $key_diff = $key[1]-$key[0];
                //数值差
                $value_diff = $value[0]-$value[1];
                //步进
                $step = ($value_diff)/$key_diff;
                $j = 1;
                //循环累加
                for($i=$key[0];$i<=$key[1];$i++)
                {
                    $t3[$i] =  round($value[0]-$step*($j-1));
                    $j++;
                }
            }
            else
            {
                $t3[$t2[$k1][0]] = $t2[$k1][1];
            }
        }
        return $t3;
    }
    function copy_dir($src,$dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) )
        {
            if (( $file != '.' ) && ( $file != '..' ))
            {
                if ( is_dir($src . '/' . $file) )
                {
                    Base_Common::copy_dir($src . '/' . $file,$dst . '/' . $file);
                    continue;
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    //根据频率计算输入时刻对应的时间范围
    function getFrequencyTimeRange($Time,$Frequency)
    {
        switch ($Frequency)
        {
             case "day":
                 $return = array("StartTime" => date("Y-m-d 00:00:00"),"EndTime" => date("Y-m-d 23:59:59",$Time));
                break;
            case "week":
                $return = array("StartTime" => date("Y-m-d 00:00:00",$Time - (date("N")-1)*86400),"EndTime" => date("Y-m-d 23:59:59",$Time + (7-date("N"))*86400));
                break;
            case "month":
                $return = array("StartTime" => date("Y-m-01 00:00:00",$Time),"EndTime" => date("Y-m-t 23:59:59",$Time));
                break;
            case "year":
                $return = array("StartTime" => date("Y-01-01 00:00:00",$Time),"EndTime" => date("Y-12-31 23:59:59",$Time));
                break;
        }
        return $return;
    }
    //将字符串解包为数组
    function parthStrToArr($Str)
    {
        $return = array();
        //以|作为分隔符解开
        $t = explode("|",$Str);
        {
            foreach($t as $key => $value)
            {
                //以=作为分割符解开
                $t2 = explode("=",$value);
                //如果存在数组前两个
                if(isset($t2['1']))
                {
                    //存入结果数组
                    $return[$t2['0']] = $t2['1'];
                }
            }
        }
        if(count($return)>=1)
        {
            return $return;
        }
        else
        {
            return false;
        }
    }
    function parthMylapsArr($Arr)
    {
        if(isset($Arr['t']) &&isset($Arr['d']))
        {
            $Date = substr($Arr['d'],0,2)."-".substr($Arr['d'],2,2)."-".substr($Arr['d'],4,2);
            $t = explode(".",$Arr['t']);
            $DateTime = $Date." ".$t['0'];
            $Arr['ChipTime'] =  strtotime($DateTime)+$t['1']/1000;
        }
        return $Arr;
    }
    /**
     *转载自：http://www.jb51.net/article/56967.htm
     * @desc 根据两点间的经纬度计算距离
     * @param float $lat 纬度值
     * @param float $lng 经度值
     */
    function getDistance($lat1, $lng1, $lat2, $lng2){
        $earthRadius = 6367000; //approximate radius of earth in meters
        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;
        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180;
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance);
    }

    static public function refreshCache($config = [],$type,$id = 0)
    {
        $url =$config->apiUrl.$config->api['api']['refresh']."?type=".$type."&id=".$id;
        return json_decode(file_get_contents($url),true);
    }
    //从excel读取文件
    static public function readDataFromExcel($filePath)
    {
        $oExcel = new PHPExcel_Reader_Excel2007();
        $sheetInfo = $oExcel->listWorksheetInfo($filePath);
        $sheet = $oExcel->load($filePath);
        $data = [];
        foreach($sheetInfo as $key => $value)
        {
            print_R($value);
            $sheetName = $value['worksheetName'];
            $currentSheet = $sheet->getSheet($key);
            $maxColumn = $value['lastColumnLetter'];
            $maxRow = $value['totalRows'];
            $data[$sheetName] = array();
            echo $maxColumn."-".$maxRow;
            for($rowIndex=1;$rowIndex<=$maxRow;$rowIndex++){        //循环读取每个单元格的内容。注意行从1开始，列从A开始
                for($colIndex='A';$colIndex<=$maxColumn;$colIndex++){
                    $addr = $colIndex.$rowIndex;
                    $cell = $currentSheet->getCell($addr)->getValue();
                    if($cell instanceof PHPExcel_RichText){ //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $data[$sheetName][$rowIndex][$colIndex] = $cell;
                }
            }
        }
        return $data;
    }
    public static function generateGroups($count = 8)
    {
        $start = ord("A");
        $return = [];
        for($i=0;$i<=$count;$i++)
        {
            $return[$i] = $i==0?"不指定":chr($start+$i-1);
        }
        return $return;
    }
    public static function generateSeed($count = 3)
    {
        $return = [];
        for($i=0;$i<=$count;$i++)
        {
            $return[$i] = $i==0?"不指定":("第".$i."批次");
        }
        return $return;
    }
    //生成小组内循环赛阵容 team_list必须是从1开始计数
    public static function generateGroupLeague($team_list)
    {
        $matchList = [];
        $teamCount = count($team_list);
        //计算轮数
        $totalRound = $teamCount-1;
        for($i = 1;$i<=$teamCount;$i++)
        {
            $t_list[$i] = $i;
        }
        $matchList = [];
        //偶数队伍
        if(($teamCount%2)==0)
        {
            for($i=1;$i<=$totalRound;$i++)
            {
                $l = array_values($t_list);

                //初始化空的反向序列
                $l_reverse = [];
                $list = $t_list;
                //反向序列每一轮会下移一位
                $start = $teamCount-($i-1);
                $current = $start;
                for($j=0;$j<($teamCount/2);$j++)
                {
                    if($current == 1)
                    {
                        $current = $teamCount;
                    }
                    $l_reverse[count($l_reverse)+1] = $current;
                    unset($l[$current-1]);
                    $current--;
                }
                $l_list = [];$s_list = [];
                foreach($l as $key => $value)
                {
                    if($value<=($teamCount/2))
                    {
                        $s_list[] = $value;
                    }
                    else
                    {
                        $l_list[] = $value;
                    }
                }
                unset($s_list[0]);
                $s_list = array_values($s_list);
                $k_list = array_merge([1],array_values($l_list),array_values($s_list));
                $l_reverse = array_combine($k_list,$l_reverse);
                foreach($l_reverse as $home => $away)
                {
                    $matchList[$i][count($matchList[$i])+1] = ['home'=>$team_list[$home],'away'=>$team_list[$away]];
                }
            }
        }
        else//奇数队伍
        {
            echo "there";
        }
        return $matchList;
    }


}

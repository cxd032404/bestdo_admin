<?php

class Cli_SocketController extends Base_Controller_Action
{
	public $oSocket;
    public $oSocketCli;
    public $oSocketQueue;
	
	public function init()
	{
		parent::init();
		$this->socketPath =  "/www/opt";
		//$this->oSocketServer = new Connect_SocketServer();
        //$this->oSocketClient = new Xrace_Connect_SocketClient();
        //$this->oSocketQueue = new Config_SocketQueue();
	}

    function socketServerAction()
    {
        //比赛ID
        $RaceId = intval($this->request->RaceId);
        $oMylaps = new Xrace_Mylaps();
        $oRace = new Xrace_Race();
        $Type = intval($this->request->type);
        echo "type:".$Type."\n";
        sleep(3);
        $RaceInfo = $oRace->getRaceInfoByUrl($RaceId);
        $RaceUserList = $oRace->getRaceUserListByUrl($RaceId);
        $ChipList = array();
        foreach($RaceUserList["RaceUserList"] as $key => $UserInfo)
        {
            if($UserInfo["ChipId"]!="")
            {
                $ChipList[$UserInfo["ChipId"]] = array("Name"=>$UserInfo["Name"],"BIB"=>$UserInfo["BIB"],"RaceGroupId"=>$UserInfo["RaceGroupId"],"TeamName"=>$UserInfo["TeamName"]);
            }
        }
        set_time_limit(0);


        //$ipserver = $RaceInfo['RaceInfo']['comment']["LocalIP"];
        $ipserver = "0.0.0.0";
        $SocketPort = "9999";
        echo $ipserver."-".$SocketPort."\n";

        $errno = 1;
        $timeout = 1;
        $buffLength = 1024;

        $socket=stream_socket_server('tcp://'.$ipserver.':'.$SocketPort, $errno, $errstr);
        echo "socket:".$socket."\n";
        stream_set_blocking($socket,0);
        while(true)
        {
            $conn = @stream_socket_accept($socket,-1);
            $Buff_to_process = array("Text" =>"","PassingMessage"=>"");
            $Last = "";
            if($conn)
            {
                fwrite($conn,"ServerName@AckPong@Version2.1@$");
            }
            while($conn)
            {
                sleep(1);
                //echo "conn:".$conn."\n";
                $buff = fread($conn,$buffLength);
                //echo "buff:".$buff."\n";
                $length = strlen($buff);
                if($length>0)
                {
                    $Buff_to_process =  array("Text" =>$Last.$buff,"PassingMessage"=>"");
                    //echo "LastText:".$Buff_to_process["Text"]."\n";
                    do{
                        $Buff_to_process = $oMylaps->popMylapsPassingMessage($Buff_to_process['Text']);
                        $MylapsInfoArr  = base_common::parthMylapsArr(base_common::parthStrToArr($Buff_to_process['PassingMessage']));
                        //echo "chip:".$MylapsInfoArr['c']."passed,time:".$MylapsInfoArr["d"]." ".$MylapsInfoArr["t"]."\n";
                        if($Type == 1)
                        {
                            $oRedis = new Base_Cache_Redis("xrace");
                            //获取缓存
                            $m = $oRedis->get("RaceTiming_".$RaceId);
                            //缓存解开
                            $m = json_decode($m,true);
                            if(!isset($m["RaceInfo"]))
                            {
                                $m["RaceInfo"] = array("RaceName"=>$RaceInfo["RaceInfo"]["RaceName"],"RaceStartTime"=>strtotime($RaceInfo['RaceInfo']['StartTime']));

                            }
                            if(trim($MylapsInfoArr['c'])!=""  && isset($ChipList[$MylapsInfoArr['c']]))
                            {
                                echo $MylapsInfoArr['c']."/n";
                                if(isset($m["TimingList"][$MylapsInfoArr['c']]))
                                {
                                    $LastTime = strtotime("20".$MylapsInfoArr["d"]." ".$MylapsInfoArr["t"]).".".substr($MylapsInfoArr["t"],-3);;

                                    $LastLap = $LastTime-$m["TimingList"][$MylapsInfoArr['c']]["LastTime"];
                                    if($LastLap>=30)
                                    {
                                        $m["TimingList"][$MylapsInfoArr['c']]["Round"] ++;
                                        $m["TimingList"][$MylapsInfoArr['c']]["LastLap"]  = $LastLap;
                                        $m["TimingList"][$MylapsInfoArr['c']]["LastTime"]  = $LastTime;
                                        $m["TimingList"][$MylapsInfoArr['c']]["TotalTime"]  = $LastTime-$m["RaceInfo"]["RaceStartTime"];


                                        if($m["TimingList"][$MylapsInfoArr['c']]["LastLap"] >0)
                                        {
                                            if(isset($m["BestLap"]["LastLap"]))
                                            {
                                                if($m["BestLap"]["LastLap"] > $m["TimingList"][$MylapsInfoArr['c']]["LastLap"])
                                                {
                                                    $m["BestLap"] =  $m["TimingList"][$MylapsInfoArr['c']];
                                                }
                                            }
                                            else
                                            {
                                                $m["BestLap"] =  $m["TimingList"][$MylapsInfoArr['c']];
                                            }

                                        }
                                        $m["LastLap"] = $m["TimingList"][$MylapsInfoArr['c']];
                                    }
                                }
                                else
                                {
                                    $m["TimingList"][$MylapsInfoArr['c']] = array_merge(array("Round"=>0),$ChipList[$MylapsInfoArr['c']]);
                                    $m["TimingList"][$MylapsInfoArr['c']]["LastTime"]  = strtotime("20".$MylapsInfoArr["d"]." ".$MylapsInfoArr["t"]).".".substr($MylapsInfoArr["t"],-3);

                                    //$m[$MylapsInfoArr['c']]["Round"]=1;
                                }
                                $m2 = $m;$t1 = array();$t2 = array();
                                foreach($m2["TimingList"] as $key => $value)
                                {
                                    $t1[$key] = $value["Round"];
                                    $t2[$key] = $value["LastTime"];
                                }
                                array_multisort($t1,SORT_DESC,SORT_NUMERIC ,$t2,SORT_ASC,$m2["TimingList"]);
                                $First= (reset($m2["TimingList"]));

                                foreach($m2["TimingList"] as $key => $value)
                                {
                                    if($First["Round"] == $value["Round"])
                                    {
                                        $m2["TimingList"][$key]["Diff"] =    $value["LastLap"] - $First["LastLap"];
                                    }
                                }
                                $oRedis -> set("RaceTiming2_".$RaceId,json_encode($m2),86400);
                                $oRedis -> set("RaceTiming_".$RaceId,json_encode($m),86400);
                            }
                        }
                        else
                        {
                            //print_R($MylapsInfoArr);
                            //echo "\n";
                            if(isset($MylapsInfoArr['c']))
                            {
                                $StartInfo = $oRace->getRaceResult($RaceId,0,0,1);
                                $L = explode("<br>",$StartInfo);
                                $t = "20".$MylapsInfoArr["d"]." ".$MylapsInfoArr["t"];
                                $t = strtotime($t);
                                $t2 = explode(".",$MylapsInfoArr["t"]);
                                $time= $t + $t2[1]/1000;
                                foreach($L as $k => $v)
                                {
                                    if(substr($v,0,strlen($MylapsInfoArr['c'])) == $MylapsInfoArr['c'])
                                    {
                                        //echo $v;
                                        $t = explode(",",$v);
                                        $lag = $time-$t[3];
                                        if($lag >0)
                                        {
                                            echo $t[5]."-".$t['2']."-".$t['1']."-",Base_Common::parthTimeLag($lag)."\n";
                                        }
                                    }
                                }
                                //sleep(1);
                            }




                        }
                    }
                    while($Buff_to_process['PassingMessage'] != "");
                    $Last = $Buff_to_process['Text'];
                }
                //sleep(1);


            }
        }
    }
	function textTestAction()
    {
        $FileName = dirname(dirname(dirname(__FILE__)));
        $FileName = $FileName.'\html\Socket\test.txt';
        $handle = fopen($FileName, "r");

        $oMylaps = new Xrace_Mylaps();
        $text = "jjj@Passing@c=CR43438|ct=CX|t=14:38:00.132|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=CR43438|b=-1@8@$";
        $text1 = "jjj@Passing@c=HZ06571|ct=CX|t=14:44:02.841|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=HZ06571|b=-1@c=KW48567|ct=CX|t=14:44:02.906|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=KW48567|b=-1@c=KW47671|ct=CX|t=14:44:02.922|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=KW47671|b=-1@c=SF92741|ct=CX|t=14:44:02.934|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=SF92741|b=-1@c=CC39407|ct=CX|t=14:44:02.843|d=170519|l=3|dv=1|re=0|an=-1|g=-1|n=CC39407|b=-1@c=CX79393|ct=CX|t=14:44:02.964|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=CX79393|b=-1@c=KH14107|ct=CX|t=14:44:02.860|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=KH14107|b=-1@c=KP66964|ct=CX|t=14:44:02.974|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=KP66964|b=-1@15@$";
        $text2 = "L86490|ct=CX|t=14:44:02.831|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=RL86490|b=-1@c=GV59557|ct=CX|t=14:44:02.881|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=GV59557|b=-1@c=HP46938|ct=CX|t=14:44:02.847|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=HP46938|b=-1@14@$";
        $text3 = "jjj@Passing@c=RN12649|ct=CX|t=14:37:59.979|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=RN12649|b=-1@c=FN59312|ct=CX|t=14:38:00.030|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=FN59312|b=-1@c=RX80579|ct=CX|t=14:37:59.976|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=RX80579|b=-1@c=FK59372|ct=CX|t=14:38:00.010|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=FK59372|b=-1@c=GK26410|ct=CX|t=14:37:59.990|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=GK26410|b=-1@c=GK85644|ct=CX|t=14:38:00.065|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=GK85644|b=-1@c=KW81076|ct=CX|t=14:38:00.046|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=KW81076|b=-1@c=KW47671|ct=CX|t=14:38:00.101|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=KW47671|b=-1@c=KP66964|ct=CX|t=14:38:00.111|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=KP66964|b=-1@c=GW97627|ct=CX|t=14:38:00.128|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=GW97627|b=-1@c=HZ50378|ct=CX|t=14:38:00.187|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=HZ50378|b=-1@c=KX88173|ct=CX|t=14:38:00.010|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=KX88173|b=-1@7@$";
        $textArr = array(
            "jjj@Passing@c=KG78429|ct=CX|t=14:38:00.132|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=CR43438|b=-1@8@$",
            "jjj@Passing@c=GV59557|ct=CX|t=14:38:00.246|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=GV59557|b=-1@c=KW48567|ct=CX|t=14:38:00.214|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=KW48567|b=-1@c=LH81075|ct=CX|t=14:38:00.049|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=LH81075|b=-1@c=HL59534|ct=CX|t=14:38:00.135|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=HL59534|b=-1@c=FK79732|ct=CX|t=14:38:00.184|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=FK79732|b=-1@c=HG68397|ct=CX|t=14:38:00.073|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=HG68397|b=-1@c=KW60342|ct=CX|t=14:38:00.273|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=KW60342|b=-1@9@$",
            "jjj@Passing@c=HX78750|ct=CX|t=14:38:00.135|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=HX78750|b=-1@c=KH14107|ct=CX|t=14:38:00.162|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=KH14107|b=-1@10@$",
            "jjj@Passing@c=FS43924|ct=CX|t=14:38:00.082|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=FS43924|b=-1@",
            "jjj@Passing@c=FS43924|ct=CX|t=14:38:00.082|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=FS43924|b=-1@c=LF82251|ct=CX|t=14:38:00.187|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=LF82251|b=-1@c=GG77645|ct=CX|t=14:38:00.106|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=GG77645|b=-1@c=FK82108|ct=CX|t=14:38:00.244|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=FK82108|b=-1@c=GN29338|ct=CX|t=14:38:00.313|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=GN29338|b=-1@c=HZ06571|ct=CX|t=14:38:00.176|d=170519|l=1|dv=1|re=0|an=-1|g",
            "=-1|n=HZ06571|b=-1@c=HG47276|ct=CX|t=14:38:00.405|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=HG47276|b=-1@c=HP46938|ct=CX|t=14:38:00.181|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=HP46938|b=-1@11@$");

        $MessageArr =  array("Text" =>$text3,"PassingMessage"=>"");


        $Last = "";
        foreach($textArr as $Key => $Value)
        {
            $MessageArr =  array("Text" =>$Last.$Value,"PassingMessage"=>"");
            //echo "LastText:".$MessageArr["Text"]."<br><br>";
            do{
                $MessageArr = $oMylaps->popMylapsPassingMessage($MessageArr['Text']);
                //print_R($MessageArr);
                //echo "<br><br>";
                //die();
                //sleep(1);
                //echo "PassingMessage:".$MessageArr['PassingMessage']."<br>";
                //print_R(base_common::parthStrToArr($MessageArr['PassingMessage']));
                $PassingInfo = (base_common::parthMylapsArr(base_common::parthStrToArr($MessageArr['PassingMessage'])));
                $ChipId = $PassingInfo['c'];
                if(strlen($ChipId)>3)
                {
                    //$Text = fread($handle, filesize ($FileName));
                    //循环到文件结束
                    while(!feof($handle))
                    {
                        //获取每行信息
                        $content = fgets($handle, 8080);
                        if(substr($content,0,strlen($ChipId)) == $ChipId)
                        {
                            echo $content;
                            $t = explode(",",$content);
                            echo Base_Common::parthTimeLag(time()-$t[3])."<br>";
                        }
                    }
                }

                //echo "Text:".$MessageArr['Text']."<br><br>";
            }
            while($MessageArr['PassingMessage'] != "");
            $Last = $MessageArr['Text'];
        }



}
    
    function socketClientAction()
    {
			echo date("Y-m-d H:i:s",time())."write connecting:\n";
			$connect = @fsockopen("127.0.0.1", 9999, $errno, $errstr, 1);
			$Buff_to_process = "";
			// stream_set_blocking($sock,TRUE);
			stream_set_timeout($connect,0);
			echo "connected:".$connect."\n"; 
			while(true)
			{
						$v = array("uType"=>"001","Length"=>18,"Msg"=>"Hello");
			            echo $v['uType']."\n";
                        $SendContent = $this->oSocketClient->PackTest($v);

						echo "connect:".$connect."\n";
						if($connect)
						{
							fwrite($connect,$SendContent);
						}
						else
						{
							fclose($connect);
							echo date("Y-m-d H:i:s",time()). "write connecting:";
							$connect = @fsockopen("127.0.0.1",99999, $errno, $errstr, 1);
							$Buff_to_process = "";
							// stream_set_blocking($sock,TRUE);
							stream_set_timeout($connect,0);
							echo "connected:".$connect."\n";                             	
						}
						sleep(1);
			}
    }


    
	function writeTxt($filename,$content)
	{
		$logpath = "/www/opt/sock/log/";
		$filename = $logpath.$filename;
		$fp = fopen($filename,'w');
		fwrite($fp,$content);
		fclose($fp);
	}
}
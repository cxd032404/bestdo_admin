<?php
    class Third_dayu_top
	{
		include "TopSdk.php";
		date_default_timezone_set('Asia/Shanghai'); 

			public function smsTest()
			{
				$c = new TopClient;
				$c->appkey = "23327292";//$appkey;
				$c->secretKey = "b54062e4e60366134595c4c527df308b";//$secret;
				$req = new AlibabaAliqinFcSmsNumSendRequest;
				$req->setExtend("123456");
				$req->setSmsType("normal");
				$req->setSmsFreeSignName("淘赛体育");
				$req->setSmsParam("{\"customer\":\"1234\"}");
				$req->setRecNum("18621758237");
				$req->setSmsTemplateCode("SMS_5910470");
				$resp = $c->execute($req);
				var_dump($resp);		
			}			
	}
?>
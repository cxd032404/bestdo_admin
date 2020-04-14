<?php
class Cli_WechatController extends Base_Controller_Action{
    protected $oMylaps;
	protected $oRace;
    
	public function init()
	{
		parent::init();
		$this->oWechat = new Xrace_WechatTiming();
		$this->oRace = new Xrace_Race();
	}
    
    public function timingAction()
    {
        $Text = date("Y-m-d H:i:s",time()).":Wechat Start To Process\n";
        $filePath = __APP_ROOT_DIR__."log/Timing/";
        $fileName = date("Y-m-d",time()).".log";
        //写入日志文件
        Base_Common::appendLog($filePath,$fileName,$Text);
		$RaceId = $this->request->RaceId;
        $Force = $this->request->Force;
		if($RaceId)
		{
			$this->oWechat->genTimingInfo($RaceId,$Force);
            $Text = date("Y-m-d H:i:s",time()).":WechatTiming Start To Process RaceId:".$RaceId."\n";
            $filePath = __APP_ROOT_DIR__."log/Timing/";
            $fileName = date("Y-m-d",time()).".log";
            //写入日志文件
            Base_Common::appendLog($filePath,$fileName,$Text);
		}
		else
		{
			$RaceList = $this->oRace->getRaceList(array("ToProcess"=>1),"TimingType,RaceId,ToProcess,comment",1);					
			$Text = date("Y-m-d H:i:s",time()).":WechatTiming ForceProcess Race Got:".count($RaceList)."\n";
			echo $Text;
			$filePath = __APP_ROOT_DIR__."log/Timing/";
			$fileName = date("Y-m-d",time()).".log";
			//写入日志文件
			Base_Common::appendLog($filePath,$fileName,$Text);
			$this->processTiming($RaceList);
			sleep(1);	
			$RaceList = $this->oRace->getRaceList(array("inRun"=>1),"TimingType,RaceId,ToProcess,comment",1);
			$Text = date("Y-m-d H:i:s",time()).":WechatTiming InRun Race Got:".count($RaceList)."\n";
			echo $Text;
			$filePath = __APP_ROOT_DIR__."log/Timing/";
			$fileName = date("Y-m-d",time()).".log";
			//写入日志文件
			Base_Common::appendLog($filePath,$fileName,$Text);
			$this->processTiming($RaceList);
		}
		//php /work/xrace/xrace_main/prototype/app/project/api/html/cli.php "ctl=wechat&ac=timing"
		//php.exe d:\xamppserver\htdocs\xrace_main\prototype\app\project\api\html\cli.php "ctl=mylaps&ac=timing&RaceId=146"
    }
	public function processTiming($RaceList)
	{
		foreach($RaceList as $RaceId => $RaceInfo)
		{
				echo $RaceInfo['RaceId'].'-'.$RaceInfo['TimingType']."\n";
				$Text = date("Y-m-d H:i:s",time()).":WechatTiming Start To Process RaceId:".$RaceInfo['RaceId'].'-'.$RaceInfo['TimingType']."\n";
				$filePath = __APP_ROOT_DIR__."log/Timing/";
				$fileName = date("Y-m-d",time()).".log";
				//写入日志文件
				Base_Common::appendLog($filePath,$fileName,$Text);
			if($RaceInfo['TimingType']=="wechat")
			{
				//数据解包
				$ProcessRate = isset($RaceInfo['comment']['ProcessRate'])?$RaceInfo['comment']['ProcessRate']:1;
				for($i=1;$i<=$ProcessRate;$i++)
				{
					$Text = date("Y-m-d H:i:s",time()).":WechatTiming Start To Process RaceId:".$RaceId."\n";
					$filePath = __APP_ROOT_DIR__."log/Timing/";
					$fileName = date("Y-m-d",time()).".log";
					//写入日志文件
					Base_Common::appendLog($filePath,$fileName,$Text);
					//$this->oWechat->genTimingInfo($RaceId,$i==1?($RaceInfo['ToProcess']):0,0);
					$this->oWechat->genTimingInfo($RaceId,1,0);

				}					
			}

		}		
	}

    public function uploadTimingAction()
    {
        $oHorizon = new Xrace_Horizon();
        $RaceId = $this->request->RaceId;
        $syncTime = time();
        $oHorizon->uploadTiming($RaceId,$syncTime);
        //php.exe d:\xamppserver\htdocs\xrace_main\prototype\app\project\api\html\cli.php "ctl=mylaps&ac=upload.timing&RaceId=162
    }
}
<?php
class Cli_MylapsController extends Base_Controller_Action{
    protected $oMylaps;
	protected $oRace;
    
	public function init()
	{
		parent::init();
		$this->oMylaps = new Xrace_Mylaps();
		$this->oRace = new Xrace_Race();
	}
    
    public function timingAction()
    {
        $Text = date("Y-m-d H:i:s",time()).":Start To Process\n";
        $filePath = __APP_ROOT_DIR__."log/Timing/";
        $fileName = date("Y-m-d",time()).".log";
        //写入日志文件
        Base_Common::appendLog($filePath,$fileName,$Text);
		$RaceId = $this->request->RaceId;
        $Force = $this->request->Force;
		if($RaceId)
		{
			$this->oMylaps->genMylapsTimingInfo($RaceId,$Force);
		}
		else
		{
			$RaceList = $this->oRace->getRaceList(array("ToProcess"=>1),"TimingType,RaceId,ToProcess,comment");
			$Text = date("Y-m-d H:i:s",time()).":Mylaps ForceProcess Race Got:".count($RaceList)."\n";
			echo $Text;
			$filePath = __APP_ROOT_DIR__."log/Timing/";
			$fileName = date("Y-m-d",time()).".log";
			//写入日志文件
			Base_Common::appendLog($filePath,$fileName,$Text);
			$this->processTiming($RaceList);
			sleep(1);
			
			$RaceList = $this->oRace->getRaceList(array("inRun"=>1),"TimingType,RaceId,ToProcess,comment");
			$Text = date("Y-m-d H:i:s",time()).":Mylaps InRun Race Got:".count($RaceList)."\n";
			echo $Text;
			$filePath = __APP_ROOT_DIR__."log/Timing/";
			$fileName = date("Y-m-d",time()).".log";
			//写入日志文件
			Base_Common::appendLog($filePath,$fileName,$Text);
			$this->processTiming($RaceList);
		}
		//php.exe d:\xamppserver\htdocs\xrace_main\prototype\app\project\api\html\cli.php "ctl=mylaps&ac=timing&RaceId=146"
    }
	public function processTiming($RaceList)
	{
		foreach($RaceList as $RaceId => $RaceInfo)
		{
			if($RaceInfo['TimingType']=="mylaps")
			{
				//数据解包
				$ProcessRate = isset($RaceInfo['comment']['ProcessRate'])?$RaceInfo['comment']['ProcessRate']:1;
				for($i=1;$i<=$ProcessRate;$i++)
				{
					$Text = date("Y-m-d H:i:s",time()).":Start To Process RaceId:".$RaceId."\n";
					$filePath = __APP_ROOT_DIR__."log/Timing/";
					$fileName = date("Y-m-d",time()).".log";
					//写入日志文件
					Base_Common::appendLog($filePath,$fileName,$Text);
					$this->oMylaps->genMylapsTimingInfo($RaceId,$i==1?($RaceInfo['ToProcess']):0,0);
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
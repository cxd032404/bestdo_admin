<?php
/**
 * 赛事管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_SportsController extends AbstractController
{
	/**赛事类型:Race
	 * @var string
	 */
	protected $sign = '?ctl=hj/race';
    protected $ctl = 'hj/race';

    /**
	 * game对象
	 * @var object
	 */
	protected $oSports;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oSports = new Hj_Sports();

	}
	//赛事类型配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
		if($PermissionCheck['return'])
		{
			//获取赛事类型列表
			$SportTypeList = $this->oSports->getAllRaceList();
            //决胜条件
			$winBy = $this->oSports->getWinBy();
			$winWith = $this->oSports->getWinWith();
			//循环赛事类型列表
			foreach($SportTypeList as $key => $RaceInfo)
            {
                //数据解包
                $SportTypeList[$key]['comment'] = json_decode($RaceInfo['comment'],true);
                $SportTypeList[$key]['winBy'] = $winBy[$RaceInfo['winBy']];
                $SportTypeList[$key]['winWith'] = $winWith[$RaceInfo['winWith']];
            }
			//渲染模版
			include $this->tpl('Hj_Sports_RaceList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加赛事类型填写配置页面
	public function raceAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceInsert",$this->sign);
		if($PermissionCheck['return'])
		{
            //获取速度显示单位
            $SpeedDisplayTypeList = $this->oSports->getSpeedDisplayList();
            //决胜条件
            $winBy = $this->oSports->getWinBy();
            $winWith = $this->oSports->getWinWith();
			//渲染模版
			include $this->tpl('Hj_Sports_RaceAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//添加新赛事类型
	public function raceInsertAction()
	{
		//检查权限
		$bind=$this->request->from('RaceName','SpeedDisplayType','winWith','winBy');
		//赛事类型名称不能为空
		if(trim($bind['RaceName'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            //数据打包
            $bind['comment'] = json_encode($bind['comment']);
		    //添加赛事类型
			$res = $this->oSports->insertRace($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改赛事类型信息页面
	public function raceModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceModify",$this->sign);
		if($PermissionCheck['return'])
		{
			//赛事类型ID
			$RaceId = intval($this->request->RaceId);
			//获取赛事类型信息
			$RaceInfo = $this->oSports->getRace($RaceId,'*');
			//数据解包
			$RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
			//获取速度显示单位
            $SpeedDisplayTypeList = $this->oSports->getSpeedDisplayList();
            //决胜条件
            $winBy = $this->oSports->getWinBy();
            $winWith = $this->oSports->getWinWith();
            //渲染模版
			include $this->tpl('Hj_Sports_RaceModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//更新赛事类型信息
	public function raceUpdateAction()
	{
	    //接收页面参数
		$bind=$this->request->from('RaceId','RaceName','SpeedDisplayType','winWith','winBy');
        //赛事类型名称不能为空
		if(trim($bind['RaceName'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            //数据打包
            $bind['comment'] = json_encode($bind['comment']);
			//修改赛事类型
			$res = $this->oSports->updateRace($bind['RaceId'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除赛事类型
	public function raceDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("RaceDelete",$this->sign);
		if($PermissionCheck['return'])
		{
			//赛事类型ID
			$RaceId = trim($this->request->RaceId);
			//删除赛事类型
			$this->oSports->deleteRace($RaceId);
			//返回之前的页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
}

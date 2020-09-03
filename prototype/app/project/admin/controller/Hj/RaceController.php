<?php
/**
 * 赛事管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_RaceController extends AbstractController
{
	/**赛事:Race
	 * @var string
	 */
	protected $sign = '?ctl=hj/race';
    protected $ctl = 'hj/race';

    /**
	 * game对象
	 * @var object
	 */
	protected $oRace;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oRace = new Hj_Race();

	}
	//赛事配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
		if($PermissionCheck['return'])
		{
            //获取赛事列表
            $RaceTypeList = $this->oRace->getRaceTypeList();
		    //获取赛事列表
			$RaceList = $this->oRace->getRaceList();
			//循环赛事列表
			foreach($RaceList as $key => $RaceInfo)
            {
                //数据解包
                $RaceList[$key]['comment'] = json_decode($RaceInfo['comment'],true);
                $RaceList[$key]['race_type'] = $RaceTypeList[$RaceInfo['race_type']]??"未知类型";
            }
			//渲染模版
			include $this->tpl('Hj_Race_RaceList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加赛事填写配置页面
	public function raceAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addRace",$this->sign);
		if($PermissionCheck['return'])
		{
            //获取赛事列表
            $RaceTypeList = $this->oRace->getRaceTypeList();
			//渲染模版
			include $this->tpl('Hj_Race_RaceAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//添加新赛事
	public function raceInsertAction()
	{
		//检查权限
		$bind=$this->request->from('race_name','race_type','team');
		//赛事名称不能为空
		if(trim($bind['race_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            //数据打包
            $bind['detail'] = json_encode([]);
		    //添加赛事
			$res = $this->oRace->insertRace($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改赛事信息页面
	public function raceModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateRace",$this->sign);
		if($PermissionCheck['return'])
		{
			//赛事ID
			$RaceId = intval($this->request->race_id);
			//获取赛事信息
			$RaceInfo = $this->oRace->getRace($RaceId,'*');
			//数据解包
			$RaceInfo['detail'] = json_decode($RaceInfo['detail'],true);
            //获取赛事列表
            $RaceTypeList = $this->oRace->getRaceTypeList();
            //渲染模版
			include $this->tpl('Hj_Race_RaceModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//更新赛事信息
	public function raceUpdateAction()
	{
	    //接收页面参数
		$bind=$this->request->from('race_id','race_name','race_type','team');
        //赛事名称不能为空
		if(trim($bind['race_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			//修改赛事
			$res = $this->oRace->updateRace($bind['race_id'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除赛事
	public function raceDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteRace",$this->sign);
		if($PermissionCheck['return'])
		{
			//赛事ID
			$RaceId = trim($this->request->race_id);
			//删除赛事
			$this->oRace->deleteRace($RaceId);
			//返回之前的页面
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //成员列表
    public function memberListAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateRace",$this->sign);
        if($PermissionCheck['return'])
        {
            //赛事ID
            $RaceId = intval($this->request->race_id);
            //获取赛事信息
            $RaceInfo = $this->oRace->getRace($RaceId,'*');
            //数据解包
            $RaceInfo['detail'] = json_decode($RaceInfo['detail'],true);
            if($RaceInfo['team']==1)
            {
                $teamList = (new Hj_Race_Team())->getTeamList(['race_id'=>$RaceId]);
                //渲染模版
                include $this->tpl('Hj_Race_TeamList');
            }
            else
            {
                $atheleteList = (new Hj_Race_Athlete())->getAthleteList(['race_id'=>$RaceId]);
                //渲染模版
                include $this->tpl('Hj_Race_AthleteList');
            }
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}

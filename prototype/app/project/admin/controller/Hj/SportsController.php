<?php
//ALTER TABLE `config_sports_type` ADD `SpeedDisplayType` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '速度显示单位' AFTER `SportsTypeName`;

/**
 * 运动管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_SportsController extends AbstractController
{
	/**运动类型:SportsType
	 * @var string
	 */
	protected $sign = '?ctl=hj/sports';
    protected $ctl = 'hj/sports';

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
	//运动类型配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
		if($PermissionCheck['return'])
		{
			//获取运动类型列表
			$SportTypeList = $this->oSports->getAllSportsTypeList();
            //决胜条件
			$winBy = $this->oSports->getWinBy();
			$winWith = $this->oSports->getWinWith();
			//循环运动类型列表
			foreach($SportTypeList as $key => $SportsTypeInfo)
            {
                //数据解包
                $SportTypeList[$key]['comment'] = json_decode($SportsTypeInfo['comment'],true);
                $SportTypeList[$key]['winBy'] = $winBy[$SportsTypeInfo['winBy']];
                $SportTypeList[$key]['winWith'] = $winWith[$SportsTypeInfo['winWith']];
            }
			//渲染模版
			include $this->tpl('Hj_Sports_SportsTypeList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加运动类型填写配置页面
	public function sportsTypeAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("SportsTypeInsert",$this->sign);
		if($PermissionCheck['return'])
		{
            //获取速度显示单位
            $SpeedDisplayTypeList = $this->oSports->getSpeedDisplayList();
            //决胜条件
            $winBy = $this->oSports->getWinBy();
            $winWith = $this->oSports->getWinWith();
			//渲染模版
			include $this->tpl('Hj_Sports_SportsTypeAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//添加新运动类型
	public function sportsTypeInsertAction()
	{
		//检查权限
		$bind=$this->request->from('SportsTypeName','SpeedDisplayType','winWith','winBy');
		//运动类型名称不能为空
		if(trim($bind['SportsTypeName'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            //数据打包
            $bind['comment'] = json_encode($bind['comment']);
		    //添加运动类型
			$res = $this->oSports->insertSportsType($bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//修改运动类型信息页面
	public function sportsTypeModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("SportsTypeModify",$this->sign);
		if($PermissionCheck['return'])
		{
			//运动类型ID
			$SportsTypeId = intval($this->request->SportsTypeId);
			//获取运动类型信息
			$SportsTypeInfo = $this->oSports->getSportsType($SportsTypeId,'*');
			//数据解包
			$SportsTypeInfo['comment'] = json_decode($SportsTypeInfo['comment'],true);
			//获取速度显示单位
            $SpeedDisplayTypeList = $this->oSports->getSpeedDisplayList();
            //决胜条件
            $winBy = $this->oSports->getWinBy();
            $winWith = $this->oSports->getWinWith();
            //渲染模版
			include $this->tpl('Hj_Sports_SportsTypeModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	
	//更新运动类型信息
	public function sportsTypeUpdateAction()
	{
	    //接收页面参数
		$bind=$this->request->from('SportsTypeId','SportsTypeName','SpeedDisplayType','winWith','winBy');
        //运动类型名称不能为空
		if(trim($bind['SportsTypeName'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            //数据打包
            $bind['comment'] = json_encode($bind['comment']);
			//修改运动类型
			$res = $this->oSports->updateSportsType($bind['SportsTypeId'],$bind);
			$response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}
	
	//删除运动类型
	public function sportsTypeDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("SportsTypeDelete",$this->sign);
		if($PermissionCheck['return'])
		{
			//运动类型ID
			$SportsTypeId = trim($this->request->SportsTypeId);
			//删除运动类型
			$this->oSports->deleteSportsType($SportsTypeId);
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

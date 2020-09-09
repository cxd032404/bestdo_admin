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
                $RaceList[$key]['detail'] = json_decode($RaceInfo['detail'],true);
                $RaceList[$key]['race_type'] = $RaceTypeList[$RaceInfo['race_type']]['name']??"未知类型";
                $RaceList[$key]['detail_type'] = $RaceTypeList[$RaceInfo['race_type']]['list'][$RaceList[$key]['detail']['detail_type']??""]['name']??"未指定";
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
            $TypeDetailList = $RaceTypeList[$RaceInfo['race_type']]['list'];
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
		$bind=$this->request->from('race_id','race_name','team','detail');
        //赛事名称不能为空
		if(trim($bind['race_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
			$bind['detail'] = json_encode($bind['detail']);
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
            $groups = Base_Common::generateGroups(8);
            //赛事ID
            $RaceId = intval($this->request->race_id);
            //获取赛事信息
            $RaceInfo = $this->oRace->getRace($RaceId,'*');
            //数据解包
            $RaceInfo['detail'] = json_decode($RaceInfo['detail'],true);
            if($RaceInfo['team']==1)
            {
                $teamList = (new Hj_Race_Team())->getTeamList(['race_id'=>$RaceId]);
                foreach($teamList as $key => $value)
                {
                    $teamList[$key]['group'] = $value['group_id']==0?"未分组":($groups[$value['group_id']]??"未知组");
                    $teamList[$key]['seed'] = $value['seed']==0?"非种子":("第".$value['seed']."批次");
                }
                //渲染模版
                $export_var = "<a class = 'pb_btn_light_1' href =".(Base_Common::getUrl('',$this->ctl,'race.member.download',['race_id'=>$RaceId])).">导出表格</a>";
                include $this->tpl('Hj_Race_TeamList');
            }
            else
            {
                $atheleteList = (new Hj_Race_Athlete())->getAthleteList(['race_id'=>$RaceId]);
                foreach($atheleteList as $key => $value)
                {
                    $atheleteList[$key]['group'] = $value['group_id'] == 0 ? "未分组" : ($groups[$value['group_id']] ?? "未知组");
                    $atheleteList[$key]['seed'] = $value['seed']==0?"非种子":("第".$value['seed']."批次");
                }
                //渲染模版
                $export_var = "<a class = 'pb_btn_light_1' href =".(Base_Common::getUrl('',$this->ctl,'race.member.download',['race_id'=>$RaceId])).">导出表格</a>";
                include $this->tpl('Hj_Race_AthleteList');
            }
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //成员列表
    public function memberModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateRace",$this->sign);
        if($PermissionCheck['return'])
        {
            $groups = Base_Common::generateGroups(8);
            //赛事ID
            $RaceId = intval($this->request->race_id);
            //记录ID
            $id = intval($this->request->id);
            //获取赛事信息
            $RaceInfo = $this->oRace->getRace($RaceId,'*');
            //数据解包
            $RaceInfo['detail'] = json_decode($RaceInfo['detail'],true);
            //获取赛事列表
            $RaceTypeList = $this->oRace->getRaceTypeList();
            $detailType = $RaceTypeList[$RaceInfo['race_type']]['list'][$RaceInfo['detail']['detail_type']??""]??[];
            $maxGroup = Base_Common::generateGroups($detailType['group']??8);
            $maxSeed = Base_Common::generateSeed(3);
            if($RaceInfo['team']==1)
            {
                $TeamInfo = (new Hj_Race_Team())->getTeam($id);
                //渲染模版
                include $this->tpl('Hj_Race_TeamModify');
            }
            else
            {
                $AthleteInfo = (new Hj_Race_Athlete())->getAthlete($id);
                //渲染模版
                include $this->tpl('Hj_Race_AthleteModify');
            }
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //更新赛事信息
    public function memberUpdateAction()
    {
        //接收页面参数
        $bind=$this->request->from('race_id','id','name','group_id','seed');
        //赛事名称不能为空
        if(trim($bind['name'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            //获取赛事信息
            $RaceInfo = $this->oRace->getRace($bind['race_id'],'*');
            //团队赛
            if($RaceInfo['team']==1)
            {
                $teamInfo = ['team_name'=>$bind['name'],'group_id'=>$bind['group_id'],'seed'=>$bind['seed']];
                $res = (new Hj_Race_Team())->updateTeam($bind['id'],$teamInfo);
            }
            else
            //个人赛
            {
                $athleteInfo = ['athlete_name'=>$bind['name'],'group_id'=>$bind['group_id'],'seed'=>$bind['seed']];
                $res = (new Hj_Race_Athlete())->updateAthlete($bind['id'],$athleteInfo);
            }
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //删除选手/队伍
    public function memberDeleteAction()
    {
        //赛事ID
        $RaceId = intval($this->request->race_id);
        //记录ID
        $id = intval($this->request->id);
        //获取赛事信息
        $RaceInfo = $this->oRace->getRace($RaceId);
        //团队赛
        if($RaceInfo['team']==1)
        {
            $res = (new Hj_Race_Team())->deleteTeam($id);
        }
        else //个人赛
        {
            $res = (new Hj_Race_Athlete())->deleteAthlete($id);
        }
        //返回之前的页面
        $this->response->goBack();
    }

    /*
     * 下载成员列表或者团队列表
     */
    public function raceMemberDownloadAction(){
           $race_id = $this->request->race_id??0;
           $RaceInfo =  $this->oRace->getRace($race_id,'*');
           $race_name = $RaceInfo['race_name'];

        if($RaceInfo['team'])
           {
               //团队列表
               $member_list = (new Hj_Race_Team())->getTeamList(['race_id'=>$race_id]);
               $list_name = '队伍名称';
               $file_name = $race_name.'队伍列表';
           }else
           {
               //选手列表
               $member_list = (new Hj_Race_Athlete())->getAthleteList(['race_id'=>$race_id]);
               $list_name = '选手名称';
               $file_name = $race_name.'选手列表';
           }
        $groups = Base_Common::generateGroups(8);
        foreach($member_list as $key => $value)
            {
                $member_list[$key]['group'] = $value['group_id']==0?"未分组":($groups[$value['group_id']]??"未知组");
                $member_list[$key]['seed'] = $value['seed']==0?"非种子":("第".$value['seed']."批次");
                $member_list[$key]['id'] = $value['team_id']??$value['athlete_id'];
                $member_list[$key]['name'] = $value['team_name']??$value['athlete_name'];
            }


        $objPHPExcel = new PHPExcel();
        /** 设置工作表名称 */
        $objPHPExcel->getActiveSheet(0)
            ->setCellValue('A1', '队伍id')
            ->setCellValue('B1', $list_name)
            ->setCellValue('C1', '分组')
            ->setCellValue('D1', '种子');
        $count = 2;
        foreach ($member_list as $key =>$member_info)
        {
            $objPHPExcel->getActiveSheet(0)
                ->setCellValue('A'.$count,$member_info['id'])
                ->setCellValue('B'.$count,$member_info['name'])
                ->setCellValue('C'.$count,$member_info['group'])
                ->setCellValue('D'.$count,$member_info['seed']);
            $count++;
        }
        $objPHPExcel->getActiveSheet(0)->setTitle($race_name);
        ob_end_clean();
        @header('pragma:public');
        @header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$file_name.'.xls"');
        @header("Content-Disposition:attachment;filename=$file_name.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    /*
       * 上传页面渲染
       */public function raceMemberUploadSubmitAction()
{
    $race_id = $this->request->get("race_id")??0;
    $is_team = $this->request->get("is_team")??0;
    //检查权限
    $PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
    if($PermissionCheck['return'])
    {
        //模板渲染
        include $this->tpl('Hj_Race_RaceMemberUpload');
    }
    else
    {
        $home = $this->sign;
        include $this->tpl('403');
    }
}
    /*
    * 上传队伍
    */
    public function raceMemberUploadAction()
    {
        $response = ['errno'=>0,'msg'=>''];
        $race_id = $this->request->race_id??0;
        $RaceInfo =  $this->oRace->getRace($race_id,'*');
        $oUpload = new Base_Upload('upload_txt');
        $upload = $oUpload->upload('upload_txt');
        $upload = $upload->resultArr;
        if($upload[1]['errno']==0) {
            $file_path = $upload[1]['path'];
        }

        $inputFileType = PHPExcel_IOFactory::identify($file_path);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);

        $PHPExcel = $objReader->load($file_path); //读取文件
        $currentSheet = $PHPExcel->getSheet(0); //读取第一个工作簿
        $race_name = $currentSheet->getTitle();
        if($RaceInfo['race_name'] != $race_name)
        {
            $response['msg'] = '赛事不匹配';
            echo json_encode($response) ;
            return;
        }


        $allRow = $currentSheet->getHighestRow(); // 所有行数
        $error = 0;
        for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++) {
            $id = trim($currentSheet->getCell('A'.$rowIndex)->getValue()??0);
            if($id == 0)
            {
                $name = trim($currentSheet->getCell('B'.$rowIndex)->getValue()??'');
                //插入数据
                if($RaceInfo['team'])
                {
                    //插入队伍
                   $res = (new Hj_Race_Team())->insertTeam(['race_id'=>$race_id,'team_name'=>$name]);
                   if(!$res)
                   {
                       $error++;
                   }
                }else
                {
                    //插入选手
                    $res = (new Hj_Race_Athlete())->insertAthlete(['race_id'=>$race_id,'athlete_name'=>$name]);
                    if($res)
                    {
                        $error++;
                    }
                }
            }
             elseif(is_numeric($id))
            {
                $name = trim($currentSheet->getCell('B'.$rowIndex)->getValue()??'');
                //插入数据
                if($RaceInfo['team'])
                {
                    //修改队伍
                    $res = (new Hj_Race_Team())->updateTeam($id,['team_name'=>$name]);
                    if(!$res)
                    {
                        $error++;
                    }
                }else
                {
                    //修改选手
                    $res = (new Hj_Race_Athlete())->updateAthlete($id,['athlete_name'=>$name]);
                    if(!$res)
                    {
                        $error++;
                    }
                }

            }

        }
        $response['result']['errno'] = $error;
        echo json_encode($response) ;
        return;
    }
}

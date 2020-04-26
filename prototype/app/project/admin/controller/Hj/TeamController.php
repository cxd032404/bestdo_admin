<?php
/**用户管理*/

class Xrace_TeamController extends AbstractController
{
	/**用户管理相关:User
	 * @var string
	 */
	protected $sign = '?ctl=xrace/team';
	/**
	 * game对象
	 * @var object
	 */
	protected $oUser;
	protected $oTeam;
	protected $oManager;
	protected $oRace;

        /**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oUser = new Xrace_UserInfo();
		$this->oTeam = new Xrace_Team();
		$this->oManager = new Widget_Manager();
		$this->oRace = new Xrace_Race();

	}
	//队伍列表
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			$params['TeamName'] = urldecode(trim($this->request->TeamName))?substr(urldecode(trim($this->request->TeamName)),0,20):"";
			//分页参数
			$params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
			$params['PageSize'] = 20;
			//获取队伍列表时需要获得记录总数
			$params['getCount'] = 1;
			//获取用户列表
			$TeamList = $this->oTeam->getTeamList($params);
            //初始化空的用户列表
            $UserList = array();
            $RaceCatalogList = array();
            $RaceStageList = array();
            //循环队伍列表
            foreach($TeamList['TeamList'] as $TeamId => $TeamInfo)
            {
                //如果没有获取过创建者信息
                if(!isset($UserList[$TeamInfo['CreateUserId']]))
                {
                    //获取用户数据
                    $UserInfo = $this->oUser->getUserInfo($TeamInfo['CreateUserId'],"UserId,Name",1);
                    //如果获取到
                    if(isset($UserInfo['UserId']))
                    {
                        //存入用户列表
                        $UserList[$TeamInfo['CreateUserId']] = $UserInfo;
                    }
                }
                //保存到创建者信息
                $CreateUserInfo = isset($UserList[$TeamInfo['CreateUserId']])?$UserList[$TeamInfo['CreateUserId']]:array();
                if($TeamInfo['IsTemp'] ==1)
                {
                    //如果没有获取过赛事信息
                    if(!isset($RaceCatalogList[$TeamInfo['RaceCatalogId']]))
                    {
                        //获取赛事数据
                        $RaceCatalogInfo = $this->oRace->getRaceCatalog($TeamInfo['RaceCatalogId'],"RaceCatalogId,RaceCatalogName");
                        //如果获取到
                        if(isset($RaceCatalogInfo['RaceCatalogId']))
                        {
                            //存入赛事列表
                            $RaceCatalogList[$TeamInfo['RaceCatalogId']] = $RaceCatalogInfo;
                        }
                    }
                    //保存到赛事信息
                    $RaceCatalogInfo = isset($RaceCatalogList[$TeamInfo['RaceCatalogId']])?$RaceCatalogList[$TeamInfo['RaceCatalogId']]:array();
                    //如果没有获取过分站信息
                    if(!isset($RaceStageList[$TeamInfo['RaceStageId']]))
                    {
                        //获取分站数据
                        $RaceStageInfo = $this->oRace->getRaceStage($TeamInfo['RaceStageId'],"RaceStageId,RaceStageName");
                        //如果获取到
                        if(isset($RaceStageInfo['RaceStageId']))
                        {
                            //存入分站列表
                            $RaceStageList[$TeamInfo['RaceStageId']] = $RaceStageInfo;
                        }
                    }
                    //保存到分站信息
                    $RaceStageInfo = isset($RaceStageList[$TeamInfo['RaceStageId']])?$RaceStageList[$TeamInfo['RaceStageId']]:array();
                }

                //保存赛事名称
                $TeamList['TeamList'][$TeamId]['RaceCatalogName'] =  isset($RaceCatalogInfo['RaceCatalogName'])?$RaceCatalogInfo['RaceCatalogName']:"未知赛事";
                //保存分站名称
                $TeamList['TeamList'][$TeamId]['RaceStageName'] =  isset($RaceStageInfo['RaceStageName'])?$RaceStageInfo['RaceStageName']:"未知分站";
                //保存创建者姓名
                $TeamList['TeamList'][$TeamId]['CreateUserName'] =  isset($CreateUserInfo['Name'])?$CreateUserInfo['Name']:"未知用户";
            }
			//翻页参数
			$page_url = Base_Common::getUrl('','xrace/team','index',$params)."&Page=~page~";
			$page_content =  base_common::multi($TeamList['TeamCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
			//模板渲染
			include $this->tpl('Xrace_Team_TeamList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//获取赛事对应的队伍列表
	public function getTeamByCatalogAction()
	{
		//赛事ID
		$RaceCatalogId = intval($this->request->RaceCatalogId);
		//分组ID
		$TeamId = intval($this->request->RaceGroupId);
		//所有赛事分组列表
		$params = array('RaceCatalogId'=>$RaceCatalogId);
		$TeamList = $this->oTeam->getTeamList($params,array("TeamId","TeamName"));
		$text = '';
		//循环赛事分组列表
		foreach($TeamList['TeamList'] as $TeamId => $TeamInfo)
		{
			//初始化选中状态
			$selected = "";
			//如果分组ID与传入的分组ID相符
			if($TeamInfo['TeamId'] == $TeamId)
			{
				//选中拼接
				$selected = 'selected="selected"';
			}
			//字符串拼接
			$text .= '<option value="'.$TeamInfo['TeamId'].'">'.$TeamInfo['TeamName'].'</option>';
		}
		echo $text;
		die();
	}
	//获取赛事对应的队伍列表
	public function getGroupByTeamAction()
	{
		//分组ID
		$TeamId = intval($this->request->TeamId);
		//分组ID
		$RaceGroupId = intval($this->request->RaceGroupId);
		//所有赛事分组列表
		$TeamInfo = $this->oTeam->getTeamInfo($TeamId);
		//数据解包
		$TeamInfo['comment'] = json_decode($TeamInfo['comment'],true);
		$text = '';
		if(isset($TeamInfo['comment']['SelectedRaceGroup']))
		{
			//所有赛事分组列表
			$RaceGroupList = $this->oRace->getRaceGroupList($TeamInfo['RaceCatalogId'],'RaceGroupId,RaceGroupName');
			//循环赛事分组列表
			foreach($TeamInfo['comment']['SelectedRaceGroup'] as $GroupId)
			{
				if(isset($RaceGroupList[$GroupId]))
				{
					//初始化选中状态
					$selected = "";
					//如果分组ID与传入的分组ID相符
					if($GroupId == $RaceGroupId)
					{
						//选中拼接
						$selected = 'selected="selected"';
					}
					//字符串拼接
					$text .= '<option value="'.$GroupId.'">'.$RaceGroupList[$GroupId]['RaceGroupName'].'</option>';
				}

			}
		}
		echo $text;
		die();
	}
    //添加队伍填写配置页面
    public function teamAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("RaceTeamInsert");
        if($PermissionCheck['return'])
        {
            //渲染模板
            include $this->tpl('Xrace_Team_TeamAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    public function testAction()
    {
        echo json_encode(array('errno' => 0));
        return true;
    }
    //添加队伍
    public function teamInsertAction()
    {
        //获取 页面参数
        $bind=$this->request->from('TeamName');
        //队伍名称不能为空
        if(trim($bind['TeamName'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            //根据队伍名称获取队伍
            $TeamInfo = $this->oTeam->getTeamInfoByName($bind['TeamName']);
            //如果获取到
            if (isset($TeamInfo['TeamId']))
            {
                $response = array('errno' => 2);
            }
            else
            {
                //获取证件类型列表
                $IdTypeList = $this->oUser->getAuthIdType();
                //获取证件类型列表
                $SexList = $this->oUser->getSexList();
                $i = 0;
                //文件上传
                $oUpload = new Base_Upload('UserList');
                $upload = $oUpload->upload('UserList');
                $res = $upload->resultArr;
                //打开文件
                $handle = fopen($res['1']['path'], 'r');
                //循环到文件结束
                while (!feof($handle))
                {
                    //获取每行信息
                    $content = fgets($handle, 8080);
                    //以,为分隔符解开
                    $t = explode(",", $content);
                    if (count($t) >= 1)
                    {
                        $i++;
                        $IdNo = trim($t['1']);
                        //如果身份证号码最少为6位
                        if(strlen($IdNo)>=6)
                        {
                            //根据证件号码获取用户信息
                            $IdUserInfo = $this->oUser->getUserByColumn("IdNo", $IdNo);
                            //如果是第一个用户
                            if ($i == 1)
                            {
                                //如果找到用户
                                if (isset($IdUserInfo['UserId']))
                                {
                                    //创建的用户
                                    $CreateUserId = $IdUserInfo['UserId'];
                                    //获取当前时间
                                    $Time = date("Y-m-d H:i:s", time());
                                    //生成队伍的数组
                                    $bind['TeamComment'] = $bind['TeamName'];
                                    $bind['CreateUserId'] = $CreateUserId;
                                    $bind['CreateTime'] = $Time;
                                    $bind['LastUpdateTime'] = $Time;
                                    //创建队伍信息
                                    $InsertTeam = $this->oTeam->insertTeam($bind);
                                    //创建成功
                                    if ($InsertTeam)
                                    {
                                        //初始化本次加入的队员数量
                                        $response = array("errno" => 0,"Joined" => 0);
                                        //如果关联比赛用户
                                        if($IdUserInfo['RaceUserId']>0)
                                        {
                                            //加入队伍
                                            $Join = $this->oTeam->insertTeamUser(array("RaceUserId"=>$IdUserInfo['RaceUserId'],"TeamId"=>$InsertTeam));
                                            //如果加入成功
                                            if($Join)
                                            {
                                                //成功数量累加
                                                $response['Joined']++;
                                            }
                                        }
                                        else
                                        {
                                            //根据用户信息复制比赛用户信息
                                            $RaceUserId = $this->oUser->createRaceUserByUserInfo($IdUserInfo['UserId']);
                                            //如果复制成功
                                            if($RaceUserId)
                                            {
                                                //加入队伍
                                                $Join = $this->oTeam->insertTeamUser(array("RaceUserId"=>$RaceUserId,"TeamId"=>$InsertTeam));
                                                //如果加入成功
                                                if($Join)
                                                {
                                                    //成功数量累加
                                                    $response['Joined']++;
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $response = array("errno" => 9);
                                        break;
                                    }
                                }
                                else//没找到用户
                                {
                                    $response = array("errno" => 3);
                                    break;
                                }
                            }
                            else//从第二个开始
                            {
                                //如果队伍创建成功
                                if($InsertTeam)
                                {
                                    //如果找到用户
                                    if (isset($IdUserInfo['UserId']))
                                    {
                                        //如果有关联比赛用户
                                        if(isset($IdUserInfo['RaceUserId']))
                                        {
                                            //加入队伍
                                            $Join = $this->oTeam->insertTeamUser(array("RaceUserId"=>$IdUserInfo['RaceUserId'],"TeamId"=>$InsertTeam));
                                            //如果加入成功
                                            if($Join)
                                            {
                                                //成功数量累加
                                                $response['Joined']++;
                                            }
                                        }
                                        //根据用户信息复制比赛用户信息
                                        $RaceUserId = $this->oUser->createRaceUserByUserInfo($IdUserInfo['UserId']);
                                        //如果复制成功
                                        if($RaceUserId)
                                        {
                                            //加入队伍
                                            $Join = $this->oTeam->insertTeamUser(array("RaceUserId"=>$RaceUserId,"TeamId"=>$InsertTeam));
                                            //如果加入成功
                                            if($Join)
                                            {
                                                //成功数量累加
                                                $response['Joined']++;
                                            }
                                        }
                                    }
                                    else
                                    {
                                        //根据证件号码获取比赛用户信息
                                        $RaceUserInfo = $this->oUser->getRaceUserByColumn("IdNo",$IdNo);
                                        //如果找到
                                        if(isset($RaceUserInfo['RaceUserId']))
                                        {
                                            //加入队伍
                                            $Join = $this->oTeam->insertTeamUser(array("RaceUserId"=>$RaceUserInfo['RaceUserId'],"TeamId"=>$InsertTeam));
                                        }
                                        else
                                        {
                                            //生成用户信息
                                            $UserInfo = array('CreateUserId'=>$CreateUserId ,'Name'=>$t['0'],'Sex'=>isset($SexList[trim($t['3'])])?trim($t['3']):0,'ContactMobile'=>trim($t['4']),'IdNo'=>trim($t['1']),'IdType'=>isset($IdTypeList[trim($t['2'])])?trim($t['2']):1,'Available'=>0,'RegTime'=>$Time);
                                            //创建用户
                                            $CreateRaceUser = $this->oUser->insertRaceUser($UserInfo);
                                            //加入队伍
                                            $Join = $this->oTeam->insertTeamUser(array("RaceUserId"=>$CreateRaceUser,"TeamId"=>$InsertTeam));
                                        }
                                        //加入成功
                                        if($Join)
                                        {
                                            $response['Joined']++;
                                        }
                                    }
                                }
                            }
                        }

                    }
                }
                if($response['errno'] != 0)
                {
                    $response = array('errno' => 9);
                }
            }
        }
        echo json_encode($response);
        return true;
    }
}

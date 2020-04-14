<?php
/**
 * 用户激活相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_User extends Base_Widget
{
	//声明所用到的表
	protected $table = 'user_info';
	protected $table_auth = 'user_auth';
	protected $table_auth_log = 'user_auth_log';
	protected $table_license = 'user_license';
	protected $table_race = 'user_race';
	protected $table_race_user_team = 'user_team';
    protected $table_stage_checkin = 'user_stage_checkin';
    protected $table_team = 'team';
    //性别列表
    protected $raceApplySourceList = array('0'=>"未知",'1'=>"线上","2"=>"线下");
	//性别列表
	protected $sex = array('1'=>"男","2"=>"女");
	//实名认证状态
	protected $authStatus = array('0'=>"未认证",'1'=>"待认证",'2'=>"已认证");
	//提交时对应的实名认证状态名
	protected $authStatus_submit = array('0'=>"不通过",'2'=>"审核通过");
	//认证记录中对应的实名认证状态名
	protected $authStatus_log = array('0'=>"拒绝","2"=>"通过");
	//实名认证用到的证件类型列表
	protected $auth_id_type = array('1'=>"身份证","2"=>"护照");
    //用户执照状态
	protected $user_license_status = array('1'=>"生效中",'2'=>"已过期",'3'=>"即将生效",'4'=>"已删除");
    //用户签到状态
    protected $user_checkin_status = array('0'=>'全部','2'=>"未签到",'1'=>"已签到");
    //用户签到短信发送状态
    protected $user_checkin_sms_sent_status = array('3'=>"不需发送",'1'=>"待发送",'2'=>"已发送");
        //获取性别列表
	public function getSexList()
	{
		return $this->sex;
	}
	//获取实名认证状态
	public function getAuthStatus($type = "display")
	{
		if($type=="display")
		{
			return $this->auth_status;
		}
		else
		{
			return $this->auth_status_submit;
		}
	}
	//获取实名认证的证件列表
	public function getAuthIdType()
	{
		return $this->auth_id_type;
	}
	//获取实名认证的记录的状态列表
	public function getAuthLogStatusTypeList()
	{
		return $this->auth_status_log;
	}

    //获得用户执照状态
	public function getUserLicenseStatusList()
	{
		return $this->user_license_status;
	}
    //获得用户签到状态列表
    public function getUserCheckInStatus()
    {
        return $this->user_checkin_status;
    }
    //获得用户签到短信发送状态列表
    public function getUserCheckInSmsSentStatus()
    {
        return $this->user_checkin_sms_sent_status;
    }
    //获得用户签到短信发送状态列表
    public function getRaceApplySourceList()
    {
        return $this->raceApplySourceList;
    }
	//创建用户
	public function insertUser(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}
	/**
	 * 获取单个用户记录
	 * @param char $UserId 用户ID
	 * @param string $fields 所要获取的数据列
	 * @return array
	 */
	public function getUserInfo($UserId, $fields = '*')
	{
		$UserId = trim($UserId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`UserId` = ?', $UserId);
	}
	/**
	 * 获取单个用户记录
	 * @param char $UserId 用户ID
	 * @param string $fields 所要获取的数据列
	 * @return array
	 */
	public function getUserInfoByMobile($Mobile, $fields = '*')
	{
		$Mobile = trim($Mobile);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`phone` = ?', $Mobile);
	}

	/**
	 * 获取当前要新建的用户的ID
	 * @return array
	 */
	public function genNewUserId()
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "select xrace.nextval('UserId')";
		return $this->db->getOne($sql);
	}
	/**
	 * 更新单个用户记录
	 * @param char $UserId 用户ID
	 * @param array $bind 更新的数据列表
	 * @return boolean
	 */
	public function updateUserInfo($UserId, array $bind)
	{
		$UserId = trim($UserId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`UserId` = ?', $UserId);
	}
	/**
	 * 更新单个用户的实名认证状态记录
	 * @param char $UserId 用户ID
	 * @param array $bind
	 * @return boolean
	 */
	public function updateUserAuthInfo($UserId, array $bind)
	{
		$UserId = trim($UserId);
		$table_to_process = Base_Widget::getDbTable($this->table_auth);
		return $this->db->update($table_to_process, $bind, '`UserId` = ?', $UserId);
	}
	/**
	 * 插入一条用户的实名认证记录
	 * @param array $bind 更新的数据列表
	 * @return boolean
	 */
	public function insertUserAuthLog(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_auth_log);
		return $this->db->insert($table_to_process, $bind);
	}
	/**
	 * 获取单条用户实名认证的状态记录
	 * @param char $UserId 用户ID
	 * @param string $fields 所要获取的数据列
	 * @return array
	 */
	public function getUserAuthInfo($UserId, $fields = '*')
	{
		$UserId = trim($UserId);
		$table_to_process = Base_Widget::getDbTable($this->table_auth);
		return $this->db->selectRow($table_to_process, $fields, '`UserId` = ?', $UserId);
	}

	/**
	 * 获取用户数量
	 * @param $fields  所要获取的数据列
	 * @param $params 传入的条件列表
	 * @return integer
	 */
	public function getUserCount($params)
	{
		//生成查询列
		$fields = Base_common::getSqlFields(array("UserCount"=>"count(UserId)"));

		//获取需要用到的表名
		$table_to_process = Base_Widget::getDbTable($this->table);
		//性别判断
		$whereSex = isset($this->sex[$params['Sex']])?" sex = ".$params['Sex']." ":"";
		//实名认证判断
		$whereAuth = isset($this->auth_status[$params['AuthStatus']])?" auth_state = ".$params['AuthStatus']." ":"";
		//姓名
		$whereName = (isset($params['Name']) && trim($params['Name']))?" name like '%".$params['Name']."%' ":"";
		//昵称
		$whereNickName = (isset($params['NickName']) && trim($params['NickName']))?" nick_name like '%".$params['NickName']."%' ":"";
		//所有查询条件置入数组
		$whereCondition = array($whereSex,$whereName,$whereNickName,$whereAuth);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

		$sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
		return $this->db->getOne($sql);
	}
	/**
	 * 用户实名名认证通过
	 * @param $UserId 用户ID
	 * @param $UserInfo 更新的用户信息
	 * @param $AuthInfo 更新的用户实名认证状态数据
	 * @return boolean
	 */
	public function UserAuth($UserId,$UserInfo,$AuthInfo)
	{
		$UserAuthInfo = $this->getUserAuthInfo($UserId);
		$UserAuthInfo['auth_resp'] = $AuthInfo['auth_resp'];
		$UserAuthInfo['auth_result'] = 2;
		$UserAuthInfo['op_time'] = date("Y-m-d H:i:s",time());
		$UserAuthInfo['op_uid'] = $AuthInfo['op_uid'];
		//事务开始
		$this->db->begin();
		$UserProfileUpdate = $this->updateUserInfo($UserId,$UserInfo);
		$UserAuthInfoUpdate = $this->updateUserAuthInfo($UserId,$UserAuthInfo);
		$UserAuthLogInsert = $this->insertUserAuthLog($UserAuthInfo+array('auth_id'=>rand(111,999)));
		if($UserProfileUpdate && $UserAuthInfoUpdate && $UserAuthLogInsert)
		{
			$this->db->commit();
			return true;
		}
		else
		{
			$this->db->rollBack();
			return false;
		}
	}
	/**
	 * 用户实名名认证通过
	 * @param $UserId 用户ID
	 * @param $UserInfo 更新的用户信息
	 * @param $AuthInfo 更新的用户实名认证状态数据
	 * @return boolean
	 */
	public function UserUnAuth($UserId,$UserInfo,$AuthInfo)
	{

		$UserAuthInfo = $this->getUserAuthInfo($UserId);
		$UserAuthInfo['auth_resp'] = $AuthInfo['auth_resp'];
		$UserAuthInfo['auth_result'] = 0;
		$UserAuthInfo['op_time'] = date("Y-m-d H:i:s",time());
		$UserAuthInfo['op_uid'] = $AuthInfo['op_uid'];
		//事务开始
		$this->db->begin();
		$UserProfileUpdate = $this->updateUserInfo($UserId,$UserInfo);
		$UserAuthInfoUpdate = $this->updateUserAuthInfo($UserId,$UserAuthInfo);
		$UserAuthLogInsert = $this->insertUserAuthLog($UserAuthInfo+array('auth_id'=>rand(111,999)));
		if($UserProfileUpdate && $UserAuthInfoUpdate && $UserAuthLogInsert)
		{
			$this->db->commit();
			return true;
		}
		else
		{
			$this->db->rollBack();
			return false;
		}
	}

	/**
	 * 获取单个用户记录
	 * @param char $UserId 用户ID
	 * @param string $fields 所要获取的数据列
	 * @return array
	 */
	public function getUserAuthLog($UserId, $fields = '*')
	{
		$UserId = trim($UserId);
		$table_to_process = Base_Widget::getDbTable($this->table_auth_log);
		$sql = "select $fields from $table_to_process where  `UserId` = ? order by op_time desc";
		return $this->db->getAll($sql,  $UserId);
	}
	/**
	 * 获取实名认证记录
	 * @param $fields  所要获取的数据列
	 * @param $params 传入的条件列表
	 * @return array
	 */
	public function getAuthLog($params,$fields = array("*"))
	{
		//生成查询列
		$fields = Base_common::getSqlFields($fields);

		//获取需要用到的表名
		$table_to_process = Base_Widget::getDbTable($this->table_auth_log);
		//开始时间
		$whereStartDate = isset($params['StartDate'])?" op_time >= '".$params['StartDate']."' ":"";
		//开始时间
		$whereEndDate = isset($params['EndDate'])?" op_time <= '".date("Y-m-d",strtotime($params['EndDate'])+86400)."' ":"";
		//审核结果
		$whereAuthResult = isset($this->auth_status_log[$params['AuthResult']])?" auth_result = ".$params['AuthResult']." ":"";
		//操作人
		$whereManager = $params['ManagerId']?" op_uid = ".$params['ManagerId']." ":"";
		//所有查询条件置入数组
		$whereCondition = array($whereStartDate,$whereEndDate,$whereAuthResult,$whereManager);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		//获取用户数量
		if(isset($params['getCount'])&&$params['getCount']==1)
		{
			$AuthLogCount = $this->getAuthLogCount($params);
		}
		else
		{
			$AuthLogCount = 0;
		}
		$limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
		$order = " ORDER BY op_time desc";
		$sql = "SELECT $fields FROM $table_to_process where 1 ".$where." ".$order." ".$limit;
		$return = $this->db->getAll($sql);
		$AuthLog = array('AuthLog'=>array(),'AuthLogCount'=>$AuthLogCount);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AuthLog['AuthLog'][$value['auth_id']] = $value;
			}
		}
		return $AuthLog;
	}
	/**
	 * 获取用户数量
	 * @param $fields  所要获取的数据列
	 * @param $params 传入的条件列表
	 * @return integer
	 */
	public function getAuthLogCount($params)
	{
		//生成查询列
		$fields = Base_common::getSqlFields(array("AuthLOgCount"=>"count(auth_id)"));

		//获取需要用到的表名
		$table_to_process = Base_Widget::getDbTable($this->table_auth_log);
		//开始时间
		$whereStartDate = isset($params['StartDate'])?" op_time >= '".$params['StartDate']."' ":"";
		//开始时间
		$whereEndDate = isset($params['EndDate'])?" op_time <= '".date("Y-m-d",strtotime($params['EndDate'])+86400)."' ":"";
		//审核结果
		$whereAuthResult = isset($this->auth_status_log[$params['AuthResult']])?" auth_result = ".$params['AuthResult']." ":"";
		//操作人
		$whereManager = $params['ManagerId']?" op_uid = ".$params['ManagerId']." ":"";
		//所有查询条件置入数组
		$whereCondition = array($whereStartDate,$whereEndDate,$whereAuthResult,$whereManager);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);

		//生成条件列
		$sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
		return $this->db->getOne($sql);
	}
        
	/*
	 * 获得用户执照
	 */
	public function getUserLicenseList($params,$fields = array("*"))
	{
		//生成查询列
		$fields = Base_common::getSqlFields($fields);
		//获取需要用到的表名
		$table_to_process = Base_Widget::getDbTable($this->table_license);
		//获得执照ID
		$whereLicenseId = isset($params['LicenseId'])?" LicenseId = '".$params['LicenseId']."' ":"";
		//获得用户ID
		$whereUserId = isset($params['UserId'])?" UserId = '".$params['UserId']."' ":"";
		//获得组别ID
		$whereGroupId = isset($params['RaceGroupId'])?" RaceGroupId = '".$params['RaceGroupId']."' ":"";
		//获得执照状态
		if(isset($params['LicenseStatus']) && $params['LicenseStatus'] != 0)
		{
			$currentTime = date("Y-m-d",time());
			$p = explode("|",$params['LicenseStatus']);
			foreach($p as $LicenseStatus)
			{
				//生效中
				if($LicenseStatus == 3)
				{
					$t[] = "(LicenseStartDate > '$currentTime')";
				}
				//失效
				elseif($LicenseStatus == 1)
				{
					$t[] = "(LicenseStartDate <= '$currentTime' and LicenseEndDate >= '$currentTime')";
				}
				//已删除
				elseif($LicenseStatus == 2)
				{
					$t[] = "(LicenseEndDate < '$currentTime')";
				}
				else
				{
					$t[] = "(LicenseStatus = ".$params['LicenseStatus'].")";
				}
			}
			$whereLicenseStatus = implode(" or ",$t);
		}
		else
		{
			$whereLicenseStatus = "";
		}
		//需要排除的时间
		if(isset($params['ExceptionDate']))
		{
			$whereExceptionDate = "((LicenseStartDate <= '".$params['ExceptionDate']['StartDate']."' and LicenseEndDate >= '".$params['ExceptionDate']['StartDate']."')".
			" or "."(LicenseStartDate <= '".$params['ExceptionDate']['EndDate']."' and LicenseEndDate >= '".$params['ExceptionDate']['EndDate']."')"." or "."(LicenseStartDate <= '".$params['ExceptionDate']['StartDate']."' and LicenseEndDate >= '".$params['ExceptionDate']['EndDate']."'))";
		}
		else
		{
			$whereExceptionDate = "";
		}
		//需要检查的时间
		if(isset($params['DuringDate']))
		{
			$whereDuringDate = "(LicenseStartDate <= '".$params['DuringDate']['StartDate']."' and LicenseEndDate >= '".$params['DuringDate']['EndDate']."')";
		}
		else
		{
			$whereDuringDate = "";
		}
		//排除数据
		$whereExceptionId = isset($params['ExceptionId'])?" LicenseId != '".$params['ExceptionId']."' ":"";
		//排除数据
		$whereExceptionStatus = isset($params['ExceptionStatus'])?" LicenseStatus != '".$params['ExceptionStatus']."' ":"";
		//所有查询条件置入数组
		$whereCondition = array($whereLicenseId,$whereUserId,$whereGroupId,$whereLicenseStatus,$whereExceptionId,$whereExceptionStatus,$whereExceptionDate,$whereDuringDate);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		//获取用户数量
		if(isset($params['getCount'])&&$params['getCount']==1)
		{
			$UserLicenseCount = $this->getUserLicenseCount($params);
		}
		else
		{
			$UserLicenseCount = 0;
		}
		//存储的数据结构
		$UserLicense = array('UserLicenseList'=>array(),'UserLicenseCount'=>$UserLicenseCount);
		$sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by LicenseId,RaceGroupId";
		$return = $this->db->getAll($sql);
		if($return)
		{
			$UserLicense['UserLicenseList'] = $return;
		}
		return $UserLicense;
	}
        
        /*
         * 获得用户执照数量
         */
	public function getUserLicenseCount($params)
		{
            //生成查询列
            $fields = Base_common::getSqlFields(array("UserLicenseCount"=>"count(LicenseId)"));
			//获取需要用到的表名
			$table_to_process = Base_Widget::getDbTable($this->table_license);
			//获得执照ID
			$whereLicenseId = isset($params['LicenseId'])?" LicenseId = '".$params['LicenseId']."' ":"";
			//获得用户ID
			$whereUserId = isset($params['UserId'])?" UserId = '".$params['UserId']."' ":"";
			//获得组别ID
			$whereGroupId = isset($params['RaceGroupId'])?" RaceGroupId = '".$params['RaceGroupId']."' ":"";
			//获得执照状态
			if(isset($params['LicenseStatus']) && $params['LicenseStatus'] != 0)
			{
				$currentTime = date("Y-m-d",time());
				$p = explode("|",$params['LicenseStatus']);
				foreach($p as $LicenseStatus)
				{
					//生效中
					if($LicenseStatus == 3)
					{
						$t[] = "(LicenseStartDate > '$currentTime')";
					}
					//失效
					elseif($LicenseStatus == 1)
					{
						$t[] = "(LicenseStartDate <= '$currentTime' and LicenseEndDate >= '$currentTime')";
					}
					//已删除
					elseif($LicenseStatus == 2)
					{
						$t[] = "(LicenseEndDate < '$currentTime')";
					}
					else
					{
						$t[] = "(LicenseStatus = ".$params['LicenseStatus'].")";
					}
				}
				$whereLicenseStatus = implode(" or ",$t);
			}
			else
			{
				$whereLicenseStatus = "";
			}
			//需要排除的时间
			if(isset($params['ExceptionDate']))
			{
				$whereExceptionDate = "((LicenseStartDate <= '".$params['ExceptionDate']['StartDate']."' and LicenseEndDate >= '".$params['ExceptionDate']['StartDate']."')".
					" or "."(LicenseStartDate <= '".$params['ExceptionDate']['EndDate']."' and LicenseEndDate >= '".$params['ExceptionDate']['EndDate']."')".
					" or "."(LicenseStartDate <= '".$params['ExceptionDate']['StartDate']."' and LicenseEndDate >= '".$params['ExceptionDate']['EndDate']."'))";
			}
			else
			{
				$whereExceptionDate = "";
			}
			//需要检查的时间
			if(isset($params['DuringDate']))
			{
				$whereDuringDate = "(LicenseStartDate <= '".$params['DuringDate']['StartDate']."' and LicenseEndDate >= '".$params['DuringDate']['EndDate']."')";
			}
			else
			{
				$whereDuringDate = "";
			}
			//排除数据
			$whereExceptionId = isset($params['ExceptionId'])?" LicenseId != '".$params['ExceptionId']."' ":"";
			//排除数据
			$whereExceptionStatus = isset($params['ExceptionStatus'])?" LicenseStatus != '".$params['ExceptionStatus']."' ":"";
			//所有查询条件置入数组
			$whereCondition = array($whereLicenseId,$whereUserId,$whereGroupId,$whereLicenseStatus,$whereExceptionId,$whereExceptionStatus,$whereExceptionDate,$whereDuringDate);
			//生成条件列
            $where = Base_common::getSqlWhere($whereCondition);
			$sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
			return $this->db->getOne($sql);
        }
	/**
	 * 获取单个用户记录
	 * @param char $UserId 用户ID
	 * @param string $fields 所要获取的数据列
	 * @return array
	 */
	public function getUserLicense($LicenseId, $fields = '*')
	{
		$LicenseId = trim($LicenseId);
		$table_to_process = Base_Widget::getDbTable($this->table_license);
		return $this->db->selectRow($table_to_process, $fields, '`LicenseId` = ?', $LicenseId);
	}
	/*
	 * 获得用户执照状态
	 */
	public function getUserLicenseStatus($UserLicenseInfo)
	{
		return $this->user_license_status[$UserLicenseInfo['LicenseStatus']];
	}

	//新增单个用户执照信息
	public function insertUserLicense(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_license);
		return $this->db->insert($table_to_process, $bind);
	}
	//创建用户报名信息
	public function insertRaceApplyUserInfo(array $bind,array $bind_update)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_race);
		return $this->db->insert_update($table_to_process, $bind,$bind_update);
	}
    //删除用户报名信息
    public function deleteRaceApplyUserInfo($RaceId,$UserId)
    {
        $UserId = intval($UserId);
        $RaceId = intval($RaceId);
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        //如果指定用户
        if($UserId)
        {
            return $this->db->delete($table_to_process, '`UserId` = ? and `RaceId` = ?', array($UserId,$RaceId));
        }
        else
        {
            return $this->db->delete($table_to_process, '`RaceId` = ?', $RaceId);
        }
    }
	//更新单个用户执照信息
	public function updateUserLicense($LicenseId,array $bind)
	{
		$LicenseId = intval($LicenseId);
		$table_to_process = Base_Widget::getDbTable($this->table_license);
		return $this->db->update($table_to_process, $bind, '`LicenseId` = ?', $LicenseId);
	}
	//获取报名记录
	public function getRaceUserList($params,$fields = array('*'))
	{
		//生成查询列
		$fields = Base_common::getSqlFields($fields);
		//获取需要用到的表名
		$table_to_process = Base_Widget::getDbTable($this->table_race);
        //获得比赛ID
        $whereStage = isset($params['RaceStageId'])?" RaceStageId = '".$params['RaceStageId']."' ":"";
		//获得比赛ID
		$whereRace = isset($params['RaceId'])?" RaceId = '".$params['RaceId']."' ":"";
		//获得用户ID
		$whereUser = (isset($params['UserId']) && $params['UserId']!="0")?" UserId = '".$params['UserId']."' ":"";
		//获得组别ID
		$whereGroup = (isset($params['RaceGroupId'])  && $params['RaceGroupId']!=0)?" RaceGroupId = '".$params['RaceGroupId']."' ":"";
		//获得赛事ID
		$whereCatalog = isset($params['RaceCatalogId'])?" RaceCatalogId = '".$params['RaceCatalogId']."' ":"";
		//所有查询条件置入数组
		$whereCondition = array($whereCatalog,$whereUser,$whereGroup,$whereRace,$whereStage);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by BIB,TeamId,ApplyId desc";
        $return = $this->db->getAll($sql);
		return $return;
	}
	//获取报名记录
	public function getUserTeamList($params,$fields = array('*'))
	{
		//生成查询列
		$fields = Base_common::getSqlFields($fields);
		//获取需要用到的表名
		$table_to_process = Base_Widget::getDbTable($this->table_race_user_team);
		//获得用户ID
		$whereUser = isset($params['UserId'])?" UserId = '".$params['UserId']."' ":"";
		//获得组别ID
		$whereGroup = isset($params['RaceGroupId'])?" RaceGroupId = '".$params['RaceGroupId']."' ":"";
		//获得赛事ID
		$whereCatalog = isset($params['RaceCatalogId'])?" RaceCatalogId = '".$params['RaceCatalogId']."' ":"";
		//所有查询条件置入数组
		$whereCondition = array($whereCatalog,$whereUser,$whereGroup);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by TeamId,RaceCatalogId,RaceGroupId desc";
		$return = $this->db->getAll($sql);
		return $return;
	}
	public function insertUserTeam($bind)
	{
		//获取当前时间
		$CurrentTime = time();
		//生成入队时间和最后更新时间
		$bind['InTime'] = date("Y-m-d H:i:s",$CurrentTime);
		$bind['LastUpdateTime'] = date("Y-m-d H:i:s",$CurrentTime);
		//否则更新最后更新时间
		$updatebind = array('LastUpdateTime'=>$bind['LastUpdateTime']);
		$table_to_process = Base_Widget::getDbTable($this->table_race_user_team);
		return $this->db->insert_update($table_to_process, $bind,$updatebind);
	}
	/**
	 * 用户退出队伍
	 * @param integer $LogId
	 * @return boolean
	 */
	public function deleteUserTeam($LogId)
	{
		$LogId = intval($LogId);
		$table_to_process = Base_Widget::getDbTable($this->table_race_user_team);
		return $this->db->delete($table_to_process, '`LogId` = ?', $LogId);
	}
	//用户退出比赛
	public function deleteUserRace($ApplyId)
	{
		$ApplyId = intval($ApplyId);
		$table_to_process = Base_Widget::getDbTable($this->table_race);
		return $this->db->delete($table_to_process, '`ApplyId` = ?', $ApplyId);
	}
    //用户退出比赛
    public function deleteUserRaceByRace($RaceId,$RaceGroupId = 0)
    {
        $RaceId = intval($RaceId);
        $RaceGroupId = intval($RaceGroupId);
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        if($RaceGroupId>0)
        {
            return $this->db->delete($table_to_process, '`RaceId` = ? and `RaceGroupId` = ?', array($RaceId,$RaceGroupId));
        }
        else
        {
            return $this->db->delete($table_to_process, '`RaceId` = ?', $RaceId);
        }
    }
    //获取报名记录
    public function getRaceUserCheckInList($params,$fields = array('*'))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_stage_checkin);
        //获得比赛ID
        $whereUser = isset($params['RaceUserId'])?" RaceUserId = ".$params['RaceUserId']:"";
        //获得比赛ID
        $whereRaceStage = isset($params['RaceStageId'])?" RaceStageId = ".$params['RaceStageId']:"";
        //获得赛事ID
        $whereCatalog = isset($params['RaceCatalogId'])?" RaceCatalogId = ".$params['RaceCatalogId']:"";
        //所有查询条件置入数组
        $whereCondition = array($whereUser,$whereCatalog,$whereRaceStage);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by CheckInStatus,RaceCatalogId,RaceStageId,UserId desc";
        $return = $this->db->getAll($sql);
        return $return;
    }


    /**
     * 新增单个用户签到记录
     * @param string $fields 所要添加的数据列
     * @return array
     */
    public function insertUserCheckInInfo($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_stage_checkin);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 获取单个队伍记录
     * @param char $TeamId 队伍ID
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getTeamInfo($TeamId, $fields = '*')
    {
        $TeamId = intval($TeamId);
        $table_to_process = Base_Widget::getDbTable($this->table_team);
        return $this->db->selectRow($table_to_process, $fields, '`TeamId` = ?', $TeamId);
    }
    //ALTER TABLE `user_race` ADD `ApplySource` TINYINT(3) UNSIGNED NOT NULL COMMENT '报名数据来源：1|线上|2线下|待扩充' AFTER `ApplyId`;
}

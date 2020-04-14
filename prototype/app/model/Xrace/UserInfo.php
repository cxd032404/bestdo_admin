<?php
/**
 * 用户数据相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_UserInfo extends Base_Widget
{
	//声明所用到的表
    protected $table = 'user_info';
    protected $table_reg_info = 'UserReg';
    protected $table_reg_log = 'UserRegLog';
    protected $table_login = 'UserLogin';
    protected $table_reset = 'UserResetPassword';
    protected $table_reset_log = 'UserResetPasswordLog';
    protected $table_stage_checkin = 'user_stage_checkin';
    protected $table_auth = 'UserAuthCode';
    protected $table_auth_log = 'UserAuthCodeLog';

    //登录方式列表
    protected $loginSource = array('WeChat'=>"微信",'Weibo'=>"微博",'Mobile'=>"手机");
    //性别列表
	protected $sex = array('0'=>"保密",'1'=>"男",'2'=>"女");
    //性别列表
    protected $raceApplySourceList = array('0'=>"未知",'1'=>"线上","2"=>"线下");
    //实名认证状态
    protected $authStatuslist = array('0'=>"未认证",'1'=>"待认证",'2'=>"已认证");
    //提交时对应的实名认证状态名
    protected $authStatusListSubmit = array('0'=>"不通过",'2'=>"审核通过");
    //认证记录中对应的实名认证状态名
    protected $authStatus_log = array('0'=>"拒绝","2"=>"通过");
    //实名认证用到的证件类型列表
    protected $authIdType = array('1'=>"身份证","2"=>"护照","3"=>"港澳通行证","4"=>"台湾通行证");
    //用户执照状态
    protected $user_license_status = array('1'=>"生效中",'2'=>"已过期",'3'=>"即将生效",'4'=>"已删除");
    //用户签到状态
    protected $stage_user_checkin_status = array('0'=>'全部','2'=>"未签到",'1'=>"已签到");
    //用户检录状态
    protected $race_user_checkin_status = array('0'=>'全部','1'=>"已检录",'2'=>"未检录");
    //用户签到短信发送状态
    protected $user_checkin_sms_sent_status = array('3'=>"不需发送",'1'=>"待发送",'2'=>"已发送");
    //选手报名记录状态
    protected $user_apply_status = array('all'=>"全部",'0'=>"正常",'1'=>"DNS",'2'=>"DNF");
    //实名认证用到的证件类型列表
    protected $auth_id_type = array('1'=>"身份证","2"=>"护照");


    //用户需要通过验证码方式的动作列表
    protected $validate_code_action = array('IdModify'=>"更新证件信息",'MobileModify'=>"更新手机号码");
    //芯片归还状态
    protected $chip_return_status = array('all'=>"全部",'0'=>"未归还",'1'=>"已归还");

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
            return $this->authStatuslist;
        }
        else
        {
            return $this->authStatusListSubmit;
        }
    }
    //获取实名认证的证件列表
    public function getAuthIdType()
    {
        return $this->authIdType;
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
    public function getStageUserCheckInStatus()
    {
        return $this->stage_user_checkin_status;
    }
    //获得用户检录状态列表
    public function getRaceUserCheckInStatus()
    {
        return $this->race_user_checkin_status;
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
    //获得用户登录方式列表
    public function getLoginSourceList()
    {
        return $this->loginSource;
    }

    //获得用户需要通过验证码方式的动作列表
    public function getValidateCodeActionList()
    {
        return $this->validate_code_action;
    }
    //获得用户报名记录状态列表
    public function getUserApplyStatusList()
    {
        return $this->user_apply_status;
    }
    //获得芯片归还状态列表
    public function getChipReturnStatusList()
    {
        return $this->chip_return_status;
    }

    /**
     * 新增单个用户注册中间记录
     * @param array $bind 所要添加的数据列
     * @return boolean
     */
    public function insertRegInfo($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_reg_info);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 新增单个用户注册中间记录
     * @param array $bind 所要添加的数据列
     * @return boolean
     */
    public function insertRegLog($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_reg_log);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 更新单个用户注册中间记录
     * @param string $RegId 注册记录ID
     * @param array $bind 所要更新的数据列
     * @return boolean
     */
    public function updateRegInfo($RegId,$bind)
    {
        $RegId = intval($RegId);
        $table_to_process = Base_Widget::getDbTable($this->table_reg_info);
        return $this->db->update($table_to_process, $bind, '`RegId` = ?', $RegId);
    }
    /**
     * 获取单个用户注册中间记录
     * @param string $RegId 注册记录ID
     * @return array
     */
    public function getRegInfo($RegId)
    {
        $RegId = intval($RegId);
        $table_to_process = Base_Widget::getDbTable($this->table_reg_info);
        return $this->db->selectRow($table_to_process, "*", '`RegId` = ?', $RegId);
    }
    /**
 * 新增单个用户注册中间记录
 * @param array $bind 所要添加的数据列
 * @return boolean
 */
    public function deleteRegInfo($RegId)
    {
        $RegId = intval($RegId);
        $table_to_process = Base_Widget::getDbTable($this->table_reg_info);
        return $this->db->delete($table_to_process, '`RegId` = ?', $RegId);
    }
    /**
     * 删除单个用户注册日志记录
     * @param array $bind 所要添加的数据列
     * @return boolean
     */
    public function deleteRegInfoByMobile($Mobile)
    {
        $Mobile = trim($Mobile);
        $table_to_process = Base_Widget::getDbTable($this->table_reg_info);
        return $this->db->delete($table_to_process, '`Mobile` = ?', $Mobile);
    }
    /**
     * 删除单个用户注册日志记录
     * @param array $bind 所要添加的数据列
     * @return boolean
     */
    public function deleteRegLog($RegId)
    {
        $RegId = intval($RegId);
        $table_to_process = Base_Widget::getDbTable($this->table_reg_log);
        return $this->db->delete($table_to_process, '`RegId` = ?', $RegId);
    }
    /**
     * 删除单个用户注册日志记录
     * @param array $bind 所要添加的数据列
     * @return boolean
     */
    public function deleteRegLogByMobile($Mobile)
    {
        $Mobile = trim($Mobile);
        $table_to_process = Base_Widget::getDbTable($this->table_reg_log);
        return $this->db->delete($table_to_process, '`Mobile` = ?', $Mobile);
    }


    /**
     * 新增单个用户重置密码中间记录
     * @param array $bind 所要添加的数据列
     * @return boolean
     */
    public function insertResetInfo($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_reset);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 新增单个用户重置密码日志
     * @param array $bind 所要添加的数据列
     * @return boolean
     */
    public function insertResetLog($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_reset_log);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 更新单个用户重置密码中间记录
     * @param string $ResetId 重置密码记录ID
     * @param array $bind 所要更新的数据列
     * @return boolean
     */
    public function updateResetInfo($ResetId,$bind)
    {
        $ResetId = intval($ResetId);
        $table_to_process = Base_Widget::getDbTable($this->table_reset);
        return $this->db->update($table_to_process, $bind, '`ResetId` = ?', $ResetId);
    }
    /**
     * 获取单个用户重置密码中间记录
     * @param string $ResetId 重置密码记录ID
     * @return array
     */
    public function getResetInfo($ResetId)
    {
        $ResetId = intval($ResetId);
        $table_to_process = Base_Widget::getDbTable($this->table_reset);
        return $this->db->selectRow($table_to_process, "*", '`ResetId` = ?', $ResetId);
    }
    /**
     * 新增单个用户重置密码中间记录
     * @param array $bind 所要添加的数据列
     * @return boolean
     */
    public function deleteResetInfo($ResetId)
    {
        $ResetId = intval($ResetId);
        $table_to_process = Base_Widget::getDbTable($this->table_reset);
        return $this->db->delete($table_to_process, '`ResetId` = ?', $ResetId);
    }

    /**
     * 根据第三方身份数据获取单个用户注册中间记录
     * @param string $RegPlatform 第三方平台
     * @param string $RegPlatform 第三方平台
     * @return array
     */
    public function getRegInfoByThirdParty($RegPlatform,$RegKey)
    {
        $RegPlatform = trim($RegPlatform);
        $RegKey = trim($RegKey);
        $table_to_process = Base_Widget::getDbTable($this->table_reg_info);
        return $this->db->selectRow($table_to_process, "*", '`RegPlatform` = ? and `RegKey` = ?', array($RegPlatform,$RegKey));
    }
    /**
     * 通过手机和短信验证码获取单个用户重置密码中间记录
     * @param string $Mobile 用户手机号码
     * @param string $Code 短信验证码
     * @return array
     *  */
    public function getResetInfoByMobile($Mobile)
    {
        $Mobile = trim($Mobile);
        $table_to_process = Base_Widget::getDbTable($this->table_reset);
        return $this->db->selectRow($table_to_process, '*', '`Mobile` = ?', array($Mobile));
    }
    /**
     * 通过手机和短信验证码获取单个用户注册中间记录
     * @param string $Mobile 用户手机号码
     * @param string $Code 短信验证码
     * @return array
     *  */
    public function getRegInfoByMobile($Mobile)
    {
        $Mobile = trim($Mobile);
        $table_to_process = Base_Widget::getDbTable($this->table_reg_info);
        return $this->db->selectRow($table_to_process, '*', '`Mobile` = ?', array($Mobile));
    }
    public function getRegLogByMobile($Mobile)
    {
        $Mobile = trim($Mobile);
        $table_to_process = Base_Widget::getDbTable($this->table_reg_log);
        return $this->db->selectRow($table_to_process, '*', '`Mobile` = ?', array($Mobile));
    }
    /**
    /**
     * 获取单个用户记录
     * @param char $UserId 用户ID
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getUserInfo($UserId, $fields = '*',$Cache=1)
    {
        $oRedis = new Base_Cache_Redis("xrace");
        //获取缓存
        if($Cache == 1)
        {
            //获取缓存
            $m = $oRedis->get("UserInfo_".$UserId);
            //缓存解开
            $UserInfo = json_decode($m,true);
            //如果结果集不有效
            if(!isset($UserInfo['UserId']))
            {
                //缓存置为0
                $Cache = 0;
            }
            else
            {
                //echo "UserInfo cahced";
            }
        }
        if($Cache == 0)
        {
            //从数据库中获取
            $UserInfo = $this->getUser($UserId, "*");
            //如果用户信息中的联系方式为空
            if(strlen(trim($UserInfo['Mobile']))<=8 )
            {
                //可以报名
                $UserInfo['NeedMobile'] = 1;
            }
            else
            {
                //可以报名
                $UserInfo['NeedMobile'] = 0;
            }
            //如果用户信息中包含不少于六位的证件号码 和 不少于两位的姓名
            if(strlen(trim($UserInfo['IdNo']))>=6 && strlen(trim($UserInfo['Name']))>=2)
            {
                //可以报名
                $UserInfo['ReadyToRace'] =1;
            }
            else
            {
                //不可以报名
                $UserInfo['ReadyToRace'] =0;
            }
            //如果结果集有效
            if(isset($UserInfo['UserId']))
            {
                //写入缓存
                $oRedis -> set("UserInfo_".$UserId,json_encode($UserInfo),86400);
            }
        }
        //如果结果集有效，并且获取的字段列表不是全部
        if(isset($UserInfo['UserId']) && $fields != "*")
        {
            //分解字段列表
            $fieldsList = explode(",",$fields);
            //循环结果集
            foreach($UserInfo as $key => $value)
            {
                //如果不在字段列表中且不是主键+身份证+姓名
                if(!in_array($key,$fieldsList) && !in_array($key, array("UserId","IdNo","UserName","ReadyToRace")))
                {
                    //删除
                    unset($UserInfo[$key]);
                }
            }
        }
        //返回结果
        return $UserInfo;
    }
	/**
 * 获取单个用户记录
 * @param char $UserId 用户ID
 * @param string $fields 所要获取的数据列
 * @return array
 */
    public function getUser($UserId, $fields = '*')
    {
        $UserId = intval($UserId);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`UserId` = ?', $UserId);
    }
    /**
     * 获取单个用户记录
     * @param char $UserId 用户ID
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function deleteUserByMobile($Mobile)
    {
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->delete($table_to_process, '`Mobile` = ?', $Mobile);
    }
    /**
     * 获取单个比赛用户记录
     * @param char $UserId 用户ID
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getRaceUser($RaceUserId, $fields = '*')
    {
        $RaceUserId = intval($RaceUserId);
        $table_to_process = Base_Widget::getDbTable($this->table_race_user);
        return $this->db->selectRow($table_to_process, $fields, '`RaceUserId` = ?', $RaceUserId);
    }
    /**
     * 新增单个用户记录
     * @param array $bind 所要添加的数据列
     * @return boolean
     */
    public function insertUser($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 新增单个用户记录
     * @param array $bind 所要添加的数据列
     * @return boolean
     */
    public function insertRaceUser($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_race_user);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 更新单个用户记录
     * @param char $UserId 用户ID
     * @param array $bind 更新的数据列表
     * @return boolean
     */
    public function updateUser($UserId, array $bind)
    {
        $UserId = intval($UserId);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->update($table_to_process, $bind, '`UserId` = ?', $UserId);
    }
    public function updateRaceUser($RaceUserId, array $bind)
    {
        $RaceUserId = intval($RaceUserId);
        $table_to_process = Base_Widget::getDbTable($this->table_race_user);
        return $this->db->update($table_to_process, $bind, '`RaceUserId` = ?', $RaceUserId);
    }
    /**
     * 根据指定字段获取单个用户记录
     * @param char $Column 字段列名
     * @param char $Column 字段值
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getUserByColumn($Column,$Value, $fields = '*')
    {
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`'.$Column.'` = ?', $Value);
    }
    /**
     * 根据指定字段获取单个比赛用户记录
     * @param char $Column 字段列名
     * @param char $Column 字段值
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getRaceUserByColumn($Column,$Value, $fields = '*')
    {
        $table_to_process = Base_Widget::getDbTable($this->table_race_user);
        return $this->db->selectRow($table_to_process, $fields, '`'.$Column.'` = ?', $Value);
    }
    /**
     * 根据指定字段获取单个用户注册记录
     * @param char $UserId 用户ID
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getUserRegByColumn($Column,$Value, $fields = '*')
    {
        $table_to_process = Base_Widget::getDbTable($this->table_reg_info);
        return $this->db->select($table_to_process, $fields, '`'.$Column.'` = ?', $Value);
    }
    /**
     * 获取用户列表
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return array
     */
    public function getUserList($params,$fields = array("*"))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        if($params["UserType"]=="RaceUser")
        {
            //获取需要用到的表名
            $table_to_process = Base_Widget::getDbTable($this->table_race_user);
            $order = " ORDER BY RaceUserId desc";

        }
        else
        {
            //获取需要用到的表名
            $table_to_process = Base_Widget::getDbTable($this->table);
            $order = " ORDER BY UserId desc";

        }
        //性别判断
        $whereSex = isset($this->sex[$params['Sex']])?" Sex = '".$params['Sex']."' ":"";
        //实名认证判断
        $whereAuth = isset($this->authStatuslist[$params['AuthStatus']])?" AuthStatus = ".$params['AuthStatus']." ":"";
        //姓名
        $whereName = (isset($params['Name']) && trim($params['Name']))?" name like '%".$params['Name']."%' ":"";
        //昵称
        $whereNickName = (isset($params['NickName']) && trim($params['NickName']))?" NickName like '%".$params['NickName']."%' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereSex,$whereName,$whereNickName,$whereAuth);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //获取用户数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $UserCount = $this->getUserCount($params);
        }
        else
        {
            $UserCount = 0;
        }
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." ".$order." ".$limit;
        $return = $this->db->getAll($sql);
        $UserList = array('UserList'=>array(),'UserCount'=>$UserCount);
        if(count($return))
        {
            foreach($return as $key => $value)
            {

                if($params["UserType"]=="RaceUser")
                {
                    $UserList['UserList'][$value['RaceUserId']] = $value;

                }
                else
                {
                    $UserList['UserList'][$value['UserId']] = $value;

                }
            }
        }
        else
        {
            return $UserList;
        }
        return $UserList;
    }
    /**
     * 获取用户数量
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return integer
     */
    public function getUserCount($params)
    {
        if($params['UserType']=="RaceUser")
        {
            //获取需要用到的表名
            $table_to_process = Base_Widget::getDbTable($this->table_race_user);
            //生成查询列
            $fields = Base_common::getSqlFields(array("UserCount"=>"count(RaceUserId)"));
        }
        else
        {
            //获取需要用到的表名
            $table_to_process = Base_Widget::getDbTable($this->table);
            //生成查询列
            $fields = Base_common::getSqlFields(array("UserCount"=>"count(UserId)"));
        }
        //性别判断
        $whereSex = isset($this->sex[$params['Sex']])?" Sex = '".$params['Sex']."' ":"";
        //实名认证判断
        $whereAuth = isset($this->authStatuslist[$params['AuthStatus']])?" AuthStatus = ".$params['AuthStatus']." ":"";
        //姓名
        $whereName = (isset($params['Name']) && trim($params['Name']))?" name like '%".$params['Name']."%' ":"";
        //昵称
        $whereNickName = (isset($params['NickName']) && trim($params['NickName']))?" NickName like '%".$params['NickName']."%' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereSex,$whereName,$whereNickName,$whereAuth);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    /**
     * 手机注册
     * @param string $Mobile 用户手机号码
     * @param string $Password 已经经过一次MD5的用户密码
     * @return array
     */
    public function MobileReg($Mobile,$Password)
    {
        $oRedis = new Base_Cache_Redis("xrace");
        //获取缓存
        $m = $oRedis->get("Mobile_".$Mobile);
        //如果获取到的数据为0
        if(intval($m)==0)
        {
            //根据手机号码查询用户
            $UserInfo = $this->getUserByColumn("Mobile",$Mobile);
            //如果查询到
            if(isset($UserInfo['UserId']) && strlen($UserInfo['Password'])==32)
            {
                //用户已经存在
                return -1;
            }
            else
            {
                //根据手机号码获取注册记录
                $RegInfo = $this->getRegInfoByMobile($Mobile);
                //如果找到记录
                if(isset($RegInfo['RegId']))
                {
                    $ValidateCode = sprintf("%06d",rand(1,999999));
                    //更新注册记录，生成新的验证码和过期时间
                    $bind = array('ExceedTime'=>date("Y-m-d H:i:s",time()+3600),'ValidateCode' => $ValidateCode);
                    $Update = $this->updateRegInfo($RegInfo['RegId'],$bind);
                    //更新成功
                    if($Update)
                    {
                        $params = array(
                            "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                            "Mobile"=> $RegInfo['Mobile'],
                            "SMSCode"=>"SMS_Validate_Code"
                        );
                        Base_common::dayuSMS($params);
                        return array("RegId"=>$RegInfo['RegId']);
                    }
                    else
                    {
                        return 0;
                    }
                }
                else
                {
                    //生成随机验证码
                    $ValidateCode = sprintf("%06d",rand(1,999999));
                    //获取当前时间
                    $Time = time();
                    //用户注册记录
                    $UserRegInfo = array("Password"=>($Password=="")?"":md5($Password),"RegPlatform"=>"Mobile","Mobile"=>$Mobile,"RegKey"=>$Mobile,"RegTime"=>date("Y-m-d H:i:s",$Time),"ValidateCode"=>$ValidateCode,'ExceedTime'=>date("Y-m-d H:i:s",$Time+3600));
                    $RegId = $this->insertRegInfo($UserRegInfo);
                    //如果写入成功
                    if($RegId)
                    {
                        $params = array(
                            "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                            "Mobile"=> $Mobile,
                            "SMSCode"=>"SMS_Validate_Code"
                        );
                        Base_common::dayuSMS($params);
                        //通知前端需要进一步获取手机
                        return array('RegId'=>$RegId);
                    }
                    else
                    {
                        return false;
                    }
                }
            }
        }
        else
        {
            //根据手机号码查询用户
            $UserInfo = $this->getUserByColumn("Mobile",$Mobile);
            //如果查询到
            if(isset($UserInfo['UserId']) && strlen($UserInfo['Password'])==32)
            {
                //用户已经存在
                return -1;
            }
            else
            {
                //根据手机号码获取注册记录
                $RegInfo = $this->getRegInfoByMobile($Mobile);
                //如果找到记录
                if(isset($RegInfo['RegId']))
                {
                    $ValidateCode = sprintf("%06d",rand(1,999999));
                    //更新注册记录，生成新的验证码和过期时间
                    $bind = array('ExceedTime'=>date("Y-m-d H:i:s",time()+3600),'ValidateCode' => $ValidateCode);
                    $Update = $this->updateRegInfo($RegInfo['RegId'],$bind);
                    //更新成功
                    if($Update)
                    {
                        $params = array(
                            "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                            "Mobile"=> $RegInfo['Mobile'],
                            "SMSCode"=>"SMS_Validate_Code"
                        );
                        Base_common::dayuSMS($params);
                        return array("RegId"=>$RegInfo['RegId']);
                    }
                    else
                    {
                        return 0;
                    }
                }
                else
                {
                    //生成随机验证码
                    $ValidateCode = sprintf("%06d",rand(1,999999));
                    //获取当前时间
                    $Time = time();
                    //用户注册记录
                    $UserRegInfo = array("RegPlatform"=>"Mobile","Mobile"=>$Mobile,"RegKey"=>$Mobile,"RegTime"=>date("Y-m-d H:i:s",$Time),"ValidateCode"=>$ValidateCode,'ExceedTime'=>date("Y-m-d H:i:s",$Time+3600));
                    $RegId = $this->insertRegInfo($UserRegInfo);
                    //如果写入成功
                    if($RegId)
                    {
                        $params = array(
                            "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                            "Mobile"=> $Mobile,
                            "SMSCode"=>"SMS_Validate_Code"
                        );
                        Base_common::dayuSMS($params);
                        //通知前端需要进一步获取手机
                        return array('RegId'=>$RegId);
                    }
                    else
                    {
                        return false;
                    }
                }
            }
        }
    }
    /**
     * 手机登录
     * @param string $Mobile 用户手机号码
     * @param string $Password 已经经过一次MD5的用户密码
     * @return array
     */
    public function MobileLogin($Mobile,$Password)
    {
        $oRedis = new Base_Cache_Redis("xrace");
        //获取缓存
        $m = $oRedis->get("Mobile_".$Mobile);
        //如果获取到的数据为0
        if(intval($m)==0)
        {
            //根据手机号码查询用户
            $UserInfo = $this->getUserByColumn("Mobile",$Mobile);
            //如果查询到
            if(isset($UserInfo['UserId']))
            {
                //如果密码为空 表示通过其他方式登陆过
                if($UserInfo['Password']=="")
                {
                    //引导用户通过其他方式登录
                    return -1;
                }
                else
                {
                    //如果密码匹配
                    if(md5($Password)==$UserInfo['Password'])
                    {
                        //写入缓存
                        $oRedis -> set("Mobile_".$Mobile,$UserInfo['UserId'],86400);
                        //更新用户数据缓存
                        $UserInfo = $this->getUserInfo($UserInfo['UserId'],"*",0);
                        return $UserInfo;
                    }
                    else
                    {
                        //返回失败
                        return false;
                    }
                }
            }
            else
            {
                return -2;
            }
        }
        else
        {
            //根据手机号码查询用户
            $UserInfo = $this->getUserByColumn("Mobile",$Mobile);
            //如果查询到
            if(isset($UserInfo['UserId']))
            {
                //缓存检查
                return $UserInfo;
            }
            else
            {
                return -2;
            }
        }
    }
    /**
     * 第三方登录
     * @param array $LoginData 登陆用的数据集
     * @param string $LoginSource 第三方登录的来源
     * @return array
     */
    public function ThirdPartyLogin($LoginData,$LoginSource)
    {
        $oRedis = new Base_Cache_Redis("xrace");
        switch ($LoginSource)
        {
            case "WeChat":
                if(isset($LoginData['openid']))
                {
                    //获取缓存
                    $m = $oRedis->get("ThirdParty_".$LoginSource."_".$LoginData['openid']);
                    //缓存数据解包
                    $m = json_decode($m,true);
                    //如果获取到的数据为0
                    if(intval($m['UserId'])==0)
                    {
                        //根据第三方平台ID查询用户
                        $UserInfo = $this->getUserByColumn("WeChatId",$LoginData['openid'],"UserId,WeChatInfo,LastLoginTime,LastLoginSource");
                        //如果查询到
                        if(isset($UserInfo['UserId']))
                        {
                            //微信数据解包
                            $UserInfo['WeChatInfo'] = json_decode($UserInfo['WeChatInfo'],true);
                            //用户数据比对
                            if(array_diff($UserInfo['WeChatInfo'],$LoginData))
                            {
                                //echo "UserInfo Cached";
                            }
                            else
                            {
                                //更新用户数据
                                $WeChatInfo = $LoginData;
                                $UserInfoUpdate = array('WeChatInfo' => json_encode($WeChatInfo));
                                $this->updateUser($UserInfo['UserId'], $UserInfoUpdate);
                            }
                            //写缓存
                            $UserInfoCache = array_merge($LoginData,array("UserId"=>$UserInfo['UserId']));
                            $oRedis->set("ThirdParty_".$LoginSource."_".$LoginData['openid'],json_encode($UserInfoCache),86400);
                            return $UserInfoCache;
                        }
                        else
                        {
                            //根据第三方平台数据获取注册中间记录
                            $RegInfo = $this->getRegInfoByThirdParty("WeChat",$LoginData['openid']);
                            //如果找到
                            if($RegInfo['RegId'])
                            {
                                /*
                                //如果当前手机号码在验证有效期内
                                if(strtotime($RegInfo['ExceedTime'])>=time())
                                {
                                    $params = array(
                                        "smsContent" => array("code"=>$RegInfo['ValidateCode'],"product"=>"淘赛体育"),
                                        "Mobile"=> $RegInfo['Mobile'],
                                        "SMSCode"=>"SMS_Validate_Code"
                                    );
                                    Base_common::dayuSMS($params);
                                    return array('RegId'=>$RegInfo['RegId'],'NeedMobile'=>0);
                                }
                                else
                                {
                                    //通知前端需要进一步获取手机
                                    return array('RegId'=>$RegInfo['RegId'],'NeedMobile'=>1);
                                }
                                */
                                //通知前端需要进一步获取手机
                                return array('RegId'=>$RegInfo['RegId'],'NeedMobile'=>1);
                            }
                            else
                            {
                                //获取当前时间
                                $Time = time();
                                //创建用户注册记录
                                $UserRegInfo = array("RegPlatform"=>"WeChat","RegKey"=>$LoginData['openid'],"RegTime"=>date("Y-m-d H:i:s",$Time),"ValidateCode"=>"","ThirdPartyInfo"=>json_encode($LoginData));
                                $RegId = $this->insertRegInfo($UserRegInfo);
                                //如果写入成功
                                if($RegId)
                                {
                                    //通知前端需要进一步获取手机
                                    return array('RegId'=>$RegId,'NeedMobile'=>1);
                                }
                                else
                                {
                                    return false;
                                }
                            }
                        }
                    }
                    else//拿到缓存
                    {
                        //获取用户信息
                        $UserInfo = $this->getUserInfo($m['UserId']);
                        if(isset($UserInfo['UserId']))
                        {
                            //微信数据解包
                            $UserInfo['WeChatInfo'] = json_decode($UserInfo['WeChatInfo'],true);
                            //用户数据比对
                            if(!array_diff($UserInfo['WeChatInfo'],$LoginData))
                            {
                                //echo "UserInfo Cached";
                            }
                            else
                            {
                                //更新用户数据
                                $WeChatInfo = $LoginData;
                                //待更新微信的数据
                                $UserInfoUpdate = array('WeChatInfo' => json_encode($WeChatInfo));
                                //更新微信数据
                                $this->updateUser($UserInfo['UserId'], $UserInfoUpdate);
                                //重新获取用户数据
                                $UserInfo = $this->getUserInfo($UserInfo['UserId'],"*",0);
                            }
                            //写缓存
                            $UserInfoCache = array_merge($LoginData,array("UserId"=>$UserInfo['UserId']));
                            $oRedis->set("ThirdParty_".$LoginSource."_".$LoginData['openid'],json_encode($UserInfoCache),86400);
                            return $UserInfoCache;
                        }
                        else
                        {
                            //获取当前时间
                            $Time = time();
                            //创建用户注册记录
                            $UserRegInfo = array("RegPlatform"=>"WeChat","RegKey"=>$LoginData['openid'],"RegTime"=>date("Y-m-d H:i:s",$Time),"ValidateCode"=>"","ThirdPartyInfo"=>json_encode($LoginData));
                            $RegId = $this->insertRegInfo($UserRegInfo);
                            //如果写入成功
                            if($RegId)
                            {
                                //通知前端需要进一步获取手机
                                return array('RegId'=>$RegId,'NeedMobile'=>1);
                            }
                            else
                            {
                                return false;
                            }
                        }
                    }
                }
                else
                {
                    return false;
                }
        }
    }
    /**
     * 第三方登录
     * @param array $LoginData 登陆用的数据集
     * @param string $LoginSource 第三方登录的来源
     * @return array
     */
    public function ThirdPartyLoginNew($LoginData,$LoginSource)
    {
        $oRedis = new Base_Cache_Redis("xrace");
        switch ($LoginSource)
        {
            case "WeChat":
                if(isset($LoginData['openid']))
                {
                    //获取缓存
                    $m = $oRedis->get("ThirdParty_".$LoginSource."_".$LoginData['openid']);
                    //缓存数据解包
                    $m = json_decode($m,true);
                    //如果获取到的数据为0
                    if(intval($m['UserId'])==0)
                    {
                        //根据第三方平台ID查询用户
                        $UserInfo = $this->getUserByColumn("WeChatId",$LoginData['openid'],"UserId,WeChatInfo,LastLoginTime,LastLoginSource");
                        //如果查询到
                        if(isset($UserInfo['UserId']))
                        {
                            //微信数据解包
                            $UserInfo['WeChatInfo'] = json_decode($UserInfo['WeChatInfo'],true);
                            //用户数据比对
                            if(array_diff($UserInfo['WeChatInfo'],$LoginData))
                            {
                                //echo "UserInfo Cached";
                            }
                            else
                            {
                                //更新用户数据
                                $WeChatInfo = $LoginData;
                                $UserInfoUpdate = array('WeChatInfo' => json_encode($WeChatInfo));
                                $this->updateUser($UserInfo['UserId'], $UserInfoUpdate);
                            }
                            //写缓存
                            $UserInfoCache = array_merge($LoginData,array("UserId"=>$UserInfo['UserId']));
                            $oRedis->set("ThirdParty_".$LoginSource."_".$LoginData['openid'],json_encode($UserInfoCache),86400);
                            return $UserInfoCache;
                        }
                        else
                        {
                            //获取当前时间
                            $Time = date("Y-m-d H:i:s",time());
                            $this->db->begin();
                            $UserInfo = array('WeChatId' => $LoginData['openid'],'WeChatInfo' => json_encode($LoginData),'LastLoginSource' => "WeChat",'RegTime'=>$Time,'LastLoginTime'=>$Time);
                            //创建用户
                            $UserId = $this->insertUser($UserInfo);
                            //写入注册记录
                            $UserRegInfo = array("RegPlatform"=>"WeChat","RegKey"=>$LoginData['openid'],"RegTime"=>$Time,"ValidateCode"=>"","ThirdPartyInfo"=>json_encode($LoginData));
                            $insertRegLog = $this->insertRegLog($UserRegInfo);
                            if($UserId)
                            {
                                echo "User Created";
                                $this->db->commit();
                                return array("UserId"=>$UserId,"UserInfo"=>$this->getUserInfo($UserId), "LoginSource"=>"WeChat");
                            }
                            else
                            {
                                $this->db->rollBack();
                                return false;
                            }
                        }
                    }
                    else//拿到缓存
                    {
                        //获取用户信息
                        $UserInfo = $this->getUserInfo($m['UserId']);
                        if(isset($UserInfo['UserId']))
                        {
                            //微信数据解包
                            $UserInfo['WeChatInfo'] = json_decode($UserInfo['WeChatInfo'],true);
                            //用户数据比对
                            if(!array_diff($UserInfo['WeChatInfo'],$LoginData))
                            {
                                //echo "UserInfo Cached";
                            }
                            else
                            {
                                //更新用户数据
                                $WeChatInfo = $LoginData;
                                //待更新微信的数据
                                $UserInfoUpdate = array('WeChatInfo' => json_encode($WeChatInfo));
                                //更新微信数据
                                $this->updateUser($UserInfo['UserId'], $UserInfoUpdate);
                                //重新获取用户数据
                                $UserInfo = $this->getUserInfo($UserInfo['UserId'],"*",0);
                            }
                            //写缓存
                            $UserInfoCache = array_merge($LoginData,array("UserId"=>$UserInfo['UserId']));
                            $oRedis->set("ThirdParty_".$LoginSource."_".$LoginData['openid'],json_encode($UserInfoCache),86400);
                            return $UserInfoCache;
                        }
                        else
                        {
                            //获取当前时间
                            $Time = date("Y-m-d H:i:s",time());
                            $this->db->begin();
                            $UserInfo = array('WeChatId' => $LoginData['openid'],'WeChatInfo' => json_encode($LoginData),'LastLoginSource' => "WeChat",'RegTime'=>$Time,'LastLoginTime'=>$Time);
                            //创建用户
                            $UserId = $this->insertUser($UserInfo);
                            //写入注册记录
                            $UserRegInfo = array("RegPlatform"=>"WeChat","RegKey"=>$LoginData['openid'],"RegTime"=>$Time,"ValidateCode"=>"","ThirdPartyInfo"=>json_encode($LoginData));
                            $insertRegLog = $this->insertRegLog($UserRegInfo);
                            if($UserId)
                            {
                                echo "User Created";
                                $this->db->commit();
                                return array("UserId"=>$UserId,"UserInfo"=>$this->getUserInfo($UserId), "LoginSource"=>"WeChat");
                            }
                            else
                            {
                                $this->db->rollBack();
                                return false;
                            }
                        }
                    }
                }
                else
                {
                    return false;
                }
        }
    }
    /**
     * 第三方登录时绑定手机
     * @param string $Mobile 用户手机号码
     * @param string $RegId 注册ID
     * @return array
     */
    public function thirdPartyRegMobile($RegId,$Mobile)
    {
        //获取注册记录
        $RegInfo = $this->getRegInfo($RegId);
        //如果找到记录
        if(isset($RegInfo['RegId']))
        {
            //如果在有效期内
            //if(strtotime($RegInfo['ExceedTime'])>=time())
            if(0)
            {
                return 1;
            }
            else
            {
                $ValidateCode = sprintf("%06d",rand(1,999999));
                //更新注册记录，生成新的验证码和过期时间
                $bind = array('ExceedTime'=>date("Y-m-d H:i:s",time()+3600),'ValidateCode' => $ValidateCode);
                $Update = $this->updateRegInfo($RegInfo['RegId'],$bind);
                //更新成功
                if($Update)
                {
                    $params = array(
                        "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                        "Mobile"=> $RegInfo['Mobile'],
                        "SMSCode"=>"SMS_Validate_Code"
                    );
                    $Log = Base_common::dayuSMS($params);

                    $filePath = __APP_ROOT_DIR__ . "Log" . "/" . "SMS" . "/";
                    $fileName = date("Ymd",time()) . ".php";
                    Base_Common::appendLog($filePath, $fileName, $Log.'/r/n', "SMSLog");
                    return 1;
                }
                else
                {
                    return 0;
                }
            }
        }
        else
        {
            return 0;
        }
    }
    /**
     * 注册时的短信验证码校验
     * @param string $Mobile 用户手机号码
     * @param string $ValidateCode 短信验证码
     * @return array
     */
    public function regMobileAuth($Mobile,$ValidateCode)
    {
        //获取注册记录
        $RegInfo = $this->getRegInfoByMobile($Mobile);

        //如果找到记录
        if(isset($RegInfo['RegId']))
        {
            //如果在有效期内
            if(strtotime($RegInfo['ExceedTime'])>=time() | 1)
            {
                if($RegInfo['ValidateCode']==$ValidateCode)
                {
                    if($RegInfo['RegPlatform'] == "WeChat")
                    {
                        $UserInfo['WeChatId'] = $RegInfo['RegKey'];
                        $UserInfo['WeChatInfo'] = $RegInfo['ThirdPartyInfo'];
                    }
                    elseif($RegInfo['RegPlatform'] == "Weibo")
                    {
                        $UserInfo['WeiboId'] = $RegInfo['RegPlatform'];
                    }
                    elseif($RegInfo['RegPlatform'] == "QQ")
                    {
                        $UserInfo['QQ'] = $RegInfo['RegPlatform'];
                    }
                    elseif($RegInfo['RegPlatform'] == "Mobile")
                    {
                        if($RegInfo['Password']=="")
                        {
                            return array("RegId"=>$RegInfo['RegId'],"NeedPassword"=>1);
                        }
                        //手机注册只保留密码
                        $UserInfo['Password'] = $RegInfo['Password'];
                    }
                    //最后登录方式
                    $UserInfo['LastLoginSource'] = $RegInfo['RegPlatform'];
                    //根据手机号码查询用户
                    $MobileUserInfo = $this->getUserByColumn("Mobile",$Mobile);
                    //如果有用户已经在使用
                    if(isset($MobileUserInfo['UserId']))
                    {
                        //获取当前时间
                        $Time = time();
                        //最后登录时间
                        $UserInfo['LastLoginTime'] = date("Y-m-d H:i:s",$Time);
                        $this->db->begin();
                        //更新为同一人
                        $updateUser = $this->updateUser($MobileUserInfo['UserId'],$UserInfo);
                        //删除注册记录
                        $deleteRegInfo = $this->deleteRegInfo($RegInfo['RegId']);
                        //插入注册日志
                        $insertRegLog = $this->insertRegLog($RegInfo);
                        //如果同时成功
                        if($updateUser && $deleteRegInfo && $insertRegLog)
                        {
                            $this->db->commit();
                            return array("UserId"=>$MobileUserInfo['UserId'],"LoginSource"=>$RegInfo['RegPlatform']);
                        }
                        else
                        {
                            $this->db->rollBack();
                            return false;
                        }
                    }
                    else
                    {
                        //验证通过，注册
                        $NewUserInfo = array('ContactMobile'=>$Mobile,'Mobile'=>$Mobile,'ContactMobile'=>$Mobile,'RegTime'=>$RegInfo['RegTime'],'LastLoginTime'=>$RegInfo['RegTime']);

                        $this->db->begin();
                        //创建用户
                        $UserId = $this->insertUser(array_merge($UserInfo,$NewUserInfo));
                        //删除注册记录
                        $deleteRegInfo = $this->deleteRegInfo($RegInfo['RegId']);
                        //插入注册日志
                        unset($RegInfo['RegId']);
                        $insertRegLog = $this->insertRegLog($RegInfo);
                        //如果同时成功
                        if($UserId && $deleteRegInfo && $insertRegLog)
                        {
                            $this->db->commit();
                            $oRedis = new Base_Cache_Redis("xrace");
                            //写入缓存
                            $oRedis -> set($RegInfo['RegPlatform']."_".$RegInfo['RegKey'],$UserId,86400);
                            return array("UserId"=>$UserId,"LoginSource"=>$RegInfo['RegPlatform']);
                        }
                        else
                        {
                            $this->db->rollBack();
                            return false;
                        }
                    }
                }
                else
                {
                    //随机验证码
                    $ValidateCode = sprintf("%06d",rand(1,999999));
                    //更新注册记录，生成新的验证码和过期时间
                    $bind = array('ExceedTime'=>date("Y-m-d H:i:s",time()+3600),'ValidateCode' => $ValidateCode);
                    $Update = $this->updateRegInfo($RegInfo['RegId'],$bind);
                    //更新成功
                    if($Update)
                    {
                        //短信内容数组
                        $params = array(
                            "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                            "Mobile"=> $RegInfo['Mobile'],
                            "SMSCode"=>"SMS_Validate_Code"
                        );
                        //短信发送
                        Base_common::dayuSMS($params);
                        return -1;
                    }
                    else
                    {
                        return 0;
                    }
                }
            }
            else
            {
                //随机验证码
                $ValidateCode = sprintf("%06d",rand(1,999999));
                //更新注册记录，生成新的验证码和过期时间
                $bind = array('ExceedTime'=>date("Y-m-d H:i:s",time()+3600),'ValidateCode' => $ValidateCode);
                $Update = $this->updateRegInfo($RegInfo['RegId'],$bind);
                //更新成功
                if($Update)
                {
                    //短信内容数组
                    $params = array(
                        "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                        "Mobile"=> $RegInfo['Mobile'],
                        "SMSCode"=>"SMS_Validate_Code"
                    );
                    //短信发送
                    Base_common::dayuSMS($params);
                    return -1;
                }
                else
                {
                    return 0;
                }
            }
        }
        else
        {
            return 0;
        }
    }
    //获取报名记录
    public function getRaceUserList($params,$fields = array('*'))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        //获得分站ID
        $whereStage = (isset($params['RaceStageId']) && intval($params['RaceStageId']))?" RaceStageId = '".$params['RaceStageId']."' ":"";
        //获得比赛ID
        $whereRace = isset($params['RaceId']) && intval($params['RaceId'])?" RaceId = '".$params['RaceId']."' ":"";
        //排除比赛ID
        $whereRaceIgnore = isset($params['RaceIdIgnore'])?" RaceId != '".$params['RaceIdIgnore']."' ":"";
        //获得用户ID
        $whereRaceUser = (isset($params['RaceUserId']) && $params['RaceUserId']!="0")?" RaceUserId = '".$params['RaceUserId']."' ":"";
        //获得组别ID
        $whereGroup = (isset($params['RaceGroupId']) && intval($params['RaceGroupId'])  && $params['RaceGroupId']!=0)?" RaceGroupId = '".$params['RaceGroupId']."' ":"";
        //获得赛事ID
        $whereCatalog = (isset($params['RaceCatalogId']) && intval($params['RaceCatalogId']))?" RaceCatalogId = '".$params['RaceCatalogId']."' ":"";
        //获得赛事ID
        $whereCheckIn = (isset($params['CheckInStatus']) && $params['CheckInStatus']>0) ?" CheckInStatus = '".$params['CheckInStatus']."' ":"";
        //根据选手报名状态
        $whereStatus = (isset($params['RaceStatus']) && $params['RaceStatus']!="all") ?" RaceStatus = '".$params['RaceStatus']."' ":"";
        //根据芯片归还状态
        $whereChipReturned = (isset($params['ChipReturned']) && $params['ChipReturned']!="all") ?" RaceStatus = '".$params['ChipReturned']."' ":"";
        //是否已经发放芯片
        $whereChip = $params['Chip']==1 ?" ChipId != '' ":"";
        //芯片ID
        $whereChipId = isset($params['ChipId']) ?" ChipId = '".$params['ChipId']."' ":"";
        //BIB
        $whereBIB = isset($params['BIB']) ?" BIB = '".$params['BIB']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereCatalog,$whereRaceUser,$whereGroup,$whereRace,$whereStage,$whereCheckIn,$whereRaceIgnore,$whereStatus,$whereChipReturned,$whereChip,$whereChipId,$whereBIB);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //分页参数
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by BIB,ChipId,RaceGroupId,TeamId,ApplyId ".$limit;
        $return = $this->db->getAll($sql);
        return $return;
    }
    //获取报名记录
    public function getRaceUserCount($params)
    {
        //生成查询列
        $fields = Base_common::getSqlFields(array("RaceUserList"=>"count(1)"));
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        //获得分站ID
        $whereStage = (isset($params['RaceStageId']) && intval($params['RaceStageId']))?" RaceStageId = '".$params['RaceStageId']."' ":"";
        //获得比赛ID
        $whereRace = isset($params['RaceId']) && intval($params['RaceId'])?" RaceId = '".$params['RaceId']."' ":"";
        //排除比赛ID
        $whereRaceIgnore = isset($params['RaceIdIgnore'])?" RaceId != '".$params['RaceIdIgnore']."' ":"";
        //获得用户ID
        $whereRaceUser = (isset($params['RaceUserId']) && $params['RaceUserId']!="0")?" RaceUserId = '".$params['RaceUserId']."' ":"";
        //获得组别ID
        $whereGroup = (isset($params['RaceGroupId']) && intval($params['RaceGroupId'])  && $params['RaceGroupId']!=0)?" RaceGroupId = '".$params['RaceGroupId']."' ":"";
        //获得赛事ID
        $whereCatalog = (isset($params['RaceCatalogId']) && intval($params['RaceCatalogId']))?" RaceCatalogId = '".$params['RaceCatalogId']."' ":"";
        //获得赛事ID
        $whereCheckIn = (isset($params['CheckInStatus']) && $params['CheckInStatus']>0) ?" CheckInStatus = '".$params['CheckInStatus']."' ":"";
        //根据选手报名状态
        $whereStatus = (isset($params['RaceStatus']) && $params['RaceStatus']!="all") ?" RaceStatus = '".$params['RaceStatus']."' ":"";
        //根据芯片归还状态
        $whereChipReturned = (isset($params['ChipReturned']) && $params['ChipReturned']!="all") ?" RaceStatus = '".$params['ChipReturned']."' ":"";
        //是否已经发放芯片
        $whereChip = $params['Chip']==1 ?" ChipId != '' ":"";
        //芯片ID
        $whereChipId = isset($params['ChipId']) ?" ChipId = '".$params['ChipId']."' ":"";
        //BIB
        $whereBIB = isset($params['BIB']) ?" BIB = '".$params['BIB']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereCatalog,$whereRaceUser,$whereGroup,$whereRace,$whereStage,$whereCheckIn,$whereRaceIgnore,$whereStatus,$whereChipReturned,$whereChip,$whereChipId,$whereBIB);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //分页参数
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by ChipId,BIB,RaceGroupId,TeamId,ApplyId ".$limit;
        $return = $this->db->getOne($sql);
        return $return;
    }
    /**
     * 获取用户数量
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return integer
     */
    public function getUserCheckInCount($params)
    {
        //生成查询列
        $fields = Base_common::getSqlFields(array("UserCount"=>"count(distinct(RaceUserId))"));
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        //获得比赛ID
        $whereStage = isset($params['RaceStageId'])?" RaceStageId = '".$params['RaceStageId']."' ":"";
        //获得比赛ID
        $whereRace = isset($params['RaceId']) && intval($params['RaceId'])?" RaceId = '".$params['RaceId']."' ":"";
        //排除比赛ID
        $whereRaceIgnore = isset($params['RaceIdIgnore'])?" RaceId != '".$params['RaceIdIgnore']."' ":"";
        //获得用户ID
        $whereRaceUser = (isset($params['RaceUserId']) && $params['RaceUserId']!="0")?" RaceUserId = '".$params['RaceUserId']."' ":"";
        //获得组别ID
        $whereGroup = (isset($params['RaceGroupId']) && intval($params['RaceGroupId'])  && $params['RaceGroupId']!=0)?" RaceGroupId = '".$params['RaceGroupId']."' ":"";
        //获得赛事ID
        $whereCatalog = isset($params['RaceCatalogId'])?" RaceCatalogId = '".$params['RaceCatalogId']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereCatalog,$whereRaceUser,$whereGroup,$whereRace,$whereStage,$whereRaceIgnore);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    //获取报名各状态的名单
    public function getRaceUserCheckInStatusCountList($params,$fields = array('*'))
    {
        $fields = array("CheckInStatus","UserCount"=>"count(distinct(RaceUserId))");
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_stage_checkin);
        //获得比赛ID
        $whereStage = isset($params['RaceStageId'])?" RaceStageId = ".$params['RaceStageId']."":"";
        //获得比赛ID
        $whereRace = isset($params['RaceId'])?" RaceId = '".$params['RaceId']."' ":"";
        //排除比赛ID
        $whereRaceIgnore = isset($params['RaceIdIgnore'])?" RaceId != '".$params['RaceIdIgnore']."' ":"";
        //获得用户ID
        $whereRaceUser = (isset($params['RaceUserId']) && $params['RaceUserId']!="0")?" RaceUserId = '".$params['RaceUserId']."' ":"";
        //获得组别ID
        $whereGroup = (isset($params['RaceGroupId'])  && $params['RaceGroupId']!=0)?" RaceGroupId = '".$params['RaceGroupId']."' ":"";
        //获得赛事ID
        $whereCatalog = isset($params['RaceCatalogId'])?" RaceCatalogId = '".$params['RaceCatalogId']."' ":"";
        //获得赛事ID
        //$whereCheckIn = $params['CheckInStatus']>0 ?" CheckInStatus = '".$params['CheckInStatus']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereCatalog,$whereRaceUser,$whereGroup,$whereRace,$whereStage,$whereRaceIgnore);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." group by CheckInStatus desc";
        $return = $this->db->getAll($sql);
        $UserCheckInStatusList = $this->getStageUserCheckInStatus();
        foreach($UserCheckInStatusList as $Status => $StatusName)
        {
            $ReturnArr[$Status] = array("StatusName"=>$StatusName,"UserCount"=>0);
        }
        foreach($return as $RaceStatus => $UserCount)
        {
            $ReturnArr[$UserCount['CheckInStatus']]["UserCount"] += $UserCount['UserCount'];
            $ReturnArr[0]["UserCount"] += $UserCount['UserCount'];
        }
        return $ReturnArr;
    }
    //获取报名各状态的名单
    public function getRaceUserStatusCountList($params,$fields = array('*'))
    {
        $fields = array("RaceStatus","UserCount"=>"count(1)");
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        //获得比赛ID
        $whereStage = isset($params['RaceStageId'])?" RaceStageId = '".$params['RaceStageId']."' ":"";
        //获得比赛ID
        $whereRace = isset($params['RaceId'])?" RaceId = '".$params['RaceId']."' ":"";
        //排除比赛ID
        $whereRaceIgnore = isset($params['RaceIdIgnore'])?" RaceId != '".$params['RaceIdIgnore']."' ":"";
        //获得用户ID
        $whereRaceUser = (isset($params['RaceUserId']) && $params['RaceUserId']!="0")?" RaceUserId = '".$params['RaceUserId']."' ":"";
        //获得组别ID
        $whereGroup = (isset($params['RaceGroupId'])  && $params['RaceGroupId']!=0)?" RaceGroupId = '".$params['RaceGroupId']."' ":"";
        //获得赛事ID
        $whereCatalog = isset($params['RaceCatalogId'])?" RaceCatalogId = '".$params['RaceCatalogId']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereCatalog,$whereRaceUser,$whereGroup,$whereRace,$whereStage,$whereRaceIgnore);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." group by RaceStatus desc";
        $return = $this->db->getAll($sql);
        $UserApplyStatusList = $this->getUserApplyStatusList();
        foreach($UserApplyStatusList as $Status => $StatusName)
        {
            $ReturnArr[$Status] = array("StatusName"=>$StatusName,"UserCount"=>0);
        }
        foreach($return as $RaceStatus => $UserCount)
        {
            $ReturnArr[$UserCount['RaceStatus']]["UserCount"] += $UserCount['UserCount'];
            $ReturnArr["all"]["UserCount"] += $UserCount['UserCount'];
        }
        return $ReturnArr;
    }
    //获取某场比赛的报名名单
    public function getRaceUserListByRace($params)
    {
        $oRedis = new Base_Cache_Redis("xrace");
        //如果需要获取缓存
        if($params['Cache'] == 1)
        {
            //获取缓存
            $m = $oRedis->get("RaceUserList_".$params['RaceId']);
            //缓存解开
            $RaceUserList = json_decode($m,true);
            //如果数据为空
            if(count($RaceUserList['RaceUserList'])==0)
            {
                //需要从数据库获取
                $NeedDB = 1;
            }
            else
            {
                //echo "cached";
            }
        }
        else
        {
            //需要从数据库获取
            $NeedDB = 1;
        }
        if(isset($NeedDB))
        {
            //获取选手名单
            $UserList = $this->getRaceUserList($params);
            //初始化空的返回值列表
            $TeamList = array('RaceUserList'=>array(),'TeamList'=>array());
            //如果获取到选手名单
            if(count($UserList))
            {
                $oTeam = new Xrace_Team();
                $oRace = new Xrace_Race();
                $RaceApplySourceList = $this->getRaceApplySourceList();
                //初始化空的分组列表
                $RaceGroupList = array();
                foreach($UserList as $ApplyId => $ApplyInfo)
                {
                    //获取用户信息
                    $RaceUserInfo = $this->getRaceUser( $ApplyInfo["RaceUserId"],'RaceUserId,Name');
                    //如果获取到用户
                    if($RaceUserInfo['RaceUserId'])
                    {
                        //存储报名数据
                        $RaceUserList['RaceUserList'][$ApplyId] = $ApplyInfo;
                        //如果列表中没有分组信息
                        if(!isset($RaceGroupList[$ApplyInfo['RaceGroupId']]))
                        {
                            //获取分组信息
                            $RaceGroupInfo = $oRace->getRaceGroup($ApplyInfo['RaceGroupId'],"RaceGroupId,RaceGroupName");
                            //如果合法则保存
                            if(isset($RaceGroupInfo['RaceGroupId']))
                            {
                                $RaceGroupList[$ApplyInfo['RaceGroupId']] = $RaceGroupInfo;
                            }
                        }
                        //保存分组信息
                        $RaceUserList['RaceUserList'][$ApplyId]['RaceGroupName'] = $RaceGroupList[$ApplyInfo['RaceGroupId']]['RaceGroupName'];
                        //获取用户名
                        $RaceUserList['RaceUserList'][$ApplyId]['Name'] = $RaceUserInfo['Name'];
                        if(!isset($RaceUserList['TeamList'][$ApplyInfo['TeamId']]))
                        {
                            //队伍信息
                            $TeamInfo = $oTeam->getTeamInfo($ApplyInfo['TeamId'],'TeamId,TeamName');
                            //如果在队伍列表中有获取到队伍信息
                            if(isset($TeamInfo['TeamId']))
                            {
                                $RaceUserList['TeamList'][$ApplyInfo['TeamId']] = $TeamInfo;
                            }
                        }
                        //格式化用户的队伍名称和队伍ID
                        $RaceUserList['RaceUserList'][$ApplyId]['TeamName'] = isset($RaceUserList['TeamList'][$ApplyInfo['TeamId']])?$RaceUserList['TeamList'][$ApplyInfo['TeamId']]['TeamName']:"个人";
                        $RaceUserList['RaceUserList'][$ApplyId]['TeamId'] = isset($RaceUserList['TeamList'][$ApplyInfo['TeamId']])?$ApplyInfo['TeamId']:0;
                        $RaceUserList['RaceUserList'][$ApplyId]['comment'] = json_decode($ApplyInfo['comment'],true);
                        $RaceUserList['RaceUserList'][$ApplyId]['ApplySourceName'] = $RaceApplySourceList[$ApplyInfo['ApplySource']];
                    }
                }
                //如果有获取到最新版本信息
                if(count($RaceUserList['RaceUserList']))
                {
                    //写入缓存
                    $oRedis -> set("RaceUserList_".$params['RaceId'],json_encode($RaceUserList),86400);
                }
            }
        }
        //如果需要筛选的队伍ID在队伍列表中
        if(isset($RaceUserList['TeamList'][$params['TeamId']]))
        {
            //循环名单
            foreach($RaceUserList['RaceUserList'] as $ApplyId => $ApplyInfo)
            {

                //如果不是想要的队伍
                if($ApplyInfo['TeamId'] != $params['TeamId'])
                {
                    //删除数据
                    unset($RaceUserList['RaceUserList'][$ApplyId]);
                }
            }
        }
        //如果只要个人报名选手
        elseif($params['TeamId'] == -1)
        {
            //循环名单
            foreach($RaceUserList['RaceUserList'] as $ApplyId => $ApplyInfo)
            {
                //如果不是想要的队伍
                if($ApplyInfo['TeamId'] != 0)
                {
                    //删除数据
                    unset($RaceUserList['RaceUserList'][$ApplyId]);
                }
            }
        }
        $RaceUserList['RaceStatus'] = $this->getRaceUserStatusCountList($params);
        $RaceUserList['LogCount'] = $this->getRaceUserCount($params);
        return $RaceUserList;
    }
    public function makeToken($UserId,$IP,$LoginSource)
    {
        //获取用户信息
        $UserInfo = $this->getUserInfo($UserId,"UserId,UserName");
        //如果获取到
        if(isset($UserInfo['UserId']))
        {
            //根据用户获取Token
            $TokenInfo = $this->getTokenByUser($UserId);
            //如果获取到 且 未超时 且 登录方式相同
            if(isset($TokenInfo['Token']) && ((strtotime($TokenInfo['Time'])+3000)>=time()) && ($TokenInfo['LoginSource']==$LoginSource))
            {
                return $TokenInfo['Token'];
            }
            else
            {
                $Token = $UserInfo['UserId']."|".$IP;
                //初始成功状态为否
                $Success = 0;
                //初始计数0
                $i=0;
                do
                {
                    $Time = date("Y-m-d H:i:s",time());
                    $Token = md5($Token."|".rand(1,999));
                    $bind = array("UserId"=>$UserId,"IP"=>Base_Common::ip2long($IP),"Token"=>$Token,"Time"=>$Time,"LoginSource"=>$LoginSource);
                    $this->db->begin();
                    //写入记录
                    $insertToken = $this->insertToken($bind,1);
                    //更新用户最后登录时间
                    $bind = array("LastLoginTime"=>$Time,"LastLoginSource"=>$LoginSource);
                    $updateUser = $this->updateUser($UserId,$bind);
                    //同时更新成功
                    if($insertToken && $updateUser)
                    {
                        $this->db->commit();
                        $Success = 1;
                    }
                    else
                    {
                        $this->db->rollck();
                    }
                    //累加计数器
                    $i++;
                }
                //当不成功且重试次数小于等于3的时候
                while(($Success==0) && ($i<3));
                if(strlen($Token)==32)
                {
                    return $Token;
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            return false;
        }
    }
    /**
     * 获取单个Token记录
     * @param char $Token 登录Token
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getToken($Token, $fields = '*')
    {
        $Token = trim($Token);
        $table_to_process = Base_Widget::getDbTable($this->table_login);
        return $this->db->selectRow($table_to_process, $fields, '`Token` = ?', $Token);
    }
    /**
     * 更新单个用户记录
     * @param char $UserId 用户ID
     * @param array $bind 更新的数据列表
     * @return boolean
     */
    public function updateToken($Token, array $bind)
    {
        $Token = trim($Token);
        $table_to_process = Base_Widget::getDbTable($this->table_login);
        return $this->db->update($table_to_process, $bind, '`Token` = ?', $Token);
    }
    /**
     * 根据用户获取单个Token记录
     * @param char $UserId 用户
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getTokenByUser($UserId, $fields = '*')
    {
        $UserId = intval($UserId);
        $table_to_process = Base_Widget::getDbTable($this->table_login);
        return $this->db->selectRow($table_to_process, $fields, '`UserId` = ?', $UserId);
    }
    /**
     * 新增单个Token记录
     * @param array $bind 所要添加的数据列
     * @param boolean $false 是否强制更新
     * @return boolean
     */
    public function insertToken($bind,$false=0)
    {
        $Time = time();
        //拼接时间
        $bind['Time'] = date("Y-m-d H:i:s",$Time);
        $table_to_process = Base_Widget::getDbTable($this->table_login);
        if($false==0)
        {
             return ($this->db->insert($table_to_process, $bind))?$bind['Token']:false;
        }
        else//强制更新
        {
            return ($this->db->replace($table_to_process, $bind))?$bind['Token']:false;
        }
    }
    /**
     * 根据已有记录创建比赛用户记录
     * @param intval $UserId 用户
     * @return boolean
     */
    public function createRaceUserByUserInfo($UserId)
    {
        $UserId = abs(intval($UserId));
        //获取用户原始数据
        $UserInfo = $this->getUser($UserId);
        //如果获取到
        if(isset($UserInfo['UserId']))
        {
            $RaceUserInfo = $this->getRaceUserByColumn("IdNo",$UserInfo['IdNo']);
            //如果已经被占用
            if(isset($RaceUserInfo['RaceUserId']))
            {
                $NewUserInfo = array("RaceUserId"=>$RaceUserInfo['RaceUserId']);
                $update = $this->updateUser($UserId,$NewUserInfo);
                if($update)
                {
                    //返回用户信息
                    return $RaceUserInfo['RaceUserId'];
                }
            }
            else
            {
                //事务开始
                $this->db->begin();
                //生成用户信息
                $RaceUserInfo = array('Birthday'=>$UserInfo['Birthday'],'CreateUserId'=>$UserInfo['UserId'],'Name'=>$UserInfo['Name'],'Sex'=>$UserInfo['Sex'],'ContactMobile'=>$UserInfo['ContactMobile'],'IdNo'=>$UserInfo['IdNo'],'IdType'=>$UserInfo['IdType'],'Available'=>1,'RegTime'=>date("Y-m-d H:i:s",time()));
                //创建用户
                $CreateRaceUser = $this->insertRaceUser($RaceUserInfo);
                //将比赛用户ID更新回用户
                $UpdateUser = $this->updateUser($UserInfo['UserId'],array("RaceUserId"=>$CreateRaceUser));
                //如果创建比赛用户成功 且 更新用户成功
                if($CreateRaceUser && $UpdateUser)
                {
                    //提交
                    $this->db->commit();
                    //更新缓存
                    $this->getUserInfo($UserId,"*",0);
                    //返回创建的用户ID
                    return $CreateRaceUser;
                }
                else
                {
                    //回滚
                    $this->db->rollBack();
                    return false;
                }
            }
        }
        else
        {
            return false;
        }
    }
    public function resetRegSmsByMobile($Mobile)
    {
        //根据手机号码获取注册记录
        $RegInfo = $this->getRegInfoByMobile($Mobile);
        //如果找到记录
        if(isset($RegInfo['RegId']))
        {
            $ValidateCode = sprintf("%06d",rand(1,999999));
            //更新注册记录，生成新的验证码和过期时间
            $bind = array('ExceedTime'=>date("Y-m-d H:i:s",time()+3600),'ValidateCode' => $ValidateCode);
            $Update = $this->updateRegInfo($RegInfo['RegId'],$bind);
            //更新成功
            if($Update)
            {
                $params = array(
                    "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                    "Mobile"=> $RegInfo['Mobile'],
                    "SMSCode"=>"SMS_Validate_Code"
                );
                Base_common::dayuSMS($params);
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    public function resetRegSmsByReg($RegId)
    {
        //根据RegId获取注册记录
        $RegInfo = $this->getRegInfo($RegId);
        //如果找到记录
        if(isset($RegInfo['RegId']))
        {
            $ValidateCode = sprintf("%06d",rand(1,999999));
            //更新注册记录，生成新的验证码和过期时间
            $bind = array('ExceedTime'=>date("Y-m-d H:i:s",time()+3600),'ValidateCode' => $ValidateCode);
            $Update = $this->updateRegInfo($RegInfo['RegId'],$bind);
            //更新成功
            if($Update)
            {
                $params = array(
                    "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                    "Mobile"=> $RegInfo['Mobile'],
                    "SMSCode"=>"SMS_Validate_Code"
                );
                Base_common::dayuSMS($params);
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    public function checkMobileExist($Mobile)
    {
        $UserInfo = $this->getUserByColumn("Mobile",$Mobile);
        if(isset($UserInfo['UserId']))
        {
            if($UserInfo['Password'] == "")
            {
                return array("UserInfo"=>$UserInfo,"Available"=>1);
            }
            else
            {
                return array("Available"=>0);
            }
        }
        else
        {
            return array("Available"=>1);
        }
    }
    /**
     * 通过手机发起密码重置
     * @param string $Mobile 用户手机号码
     * @return array
     */
    public function MobileResetPassword($Mobile)
    {
        $oRedis = new Base_Cache_Redis("xrace");
        //获取缓存
        $m = $oRedis->get("Mobile_".$Mobile);
        //如果获取到的数据为0
        if(intval($m)==0)
        {
            //根据手机号码查询用户
            $UserInfo = $this->getUserByColumn("Mobile",$Mobile);
            //如果查询到
            if(!isset($UserInfo['UserId']))
            {
                //用户不存在
                return -1;
            }
            else
            {
                //当前时间
                $Time = time();
                //如果密码不为空
                if($UserInfo['Password']!="")
                {
                    //根据手机号码获取重置记录
                    $ResetInfo = $this->getResetInfoByMobile($Mobile);

                        //如果找到
                        if(isset($ResetInfo['ResetId']))
                        {
                            //生成验证码
                            $ValidateCode = sprintf("%06d",rand(1,999999));
                            //更新重置记录
                            $UserResetInfo = array("ResetTime"=>date("Y-m-d H:i:s",$Time),"ValidateCode"=>$ValidateCode,'ExceedTime'=>date("Y-m-d H:i:s",$Time+3600));
                            $Reset = $this->updateResetInfo($ResetInfo['ResetId'],$UserResetInfo);
                            //更新成功
                            if($Reset)
                            {
                                $params = array(
                                    "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                                    "Mobile"=> $ResetInfo['Mobile'],
                                    "SMSCode"=>"SMS_Reset_Password"
                                );
                                Base_common::dayuSMS($params);
                                return $ResetInfo['ResetId'];
                            }
                            else
                            {
                                return 0;
                            }

                        }
                        else
                        {
                            //生成验证码
                            $ValidateCode = sprintf("%06d",rand(1,999999));
                            //创建重置记录
                            $UserResetInfo = array("ResetSource"=>"Mobile","Mobile"=>$Mobile,"ResetTime"=>date("Y-m-d H:i:s",$Time),"ValidateCode"=>$ValidateCode,'ExceedTime'=>date("Y-m-d H:i:s",$Time+3600));
                            $ResetId = $this->insertResetInfo($UserResetInfo);
                            //更新成功
                            if($ResetId)
                            {
                                $params = array(
                                    "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                                    "Mobile"=> $UserResetInfo['Mobile'],
                                    "SMSCode"=>"SMS_Reset_Password"
                                );
                                Base_common::dayuSMS($params);
                                return $ResetId;
                            }
                            else
                            {
                                return 0;
                            }
                        }

                }
                else
                {
                    //空密码不修改
                    return -2;
                }
            }
        }
        else
        {
            //根据手机号码查询用户
            $UserInfo = $this->getUser($Mobile);
            //如果查询到
            if(!isset($UserInfo['UserId']))
            {
                //用户不存在
                return -1;
            }
            else
            {
                //当前时间
                $Time = time();
                //如果密码不为空
                if($UserInfo['Password']!="")
                {
                    //根据手机号码获取重置记录
                    $ResetInfo = $this->getResetInfoByMobile($Mobile);
                    //如果找到
                    if(isset($ResetInfo['ResetId']))
                    {
                        //生成验证码
                        $ValidateCode = sprintf("%06d",rand(1,999999));
                        //更新重置记录
                        $UserResetInfo = array("ResetTime"=>date("Y-m-d H:i:s",$Time),"ValidateCode"=>$ValidateCode,'ExceedTime'=>date("Y-m-d H:i:s",$Time+3600));
                        $Reset = $this->updateResetInfo($ResetInfo['ResetId'],$UserResetInfo);
                        //更新成功
                        if($Reset)
                        {
                            $params = array(
                                "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                                "Mobile"=> $ResetInfo['Mobile'],
                                "SMSCode"=>"SMS_Reset_Password"
                            );
                            Base_common::dayuSMS($params);
                            return true;
                        }
                        else
                        {
                            return 0;
                        }
                    }
                    else
                    {
                        //生成验证码
                        $ValidateCode = sprintf("%06d",rand(1,999999));
                        //创建重置记录
                        $UserResetInfo = array("ResetSource"=>"Mobile","Mobile"=>$Mobile,"ResetTime"=>date("Y-m-d H:i:s",$Time),"ValidateCode"=>$ValidateCode,'ExceedTime'=>date("Y-m-d H:i:s",$Time+3600));
                        $ResetId = $this->insertResetInfo($UserResetInfo);
                        //更新成功
                        if($ResetId)
                        {
                            $params = array(
                                "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                                "Mobile"=> $UserResetInfo['Mobile'],
                                "SMSCode"=>"SMS_Reset_Password"
                            );
                            Base_common::dayuSMS($params);
                            return true;
                        }
                        else
                        {
                            return 0;
                        }
                    }

                }
                else
                {
                    //空密码不修改
                    return -2;
                }
            }
        }
    }
    /**
     * 重置密码时的短信验证码校验
     * @param string $Mobile 用户手机号码
     * @param string $ValidateCode 短信验证码
     * @return array
     */
    public function resetPasswordMobileAuth($Mobile,$ValidateCode)
    {
        //获取重置密码记录
        $ResetInfo = $this->getResetInfoByMobile($Mobile);
        //如果找到记录
        if(isset($ResetInfo['ResetId']))
        {
            //获取当前时间
            $Time = time();
            //如果在有效期内
            if(strtotime($ResetInfo['ExceedTime'])>=time())
            {
                if($ResetInfo['ValidateCode']==$ValidateCode)
                {

                    //更新记录，写入更新的过期时间
                    $NewResetInfo['UpdateExceedTime'] = date("Y-m-d H:i:s",$Time+1800);
                    $update = $this->updateResetInfo($ResetInfo['ResetId'],$NewResetInfo);
                    //如果更新成功
                    if($update)
                    {
                        return 1;
                    }
                    else
                    {
                        return 0;
                    }

                }
                else
                {
                    //生成验证码
                    $ValidateCode = sprintf("%06d",rand(1,999999));
                    //更新重置记录
                    $UserResetInfo = array("ResetTime"=>date("Y-m-d H:i:s",$Time),"ValidateCode"=>$ValidateCode,'ExceedTime'=>date("Y-m-d H:i:s",$Time+3600),'UpdateExceedTime'=>date("Y-m-d H:i:s",0));
                    $Reset = $this->updateResetInfo($ResetInfo['ResetId'],$UserResetInfo);
                    //更新成功
                    if($Reset)
                    {
                        $params = array(
                            "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                            "Mobile"=> $ResetInfo['Mobile'],
                            "SMSCode"=>"SMS_Reset_Password"
                        );
                        Base_common::dayuSMS($params);
                        return -1;
                    }
                    else
                    {
                        return 0;
                    }
                }
            }
            else
            {
                //生成验证码
                $ValidateCode = sprintf("%06d",rand(1,999999));
                //更新重置记录
                $UserResetInfo = array("ResetTime"=>date("Y-m-d H:i:s",$Time),"ValidateCode"=>$ValidateCode,'ExceedTime'=>date("Y-m-d H:i:s",$Time+3600));
                $Reset = $this->updateResetInfo($ResetInfo['ResetId'],$UserResetInfo);
                //更新成功
                if($Reset)
                {
                    $params = array(
                        "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                        "Mobile"=> $ResetInfo['Mobile'],
                        "SMSCode"=>"SMS_Reset_Password"
                    );
                    Base_common::dayuSMS($params);
                    return -2;
                }
                else
                {
                    return 0;
                }
            }
        }
        else
        {
            return 0;
        }
    }
    /**
     * 重置密码
     * @param string $ResetId 重置密码记录Id
     * @param string $Password 密码
     * @return array
     */
    public function resetPassword($ResetId,$Password,$IP)
    {
        //获取重置密码记录
        $ResetInfo = $this->getResetInfo($ResetId);
        //如果找到记录
        if(isset($ResetInfo['ResetId']))
        {
            //获取当前时间
            $Time = time();
            //如果在有效期内
            if(strtotime($ResetInfo['UpdateExceedTime'])>=time())
            {

                //根据手机号码查询用户
                $MobileUserInfo = $this->getUserByColumn("Mobile",$ResetInfo['Mobile']);
                //如果有用户已经在使用 并且 有密码
                if(isset($MobileUserInfo['UserId']) && strlen($MobileUserInfo['Password'])==32)
                {
                    $this->db->begin();
                    //创建更新日志，写入更新时间，IP，密码
                    unset($ResetInfo['ValidateCode'],$ResetInfo['ExceedTime'],$ResetInfo['UpdateExceedTime']);
                    $ResetLog = array_merge($ResetInfo,array("UpdateTime"=>date("Y-m-d H:i:s",$Time),"IP"=>Base_Common::ip2long($IP),"Password"=>md5($Password)));
                    unset($ResetLog['ResetId']);
                    $insertResetLog = $this->insertResetLog($ResetLog);
                    //更新用户密码
                    $UserInfo = array("Password"=>md5($Password));
                    $updateUser = $this->updateUser($MobileUserInfo['UserId'],$UserInfo);
                    //删除更新记录
                    $deleteResetInfo = $this->deleteResetInfo($ResetInfo['ResetId']);
                    //如果同时成功
                    if($insertResetLog && $updateUser && $deleteResetInfo)
                    {
                        $this->db->commit();
                        //重建缓存
                        $this->getUserInfo($MobileUserInfo['UserId'],"*",0);
                        return 1;
                    }
                    else
                    {
                        $this->db->rollback;
                        return 0;
                    }
                }
            }
            else
            {
                //生成验证码
                $ValidateCode = sprintf("%06d",rand(1,999999));
                //更新重置记录
                $UserResetInfo = array("ResetTime"=>date("Y-m-d H:i:s",$Time),"ValidateCode"=>$ValidateCode,'ExceedTime'=>date("Y-m-d H:i:s",$Time+1800),'UpdateExceedTime'=>date("Y-m-d H:i:s",$Time+1800));
                $Reset = $this->updateResetInfo($ResetInfo['ResetId'],$UserResetInfo);
                //更新成功
                if($Reset)
                {
                    $params = array(
                        "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                        "Mobile"=> $ResetInfo['Mobile'],
                        "SMSCode"=>"SMS_Reset_Password"
                    );
                    Base_common::dayuSMS($params);
                    return -1;
                }
                else
                {
                    return 0;
                }
            }
        }
        else
        {
            return 0;
        }
    }
    /**
     * 更新密码
     * @param string $UserId 用户ID
     * @param string $Password 密码
     * @return array
     */
    public function userResetPassword($UserId,$OldPassword,$Password,$IP)
    {
        //获取当前时间
        $Time = time();
        //根据手机号码查询用户
        $UserInfo = $this->getUser($UserId,"UserId,Password,Mobile");
        //如果有用户已经在使用 并且 有密码
        if(isset($UserInfo['UserId']) && ($UserInfo['Password']) == md5($OldPassword))
        {
            $this->db->begin();
            //创建更新日志
            $ResetLog = array("ResetSource"=>"User","Mobile"=>$UserInfo['Mobile'],"ResetTime"=>date("Y-m-d H:i:s",$Time),"UpdateTime"=>date("Y-m-d H:i:s",$Time),"IP"=>Base_Common::ip2long($IP),"Password"=>md5($Password));
            //写入更新日志
            $insertResetLog = $this->insertResetLog($ResetLog);
            //更新用户密码
            $NewUserInfo = array("Password"=>md5($Password));
            $updateUser = $this->updateUser($UserInfo['UserId'],$NewUserInfo);
            //如果同时成功
            if($insertResetLog && $updateUser)
            {
                $this->db->commit();
                //重建缓存
                $this->getUserInfo($UserInfo['UserId'],"*",0);
                return 1;
            }
            else
            {
                $this->db->rollback;
                return 0;
            }
        }
    }
    //创建用户报名信息
    public function insertRaceApplyUserInfo(array $bind,array $bind_update)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        return $this->db->insert_update($table_to_process, $bind,$bind_update);
    }
    //获得用户报名信息
    public function getRaceApplyUserInfo($ApplyId, $fields = '*')
    {
        $ApplyId = intval($ApplyId);
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        return $this->db->selectRow($table_to_process, $fields, '`ApplyId` = ?', $ApplyId);
    }
    //更新用户报名信息
    public function updateRaceUserApply($ApplyId, array $bind)
    {
        $ApplyId = intval($ApplyId);
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        return $this->db->update($table_to_process, $bind, '`ApplyId` = ?', $ApplyId);
    }
    //获取报名记录
    public function getRaceUserCheckInList($params,$fields = array('*'))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_stage_checkin);
        //用户
        $whereUser = isset($params['RaceUserId'])?" RaceUserId = ".$params['RaceUserId']:"";
        //获得分站
        $whereRaceStage = isset($params['RaceStageId'])?" RaceStageId = ".$params['RaceStageId']:"";
        //获得赛事
        $whereCatalog = isset($params['RaceCatalogId'])?" RaceCatalogId = ".$params['RaceCatalogId']:"";
        //签到状态
        $whereCheckIn = isset($params['CheckinStatus']) && intval($params['CheckinStatus'])>0?" CheckinStatus = '".$params['CheckinStatus']."'":"";
        //所有查询条件置入数组
        $whereCondition = array($whereUser,$whereCatalog,$whereRaceStage,$whereCheckIn);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by CheckInStatus,RaceCatalogId,RaceStageId,RaceUserId desc";
        $return = $this->db->getAll($sql);
        return $return;
    }
    /**
     * 新增单个用户签到记录
     * @param string $bind 所要更新的数据列
     * @return array
     */
    public function insertUserCheckInInfo($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_stage_checkin);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 获取单个用户签到记录
     * @param char $UserId 用户ID
     * @param char $RaceStageId 分站ID
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getUserCheckInInfo($RaceUserId,$RaceStageId,$fields = '*')
    {
        $RaceUserId = intval($RaceUserId);
        $RaceStageId = intval($RaceStageId);
        $table_to_process = Base_Widget::getDbTable($this->table_stage_checkin);
        return $this->db->selectRow($table_to_process, $fields, '`RaceUserId` = ? and `RaceStageId` = ?', array($RaceUserId,$RaceStageId));
    }
    /**
     * 更新单个用户签到记录
     * @param char $UserId 用户ID
     * @param char $RaceStageId 分站ID
     * @param string $bind 所要更新的数据列
     * @return array
     */
    public function updateUserCheckInInfo($RaceUserId,$RaceStageId,$bind)
    {
        $RaceUserId = intval($RaceUserId);
        $RaceStageId = intval($RaceStageId);
        $table_to_process = Base_Widget::getDbTable($this->table_stage_checkin);
        return $this->db->update($table_to_process, $bind, '`RaceUserId` = ? and `RaceStageId` = ?', array($RaceUserId,$RaceStageId));
    }
    /**
     * 根据BIB获取选手的报名名记录
     * @param char $BIB 选手号码
     * @param char $RaceId 比赛ID
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getRaceApplyUserInfoByBIB($RaceId,$BIB,$fields = '*')
    {
        $RaceId = intval($RaceId);
        $BIB = trim($BIB);
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        return $this->db->selectRow($table_to_process, $fields, '`RaceId` = ? and `BIB` = ?', array($RaceId,$BIB));
    }
    /**
     * 根据BIB获取选手的阿伯名记录
     * @param char $BIB 选手号码
     * @param char $RaceId 比赛ID
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getRaceApplyUserInfoByUser($RaceId,$RaceUserId,$fields = '*')
    {
        $RaceId = intval($RaceId);
        $RaceUserId = intval($RaceUserId);
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        return $this->db->selectRow($table_to_process, $fields, '`RaceId` = ? and `RaceUserId` = ?', array($RaceId,$RaceUserId));
    }

    /**
     * 添加单个用户的验证记录
     * @param intval $UserId 用户ID
     * @param string $Action 动作
     * @return array
     */
    public function getValidateAuthInfo($UserId,$Action)
    {
        $UserId = intval($UserId);
        $Action = trim($Action);
        $table_to_process = Base_Widget::getDbTable($this->table_auth);
        return $this->db->selectRow($table_to_process, "*", '`UserId` = ? and `Action` = ?', array($UserId,$Action));
    }
    /**
     * 更新单个用户的验证记录
     * @param intval $UserId 用户ID
     * @param string $Action 动作
     * @param array $bind 更新的内容
     * @return array
     */
    public function updateValidateAuthInfo($UserId,$Action,$bind)
    {
        $UserId = intval($UserId);
        $Action = trim($Action);
        $table_to_process = Base_Widget::getDbTable($this->table_auth);
        return $this->db->update($table_to_process, $bind, '`UserId` = ? and `Action` = ?', array($UserId,$Action));
    }
    /**
     * 删除单个用户的验证记录
     * @param string $AuthId 验证记录
     * @return array
     */
    public function deleteValidateAuthInfo($AuthId)
    {
        $AuthId = intval($AuthId);
        $table_to_process = Base_Widget::getDbTable($this->table_auth);
        return $this->db->delete($table_to_process,'`AuthId` = ?',$AuthId);
    }
    /**
     * 新增单个用户的验证记录
     * @param intval $UserId 用户ID
     * @param string $Action 动作
     * @return array
     */
    public function insertValidateAuthInfo($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_auth);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 新增单个用户的验证日志
     * @param array $bind 记录内容
     * @return array
     */
    public function insertValidateAuthLog($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_auth_log);
        return $this->db->insert($table_to_process, $bind);
    }

    /**
     * 用户申请发送验证码
     * @param intval $UserId 用户ID
     * @param string $Action 动作
     * @param string $AuthType 验证码发送方式
     * @param string $AuthKey 验证发送方唯一KEY
     * @return array
     */
    public function userValidateAuthApply($UserId,$Action,$AuthType,$AuthKey)
    {
        //获取用户当前是否有未完成的验证
        $AuthInfo = $this->getValidateAuthInfo($UserId,$Action);
        //获取所有需要验证码的动作列表
        $ValidateCodeActionList = $this->getValidateCodeActionList();
        //如果指定动作不在范围内
        if(!isset($ValidateCodeActionList[$Action]))
        {
            //返回错误
            return false;
        }
        //如果找到
        if(isset($AuthInfo['AuthId']))
        {
            //获取当前时间
            $Time = time();
            //生成验证码
            $ValidateCode = sprintf("%06d",rand(1,999999));
            //待更新数据
            $bind = array("AuthType"=>$AuthType,"AuthKey"=>$AuthKey,"ApplyTime"=>date("Y-m-d H:i:s",$Time),"ExceedTime"=>date("Y-m-d H:i:s",$Time+1800),"ValidateCode"=>$ValidateCode);
            //重置状态
            $reset = $this->updateValidateAuthInfo($UserId,$Action,$bind);
            //如果更新成功
            if($reset)
            {
                //如果验证方式为手机
                if($AuthType=="Mobile")
                {
                    $params = array(
                        "smsContent" => array("code"=>$ValidateCode,"action"=>$ValidateCodeActionList[$Action]),
                        "Mobile"=> $AuthKey,
                        "SMSCode"=>"ValidateCode"
                    );
                    Base_common::dayuSMS($params);
                }
                return 1;
            }
            else
            {
                return 0;
            }
        }
        else
        {
            //获取当前时间
            $Time = time();
            //生成验证码
            $ValidateCode = sprintf("%06d",rand(1,999999));
            //待更新数据
            $bind = array("UserId"=>$UserId,"Action"=>$Action,"AuthType"=>$AuthType,"AuthKey"=>$AuthKey,"ApplyTime"=>date("Y-m-d H:i:s",$Time),"ExceedTime"=>date("Y-m-d H:i:s",$Time+1800),"ValidateCode"=>$ValidateCode);
            //重置状态
            $reset = $this->insertValidateAuthInfo($bind);
            //如果更新成功
            if($reset)
            {
                //如果验证方式为手机
                if($AuthType=="Mobile")
                {
                    $params = array(
                        "smsContent" => array("code"=>$ValidateCode,"action"=>$ValidateCodeActionList[$Action]),
                        "Mobile"=> $AuthKey,
                        "SMSCode"=>"ValidateCode"
                    );
                    Base_common::dayuSMS($params);
                }
                return 1;
            }
            else
            {
                return 0;
            }
        }
    }
    /**
     * 用户申请发送验证码
     * @param intval $UserId 用户ID
     * @param string $Action 动作
     * @param string $AuthType 验证码发送方式
     * @param string $AuthKey 验证发送方唯一KEY
     * @return array
     */
    public function userValidateAuth($UserId,$Action,$ValidateCode)
    {
        //获取用户当前是否有未完成的验证
        $AuthInfo = $this->getValidateAuthInfo($UserId,$Action);
        //如果找到
        if(isset($AuthInfo['AuthId']))
        {
            $this->insertValidateAuthLog(array_merge($AuthInfo,array("UserEntered"=>$ValidateCode,"UserEnteredTime"=>date("Y-m-d H:i:s",time()))));
            //如果已经超时
            if(strtotime($AuthInfo['ExceedTime'])<time())
            {
                //获取当前时间
                $Time = time();
                //生成验证码
                $ValidateCode = sprintf("%06d",rand(1,999999));
                //待更新数据
                $bind = array("AuthType"=>$AuthInfo['AuthType'],"AuthKey"=>$AuthInfo['AuthKey'],"ApplyTime"=>date("Y-m-d H:i:s",$Time),"ExceedTime"=>date("Y-m-d H:i:s",$Time+1800),"ValidateCode"=>$ValidateCode);
                //重置状态
                $reset = $this->updateValidateAuthInfo($UserId,$Action,$bind);
                //如果更新成功
                if($reset)
                {
                    //获取所有需要验证码的动作列表
                    $ValidateCodeActionList = $this->getValidateCodeActionList();
                    //如果验证方式为手机
                    if($AuthInfo['AuthType']=="Mobile")
                    {
                        $params = array(
                            "smsContent" => array("code"=>$ValidateCode,"action"=>$ValidateCodeActionList[$Action]),
                            "Mobile"=> $AuthInfo['AuthKey'],
                            "SMSCode"=>"ValidateCode"
                        );
                        Base_common::dayuSMS($params);
                    }
                    return array("return"=>-1,"Mobile"=>$AuthInfo['AuthKey']);
                }
                else
                {
                    return array("return"=>0,"Mobile"=>$AuthInfo['AuthKey']);
                }
            }
            else
            {
                //如果验证码正确
                if($ValidateCode == $AuthInfo['ValidateCode'])
                {
                    $this->deleteValidateAuthInfo($AuthInfo['AuthId']);
                    return array("return"=>1,"Mobile"=>$AuthInfo['AuthKey']);
                }
                else
                {
                    //获取当前时间
                    $Time = time();
                    //生成验证码
                    $ValidateCode = sprintf("%06d",rand(1,999999));
                    //待更新数据
                    $bind = array("AuthType"=>$AuthInfo['AuthType'],"AuthKey"=>$AuthInfo['AuthKey'],"ApplyTime"=>date("Y-m-d H:i:s",$Time),"ExceedTime"=>date("Y-m-d H:i:s",$Time+1800),"ValidateCode"=>$ValidateCode);
                    //重置状态
                    $reset = $this->updateValidateAuthInfo($UserId,$Action,$bind);
                    //如果更新成功
                    if($reset)
                    {
                        //获取所有需要验证码的动作列表
                        $ValidateCodeActionList = $this->getValidateCodeActionList();
                        //如果验证方式为手机
                        if($AuthInfo['AuthType']=="Mobile")
                        {
                            $params = array(
                                "smsContent" => array("code"=>$ValidateCode,"action"=>$ValidateCodeActionList[$Action]),
                                "Mobile"=> $AuthInfo['AuthKey'],
                                "SMSCode"=>"ValidateCode"
                            );
                            Base_common::dayuSMS($params);
                        }
                        return array("return"=>-1,"Mobile"=>$AuthInfo['AuthKey']);
                    }
                    else
                    {
                        return array("return"=>0,"Mobile"=>$AuthInfo['AuthKey']);
                    }
                }
            }
        }
        else
        {
            return array("return"=>0,"Mobile"=>$AuthInfo['AuthKey']);
        }
    }
    public function UserRaceStatusRestore($ApplyId)
    {
        //获取报名记录
        $UserRaceApplyInfo = $this->getRaceApplyUserInfo($ApplyId);
        //如果找到
        if($UserRaceApplyInfo['ApplyId'])
        {
            //如果是正常状态
            if($UserRaceApplyInfo['RaceStatus']==0)
            {
                return true;
            }
            else
            {
                //数据解包
                $comment = json_decode($UserRaceApplyInfo['comment'],true);
                unset($comment['DNF'],$comment['DNS']);
                $bind = array("comment"=>json_encode($comment),"RaceStatus"=>0);
                //更新报名记录
                return $this->updateRaceUserApply($ApplyId,$bind);
            }
        }
        else
        {
            return fasle;
        }
    }
    public function UserRaceDNF($ApplyId,$Reason,$manager_id)
    {
        //获取报名记录
        $UserRaceApplyInfo = $this->getRaceApplyUserInfo($ApplyId);
        //如果找到
        if($UserRaceApplyInfo['ApplyId'])
        {
            //如果已经DNF
            if($UserRaceApplyInfo['RaceStatus']==2)
            {
                return true;
            }
            //如果已经DNF
            elseif($UserRaceApplyInfo['RaceStatus']==1)
            {
                return true;
            }
            else
            {
                //数据解包
                $comment = json_decode($UserRaceApplyInfo['comment'],true);
                //记录时间，操作人员和理由
                $comment['DNF'] = array("Time"=>time(),"Reason"=>$Reason,"manager_id"=>$manager_id);
                $bind = array("comment"=>json_encode($comment),"RaceStatus"=>2);
                //更新报名记录
                return $this->updateRaceUserApply($ApplyId,$bind);
            }
        }
        else
        {
            return fasle;
        }
    }
    public function UserRaceDNS($ApplyId,$Reason,$manager_id)
    {
        //获取报名记录
        $UserRaceApplyInfo = $this->getRaceApplyUserInfo($ApplyId);
        //如果找到
        if($UserRaceApplyInfo['ApplyId'])
        {
            //如果已经DNF
            if($UserRaceApplyInfo['RaceStatus']==2)
            {
                return true;
            }
            //如果已经DNF
            elseif($UserRaceApplyInfo['RaceStatus']==1)
            {
                return true;
            }
            else
            {
                //数据解包
                $comment = json_decode($UserRaceApplyInfo['comment'],true);
                //记录时间，操作人员和理由
                $comment['DNS'] = array("Time"=>time(),"Reason"=>$Reason,"manager_id"=>$manager_id);
                $bind = array("comment"=>json_encode($comment),"RaceStatus"=>1);
                //更新报名记录
                return $this->updateRaceUserApply($ApplyId,$bind);
            }
        }
        else
        {
            return fasle;
        }
    }
    //芯片归还
    public function ChipReturn($ApplyId)
    {
        //获取报名记录
        $ApplyInfo = $this->getRaceApplyUserInfo($ApplyId);
        //如果记录找到
        if(isset($ApplyInfo['ApplyId']))
        {
            //数据解包
            $ApplyInfo['comment'] = json_decode($ApplyInfo['comment'],true);
            //如果已经归还
            if($ApplyInfo['ChipReturned']==1)
            {
                //如果归还时间有效
                if(isset($ApplyInfo['comment']['ChipReturnTime']) && isset($ApplyInfo['comment']['ChipReturnTime'])>0)
                {

                }
                else
                {
                    //保存当前时间
                    $ApplyInfo['comment']['ChipReturnTime'] = time();
                    //数据解包
                    $ApplyInfo['comment'] = json_encode($ApplyInfo['comment']);
                    //更新数据
                    $this->updateRaceUserApply($ApplyId,$ApplyInfo);
                }
                return true;
            }
            else
            {
                //保存归还状态
                $ApplyInfo['ChipReturned'] = 1;
                //保存当前时间
                $ApplyInfo['comment']['ChipReturnTime'] = time();
                //数据解包
                $ApplyInfo['comment'] = json_encode($ApplyInfo['comment']);
                //更新数据
                return $this->updateRaceUserApply($ApplyId,$ApplyInfo);
            }
        }
    }
    //获取单个分站的芯片归还状态
    public function getChipReturnStatus($RaceStageId,$ReturnStatus,$Page,$PageSize)
    {
        $params = array("RaceStageId"=>$RaceStageId);
        //获取总计芯片发放数量
        $TotalChipCount = $this->getChipCount($params,array("Count(distinct(ChipId)) as ChipCount"));
        //获取芯片归还状态汇总
        $ChipReturnStatusCount = $this->getChipReturnStatusCount($params,array("ChipCount"=>"Count(distinct(ChipId))","ChipReturned"));
        //获取状态列表
        $ChipReutrnStatusList = $this->getChipReturnStatusList();
        //初始化空的返回值列表
        $return = array("StatusList"=>array(),"ChipList"=>array());
        foreach($ChipReutrnStatusList as $key => $value)
        {
            $return["StatusList"][$key] = array("StatusName"=>$value,"ChipCount"=>0);
        }
        //依次填入数据
        foreach($ChipReturnStatusCount as $key => $value)
        {
            $return["StatusList"][$value['ChipReturned']]['ChipCount'] = $value['ChipCount'];
        }
        $return["StatusList"]['all']['ChipCount'] = $TotalChipCount;
        //获取报名记录
        $UserRaceApplyList = $this->getChipList(array("RaceStageId"=>$RaceStageId,"ChipReturned"=>$ReturnStatus,"Chip"=>1,"Page"=>$Page,"PageSize"=>$PageSize));
        //循环报名记录
        foreach($UserRaceApplyList as $key => $value)
        {
            $return["ChipList"][$value['ChipReturned']][$value['ChipId']][$value['ApplyId']] = $value;
        }
        ksort($return["ChipList"]);
        return $return;
    }
    //获取报名记录
    public function getChipReturnStatusCount($params,$fields = array('*'))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        //获得比赛ID
        $whereStage = isset($params['RaceStageId'])?" RaceStageId = '".$params['RaceStageId']."' ":"";
        //获得比赛ID
        $whereRace = isset($params['RaceId']) && intval($params['RaceId'])?" RaceId = '".$params['RaceId']."' ":"";
        //是否已经发放芯片
        $whereChip = " ChipId != '' ";
        //所有查询条件置入数组
        $whereCondition = array($whereRace,$whereStage,$whereChip);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //分页参数
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." group by ChipReturned desc";
        $return = $this->db->getAll($sql);
        return $return;
    }
    //获取报名记录
    public function getChipCount($params,$fields = array('*'))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        //获得比赛ID
        $whereStage = isset($params['RaceStageId'])?" RaceStageId = '".$params['RaceStageId']."' ":"";
        //获得比赛ID
        $whereRace = isset($params['RaceId']) && intval($params['RaceId'])?" RaceId = '".$params['RaceId']."' ":"";
        //是否已经发放芯片
        $whereChip = " ChipId != '' ";
        //所有查询条件置入数组
        $whereCondition = array($whereRace,$whereStage,$whereChip);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //分页参数
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        $return = $this->db->getOne($sql);
        return $return;
    }
    //获取报名记录
    public function getChipList($params,$fields = array('*'))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        //获得比赛ID
        $whereStage = isset($params['RaceStageId'])?" RaceStageId = '".$params['RaceStageId']."' ":"";
        //获得比赛ID
        $whereRace = isset($params['RaceId']) && intval($params['RaceId'])?" RaceId = '".$params['RaceId']."' ":"";
        //是否已经发放芯片
        $whereChip = " ChipId != '' ";
        //根据芯片归还状态
        $whereChipReturned = (isset($params['ChipReturned']) && $params['ChipReturned']!="all") ?" ChipReturned = '".$params['ChipReturned']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereRace,$whereStage,$whereChip,$whereChipReturned);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //分页参数
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT distinct(ChipId) FROM $table_to_process where 1 ".$where." order by  ChipReturned,ChipId desc".$limit;
        $return = $this->db->getAll($sql);
        //初始化空的芯片列表
        $ChipList = array();
        foreach($return as $key => $value)
        {
            $ChipList[] = '"'.$value['ChipId'].'"';
        }
        $ChipList = implode(",",$ChipList);
        //所有查询条件置入数组
        $whereCondition = array($whereRace,$whereStage,$whereChipReturned);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields from $table_to_process where ChipId in (".$ChipList.") ".$where;
        $return = $this->db->getAll($sql);
        return $return;
    }

}

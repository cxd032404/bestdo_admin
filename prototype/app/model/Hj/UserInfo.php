<?php
/**
 * 用户数据相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_UserInfo extends Base_Widget
{
	//声明所用到的表
    protected $table = 'user_info';
    protected $table_company_user = 'company_user_list';
    protected $table_reg_info = 'UserReg';
    protected $table_reg_log = 'UserRegLog';
    protected $table_reset = 'UserResetPassword';
    protected $table_reset_log = 'UserResetPasswordLog';
    protected $table_activity_log = 'user_activity_log';


    protected $companyUserAuthType = array("mobile"=>"手机号","worker_id"=>"工号");
    //登录方式列表
    protected $loginSource = array('WeChat'=>"微信",'Weibo'=>"微博",'Mobile'=>"手机",'MiniProgram'=>"小程序",'test'=>"测试用户");
    //性别列表
	protected $sex = array('0'=>"保密",'1'=>"男",'2'=>"女");
    //性别列表
    protected $raceApplySourceList = array('0'=>"未知",'1'=>"线上","2"=>"线下");
    //用户签到状态
    protected $stage_user_checkin_status = array('0'=>'全部','2'=>"未签到",'1'=>"已签到");
    //用户检录状态
    protected $race_user_checkin_status = array('0'=>'全部','1'=>"已检录",'2'=>"未检录");
    //用户签到短信发送状态
    protected $user_checkin_sms_sent_status = array('3'=>"不需发送",'1'=>"待发送",'2'=>"已发送");

    //获取用户验证方式列表
	public function getCompanyUserAuthType()
    {
        return $this->companyUserAuthType;
    }
    //获取性别列表
	public function getsexList()
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
     * @param char $user_id 用户ID
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getUserInfo($user_id, $fields = '*',$Cache=1)
    {
        $oRedis = new Base_Cache_Redis("xrace");
        //获取缓存
        if($Cache == 1)
        {
            //获取缓存
            $m = $oRedis->get("UserInfo_".$user_id);
            //缓存解开
            $UserInfo = json_decode($m,true);
            //如果结果集不有效
            if(!isset($UserInfo['user_id']))
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
            $UserInfo = $this->getUser($user_id, "*");
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
            if(isset($UserInfo['user_id']))
            {
                //写入缓存
                $oRedis -> set("UserInfo_".$user_id,json_encode($UserInfo),86400);
            }
        }
        //如果结果集有效，并且获取的字段列表不是全部
        if(isset($UserInfo['user_id']) && $fields != "*")
        {
            //分解字段列表
            $fieldsList = explode(",",$fields);
            //循环结果集
            foreach($UserInfo as $key => $value)
            {
                //如果不在字段列表中且不是主键+身份证+姓名
                if(!in_array($key,$fieldsList) && !in_array($key, array("user_id","IdNo","UserName","ReadyToRace")))
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
 * @param char $user_id 用户ID
 * @param string $fields 所要获取的数据列
 * @return array
 */
    public function getUser($user_id, $fields = '*')
    {
        $user_id = intval($user_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`user_id` = ?', $user_id);
    }
    /**
     * 获取单个用户记录
     * @param char $wechatid 用户微信openid
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getUserByWechat($wechat_id, $fields = '*')
    {
        $wechat_id = trim($wechat_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`wechatid` = ?', $wechat_id);
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
     * 更新单个用户记录
     * @param char $user_id 用户ID
     * @param array $bind 更新的数据列表
     * @return boolean
     */
    public function updateUser($user_id, array $bind)
    {
        $user_id = intval($user_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->update($table_to_process, $bind, '`user_id` = ?', $user_id);
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
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        $order = " ORDER BY user_id desc";
        //企业
        $whereCompany = (isset($params['company_id']) && ($params['company_id']>0))?" company_id = '".$params['company_id']."' ":"";
        //性别判断
        $wheresex = isset($this->sex[$params['sex']])?" sex = '".$params['sex']."' ":"";
        //姓名
        $whereName = (isset($params['true_name']) && trim($params['true_name']))?" true_name like '%".$params['true_name']."%' ":"";
        //昵称
        $whereNickName = (isset($params['NickName']) && trim($params['nick_name']))?" nick_name like '%".$params['nick_name']."%' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereCompany,$wheresex,$whereName,$whereNickName);
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
                $UserList['UserList'][$value['user_id']] = $value;
            }
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
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        //生成查询列
        $fields = Base_common::getSqlFields(array("UserCount"=>"count(user_id)"));
        //企业
        $whereCompany = (isset($params['company_id']) && ($params['company_id']>0))?" company_id = '".$params['company_id']."' ":"";
        //性别判断
        $wheresex = isset($this->sex[$params['sex']])?" sex = '".$params['sex']."' ":"";
        //姓名
        $whereName = (isset($params['true_name']) && trim($params['true_name']))?" true_name like '%".$params['true_name']."%' ":"";
        //昵称
        $whereNickName = (isset($params['NickName']) && trim($params['nick_name']))?" nick_name like '%".$params['nick_name']."%' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereCompany,$wheresex,$whereName,$whereNickName);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    /**
     * 获取企业导入用户列表
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return array
     */
    public function getCompanyUserList($params,$fields = array("*"))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_company_user);
        $order = " ORDER BY id desc";
        //企业
        $whereCompany = (isset($params['company_id']) && ($params['company_id']>0))?" company_id = '".$params['company_id']."' ":"";
        //姓名
        $whereMobile = (isset($params['mobile']) && trim($params['mobile']))?" mobile like '%".$params['mobile']."%' ":"";
        //姓名
        $whereName = (isset($params['name']) && trim($params['name']))?" name like '%".$params['name']."%' ":"";
        //昵称
        $wherWorkerId = (isset($params['worker_id']) && trim($params['worker_id']))?" worker_id like '%".$params['worker_id']."%' ":"";
        //id列表
        $whereIdList = (isset($params['idList']) && count($params['idList']))?" id in (".implode(",",$params['idList']).") ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereCompany,$whereMobile,$whereName,$wherWorkerId,$whereIdList);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //获取用户数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $UserCount = $this->getCompanyUserCount($params);
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
                $UserList['UserList'][$value['id']] = $value;
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
    public function getCompanyUserCount($params)
    {
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_company_user);
        //生成查询列
        $fields = Base_common::getSqlFields(array("UserCount"=>"count(user_id)"));
        //企业
        $whereCompany = (isset($params['company_id']) && ($params['company_id']>0))?" company_id = '".$params['company_id']."' ":"";
        //姓名
        $whereMobile = (isset($params['mobile']) && trim($params['mobile']))?" mobile like '%".$params['mobile']."%' ":"";
        //姓名
        $whereName = (isset($params['name']) && trim($params['name']))?" name like '%".$params['name']."%' ":"";
        //昵称
        $wherWorkerId = (isset($params['worker_id']) && trim($params['worker_id']))?" worker_id like '%".$params['worker_id']."%' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereCompany,$whereMobile,$whereName,$wherWorkerId);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    /**
     * 根据输入条件匹配企业导入的用户列表
     * @param char $name 姓名
     * @param char $authType 验证方式
     * @param char $auth 用以验证的数据
     * @param string $fields 所要获取的数据列
     * @return array
     */
    public function getCompanyUserByColumn($company_id,$name = "",$authType = "mobile", $auth = "",$fields = "*")
    {
        $table_to_process = Base_Widget::getDbTable($this->table_company_user);
        return $this->db->select($table_to_process, $fields, '`company_id` = ? and `name` = ? and `'.$authType.'` = ?', [$company_id,$name,$auth]);
    }
    /**
     * 新增单个用户记录
     * @param array $bind 所要添加的数据列
     * @return boolean
     */
    public function insertCompanyUser($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_company_user);
        return $this->db->insert($table_to_process, $bind);
    }

    /**
     * 更新单个企业导入用户记录
     * @param char $user_id 用户ID
     * @param array $bind 更新的数据列表
     * @return boolean
     */
    public function updateCompanyUser($id, array $bind)
    {
        $id = intval($id);
        $bind['update_time'] = date("Y-m-d H:i:s",time());
        $table_to_process = Base_Widget::getDbTable($this->table_company_user);
        return $this->db->update($table_to_process, $bind, '`id` = ?', $id);
    }
    /**
     * 更新单个企业导入用户记录
     * @param char $user_id 用户ID
     * @param array $bind 更新的数据列表
     * @return boolean
     */
    public function updateCompanyUserByUser($user_id, array $bind)
    {
        $user_id = intval($user_id);
        $bind['update_time'] = date("Y-m-d H:i:s",time());
        $table_to_process = Base_Widget::getDbTable($this->table_company_user);
        return $this->db->update($table_to_process, $bind, '`user_id` = ?', $user_id);
    }

    public function getTokenForManager($manager_id)
    {
        $redis_key = "UserTokenForManager_".$manager_id;
        $oRedis = new Base_Cache_Redis("Hj");
        $cache = $oRedis->get($redis_key);
        if(!$cache || strlen($cache)<=20)
        {
            $tokenUrl = $this->config->apiUrl.$this->config->api['api']['get_token_for_manager']."?manager_id=".$manager_id;
            $tokenInfo  = file_get_contents($tokenUrl);
            $tokenInfo = json_decode($tokenInfo,true);
            $userToken = $tokenInfo['data']['user_token']??"";
            if($userToken != "")
            {
                $oRedis->set($redis_key,$userToken,3600);
            }
            else
            {
                $userToken = "";
            }
        }
        else
        {
            $userToken = $cache;
        }
        return $userToken;
    }
    /**
     * 获取用户报名列表
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return array
     */
    public function getUserActivityLog($params,$fields = array("*"))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_activity_log);
        $order = " ORDER BY id desc";
        //企业
        $whereCompany = (isset($params['company_id']) && ($params['company_id']>0))?" company_id = '".$params['company_id']."' ":"";
        //活动
        $whereActivity = (isset($params['activity_id']) && ($params['activity_id']>0))?" activity_id = '".$params['activity_id']."' ":"";
        //用户
        $whereUser = (isset($params['user_id']) && ($params['user_id']>0))?" user_id = '".$params['user_id']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereCompany,$whereActivity,$whereUser);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //获取用户数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $UserCount = $this->getUserActivityLogCount($whereCondition);
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
                $UserList['UserList'][$value['user_id']] = $value;
            }
        }
        else
        {
            return $UserList;
        }
        return $UserList;
    }
    /**
     * 获取用户报名记录数量
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return integer
     */
    public function getUserActivityLogCount($whereCondition)
    {
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_activity_log);
        //生成查询列
        $fields = Base_common::getSqlFields(array("UserCount"=>"count(user_id)"));
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }

    public function getConnectedUserInfo($manager_id)
    {
        $managerInfo = (new Widget_Manager())->getRow($manager_id,"id,openid");
        $userInfo = [];
        if($managerInfo['openid']!="")
        {
            $table_to_process = Base_Widget::getDbTable($this->table);
            $userInfo = $this->db->selectRow($table_to_process, "user_id,wechatid", '`wechatid` = ?', trim($managerInfo['openid']));
        }
        return $userInfo;
    }

}

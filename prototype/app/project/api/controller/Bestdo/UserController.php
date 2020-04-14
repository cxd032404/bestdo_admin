<?php
/**
 *
 * 
 */
class Bestdo_UserController extends AbstractController
{
    /**
     *对象声明
     */
    protected $oUser;

    /**
     * 初始化
     * (non-PHPdoc)
     * @see AbstractController#init()
     */
    public function init()
    {
        parent::init();
        $this->oUser = new Bestdo_UserInfo();
    }

    /**
     *获取所用户信息(缓存)
     */
    public function getUserInfoAction()
    {
        //是否调用缓存
        $Cache = isset($this->request->Cache) ? abs(intval($this->request->Cache)) : 1;
        //是否显示说明注释 默认为1
        $UserId = isset($this->request->UserId) ? abs(intval($this->request->UserId)) : 0;
        //获得用户信息
        $UserInfo = $this->oUser->getUserInfo($UserId,"*",$Cache);
        //结果数组 如果列表中有数据则返回成功，否则返回失败
        $result = array("return" => isset($UserInfo['UserId']) ? 1 : 0, "UserInfo" => $UserInfo);
        echo json_encode($result);
    }
    /**
     *通过Token获取所用户信息(缓存)
     */
    public function getUserInfoByTokenAction()
    {
        //是否调用缓存
        $Cache = isset($this->request->Cache) ? abs(intval($this->request->Cache)) : 1;
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            //获得用户信息
            $UserInfo = $this->oUser->getUserInfo($TokenInfo['UserId'],"*",$Cache);
            //结果数组 如果列表中有数据则返回成功，否则返回失败
            $result = array("return" => isset($UserInfo['UserId']) ? 1 : 0, "UserInfo" => $UserInfo);
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    /**
     *通过微信OpenID获取所用户信息(缓存)
     */
    public function getUserInfoByWechatAction()
    {
        //是否调用缓存
        $Cache = isset($this->request->Cache) ? abs(intval($this->request->Cache)) : 1;
        //Token
        $OpenId = isset($this->request->OpenId) ? trim($this->request->OpenId) : "";
        //根据第三方平台ID查询用户
        $UserInfo = $this->oUser->getUserByColumn("WeChatId",$OpenId,"UserId");
        //客户端
        $IP = isset($this->request->IP) ?  trim($this->request->IP):"127.0.0.1";
        //如果查询到
        if(isset($UserInfo['UserId']))
        {
            //获得用户信息
            $UserInfo = $this->oUser->getUserInfo($UserInfo['UserId'],"*",$Cache);
            //如果获取到
            if(isset($UserInfo['UserId']))
            {
                $result = array("return" => 1, "UserInfo" => $UserInfo,"Token"=>$this->oUser->makeToken($UserInfo['UserId'],$IP,"WeChat"));

            }
            else
            {
                //结果数组 如果列表中有数据则返回成功，否则返回失败
                $result = array("return" => 0, "NeedReg" => 1);
            }
        }
        else
        {
            $result = array("return" => 0,"NeedReg"=>1);
        }
        echo json_encode($result);
    }

    /**
     *手机注册
     */
    public function mobileRegAction()
    {
        //手机号码
        $Mobile = isset($this->request->Mobile) ? trim($this->request->Mobile) : "";
        //密码
        $Password = isset($this->request->Password) ? trim($this->request->Password) : "";
        //客户端
        $IP = isset($this->request->IP) ?  trim($this->request->IP):"127.0.0.1";
        //尝试账号登录
        $Login = $this->oUser->MobileReg($Mobile,$Password);
        //无用户，创建
        if(isset($Login['RegId']))
        {
            //结果数组 返回注册信息，引导绑定手机
            $result = array("return" => 1, "RegInfo" => $Login,"comment" => "请输入已经发往手机的验证码");
        }
        elseif($Login == -1)
        {
            //结果数组 返回失败
            $result = array("return" => 0,"comment" => "用户已存在，请更换其他手机号码注册");
        }
        else
        {
            //结果数组 返回失败
            $result = array("return" => 0,"comment" => "注册失败，请重试");
        }
        echo json_encode($result);
    }
    /**
     *手机登录
     */
    public function mobileLoginAction()
    {
        //手机号码
        $Mobile = isset($this->request->Mobile) ? trim($this->request->Mobile) : "";
        //密码
        $Password = isset($this->request->Password) ? trim($this->request->Password) : "";
        //客户端
        $IP = isset($this->request->IP) ?  trim($this->request->IP):"127.0.0.1";
        //尝试账号登录
        $Login = $this->oUser->MobileLogin($Mobile,$Password);
        //登录成功
        if(isset($Login['UserId']))
        {
            //结果数组 返回用户信息
            $result = array("return" => 1, "UserInfo" => $Login,"Token"=>$this->oUser->makeToken($Login['UserId'],$IP,"Mobile"),"comment" => "登录成功");
        }
        //已有用户用第三方登陆过
        elseif($Login == -1)
        {
            //结果数组 返回注册信息，引导绑定手机
            $result = array("return" => -1, "comment" => "是否用其他方式登陆过？试试微信？");
        }
        //无用户，创建
        elseif($Login == -2)
        {
            //结果数组 返回失败
            $result = array("return" => 0,"comment" => "登录失败，请重试");
        }
        else
        {
            //结果数组 返回失败
            $result = array("return" => 0,"comment" => "登录失败，请重试");
        }
        echo json_encode($result);
    }
    /**
     *第三方登录
     */
    public function thirdPartyLoginAction()
    {
        $LoginData  = '{"openid": "odLjsvvYfXvkm9Rkrd4HAHXeqvA8","nickname": "JiMMy","headimgurl": "http://wx.qlogo.cn/mmopen/s6icJeKAt9X2zFZiafUjibkZhkibib8ickRZMDeoIwpfAeh04htIbSecdkU5uoW0AdAucU1kM4tEnKuw6uW6zeaWBYwLMYj9evlJvy/0","sex": "0","province": "","city": ""}';
        //$LoginData  = '{"openid": "odLjsvnl2cUkbbbM8EBvZmJOX7Sw","nickname": "鏍嬭緣tim","headimgurl": "http://wx.qlogo.cn/mmopen/fl6pKMZtTyXGYHHVno0td2q2q1K7U1r4Gx1Hib8mL7lVQiaCdux7ZrtAZicmeOu79ZOuhGicDmSUC9LiaqIRwIzQbVIzyvwbXmyn3/0","sex": "1","province": "涓婃捣","city": "娴︿笢鏂板尯"}';
        //身份数据
        $LoginData = isset($this->request->LoginData) ? trim($this->request->LoginData) : $LoginData;
        //第三方来源
        $LoginSource = isset($this->request->LoginSource) ? trim($this->request->LoginSource) : "WeChat";
        //客户端
        $IP = isset($this->request->IP) ?  trim($this->request->IP):"127.0.0.1";
        //身份数据解包
        $LoginData = json_decode($LoginData,true);
        //尝试第三方登录
        $Login = $this->oUser->ThirdPartyLoginNew($LoginData,$LoginSource);
        //登录成功
        if(isset($Login['UserId']))
        {
            //结果数组 返回用户信息
            $result = array("return" => 1, "UserInfo" => $Login,"Token"=>$this->oUser->makeToken($Login['UserId'],$IP,$LoginSource),"comment" => "登录成功");
        }
        //无用户，创建
        elseif(isset($Login['RegId']))
        {
            //结果数组 返回注册信息，引导绑定手机
            $result = array("return" => 1, "RegInfo" => $Login,"comment" => $Login['NeedMobile']?"请绑定手机":"请输入已经发往手机的验证码");
        }
        else
        {
            //结果数组 返回失败
            $result = array("return" => 0,"comment" => "登录失败，请重试");
        }
        echo json_encode($result);
    }
    /**
     *第三方登录时绑定手机
     */
    public function thirdPartyRegByMobileAction()
    {
        $Mobile = isset($this->request->Mobile) ? trim($this->request->Mobile) : "";
        $RegId = isset($this->request->RegId) ? abs(intval($this->request->RegId)) :0;
        //获取注册记录
        $RegInfo = $this->oUser->getRegInfo($RegId);
        //根据手机号码获取用户信息
        $UserInfo = $this->oUser->getUserByColumn("Mobile",$Mobile);
        //如果用户找到 且 当前登录方式的信息为不空（表示用户用同样的方式注册过且绑定同样的手机）
        if(isset($UserInfo['UserId']) && ($UserInfo[$RegInfo['RegPlatform']."Info"]!=""))
        {
            //返回错误
            $result = array("return" => 0,"comment" => "用户已存在，请输入其他的手机号码");
        }
        else
        {
            //获取正在用此手机号注册的用户
            $RegInfoList = $this->oUser->getUserRegByColumn("Mobile",$Mobile);
            //循环注册记录
            foreach($RegInfoList as $key => $value)
            {
                //如果发现尚未失效
                if(strtotime($value['ExceedTime'])>=time() && $value['RegId']!=$RegId)
                {
                    //返回错误
                    $result = array("return" => 0,"comment" => "此手机其他用户正在验证中");
                    echo json_encode($result);
                    return;
                }
            }
            //如果获取到注册记录
            if(isset($RegInfo['RegId']))
            {
                //尚未绑定手机
                if($RegInfo['Mobile']=="")
                {
                    $ValidateCode = sprintf("%06d",rand(1,999999));
                    //更新记录
                    $RegInfoUpdate = array('Mobile'=>$Mobile,'ExceedTime'=>date("Y-m-d H:i:s",time()+3600),'ValidateCode' => $ValidateCode);
                    $update = $this->oUser->updateRegInfo($RegInfo['RegId'],$RegInfoUpdate);
                    //如果更新成功
                    if($update)
                    {
                        $params = array(
                            "smsContent" => array("code"=>$ValidateCode,"product"=>"淘赛体育"),
                            "Mobile"=> $RegInfoUpdate['Mobile'],
                            "SMSCode"=>"SMS_Validate_Code"
                        );
                        Base_common::dayuSMS($params);
                        $result = array("return" => 1,"comment" => "验证码已发送");
                    }
                    else
                    {
                        //返回错误
                        $result = array("return" => 0,"comment" => "更新失败，请重试");
                    }
                }
                else
                {
                    //如果已经发送该手机
                    if($RegInfo['Mobile']==$Mobile)
                    {
                        //更新记录
                        $update = $this->oUser->thirdPartyRegMobile($RegId,$Mobile);
                        //如果更新成功
                        if($update)
                        {
                            $result = array("return" => 1,"comment" => "验证码已发送");
                        }
                        else
                        {
                            //返回错误
                            $result = array("return" => 0,"comment" => "更新失败，请重试");
                        }
                    }
                    else
                    {
                        //如果已过有效期
                        if(strtotime($RegInfo['ExceedTime'])<=time())
                        {
                            //更新记录
                            $update = $this->oUser->thirdPartyRegMobile($RegId,$Mobile);
                            //如果更新成功
                            if($update)
                            {
                                $result = array("return" => 1,"comment" => "验证码已发送");
                            }
                            else
                            {
                                //返回错误
                                $result = array("return" => 0,"comment" => "更新失败，请重试");
                            }
                        }
                        else
                        {
                            $result = array("return" => 1,"comment" => "验证码之前已发送到尾号为".substr($RegInfo['Mobile'],-4)."的其他手机");
                        }
                    }
                }
            }
            else
            {
                //返回错误
                $result = array("return" => 0,"comment" => "无此记录，请重试");
            }
        }
        echo json_encode($result);
    }
    /**
     *注册时的短信验证
     */
    public function regMobileAuthAction()
    {
        //手机号码
        $Mobile = isset($this->request->Mobile) ? trim($this->request->Mobile) : "";
        //验证码
        $ValidateCode = isset($this->request->ValidateCode) ? trim($this->request->ValidateCode) : "";
        //客户端
        $IP = isset($this->request->IP) ?  trim($this->request->IP):"127.0.0.1";
        //短信验证
        $Auth = $this->oUser->regMobileAuth($Mobile,$ValidateCode);
        //如果验证成功
        if($Auth['UserId'] > 0)
        {
            //结果数组 返回用户信息
            $result = array("return" => 1, "UserInfo" => $this->oUser->getUserInfo($Auth['UserId'],"*",0),"Token"=>$this->oUser->makeToken($Auth['UserId'],$IP,$Auth['LoginSource']),"comment" => "注册成功");
        }
        elseif($Auth['RegId']>0)
        {
            //结果数组 返回用户信息
            $result = array("return" => 0, "NeedPassword" => 1, "comment" => "请输入密码");
        }
        elseif($Auth == -1)
        {
            //结果数组 返回用户信息
            $result = array("return" => 0, "Auth" => 1, "comment" => "验证失败，已重发请重新输入");
        }
        else
        {
            //结果数组 返回用户信息
            $result = array("return" => 0, "comment" => "验证失败");
        }
        echo json_encode($result);
    }
    //根据他人填写的信息选择用户
    public function getUserByOtherAction()
    {
        //获取证件类型列表
        $IdTypeList = $this->oUser->getAuthIdType();
        //获取证件类型列表
        $SexList = $this->oUser->getSexList();
        //用户姓名
        $Name = trim($this->request->Name);
        //证件号码
        $IdNo = trim($this->request->IdNo);
        //生日
        $Birthday = trim($this->request->Birthday);
        //证件类型
        $IdType = abs(intval($this->request->IdType));
        //如果不在证件类型范围内，默认为身份证
        $IdType = isset($IdTypeList[$IdType])?$IdType:1;
        //证件类型
        $Sex = abs(intval($this->request->Sex));
        //如果不在证件类型范围内，默认为身份证
        $Sex = isset($SexList[$Sex])?$Sex:0;
        //联系电话号码
        $ContactMobile = trim($this->request->ContactMobile);
        //如果证件号码长度不足
        if(strlen($IdNo) <=6)
        {
            //返回错误
            $result = array("return" => 0,"comment"=>"请输入合法的证件号");
        }
        else
        {
            //根据证件号码获取用户信息
            $UserInfo = $this->oUser->getUserByColumn("IdNo",$IdNo);
            //如果已经被占用
            if(isset($UserInfo['UserId']))
            {
                //如果关联比赛用户
                if($UserInfo['RaceUserId']>0)
                {
                    //根据证件号码获取比赛用户信息
                    $RaceUserInfo = $this->oUser->getRaceUser($UserInfo['RaceUserId']);
                    if(($RaceUserInfo['ContactMobile'] != $ContactMobile ) || ($RaceUserInfo['Name'] != $Name) || (($IdType !=1) && ($RaceUserInfo['Birthday']!=$Birthday)))
                    {
                        $NewUserInfo = array('Name'=>$Name,'ContactMobile'=>$ContactMobile,'Birthday'=>$Birthday);
                        $update = $this->oUser->updateRaceUser($UserInfo['RaceUserId'],$NewUserInfo);
                        if($update)
                        {
                            $RaceUserInfo = $this->oUser->getRaceUser($UserInfo['RaceUserId']);
                        }
                    }
                    //返回用户信息
                    $result = array("return" => 1,"RaceUserInfo"=>$RaceUserInfo);
                }
                else
                {
                    //根据证件号码获取比赛用户信息
                    $RaceUserInfo = $this->oUser->getRaceUserByColumn("IdNo",$IdNo);
                    //如果已经被占用
                    if(isset($RaceUserInfo['RaceUserId']))
                    {
                        $NewUserInfo = array("RaceUserId"=>$RaceUserInfo['RaceUserId']);
                        $update = $this->oUser->updateUser($UserInfo['UserId'],$NewUserInfo);
                        if($update)
                        {
                            //返回用户信息
                            $result = array("return" => 1,"RaceUserInfo"=>$RaceUserInfo);
                        }

                    }
                    else
                    {
                        //根据用户创建比赛用户
                        $RaceUserId = $this->oUser->createRaceUserByUserInfo($UserInfo['UserId']);
                        //如果创建成功
                        if($RaceUserId)
                        {
                            //根据证件号码获取比赛用户信息
                            $RaceUserInfo = $this->oUser->getRaceUser($RaceUserId);
                            //返回用户信息
                            $result = array("return" => 1,"RaceUserInfo"=>$RaceUserInfo);
                        }
                        else
                        {
                            $result = array("return" => 0,"comment"=>"用户数据错误");
                        }
                    }

                }
            }
            else
            {
                //根据证件号码获取比赛用户信息
                $RaceUserInfo = $this->oUser->getRaceUserByColumn("IdNo",$IdNo);
                //如果已经被占用
                if(isset($RaceUserInfo['RaceUserId']))
                {
                    if(($RaceUserInfo['ContactMobile'] != $ContactMobile ) || ($RaceUserInfo['Name'] != $Name) || (($IdType !=1) && ($RaceUserInfo['Birthday']!=$Birthday)))
                    {
                        $UserInfo = array('Name'=>$Name,'ContactMobile'=>$ContactMobile,'Birthday'=>$Birthday);
                        $update = $this->oUser->updateRaceUser($RaceUserInfo['RaceUserId'],$UserInfo);
                        if($update)
                        {
                            $RaceUserInfo = $this->oUser->getRaceUser($RaceUserInfo['RaceUserId']);
                        }
                    }
                    //返回用户信息
                    $result = array("return" => 1,"RaceUserInfo"=>$RaceUserInfo);
                }
                else
                {
                    //如果姓名长度不足
                    if(strlen($Name) <=2)
                    {
                        //返回错误
                        $result = array("return" => 0,"comment"=>"请输入合法的姓名");
                    }
                    else
                    {
                        //生成用户信息
                        $UserInfo = array('CreateUserId'=>0,'Name'=>$Name,'Sex'=>$Sex,'Birthday'=>$Birthday,'ContactMobile'=>$ContactMobile,'IdNo'=>$IdNo,'IdType'=>$IdType,'Available'=>0,'RegTime'=>date("Y-m-d H:i:s",time()));
                        if($IdType==1)
                        {
                            $UserInfo['Birthday'] = substr($IdNo,6,4)."-".substr($IdNo,10,2)."-".substr($IdNo,12,2);
                            $UserInfo['Sex'] = $Sex==0?$Sex:(intval(substr($IdNo,16,1))%2==0?2:1);
                        }
                        //创建用户
                        $CreateUser = $this->oUser->insertRaceUser($UserInfo);
                        //如果创建成功
                        if($CreateUser)
                        {
                            //强制获取用户信息
                            $RaceUserInfo = $this->oUser->getRaceUser($CreateUser,"*");
                            //返回用户信息
                            $result = array("return" => 1,"RaceUserInfo"=>$RaceUserInfo);
                        }
                        else
                        {
                            //返回错误
                            $result = array("return" => 0,"comment"=>"创建失败");
                        }
                    }
                }
            }
        }
        echo json_encode($result);
    }
    //更新用户的身份信息（身份证，姓名）
    public function updateUserIdentityAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            //证件号码
            $Birthday = trim($this->request->Birthday);
            //获取证件类型列表
            $IdTypeList = $this->oUser->getAuthIdType();
            //获取证件类型列表
            $SexList = $this->oUser->getSexList();
            //用户姓名
            $Name = trim($this->request->Name);
            //证件号码
            $IdNo = trim($this->request->IdNo);
            //证件类型
            $IdType = abs(intval($this->request->IdType));
            //如果不在证件类型范围内，默认为身份证
            $IdType = isset($IdTypeList[$IdType])?$IdType:1;
            //证件类型
            $Sex = abs(intval($this->request->Sex));
            //如果不在证件类型范围内，默认为身份证
            $Sex = isset($SexList[$Sex])?$Sex:0;
            //紧急联系人姓名
            $ContactName = trim($this->request->ContactName);
            //证件号码
            $ContactMobile = trim($this->request->ContactMobile);
            //如果证件号码长度不足
            if(strlen($IdNo) <=6)
            {
                //返回错误
                $result = array("return" => 0,"comment"=>"请输入合法的证件号");
            }
            else
            {
                //根据证件号码获取用户信息
                $IdUserInfo = $this->oUser->getUserByColumn("IdNo",$IdNo);
                //如果已经被占用 且不是该用户本人
                if(isset($IdUserInfo['UserId']) && ($IdUserInfo['UserId']!=$TokenInfo['UserId']))
                {
                    //返回错误
                    $result = array("return" => 0,"commento"=>"证件号码已经被其他用户使用");
                }
                else
                {
                    //如果姓名长度不足
                    if(strlen($Name) <=2)
                    {
                        //返回错误
                        $result = array("return" => 0,"comment"=>"请输入合法的姓名");
                    }
                    else
                    {
                        //生成用户信息
                        $UserInfo = array('Name'=>$Name,'Sex'=>$Sex,'IdNo'=>$IdNo,'IdType'=>$IdType,'Birthday'=>$Birthday,"ICE"=>json_encode(array("1"=>array('Name'=>$ContactName,'ContactMobile'=>$ContactMobile))));
                        if($IdType==1)
                        {
                            $UserInfo['Birthday'] = substr($IdNo,6,4)."-".substr($IdNo,10,2)."-".substr($IdNo,12,2);
                            $UserInfo['Sex'] = $Sex==0?$Sex:(intval(substr($IdNo,16,1))%2==0?2:1);
                        }
                        //更新用户
                        $UpdateUser = $this->oUser->updateUser($TokenInfo['UserId'],$UserInfo);
                        //如果创建成功
                        if($UpdateUser)
                        {
                            if($IdUserInfo['RaceUserId']==0)
                            {
                                //根据用户创建比赛用户
                                $RaceUserId = $this->oUser->createRaceUserByUserInfo($TokenInfo['UserId']);
                            }
                            //强制获取用户信息
                            $UserInfo = $this->oUser->getUserInfo($TokenInfo['UserId'],"*",0);
                            //返回用户信息
                            $result = array("return" => 1,"UserInfo"=>$UserInfo);
                        }
                        else
                        {
                            //返回错误
                            $result = array("return" => 0,"comment"=>"更新失败");
                        }
                    }
                }
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }

    //更新用户的身份信息（身份证，姓名）
    public function updateUserIdentityApplyAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            $UserInfo = $this->oUser->getUserInfo($TokenInfo['UserId'],"UserId,Mobile",0);
            //如果手机号码长度大于8
            if(strlen($UserInfo['Mobile'])>8)
            {
                //发短信
                $return = $this->oUser->userValidateAuthApply($TokenInfo['UserId'],"IdModify","Mobile",$UserInfo['Mobile']);
                if($return)
                {
                    //返回失败，要求绑定手机
                    $result = array("return" => 1,"NeedMobile"=>1,"comment"=>"短信发送成功");
                }
                else
                {
                    //返回失败，要求绑定手机
                    $result = array("return" => 0,"NeedMobile"=>1,"comment"=>"短信发送失败");
                }

            }
            else
            {
                //返回失败，要求绑定手机
                $result = array("return" => 0,"NeedMobile"=>1);
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    // 申请更新用户的手机号码
    public function updateUserMobileApplyAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            $UserInfo = $this->oUser->getUserInfo($TokenInfo['UserId'],"UserId,Mobile",0);
            //如果手机号码长度大于8
            if(strlen($UserInfo['Mobile'])>8)
            {
                //发短信
                $return = $this->oUser->userValidateAuthApply($TokenInfo['UserId'],"MobileModify","Mobile",$UserInfo['Mobile']);
                if($return)
                {
                    //返回成功
                    $result = array("return" => 1,"NeedMobile"=>1,"comment"=>"短信发送成功");
                }
                else
                {
                    //返回失败，要求绑定手机
                    $result = array("return" => 0,"NeedMobile"=>1,"comment"=>"短信发送失败");
                }

            }
            else
            {
                //手机号码
                $Mobile = isset($this->request->Mobile) ? trim($this->request->Mobile) : "";
                if(strlen($Mobile)>=8)
                {
                    //发短信
                    $return = $this->oUser->userValidateAuthApply($TokenInfo['UserId'],"MobileModify","Mobile",$Mobile);
                    if($return)
                    {
                        //返回成功
                        $result = array("return" => 1,"NeedMobile"=>1,"comment"=>"短信发送成功");
                    }
                    else
                    {
                        //返回失败，要求绑定手机
                        $result = array("return" => 0,"NeedMobile"=>1,"comment"=>"短信发送失败");
                    }
                }
                else
                {
                    //返回失败，要求重写手机号码
                    $result = array("return" => 0,"NeedMobile"=>1,"comment"=>"请输入有效的手机号码");
                }
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    //更新用户的身份信息（身份证，姓名）
    public function updateUserIdentityByValidateCodeAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            //验证码
            $ValidateCode = trim($this->request->ValidateCode);
            //获取用户信息
            $UserInfo = $this->oUser->getUserInfo($TokenInfo['UserId'],"UserId,Mobile",0);
            //先验证短信
            $validate = $this->oUser->userValidateAuth($TokenInfo['UserId'],"IdModify",$ValidateCode);
            //验证成功
            if($validate['return'])
            {
                //证件号码
                $Birthday = trim($this->request->Birthday);
                //获取证件类型列表
                $IdTypeList = $this->oUser->getAuthIdType();
                //获取证件类型列表
                $SexList = $this->oUser->getSexList();
                //用户姓名
                $Name = trim($this->request->Name);
                //证件号码
                $IdNo = trim($this->request->IdNo);
                //证件类型
                $IdType = abs(intval($this->request->IdType));
                //如果不在证件类型范围内，默认为身份证
                $IdType = isset($IdTypeList[$IdType])?$IdType:1;
                //证件类型
                $Sex = abs(intval($this->request->Sex));
                //如果不在证件类型范围内，默认为身份证
                $Sex = isset($SexList[$Sex])?$Sex:0;
                //紧急联系人姓名
                $ContactName = trim($this->request->ContactName);
                //证件号码
                $ContactMobile = trim($this->request->ContactMobile);
                //如果证件号码长度不足
                if(strlen($IdNo) <=6)
                {
                    //发短信
                    $this->oUser->userValidateAuthApply($TokenInfo['UserId'],"IdModify","Mobile",$UserInfo['Mobile']);
                    //返回错误
                    $result = array("return" => 0,"comment"=>"请输入合法的证件号");
                }
                else
                {
                    //根据证件号码获取用户信息
                    $IdUserInfo = $this->oUser->getUserByColumn("IdNo",$IdNo);
                    //如果已经被占用 且不是该用户本人
                    if(isset($IdUserInfo['UserId']) && ($IdUserInfo['UserId']!=$TokenInfo['UserId']))
                    {
                        $this->oUser->userValidateAuthApply($TokenInfo['UserId'],"IdModify","Mobile",$UserInfo['Mobile']);
                        //返回错误
                        $result = array("return" => 0,"comment"=>"证件号码已经被其他用户使用");
                    }
                    else
                    {
                        //如果姓名长度不足
                        if(strlen($Name) <=2)
                        {
                            $this->oUser->userValidateAuthApply($TokenInfo['UserId'],"IdModify","Mobile",$UserInfo['Mobile']);
                            //返回错误
                            $result = array("return" => 0,"comment"=>"请输入合法的姓名");
                        }
                        else
                        {
                            //生成用户信息
                            $UserInfo = array('Name'=>$Name,'Sex'=>$Sex,'IdNo'=>$IdNo,'IdType'=>$IdType,'Birthday'=>$Birthday,"ICE"=>json_encode(array("1"=>array('Name'=>$ContactName,'ContactMobile'=>$ContactMobile))));
                            if($IdType==1)
                            {
                                $UserInfo['Birthday'] = substr($IdNo,6,4)."-".substr($IdNo,10,2)."-".substr($IdNo,12,2);
                                $UserInfo['Sex'] = $Sex==0?$Sex:(intval(substr($IdNo,16,1))%2==0?2:1);
                            }
                            //更新用户
                            $UpdateUser = $this->oUser->updateUser($TokenInfo['UserId'],$UserInfo);
                            //如果创建成功
                            if($UpdateUser)
                            {
                                if($IdUserInfo['RaceUserId']==0)
                                {
                                    //根据用户创建比赛用户
                                    $RaceUserId = $this->oUser->createRaceUserByUserInfo($TokenInfo['UserId']);
                                }
                                //强制获取用户信息
                                $UserInfo = $this->oUser->getUserInfo($TokenInfo['UserId'],"*",0);
                                //返回用户信息
                                $result = array("return" => 1,"UserInfo"=>$UserInfo);
                            }
                            else
                            {
                                $this->oUser->userValidateAuthApply($TokenInfo['UserId'],"IdModify","Mobile",$UserInfo['Mobile']);
                                //返回错误
                                $result = array("return" => 0,"comment"=>"更新失败");
                            }
                        }
                    }
                }
            }
            else
            {
                //返回错误
                $result = array("return" => 0,"comment"=>"验证失败，短信验证码已重发");
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    //更新用户的手机号码
    public function updateUserMobileByValidateCodeAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            //验证码
            $ValidateCode = trim($this->request->ValidateCode);
            //获取用户信息
            $UserInfo = $this->oUser->getUserInfo($TokenInfo['UserId'],"UserId,Mobile",0);
            //先验证短信
            $validate = $this->oUser->userValidateAuth($TokenInfo['UserId'],"IdModify",$ValidateCode);
            //验证成功
            if($validate['return'])
            {
                //如果手机号码长度大于8
                if(strlen($UserInfo['Mobile'])>8)
                {
                    //生成用户信息
                    $UserInfo = array('Mobile'=>"",'ContactMobile'=>"");
                    //更新用户
                    $UpdateUser = $this->oUser->updateUser($TokenInfo['UserId'],$UserInfo);
                    //如果更新成功
                    if($UpdateUser)
                    {
                        //强制获取用户信息
                        $UserInfo = $this->oUser->getUserInfo($TokenInfo['UserId'],"*",0);
                        //返回用户信息
                        $result = array("return" => 1,"UserInfo"=>$UserInfo);
                    }
                    else
                    {
                        $this->oUser->userValidateAuthApply($TokenInfo['UserId'],"IdModify","Mobile",$UserInfo['Mobile']);
                        //返回错误
                        $result = array("return" => 0,"comment"=>"更新失败");
                    }
                }
                else
                {
                    //生成用户信息
                    $UserInfo = array('Mobile'=>$validate['Mobile'],'ContactMobile'=>$validate['Mobile']);
                    //更新用户
                    $UpdateUser = $this->oUser->updateUser($TokenInfo['UserId'],$UserInfo);
                    //如果更新成功
                    if($UpdateUser)
                    {
                        //强制获取用户信息
                        $UserInfo = $this->oUser->getUserInfo($TokenInfo['UserId'],"*",0);
                        //返回用户信息
                        $result = array("return" => 1,"UserInfo"=>$UserInfo);
                    }
                    else
                    {
                        $this->oUser->userValidateAuthApply($TokenInfo['UserId'],"IdModify","Mobile",$validate['Mobile']);
                        //返回错误
                        $result = array("return" => 0,"comment"=>"更新失败");
                    }
                }
            }
            else
            {
                //返回错误
                $result = array("return" => 0,"comment"=>"更新失败，短信验证码已重发");
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    //更新用户的联系信息（省/市/地址）
    public function updateUserContactAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            //省
            $Province = trim(urldecode($this->request->Province));
            //市
            $City = trim(urldecode($this->request->City));
            //地址
            $Address = trim(urldecode($this->request->Address));
            //生成用户信息
            $UserInfo = array("Contact"=>json_encode(array('Province'=>$Province,'City'=>$City,'Address'=>$Address)));
            //更新用户
            $UpdateUser = $this->oUser->updateUser($TokenInfo['UserId'],$UserInfo);
            //如果创建成功
            if($UpdateUser)
            {
                //强制获取用户信息
                $UserInfo = $this->oUser->getUserInfo($TokenInfo['UserId'],"*",0);
                //返回用户信息
                $result = array("return" => 1,"UserInfo"=>$UserInfo);
            }
            else
            {
                //返回错误
                $result = array("return" => 0,"comment"=>"更新失败");
            }

        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    //更新用户的身份信息（身份证，姓名）
    public function updateUserIceAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            //紧急联系人姓名
            $Name = trim($this->request->Name);
            //证件号码
            $ContactMobile = trim($this->request->ContactMobile);
            //如果紧急联系人姓名和手机长度不足
            if((strlen($Name) <=2) || (strlen($ContactMobile) <=8))
            {
                //返回错误
                $result = array("return" => 0,"comment"=>"请输入合法紧急联系人姓名和联系方式");
            }
            else
            {
                //生成用户信息
                $UserInfo = array("ICE"=>json_encode(array("1"=>array('Name'=>$Name,'ContactMobile'=>$ContactMobile))));
                //更新用户
                $UpdateUser = $this->oUser->updateUser($TokenInfo['UserId'],$UserInfo);
                //如果创建成功
                if($UpdateUser)
                {
                    //强制获取用户信息
                    $UserInfo = $this->oUser->getUserInfo($TokenInfo['UserId'],"*",0);
                    //返回用户信息
                    $result = array("return" => 1,"UserInfo"=>$UserInfo);
                }
                else
                {
                    //返回错误
                    $result = array("return" => 0,"comment"=>"更新失败");
                }
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    //更新用户的身份信息（身份证，姓名）
    public function testDeleteAction()
    {
        $Mobile = isset($this->request->Mobile) ? trim($this->request->Mobile) :"";
        $deleteRegInfo = $this->oUser->deleteRegInfoByMobile($Mobile);
        $deleteRegLog = $this->oUser->deleteRegLogByMobile($Mobile);
        $delete = $this->oUser->deleteUserByMobile($Mobile);
        echo "deleteReg:".$deleteRegInfo."<br>";
        echo "deleteUser:".$delete."<br>";
        echo "deleteRegLOg:".$deleteRegLog."<br>";

    }
    public function resetRegSmsByMobileAction()
    {
        $Mobile = isset($this->request->Mobile) ? trim($this->request->Mobile) :"";
        $Reset = $this->oUser->resetRegSmsByMobile($Mobile);
        if($Reset)
        {
            $result = array("return" => 1,"comment"=>"重发成功");
        }
        else
        {
            $result = array("return" => 0,"comment"=>"发送失败，请重试");
        }
        echo json_encode($result);
    }
    public function resetRegSmsByRegAction()
    {
        $RegId = isset($this->request->RegId) ? abs(intval($this->request->RegId)) :0;
        $Reset = $this->oUser->resetRegSmsByReg($RegId);
        if($Reset)
        {
            $result = array("return" => 1,"comment"=>"重发成功");
        }
        else
        {
            $result = array("return" => 0,"comment"=>"发送失败，请重试");
        }
        echo json_encode($result);
    }
    public function checkMobileExistAction()
    {
        $Mobile = isset($this->request->Mobile) ? trim($this->request->Mobile) :"";
        $Check = $this->oUser->checkMobileExist($Mobile);
        if($Check['Available']==1)
        {
            if(isset($Check['UserInfo']['UserId']))
            {
                $result = array("return" => 1,"UserInfo"=>$Check['UserInfo'],"comment"=>"可以继续注册");
            }
            else
            {
                $result = array("return" => 1,"comment"=>"可以继续注册");
            }
        }
        else
        {
            $result = array("return" => 0,"comment"=>"手机号码已经被占用");
        }
        echo json_encode($result);
    }
    public function updateRegPasswordAction()
    {
        $RegId = isset($this->request->RegId) ? abs(intval($this->request->RegId)) :0;
        $Password = isset($this->request->Password) ? trim($this->request->Password) :"";
        //获取注册记录
        $RegInfo = $this->oUser->getRegInfo($RegId);
        //如果获取到注册记录
        if(isset($RegInfo['RegId']))
        {
            //如果密码为空 且 是手机注册
            if(($RegInfo['Password'] == "") && ($RegInfo['RegPlatform'] == "Mobile"))
            {
                $NewRegInfo = array("Password" =>md5($Password));
                $Update = $this->oUser->updateRegInfo($RegInfo['RegId'],$NewRegInfo);
                if($Update)
                {
                    $result = array("return" => 1,"comment"=>"更新成功");
                }
                else
                {
                    $result = array("return" => 0,"comment"=>"更新失败");
                }
            }
            else
            {
                $result = array("return" => 0,"comment"=>"密码已存在，禁止更新");
            }
        }
        else
        {
            $result = array("return" => 0,"comment"=>"无此记录");
        }

        echo json_encode($result);
    }
    /**
     *手机注册
     */
    public function mobileResetPasswordAction()
    {
        //手机号码
        $Mobile = isset($this->request->Mobile) ? trim($this->request->Mobile) : "";
        //尝试重置密码
        $Reset = $this->oUser->MobileResetPassword($Mobile);
        //重置成功
        if($Reset>0)
        {
            //结果数组 返回注册信息，引导绑定手机
            $result = array("return" => 1,"ResetId"=>$Reset, "comment" => "请输入已经发往手机的验证码");
        }
        elseif($Reset == -1)
        {
            //结果数组 返回失败
            $result = array("return" => 0,"comment" => "无此用户");
        }
        elseif($Reset == -2)
        {
            //结果数组 返回失败
            $result = array("return" => 0,"comment" => "此用户未设置密码");
        }
        echo json_encode($result);
    }
    /**
     *重置时的短信验证
     */
    public function resetPasswordMobileAuthAction()
    {
        //手机号码
        $Mobile = isset($this->request->Mobile) ? trim($this->request->Mobile) : "";
        //验证码
        $ValidateCode = isset($this->request->ValidateCode) ? trim($this->request->ValidateCode) : "";
        //客户端
        $IP = isset($this->request->IP) ?  trim($this->request->IP):"127.0.0.1";
        //短信验证
        $Auth = $this->oUser->resetPasswordMobileAuth($Mobile,$ValidateCode);
        //如果验证成功
        if($Auth)
        {
            //结果数组 返回用户信息
            $result = array("return" => 1,"comment" => "请更新密码");
        }
        elseif($Auth == -1)
        {
            //结果数组 返回用户信息
            $result = array("return" => 0, "NeedAuth" => 1, "comment" => "验证失败，已重发请重新输入");
        }
        elseif($Auth == -2)
        {
            //结果数组 返回用户信息
            $result = array("return" => 0, "NeedAuth" => 1, "comment" => "验证超时，已重发请重新输入");
        }
        else
        {
            //结果数组 返回用户信息
            $result = array("return" => 0, "comment" => "验证失败");
        }
        echo json_encode($result);
    }
    /**
     *重置密码
     */
    public function resetPasswordAction()
    {
        //手机号码
        $ResetId = abs(intval($this->request->ResetId)) ? abs(intval($this->request->ResetId)) : 0;
        //验证码
        $Password = isset($this->request->Password) ? trim($this->request->Password) : "";
        //客户端
        $IP = isset($this->request->IP) ?  trim($this->request->IP):"127.0.0.1";
        //短信验证
        $Reset = $this->oUser->resetPassword($ResetId,$Password,$IP);
        //如果验证成功
        if($Reset)
        {
            //结果数组 返回用户信息
            $result = array("return" => 1,"comment" => "重置密码成功");
        }
        elseif($Reset == -1)
        {
            //结果数组 返回用户信息
            $result = array("return" => 0, "NeedAuth" => 1, "comment" => "验证超时，已重发请重新输入");
        }
        else
        {
            //结果数组 返回用户信息
            $result = array("return" => 0, "comment" => "验证失败");
        }
        echo json_encode($result);
    }
    /**
     *更新密码
     */
    public function userResetPasswordAction()
    {
        //旧密码
        $OldPassword = isset($this->request->OldPassword) ? trim($this->request->OldPassword) : "";
        //密码
        $Password = isset($this->request->Password) ? trim($this->request->Password) : "";
        //客户端
        $IP = isset($this->request->IP) ?  trim($this->request->IP):"127.0.0.1";
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            //短信验证
            $Reset = $this->oUser->userResetPassword($TokenInfo['UserId'],$OldPassword,$Password,$IP);
            //如果验证成功
            if($Reset)
            {
                //结果数组 返回用户信息
                $result = array("return" => 1,"comment" => "更新密码成功");
            }
            else
            {
                //结果数组 返回用户信息
                $result = array("return" => 0, "comment" => "更新密码失败");
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    /**
     *获取用户报名记录
     */
    public function getUserRaceLogAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            //获取用户信息
            $UserInfo = $this->oUser->getUserInfo($TokenInfo['UserId'],"UserId,RaceUserId",0);
            if($UserInfo['RaceUserId'])
            {
                $UserRaceList = $this->oUser->getRaceUserList(array("RaceUserId"=>$UserInfo['RaceUserId']));
                if(count($UserRaceList))
                {
                    $oRace = new Xrace_Race();
                    $RaceCatalogList = array();
                    $RaceGroupList = array();
                    $RaceList = array();
                    foreach($UserRaceList as $key => $RaceInfo)
                    {
                        if(!isset($RaceCatalogList[$RaceInfo['RaceCatalogId']]))
                        {
                            $RaceCatalogList[$RaceInfo['RaceCatalogId']] = $oRace->getRaceCatalog($RaceInfo['RaceCatalogId'],"RaceCatalogId,RaceCatalogName");
                        }
                        if(!isset($RaceGroupList[$RaceInfo['RaceGroupId']]))
                        {
                            $RaceGroupList[$RaceInfo['RaceGroupId']] = $oRace->getRaceGroup($RaceInfo['RaceGroupId'],"RaceGroupId,RaceGroupName");
                        }
                        if(!isset($RaceCatalogList[$RaceInfo['RaceId']]))
                        {
                            $RaceList[$RaceInfo['RaceId']] = $oRace->getRace($RaceInfo['RaceId'],"RaceId,RaceName");
                        }
                        $UserRaceList[$key]['RaceCatalogName'] = $RaceCatalogList[$RaceInfo['RaceCatalogId']]['RaceCatalogName'];
                        $UserRaceList[$key]['RaceGroupName'] = $RaceGroupList[$RaceInfo['RaceGroupId']]['RaceGroupName'];
                        $UserRaceList[$key]['RaceName'] = $RaceList[$RaceInfo['RaceId']]['RaceName'];
                    }
                }
            }
            $result = array("return" => 1,"UserRaceList"=>$UserRaceList);
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    /**
     *用户添加自己的芯片
     */
    public function addUserChipAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            //芯片
            $ChipId = trim($this->request->ChipId);
            //芯片类型
            $ChipType = trim($this->request->ChipType);
            //昵称
            $NickName = trim(urldecode($this->request->NickName));
            //如果长度为空，则用芯片ID填充
            $NickName = strlen($NickName)?$NickName:$ChipId;
            $oChip = new Xrace_Chip();
            //获取芯片类型列表数组
            $ChipTypeList = $oChip->getChipTypeList();
            //如果不在类型列表内，则默认为Mylaps
            $ChipType = isset($ChipTypeList[$ChipType])?$ChipType:1;
            //获取芯片信息
            $ChipInfo = $oChip->getChipInfo($ChipId);
            //如果找到芯片信息
            if(isset($ChipInfo['ChipId']))
            {
                //如果芯片已经属于自己
                if($ChipInfo['UserId']==$TokenInfo['UserId'])
                {
                    //结果数组 返回用户信息
                    $result = array("return" => 1,"comment" => "芯片已经添加过了，无需重复添加");
                }
                else
                {
                    //结果数组 返回用户信息
                    $result = array("return" => 0,"comment" => "芯片添加失败，已经被其他用户绑定此芯片了哦");
                }
            }
            else
            {
                //初始化芯片信息
                $ChipInfo = array('ChipId'=>$ChipId,'UserId'=>$TokenInfo['UserId'],'ChipTypeId'=>$ChipType,'Nickname'=>$NickName);
                //添加数据
                $Add = $oChip->insertChip($ChipInfo);
                //如果添加成功
                if($Add)
                {
                    //结果数组
                    $result = array("return" => 1,"comment" => "芯片添加成功");
                }
                else
                {
                    //结果数组
                    $result = array("return" => 0,"comment" => "芯片添加失败，请稍后重试");
                }
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    /**
     *用户xiu该自己的芯片
     */
    public function updateUserChipAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            //芯片
            $ChipId = trim($this->request->ChipId);
            //芯片类型
            $ChipType = trim($this->request->ChipType);
            //昵称
            $NickName = trim(urldecode($this->request->NickName));
            //如果长度为空，则用芯片ID填充
            $NickName = strlen($NickName)?$NickName:$ChipId;
            $oChip = new Xrace_Chip();
            //获取芯片类型列表数组
            $ChipTypeList = $oChip->getChipTypeList();
            //如果不在类型列表内，则默认为Mylaps
            $ChipType = isset($ChipTypeList[$ChipType])?$ChipType:1;
            //获取芯片信息
            $ChipInfo = $oChip->getChipInfo($ChipId);
            //如果找到芯片信息
            if(isset($ChipInfo['ChipId']))
            {
                //如果芯片已经属于自己
                if($ChipInfo['UserId']==$TokenInfo['UserId'])
                {
                    $bind = array("NickName"=>$NickName);
                    //更新芯片
                    $update = $oChip->updateChipInfo($ChipId,$bind);
                    //如果更新成功
                    if($update)
                    {
                        //结果数组
                        $result = array("return" => 1,"comment" => "芯片更新成功");
                    }
                    else
                    {
                        //结果数组
                        $result = array("return" => 0,"comment" => "芯片更新失败，请稍后重试");
                    }
                }
                else
                {
                    //结果数组
                    $result = array("return" => 0,"comment" => "芯片添加失败，已经被其他用户绑定此芯片了哦");
                }
            }
            else
            {
                    //结果数组
                $result = array("return" => 0,"comment" => "芯片不存在哦，请检查后重试");
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    /**
     *用户添加自己的芯片
     */
    public function getUserChipListAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            $oChip = new Xrace_Chip();
            //获取芯片列表
            $UserChipList = $oChip->getChipList(array("UserId"=>$TokenInfo['UserId']));
            //结果数组 返回芯片列表
            $result = array("return" => 1,"UserChipList"=>$UserChipList);
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    /**
     *用户删除自己的芯片
     */
    public function deleteUserChipAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if($TokenInfo['UserId'])
        {
            //芯片
            $ChipId = trim($this->request->ChipId);
            $oChip = new Xrace_Chip();
            //获取芯片信息
            $ChipInfo = $oChip->getChipInfo($ChipId);
            //如果找到芯片信息
            if(isset($ChipInfo['ChipId']))
            {
                //如果芯片已经属于自己
                if($ChipInfo['UserId']==$TokenInfo['UserId'])
                {
                    //删除芯片
                    $delete = $oChip->deleteChip($ChipId);
                    //如果删除成功
                    if($delete)
                    {
                        //结果数组
                        $result = array("return" => 1,"comment" => "芯片删除成功");
                    }
                    else
                    {
                        //结果数组
                        $result = array("return" => 0,"comment" => "芯片删除失败，请稍后重试");
                    }
                }
                else
                {
                    //结果数组
                    $result = array("return" => 0,"comment" => "芯片删除失败，已经被其他用户绑定此芯片了哦");
                }
            }
            else
            {
                //结果数组
                $result = array("return" => 0,"comment" => "芯片不存在哦，请检查后重试");
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    /**
     *用户约战
     */
    public function applyRaceAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if ($TokenInfo['UserId'])
        {
            //开始时间
            $StartTime = abs(intval($this->request->StartTime));
            //开始时间
            $EndTime = abs(intval($this->request->EndTime));
            //场地
            $ArenaId = abs(intval($this->request->ArenaId));
            //芯片
            $ChipId = trim(urldecode($this->request->ChipId));
            //如果结束时间时间小于当前时间
            if($EndTime <= time())
            {
                $result = array("return" => 0,"comment"=>"您选择的时间段有误，请重新选择");
            }
            else
            {
                $oChip = new Xrace_Chip();
                //获取芯片信息
                $ChipInfo = $oChip->getChipInfo($ChipId);
                //如果找到芯片信息
                if(isset($ChipInfo['ChipId']))
                {
                    //如果芯片是用户本人的
                    if($ChipInfo['UserId']==$TokenInfo['UserId'])
                    {
                        $oArena = new Xrace_Arena();
                        //获取场地信息
                        $ArenaInfo = $oArena->getArena($ArenaId,'ArenaId,ArenaName');
                        //如果找到
                        if(isset($ArenaInfo['ArenaId']))
                        {
                            $oUserRace = new Xrace_UserRace();
                            //获取尚在处理的约战队列
                            $UserApplyCount = $oUserRace->getUserRaceApplyCount(array("UserId"=>$TokenInfo['UserId'],"ArenaId"=>$ArenaInfo['ArenaId'],"ChipId"=>$ChipId));
                            if($oUserRace->getMaxRaceAppplyCount() <= $UserApplyCount)
                            {
                                //返回失败
                                $result = array("return" => 0, "comment" => "处理中的约战请求最多不超过".$oUserRace->getMaxRaceAppplyCount()."条哦");
                            }
                            else
                            {
                                //初始化数据 过期时间设为约战时间段结束前10分钟
                                $ApplyInfo = array("ApplyExceedTime"=>$EndTime-600,"ChipName"=>$ChipInfo['NickName'],"UserId"=>$TokenInfo['UserId'],"ArenaId"=>$ArenaInfo['ArenaId'],"ApplyTime"=>time(),"ApplyStartTime"=>$StartTime,"ApplyEndTime"=>$EndTime,"ChipId"=>$ChipId);
                                //添加记录
                                $Apply = $oUserRace->insertRaceApply($ApplyInfo);
                                //如果添加成功
                                if($Apply)
                                {
                                    //返回成功
                                    $result = array("return" => 1, "comment" => "加入队列，请等待系统为您分配对手");
                                }
                                else
                                {
                                    //返回成功
                                    $result = array("return" => 0, "comment" => "加入队列失败，请稍后重试");
                                }
                            }
                        }
                        else
                        {
                            //返回失败
                            $result = array("return" => 0, "comment" => "无此场地");
                        }
                    }
                    else
                    {
                        //返回失败
                        $result = array("return" => 0, "comment" => "芯片好像不是你的哦，选错了？");
                    }
                }
                else
                {
                    //结果数组
                    $result = array("return" => 0,"comment" => "芯片不存在哦，请检查后重试");
                }
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    /**
     *用户约战队列
     */
    public function getUserApplyRaceQueueAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if ($TokenInfo['UserId'])
        {
            //页码，默认为1
            $Page = isset($this->request->Page) ? abs(intval($this->request->Page)) : 1;
            //每页数量
            $PageSize = isset($this->request->PageSize) ? abs(intval($this->request->PageSize)) : 5;
            //场地
            $ArenaId = abs(intval($this->request->ArenaId));
            //芯片
            $ChipId = trim(urldecode($this->request->ChipId));
            //UserId
            $UserId = abs(intval($this->request->UserId));
            //如果指定选手是登陆用户本人
            if($UserId == $TokenInfo['UserId'])
            {
                $params = array("ArenaId"=>$ArenaId,"ChipId"=>$ChipId,"UserId"=>$TokenInfo['UserId'],"Page"=>$Page,"PageSize"=>$PageSize);
            }
            elseif($UserId>0)
            {
                $params = array("ArenaId"=>$ArenaId,"ChipId"=>$ChipId,"UserId"=>$UserId,"Page"=>$Page,"PageSize"=>$PageSize);
            }
            else
            {
                $params = array("ArenaId"=>$ArenaId,"ChipId"=>$ChipId,"UserIgnore"=>$TokenInfo['UserId'],"UserId"=>$UserId,"Page"=>$Page,"PageSize"=>$PageSize);
            }
            $oUserRace = new Xrace_UserRace();
            //获取用户约战队列
            $UserRaceApplyList = $oUserRace->getUserRaceApplyList($params);
            if(count($UserRaceApplyList['UserRaceApplyList']))
            {
                $oArena = new Xrace_Arena();
                //获取场地列表
                $ArenaList = $oArena->getAllArenaList("ArenaName,ArenaId");
                $oUserInfo = new Xrace_UserInfo();
                foreach($UserRaceApplyList['UserRaceApplyList'] as $ApplyId => $ApplyInfo)
                {
                    //如果用户列表中未找到这个用户
                    if(!isset($UserList[$ApplyInfo['UserId']]))
                    {
                        //获取用户信息
                        $UserList[$ApplyInfo['UserId']] = $oUserInfo->getUser($ApplyInfo['UserId'],"UserId,Name,Sex");
                    }
                    //用户姓名
                    $UserRaceApplyList['UserRaceApplyList'][$ApplyId]['Name'] = isset($UserList[$ApplyInfo['UserId']]['Name'])?$UserList[$ApplyInfo['UserId']]['Name']:"未知用户";
                    //场地名称
                    $UserRaceApplyList['UserRaceApplyList'][$ApplyId]['ArenaName'] = isset($ArenaList[$ApplyInfo['ArenaId']])?$ArenaList[$ApplyInfo['ArenaId']]['ArenaName']:"未知场地";
                }
            }
            $result = array("return" => 1,"UserRaceApplyQueue"=>$UserRaceApplyList['UserRaceApplyList']);
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    /**
     *用户对战队列
     */
    public function getUserRaceListAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if ($TokenInfo['UserId'])
        {
            //页码，默认为1
            $Page = isset($this->request->Page) ? abs(intval($this->request->Page)) : 1;
            //每页数量
            $PageSize = isset($this->request->PageSize) ? abs(intval($this->request->PageSize)) : 5;
            //场地
            $ArenaId = abs(intval($this->request->ArenaId));
            //芯片
            $ChipId = trim(urldecode($this->request->ChipId));
            //场地
            $ArenaId = abs(intval($this->request->ArenaId));
            //UserId
            $UserId = abs(intval($this->request->UserId));
            //如果指定选手是登陆用户本人
            if($UserId == $TokenInfo['UserId'])
            {
                $params = array("ArenaId"=>$ArenaId,"ChipId"=>$ChipId,"ArenaId"=>$ArenaId,"UserId"=>$TokenInfo['UserId'],"Page"=>$Page,"PageSize"=>$PageSize);
            }
            elseif($UserId>0)
            {
                $params = array("ArenaId"=>$ArenaId,"ChipId"=>$ChipId,"ArenaId"=>$ArenaId,"UserId"=>$UserId,"Page"=>$Page,"PageSize"=>$PageSize);
            }
            else
            {
                $params = array("ArenaId"=>$ArenaId,"ChipId"=>$ChipId,"ArenaId"=>$ArenaId,"UserIgnore"=>$TokenInfo['UserId'],"UserId"=>$UserId,"Page"=>$Page,"PageSize"=>$PageSize);
            }
            $oUserRace = new Xrace_UserRace();
            //获取用户约战队列
            $UserRaceList = $oUserRace->getUserRaceList($params,array("UserRaceId,RaceId,ChipId"));
            if(count($UserRaceList['UserRaceList']))
            {
                $oArena = new Xrace_Arena();
                //初始化空的场地队列
                $ArenaList = array();
                //获取比赛状态列表
                $RaceStatusList = $oUserRace->getRaceStausList();
                foreach($UserRaceList['UserRaceList'] as $UserRaceId => $ApplyInfo)
                {
                    //获取关联的比赛信息
                    $RaceInfo = $oUserRace->getAppliedRace($ApplyInfo['RaceId']);
                    if(isset($RaceInfo['RaceId']))
                    {
                        if(!isset($ArenaList[$RaceInfo['ArenaId']]))
                        {
                            $ArenaList[$RaceInfo['ArenaId']] = $oArena->getArena($RaceInfo['ArenaId'],"ArenaId,ArenaName");
                        }
                        //场地名称
                        $UserRaceList['UserRaceList'][$UserRaceId]['ArenaName'] = isset($ArenaList[$RaceInfo['ArenaId']])?$ArenaList[$RaceInfo['ArenaId']]['ArenaName']:"未知场地";
                        $UserRaceList['UserRaceList'][$UserRaceId]['RaceStartTime'] = $RaceInfo['RaceStartTime'];
                        $UserRaceList['UserRaceList'][$UserRaceId]['RaceEndTime'] = $RaceInfo['RaceEndTime'];
                        //保存比赛状态信息
                        $UserRaceList['UserRaceList'][$UserRaceId]['RaceStatusName'] = $RaceStatusList[$RaceInfo['RaceStatus']];
                    }
                }
            }
            $result = array("return" => 1,"UserRaceList"=>$UserRaceList['UserRaceList']);
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    /**
     *用户约战信息
     */
    public function getUserApplyRaceInfoAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if ($TokenInfo['UserId'])
        {
            //申请记录
            $ApplyId = abs(intval($this->request->ApplyId));
            //申请记录
            $ApplyId = abs(intval($this->request->ApplyId));
            //芯片
            $ChipId = trim($this->request->ChipId);

            $oUserRace = new Xrace_UserRace();
            //获取用户约战队列
            $ApplyInfo = $oUserRace->getUserRaceApply($ApplyId);
            if(isset($ApplyInfo['ApplyId']))
            {
                $oUserInfo = new Xrace_UserInfo();
                //获取用户信息
                $UserInfo = $oUserInfo->getUser($ApplyInfo['UserId'],"UserId,Name,Sex");
                $oArena = new Xrace_Arena();
                //获取场地信息
                $ArenaInfo = $oArena->getArena($ApplyInfo['ArenaId'],'ArenaId,ArenaName');
                //用户姓名
                $ApplyInfo['Name'] = isset($UserInfo['Name'])?$UserInfo['Name']:"未知用户";
                    //场地名称
                $ApplyInfo['ArenaName'] = isset($ArenaInfo['ArenaId'])?$ArenaInfo['ArenaName']:"未知场地";
                $result = array("return" => 1,"UserRaceApplyInfo"=>$ApplyInfo);
            }
            else
            {
                $result = array("return" => 0,"comment"=>"无此记录");
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    /**
     *用户主动加入某个对战
     */
    public function userRaceToApplyAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if ($TokenInfo['UserId'])
        {
            //申请记录
            $ApplyId = abs(intval($this->request->ApplyId));
            //芯片
            $ChipId = trim($this->request->ChipId);
            $oUserRace = new Xrace_UserRace();
            //获取用户约战队列
            $ApplyInfo = $oUserRace->getUserRaceApply($ApplyId);
            if(isset($ApplyInfo['ApplyId']))
            {
                //如果对战对手是自己
                if($TokenInfo['UserId']==$ApplyInfo['UserId'])
                {
                    $result = array("return" => 0,"comment"=>"咦，不能和自己对战哦");
                }
                else
                {
                    //获取当前时间
                    $Time = time();
                    //如果尚未到这个约战的过期时间
                    if($Time <= $ApplyInfo['ApplyExceedTime'])
                    {
                        //对战信息的处理（单人模式）
                        $Apply = $oUserRace->insertUserAppliedRace($ApplyId,1,array(array("UserId"=>$TokenInfo['UserId'],"ChipId"=>$ChipId),array("UserId"=>$ApplyInfo['UserId'],"ChipId"=>$ApplyInfo['ChipId'])));
                        //如果创建成功
                        if($Apply)
                        {
                            //获取比赛信息
                            $RaceInfo = $oUserRace->getAppliedRace($Apply);
                            $oArena = new Xrace_Arena();
                            $oChip = new Xrace_Chip();
                            //获取场地信息
                            $ArenaInfo = $oArena->getArena($RaceInfo['ArenaId'],'ArenaId,ArenaName');
                            $RaceInfo['ArenaName'] = $ArenaInfo['ArenaName'];
                            $UserList = $oUserRace->getUserRaceList(array("RaceId"=>$Apply));
                            foreach($UserList['UserRaceList'] as $UserId => $ApplyInfo)
                            {
                                //获得用户信息
                                $UserInfo = $this->oUser->getUserInfo($UserId,"UserId,Name");
                                //保存用户姓名
                                $UserList['UserRaceList'][$UserId]['Name'] = $UserInfo['Name'];
                                //获取芯片信息
                                $ChipInfo = $oChip->getChipInfo($ApplyInfo['ChipId'],"ChipId,NickName");
                                //保存用户姓名
                                $UserList['UserRaceList'][$UserId]['ChipName'] = $ChipInfo['NickName'];
                            }
                            //返回比赛信息
                            $result = array("return" => 1,"comment"=>"约战成功！","RaceInfo"=>$RaceInfo,"UserList"=>$UserList['UserRaceList']);
                        }
                        else
                        {
                            $result = array("return" => 0,"comment"=>"约战失败！");
                        }
                    }
                    else
                    {
                        $result = array("return" => 0,"comment"=>"约战请求过期了嘛，换个试试");
                    }
                }
            }
            else
            {
                $result = array("return" => 0,"comment"=>"无此记录");
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
    /**
     *获取用户对战详情
     */
    public function getUserAppliedRaceInfoAction()
    {
        //Token
        $Token = isset($this->request->Token) ? trim($this->request->Token) : "";
        //获取Tokenx信息
        $TokenInfo = $this->oUser->getToken($Token);
        //如果获取到
        if ($TokenInfo['UserId'])
        {
            //比赛记录
            $RaceId = abs(intval($this->request->RaceId));
            $oUserRace = new Xrace_UserRace();
            //获取比赛信息
            $RaceInfo = $oUserRace->getAppliedRace($RaceId);
            //如果找到比赛
            if($RaceInfo['RaceId'])
            {
                $oArena = new Xrace_Arena();
                $oChip = new Xrace_Chip();
                //获取比赛状态列表
                $RaceStatusList = $oUserRace->getRaceStausList();
                //获取用户对战状态列表
                $UserRaceStausList = $oUserRace->getUserRaceStausList();
                //获取场地信息
                $ArenaInfo = $oArena->getArena($RaceInfo['ArenaId'], 'ArenaId,ArenaName');
                //数据解包
                $RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
                //保存场地信息
                $RaceInfo['ArenaName'] = $ArenaInfo['ArenaName'];
                //保存比赛状态信息
                $RaceInfo['RaceStatusName'] = $RaceStatusList[$RaceInfo['RaceStatus']];
                //获取选手列表
                $UserList = $oUserRace->getUserRaceList(array("RaceId" => $RaceId));
                //循环选手列表
                foreach ($UserList['UserRaceList'] as $UserRaceId => $ApplyInfo)
                {
                    //获得用户信息
                    $UserInfo = $this->oUser->getUserInfo($ApplyInfo['UserId'], "UserId,Name");
                    //保存用户姓名
                    $UserList['UserRaceList'][$UserRaceId]['Name'] = $UserInfo['Name'];
                    //获取芯片信息
                    $ChipInfo = $oChip->getChipInfo($ApplyInfo['ChipId'], "ChipId,NickName");
                    //保存用户姓名
                    $UserList['UserRaceList'][$UserRaceId]['ChipName'] = $ChipInfo['NickName'];
                    //保存用户比赛状态
                    $UserList['UserRaceList'][$UserRaceId]['ResultName'] = $UserRaceStausList[$ApplyInfo['Result']];

                }
                //返回比赛信息
                $result = array("return" => 1, "RaceInfo" => $RaceInfo, "UserList" => $UserList['UserRaceList']);
            }
            else
            {
                $result = array("return" => 0,"comment"=>"无此记录");
            }
        }
        else
        {
            $result = array("return" => 0,"NeedLogin"=>1);
        }
        echo json_encode($result);
    }
}
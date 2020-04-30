{tpl:tpl contentHeader/}
<script type="text/javascript">
    function userDetail(uid){
        userDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=user.detail&user_id=' + uid, {title:'用户详情',width:600,height:400});
    }
    function userPasswordUpdate(uid){
        userPasswordUpdateBox = divBox.showBox('{tpl:$this.sign/}&ac=user.password.update.submit&user_id=' + uid, {title:'密码更新',width:400,height:300});
    }
    function userMobileUpdate(uid){
        userMobileUpdateBox = divBox.showBox('{tpl:$this.sign/}&ac=user.mobile.update.submit&user_id=' + uid, {title:'手机号码更新',width:400,height:220});
    }
    function userAuth(uid){
        userAuthBox = divBox.showBox('{tpl:$this.sign/}&ac=user.auth.info&user_id=' + uid, {title:'实名认证',width:600,height:400});
    }
    function userTeamList(uid,uname){
        userTeamListBox = divBox.showBox('{tpl:$this.sign/}&ac=user.team.list&user_id=' + uid, {title:uname+'的队伍列表',width:600,height:400});
    }
</script>

<fieldset><legend>操作</legend>
</fieldset>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
    企业：<select name="company_id"  id="company_id" size="1">
        <option value="0"{tpl:if(0==$params.company_id)}selected="selected"{/tpl:if} >全部</option>
        {tpl:loop $companyList  $company_info}
        <option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$params.company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
        {/tpl:loop}
    </select>
    姓名:<input type="text" class="span2" name="true_name" value="{tpl:$params.true_name/}" />
    昵称:<input type="text" class="span2" name="nick_name" value="{tpl:$params.nick_name/}" />
    性别:<select name="sex" class="span2" size="1">
        <option value="-1" {tpl:if($params.sex==-1)}selected="selected"{/tpl:if}>全部</option>
        {tpl:loop $sexList $sexSymble $sexName}
        <option value="{tpl:$sexSymble/}" {tpl:if($params.sex==$sexSymble)}selected="selected"{/tpl:if}>{tpl:$sexName/}</option>
        {/tpl:loop}
    </select>

    <input type="submit" name="Submit" value="查询" />{tpl:$export_var/}
</form>
<fieldset><legend>用户列表</legend>
<table width="99%" align="center" class="table table-bordered table-striped">
      <tr>
        <th align="center" class="rowtip">用户ID</th>
          <th align="center" class="rowtip">企业</th>
          <th align="center" class="rowtip">真实姓名</th>
          <th align="center" class="rowtip">昵称</th>
        <th align="center" class="rowtip">联系电话</th>
        <th align="center" class="rowtip">性别</th>
          <!--<th align="center" class="rowtip">实名认证</th>-->
        <th align="center" class="rowtip">生日</th>
        <th align="center" class="rowtip">注册时间</th>
        <th align="center" class="rowtip">最后登陆</th>
        <th align="center" class="rowtip">操作</th>
      </tr>
    {tpl:loop $UserList.UserList $UserInfo}
      <tr class="hover">
        <td align="center">{tpl:$UserInfo.user_id/}</td>
          <td align="center">{tpl:$UserInfo.company_name/}</td>
          <td align="center">{tpl:$UserInfo.true_name/}</td>
        <td align="center">{tpl:$UserInfo.nick_name/}</td>
        <td align="center">{tpl:$UserInfo.mobile/}</td>
        <td align="center">{tpl:$UserInfo.sex/}</td>
        <td align="center">{tpl:$UserInfo.birthday/}</td>
        <td align="center">{tpl:$UserInfo.reg_time/}</td>
        <td align="center">{tpl:$UserInfo.last_login_time/}<br>{tpl:$UserInfo.LoginSourceName/}</td>
        <!--<td align="center"><a  href="javascript:;" onclick="userDetail('{tpl:$UserInfo.user_id/}')">详细</a>{tpl:if($UserInfo.AuthStatus==1)} | <a  href="javascript:;" onclick="userAuth('{tpl:$UserInfo.user_id/}')">审核</a>{/tpl:if} | {tpl:$UserInfo.License/} | {tpl:$UserInfo.Team/} | <a  href="javascript:;" onclick="userPasswordUpdate('{tpl:$UserInfo.user_id/}')">更新密码</a> | <a  href="javascript:;" onclick="userMobileUpdate('{tpl:$UserInfo.user_id/}')">更新手机</a></td>-->
          <td align="center"><a  href="javascript:;" onclick="userDetail('{tpl:$UserInfo.user_id/}')">详细</a></td>

      </tr>
    {/tpl:loop}
    <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>

</table>
</fieldset>
{tpl:tpl contentFooter/}
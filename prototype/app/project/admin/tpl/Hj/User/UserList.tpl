{tpl:tpl contentHeader/}
<script type="text/javascript">
    function userDetail(uid){
        userDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=user.detail&UserId=' + uid, {title:'用户详情',width:600,height:400});
    }
    function userPasswordUpdate(uid){
        userPasswordUpdateBox = divBox.showBox('{tpl:$this.sign/}&ac=user.password.update.submit&UserId=' + uid, {title:'密码更新',width:400,height:300});
    }
    function userMobileUpdate(uid){
        userMobileUpdateBox = divBox.showBox('{tpl:$this.sign/}&ac=user.mobile.update.submit&UserId=' + uid, {title:'手机号码更新',width:400,height:220});
    }
    function userAuth(uid){
        userAuthBox = divBox.showBox('{tpl:$this.sign/}&ac=user.auth.info&UserId=' + uid, {title:'实名认证',width:600,height:400});
    }
    function userTeamList(uid,uname){
        userTeamListBox = divBox.showBox('{tpl:$this.sign/}&ac=user.team.list&UserId=' + uid, {title:uname+'的队伍列表',width:600,height:400});
    }
</script>

<fieldset><legend>操作</legend>
</fieldset>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
    姓名:<input type="text" class="span2" name="Name" value="{tpl:$params.Name/}" />
    昵称:<input type="text" class="span2" name="NickName" value="{tpl:$params.NickName/}" />
    性别:<select name="Sex" class="span2" size="1">
        <option value="-1" {tpl:if($params.Sex==-1)}selected="selected"{/tpl:if}>全部</option>
        {tpl:loop $SexList $SexSymble $SexName}
        <option value="{tpl:$SexSymble/}" {tpl:if($params.Sex==$SexSymble)}selected="selected"{/tpl:if}>{tpl:$SexName/}</option>
        {/tpl:loop}
    </select>
    实名认证状态:<select name="AuthStatus" class="span2" size="1">
        <option value="-1" >全部</option>
        {tpl:loop $AuthStatusList $AuthStatus $AuthStatusName}
        <option value="{tpl:$AuthStatus/}" {tpl:if($params.AuthStatus==$AuthStatus)}selected="selected"{/tpl:if}>{tpl:$AuthStatusName/}</option>
        {/tpl:loop}
    </select>

    <input type="submit" name="Submit" value="查询" />{tpl:$export_var/}
</form>
<fieldset><legend>用户列表</legend>
<table width="99%" align="center" class="table table-bordered table-striped">
      <tr>
        <th align="center" class="rowtip">用户ID</th>
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
        <td align="center">{tpl:$UserInfo.UserId/}</td>
        <td align="center">{tpl:$UserInfo.Name/}</td>
        <td align="center">{tpl:$UserInfo.NickName/}</td>
        <td align="center">{tpl:$UserInfo.Mobile/}</td>
        <td align="center">{tpl:$UserInfo.Sex/}</td>
          <!--<td align="center">{tpl:$UserInfo.AuthStatus/}</td>-->
        <td align="center">{tpl:$UserInfo.Birthday/}</td>
        <td align="center">{tpl:$UserInfo.RegTime/}</td>
        <td align="center">{tpl:$UserInfo.LastLoginTime/}<br>{tpl:$UserInfo.LoginSourceName/}</td>
        <!--<td align="center"><a  href="javascript:;" onclick="userDetail('{tpl:$UserInfo.UserId/}')">详细</a>{tpl:if($UserInfo.AuthStatus==1)} | <a  href="javascript:;" onclick="userAuth('{tpl:$UserInfo.UserId/}')">审核</a>{/tpl:if} | {tpl:$UserInfo.License/} | {tpl:$UserInfo.Team/} | <a  href="javascript:;" onclick="userPasswordUpdate('{tpl:$UserInfo.UserId/}')">更新密码</a> | <a  href="javascript:;" onclick="userMobileUpdate('{tpl:$UserInfo.UserId/}')">更新手机</a></td>-->
          <td align="center"><a  href="javascript:;" onclick="userDetail('{tpl:$UserInfo.UserId/}')">详细</a></td>

      </tr>
    {/tpl:loop}
    <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>

</table>
</fieldset>
{tpl:tpl contentFooter/}

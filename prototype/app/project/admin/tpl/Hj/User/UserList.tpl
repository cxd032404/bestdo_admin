{tpl:tpl contentHeader/}
<script type="text/javascript">
    function userDetail(uid,uname){
        userDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=user.detail&user_id=' + uid, {title:'用户详情-'+uname,width:600,height:500});
    }
    function userDepartment(uid,uname){
        userDepartmentUpdateBox = divBox.showBox('{tpl:$this.sign/}&ac=user.department.modify&user_id=' + uid, {title:'部门更新-'+uname,width:400,height:300});
    }

    function userDisable(p_id,p_name){
        msg = '是否停用 ' + p_name + '的账号?'
        disableBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=user.disable&user_id=' + p_id;}});
    }

    function setTestUser(p_id,p_name,status){
        if(status >0)
        {
            msg = '是否将 ' + p_name + '的账号设定为测试用户?';

        }
        else
        {
            msg = '是否将 ' + p_name + '的账号设定为普通用户?';
        }
        testBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=set.test.user&user_id=' + p_id + '&status='+  status;}});
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
    电话:<input type="text" class="span2" name="mobile" value="{tpl:$params.mobile/}" />
    性别:<select name="sex" class="span2" size="1">
        <option value="-1" {tpl:if($params.sex==-1)}selected="selected"{/tpl:if}>全部</option>
        {tpl:loop $sexList $sexSymble $sexName}
        <option value="{tpl:$sexSymble/}" {tpl:if($params.sex==$sexSymble)}selected="selected"{/tpl:if}>{tpl:$sexName/}</option>
        {/tpl:loop}
    </select>
    <button type="submit" class="pb_btn_light_1">搜索</button>{tpl:$export_var/}
</form>
<fieldset><legend>用户列表</legend>
<div class="ifm_all">
    <table  align="center" class="table table-bordered table-striped" >
      <tr>
        <th align="center" width = "4%" class="rowtip">用户ID</th>
          <th align="center" width = "8%" class="rowtip">企业</th>
          <th align="center" width = "10%" class="rowtip">部门</th>
          <th align="center" width = "10%" class="rowtip">真实姓名</th>
          <th align="center" width = "10%" class="rowtip">昵称</th>
        <th align="center" width = "5%" class="rowtip">联系电话</th>
        <th align="center" width = "3%" class="rowtip">性别</th>
        <th align="center" width = "8%" class="rowtip">注册时间</th>
        <th align="center" width = "8%" class="rowtip">最后登陆</th>
        <th align="center" width = "20%" class="rowtip">操作</th>
      </tr>
    {tpl:loop $UserList.UserList $UserInfo}
      <tr class="hover">
        <td align="center">{tpl:$UserInfo.user_id/} <br> {tpl:if($UserInfo.is_del==1)}(停用){/tpl:if}</td>
          <td align="center">{tpl:$UserInfo.company_name/}</td>
          <td align="center"><a  href="javascript:;" onclick="userDepartment('{tpl:$UserInfo.user_id/}','{tpl:$UserInfo.true_name/}')">{tpl:$UserInfo.department_name/}</a></td>
          <td align="center">{tpl:$UserInfo.true_name/}</td>
        <td align="center">{tpl:$UserInfo.nick_name/}</td>
        <td align="center">{tpl:$UserInfo.mobile/}</td>
        <td align="center">{tpl:$UserInfo.sex/}</td>
        <td align="center">{tpl:$UserInfo.reg_time/}</td>
        <td align="center">{tpl:$UserInfo.last_login_time/}<br>{tpl:$UserInfo.LoginSourceName/}</td>
          <td align="center"><a class = "pb_btn_light_1" href="javascript:;" onclick="userDetail('{tpl:$UserInfo.user_id/}','{tpl:$UserInfo.true_name/}')">详细</a>{tpl:if($UserInfo.is_del!=1)} <a class = "pb_btn_grey_1" href="javascript:;" onclick="userDisable('{tpl:$UserInfo.user_id/}','{tpl:$UserInfo.true_name/}')">停用</a>{/tpl:if}
              {tpl:if($UserInfo.test==1)}  <a class = "pb_btn_dark_2" href="javascript:;" onclick="setTestUser('{tpl:$UserInfo.user_id/}','{tpl:$UserInfo.true_name/}',0)">设为普通用户</a>
              {tpl:else}
                <a  class = "pb_btn_light_2" href="javascript:;" onclick="setTestUser('{tpl:$UserInfo.user_id/}','{tpl:$UserInfo.true_name/}',1)">设为测试用户</a>
              {/tpl:if}
          </td>

      </tr>
    {/tpl:loop}
    <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>
</table>
</div>
</fieldset>
{tpl:tpl contentFooter/}

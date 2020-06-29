{tpl:tpl contentHeader/}
<script type="text/javascript">
    $(document).ready(function(){
        $('#company_user_upload').click(function(){
            uploadUserBox = divBox.showBox('{tpl:$this.sign/}&ac=company.user.upload.submit', {title:'上传用户',width:600,height:300});
        });
    });
</script>

<fieldset><legend>操作</legend>
    [ <a href="javascript:;" id="company_user_upload">上传用户</a> ]
</fieldset>
<form action="{tpl:$this.sign/}&ac=company.user.list" name="form" id="form" method="post">
    企业：<select name="company_id"  id="company_id" size="1">
        <option value="0"{tpl:if(0==$params.company_id)}selected="selected"{/tpl:if} >全部</option>
        {tpl:loop $companyList  $company_info}
        <option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$params.company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
        {/tpl:loop}
    </select>
    姓名:<input type="text" class="span2" name="true_name" value="{tpl:$params.true_name/}" />
    手机:<input type="text" class="span2" name="mobile" value="{tpl:$params.mobile/}" />
    工号:<input type="text" class="span2" name="worker_id" value="{tpl:$params.worker_id/}" />
    <input type="submit" name="submit" value="查询" />{tpl:$export_var/}
</form>
<fieldset><legend>用户列表</legend>
<table width="99%" align="center" class="table table-bordered table-striped">
      <tr>
        <th align="center" class="rowtip">企业</th>
        <th align="center" class="rowtip">真实姓名</th>
        <th align="center" class="rowtip">工号</th>
        <th align="center" class="rowtip">联系电话</th>
          <th align="center" class="rowtip">部门</th>
          <th align="center" class="rowtip">关联用户ID</th>
          <th align="center" class="rowtip">操作</th>
      </tr>
    {tpl:loop $UserList.UserList $UserInfo}
      <tr class="hover">
        <td align="center">{tpl:$UserInfo.company_name/}</td>
        <td align="center">{tpl:$UserInfo.name/}</td>
        <td align="center">{tpl:$UserInfo.worker_id/}</td>
        <td align="center">{tpl:$UserInfo.mobile/}</td>
          <td align="center">{tpl:$UserInfo.department_name/}</td>
          <td align="center">{tpl:$UserInfo.user_id/}</td>
          <!--<td align="center"><a  href="javascript:;" onclick="userDetail('{tpl:$UserInfo.user_id/}')">详细</a>{tpl:if($UserInfo.AuthStatus==1)} | <a  href="javascript:;" onclick="userAuth('{tpl:$UserInfo.user_id/}')">审核</a>{/tpl:if} | {tpl:$UserInfo.License/} | {tpl:$UserInfo.Team/} | <a  href="javascript:;" onclick="userPasswordUpdate('{tpl:$UserInfo.user_id/}')">更新密码</a> | <a  href="javascript:;" onclick="userMobileUpdate('{tpl:$UserInfo.user_id/}')">更新手机</a></td>-->
          <td align="center"><a  href="javascript:;" onclick="userDetail('{tpl:$UserInfo.user_id/}')">详细</a></td>

      </tr>
    {/tpl:loop}
    <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>

</table>
</fieldset>
{tpl:tpl contentFooter/}

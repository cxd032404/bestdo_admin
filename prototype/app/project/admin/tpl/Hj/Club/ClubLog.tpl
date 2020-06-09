{tpl:tpl contentHeader/}
<script type="text/javascript">

</script>

<fieldset><legend>操作</legend>
[ <a href="{tpl:$this.sign/}&company_id={tpl:$clubInfo.company_id/}">返回</a> ]
</fieldset>

<fieldset><legend>报名记录</legend>

  <table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">报名记录ID</th>
    <th align="center" class="rowtip">姓名</th>
    <th align="center" class="rowtip">报名时间</th>
  </tr>

{tpl:loop $clubList.UserList $logInfo}
  <tr class="hover">
    <td align="center">{tpl:$logInfo.id/}</td>
    <td align="center">{tpl:$logInfo.user_name/}</td>
    <td align="center">{tpl:$logInfo.create_time/}</td>
  </tr>
{/tpl:loop}
  <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>

</table>
</fieldset>
{tpl:tpl contentFooter/}

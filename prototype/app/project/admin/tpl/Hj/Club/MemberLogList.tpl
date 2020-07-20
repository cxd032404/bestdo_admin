{tpl:tpl contentHeader/}
<script type="text/javascript">

</script>

<fieldset><legend>操作</legend>
[ <a href="{tpl:$this.sign/}&club_id={tpl:$clubInfo.club_id/}"><img src="/icon/return.png" width='30' height='30'/></a> ]
</fieldset>

<fieldset><legend>{tpl:$clubInfo.club_name/} 成员记录</legend>

  <table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">姓名</th>
    <th align="center" class="rowtip">发起人姓名</th>
    <th align="center" class="rowtip">审核人姓名</th>
    <th align="center" class="rowtip">动作</th>
    <th align="center" class="rowtip">发起时间</th>
    <th align="center" class="rowtip">最后更新时间</th>
    <th align="center" class="rowtip">处理时间</th>
    <th align="center" class="rowtip">处理状态</th>
    <th align="center" class="rowtip">备注</th>
  </tr>

{tpl:loop $logList.LogList $logInfo}
  <tr class="hover">
    <td align="center">{tpl:if($logInfo.user_img=="")}{tpl:else}<img src="{tpl:$logInfo.user_img/}" width="30px;" height="30px;"/>{/tpl:if} {tpl:$logInfo.user_name/} </td>
    <td align="center">{tpl:if($logInfo.operate_img=="")}{tpl:else}<img src="{tpl:$logInfo.operate_img/}" width="30px;" height="30px;"/>{/tpl:if} {tpl:$logInfo.operate_user_name/}</td>
    <td align="center">{tpl:if($logInfo.process_img=="")}{tpl:else}<img src="{tpl:$logInfo.process_img/}" width="30px;" height="30px;"/>{/tpl:if} {tpl:$logInfo.process_user_name/}</td>
    <td align="center">{tpl:$logInfo.action_name/}</td>
    <td align="center">{tpl:$logInfo.create_time/}</td>
    <td align="center">{tpl:$logInfo.update_time/}</td>
    <td align="center">{tpl:$logInfo.process_time/}</td>
    <td align="center">{tpl:$logInfo.result_name/}</td>

    <td align="center">发起人：{tpl:$logInfo.detail.comment/}<p>
        操作人：{tpl:$logInfo.detail.operate_comment/}<p></td>
  </tr>
{/tpl:loop}
  <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>

</table>
</fieldset>
{tpl:tpl contentFooter/}

{tpl:tpl contentHeader/}
<script type="text/javascript">
  function invite(){
    inviteBox = divBox.showBox('{tpl:$this.sign/}&ac=invite&club_id={tpl:$clubInfo.club_id/}', {title:'邀请成员加入',width:600,height:500});
  }
</script>

<fieldset><legend>操作</legend>
[ <a href="{tpl:$this.sign/}&club_id={tpl:$clubInfo.club_id/}">返回</a> ｜ <a href="javascript:;" onclick="invite();">邀请</a> ]
</fieldset>

<fieldset><legend>{tpl:$clubInfo.club_name/} 成员列表</legend>

  <table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">姓名</th>
    <th align="center" class="rowtip">头像</th>
    <th align="center" class="rowtip">加入时间</th>
    <th align="center" class="rowtip">备注</th>
  </tr>

{tpl:loop $memberList.MemberList $memberInfo}
  <tr class="hover">
    <td align="center">{tpl:$memberInfo.user_name/}</td>
    <td align="center">{tpl:if($memberInfo.user_img=="")}无{tpl:else}<img src="{tpl:$memberInfo.user_img/}" width="30px;" height="30px;"/>{/tpl:if}</td>
    <td align="center">{tpl:$memberInfo.create_time/}</td>
    <td align="center">{tpl:$memberInfo.detail.comment/}</td>
  </tr>
{/tpl:loop}
  <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>

</table>
</fieldset>
{tpl:tpl contentFooter/}

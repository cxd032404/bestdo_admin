{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="invite_form" name="invite_form" action="{tpl:$inviteUrl/}" method="post">
<input type="hidden" name="club_id" id="club_id" value="{tpl:$clubInfo.club_id/}" />
<input type="hidden" name="UserToken" id="UserToken" value="{tpl:$token/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>俱乐部名称</td>
	<td align="left">{tpl:$clubInfo.club_name/}</td>
</tr>
<tr class="hover">
<td>姓名</td>
<td align="left"><input type="text" class="span2" name="user_name"  id="user_name" value="" size="50" onchange="getUser()"/></td>
</tr>
<tr class="hover">
	<td>说明</td>
	<td align="left"><input name="comment" type="text" class="span2" id="comment" value="" size="50" /></td>
</tr>

	<tr class="hover"><td>用户列表</td>
	<td align="left">	<select name="user_id"  id="user_id" size="1">
			{tpl:loop $companyList  $company_info}
			<option value="0">请搜索</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="invite_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#invite_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.code != 200) {
				divBox.alertBox(jsonResponse.msg,function(){});
			} else {
				var message = '邀请成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=member.list&club_id=' + $('#club_id').val());}});
			}
		}
	};
	$('#invite_form').ajaxForm(options);
});
function getUser()
{
	club=$("#club_id");
	user_name=$("#user_name");
	$.ajax
	({
		type: "GET",
		url: "?ctl=hj/club&ac=get.user.for.invite&club_id="+club.val()+"&user_name="+user_name.val(),
		success: function(msg)
		{
			$("#user_id").html(msg);
		}});
}
</script>
{tpl:tpl contentFooter/}
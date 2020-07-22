{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="leave_club_form" name="leave_club_form" action="{tpl:$leaveUrl/}" method="post">
	<input type="hidden" name="club_id" id="club_id" value="{tpl:$clubInfo.club_id/}" />
<input type="hidden" name="user_id" value="{tpl:$userInfo.user_id/}" />
	<input type="hidden" name="UserToken" id="UserToken" value="{tpl:$token/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td></td>
<td align="left">即将将用户 {tpl:$userInfo.club_name/} 从 {tpl:$clubInfo.club_name/} 中移除！</td>
</tr>
<tr class="hover">
<td>请输入理由</td>
<td align="left"><input type="text" class="span4" name="reason"  id="reason" value="{tpl:$clubInfo.club_sign/}" size="50" /></td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="leave_club_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#leave_club_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.code != 200) {
				divBox.alertBox(jsonResponse.msg,function(){});
			} else {
				var message = jsonResponse.msg;
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=member.list&club_id=' + $('#club_id').val());}});
			}
		}
	};
	$('#leave_club_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
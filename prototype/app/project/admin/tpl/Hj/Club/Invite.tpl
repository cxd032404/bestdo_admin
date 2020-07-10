{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="club_update_form" name="club_update_form" action="{tpl:$this.sign/}&ac=club.update" method="post">
<input type="hidden" name="club_id" id="club_id" value="{tpl:$clubInfo.club_id/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>俱乐部名称</td>
<td align="left"><input name="club_name" type="text" class="span2" id="club_name" value="{tpl:$clubInfo.club_name/}" size="50" /></td>
</tr>
<tr class="hover">
	<td>姓名</td>
<td align="left"><input type="text" class="span4" name="user_name"  id="user_name" value="" size="50" onchange="getUser()"/></td>
</tr>

	<tr class="hover"><td>用户列表</td>
	<td align="left">	<select name="user_id"  id="user_id" size="1">
			{tpl:loop $companyList  $company_info}
			<option value="0">请搜索</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="club_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#club_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '俱乐部名称不能为空，请修正后再次提交';
				errors[3] = '俱乐部标识不能为空，请修正后再次提交';
				errors[4] = '俱乐部标识'+$('#club_sign').val()+'已重复，请修正后再次提交';
				errors[8] = '尚未绑定管理员用户，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改俱乐部成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + jsonResponse.company_id);}});
			}
		}
	};
	$('#club_update_form').ajaxForm(options);
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
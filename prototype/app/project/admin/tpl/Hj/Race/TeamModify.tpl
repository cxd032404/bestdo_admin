{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="team_update_form" name="team_update_form" action="{tpl:$this.sign/}&ac=member.update" method="post">
	<input type="hidden" name="race_id" id="race_id" value="{tpl:$TeamInfo.team_id/}" />
	<input type="hidden" name="id" id="id" value="{tpl:$TeamInfo.team_id/}" />
<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>队伍名称</td>
<td align="left"><input name="name" type="text" class="span2" id="name" value="{tpl:$TeamInfo.team_name/}" size="50" /></td>
</tr>
<tr class="hover">
<td>分组</td>
<td align="left">
<select name="group_id" id="group_id" size="1" class="span2">
	{tpl:loop $maxGroup $group $name}
	<option value="{tpl:$group/}" {tpl:if($group==$TeamInfo.group_id)}selected="selected"{/tpl:if}>{tpl:$name/}</option>
	{/tpl:loop}
</select>
</td>
</tr>
<tr class="hover">
	<td>种子</td>
	<td align="left">
		<select name="seed" id="seed" size="1" class="span2">
			{tpl:loop $maxSeed $seed $name}
			<option value="{tpl:$seed/}" {tpl:if($seed==$TeamInfo.seed)}selected="selected"{/tpl:if}>{tpl:$name/}</option>
			{/tpl:loop}
		</select>
	</td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="team_update_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#team_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '队伍名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改队伍成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&ac=member.list&race_id=' + $('#race_id').val());}});
			}
		}
	};
	$('#team_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="athlete_update_form" name="athlete_update_form" action="{tpl:$this.sign/}&ac=member.update" method="post">
	<input type="hidden" name="race_id" id="race_id" value="{tpl:$AthleteInfo.race_id/}" />
	<input type="hidden" name="id" id="id" value="{tpl:$AthleteInfo.athlete_id/}" />
<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>选手姓名</td>
<td align="left"><input name="name" type="text" class="span2" id="name" value="{tpl:$AthleteInfo.athlete_name/}" size="50" /></td>
</tr>
<tr class="hover">
<td>分组</td>
<td align="left">
<select name="group_id" id="group_id" size="1" class="span2">
	{tpl:loop $maxGroup $group $name}
	<option value="{tpl:$group/}" {tpl:if($group==$AthleteInfo.group_id)}selected="selected"{/tpl:if}>{tpl:$name/}</option>
	{/tpl:loop}
</select>
</td>
</tr>
<tr class="hover">
	<td>种子</td>
	<td align="left">
		<select name="seed" id="seed" size="1" class="span2">
			{tpl:loop $maxSeed $seed $name}
			<option value="{tpl:$seed/}" {tpl:if($seed==$AthleteInfo.seed)}selected="selected"{/tpl:if}>{tpl:$name/}</option>
			{/tpl:loop}
		</select>
	</td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="athlete_update_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#athlete_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '选手姓名不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改选手成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&ac=member.list&race_id=' + $('#race_id').val());}});
			}
		}
	};
	$('#athlete_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="race_update_form" name="race_update_form" action="{tpl:$this.sign/}&ac=race.update" method="post">
<input type="hidden" name="race_id" value="{tpl:$RaceInfo.race_id/}" />
<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>赛事名称</td>
<td align="left"><input name="race_name" type="text" class="span2" id="race_name" value="{tpl:$RaceInfo.race_name/}" size="50" /></td>
</tr>
<tr class="hover">
<td>赛事类型</td>
<td align="left">
<select name="race_type" size="1" class="span2">
	{tpl:loop $RaceTypeList $RaceType $typeName}
	<option value="{tpl:$RaceType/}" {tpl:if($SpeedDisplayType==$RaceType.race_type)}selected="selected"{/tpl:if}>{tpl:$typeName/}</option>
	{/tpl:loop}
</select>
</td>
</tr>
<tr class="hover">
	<td>选手类型</td>
	<td align="left">
		<select name="team" size="1" class="span2">
			<option value="0" {tpl:if(0==$RaceInfo.team)}selected="selected"{/tpl:if}>个人</option>
			<option value="1" {tpl:if(1==$RaceInfo.team)}selected="selected"{/tpl:if}>团队</option>
		</select>
	</td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="race_update_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#race_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '赛事名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改赛事成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#race_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
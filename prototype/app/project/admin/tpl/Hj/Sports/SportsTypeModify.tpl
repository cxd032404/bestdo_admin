{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="sports_type_update_form" name="sports_type_update_form" action="{tpl:$this.sign/}&ac=sports.type.update" method="post">
<input type="hidden" name="SportsTypeId" value="{tpl:$SportsTypeInfo.SportsTypeId/}" />
<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>运动类型名称</td>
<td align="left"><input name="SportsTypeName" type="text" class="span2" id="SportsTypeName" value="{tpl:$SportsTypeInfo.SportsTypeName/}" size="50" /></td>
</tr>
<tr class="hover">
<td>速度显示单位</td>
<td align="left">
<select name="SpeedDisplayType" size="1" class="span2">
	<option value="0" {tpl:if(0==$SportsTypeInfo.SpeedDisplayType)}selected="selected"{/tpl:if}>不显示</option>
	{tpl:loop $SpeedDisplayTypeList $SpeedDisplayType}
	<option value="{tpl:$SpeedDisplayType/}" {tpl:if($SpeedDisplayType==$SportsTypeInfo.SpeedDisplayType)}selected="selected"{/tpl:if}>{tpl:$SpeedDisplayType/}</option>
	{/tpl:loop}
</select>
</td>
</tr>
<tr class="hover">
	<td>决胜方式</td>
	<td align="left">
		<select name="winBy" size="1" class="span2">
			{tpl:loop $winBy $winByType $winByName}
			<option value="{tpl:$winByType/}" {tpl:if($winByType==$SportsTypeInfo.winBy)}selected="selected"{/tpl:if}>{tpl:$winByName/}</option>
			{/tpl:loop}
		</select>
	</td>
</tr>
	<tr class="hover">
		<td>取胜条件</td>
		<td align="left">
			<select name="winWith" size="1" class="span2">
				{tpl:loop $winWith $winWithType $winWithName}
				<option value="{tpl:$winWithType/}" {tpl:if($winWithType==$SportsTypeInfo.winWith)}selected="selected"{/tpl:if}>{tpl:$winWithName/}</option>
				{/tpl:loop}
			</select>
		</td>
	</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="sports_type_update_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#sports_type_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '运动类型名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改运动类型成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#sports_type_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
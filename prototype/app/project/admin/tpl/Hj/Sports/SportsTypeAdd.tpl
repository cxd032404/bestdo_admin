{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="sports_type_add_form" name="sports_type_add_form" action="{tpl:$this.sign/}&ac=sports.type.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>运动类型名称</td>
	<td align="left"><input type="text" class="span2" name="SportsTypeName"  id="SportsTypeName" value="" size="50" /></td>
</tr>
<td>速度显示单位</td>
<td align="left">
	<select name="SpeedDisplayType" size="1" class="span2">
		<option value="0" {tpl:if(0==$SportsTypeInfo.SpeedDisplayType)}selected="selected"{/tpl:if}>不显示</option>
		{tpl:loop $SpeedDisplayTypeList $SpeedDisplayType}
		<option value="{tpl:$SpeedDisplayType/}">{tpl:$SpeedDisplayType/}</option>
		{/tpl:loop}
	</select>
</td>
</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="app_add_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#app_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '运动类型名称不能为空，请修正后再次提交';
				errors[2] = '运动类型不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加运动类型成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#sports_type_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
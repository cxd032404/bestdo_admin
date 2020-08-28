{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="race_add_form" name="race_add_form" action="{tpl:$this.sign/}&ac=race.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>运动类型名称</td>
	<td align="left"><input type="text" class="span2" name="race_name"  id="race_name" value="" size="50" /></td>
</tr>
<tr class="hover">
<td>赛事类型</td>
<td align="left">
	<select name="race_type" size="1" class="span2">
		{tpl:loop $RaceTypeList $RaceType $typeName}
		<option value="{tpl:$RaceType/}">{tpl:$typeName/}</option>
		{/tpl:loop}
	</select>
</td>
</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="app_add_submit" class="pb_btn_dark_1">提交</button></td>
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
				errors[1] = '赛事名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加赛事成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#race_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="section_add_form" name="section_add_form" action="{tpl:$this.sign/}&ac=section.insert" method="post">
	<input type="hidden" class="span2" name="SportsTypeId" id="SportsTypeId"  value="{tpl:$SportsTypeId/}" size="50" />
	<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>名称</td>
	<td align="left"><input type="text" class="span2" name="name"  id="name" value="" size="50" /></td>
</tr>
<tr class="hover">
	<td>时间</td>
<td align="left"><input type="text" class="span2" name="time"  id="time" value="10" size="50" />分钟</td>
</tr>
<tr class="hover">
	<td>附加分段</td>
	<td align="left">
		<select name="additional" size="1" class="span2">
			<option value="0">否</option>
			<option value="1">是</option>
		</select>
	</td>
</tr>
<tr class="hover">
	<td>是否决胜</td>
<td align="left">
	<select name="win" size="1" class="span2">
		<option value="0">否</option>
		<option value="1">是</option>
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
				errors[1] = '分段名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加分段成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+ '&ac=section&SportsTypeId=' + $('#SportsTypeId').val());}});
			}
		}
	};
	$('#section_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
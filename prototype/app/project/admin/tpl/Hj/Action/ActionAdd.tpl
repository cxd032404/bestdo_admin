{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="action_add_form" name="action_add_form" action="{tpl:$this.sign/}&ac=action.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>动作标识</td>
	<td align="left"><input type="text" class="span2" name="Action"  id="Action" value="" size="50" /></td>
</tr>
<td>动作名称</td>
	<td align="left"><input type="text" class="span2" name="ActionName"  id="ActionName" value="" size="50" /></td>
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
				errors[1] = '动作名称不能为空，请修正后再次提交';
				errors[2] = '动作标识不能为空，请修正后再次提交';
				errors[3] = '动作标识已经被占用，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加动作成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#action_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="update_log_add_form" name="update_log_add_form" action="{tpl:$this.sign/}&ac=update.log.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
	<td>更新日期</td>
	<td align="left"><input type="text" name="UpdateDate" value="{tpl:$UpdateDate/}" class="input-medium"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" ></td>
</tr>
	<tr class="hover">
		<td>更新类型</td>
		<td align="left">
			<select name="LogType" class = "span2" size="1">
				{tpl:loop $UpdateLogTypeList $LogType $LogTypeName}
				<option value="{tpl:$LogType/}">{tpl:$LogTypeName/}</option>
				{/tpl:loop}
			</select>
		</td>
	</tr>
	<tr class="hover">
<td>更新内容</td>
	<td align="left"><textarea name="comment" id="comment" class="span5" rows="4"></textarea></td>
</tr>

	<tr class="noborder"><td></td>
<td><button type="submit" id="app_add_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#app_add_submit').click(function(){
	var options = {
		dataOS:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '更新内容，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加更新记录成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#update_log_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
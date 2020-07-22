{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="credit_add_form" name="credit_add_form" action="{tpl:$this.sign/}&ac=credit.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>积分类目名称</td>
	<td align="left"><input type="text" class="span2" name="CreditName"  id="CreditName" value="" size="50" /></td>
</tr>
<tr class="hover">
<td>消费比例(分)</td>
<td align="left"><input type="text" class="span1" name="CreditRate"  id="CreditRate" value="" size="50" /></td>
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
				errors[1] = '积分类目名称不能为空，请修正后再次提交';
				errors[2] = '积分类目不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加积分类目成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#credit_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
{tpl:tpl contentHeader/}

<fieldset>
	[ <a href="{tpl:$this.sign/}">返回</a> ]
</fieldset>
<div class="br_bottom"></div>
<form id="boutique_update_form" name="boutique_update_form" action="{tpl:$this.sign/}&ac=boutique.update" method="post">
<input type="hidden" name="company_id" value="{tpl:$companyInfo.company_id/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
	<tr class="hover">
		<td colspan="1">精品课对应文章列表</td>
	</tr>
		{tpl:loop $listList  $list_info}
	<tr class="noborder">
	<td><input type="checkbox" name="boutique[{tpl:$list_info.list_id/}]" value="{tpl:$list_info.list_id/}" {tpl:if($list_info.checked == 1)}checked{/tpl:if} />{tpl:$list_info.list_name/}
	</td>
			{/tpl:loop}

<tr class="noborder">
<td><button type="submit" id="boutique_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#boutique_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '至少要选定1个列表，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改企业成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#boutique_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
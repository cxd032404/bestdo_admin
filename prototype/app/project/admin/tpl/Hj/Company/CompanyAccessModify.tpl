{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="company_access_update_form" name="company_access_update_form" action="{tpl:$this.sign/}&ac=company.access.update" method="post">
<input type="hidden" name="company_id" value="{tpl:$companyInfo.company_id/}" />

	<table width="99%" align="center" class="table table-bordered table-striped" >
		<tr class="hover">
			<td colspan="1">{tpl:$companyInfo.CompanyName/}对应权限列表</td>
		</tr>
		{tpl:loop $accessList $app_id $access_info}
		<tr class="noborder">
			<td><input type="checkbox" name="access[{tpl:$app_id/}]" value="1" {tpl:if($access_info.access == 1)}checked{/tpl:if} />{tpl:$access_info.name/}
			</td>
			{/tpl:loop}
		<tr class="noborder">
			<td><button type="submit" id="company_access_update_submit" class="pb_btn_dark_1">提交</button></td>
		</tr>
	</table>
</form>

<script type="text/javascript">
$('#company_access_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改权限成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#company_access_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
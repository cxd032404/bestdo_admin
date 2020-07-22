{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="company_user_upload_form" name="company_user_upload_form" action="{tpl:$this.sign/}&ac=company.user.upload" method="post">
	<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1">
			{tpl:loop $companyList  $companyInfo}
			<option value="{tpl:$companyInfo.company_id/}">{tpl:$companyInfo.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover"><td>验证方式</td>
	<td align="left">
			{tpl:loop $companyUserAuthType $authType $authTypeName}
				<input name="auth_type" id="auth_type" type="radio" value="{tpl:$authType/}" /> {tpl:$authTypeName/}
			{/tpl:loop}
		</td>
</tr>
<td>文件上传：<p>姓名,验证方式</td>
<td align="left"><input name="upload_txt[1]" type="file" id="upload_txt[1]" />

</td>
</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="company_user_upload_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#company_user_upload_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '必须选择一个验证方式，以组合证明用户的唯一性，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '上传完毕 总计成功：'+jsonResponse.result.success + '人，失败：'+ jsonResponse.result.error+ '人，已经存在：'+jsonResponse.result.exist +'人，加入索引：'+jsonResponse.result.index +'人';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=company.user.list&company_id=' + $('#company_id').val());}});
			}
		}
	};
	$('#company_user_upload_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
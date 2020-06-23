{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="company_add_form" name="company_add_form" action="{tpl:$this.sign/}&ac=company.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>企业名称</td>
	<td align="left"><input type="text" class="span2" name="company_name"  id="company_name" value="" size="50" /></td>
</tr>
<tr class="hover"><td>上级企业</td>
	<td align="left">	<select name="parent_id"  id="parent_id" size="1">
			<option value="0">无上级</option>
			{tpl:loop $companyList  $companyInfo}
			<option value="{tpl:$companyInfo.company_id/}">{tpl:$companyInfo.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover"><td>是否显示</td>
	<td align="left">
	<input type="radio" name="display" id="display" value="1">显示
	<input type="radio" name="display" id="display" value="0" checked >隐藏
</td>
</tr>
<tr class="hover">
<td>图片上传：</td>
	<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" /></td>
</tr>
	<tr class="noborder">
<td>每日步数</td>
	<td align="left"><input type="text" class="span2" name="detail[daily_step]"  id="detail[daily_step]" value="6000" size="50" /></td>
</tr>
	<tr class="noborder"><td>
<td><button type="submit" id="company_add_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#company_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '企业名称不能为空，请修正后再次提交';
				errors[2] = '没有上传图片或上传失败，请修正后再次提交';
				errors[3] = '企业名称'+$('#company_name').val()+'已重复，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加企业成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#company_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
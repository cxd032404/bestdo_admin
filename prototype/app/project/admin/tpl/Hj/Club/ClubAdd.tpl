{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="club_add_form" name="club_add_form" action="{tpl:$this.sign/}&ac=club.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>俱乐部名称</td>
	<td align="left"><input type="text" class="span3" name="club_name"  id="club_name" value="" size="50" /></td>
</tr>
<td>俱乐部标识</td>
<td align="left"><input type="text" class="span2" name="club_sign"  id="club_sign" value="" size="50" /></td>
</tr>
<td>人数限制</td>
<td align="left"><input type="text" class="span2" name="member_limit"  id="member_limit" value="100" size="50" /></td>
</tr>
<td>当前允许加入</td>
<td align="left">
	允许: <input type="radio" name="allow_enter" id="allow_enter" value="1" checked/>
	拒绝: <input type="radio" name="allow_enter" id="allow_enter" value="0" /></td>
</tr>


	<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1">
			{tpl:loop $companyList  $companyInfo}
			<option value="{tpl:$companyInfo.company_id/}">{tpl:$companyInfo.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover">
	<td>图片上传：</td>
	<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" /></td>
</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="club_add_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#club_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '俱乐部名称不能为空，请修正后再次提交';
				errors[2] = '没有上传图片或上传失败，请修正后再次提交';
				errors[3] = '俱乐部标识不能为空，请修正后再次提交';
				errors[4] = '俱乐部标识'+$('#club_sign').val()+'已重复，请修正后再次提交';
				errors[8] = '尚未绑定管理员用户，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加俱乐部成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + $('#company_id').val());}});
			}
		}
	};
	$('#club_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
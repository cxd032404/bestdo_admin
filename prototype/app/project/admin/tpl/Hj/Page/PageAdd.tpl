{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="page_add_form" name="page_add_form" action="{tpl:$this.sign/}&ac=page.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>页面名称</td>
	<td align="left"><input type="text" class="span2" name="page_name"  id="page_name" value="" size="50" /></td>
</tr>
<td>页面url</td>
	<td align="left"><input type="text" class="span4" name="page_url"  id="page_url" value="" size="50" /></td>
</tr>
<td>页面标识</td>
<td align="left"><input type="text" class="span4" name="page_sign"  id="page_sign" value="" size="50" /></td>
</tr>
<tr class="hover"><td>需要登录</td>
	<td align="left">	<select name="need_login"  id="need_login" size="1">
			<option value="0">不需要</option>
			<option value="1">需要</option>
		</select></td>
</tr>
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1">
			{tpl:loop $companyList  $companyInfo}
			<option value="{tpl:$companyInfo.company_id/}">{tpl:$companyInfo.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
	<td>页面参数<br>string数组<br>int整数<br>string字符串
	<td align="left"><input type="text" class="span4" name="detail[params]"  id="detail[params]" value="" size="50" /></td>
	</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="page_add_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#page_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '页面名称不能为空，请修正后再次提交';
				errors[2] = '页面url不能为空，请修正后再次提交';
				errors[3] = '页面标识不能为空，请修正后再次提交';
				errors[4] = '页面标识'+$('#page_sign').val()+'已重复，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加页面成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=page.detail&page_id=' + jsonResponse.page_id);}});
			}
		}
	};
	$('#page_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
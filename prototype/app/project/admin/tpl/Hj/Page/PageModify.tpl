{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="page_update_form" name="page_update_form" action="{tpl:$this.sign/}&ac=page.update" method="post">
<input type="hidden" name="page_id" value="{tpl:$pageInfo.page_id/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>页面名称</td>
<td align="left"><input name="page_name" type="text" class="span2" id="page_name" value="{tpl:$pageInfo.page_name/}" size="50" /></td>
</tr>
<td>页面url</td>
	<td align="left"><input type="text" class="span4" name="page_url"  id="page_url" value="{tpl:$pageInfo.page_url/}" size="50" /></td>
</tr>
<td>页面标识</td>
<td align="left"><input type="text" class="span4" name="page_sign"  id="page_sign" value="{tpl:$pageInfo.page_sign/}" size="50" /></td>
</tr>
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1">
			{tpl:loop $companyList  $company_info}
			<option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$pageInfo.company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<td>页面参数<br>string数组<br>int整数<br>string字符串
<td align="left"><input type="text" class="span4" name="detail[params]"  id="detail[params]" value="{tpl:$pageInfo.detail.params/}" size="50" /></td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="page_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#page_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
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
				var message = '修改页面成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + $('#company_id').val());}});
			}
		}
	};
	$('#page_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
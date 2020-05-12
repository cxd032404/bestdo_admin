{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="list_update_form" name="list_update_form" action="{tpl:$this.sign/}&ac=list.update" method="post">
<input type="hidden" name="list_id" value="{tpl:$listInfo.list_id/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>列表名称</td>
<td align="left"><input name="list_name" type="text" class="span2" id="list_name" value="{tpl:$listInfo.list_name/}" size="50" /></td>
</tr>
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1">
			{tpl:loop $companyList  $company_info}
			<option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$listInfo.company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover"><td>列表分类</td>
	<td align="left">
		<select name="list_type"  id="list_type" size="1">
			{tpl:loop $listTypeList  $type $type_info}
			<option value="{tpl:$type/}"{tpl:if($type==$listInfo.list_type)}selected="selected"{/tpl:if} >{tpl:$type_info.name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
	<tr class="hover"><td colspan = 2>备注</td></tr>
	<tr class="hover"><td colspan = 2>
			<textarea style="width:500px; height:200px" name="detail[comment]" id="detail[comment]" >{tpl:$listInfo.detail.comment/}</textarea>
		</td>
	</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="list_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#list_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '列表名称不能为空，请修正后再次提交';
				errors[2] = '列表名称'+$('#list_name').val()+'已重复，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改列表成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + $('#company_id').val()+ '&list_type=' + $('#list_type').val());}});
			}
		}
	};
	$('#list_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
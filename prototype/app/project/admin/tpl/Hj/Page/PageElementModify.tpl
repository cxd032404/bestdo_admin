{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="page_element_modify_form" name="page_element_modify_form" action="{tpl:$this.sign/}&ac=page.element.update" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<input type="hidden" id="element_id" name="element_id" value="{tpl:$elementInfo.element_id/}" />
<input type="hidden" id="page_id" name="page_id" value="{tpl:$elementInfo.page_id/}" />
<tr class="hover">
<td>元素名称</td>
	<td align="left"><input type="text" class="span2" name="element_name"  id="element_name" value="{tpl:$elementInfo.element_name/}" size="50" /></td>
</tr>
<td>元素标识</td>
	<td align="left"><input type="text" class="span2" name="element_sign"  id="element_sign" value="{tpl:$elementInfo.element_sign/}" size="50" /></td>
</tr>
<tr class="hover"><td>元素类型</td>
	<td align="left">	<select name="element_type"  id="element_type" size="1">
			{tpl:loop $elementTypeList  $elementTypeInfo}
			<option value="{tpl:$elementTypeInfo.element_type/}" {tpl:if($elementInfo.element_type==$elementTypeInfo.element_type)}selected="selected"{/tpl:if}>{tpl:$elementTypeInfo.element_type_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="page_element_modify_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#page_element_modify_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '页面元素名称不能为空，请修正后再次提交';
				errors[2] = '页面元素标识不能为空，请修正后再次提交';
				errors[3] = '页面元素标识'+ $('#element_sign').val() +'已重复，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改页面元素成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=page.detail&page_id=' + $('#page_id').val());}});
			}
		}
	};
	$('#page_element_modify_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
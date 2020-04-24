{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="page_element_detail_update_form" name="page_element_detail_update_form" action="{tpl:$this.sign/}&ac=page.element.detail.update" method="post">
<input type="hidden" id="element_type" name="element_type" value="{tpl:$elementInfo.element_type/}" />
<input type="hidden" name="element_id" value="{tpl:$elementInfo.element_id/}" />
<fieldset>
	[ <a href="{tpl:$this.sign/}&ac=page.detail&page_id={tpl:$elementInfo.page_id/}">返回</a>]
</fieldset>
<fieldset><legend>页面元素详情</legend>

		<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr class="hover">
<td>元素名称</td>
<td align="left">{tpl:$elementInfo.element_name/}</td>
</tr>
<tr class="hover">
<td>元素标识</td>
<td align="left">{tpl:$elementInfo.element_sign/}</td>
</tr>
<tr class="hover">
<td>元素标识</td>
<td align="left">{tpl:$elementTypeInfo.element_type_name/}</td>
</tr>
<tr class="hover"><td colspan="2">元素详情</td></tr>
<tr class="hover">
<td>图片上传：</td>
	<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" />
    {tpl:if($elementInfo.detail.img_url!="")}
    已选图片:<img src="{tpl:$elementInfo.detail.img_url/}" width="30px;" height="30px;"/>
    <br>       
    {/tpl:if}
    </td>
</tr>
<tr class="hover">
<td>跳转路径</td>
	<td align="left"><input type="text" class="span2" name="detail[img_jump_url]"  id="detail[img_jump_url]" value="{tpl:$elementInfo.detail.img_jump_url/}" size="50" /></td>
</tr>

<tr class="noborder"><td></td>
<td><button type="submit" id="page_element_detail_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#page_element_detail_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '页面元素名称不能为空，请修正后再次提交';
				errors[2] = '必须上传图片，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改页面元素成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=page.detail&page_id=' + $('#page_id').val());}});
			}
		}
	};
	$('#page_element_detail_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
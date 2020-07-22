{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="source_modify_form" name="source_modify_form" action="{tpl:$this.sign/}&ac=source.detail.update" method="post">
<input type="hidden" name="config_sign" id="config_sign" value="{tpl:$config_sign/}" />
<input type="hidden" name="pos" id="pos" value="{tpl:$pos/}" />
	<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>图片上传：</td>
	<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" />
		{tpl:if($sourceInfo.img_url!="")}
		已选图片:<img src="{tpl:$sourceInfo.img_url/}" width="30px;" height="30px;"/>
	<br>
		{/tpl:if}
    </td>
</tr>
<tr class="hover">
	<td>文字</td>
	<td align="left"><input type="text" class="span2" name="detail[text]"  id="detail[text]" value="{tpl:$sourceInfo.text/}" size="50" /></td>
</tr>
<tr class="hover">
<td>跳转路径</td>
	<td align="left"><input type="text" class="span4" name="detail[img_jump_url]"  id="detail[img_jump_url]" value="{tpl:$sourceInfo.img_jump_url/}" size="50" /></td>
</tr>
	<tr class="hover">
		<td>标签</td>
		<td align="left"><input type="text" class="span2" name="detail[title]"  id="detail[title]" value="{tpl:$sourceInfo.title/}" size="50" /></td>
	</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="source_modify_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#source_modify_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[2] = '必须上传图片，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改图片成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&&ac=source.detail&config_sign=' + $('#config_sign').val());}});
			}
		}
	};
	$('#source_modify_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
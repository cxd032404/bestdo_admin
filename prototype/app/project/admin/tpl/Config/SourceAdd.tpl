{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="source_add_form" name="source_add_form" action="{tpl:$this.sign/}&ac=source.detail.insert" method="post">
<input type="hidden" name="config_sign" id="config_sign" value="{tpl:$configInfo.config_sign/}" />
<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>图片上传：</td>
	<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" />
    </td>
</tr>
<tr class="hover">
	<td>文字</td>
	<td align="left"><input type="text" class="span2" name="detail[text]"  id="detail[text]" value="" size="50" /></td>
</tr>
<td>跳转路径</td>
	<td align="left"><input type="text" class="span4" name="detail[img_jump_url]"  id="detail[img_jump_url]" value="" size="50" /></td>
</tr>
	<td>标签</td>
	<td align="left"><input type="text" class="span2" name="detail[title]"  id="detail[title]" value="" size="50" /></td>
	</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="source_add_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#source_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '必须上传图片，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加图片成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=source.detail&config_sign=' + $('#config_sign').val());}});
			}
		}
	};
	$('#source_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
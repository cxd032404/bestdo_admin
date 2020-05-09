{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="post_form" name="post_form" enctype="multipart/form-data" action="{tpl:$postUrl/}" method="post">
<input type="hidden" name="list_id" id="list_id" value="{tpl:$listInfo.list_id/}" />
<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>图片上传：</td>
	<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" />
    </td>
</tr>
<tr class="hover">
	<td>图片上传：</td>
	<td align="left"><input name="upload_img[2]" type="file" id="upload_img[2]" />
	</td>
</tr>
<tr class="hover">
	<td>文本：</td>
	<td align="left"><input name="upload_txt[1]" type="file" id="upload_txt[1]" />
	</td>
</tr>
<tr class="hover"><td colspan = 2>文本</td></tr>
<tr class="hover"><td colspan = 2>
		<textarea style="width:500px; height:200px" name="detail[comment]" id="detail[comment]" ></textarea>
	</td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="post_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
	$('#post_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {},
			success:function(jsonResponse) {
				alert(jsonResponse.success);
				if (jsonResponse.success == "false") {
					//divBox.alertBox(jsonResponse.msg,function(){});
				} else {
					var message = '发布成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+'&ac=list&list_id='+$('#list_id').val());}});
				}
			}
		};
		$('#post_form').ajaxForm(options);
	});
</script>
{tpl:tpl contentFooter/}
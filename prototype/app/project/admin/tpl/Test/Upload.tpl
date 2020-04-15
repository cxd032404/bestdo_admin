{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="upload_form" name="upload_form" action="{tpl:$this.sign/}&ac=upload" method="post">
	<input type="hidden" name="SportsTypeId" value="1" />
	<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
		<tr class="hover"><td>图片上传1：</td>
			<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" /></td>
		</tr>

		<tr class="hover"><td>图片上传2：</td>
			<td align="left"><input name="upload_img[2]" type="file" id="upload_img[2]" /></td>
		</tr>

		<tr class="hover"><td>图片上传3：</td>
			<td align="left"><input name="upload_img[3]" type="file" id="upload_img[3]" /></td>
		</tr>

		<tr class="noborder"><td></td>
			<td><button type="submit" id="upload_submit">提交</button></td>
		</tr>
	</table>
</form>

<script type="text/javascript">
	$('#upload_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {},
			success:function(jsonResponse) {
				if (jsonResponse.errno) {
					var errors = [];

					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '导入完毕，上传图片路径<br>'+jsonResponse.url;
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});				}
			}
		};
		$('#upload_form').ajaxForm(options);
	});
</script>
{tpl:tpl contentFooter/}







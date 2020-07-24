{tpl:tpl contentHeader/}
<div class="br_bottom"></div>

<form id="upload_form" name="upload_form" action="{tpl:$this.sign/}&ac=upload" method="post">
	<table width="99%" align="center" class="table table-bordered table-striped" >
		<tr class="hover"><td>excel上传1：</td>
			<td align="left"><input name="upload_excel[1]" type="file" id="upload_excel[1]" /></td>
		</tr>
		<tr class="noborder"><td></td>
			<td><button type="submit" id="upload_submit" class="pb_btn_dark_1">提交</button></td>
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

<?php Third_ckeditor_ckeditor::render("comment")?>


<?php Third_ckeditor_ckeditor::render("comment2")?>
{tpl:tpl contentFooter/}







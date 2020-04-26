{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<script src="js/ckeditor5/ckeditor.js"></script>

<form id="upload_form" name="upload_form" action="{tpl:$this.sign/}&ac=send																											" method="post">
	<input type="hidden" name="SportsTypeId" value="1" />
	<table width="99%" align="center" class="table table-bordered table-striped" >
		<tr class="hover"><td>手机号码：</td>
			<td align="left"><input name="mobile" type="text" id="mobile" /></td>
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
					var message = '发送完毕，成功发送<br>'+jsonResponse.success+'条';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});				}
			}
		};
		$('#upload_form').ajaxForm(options);
	});
</script>
{tpl:tpl contentFooter/}







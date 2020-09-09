{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="company_user_upload_form" name="company_user_upload_form" action="{tpl:$this.sign/}&ac=race.member.upload&race_id={tpl:$race_id/}&is_team={tpl:$is_team/}" method="post" enctype="multipart/form-data">
	<table width="99%" align="center" class="table table-bordered table-striped">

<td>文件上传：</td>
<td align="left"><input name="upload_txt[1]" type="file" id="upload_txt[1]" />

</td>
</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="company_user_upload_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#company_user_upload_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				if(jsonResponse.msg)
					{
						var message = jsonResponse.msg
					}else
						{
							if(jsonResponse.errno != 0 )
							{
								var message = '上传完毕,'+jsonResponse.errno+'条修改失败'
							}else
							{
								var message = '上传完毕'
							}
						}

				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}')}});
			}
		}
	};
	$('#company_user_upload_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
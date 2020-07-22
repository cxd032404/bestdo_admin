{tpl:tpl contentHeader/}
<script src="js/ckeditor/ckeditor.js"></script>

<div class="br_bottom"></div>
<form id="protocal_update_form" name="protocal_update_form" action="{tpl:$this.sign/}&ac=protocal.update" method="post">
<input type="hidden" name="company_id" value="{tpl:$companyInfo.company_id/}" />
	<input type="hidden" name="type" value="{tpl:$type/}" />
<table width="99%" align="center" class="table table-bordered table-striped" >
	<tr class="hover"><td colspan = 2>{tpl:$companyInfo.company_name/}</td></tr>
	<tr class="hover"><td colspan = 2>协议内容</td></tr>
	<tr class="hover"><td colspan = 2>
			<textarea style="width:500px; height:200px" name="content" id="content" >{tpl:$protocal.content/}</textarea>
		</td>
	</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="protocal_update_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>
<?php Third_ckeditor_ckeditor::render("content")?>
<script type="text/javascript">
$('#protocal_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改协议成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#protocal_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
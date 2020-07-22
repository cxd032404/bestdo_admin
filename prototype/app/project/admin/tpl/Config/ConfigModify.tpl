{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="config_update_form" name="config_update_form" action="{tpl:$this.sign/}&ac=config.update" method="post">
<input type="hidden" name="config_sign" value="{tpl:$configInfo.config_sign/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>配置名称</td>
<td align="left"><input name="config_name" type="text" class="span2" id="config_name" value="{tpl:$configInfo.config_name/}" size="50" /></td>
</tr>
<td>配置类型</td>
<td align="left">{tpl:$configInfo.config_type_name/}</td>
</tr>
<td>内容</td>
	<td align="left"><textarea style="width:450px; height:200px" name="content" id="content" >{tpl:$configInfo.content/}</textarea>
</td>
</tr>
<td colspan = 2><button type="submit" id="config_update_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#config_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '配置名称不能为空，请修正后再次提交';
				errors[3] = '配置名称'+$('#config_name').val()+'已重复，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改配置成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#config_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
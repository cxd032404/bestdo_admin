{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="config_add_form" name="config_add_form" action="{tpl:$this.sign/}&ac=config.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>配置名称</td>
	<td align="left"><input type="text" class="span2" name="config_name"  id="config_name" value="" size="50" /></td>
</tr>
<td>配置标示</td>
<td align="left"><input type="text" class="span2" name="config_sign"  id="config_sign" value="" size="50" /></td>
</tr>
<tr class="hover"><td>配置类型</td>
	<td align="left">        <select name="config_type"  id="config_type" size="1">
			{tpl:loop $configTypeList  $type $name}
			<option value="{tpl:$type/}"{tpl:if($type==$config_type)}selected="selected"{/tpl:if} >{tpl:$name/}</option>
			{/tpl:loop}
		</select>
	</td>
</tr>
	<tr class="noborder"><td>
<td><button type="submit" id="config_add_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#config_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '配置名称不能为空，请修正后再次提交';
				errors[3] = '配置名称 '+$('#config_name').val()+' 已重复，请修正后再次提交';
				errors[2] = '配置标示 '+$('#config_sign').val()+' 已重复，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加配置成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#config_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
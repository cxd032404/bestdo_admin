{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="race_update_form" name="race_update_form" action="{tpl:$this.sign/}&ac=place.update" method="post">
<input type="hidden" name="place_id" value="{tpl:$place_info.place_id/}" />
<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
	<td>场地名称</td>
	<td align="left"><input type="text" class="span2" name="place_name"  idid="place_name" value="{tpl:$place_info.place_name/}" size="50" /></td>
</tr>
	<tr class="hover">
		<td>赛事</td>
		<td align="left"><input type="text" class="span2" name="race_name"  id="race_name" value="{tpl:$race_name/}" size="50"  readonly /></td>
	</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="race_update_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#race_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '场馆名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改场馆成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#race_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
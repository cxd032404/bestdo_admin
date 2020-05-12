{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="vote_option_add_form" name="vote_option_add_form" action="{tpl:$this.sign/}&ac=vote.option.update" method="post">
<input type="hidden" name="vote_id" id="vote_id" value="{tpl:$voteInfo.vote_id/}" />
	<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>投票选项名称</td>
	<td align="left"><input type="text" class="span2" name="vote_option_name"  id="vote_option_name" value="" size="50" /></td>
</tr>
<tr class="hover">
<td>投票选项标识</td>
<td align="left"><input type="text" class="span2" name="vote_option_sign"  id="vote_option_sign" value="" size="50" /></td>
</tr>
<tr class="hover">
	<td rowspan="2">投票选项标识</td >
	<td align="left">
		<input type="radio" name="detail[source_from]" id="detail[source_from]" value="from_list" /> 来自列表：
		<select name="detail[list_id]"  id="detail[list_id]" size="1">
	{tpl:loop $listList  $list_info}
	<option value="{tpl:$list_info.list_id/}">{tpl:$list_info.list_name/}</option>
	{/tpl:loop}
</select>
	</td>
</tr>
<tr class="hover">
	<td align="left">
		<input type="radio" name="detail[source_from]" id="detail[source_from]" value="none" /> 无
	</td>
</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="vote_add_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#vote_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '投票选项名称不能为空，请修正后再次提交';
				errors[2] = '没有上传图片或上传失败，请修正后再次提交';
				errors[3] = '投票选项标识不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加投票选项成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=vote.detail&vote_id=' + $('#vote_id').val());}});
			}
		}
	};
	$('#vote_option_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="vote_add_form" name="vote_add_form" action="{tpl:$this.sign/}&ac=vote.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>投票名称</td>
	<td align="left"><input type="text" class="span2" name="vote_name"  id="vote_name" value="" size="50" /></td>
</tr>
<tr class="hover">
<td>投票时间</td>
	<th align="center" class="rowtip">
		<input type="text" name="start_time"  id="start_time" class="input-medium"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="end_time" id="end_time" class="input-medium"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></th></tr>
</tr>
	<td>投票标识</td>
<td align="left"><input type="text" class="span4" name="vote_sign"  id="vote_sign" value="" size="50" /></td>
</tr>
<tr class="hover"><td>属于活动</td>
	<td align="left">	<select name="activity_id"  id="activity_id" size="1">
			{tpl:loop $activityList  $activityInfo}
			<option value="{tpl:$activityInfo.activity_id/}">{tpl:$activityInfo.activity_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover">
	<td>图片上传：</td>
	<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" /></td>
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
				errors[1] = '投票名称不能为空，请修正后再次提交';
				errors[2] = '没有上传图片或上传失败，请修正后再次提交';
				errors[3] = '投票标识不能为空，请修正后再次提交';
				errors[4] = '投票标识'+$('#vote_sign').val()+'已重复，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加投票成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&activity_id=' + $('#activity_id').val());}});
			}
		}
	};
	$('#vote_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
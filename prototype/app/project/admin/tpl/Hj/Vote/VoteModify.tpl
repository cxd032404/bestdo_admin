{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="vote_update_form" name="vote_update_form" action="{tpl:$this.sign/}&ac=vote.update" method="post">
<input type="hidden" name="vote_id" value="{tpl:$voteInfo.vote_id/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>投票名称</td>
<td align="left"><input name="vote_name" type="text" class="span2" id="vote_name" value="{tpl:$voteInfo.vote_name/}" size="50" /></td>
</tr>
<td>投票时间</td>
<th align="center" class="rowtip">
	<input type="text" name="start_time"  id="start_time" class="input-medium" value="{tpl:$voteInfo.start_time/}"  onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="end_time" id="end_time" class="input-medium" value="{tpl:$voteInfo.end_time/}"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></th></tr>
</tr>
<td>投票标识</td>
<td align="left"><input type="text" class="span4" name="vote_sign"  id="vote_sign" value="{tpl:$voteInfo.vote_sign/}" size="50" /></td>
</tr>
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="activity_id"  id="activity_id" size="1">
			{tpl:loop $activityList  $activity_info}
			<option value="{tpl:$activity_info.activity_id/}"{tpl:if($activity_info.activity_id==$voteInfo.activity_id)}selected="selected"{/tpl:if} >{tpl:$activity_info.activity_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
	<tr class="hover"><td>上传图片</td>
		<td align="left">
			{tpl:if($voteInfo.icon!="")}
			已选图片:<img src="{tpl:$voteInfo.icon/}" width="30px;" height="30px;"/>
		<br>
			{/tpl:if}
			更改图片:<input name="upload_img[1]" type="file" class="span4" id="upload_img[1]"/>
		</td>
	</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="vote_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#vote_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '投票名称不能为空，请修正后再次提交';
				errors[3] = '投票标识不能为空，请修正后再次提交';
				errors[4] = '投票标识'+$('#vote_sign').val()+'已重复，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改投票成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&activity_id=' + jsonResponse.activity_id);}});
			}
		}
	};
	$('#vote_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
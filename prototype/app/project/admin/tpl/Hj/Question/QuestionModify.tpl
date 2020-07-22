{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="question_update_form" name="question_update_form" action="{tpl:$this.sign/}&ac=question.update" method="post">
<input type="hidden" name="question_id" id="question_id" value="{tpl:$questionInfo.question_id/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
	<td>提问</td>
	<td align="left"><textarea style="width:580px; height:200px" name="question"  id="question" value="" size="50" />{tpl:$questionInfo.question/}</textarea></td>
</tr>
<tr class="hover">
	<td>回答</td>
	<td align="left"><textarea style="width:580px; height:200px" name="answer"  id="answer" value="" size="50" />{tpl:$questionInfo.answer/}</textarea></td>
</tr>
<tr class="hover">
	<td>关键字</td>
	<td align="left"> "|"分割<br><input type="text" class="span4" name="detail[keywords]"  id="detail[keywords]" value="{tpl:$questionInfo.detail.keywords/}" size="50" /></td>
</tr>
<tr class="hover"><td>活动</td>
	<td align="left">	<select name="activity_id"  id="activity_id" size="1">
			{tpl:loop $activityList.ActivityList  $activity_info}
			<option value="{tpl:$activity_info.activity_id/}"{tpl:if($activity_info.activity_id==$questionInfo.activity_id)}selected="selected"{/tpl:if} >{tpl:$activity_info.activity_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="question_update_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#question_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '提问不能为空，请修正后再次提交';
				errors[3] = '回答不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改提问成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&activity_id=' + jsonResponse.activity_id);}});
			}
		}
	};
	$('#question_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
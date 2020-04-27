{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="activity_update_form" name="activity_update_form" action="{tpl:$this.sign/}&ac=activity.update" method="post">
<input type="hidden" name="activity_id" value="{tpl:$activityInfo.activity_id/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>页面名称</td>
<td align="left"><input name="activity_name" type="text" class="span2" id="activity_name" value="{tpl:$activityInfo.activity_name/}" size="50" /></td>
</tr>
<td>活动时间</td>
<th align="center" class="rowtip">
	<input type="text" name="start_time"  id="start_time" class="input-medium" value="{tpl:$activityInfo.start_time/}"  onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="end_time" id="end_time" class="input-medium" value="{tpl:$activityInfo.end_time/}"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></th></tr>
</tr>
<td>报名时间</td>
<th align="center" class="rowtip">
	<input type="text" name="apply_start_time "  id="apply_start_time " class="input-medium" value="{tpl:$activityInfo.apply_start_time/}"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="apply_end_time" id="apply_end_time" class="input-medium"  value="{tpl:$activityInfo.apply_end_time/}" onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></th></tr>
</tr>
<td>页面标识</td>
<td align="left"><input type="text" class="span4" name="activity_sign"  id="activity_sign" value="{tpl:$activityInfo.activity_sign/}" size="50" /></td>
</tr>
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1">
			{tpl:loop $companyList  $company_info}
			<option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$activityInfo.company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="activity_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#activity_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '页面名称不能为空，请修正后再次提交';
				errors[3] = '页面标识不能为空，请修正后再次提交';
				errors[4] = '页面标识'+$('#activity_sign').val()+'已重复，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改页面成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + jsonResponse.company_id);}});
			}
		}
	};
	$('#activity_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
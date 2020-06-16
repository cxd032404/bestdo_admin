{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="activity_update_form" name="activity_update_form" action="{tpl:$this.sign/}&ac=activity.update" method="post">
<input type="hidden" name="activity_id" value="{tpl:$activityInfo.activity_id/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>活动名称</td>
<td align="left"><input name="activity_name" type="text" class="span2" id="activity_name" value="{tpl:$activityInfo.activity_name/}" size="50" /></td>
</tr>
<tr class="hover">
	<td>活动时间</td>
<th align="center" class="rowtip">
	<input type="text" name="start_time"  id="start_time" class="input-medium" value="{tpl:$activityInfo.start_time/}"  onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="end_time" id="end_time" class="input-medium" value="{tpl:$activityInfo.end_time/}"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></th></tr>
</tr>
<tr class="hover">
	<td>报名时间</td>
<th align="center" class="rowtip">
	<input type="text" name="apply_start_time "  id="apply_start_time " class="input-medium" value="{tpl:$activityInfo.apply_start_time/}"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="apply_end_time" id="apply_end_time" class="input-medium"  value="{tpl:$activityInfo.apply_end_time/}" onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></th></tr>
</tr>
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1" onchange="getClubByCompany()">
			{tpl:loop $companyList  $company_info}
			<option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$activityInfo.company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover"><td>对应俱乐部</td>
	<td align="left">	<select name="club_id"  id="club_id" size="1">
			<option value="0"{tpl:if($club_info.club_id==0)}selected="selected"{/tpl:if} >不指定</option>
			{tpl:loop $clubList  $club_info}
			<option value="{tpl:$club_info.club_id/}"{tpl:if($club_info.club_id==$activityInfo.club_id)}selected="selected"{/tpl:if} >{tpl:$club_info.club_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover"><td>仅限俱乐部内成员</td>
	<td align="left">	<select name="club_member_only"  id="club_member_only" size="1">
			<option value="0"{tpl:if($club_info.club_member_only==0)}selected="selected"{/tpl:if} >否</option>
			<option value="1"{tpl:if($club_info.club_member_only==1)}selected="selected"{/tpl:if} >是</option>

		</select></td>
</tr>
<tr class="hover">
	<td>人数限制</td>
	<td align="left"><input name="member_limit" type="text" class="span2" id="member_limit" value="{tpl:$activityInfo.member_limit/}" size="50" /></td>
</tr>
	<tr class="hover"><td>上传图片</td>
		<td align="left">
			{tpl:if($activityInfo.icon!="")}
			已选图片:<img src="{tpl:$activityInfo.icon/}" width="30px;" height="30px;"/>
		<br>
			{/tpl:if}
			更改图片:<input name="upload_img[1]" type="file" class="span4" id="upload_img[1]"/>
		</td>
	</tr>
<tr class="hover">
	<td>自动跳转</td>
	<td align="left"><input type="text" class="span4" name="detail[jump_url]"  id="detail[jump_url]" value="{tpl:$activityInfo.detail.jump_url/}" size="50" /></td>
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
				errors[1] = '活动名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改活动成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + jsonResponse.company_id);}});
			}
		}
	};
	$('#activity_update_form').ajaxForm(options);
});

function getClubByCompany()
{
	company=$("#company_id");
	$.ajax
	({
		type: "GET",
		url: "?ctl=hj/activity&ac=get.club.by.company&company_id="+company.val(),
		success: function(msg)
		{
			$("#club_id").html(msg);
		}});
}
</script>
{tpl:tpl contentFooter/}
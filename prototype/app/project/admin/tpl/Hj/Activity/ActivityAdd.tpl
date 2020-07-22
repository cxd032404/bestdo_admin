{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="activity_add_form" name="activity_add_form" action="{tpl:$this.sign/}&ac=activity.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>活动名称</td>
	<td align="left"><input type="text" class="span2" name="activity_name"  id="activity_name" value="" size="50" /></td>
</tr>
<tr class="hover">
<td>活动时间</td>
	<th align="center" class="rowtip">
		<input type="text" name="start_time"  id="start_time" class="input-medium"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="end_time" id="end_time" class="input-medium"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></th></tr>
</tr>
<tr class="hover">
<td>报名时间</td>
<th align="center" class="rowtip">
	<input type="text" name="apply_start_time "  id="apply_start_time " class="input-medium"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="apply_end_time" id="apply_end_time" class="input-medium"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></th></tr>
</tr>
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1" onchange="getClubByCompany()">
			{tpl:loop $companyList  $companyInfo}
			<option value="{tpl:$companyInfo.company_id/}">{tpl:$companyInfo.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
	<tr class="hover"><td>对应俱乐部</td>
		<td align="left">	<select name="club_id"  id="club_id" size="1">
				<option value="0" selected="selected" >不指定</option>
				{tpl:loop $clubList  $club_info}
				<option value="{tpl:$club_info.club_id/}">{tpl:$club_info.club_name/}</option>
				{/tpl:loop}
			</select></td>
	</tr>
	<tr class="hover"><td>仅限俱乐部内成员</td>
		<td align="left">	<select name="club_member_only"  id="club_member_only" size="1">
				<option value="0">否</option>
				<option value="1">是</option>
			</select></td>
	</tr>
	<tr class="hover">
		<td>人数限制</td>
		<td align="left"><input name="member_limit" type="text" class="span2" id="member_limit" value="100" size="50" /></td>
	</tr>
<tr class="hover">
	<td>图片上传：</td>
	<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" /></td>
</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="activity_add_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#activity_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '活动名称不能为空，请修正后再次提交';
				errors[2] = '没有上传图片或上传失败，请修正后再次提交';
				errors[8] = '尚未绑定管理员用户，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加活动成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + $('#company_id').val());}});
			}
		}
	};
	$('#activity_add_form').ajaxForm(options);
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
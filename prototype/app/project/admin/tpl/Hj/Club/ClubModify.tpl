{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="club_update_form" name="club_update_form" action="{tpl:$this.sign/}&ac=club.update" method="post">
<input type="hidden" name="club_id" value="{tpl:$clubInfo.club_id/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>俱乐部名称</td>
<td align="left"><input name="club_name" type="text" class="span2" id="club_name" value="{tpl:$clubInfo.club_name/}" size="50" /></td>
</tr>
<tr class="hover">
	<td>俱乐部时间</td>
<th align="center" class="rowtip">
	<input type="text" name="start_time"  id="start_time" class="input-medium" value="{tpl:$clubInfo.start_time/}"  onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="end_time" id="end_time" class="input-medium" value="{tpl:$clubInfo.end_time/}"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></th></tr>
</tr>
<tr class="hover">
	<td>报名时间</td>
<th align="center" class="rowtip">
	<input type="text" name="apply_start_time "  id="apply_start_time " class="input-medium" value="{tpl:$clubInfo.apply_start_time/}"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="apply_end_time" id="apply_end_time" class="input-medium"  value="{tpl:$clubInfo.apply_end_time/}" onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></th></tr>
</tr>
<tr class="hover">
	<td>俱乐部标识</td>
<td align="left"><input type="text" class="span4" name="club_sign"  id="club_sign" value="{tpl:$clubInfo.club_sign/}" size="50" /></td>
</tr>
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1">
			{tpl:loop $companyList  $company_info}
			<option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$clubInfo.company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
	<tr class="hover"><td>上传图片</td>
		<td align="left">
			{tpl:if($clubInfo.icon!="")}
			已选图片:<img src="{tpl:$clubInfo.icon/}" width="30px;" height="30px;"/>
		<br>
			{/tpl:if}
			更改图片:<input name="upload_img[1]" type="file" class="span4" id="upload_img[1]"/>
		</td>
	</tr>
<tr class="hover">
	<td>自动跳转</td>
	<td align="left"><input type="text" class="span4" name="detail[jump_url]"  id="detail[jump_url]" value="{tpl:$clubInfo.detail.jump_url/}" size="50" /></td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="club_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#club_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '俱乐部名称不能为空，请修正后再次提交';
				errors[3] = '俱乐部标识不能为空，请修正后再次提交';
				errors[4] = '俱乐部标识'+$('#club_sign').val()+'已重复，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改俱乐部成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + jsonResponse.company_id);}});
			}
		}
	};
	$('#club_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
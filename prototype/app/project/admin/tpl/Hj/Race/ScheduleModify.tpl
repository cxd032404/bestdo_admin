{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="race_update_form" name="race_update_form" action="{tpl:$this.sign/}&ac=schedule.update" method="post">
	<input type="hidden" name="race_id" id="race_id" value="{tpl:$schedualInfo.race_id/}" />
	<input type="hidden" name="schedule_id" id="schedule_id" value="{tpl:$schedualInfo.id/}" />
<table width="99%" align="center" class="table table-bordered table-striped" >
	<tr class="hover">
		<td>开始日期：</td>
		<td align="left"><input type="text" name="start_time"  id="start_time " class="input-medium" value="{tpl:$schedualInfo.start_time/}"  onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
		</td>
	</tr>
	<tr class="hover">
		<td>开始日期：</td>
		<td align="left"><input type="text" name="end_time"  id="end_time " class="input-medium" value="{tpl:$schedualInfo.start_time/}"  onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
		</td>
	</tr>
	<tr class="hover">
	<td>场地</td>
	<td align="left">
		<select name="place" size="1" class="span2">
			<option value="0" {tpl:if(0==$schedualInfo.place)}selected="selected"{/tpl:if} >未选择</option>
			{tpl:loop $placeList $place_id $placeInfo}
			<option value="{tpl:$place_id/}" {tpl:if($place_id==$schedualInfo.place)}selected="selected"{/tpl:if} >{tpl:$placeInfo.place_name/}</option>
			{/tpl:loop}
		</select>
	</td></tr>
	<tr class="hover">
		<td>赛事</td>
		<td align="left"><input type="text" class="span4" name="match_name"  id="match_name" value="{tpl:$schedualInfo.match_name/}" size="50"/></td>
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
				errors[1] = '比赛名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改赛程成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}&ac=schedule&race_id='+ $('#race_id').val());}});
			}
		}
	};
	$('#race_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
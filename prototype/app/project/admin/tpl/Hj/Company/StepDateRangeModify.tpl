{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="date_range_modify_form" name="date_range_modify_form" action="{tpl:$this.sign/}&ac=step.date.range.update" method="post">
	<input type="hidden" name="company_id" id="company_id" value="{tpl:$dateRangeInfo.company_id/}" />
	<input type="hidden" name="date_id" id="date_id" value="{tpl:$dateRangeInfo.date_id/}" />
<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>开始日期：</td>
<td align="left"><input type="text" name="start_date"  id="start_date " class="input-medium" value="{tpl:$dateRangeInfo.start_date/}"  onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" >
</td>
</tr>
<tr class="hover">
<td>开始日期：</td>
<td align="left"><input type="text" name="end_date"  id="end_date " class="input-medium" value="{tpl:$dateRangeInfo.end_date/}"  onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" >
</td>
</tr>
<tr class="hover">
<td>标题</td>
<td align="left"><input type="text" class="span3" name="detail[title]"  id="detail[title]" value="{tpl:$dateRangeInfo.detail.title/}" size="50" /></td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="date_range_modify_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#date_range_modify_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '日期输入错误，请修正后再次提交';
				errors[3] = '所输入的时间段有冲突，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改日期成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=step.date.range&company_id=' + $('#company_id').val());}});
			}
		}
	};
	$('#date_range_modify_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
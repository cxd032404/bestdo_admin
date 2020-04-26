{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="action_credit_add_form" name="action_credit_add_form" action="{tpl:$this.sign/}&ac=action.credit.insert" method="post">
<input type="hidden" name="ActionId" value="{tpl:$ActionInfo.ActionId/}" />
<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr class="hover">
	<tr class="hover"><td>积分类目列表</td>
		<td align="left">
			<select name="CreditId" id="CreditId" size="1">
				{tpl:loop $CreditList $CreditInfo}
				<option value="{tpl:$CreditInfo.CreditId/}" >{tpl:$CreditInfo.CreditName/}</option>
				{/tpl:loop}
			</select>
		</td>
	</tr>
	</tr>
	<tr class="hover"><th align="center" class="rowtip">时间范围</th>
		<th align="center" class="rowtip"><input type="text" name="StartTime" class="input-medium"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="EndTime" value="" class="input-medium"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></th>
	</tr>
<tr class="hover"><td>频率</td>
		<td align="left">
			<select name="Frequency" id="Frequency"  class="span2"  onchange="getFrequenceConditon()">
				{tpl:loop $CreditFrequenceList $Frequency $FrequencyInfo}
				<option value="{tpl:$Frequency/}" >{tpl:$FrequencyInfo.Name/}</option>
				{/tpl:loop}
			</select>
			<div id="condition" name="condition"></div>
		</td>
</tr>
<td>获得积分数量</td>
<td align="left"><input type="text" class="span1" name="Credit"  id="Credit" value="" size="50" /></td>
</tr>
<td>获得积分次数</td>
<td align="left"><input type="text" class="span1" name="CreditCount"  id="CreditCount" value="" size="50" /></td>
</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="action_credit_add_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#action_credit_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '积分添加成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#action_credit_add_form').ajaxForm(options);
});
function getFrequenceConditon()
{
	frequency=$("#Frequency");
	$.ajax
	({
		type: "GET",
		url: "?ctl=hj/credit&ac=get.frequency.condition&Frequence="+frequency.val(),
		success: function(msg)
		{
			$("#condition").html(msg);
		}});
}
</script>
{tpl:tpl contentFooter/}
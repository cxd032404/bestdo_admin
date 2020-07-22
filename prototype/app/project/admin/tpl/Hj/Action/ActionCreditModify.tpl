{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="action_credit_add_form" name="action_credit_add_form" action="{tpl:$this.sign/}&ac=action.credit.update" method="post">
<input type="hidden" name="ActionId" id="ActionId" value="{tpl:$ActionInfo.ActionId/}" />
<input type="hidden" name="CId" id="CId" value="{tpl:$CId/}" />
	<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr class="hover">
<tr class="hover"><td>积分类目列表</td>
	<td align="left">
		<select name="CreditId" id="CreditId" size="1">
			{tpl:loop $CreditList $CreditInfo}
			<option value="{tpl:$CreditInfo.CreditId/}" {tpl:if($CreditInfo.CreditId==$Credit.CreditId)}selected="selected"{/tpl:if}>{tpl:$CreditInfo.CreditName/}</option>
			{/tpl:loop}
		</select>
	</td>
</tr>
<tr class="hover"><td>频率</td>
		<td align="left">
			<select name="Frequency" id="Frequency"  class="span2"  onchange="getFrequenceConditon()">
				{tpl:loop $CreditFrequenceList $Frequency $FrequencyInfo}
				<option value="{tpl:$Frequency/}" {tpl:if($Frequency==$Credit.Frequency)}selected="selected"{/tpl:if}>{tpl:$FrequencyInfo.Name/}</option>
				{/tpl:loop}
			</select>
			<div id="condition" name="condition">{tpl:$ConditionText/}</div>
		</td>
</tr>
<tr class="hover"><th align="center" class="rowtip">时间范围</th>
	<th align="center" class="rowtip"><input type="text" name="StartTime" class="input-medium" value="{tpl:$Credit.StartTime/}" onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="EndTime"  class="input-medium"  value="{tpl:$Credit.EndTime/}" onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" ></th>
</tr>
<td>获得积分数量</td>
<td align="left"><input type="text" class="span1" name="Credit"  id="Credit" value="{tpl:$Credit.Credit/}" size="50" /></td>
</tr>
<td>获得积分次数</td>
<td align="left"><input type="text" class="span1" name="CreditCount"  id="CreditCount" value="{tpl:$Credit.CreditCount/}" size="50" /></td>
</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="action_credit_add_submit" class="pb_btn_dark_1">提交</button></td>
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
				var message = '积分更新成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#action_credit_add_form').ajaxForm(options);
});
function getCreditByCatalog()
{
	catalog=$("#RaceCatalogId");
	$.ajax
	({
		type: "GET",
		url: "?ctl=xrace/credit&ac=get.credit.list.by.catalog&RaceCatalogId="+catalog.val(),
		success: function(msg)
		{
			$("#CreditId").html(msg);
		}});
}
function getFrequenceConditon()
{
	action=$("#ActionId");
	cid=$("#CId");
	frequency=$("#Frequency");
	$.ajax
	({
		type: "GET",
		url: "?ctl=xrace/credit&ac=get.frequency.condition&Frequence="+frequency.val()+"&ActionId="+action.val()+"&CId="+cid.val(),
		success: function(msg)
		{
			$("#condition").html(msg);
		}});
}
</script>
{tpl:tpl contentFooter/}
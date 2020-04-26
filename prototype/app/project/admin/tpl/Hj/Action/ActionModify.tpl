{tpl:tpl contentHeader/}
<script type="text/javascript">
	function CreditAdd(aid){
        addCreditBox = divBox.showBox('{tpl:$this.sign/}&ac=action.credit.add&ActionId=' + aid, {title:'添加积分',width:500,height:500});
    }
    function CreditModify(aid,cid){
        addCreditBox = divBox.showBox('{tpl:$this.sign/}&ac=action.credit.modify&ActionId=' + aid + '&CId=' + cid, {title:'修改积分',width:500,height:500});
    }
	function CreditDelete(aid,cid,c_name){
        deleteLicenseBox = divBox.confirmBox({content:'是否删除 '+c_name+'?',ok:function(){location.href = '{tpl:$this.sign/}&ac=action.credit.delete&ActionId=' + aid + '&CId=' + cid;}});
    }

</script>
<div class="br_bottom"></div>
<form id="action_update_form" name="action_update_form" action="{tpl:$this.sign/}&ac=action.update" method="post">
<input type="hidden" name="ActionId" value="{tpl:$ActionInfo.ActionId/}" />
<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
<tr class="hover">
<td>动作标识</td>
<td align="left"><input name="Action" type="text" class="span2" id="Action" value="{tpl:$ActionInfo.Action/}" size="50" /></td>
</tr>
<td>动作名称</td>
<td align="left"><input name="ActionName" type="text" class="span2" id="ActionName" value="{tpl:$ActionInfo.ActionName/}" size="50" /></td>
</tr>
<tr class="hover"><td>积分增加</td>
	<td align="left">
		{tpl:if(isset($ActionInfo.CreditList)&&(count($ActionInfo.CreditList)>=1))}
		{tpl:$CreditListHtml/}
        <a href="javascript:;" onclick="CreditAdd('{tpl:$ActionInfo.ActionId/}');">添加积分</a>
		{tpl:else}
		<table>
			<tr><td>
					无积分 <a href="javascript:;" onclick="CreditAdd('{tpl:$ActionInfo.ActionId/}');">添加积分</a>
				</td></tr>
		</table>
		{/tpl:if}
	</td>
</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="action_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#action_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '动作名称不能为空，请修正后再次提交';
				errors[2] = '请选择一个有效的赛事，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改动作成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#action_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
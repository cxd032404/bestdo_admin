{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="update_log_update_form" name="update_log_update_form" action="{tpl:$this.sign/}&ac=update.log.update" method="post">
<input type="hidden" name="UpdateLogId" id="UpdateLogId" value="{tpl:$UpdateLogInfo.UpdateLogId/}" />
<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
    <tr class="hover">
        <td>更新日期</td>
        <td align="left"><input type="text" name="UpdateDate" value="{tpl:$UpdateLogInfo.UpdateDate/}" class="input-medium"   onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd'})" ></td>
    </tr>
    <tr class="hover">
        <td>更新类型</td>
        <td align="left">
            <select name="LogType" class = "span2" size="1">
                {tpl:loop $UpdateLogTypeList $LogType $LogTypeName}
                <option value="{tpl:$LogType/}"{tpl:if($LogType==$UpdateLogInfo.LogType)}selected="selected"{/tpl:if}>{tpl:$LogTypeName/}</option>
                {/tpl:loop}
            </select>
        </td>
    </tr>
    <tr class="hover">
        <td>更新内容</td>
        <td align="left"><textarea name="comment" id="comment" class="span5" rows="4">{tpl:$UpdateLogInfo.comment/}</textarea></td>
    </tr>
    <tr class="noborder"><td></td>
        <td><button type="submit" id="update_log_update_submit">提交</button></td>
    </tr>
</table>
</form>

<script type="text/javascript">
$('#update_log_update_submit').click(function(){
	var options = {
		dataOS:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '更新内容不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改更新记录成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#update_log_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
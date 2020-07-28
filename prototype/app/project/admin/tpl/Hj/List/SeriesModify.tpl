{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="series_update_form" name="series_update_form" action="{tpl:$this.sign/}&ac=series.update" method="post">
<input type="hidden" name="series_id" value="{tpl:$seriesInfo.series_id/}" />
	<input type="hidden" name="company_id" value="{tpl:$seriesInfo.company_id/}" />
	<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>系列名称</td>
<td align="left"><input name="series_name" type="text" class="span2" id="series_name" value="{tpl:$seriesInfo.series_name/}" size="50" /></td>
</tr>
<tr class="hover">
	<td>系列标示</td>
	<td align="left"><input name="series_sign" type="text" class="span2" id="series_sign" value="{tpl:$seriesInfo.series_sign/}" size="50" /></td>
</tr>
	<tr class="hover">
		<td>数据列数量</td>
		<td align="left">	<select class="span1" name="series_count"  id="series_count" size="1" >
				{tpl:loop $countList  $count}
				<option value="{tpl:$count/}" {tpl:if($count==$seriesInfo.series_count)}selected="selected"{/tpl:if} >{tpl:$count/}</option>
				{/tpl:loop}
			</select></td>
	</tr>
	<tr class="hover"><td colspan = 2>备注</td></tr>
	<tr class="hover"><td colspan = 2>
			<textarea style="width:500px; height:200px" name="detail[comment]" id="detail[comment]" >{tpl:$seriesInfo.detail.comment/}</textarea>
		</td>
	</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="series_update_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#series_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '系列名称不能为空，请修正后再次提交';
				errors[4] = '系列标示不能为空，请修正后再次提交';
				errors[2] = '系列名称'+$('#series_name').val()+'已重复，请修正后再次提交';
				errors[3] = '系列标示'+$('#series_sign').val()+'已重复，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改系列成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + $('#company_id').val());}});
			}
		}
	};
	$('#series_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
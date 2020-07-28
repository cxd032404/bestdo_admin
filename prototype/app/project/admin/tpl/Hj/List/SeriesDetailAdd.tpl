{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="series_detail_add_form" name="series_detail_add_form" action="{tpl:$this.sign/}&ac=series.detail.insert" method="post">
<input type="hidden" name="series_id" id="series_id"  value="{tpl:$seriesInfo.series_id/}" />
	<table width="99%" align="center" class="table table-bordered table-striped" >
		{tpl:loop $countList  $id}
		<tr class="hover">
			<td>数据列【{tpl:$id/}】</td>
			<td align="left">	<select class="span3" name="list_id[{tpl:$id/}]"  id="list_id[{tpl:$id/}]" size="1" >
					<option value="0" >不选择</option>
					{tpl:loop $ListList.ListList  $listInfo}
					<option value="{tpl:$listInfo.list_id/}" >{tpl:$listInfo.list_name/}</option>
					{/tpl:loop}
				</select></td>
		</tr>
		{/tpl:loop}
<tr class="noborder"><td></td>
<td><button type="submit" id="series_detail_add_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#series_detail_add_submit').click(function(){
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
	$('#series_detail_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
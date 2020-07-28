{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="series_detail_add_form" name="series_detail_add_form" action="{tpl:$this.sign/}&ac=series.detail.insert" method="post">
<input type="hidden" name="series_id" id="series_id"  value="{tpl:$seriesInfo.series_id/}" />
	<table width="99%" align="center" class="table table-bordered table-striped" >
		<tr class="hover">
			<td>名称</td>
			<td align="left"><input type="text" class="span2" name="detail_name"  id="detail_name" value="" size="50" /></td>
		</tr>
		{tpl:loop $countList  $id}
		<tr class="hover">
			<td>数据列【{tpl:$id/}】</td>
			<td align="left">	<select class="span3" name="detail[list_id][{tpl:$id/}]"  id="detail[list_id][{tpl:$id/}]" size="1" >
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
				errors[1] = '元素名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加数据成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=series.detail&series_id=' + $('#series_id').val());}});
			}
		}
	};
	$('#series_detail_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
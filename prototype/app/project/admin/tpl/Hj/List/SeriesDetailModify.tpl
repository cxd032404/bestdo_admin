{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="series_detail_modify_form" name="series_detail_modify_form" action="{tpl:$this.sign/}&ac=series.detail.update" method="post">
	<input type="hidden" name="series_id" id="series_id"  value="{tpl:$detailInfo.series_id/}" />
	<input type="hidden" name="detail_id" id="detail_id"  value="{tpl:$detailInfo.detail_id/}" />
	<table width="99%" align="center" class="table table-bordered table-striped" >
		<tr class="hover">
			<td>名称</td>
			<td align="left"><input type="text" class="span2" name="detail_name"  id="detail_name" value="{tpl:$detailInfo.detail_name/}" size="50" /></td>
		</tr>
		{tpl:loop $countList  $id}
		<tr class="hover">
			<td>数据列【{tpl:$id/}】</td>
			<td align="left">	<select class="span3" name="detail[list_id][{tpl:$id/}]"  id="detail[list_id][{tpl:$id/}]" size="1" >
					{tpl:loop $detailInfo.detail.list_id  $l_id $list_id}
						{tpl:if($l_id==$id)}
							<option value="0" {tpl:if(0==$listInfo.list_id)}selected="selected"{/tpl:if}>不选择</option>
							{tpl:loop $ListList.ListList  $listInfo}
								<option value="{tpl:$listInfo.list_id/}" {tpl:if($list_id==$listInfo.list_id)}selected="selected"{/tpl:if}>{tpl:$listInfo.list_name/}</option>
							{/tpl:loop}
						{/tpl:if}
					{/tpl:loop}
				</select></td>
		</tr>
		{/tpl:loop}
<tr class="noborder"><td></td>
<td><button type="submit" id="series_detail_modify_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#series_detail_modify_submit').click(function(){
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
				var message = '修改数据成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=series.detail&series_id=' + $('#series_id').val());}});
			}
		}
	};
	$('#series_detail_modify_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
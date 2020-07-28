{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="series_add_form" name="series_add_form" action="{tpl:$this.sign/}&ac=series.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
	<td>系列名称</td>
	<td align="left"><input type="text" class="span2" name="series_name"  id="series_name" value="" size="50" /></td>
</tr>
	<input type="hidden"  name="specifiedType"  id="specifiedType" value="{tpl:$specifiedType/}" size="50" />
	<td>系列标示</td>
	<td align="left"><input type="text" class="span2" name="series_sign"  id="series_sign" value="" size="50" /></td>
	</tr>
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1" >
			{tpl:loop $companyList  $companyInfo}
			<option value="{tpl:$companyInfo.company_id/}">{tpl:$companyInfo.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover">
	<td>数据列数量</td>
	<td align="left">	<select class="span1" name="series_count"  id="series_count" size="1" >
			{tpl:loop $countList  $count}
			<option value="{tpl:$count/}">{tpl:$count/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover"><td colspan = 2>备注</td></tr>
<tr class="hover"><td colspan = 2>
		<textarea style="width:500px; height:200px" name="detail[comment]" id="detail[comment]" ></textarea>
	</td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="series_add_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#series_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
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
				var message = '添加系列成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + $('#company_id').val()+ '&ac=' + jsonResponse.ac);}});
			}
		}
	};
	$('#series_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
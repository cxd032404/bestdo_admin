{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="list_add_form" name="list_add_form" action="{tpl:$this.sign/}&ac=list.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>列表名称</td>
	<td align="left"><input type="text" class="span2" name="list_name"  id="list_name" value="" size="50" /></td>
</tr>
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1">
			{tpl:loop $companyList  $companyInfo}
			<option value="{tpl:$companyInfo.company_id/}">{tpl:$companyInfo.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover"><td>列表分类</td>
	<td align="left">
			<select name="list_type"  id="list_type" size="1">
				{tpl:loop $listTypeList  $type $type_info}
				<option value="{tpl:$type/}">{tpl:$type_info.name/}</option>
				{/tpl:loop}
		</select></td>
</tr>
<tr class="hover"><td>自定义数量</td>
	<td align="left">图片：
		<select name="detail[limit][pic]"  id="detail[limit][pic]" size="1" class="span2">
			<?php for($i=0;$i<=10;$i++){?>
			<option value="{tpl:$i/}">{tpl:$i/}</option>
					<?php }?>
		</select>
		视频：
		<select name="detail[limit][video]"  id="detail[limit][video]" size="1" class="span2">
			<?php for($i=0;$i<=3;$i++){?>
			<option value="{tpl:$i/}">{tpl:$i/}</option>
			<?php }?>
		</select>
		文本文件：
		<select name="detail[limit][txt]"  id="detail[limit][txt]" size="1" class="span2">
			<?php for($i=0;$i<=3;$i++){?>
			<option value="{tpl:$i/}">{tpl:$i/}</option>
			<?php }?>
		</select>
		文本域：
		<select name="detail[limit][textarea]"  id="detail[limit][textarea]" size="1" class="span2">
			<option value="1">需要</option>
			<option value="0">不需要</option>
		</select>
	</td>
</tr>
	<tr class="hover"><td colspan = 2>备注</td></tr>
	<tr class="hover"><td colspan = 2>
			<textarea style="width:500px; height:200px" name="detail[comment]" id="detail[comment]" ></textarea>
		</td>
	</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="list_add_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#list_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '列表名称不能为空，请修正后再次提交';
				errors[2] = '列表名称'+$('#list_name').val()+'已重复，请修正后再次提交';
				errors[3] = '至少要选择一个可上传资源，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加列表成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + $('#company_id').val());}});
			}
		}
	};
	$('#list_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
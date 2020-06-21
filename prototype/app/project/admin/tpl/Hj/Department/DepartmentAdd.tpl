{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<form id="department_add_form" name="department_add_form" action="{tpl:$this.sign/}&ac=department.insert" method="post">
<table width="99%" align="center" class="table table-bordered table-striped">
<tr class="hover">
<td>部门名称</td>
	<td align="left"><input type="text" class="span2" name="department_name"  id="department_name" value="" size="50" /></td>
</tr>
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1" onchange="getDepartmentByCompany()">
			<option value="0">请选择</option>
			{tpl:loop $companyList  $companyInfo}
			<option value="{tpl:$companyInfo.company_id/}">{tpl:$companyInfo.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover"><td>一级部门</td>
	<td align="left">	<select name="parent_id"  id="parent_id" size="1" onchange="getDepartmentByCompany_2()">
		</select></td>
</tr>
<tr class="hover"><td>二级部门</td>
	<td align="left">	<select name="parent_id_2"  id="parent_id_2" size="1">
		</select></td>
</tr>
	<tr class="noborder"><td></td>
<td><button type="submit" id="department_add_submit">提交</button></td>
</tr>
</table>
</form>
<script type="text/javascript">
$('#department_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '部门名称不能为空，请修正后再次提交';
				errors[2] = '必须选择一家企业，请修正后再次提交';
				errors[4] = '部门名称'+$('#department_name').val()+'已重复，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加部门成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + $('#company_id').val());}});
			}
		}
	};
	$('#department_add_form').ajaxForm(options);
});
function getDepartmentByCompany()
{
	company=$("#company_id");
	$.ajax
	({
		type: "GET",
		url: "?ctl=hj/department&ac=get.department.by.company&company_id="+company.val(),
		success: function(msg)
		{
			$("#parent_id").html(msg);
			$("#parent_id_2").html("");
		}});
}
function getDepartmentByCompany_2()
{
	company=$("#company_id");
	parent=$("#parent_id");
	$.ajax
	({
		type: "GET",
		url: "?ctl=hj/department&ac=get.department.by.company&company_id="+company.val()+"&parent_id="+parent.val(),
		success: function(msg)
		{
			$("#parent_id_2").html(msg);
		}});
}
</script>
{tpl:tpl contentFooter/}
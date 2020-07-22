{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="department_update_form" name="department_update_form" action="{tpl:$this.sign/}&ac=department.update" method="post">
<input type="hidden" name="user_id" id="user_id" value="{tpl:$userInfo.user_id/}" />
	<input type="hidden" name="true_name" id="true_name" value="{tpl:$userInfo.true_name/}" />
	<input type="hidden" name="company_id" id="company_id" value="{tpl:$departmentInfo.company_id/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover"><td>一级部门</td>
	<td align="left">	<select name="department_id_1"  id="department_id_1" size="1" onchange="getDepartmentByCompany_2()">
			<option value="0">不选择</option>
			{tpl:loop $departmentList_1  $d_info}
			<option value="{tpl:$d_info.department_id/}"{tpl:if($d_info.selected==1)}selected="selected"{/tpl:if} >{tpl:$d_info.department_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover"><td>二级部门</td>
	<td align="left">	<select name="department_id_2"  id="department_id_2" size="1" onchange="getDepartmentByCompany_3()">
			<option value="0">不选择</option>
			{tpl:loop $departmentList_2  $d_info_2}
			<option value="{tpl:$d_info_2.department_id/}"{tpl:if($d_info_2.selected==1)}selected="selected"{/tpl:if} >{tpl:$d_info_2.department_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover"><td>三级部门</td>
	<td align="left">	<select name="department_id_3"  id="department_id_3" size="1">
			<option value="0">不选择</option>
			{tpl:loop $departmentList_3  $d_info_3}
			<option value="{tpl:$d_info_3.department_id/}"{tpl:if($d_info_3.selected==1)}selected="selected"{/tpl:if} >{tpl:$d_info_3.department_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="department_update_submit" class="pb_btn_dark_1">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#department_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '部门名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改用户成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + $('#company_id').val()+ '&true_name=' + encodeURIComponent($('#true_name').val()));}});
			}
		}
	};
	$('#department_update_form').ajaxForm(options);
});
function getDepartmentByCompany_2()
{
	company=$("#company_id");
	parent=$("#department_id_1");
	$.ajax
	({
		type: "GET",
		url: "?ctl=hj/department&ac=get.department.by.company&company_id="+company.val()+"&parent_id="+parent.val(),
		success: function(msg)
		{
			$("#department_id_2").html(msg);
			$("#department_id_3").html("");
		}});
}function getDepartmentByCompany_3()
{
	company=$("#company_id");
	parent=$("#department_id_2");
	$.ajax
	({
		type: "GET",
		url: "?ctl=hj/department&ac=get.department.by.company&company_id="+company.val()+"&parent_id="+parent.val(),
		success: function(msg)
		{
			$("#department_id_3").html(msg);
		}});
}
</script>
{tpl:tpl contentFooter/}
{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_department').click(function(){
    addDepartmentBox = divBox.showBox('{tpl:$this.sign/}&ac=department.add', {title:'添加部门',width:600,height:450});
  });
});

function departmentDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deleteDepartmentBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=department.delete&department_id=' + p_id;}});
}

function departmentModify(mid){
  modifyDepartmentBox = divBox.showBox('{tpl:$this.sign/}&ac=department.modify&department_id=' + mid, {title:'修改部门',width:600,height:450});
}

</script>

<fieldset><legend>操作</legend>

  <div align=right><a class="pb_btn_dark_1" href="javascript:;" id="add_department">新增</a></div>

</fieldset>

<fieldset><legend>部门列表 </legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
<select name="company_id"  id="company_id" size="1">
      {tpl:loop $companyList  $company_info}
      <option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
      {/tpl:loop}
    </select>
  <button type="submit" class="pb_btn_light_1">搜索</button>
</form>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">部门ID</th>
    <th align="center" class="rowtip">部门名称</th>
      <th align="center" class="rowtip">上级部门</th>
    <th align="center" class="rowtip">对应企业</th>
    <th align="center" class="rowtip">更新时间</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $departmentList $departmentInfo}
  <tr class="hover">
    <td align="center">{tpl:$departmentInfo.department_id/}</td>
    <td align="center">{tpl:$departmentInfo.display_department_name/}</td>
    <td align="center">{tpl:$departmentInfo.parent_department_name/}</td>
      <td align="center">{tpl:$departmentInfo.company_name/}</td>
    <td align="center">{tpl:$departmentInfo.update_time/}</td>
      <td align="center"> {tpl:if(0==$departmentInfo.child_count)}  <a class="pb_btn_grey_1" href="javascript:;" onclick="departmentDelete('{tpl:$departmentInfo.department_id/}','{tpl:$departmentInfo.department_name/}')">删除</a> {/tpl:if}
  <a class="pb_btn_light_1" href="javascript:;" onclick="departmentModify('{tpl:$departmentInfo.department_id/}');">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

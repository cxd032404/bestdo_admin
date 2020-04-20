{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_app').click(function(){
    addAppBox = divBox.showBox('{tpl:$this.sign/}&ac=company.add', {title:'添加企业',width:400,height:250});
  });
});

function companyDelete(p_id, p_name){
  deleteAppBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=company.delete&company_id=' + p_id;}});
}

function companyModify(mid){
  modifycompanyBox = divBox.showBox('{tpl:$this.sign/}&ac=company.modify&company_id=' + mid, {title:'修改企业',width:400,height:250});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_app">添加企业</a> ]
</fieldset>

<fieldset><legend>企业列表 </legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">企业ID</th>
    <th align="center" class="rowtip">企业名称</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $SportTypeList $companyInfo}
  <tr class="hover">
    <td align="center">{tpl:$companyInfo.company_id/}</td>
    <td align="center">{tpl:$companyInfo.company_name/}</td>

      <td align="center"><a  href="javascript:;" onclick="companyDelete('{tpl:$companyInfo.company_id/}','{tpl:$companyInfo.company_name/}')">删除</a> |  <a href="javascript:;" onclick="companyModify('{tpl:$companyInfo.company_id/}');">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

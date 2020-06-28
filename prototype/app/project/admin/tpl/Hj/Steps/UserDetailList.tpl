{tpl:tpl contentHeader/}
<script type="text/javascript">
</script>

<fieldset><legend>操作</legend>
</fieldset>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
    企业：<select name="company_id"  id="company_id" size="1" onchange="getDepartmentByCompany()">
        <option value="0" {tpl:if(0==$params.company_id)}selected="selected"{/tpl:if} >全部</option>
        {tpl:loop $companyList  $company_info}
        <option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$params.company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
        {/tpl:loop}
    </select>
    部门:
    <select name="department_id_1"  id="department_id_1" size="1" onchange="getDepartmentByCompany_2()">
        <option value="0" {tpl:if(0==$params.department_id_1)}selected="selected"{/tpl:if} >不选择</option>
        {tpl:loop $departmentList_1  $d_info}
        <option value="{tpl:$d_info.department_id/}"{tpl:if($d_info.department_id==$params.department_id_1)}selected="selected"{/tpl:if} >{tpl:$d_info.department_name/}</option>
        {/tpl:loop}
    </select>
    -
    <select name="department_id_2"  id="department_id_2" size="1" onchange="getDepartmentByCompany_3()">
        <option value="0" {tpl:if(0==$params.department_id_2)}selected="selected"{/tpl:if} >不选择</option>
        {tpl:loop $departmentList_2  $d_info_2}
        <option value="{tpl:$d_info_2.department_id/}"{tpl:if($d_info_2.department_id==$params.department_id_2)}selected="selected"{/tpl:if} >{tpl:$d_info_2.department_name/}</option>
        {/tpl:loop}
    </select>
    -
    <select name="department_id_3"  id="department_id_3" size="1">
        <option value="0" {tpl:if(0==$params.department_id_3)}selected="selected"{/tpl:if} >不选择</option>
        {tpl:loop $departmentList_3  $d_info_3}
        <option value="{tpl:$d_info_3.department_id/}"{tpl:if($d_info_3.department_id==$params.department_id_3)}selected="selected"{/tpl:if} >{tpl:$d_info_3.department_name/}</option>
        {/tpl:loop}
    </select>

    <input type="submit" name="submit" value="查询" />{tpl:$export_var/}
</form>
<fieldset><legend>详情列表</legend>
<table width="99%" align="center" class="table table-bordered table-striped">
      <tr>
        <th align="center" class="rowtip">企业</th>
          <th align="center" class="rowtip">姓名</th>
          <th align="center" class="rowtip">日期</th>
          <th align="center" class="rowtip">步数</th>
        <th align="center" class="rowtip">热量</th>
        <th align="center" class="rowtip">估测距离</th>
          <th align="center" class="rowtip">达标率</th>
          <th align="center" class="rowtip">达标</th>
        <th align="center" class="rowtip">更新时间</th>
      </tr>
    {tpl:loop $StepsDetailList.DetailList $StepsInfo}
      <tr class="hover">
        <td align="center">{tpl:$StepsInfo.company_name/}</td>
          <td align="center">{tpl:$StepsInfo.user_name/}</td>
          <td align="center">{tpl:$StepsInfo.date/}</td>
        <td align="center">{tpl:$StepsInfo.step/}</td>
        <td align="center">{tpl:$StepsInfo.kcal/}kcal</td>
        <td align="center">{tpl:$StepsInfo.distance/}米</td>
          <td align="center">{tpl:$StepsInfo.achive_rate/}%</td>
        <td align="center">{tpl:if(0==$StepsInfo.achive)}未达标{tpl:else}达标{/tpl:if}</td>
        <td align="center">{tpl:$StepsInfo.update_time/}</td>
      </tr>
    {/tpl:loop}
    <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>

</table>
</fieldset>
<script type="text/javascript">
    function getDepartmentByCompany()
    {
        company=$("#company_id");
        $.ajax
        ({
            type: "GET",
            url: "?ctl=hj/department&ac=get.department.by.company&company_id="+company.val(),
            success: function(msg)
            {
                $("#department_id_1").html(msg);
                $("#department_id_2").html("");
            }});
    }
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
            }});
    }
    function getDepartmentByCompany_3()
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

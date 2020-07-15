{tpl:tpl contentHeader/}


<fieldset><legend>操作</legend>
</fieldset>

<fieldset><legend>企业列表 </legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">企业ID</th>
    <th align="center" class="rowtip">企业名称</th>
    <th align="center" class="rowtip">上级</th>
    <th align="center" class="rowtip">显示</th>
    <th align="center" class="rowtip">图标</th>
    <th align="center" class="rowtip">更新时间</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $companyList $companyInfo}
  <tr class="hover">
    <td align="center">{tpl:$companyInfo.company_id/}</td>
    <td align="center">{tpl:$companyInfo.company_name/}</td>
    <td align="center">{tpl:$companyInfo.parent_name/}</td>
    <td align="center">{tpl:$companyInfo.display_name/}</td>
    <td align="center">{tpl:if($companyInfo.icon=="")}未定义{tpl:else}<img src="{tpl:$RootUrl/}{tpl:$companyInfo.icon/}" width='150' height='130'/>{/tpl:if}</td>
    <td align="center">{tpl:$companyInfo.update_time/}</td>
      <td align="center">
        <a href="{tpl:$this.sign/}&ac=club.banner&company_id={tpl:$companyInfo.company_id/}">俱乐部Banner({tpl:$companyInfo.detail.clubBanner func="count(@@)"/})</a>
        | <a href="{tpl:$this.sign/}&ac=step.banner&company_id={tpl:$companyInfo.company_id/}">健步走Banner({tpl:$companyInfo.detail.stepBanner func="count(@@)"/})</a>
      </td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

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
    <td align="center">{tpl:if($companyInfo.icon=="")}未定义{tpl:else}<img src="{tpl:$RootUrl/}{tpl:$companyInfo.icon/}" width='90' height='90'/>{/tpl:if}</td>
    <td align="center">{tpl:$companyInfo.update_time/}</td>
      <td align="center">
        <a class = "pb_btn_light_3" href="{tpl:$this.sign/}&ac=banner.list&banner_type=clubBanner&company_id={tpl:$companyInfo.company_id/}&currentPage={tpl:$currentPage/}">俱乐部Banner({tpl:$companyInfo.detail.clubBanner func="count(@@)"/})</a>
        | <a class = "pb_btn_light_3" href="{tpl:$this.sign/}&ac=banner.list&banner_type=stepBanner&company_id={tpl:$companyInfo.company_id/}&currentPage={tpl:$currentPage/}">健步走Banner({tpl:$companyInfo.detail.stepBanner func="count(@@)"/})</a>
        <p> <a class = "pb_btn_light_3" href="{tpl:$this.sign/}&ac=banner.list&banner_type=wtBanner&company_id={tpl:$companyInfo.company_id/}&currentPage={tpl:$currentPage/}">文体之窗Banner({tpl:$companyInfo.detail.wtBanner func="count(@@)"/})</a>
        | <a class = "pb_btn_light_3" href="{tpl:$this.sign/}&ac=banner.list&banner_type=indexBanner&company_id={tpl:$companyInfo.company_id/}&currentPage={tpl:$currentPage/}">首页Banner({tpl:$companyInfo.detail.indexBanner func="count(@@)"/})</a>
      </td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

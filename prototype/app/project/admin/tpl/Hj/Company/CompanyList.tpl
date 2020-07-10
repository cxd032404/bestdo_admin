{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_company').click(function(){
    addCompanyBox = divBox.showBox('{tpl:$this.sign/}&ac=company.add', {title:'添加企业',width:600,height:300});
  });
});

function companyDelete(c_id,p_id,p_name){
  if(p_id>0)
  {
    msg = '是否删除 ' + p_name + '?'
  }
  else
  {
    msg = '是否删除 ' + p_name + '?' + '(其下所属的企业也会被一并删除)'
  }
  deleteAppBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=company.delete&company_id=' + c_id;}});
}

function companyModify(mid){
  modifycompanyBox = divBox.showBox('{tpl:$this.sign/}&ac=company.modify&company_id=' + mid, {title:'修改企业',width:600,height:400});
}
function protocalModify(mid,type){
  modifycompanyBox = divBox.showBox('{tpl:$this.sign/}&ac=protocal.modify&company_id=' + mid + '&type=' + type, {title:'协议',width:600,height:600});
}
function regPage(mid,name){
    openIdBindBox = divBox.showBox('{tpl:$this.sign/}&ac=reg.page.qr&id=' + mid, {title:name+':注册页',width:430,height:430});
}
function regPage_miniprogram(mid,name){
    openIdBindBox = divBox.showBox('{tpl:$this.sign/}&ac=reg.page.qr.miniprogram&id=' + mid, {title:name+':注册页(小程序)',width:430,height:430});
}
</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_company">添加企业</a> ]
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
      <td align="center"><a  href="javascript:;" onclick="companyDelete('{tpl:$companyInfo.company_id/}','{tpl:$companyInfo.parent_id/}','{tpl:$companyInfo.company_name/}')">删除</a>
 |  <a href="javascript:;" onclick="companyModify('{tpl:$companyInfo.company_id/}');">修改</a>
  | <a href="javascript:;" onclick="protocalModify('{tpl:$companyInfo.company_id/}','privacy');">隐私政策</a>
  | <a href="javascript:;" onclick="protocalModify('{tpl:$companyInfo.company_id/}','user');">用户政策</a>
          | <a href="javascript:;" onclick="protocalModify('{tpl:$companyInfo.company_id/}','privacy_m');">隐私政策(小程序）</a>
          | <a href="javascript:;" onclick="protocalModify('{tpl:$companyInfo.company_id/}','user_m');">用户政策（小程序）</a>
  | <a href="javascript:;" onclick="regPage_miniprogram('{tpl:$companyInfo.company_id/}','{tpl:$companyInfo.company_name/}');">注册页面(小程序）</a>
          | <a href="javascript:;" onclick="regPage('{tpl:$companyInfo.company_id/}','{tpl:$companyInfo.company_name/}');">注册页面</a>
        | <a href="{tpl:$this.sign/}&ac=boutique&company_id={tpl:$companyInfo.company_id/}">精品课设置({tpl:$companyInfo.detail.boutique func='count(@@)'/}) </a>
        | <a href="{tpl:$this.sign/}&ac=club.banner&company_id={tpl:$companyInfo.company_id/}">俱乐部Banner({tpl:$companyInfo.detail.clubBanner func="count(@@)"/})</a>
        | <a href="{tpl:$this.sign/}&ac=step.banner&company_id={tpl:$companyInfo.company_id/}">健步走Banner({tpl:$companyInfo.detail.stepBanner func="count(@@)"/})</a>
        | <a href="{tpl:$this.sign/}&ac=step.date.range&company_id={tpl:$companyInfo.company_id/}">健步走日期设置</a>
      </td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

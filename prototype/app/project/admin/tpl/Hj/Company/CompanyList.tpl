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
[ <a class = "pb_btn_dark_1" href="javascript:;" id="add_company">新增</a> ]
</fieldset>

<fieldset><legend>企业列表 </legend>
    <div style="height: auto;overflow: scroll !important;width: 70%;">
        <table  align="center" class="table table-bordered table-striped" style="overflow: scroll;max-width: none;width: 1500px;">
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
    <td align="center">{tpl:if($companyInfo.icon=="")}未定义{tpl:else}<img src="{tpl:$RootUrl/}{tpl:$companyInfo.icon/}" width='120' height='120'/>{/tpl:if}</td>
    <td align="center">{tpl:$companyInfo.update_time/}</td>
      <td align="center"><a class = "pb_btn_grey_1"  href="javascript:;" onclick="companyDelete('{tpl:$companyInfo.company_id/}','{tpl:$companyInfo.parent_id/}','{tpl:$companyInfo.company_name/}')">删除</a>
 |  <a class = "pb_btn_light_1" href="javascript:;" onclick="companyModify('{tpl:$companyInfo.company_id/}');">修改</a>
  | <a class = "pb_btn_light_2" href="javascript:;" onclick="protocalModify('{tpl:$companyInfo.company_id/}','privacy');">隐私政策</a>
          | <a class = "pb_btn_light_2" href="javascript:;" onclick="protocalModify('{tpl:$companyInfo.company_id/}','user');">用户政策</a>
          | <a class = "pb_btn_light_2" href="javascript:;" onclick="regPage('{tpl:$companyInfo.company_id/}','{tpl:$companyInfo.company_name/}');">注册页面</a>
          <p><a class = "pb_btn_light_3" href="javascript:;" onclick="protocalModify('{tpl:$companyInfo.company_id/}','privacy_m');">隐私政策(小程序)</a>
          | <a class = "pb_btn_light_3" href="javascript:;" onclick="protocalModify('{tpl:$companyInfo.company_id/}','user_m');">用户政策(小程序)</a>
          | <a class = "pb_btn_light_3" href="javascript:;" onclick="regPage_miniprogram('{tpl:$companyInfo.company_id/}','{tpl:$companyInfo.company_name/}');">注册页面(小程序)</a>
          | <a class = "pb_btn_light_3" href="{tpl:$this.sign/}&ac=list&type=boutique&company_id={tpl:$companyInfo.company_id/}">精品课设置({tpl:$companyInfo.detail.boutique func='count(@@)'/})</a>
          | <a class = "pb_btn_light_3" href="{tpl:$this.sign/}&ac=list&type=honor&company_id={tpl:$companyInfo.company_id/}">荣誉堂设置({tpl:$companyInfo.detail.honor func='count(@@)'/})</a>
          <p><a class = "pb_btn_light_3" href="{tpl:$this.sign/}&ac=banner.list&banner_type=clubBanner&company_id={tpl:$companyInfo.company_id/}&currentPage={tpl:$currentPage/}">俱乐部Banner({tpl:$companyInfo.detail.clubBanner func="count(@@)"/})</a>
          | <a class = "pb_btn_light_3" href="{tpl:$this.sign/}&ac=banner.list&banner_type=stepBanner&company_id={tpl:$companyInfo.company_id/}&currentPage={tpl:$currentPage/}">健步走Banner({tpl:$companyInfo.detail.stepBanner func="count(@@)"/})</a>
          | <a class = "pb_btn_light_3" href="{tpl:$this.sign/}&ac=banner.list&banner_type=wtBanner&company_id={tpl:$companyInfo.company_id/}&currentPage={tpl:$currentPage/}">文体之窗Banner({tpl:$companyInfo.detail.wtBanner func="count(@@)"/})</a>
          | <a class = "pb_btn_light_3" href="{tpl:$this.sign/}&ac=banner.list&banner_type=indexBanner&company_id={tpl:$companyInfo.company_id/}&currentPage={tpl:$currentPage/}">首页Banner({tpl:$companyInfo.detail.indexBanner func="count(@@)"/})</a>
          | <a class = "pb_btn_light_3"href="{tpl:$this.sign/}&ac=step.date.range&company_id={tpl:$companyInfo.company_id/}">健步走日期设置</a>
      </td>
  </tr>
{/tpl:loop}
</table>
    </div>

</fieldset>
{tpl:tpl contentFooter/}

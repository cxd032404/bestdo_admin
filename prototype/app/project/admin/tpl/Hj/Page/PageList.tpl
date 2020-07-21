{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_page').click(function(){
    addPageBox = divBox.showBox('{tpl:$this.sign/}&ac=page.add', {title:'添加页面',width:600,height:450});
  });
});

function pageDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deletePageBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=page.delete&page_id=' + p_id;}});
}

function pageModify(mid){
  modifyPageBox = divBox.showBox('{tpl:$this.sign/}&ac=page.modify&page_id=' + mid, {title:'修改页面',width:600,height:450});
}

</script>

<fieldset><legend>操作</legend>
[ <a class = "pb_btn_dark_1" href="javascript:;" id="add_page">新增</a> ]
</fieldset>

<fieldset><legend>页面列表 </legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
<select name="company_id"  id="company_id" size="1">
      <option value="0"{tpl:if(0==$company_id)}selected="selected"{/tpl:if} >全部</option>
      {tpl:loop $companyList  $company_info}
      <option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
      {/tpl:loop}
    </select>
  <input type="image" name="submit" value="查询" src="/icon/search.png" width='30' height='30'/>
</form>
    <div style="height: auto;overflow: scroll !important;width: 60%;">
        <table  align="center" class="table table-bordered table-striped" style="overflow: scroll;max-width: none;width: 1600px;">
  <tr>
    <th align="center" class="rowtip">页面ID</th>
    <th align="center" class="rowtip">页面名称</th>
    <th align="center" class="rowtip">页面URL</th>
    <th align="center" class="rowtip">页面标识</th>
    <th align="center" class="rowtip">对应企业</th>
    <th align="center" class="rowtip">更新时间</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $pageList $pageInfo}
  <tr class="hover">
    <td align="center">{tpl:$pageInfo.page_id/}</td>
    <td align="center">{tpl:$pageInfo.page_name/}</td>
    <td align="center">{tpl:$pageInfo.page_url/}</td>
      <td align="center">{tpl:$pageInfo.page_sign/}</td>
      <td align="center">{tpl:$pageInfo.company_name/}</td>
    <td align="center">{tpl:$pageInfo.update_time/}</td>
      <td align="center"><a class="pb_btn_grey_1" href="javascript:;" onclick="pageDelete('{tpl:$pageInfo.page_id/}','{tpl:$pageInfo.page_name/}')">删除</a>
 |  <a class = "pb_btn_light_1" href="javascript:;" onclick="pageModify('{tpl:$pageInfo.page_id/}');">修改</a> | <a class = "pb_btn_grey_2" href="{tpl:$this.sign/}&ac=page.detail&page_id={tpl:$pageInfo.page_id/}">页面元素 ({tpl:$pageInfo.element_count/})</a> | <a class = "pb_btn_grey_1" target="_blank" href="{tpl:$pageInfo.test_url/}?{tpl:$pageInfo.page_params/}">测试页面</a></td>
  </tr>
{/tpl:loop}
</table>
    </div>
</fieldset>
{tpl:tpl contentFooter/}

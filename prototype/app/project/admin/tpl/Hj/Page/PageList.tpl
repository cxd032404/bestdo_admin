{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_page').click(function(){
    addPageBox = divBox.showBox('{tpl:$this.sign/}&ac=page.add', {title:'添加页面',width:600,height:300});
  });
});

function pageDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deletePageBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=page.delete&page_id=' + p_id;}});
}

function pageModify(mid){
  modifyPageBox = divBox.showBox('{tpl:$this.sign/}&ac=page.modify&page_id=' + mid, {title:'修改页面',width:600,height:350});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_page">添加页面</a> ]
</fieldset>

<fieldset><legend>页面列表 </legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
<select name="company_id"  id="company_id" size="1">
      <option value="0"{tpl:if(0==$company_id)}selected="selected"{/tpl:if} >全部</option>
      {tpl:loop $companyList  $company_info}
      <option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
      {/tpl:loop}
    </select>
  <input type="submit" name="Submit" value="查询" />
</form>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">页面ID</th>
    <th align="center" class="rowtip">页面名称</th>
    <th align="center" class="rowtip">页面URL</th>
    <th align="center" class="rowtip">对应企业</th>
    <th align="center" class="rowtip">更新时间</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $pageList $pageInfo}
  <tr class="hover">
    <td align="center">{tpl:$pageInfo.page_id/}</td>
    <td align="center">{tpl:$pageInfo.page_name/}</td>
    <td align="center">{tpl:$pageInfo.page_url/}</td>
    <td align="center">{tpl:$pageInfo.company_name/}</td>
    <td align="center">{tpl:$pageInfo.update_time/}</td>
      <td align="center"><a  href="javascript:;" onclick="pageDelete('{tpl:$pageInfo.page_id/}','{tpl:$pageInfo.page_name/}')">删除</a>
 |  <a href="javascript:;" onclick="pageModify('{tpl:$pageInfo.page_id/}');">修改</a> |  <a href="{tpl:$this.sign/}&ac=page.detail&page_id={tpl:$pageInfo.page_id/}">页面元素 ({tpl:$pageInfo.element_count/})</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

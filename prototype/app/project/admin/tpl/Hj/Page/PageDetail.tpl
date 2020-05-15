{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_page_element').click(function(){
    addPageElementBox = divBox.showBox('{tpl:$this.sign/}&ac=page.element.add&page_id='+$('#page_id').val(), {title:'添加元素到'+$('#page_name').val(),width:400,height:300});
  });
});

function pageElementDelete(e_id,e_name){
    msg = '是否删除 ' + e_name + '?'
  deletePageElementBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=page.element.delete&element_id=' + e_id;}});
}

function pageElementModify(e_id){
  modifyPageBox = divBox.showBox('{tpl:$this.sign/}&ac=page.element.modify&element_id=' + e_id, {title:'修改页面元素',width:600,height:350});
}

</script>
<fieldset>
[ <a href="{tpl:$this.sign/}&company_id={tpl:$pageInfo.company_id/}">返回</a> | <a href="javascript:;" id="add_page_element">添加页面元素</a> ]
</fieldset>
<input type="hidden" id="page_id" name="page_id" value="{tpl:$pageInfo.page_id/}" />
<input type="hidden" id="page_name" name="page_name" value="{tpl:$pageInfo.page_name/}" />

<fieldset><legend>页面{tpl:$pageInfo.page_name/}元素列表</legend></fieldset>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">元素名称</th>
    <th align="center" class="rowtip">元素标识</th>
    <th align="center" class="rowtip">元素类型</th>
    <th align="center" class="rowtip">更新时间</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $pageElementList $elementInfo}
  <tr class="hover">
    <td align="center">{tpl:$elementInfo.element_name/}</td>
    <td align="center">{tpl:$elementInfo.element_sign/}</td>
    <td align="center">{tpl:$elementInfo.element_type_name/}</td>
    <td align="center">{tpl:$elementInfo.update_time/}</td>
      <td align="center"><a  href="javascript:;" onclick="pageElementDelete('{tpl:$elementInfo.element_id/}','{tpl:$elementInfo.element_name/}')">删除</a>
 |  <a href="javascript:;" onclick="pageElementModify('{tpl:$elementInfo.element_id/}');">修改</a>
        {tpl:if($elementInfo.editable==1)}
        |  <a href="{tpl:$this.sign/}&ac=page.element.detail&element_id={tpl:$elementInfo.element_id/}">元素详情</a>
        {/tpl:if}
      </td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

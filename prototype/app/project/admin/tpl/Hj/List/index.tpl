{tpl:tpl contentHeader/}
<script type="text/javascript">
    $(document).ready(function(){
        $('#add_list').click(function(){
            addListBox = divBox.showBox('{tpl:$this.sign/}&ac=list.add'+ '&currentPage='+$('#currentPage').val(), {title:'添加列表',width:600,height:600});
        });
    });

function listDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deleteactivityBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=list.delete&list_id=' + p_id+ '&currentPage='+$('#currentPage').val();}});
}

function listModify(lid){
  modifyactivityBox = divBox.showBox('{tpl:$this.sign/}&ac=list.modify&list_id=' + lid+ '&currentPage='+$('#currentPage').val(), {title:'修改列表',width:600,height:700});
}

function post(lid){
  postBox = divBox.showBox('{tpl:$this.sign/}&ac=post&list_id=' + lid+ '&currentPage='+$('#currentPage').val(), {title:'提交',width:600,height:800});
}

</script>

<fieldset><legend>操作</legend>
    <div align=right><a class = "pb_btn_dark_1" href="javascript:;" id="add_list">新增</a></div>
</fieldset>

<fieldset><legend>列表</legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
    <input type="hidden" name="currentPage" id="currentPage" value="{tpl:$currentPage/}" />
<select name="company_id"  id="company_id" size="1">
      <option value="0"{tpl:if(0==$company_id)}selected="selected"{/tpl:if} >全部</option>
      {tpl:loop $companyList  $company_info}
      <option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
      {/tpl:loop}
    </select>

  <select name="list_type"  id="list_type" size="1">
    <option value="0"{tpl:if(0==$list_type)}selected="selected"{/tpl:if} >全部</option>
    {tpl:loop $listTypeList  $type $type_info}
    <option value="{tpl:$type/}"{tpl:if($type==$list_type)}selected="selected"{/tpl:if} >{tpl:$type_info.name/}</option>
    {/tpl:loop}
  </select>
  <button type="submit" class="pb_btn_light_1">搜索</button>
</form>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">列表ID</th>
    <th align="center" class="rowtip">列表名称</th>
    <th align="center" class="rowtip">对应企业</th>
      <th align="center" class="rowtip">对应活动</th>
      <th align="center" class="rowtip">列表分类</th>
    <th align="center" class="rowtip">更新时间</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $ListList.ListList $listInfo}
  <tr class="hover">
    <td align="center">{tpl:$listInfo.list_id/}</td>
    <td align="center">{tpl:$listInfo.list_name/}</td>
      <td align="center">{tpl:$listInfo.company_name/}</td>
      <td align="center">{tpl:$listInfo.activity_name/}</td>

      <td align="center">{tpl:$listInfo.list_type_name/}</td>

      <td align="center">{tpl:$listInfo.update_time/}</td>
      <td align="center"><a class = "pb_btn_grey_1" href="javascript:;" onclick="listDelete('{tpl:$listInfo.list_id/}','{tpl:$listInfo.list_name/}')">删除</a>
   <a class = "pb_btn_light_1" href="javascript:;" onclick="listModify('{tpl:$listInfo.list_id/}');">修改</a>
           <a class = "pb_btn_grey_1" href="{tpl:$this.sign/}&ac=list&list_id={tpl:$listInfo.list_id/}&currentPage={tpl:$currentPage/}">列表{tpl:if($listInfo.posts_count>0)}({tpl:$listInfo.posts_count/}){/tpl:if} </a></td>
  </tr>
{/tpl:loop}
    <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>

</table>
</fieldset>
{tpl:tpl contentFooter/}

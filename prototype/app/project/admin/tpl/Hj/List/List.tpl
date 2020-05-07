{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_list').click(function(){
    addactivityBox = divBox.showBox('{tpl:$this.sign/}&ac=activity.add', {title:'添加活动',width:600,height:500});
  });
});

function activityDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deleteactivityBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=activity.delete&activity_id=' + p_id;}});
}

function activityModify(mid){
  modifyactivityBox = divBox.showBox('{tpl:$this.sign/}&ac=activity.modify&activity_id=' + mid, {title:'修改活动',width:600,height:500});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_list">添加列表</a> ]
</fieldset>

<fieldset><legend>活动列表 </legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
<select name="company_id"  id="company_id" size="1">
      <option value="0"{tpl:if(0==$company_id)}selected="selected"{/tpl:if} >全部</option>
      {tpl:loop $companyList  $company_info}
      <option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
      {/tpl:loop}
    </select>

  <select name="list_type"  id="list_type" size="1">
    <option value="0"{tpl:if(0==$list_type)}selected="selected"{/tpl:if} >全部</option>
    {tpl:loop $listTypeList  $type $name}
    <option value="{tpl:$type/}"{tpl:if($type==$list_type)}selected="selected"{/tpl:if} >{tpl:$name/}</option>
    {/tpl:loop}
  </select>
  <input type="submit" name="Submit" value="查询" />
</form>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">活动ID</th>
    <th align="center" class="rowtip">活动名称</th>
    <th align="center" class="rowtip">活动标识</th>
    <th align="center" class="rowtip">对应企业</th>
    <th align="center" class="rowtip">活动时间</th>
    <th align="center" class="rowtip">报名时间</th>
    <th align="center" class="rowtip">更新时间</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $listList $listInfo}
  <tr class="hover">
    <td align="center">{tpl:$listInfo.activity_id/}</td>
    <td align="center">{tpl:$listInfo.activity_name/}</td>
      <td align="center">{tpl:$listInfo.activity_sign/}</td>
      <td align="center">{tpl:$listInfo.company_name/}</td>
      <td align="center">{tpl:$listInfo.start_time/}<P>{tpl:$listInfo.end_time/}</td>
      <td align="center">{tpl:$listInfo.apply_start_time/}<P>{tpl:$listInfo.apply_end_time/}</td>
      <td align="center">{tpl:$listInfo.update_time/}</td>
      <td align="center"><a  href="javascript:;" onclick="activityDelete('{tpl:$listInfo.activity_id/}','{tpl:$listInfo.activity_name/}')">删除</a>
 |  <a href="javascript:;" onclick="activityModify('{tpl:$listInfo.activity_id/}');">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

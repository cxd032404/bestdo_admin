{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_activity').click(function(){
    addactivityBox = divBox.showBox('{tpl:$this.sign/}&ac=activity.add', {title:'添加活动',width:600,height:500});
  });
});

function activityDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deleteactivityBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=activity.delete&activity_id=' + p_id;}});
}

function activityModify(mid){
  modifyactivityBox = divBox.showBox('{tpl:$this.sign/}&ac=activity.modify&activity_id=' + mid, {title:'修改活动',width:600,height:800});
}

</script>

<fieldset><legend>操作</legend>
 <a class = "pb_btn_dark_1" href="javascript:;" id="add_activity">新增</a>
</fieldset>

<fieldset><legend>活动列表 </legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
<select name="company_id"  id="company_id" size="1">
      <option value="0"{tpl:if(0==$company_id)}selected="selected"{/tpl:if} >全部</option>
      {tpl:loop $companyList  $company_info}
      <option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
      {/tpl:loop}
    </select>
  <button type="submit" class="pb_btn_light_1">搜索</button>
</form>
  <div class="ifm_all">
    <table  align="center" class="table table-bordered table-striped" style="overflow: scroll;max-width: none;width: 2200px;">
      <tr>
    <th align="center" class="rowtip">活动ID</th>
    <th align="center" class="rowtip">活动名称</th>
    <th align="center" class="rowtip">对应企业</th>
    <th align="center" class="rowtip">关联俱乐部</th>
    <th align="center" class="rowtip">创建者</th>
      <th align="center" class="rowtip">仅俱乐部成员</th>
      <th align="center" class="rowtip">人数限制</th>
      <th align="center" class="rowtip">活动时间</th>
    <th align="center" class="rowtip">报名时间</th>
    <th align="center" class="rowtip">更新时间</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $activityList.ActivityList $activityInfo}
  <tr class="hover">
    <td align="center">{tpl:$activityInfo.activity_id/}</td>
    <td align="center">{tpl:$activityInfo.activity_name/}</td>
      <td align="center">{tpl:$activityInfo.company_name/}</td>
    <td align="center">{tpl:$activityInfo.club_name/}</td>
    <td align="center">{tpl:$activityInfo.create_user_name/}</td>
      <td align="center">{tpl:if($activityInfo.club_member_only==1)}是{tpl:else}否{/tpl:if}</td>
      <td align="center">{tpl:$activityInfo.member_limit/}</td>


      <td align="center">{tpl:$activityInfo.start_time/}<P>{tpl:$activityInfo.end_time/}</td>
      <td align="center">{tpl:$activityInfo.apply_start_time/}<P>{tpl:$activityInfo.apply_end_time/}</td>
      <td align="center">{tpl:$activityInfo.update_time/}</td>

      <td align="center"><a class = "pb_btn_grey_1" href="javascript:;" onclick="activityDelete('{tpl:$activityInfo.activity_id/}','{tpl:$activityInfo.activity_name/}')">删除</a>
  <a class = "pb_btn_light_1" href="javascript:;" onclick="activityModify('{tpl:$activityInfo.activity_id/}');">修改</a>  <a class = "pb_btn_light_2" href="{tpl:$this.sign/}&ac=activity.log&activity_id={tpl:$activityInfo.activity_id/}">报名记录{tpl:if($activityInfo.count>0)}({tpl:$activityInfo.count/}){/tpl:if}</a>{tpl:if($activityInfo.download == 1)}<a href="{tpl:$this.sign/}&ac=activity.download&activity_id={tpl:$activityInfo.activity_id/}">下载</a>{/tpl:if}</td>
  </tr>
{/tpl:loop}
  <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>
</table>
  </div>

</fieldset>
{tpl:tpl contentFooter/}

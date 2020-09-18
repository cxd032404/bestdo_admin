{tpl:tpl contentHeader/}
<script type="text/javascript">
function scheduleDelete(p_id, p_name){
	deleteScheduleBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=schedule.delete&id=' + p_id;}});
}

function scheduleModify(sid){
	modifyScheduleBox = divBox.showBox('{tpl:$this.sign/}&ac=schedule.modify&id=' + sid, {title:'修改赛程',width:420,height:300});
}
</script>

<fieldset><legend>操作</legend>
    <span style="float:left;"><a class = "pb_btn_light_1"  href="{tpl:$this.sign/}">返回</a></span>
</fieldset>

<fieldset><legend>赛程列表</legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
      <th align="center" class="rowtip">阶段</th>
      <th align="center" class="rowtip">赛程ID</th>
    <th align="center" class="rowtip">比赛名称</th>
      <th align="center" class="rowtip">场地</th>
      <th align="center" class="rowtip">时间</th>
      <th align="center" class="rowtip">对阵</th>
      <th align="center" class="rowtip">更新</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $scheduleList $scheduleInfo}
  <tr class="hover">
      {tpl:loop $phaseCount $phase $phase_info}
      {tpl:if($phase_info.start==$scheduleInfo.id)}<td rowspan="{tpl:$phase_info.count/}" align="center">第{tpl:$phase/}阶段</td>{/tpl:if}
      {/tpl:loop}
      <td align="center">{tpl:$scheduleInfo.id/}</td>
      <td align="center">{tpl:$scheduleInfo.match_name/}</td>
      <td align="center">{tpl:$scheduleInfo.place_name/}</td>
      <td align="center">{tpl:$scheduleInfo.start_time/}<p>{tpl:$scheduleInfo.end_time/}</td>
      <td align="center">{tpl:$scheduleInfo.vs/}</td>
      <td align="center">{tpl:$scheduleInfo.update_time/}</td>
      </td>
      <td align="center"><a class = "pb_btn_grey_1" href="javascript:;" onclick="scheduleDelete('{tpl:$scheduleInfo.id/}','{tpl:$scheduleInfo.schedule_name/}')">删除</a>
          <a class = "pb_btn_light_1" href="javascript:;" onclick="scheduleModify('{tpl:$scheduleInfo.id/}');">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_race').click(function(){
		addRaceBox = divBox.showBox('{tpl:$this.sign/}&ac=race.add', {title:'添加赛事',width:400,height:300});
	});
});

function raceDelete(p_id, p_name){
	deleteRaceBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=race.delete&race_id=' + p_id;}});
}

function raceModify(sid){
	modifyRaceBox = divBox.showBox('{tpl:$this.sign/}&ac=race.modify&race_id=' + sid, {title:'修改赛事',width:400,height:300});
}

function reSchedule(sid){
    scheduleBox = divBox.confirmBox({content:'重新创建赛程将清除之前所有的比赛并重新创建，是否确定?',ok:function(){location.href = '{tpl:$this.sign/}&ac=re.schedule&race_id='+sid;}});
}

</script>

<fieldset><legend>操作</legend>
    <div align=right><a class = "pb_btn_dark_1" href="javascript:;" id="add_race">新增</a></div>
</fieldset>

<fieldset><legend>赛程列表</legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
      <th align="center" class="rowtip">阶段</th>
      <th align="center" class="rowtip">赛程ID</th>
    <th align="center" class="rowtip">比赛名称</th>
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
      <td align="center">{tpl:$scheduleInfo.start_time/}<p>{tpl:$scheduleInfo.end_time/}</td>
      <td align="center">{tpl:$scheduleInfo.vs/}</td>
      <td align="center">{tpl:$scheduleInfo.update_time/}</td>
      </td>
      <td align="center"><a class = "pb_btn_grey_1" href="javascript:;" onclick="raceDelete('{tpl:$RaceInfo.race_id/}','{tpl:$RaceInfo.race_name/}')">删除</a>
          <a class = "pb_btn_light_1" href="javascript:;" onclick="raceModify('{tpl:$RaceInfo.race_id/}');">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

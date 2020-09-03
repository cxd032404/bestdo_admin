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

</script>

<fieldset><legend>操作</legend>
    <span style="float:left;"><a class = "pb_btn_light_1"  href="{tpl:$this.sign/}">返回</a></span>
    <span style="float:right;"><a class = "pb_btn_dark_1"  href="javascript:;" id="add_race">新增</a></span>
</fieldset>

<fieldset><legend>队伍列表</legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">队伍ID</th>
    <th align="center" class="rowtip">队伍名称</th>
      <th align="center" class="rowtip">分组</th>
      <th align="center" class="rowtip">添加时间</th>
      <th align="center" class="rowtip">更新时间</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $teamList $team_id $team_info}
  <tr class="hover">
    <td align="center">{tpl:$team_info.team_id/}</td>
    <td align="center">{tpl:$team_info.team_name/}</td>
      <td align="center">{tpl:$team_info.group/}</td>
      <td align="center">{tpl:$team_info.create_time/}</td>
    <td align="center">{tpl:$team_info.update_time/}</td>
      </td>
      <td align="center"><a class = "pb_btn_grey_1" href="javascript:;" onclick="raceDelete('{tpl:$RaceInfo.race_id/}','{tpl:$RaceInfo.race_name/}')">删除</a><a class = "pb_btn_light_1" href="javascript:;" onclick="raceModify('{tpl:$RaceInfo.race_id/}');">修改</a>
          <a class = "pb_btn_grey_1" href="{tpl:$this.sign/}&ac=member.list&race_id={tpl:$RaceInfo.race_id/}">{tpl:if($RaceInfo.team==1)}团队列表{tpl:else}选手列表{/tpl:if}</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

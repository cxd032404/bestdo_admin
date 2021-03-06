{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_team').click(function(){
		addTeamBox = divBox.showBox('{tpl:$this.sign/}&ac=team.add', {title:'添加队伍',width:400,height:300});
	});
});

function teamDelete(p_id, p_name){
	deleteTeamBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=member.delete&id=' + p_id;}});
}

function teamModify(sid){
	modifyTeamBox = divBox.showBox('{tpl:$this.sign/}&ac=member.modify&race_id={tpl:$RaceId/}&id=' + sid, {title:'修改队伍',width:400,height:300});
}

function uploadMember(){
  uploadUserBox = divBox.showBox("{tpl:$this.sign/}&ac=race.member.upload.submit&race_id={tpl:$RaceId/}", {title:'导入队伍',width:600,height:300});
}
function reGroup(){
  reGroupBox = divBox.confirmBox({content:'重新分组将清除之前所有的分组并重新创建，是否确定?',ok:function(){location.href = '{tpl:$this.sign/}&ac=re.group&race_id={tpl:$RaceId/}';}});
}

</script>

<fieldset><legend>操作</legend>
  <span style="float:left;"><a class = "pb_btn_light_1"  href="{tpl:$this.sign/}">返回</a>
    <a class = "pb_btn_grey_1" href="javascript:;" onclick="reGroup()">自动分组</a></span>
  <span style="float:right;"><a class = "pb_btn_dark_1"  href="javascript:;" id="add_team">新增</a></span>
    <span style="float:right;">{tpl:$export_var/}</span>
    <span style="float:right;"><a class = "pb_btn_dark_2 raceMemberUpload" href="javascript:;" onclick="uploadMember()" >导入队伍</a></span>
</fieldset>

<fieldset><legend>队伍列表</legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">队伍ID</th>
    <th align="center" class="rowtip">队伍名称</th>
      <th align="center" class="rowtip">分组</th>
      <th align="center" class="rowtip">种子</th>
      <th align="center" class="rowtip">添加时间</th>
      <th align="center" class="rowtip">更新时间</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $teamList $team_id $team_info}
  <tr class="hover">
    <td align="center">{tpl:$team_info.team_id/}</td>
    <td align="center">{tpl:$team_info.team_name/}</td>
      <td align="center">{tpl:$team_info.group/}</td>
      <td align="center">{tpl:$team_info.seed/}</td>
      <td align="center">{tpl:$team_info.create_time/}</td>
    <td align="center">{tpl:$team_info.update_time/}</td>
      </td>
      <td align="center"><a class = "pb_btn_grey_1" href="javascript:;" onclick="teamDelete('{tpl:$team_info.team_id/}','{tpl:$team_info.team_name/}')">删除</a><a class = "pb_btn_light_1" href="javascript:;" onclick="teamModify('{tpl:$team_info.team_id/}');">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

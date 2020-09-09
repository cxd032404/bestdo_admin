{tpl:tpl contentHeader/}
<script type="text/javascript">
  $(document).ready(function(){
    $('#add_athlete').click(function(){
      addAthleteBox = divBox.showBox('{tpl:$this.sign/}&ac=athlete.add', {title:'添加队伍',width:400,height:300});
    });
  });

  function athleteDelete(p_id, p_name){
    deleteAthleteBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=member.delete&id=' + p_id;}});
  }

  function athleteModify(sid){
    modifyAthleteBox = divBox.showBox('{tpl:$this.sign/}&ac=member.modify&race_id={tpl:$RaceId/}&id=' + sid, {title:'修改队伍',width:400,height:300});
  }

</script>

<fieldset><legend>操作</legend>
    <span style="float:left;"><a class = "pb_btn_light_1"  href="{tpl:$this.sign/}">返回</a></span>
    <span style="float:right;"><a class = "pb_btn_dark_1"  href="javascript:;" id="add_race">新增</a></span>
    <span style="float:right;">{tpl:$export_var/}</span>
    <span style="float:right;"><a class = "pb_btn_dark_2 raceMemberUpload" href="javascript:;"  race_id = {tpl:$RaceId/}>导入选手</a></span>
</fieldset>

<fieldset><legend>选手</legend>
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

{tpl:loop $atheleteList $athlete_id $athlete_info}
  <tr class="hover">
    <td align="center">{tpl:$athlete_info.athlete_id/}</td>
    <td align="center">{tpl:$athlete_info.athlete_name/}</td>
      <td align="center">{tpl:$athlete_info.group/}</td>
      <td align="center">{tpl:$athlete_info.seed/}</td>
      <td align="center">{tpl:$athlete_info.create_time/}</td>
    <td align="center">{tpl:$athlete_info.update_time/}</td>
      </td>
    <td align="center"><a class = "pb_btn_grey_1" href="javascript:;" onclick="athleteDelete('{tpl:$athlete_info.athlete_id/}','{tpl:$athlete_info.athlete_name/}')">删除</a><a class = "pb_btn_light_1" href="javascript:;" onclick="athleteModify('{tpl:$athlete_info.athlete_id/}');">修改</a></td>

  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}
<script type="text/javascript">
  $(document).ready(function(){
    $('.raceMemberUpload').click(function(){
      race_id = $(this).attr('race_id');
      uploadUserBox = divBox.showBox("{tpl:$this.sign/}&ac=race.member.upload.submit&race_id="+race_id, {title:'导入选手',width:600,height:300});
    });
  });
</script>

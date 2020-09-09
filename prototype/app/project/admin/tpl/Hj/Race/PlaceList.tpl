{tpl:tpl contentHeader/}
<script type="text/javascript">
  $(document).ready(function(){
    $('#add_place').click(function(){
      addAthleteBox = divBox.showBox('{tpl:$this.sign/}&ac=place.add&race_id='+{tpl:$race_id/}, {title:'添加场地',width:400,height:300});
    });
  });

  function placeDelete(p_id, p_name){
    deleteAthleteBox = divBox.confirmBox({content:'是否删除 '+ p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=place.delete&place_id=' + p_id;}});
  }

  function placeModify(sid){
    modifyAthleteBox = divBox.showBox('{tpl:$this.sign/}&ac=place.modify&place_id={tpl:/}&place_id=' + sid, {title:'修改场地',width:400,height:300});
  }

</script>

<fieldset><legend>操作</legend>
    <span style="float:left;"><a class = "pb_btn_light_1"  href="{tpl:$this.sign/}">返回</a></span>
    <span style="float:right;"><a class = "pb_btn_dark_1"  href="javascript:;" id="add_place">新增</a></span>
</fieldset>

<fieldset><legend>选手</legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">场地ID</th>
    <th align="center" class="rowtip">场馆名称</th>
      <th align="center" class="rowtip">赛事</th>
      <th align="center" class="rowtip">添加时间</th>
      <th align="center" class="rowtip">更新时间</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $place_list $aplace_id $place_info}
  <tr class="hover">
    <td align="center">{tpl:$place_info.place_id/}</td>
    <td align="center">{tpl:$place_info.place_name/}</td>
      <td align="center">{tpl:$race_name/}</td>
      <td align="center">{tpl:$place_info.create_time/}</td>
    <td align="center">{tpl:$place_info.update_time/}</td>
      </td>
    <td align="center"><a class = "pb_btn_grey_1" href="javascript:;" onclick="placeDelete('{tpl:$place_info.place_id/}','{tpl:$place_info.place_name/}')">删除</a><a class = "pb_btn_light_1" href="javascript:;" onclick="placeModify('{tpl:$place_info.place_id/}');">修改</a></td>

  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}


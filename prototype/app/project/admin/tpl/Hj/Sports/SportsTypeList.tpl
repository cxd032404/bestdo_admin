{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_app').click(function(){
		addAppBox = divBox.showBox('{tpl:$this.sign/}&ac=sports.type.add', {title:'添加运动类型',width:400,height:300});
	});
});

function sportsTypeDelete(p_id, p_name){
	deleteAppBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=sports.type.delete&SportsTypeId=' + p_id;}});
}

function sportsTypeModify(sid){
	modifySportsTypeBox = divBox.showBox('{tpl:$this.sign/}&ac=sports.type.modify&SportsTypeId=' + sid, {title:'修改运动类型',width:400,height:300});
}

</script>

<fieldset><legend>操作</legend>
    <div align=right><a class = "pb_btn_dark_1" href="javascript:;" id="add_app">新增</a></div>
</fieldset>

<fieldset><legend>运动类型列表</legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">运动类型ID</th>
    <th align="center" class="rowtip">运动类型名称</th>
    <th align="center" class="rowtip">速度显示类型</th>
      <th align="center" class="rowtip">决胜方式</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $SportTypeList $SportsTypeInfo}
  <tr class="hover">
    <td align="center">{tpl:$SportsTypeInfo.SportsTypeId/}</td>
    <td align="center">{tpl:$SportsTypeInfo.SportsTypeName/}</td>
    <td align="center">{tpl:if($SportsTypeInfo.SpeedDisplayType=="0")}不显示{tpl:else}{tpl:$SportsTypeInfo.SpeedDisplayType/}{/tpl:if}</td>
      <td align="center">{tpl:$SportsTypeInfo.winBy/}-{tpl:$SportsTypeInfo.winWith/}</td>
      <td align="center"><a class = "pb_btn_grey_1" href="javascript:;" onclick="sportsTypeDelete('{tpl:$SportsTypeInfo.SportsTypeId/}','{tpl:$SportsTypeInfo.SportsTypeName/}')">删除</a><a class = "pb_btn_light_1" href="javascript:;" onclick="sportsTypeModify('{tpl:$SportsTypeInfo.SportsTypeId/}');">修改</a><a class = "pb_btn_dark_1" href="{tpl:$this.sign/}&ac=section&SportsTypeId={tpl:$SportsTypeInfo.SportsTypeId/}">分段</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_section').click(function(){
		addAppBox = divBox.showBox('{tpl:$this.sign/}&ac=section.add&SportsTypeId={tpl:$SportsTypeId/}', {title:'添加分段',width:400,height:300});
	});
});

function sectionDelete(sid,pos,p_name){
	deleteSexBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=section.delete&SportsTypeId=' + sid + '&pos=' + pos;}});
}

function sectionModify(sid,pos,name){
	modifySectionBox = divBox.showBox('{tpl:$this.sign/}&ac=section.modify&SportsTypeId=' + sid + '&pos='+pos, {title:'修改分段-'+name,width:400,height:300});
}

</script>

<fieldset><legend>操作</legend>
    <div align=right><a class = "pb_btn_dark_1" href="javascript:;" id="add_section">新增</a></div>
</fieldset>

<fieldset><legend>分段列表</legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">分段ID</th>
    <th align="center" class="rowtip">名称</th>
    <th align="center" class="rowtip">时间</th>
      <th align="center" class="rowtip">是否附加</th>
      <th align="center" class="rowtip">是否决胜</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $SportsTypeInfo.comment.section $pos $SectionInfo}
  <tr class="hover">
    <td align="center">{tpl:$pos/}</td>
    <td align="center">{tpl:$SectionInfo.name/}</td>
      <td align="center">{tpl:$SectionInfo.time/}</td>
      <td align="center">{tpl:if($SectionInfo.additional=="0")}否{tpl:else}是{/tpl:if}</td>
      <td align="center">{tpl:if($SectionInfo.win=="0")}否{tpl:else}是{/tpl:if}</td>
      <td align="center"><a class = "pb_btn_grey_1" href="javascript:;" onclick="sectionDelete('{tpl:$SportsTypeId/}','{tpl:$SectionInfo.pos/}','{tpl:$SectionInfo.name/}')">删除</a><a class = "pb_btn_light_1" href="javascript:;" onclick="sectionModify('{tpl:$SportsTypeId/}','{tpl:$pos/}','{tpl:$SectionInfo.name/}');">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

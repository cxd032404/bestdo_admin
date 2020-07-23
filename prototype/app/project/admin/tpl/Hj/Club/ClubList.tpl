{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_club').click(function(){
    addclubBox = divBox.showBox('{tpl:$this.sign/}&ac=club.add', {title:'添加俱乐部',width:600,height:500});
  });
});

function clubDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deleteclubBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=club.delete&club_id=' + p_id;}});
}

function clubModify(mid){
  modifyclubBox = divBox.showBox('{tpl:$this.sign/}&ac=club.modify&club_id=' + mid, {title:'修改俱乐部',width:600,height:500});
}

</script>

<fieldset><legend>操作</legend>
    <div align=right><a class="pb_btn_dark_2" href="javascript:;" id="add_club">+添加俱乐部</a></div>
</fieldset>

<fieldset><legend>俱乐部列表 </legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
<select name="company_id"  id="company_id" size="1">
      <option value="0"{tpl:if(0==$company_id)}selected="selected"{/tpl:if} >全部</option>
      {tpl:loop $companyList  $company_info}
      <option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
      {/tpl:loop}
    </select>
  <button type="submit" class="pb_btn_light_1">搜索</button>
</form>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">俱乐部ID</th>
    <th align="center" class="rowtip">俱乐部名称</th>
    <th align="center" class="rowtip">对应企业</th>
    <th align="center" class="rowtip">人数限制</th>
    <th align="center" class="rowtip">是否允许加入</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $clubList $clubInfo}
  <tr class="hover">
    <td align="center">{tpl:$clubInfo.club_id/}</td>
    <td align="center">{tpl:$clubInfo.club_name/}</td>
      <td align="center">{tpl:$clubInfo.company_name/}</td>
      <td align="center">{tpl:$clubInfo.member_limit/}</td>
      <td align="center">{tpl:if($clubInfo.allow_enter==1)}允许{tpl:else}拒绝{/tpl:if}</td>
      <td align="center"><a class="pb_btn_grey_1" href="javascript:;" onclick="clubDelete('{tpl:$clubInfo.club_id/}','{tpl:$clubInfo.club_name/}')">删除</a>
  <a class="pb_btn_light_1"  href="javascript:;" onclick="clubModify('{tpl:$clubInfo.club_id/}');">修改</a>
           <a class="pb_btn_grey_1" href="{tpl:$this.sign/}&ac=member.list&club_id={tpl:$clubInfo.club_id/}">名单{tpl:if($clubInfo.member_count>0)}({tpl:$clubInfo.member_count/}){/tpl:if}</a>
           <a class="pb_btn_grey_1" href="{tpl:$this.sign/}&ac=member.log&club_id={tpl:$clubInfo.club_id/}">记录</a>
           <a class="pb_btn_light_2" href="{tpl:$this.sign/}&ac=banner&club_id={tpl:$clubInfo.club_id/}">Banner({tpl:$clubInfo.detail.banner func="count(@@)"/})</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

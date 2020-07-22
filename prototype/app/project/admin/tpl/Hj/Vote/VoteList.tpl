{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_vote').click(function(){
    addVoteBox = divBox.showBox('{tpl:$this.sign/}&ac=vote.add', {title:'添加投票',width:600,height:500});
  });
});

function voteDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deleteVoteBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=vote.delete&vote_id=' + p_id;}});
}

function voteModify(mid){
  modifyVoteBox = divBox.showBox('{tpl:$this.sign/}&ac=vote.modify&vote_id=' + mid, {title:'修改投票',width:600,height:500});
}

</script>

<fieldset><legend>操作</legend>
[ <a class="pb_btn_dark_1" href="javascript:;" id="add_vote">新增</a> ]
</fieldset>

<fieldset><legend>投票列表 </legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
<select name="activity_id"  id="activity_id" size="1">
      <option value="0"{tpl:if(0==$activity_id)}selected="selected"{/tpl:if} >全部</option>
      {tpl:loop $activityList.ActivityList  $activity_info}
      <option value="{tpl:$activity_info.activity_id/}"{tpl:if($activity_info.activity_id==$activity_id)}selected="selected"{/tpl:if} >{tpl:$activity_info.activity_name/}</option>
      {/tpl:loop}
    </select>
    <button type="submit" class="pb_btn_light_1">搜索</button>

    </form>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">投票ID</th>
    <th align="center" class="rowtip">投票名称</th>
    <th align="center" class="rowtip">投票标识</th>
    <th align="center" class="rowtip">对应活动</th>
    <th align="center" class="rowtip">投票时间</th>
    <th align="center" class="rowtip">更新时间</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $voteList $voteInfo}
  <tr class="hover">
    <td align="center">{tpl:$voteInfo.vote_id/}</td>
    <td align="center">{tpl:$voteInfo.vote_name/}</td>
      <td align="center">{tpl:$voteInfo.vote_sign/}</td>
      <td align="center">{tpl:$voteInfo.activity_name/}</td>
      <td align="center">{tpl:$voteInfo.start_time/}<P>{tpl:$voteInfo.end_time/}</td>
      <td align="center">{tpl:$voteInfo.update_time/}</td>
      <td align="center"><a  class="pb_btn_grey_1" href="javascript:;" onclick="voteDelete('{tpl:$voteInfo.vote_id/}','{tpl:$voteInfo.vote_name/}')">删除</a>
 |  <a class="pb_btn_light_1" href="javascript:;" onclick="voteModify('{tpl:$voteInfo.vote_id/}');">修改</a> |  <a class="pb_btn_grey_1" href="{tpl:$this.sign/}&ac=vote.detail&vote_id={tpl:$voteInfo.vote_id/}">详情</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

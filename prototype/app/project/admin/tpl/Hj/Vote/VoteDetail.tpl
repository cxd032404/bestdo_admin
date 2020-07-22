{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_vote_option').click(function(){
    addVotOptionBox = divBox.showBox('{tpl:$this.sign/}&ac=vote.option.add&vote_id='+$('#vote_id').val(), {title:'添加投票选项',width:600,height:500});
  });
});

function voteOptionDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deleteVoteOptionBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=vote.option.delete&vote_option_sign=' + p_id + '&vote_id='+$('#vote_id').val();}});
}

function voteOptionModify(mid){
  modifyVoteOptionBox = divBox.showBox('{tpl:$this.sign/}&ac=vote.option.modify&vote_option_sign=' + mid + '&vote_id='+$('#vote_id').val(), {title:'修改投票选项',width:600,height:500});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="{tpl:$this.sign/}&activity_id={tpl:$voteInfo.activity_id/}"><img src="/icon/return.png" width='30' height='30'/></a> ｜ <a href="javascript:;" id="add_vote_option">添加选项</a> ]
</fieldset>

<fieldset><legend>投票选项列表 </legend>
    <form id="vote_update_form" name="vote_update_form" action="{tpl:$this.sign/}&ac=vote.option.update" method="post">
    <input type="hidden" name="vote_id" id="vote_id" value="{tpl:$voteInfo.vote_id/}" />
</form>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">投票选项</th>
    <th align="center" class="rowtip">标示</th>
    <th align="center" class="rowtip">来源</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $voteInfo.detail $optionSign $optionInfo}
  <tr class="hover">
    <td align="center">{tpl:$optionInfo.vote_option_name/}</td>
    <td align="center">{tpl:$optionSign/}</td>
      <td align="center">{tpl:$optionInfo.source_from_name/}</td>
      <td align="center"><a  href="javascript:;" onclick="voteOptionDelete('{tpl:$optionSign/}','{tpl:$optionInfo.vote_option_name/}')"><img src="/icon/del.png" width='30' height='30'/></a>
 |  <a href="javascript:;" onclick="voteOptionModify('{tpl:$optionSign/}');"><img src="/icon/edit2.png" width='30' height='30'/></a> |  <a href="{tpl:$this.sign/}&ac=vote.detail&vote_id={tpl:$voteInfo.vote_id/}">详情</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

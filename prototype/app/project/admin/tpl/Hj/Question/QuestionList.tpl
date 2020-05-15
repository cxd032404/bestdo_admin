{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_question').click(function(){
    addQuestionBox = divBox.showBox('{tpl:$this.sign/}&ac=question.add', {title:'添加提问',width:700,height:700});
  });
});

function questionDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deleteQuestionBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=question.delete&question_id=' + p_id;}});
}

function questionModify(mid){
  modifyQuestionBox = divBox.showBox('{tpl:$this.sign/}&ac=question.modify&question_id=' + mid, {title:'修改提问',width:700,height:700});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_question">添加提问</a> ]
</fieldset>

<fieldset><legend>提问列表 </legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
<select name="activity_id"  id="activity_id" size="1">
      <option value="0"{tpl:if(0==$activity_id)}selected="selected"{/tpl:if} >全部</option>
      {tpl:loop $activityList  $activity_info}
      <option value="{tpl:$activity_info.activity_id/}"{tpl:if($activity_info.activity_id==$activity_id)}selected="selected"{/tpl:if} >{tpl:$activity_info.activity_name/}</option>
      {/tpl:loop}
    </select>
  <input type="submit" name="submit" value="查询" />
</form>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">提问ID</th>
    <th align="center" class="rowtip">提问</th>
    <th align="center" class="rowtip">回答</th>
    <th align="center" class="rowtip">对应活动</th>
    <th align="center" class="rowtip">创建时间</th>
    <th align="center" class="rowtip">更新时间</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $questionList $questionInfo}
  <tr class="hover">
    <td align="center">{tpl:$questionInfo.question_id/}</td>
    <td align="center">{tpl:$questionInfo.question/}</td>
      <td align="center">{tpl:$questionInfo.answer/}</td>
      <td align="center">{tpl:$questionInfo.activity_name/}</td>
      <td align="center">{tpl:$questionInfo.create_time/}</td>
      <td align="center">{tpl:$questionInfo.update_time/}</td>
      <td align="center"><a  href="javascript:;" onclick="questionDelete('{tpl:$questionInfo.question_id/}','{tpl:$questionInfo.question_name/}')">删除</a>
 |  <a href="javascript:;" onclick="questionModify('{tpl:$questionInfo.question_id/}');">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

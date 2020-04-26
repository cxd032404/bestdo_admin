{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function()
{
	$('#add_action').click(function(){
		addActionBox = divBox.showBox('{tpl:$this.sign/}&ac=action.add', {title:'添加动作',width:400,height:200});
	});
});
function CreditModify(aid,cid){
    addCreditBox = divBox.showBox('{tpl:$this.sign/}&ac=action.credit.modify&ActionId=' + aid + '&CId=' + cid, {title:'修改积分',width:500,height:500});
}

function actionDelete(p_id, p_name){
	deleteActionBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=action.delete&ActionId=' + p_id;}});
}

function actionModify(mid){
	modifyActionBox = divBox.showBox('{tpl:$this.sign/}&ac=action.modify&ActionId=' + mid, {title:'修改动作',width:400,height:300});
}

function CreditDelete(aid,cid,c_name){
    deleteLicenseBox = divBox.confirmBox({content:'是否删除 '+c_name+'?',ok:function(){location.href = '{tpl:$this.sign/}&ac=action.credit.delete&ActionId=' + aid + '&CId=' + cid;}});
}

</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_action">添加动作</a> ]
</fieldset>
<fieldset><legend>动作列表 </legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">动作ID</th>
    <th align="center" class="rowtip">对应标识</th>
    <th align="center" class="rowtip">动作名称</th>
    <th align="center" class="rowtip">积分</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

  {tpl:loop $ActionList $ActionInfo}
  <tr class="hover">
    <td align="center">{tpl:$ActionInfo.ActionId/}</td>
    <td align="center">{tpl:$ActionInfo.Action/}</td>
    <td align="center">{tpl:$ActionInfo.ActionName/}</td>
    <td align="center">{tpl:$ActionInfo.CreditListHtml/}</td>

      <td align="center"><a  href="javascript:;" onclick="actionDelete('{tpl:$ActionInfo.ActionId/}','{tpl:$ActionInfo.ActionName/}')">删除</a> |  <a href="javascript:;" onclick="actionModify('{tpl:$ActionInfo.ActionId/}');">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

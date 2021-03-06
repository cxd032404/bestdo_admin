{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_credit').click(function(){
		addCreditBox = divBox.showBox('{tpl:$this.sign/}&ac=credit.add', {title:'添加积分类目',width:400,height:200});
	});
});

function creditDelete(p_id, p_name){
	deleteCreditBox = divBox.confirmBox({content:'是否删除 ' + p_name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=credit.delete&CreditId=' + p_id;}});
}

function creditModify(mid){
	modifyCreditBox = divBox.showBox('{tpl:$this.sign/}&ac=credit.modify&CreditId=' + mid, {title:'修改积分类目',width:400,height:200});
}

</script>

<fieldset><legend>操作</legend>
    <div>
        <span style="float:right;"><a class="pb_btn_dark_1" href="javascript:;" id="add_credit">新增</a></span>
    </div>

</fieldset>
<fieldset><legend>积分类目列表 </legend>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">积分类目ID</th>
    <th align="center" class="rowtip">积分类目名称</th>
    <th align="center" class="rowtip">消费比例</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $CreditList $CreditInfo}
  <tr class="hover">
    <td align="center">{tpl:$CreditInfo.CreditId/}</td>
    <td align="center">{tpl:$CreditInfo.CreditName/}</td>
    <td align="center">{tpl:if($CreditInfo.CreditRate>0)}{tpl:$CreditInfo.CreditRate/}分{tpl:else}不可消费{/tpl:if}</td>
    <td align="center"><a class="pb_btn_dark_1" href="javascript:;" onclick="creditDelete('{tpl:$CreditInfo.CreditId/}','{tpl:$CreditInfo.CreditName/}')">删除</a>  <a class="pb_btn_light_1" href="javascript:;" onclick="creditModify('{tpl:$CreditInfo.CreditId/}');">修改</a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

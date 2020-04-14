{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
	$('#add_update_log').click(function(){
		addUpdateLogBox = divBox.showBox('{tpl:$this.sign/}&ac=update.log.add', {title:'添加更新记录',width:600,height:350});
	});
});

function updateLogDelete(t_id){
	deleteUpdateLogBox = divBox.confirmBox({content:'是否删除 ?',ok:function(){location.href = '{tpl:$this.sign/}&ac=update.log.delete&UpdateLogId=' + t_id;}});
}

function updaetLogModify(lid){
	modifyUpdateLogBox = divBox.showBox('{tpl:$this.sign/}&ac=update.log.modify&UpdateLogId=' + lid, {title:'修改更新记录',width:600,height:350});
}


</script>

<fieldset><legend>操作</legend>
[ <a href="javascript:;" id="add_update_log">添加更新记录</a> ]
</fieldset>

<fieldset><legend>更新记录列表</legend>
<table width="99%" align="center" class="table table-bordered table-striped">

{tpl:loop $UpdateLogList $UpdateDate $LogTypeList}
  <tr class="hover">
      <th align="center" class="rowtip" colspan="3">更新日期:{tpl:$UpdateDate/}</th>
  </tr>
      {tpl:loop $LogTypeList $LogType $LogList}
          {tpl:loop $UpdateLogTypeList $Type $LogTypeName}
          {tpl:if($LogType==$Type)}<tr class="hover"><th align="center" class="rowtip"  colspan="3">{tpl:$LogTypeName/}:</th></tr>{/tpl:if}
          {/tpl:loop}
            {tpl:loop $LogList $LogId $Log}
              <tr class="hover"><th align="center" class="rowtip" colspan="2">{tpl:$Log.comment/}</th>
                  <td align="center"><a href="javascript:;" onclick="updaetLogModify('{tpl:$Log.UpdateLogId/}');">修改</a> |  <a  href="javascript:;" onclick="updateLogDelete('{tpl:$Log.UpdateLogId/}}')">删除</a></td></tr>
            {/tpl:loop}
      {/tpl:loop}
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

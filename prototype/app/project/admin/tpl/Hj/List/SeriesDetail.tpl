{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_detail').click(function(){
    addDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=series.detail.add&series_id=' + $('#series_id').val(), {title:'新增数据列-{tpl:$seriesInfo.series_name/}',width:500,height:300});
  });
});

function detailDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deleteDetailBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=series.detail.delete&detail_id=' + p_id;}});
}

function detailModify(lid){
  modifyDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=series.detail.modify&detail_id=' + lid, {title:'修改详情',width:600,height:500});
}

</script>

<fieldset><legend>操作</legend>
  <div>
    <span style="float:left;"><a class = "pb_btn_light_1" href="{tpl:$this.sign/}&company_id={tpl:$seriesInfo.company_id/}">返回</a></span>
    <span style="float:right;"><a class = "pb_btn_dark_1" href="javascript:;" id="add_detail">新增</a></span>
  </div>
</fieldset>

<fieldset><legend>列表详情</legend>
  <input type="hidden" id="company_id" name="company_id" value="{tpl:$seriesInfo.company_id/}" />
  <input type="hidden" id="series_id" name="series_id" value="{tpl:$series_id/}" />
  <table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">元素ID</th>
    <th align="center" class="rowtip">元素名称</th>
    {tpl:loop $countList $i}
      <th align="center" class="rowtip">数据列【{tpl:$i/}】</th>
    {/tpl:loop}
    <th align="center" class="rowtip">创建时间</th>
    <th align="center" class="rowtip">更新时间</th>

    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $seriesDetailList.SeriesDetailList $detailInfo}
  <tr class="hover">
    <td align="center">{tpl:$detailInfo.detail_id/}</td>
    <td align="center">{tpl:$detailInfo.detail_name/}</td>
    {tpl:loop $countList $i}

    {tpl:loop $detailInfo.detail.list_id $l_id $list_name}
      {tpl:if($l_id==$i)}
        <th align="center" class="rowtip">{tpl:$list_name/}</th>
      {/tpl:if}
      {/tpl:loop}
    {/tpl:loop}
    <td align="center">{tpl:$detailInfo.create_time/}</td>
    <td align="center">{tpl:$detailInfo.update_time/}</td>
      <td align="center">
        <a class = "pb_btn_grey_1" href="javascript:;" onclick="detailDelete('{tpl:$detailInfo.detail_id/}','{tpl:$detailInfo.detail_name/}')">删除</a>
        <a class = "pb_btn_light_1" href="javascript:;" onclick="detailModify('{tpl:$detailInfo.detail_id/}');">修改</a>
      </td>
  </tr>
{/tpl:loop}
  <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>

</table>
</fieldset>
{tpl:tpl contentFooter/}

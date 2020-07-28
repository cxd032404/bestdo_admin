{tpl:tpl contentHeader/}
<script type="text/javascript">
    $(document).ready(function(){
        $('#add_series').click(function(){
            addListBox = divBox.showBox('{tpl:$this.sign/}&ac=series.add'+ '&currentPage='+$('#currentPage').val(), {title:'添加系列',width:600,height:600});
        });
    });

function seriesDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deleteactivityBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=series.delete&series_id=' + p_id+ '&currentPage='+$('#currentPage').val();}});
}

function seriesModify(lid){
  modifyactivityBox = divBox.showBox('{tpl:$this.sign/}&ac=series.modify&series_id=' + lid+ '&currentPage='+$('#currentPage').val(), {title:'修改系列',width:600,height:700});
}

</script>

<fieldset><legend>操作</legend>
    <div align=right><a class = "pb_btn_dark_1" href="javascript:;" id="add_series">新增</a></div>
</fieldset>

<fieldset><legend>系列</legend>
<form action="{tpl:$this.sign/}" name="form" id="form" method="post">
    <input type="hidden" name="currentPage" id="currentPage" value="{tpl:$currentPage/}" />
<select name="company_id"  id="company_id" size="1">
      <option value="0"{tpl:if(0==$company_id)}selected="selected"{/tpl:if} >全部</option>
      {tpl:loop $companyList  $company_info}
      <option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$params.company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
      {/tpl:loop}
    </select>
  <button type="submit" class="pb_btn_light_1">搜索</button>
</form>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">系列ID</th>
    <th align="center" class="rowtip">系列名称</th>
      <th align="center" class="rowtip">系列标示</th>
      <th align="center" class="rowtip">对应企业</th>
      <th align="center" class="rowtip">系列数量</th>
      <th align="center" class="rowtip">元素数量</th>
      <th align="center" class="rowtip">详情</th>
      <th align="center" class="rowtip">更新时间</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $seriesList.SeriesList $seriesInfo}
  <tr class="hover">
    <td align="center">{tpl:$seriesInfo.series_id/}</td>
    <td align="center">{tpl:$seriesInfo.series_name/}</td>
      <td align="center">{tpl:$seriesInfo.series_sign/}</td>
      <td align="center">{tpl:$seriesInfo.company_name/}</td>
      <td align="center">{tpl:$seriesInfo.series_count/}</td>
      <td align="center">0</td>
      <td align="center">0</td>
      <td align="center">0</td>

      <td align="center">{tpl:$seriesInfo.update_time/}</td>
      <td align="center"><a class = "pb_btn_grey_1" href="javascript:;" onclick="seriesDelete('{tpl:$seriesInfo.series_id/}','{tpl:$seriesInfo.series_name/}')">删除</a>
   <a class = "pb_btn_light_1" href="javascript:;" onclick="seriesModify('{tpl:$seriesInfo.series_id/}');">修改</a>
           <a class = "pb_btn_grey_1" href="{tpl:$this.sign/}&ac=series.detail&series_id={tpl:$seriesInfo.series_id/}&currentPage={tpl:$currentPage/}">详细{tpl:if($seriesInfo.posts_count>0)}({tpl:$seriesInfo.posts_count/}){/tpl:if} </a></td>
  </tr>
{/tpl:loop}
    <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>

</table>
</fieldset>
{tpl:tpl contentFooter/}

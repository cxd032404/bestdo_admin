{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_detail').click(function(){
    addDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=series.detail.add&series_id=' + $('#series_id').val(), {title:'新增数据列-{tpl:$seriesInfo.series_name/}',width:500,height:300});
  });
});

function listDelete(p_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deleteactivityBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=list.delete&list_id=' + p_id;}});
}

function listModify(lid){
  modifyactivityBox = divBox.showBox('{tpl:$this.sign/}&ac=list.modify&list_id=' + lid, {title:'修改列表',width:600,height:500});
}

</script>

<fieldset><legend>操作</legend>
  <div>
    <span style="float:left;"><a class = "pb_btn_light_1" href="{tpl:$return_url/}">返回</a></span>
    <span style="float:right;"><a class = "pb_btn_dark_1" href="javascript:;" id="add_detail">新增</a></span>
  </div>
</fieldset>

<fieldset><legend>列表详情</legend>
  <input type="hidden" id="company_id" name="company_id" value="{tpl:$seriesInfo.company_id/}" />
  <input type="hidden" id="series_id" name="series_id" value="{tpl:$series_id/}" />
  <table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">文章Id</th>
    <th align="center" class="rowtip">提交人</th>
    <th align="center" class="rowtip">可见</th>
    <th align="center" class="rowtip">浏览</th>
    <th align="center" class="rowtip">点赞</th>
    <th align="center" class="rowtip">创建时间</th>
    <th align="center" class="rowtip">更新时间</th>
    <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $list.postsList $postsInfo}
  <tr class="hover">
    <td align="center">{tpl:$postsInfo.post_id/}</td>
    <td align="center">{tpl:$postsInfo.user_name/}</td>
    <td align="center">{tpl:$postsInfo.visible_name/}</td>
    <td align="center">{tpl:$postsInfo.views/}</td>
    <td align="center">{tpl:$postsInfo.kudos/}</td>
    <td align="center">{tpl:$postsInfo.create_time/}</td>
    <td align="center">{tpl:$postsInfo.update_time/}</td>
      <td align="center">
        {tpl:if($postsInfo.visible==1)}
        <a class = "pb_btn_grey_1" href="{tpl:$display_url/}&post_id={tpl:$postsInfo.post_id/}">隐藏</a>
        {tpl:else}
        <a class = "pb_btn_dark_1" href="{tpl:$display_url/}&post_id={tpl:$postsInfo.post_id/}&display=1">显示</a>
        {/tpl:if}
        <a class = "pb_btn_light_1" href="{tpl:$this.sign/}&ac=posts.detail&post_id={tpl:$postsInfo.post_id/}">详情</a></td>
  </tr>
{/tpl:loop}
  <tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>

</table>
</fieldset>
{tpl:tpl contentFooter/}

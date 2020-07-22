{tpl:tpl contentHeader/}
<script type="text/javascript">
$(document).ready(function(){
  $('#add_config').click(function(){
    addConfigBox = divBox.showBox('{tpl:$this.sign/}&ac=config.add', {title:'添加配置',width:650,height:600});
  });
});

function configDelete(c_id,p_name){
    msg = '是否删除 ' + p_name + '?'
  deleteAppBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=config.delete&config_sign=' + c_id;}});
}

function configModify(mid){
  modifyconfigBox = divBox.showBox('{tpl:$this.sign/}&ac=config.modify&config_sign=' + mid, {title:'修改配置',width:650,height:600});
}
</script>

<fieldset><legend>操作</legend>
[ <a class = "pb_btn_dark_1" href="javascript:;" id="add_config">新增</a> ]
</fieldset>

<fieldset><legend> 配置列表 </legend>
    <form action="{tpl:$this.sign/}" name="form" id="form" method="post">
        <select name="config_type"  id="config_type" size="1">
            <option value="" {tpl:if(""==$config_type)}selected="selected"{/tpl:if} >全部</option>
            {tpl:loop $configTypeList  $type $name}
            <option value="{tpl:$type/}"{tpl:if($type==$config_type)}selected="selected"{/tpl:if} >{tpl:$name/}</option>
            {/tpl:loop}
        </select>
        <input type="image" name="submit" value="查询" src="/icon/search.png" width='30' height='30'/>
    </form>
<table width="99%" align="center" class="table table-bordered table-striped">
  <tr>
    <th align="center" class="rowtip">配置标示</th>
    <th align="center" class="rowtip">配置名称</th>
    <th align="center" class="rowtip">配置类型</th>
    <th align="center" class="rowtip">内容</th>
    <th align="center" class="rowtip">创建时间</th>
    <th align="center" class="rowtip">更新时间</th>
      <th align="center" class="rowtip">操作</th>
  </tr>

{tpl:loop $configList $configInfo}
  <tr class="hover">
    <td align="center">{tpl:$configInfo.config_sign/}</td>
    <td align="center">{tpl:$configInfo.config_name/}</td>
    <td align="center">{tpl:$configInfo.config_type_name/}</td>
    <td align="center" style="word-break : break-all; overflow:hidden; "> {tpl:if($configInfo.config_type=="source")}<a href="{tpl:$this.sign/}&ac=source.detail&config_sign={tpl:$configInfo.config_sign/}">内容更新</a>{tpl:else}{tpl:$configInfo.content/}{/tpl:if}</td>
      <td align="center">{tpl:$configInfo.create_time/}</td>
      <td align="center">{tpl:$configInfo.update_time/}</td>
      <td align="center"><a  href="javascript:;" onclick="configDelete('{tpl:$configInfo.config_sign/}','{tpl:$configInfo.parent_id/}','{tpl:$configInfo.config_name/}')"><img src="/icon/del.png" width='30' height='30'/></a>
 |  <a href="javascript:;" onclick="configModify('{tpl:$configInfo.config_sign/}');"><img src="/icon/edit2.png" width='30' height='30'/></a></td>
  </tr>
{/tpl:loop}
</table>
</fieldset>
{tpl:tpl contentFooter/}

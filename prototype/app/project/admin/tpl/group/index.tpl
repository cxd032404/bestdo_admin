{tpl:tpl contentHeader/}
<fieldset><legend>操作</legend>
[ <a class = "pb_btn_dark_1" href="?ctl=group&ac=add">新增</a> ]
</fieldset>

<fieldset><legend>列表</legend>
<form name="group_list_form" id="group_list_form" action="?ctl=group" method="post">
<table class="table table-bordered table-striped" width="100%">
<tr><th width="60">ID</th><th>名称</th><th>操作</th></tr>
{tpl:loop $group $row}
<tr class="hover"><td>{tpl:$row.group_id/}</td>
<td>{tpl:$row.name/}</a></td>
<td>
<a href="?ctl=group&ac=modify&group_id={tpl:$row.group_id/}"><img src="/icon/edit2.png" width='30' height='30'/></a>
| <a href="?ctl=group&ac=delete&group_id={tpl:$row.group_id/}" onclick="return confirm('确定要删除管理员组？');"><img src="/icon/del.png" width='30' height='30'/></a>
| <a href="?ctl=menu/permission&ac=modify.by.group&group_id={tpl:$row.group_id/}">菜单权限</a>
| <a href="?ctl=config/permission&ac=list.partner.permission&group_id={tpl:$row.group_id/}">数据权限</a></td>
</tr>
{/tpl:loop}
</table>
</form>
</fieldset>
{tpl:tpl contentFooter/}

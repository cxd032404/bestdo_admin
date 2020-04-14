{tpl:tpl contentHeader/}
<form name="group_permission_update_form" id="group_permission_update_form" action="?ctl=data.group&ac=permission.modify" method="post">
<table class="tbv" width="100%">
<INPUT TYPE="hidden" NAME="group_id" id="group_id" value="{tpl:$group_id/}">
<fieldset>
	<legend>{tpl:$group.name/} 数据权限列表</legend>
	<table width="99%" align="center" class="table table-bordered table-striped">
		<tr>
			<th align="center" class="rowtip">赛事</th>
			<th align="center" class="rowtip">权限</th>
		</tr>
		{tpl:loop $totalPermission $RaceCatalogId $RaceCatalogInfo}
		<tr>
			<th align="center" class="rowtip">{tpl:$RaceCatalogInfo.RaceCatalogName/}</th>
			<th align="center" class="rowtip"><input type = 'checkbox' name = RaceCatalogList[{tpl:$RaceCatalogId/}][Permission] {tpl:if ($RaceCatalogInfo.Permission==1)} checked {/tpl:if} value="{tpl:$RaceCatalogId/}"></th>
		</tr>
		{/tpl:loop}
	</table>
</fieldset>
	<tr class="noborder">
		<th></th><td>
		<button type="submit" id="group_update_submit">提交</button></td><td>&nbsp;</td>
	</tr>
</table>
</form>
{tpl:tpl contentFooter/}

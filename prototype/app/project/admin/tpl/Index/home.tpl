{tpl:tpl contentHeader/}
<fieldset><legend>更新记录</legend>
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
            </tr>
    {/tpl:loop}
    {/tpl:loop}
        </tr>
        {/tpl:loop}
    </table>
</fieldset>
{tpl:tpl contentFooter/}

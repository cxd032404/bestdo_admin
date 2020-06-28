{tpl:tpl contentHeader/}
<div class="br_bottom"></div>


    <table width="99%" align="center" class="table table-bordered table-striped" >
        <tr class="hover">
            <th align="center" class="rowtip" rowspan="10" colspan="2"><img src="{tpl:$UserInfo.thumb/}" width='160' height='160'/></th>
        </tr>
        <tr class="hover">
            <th align="center"class="rowtip">用户昵称</th>
            <td align="left"  colspan="3" >{tpl:$UserInfo.NickName/}</td>
        </tr>
        <tr class="hover">
            <th align="center" class="rowtip">用户姓名</th>
            <td align="left">{tpl:$UserInfo.Name/}</td>
        </tr>
        <tr class="hover">
            <th align="center" class="rowtip">用户性别</th>
            <td align="left">{tpl:$UserInfo.Sex/}</td>
        </tr>
        <tr class="hover">
            <th align="center" class="rowtip">联系电话</th>
            <td align="left">{tpl:$UserInfo.Mobile/}</td>
        </tr>
        <tr class="hover">
            <th align="center" class="rowtip">实名认证状态</th>
            <td align="left">{tpl:$UserInfo.AuthStatus/}</td>
        </tr>
        {tpl:if($UserInfo.IdNo!="")}
        <tr class="hover">
            <th align="center" class="rowtip">证件类型</th>
            <td align="left">{tpl:$UserInfo.AuthIdType/}</td>
        </tr>
        <tr class="hover">
           <th align="center" class="rowtip">生日</th>
            <td align="left">{tpl:$UserInfo.Birthday/}</td>
        </tr>
        <tr class="hover">
            <th align="center" class="rowtip">证件号码</th>
            <td align="left">{tpl:$UserInfo.IdNo/}</td>
        </tr>
        <tr class="hover">
            <th align="center" class="rowtip">证件有效期</th>
            <td align="left">{tpl:$UserInfo.AuthExpireDate/}</td>
        </tr>
        {/tpl:if}
        {tpl:if(count($UserInfo.UserAuthLog))}
        <tr class="hover">
            <th align="center" class="rowtip" colspan="4">实名认证记录</th>
        </tr>
        <tr class="hover">
            <th align="center" class="rowtip">操作时间</th>
            <th align="center" class="rowtip">后台管理员账号</th>
            <th align="center" class="rowtip">操作结果</th>
            <th align="center" class="rowtip">说明</th>
        </tr>
            {tpl:loop $UserInfo.UserAuthLog $LogId $LogInfo}
                <tr class="hover">
                        <td align="left">{tpl:$LogInfo.op_time/}</th>
                        <td align="left">{tpl:$LogInfo.ManagerName/}</th>
                        <td align="left">{tpl:$LogInfo.AuthResult/}</th>
                        <td align="left">{tpl:$LogInfo.auth_resp/}</th>
                </tr>
            {/tpl:loop}

        {/tpl:if}


    </table>

{tpl:tpl contentFooter/}
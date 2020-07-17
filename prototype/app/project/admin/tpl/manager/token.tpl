{tpl:tpl contentHeader/}
<div class="br_bottom"></div>


<table width="99%" align="center" class="table table-bordered table-striped" >
    <tr class="hover">
        <th align="center" class="rowtip" rowspan="20" colspan="2"><img src="{tpl:$userInfo.user_img/}" width='160' height='160'/></th>
    </tr>
    <tr class="hover">
        <th align="center"class="rowtip">用户昵称</th>
        <td align="left"  colspan="3" >{tpl:$userInfo.nick_name/}</td>
    </tr>
    <tr class="hover">
        <th align="center" class="rowtip">用户姓名</th>
        <td align="left">{tpl:$userInfo.true_name/}</td>
    </tr>
    <tr class="hover">
        <th align="center" class="rowtip">微信openId</th>
        <td align="left">{tpl:$userInfo.wechatid/}</td>
    </tr>
    <tr class="hover">
        <th align="center" class="rowtip">小程序openId</th>
        <td align="left">{tpl:$userInfo.mini_program_id/}</td>
    </tr>
    <tr class="hover">
        <th align="center" class="rowtip">unionId</th>
        <td align="left">{tpl:$userInfo.unionid/}</td>
    </tr>
    <tr class="hover">
        <th align="center" class="rowtip"  style="word-break : break-all; overflow:hidden; ">token</th>
        <td align="left" style="word-break : break-all; overflow:hidden; ">{tpl:$token/}</td>
    </tr>
</table>

{tpl:tpl contentFooter/}
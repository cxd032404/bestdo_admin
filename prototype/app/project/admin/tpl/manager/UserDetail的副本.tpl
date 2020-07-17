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
            <th align="center" class="rowtip">用户性别</th>
            <td align="left">{tpl:$userInfo.sex/}</td>
        </tr>
        <tr class="hover">
            <th align="center" class="rowtip">联系电话</th>
            <td align="left">{tpl:$userInfo.mobile/}</td>
        </tr>
        <tr class="hover">
            <th align="center" class="rowtip">注册时间</th>
            <td align="left">{tpl:$userInfo.reg_time/}</td>
        </tr>
        <tr class="hover">
            <th align="center" class="rowtip">最后登录时间</th>
            <td align="left">{tpl:$userInfo.last_login_time/}</td>
        </tr>
        <tr class="hover">
            <th align="center" class="rowtip">最后登录方式</th>
            <td align="left">{tpl:$userInfo.LoginSourceName/}</td>
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
            <th align="center" class="rowtip">unionId</th>
            <td align="left">{tpl:$userInfo.department_name/}</td>
        </tr>
    </table>

{tpl:tpl contentFooter/}
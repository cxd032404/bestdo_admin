{tpl:tpl contentHeader/}
<script type="text/javascript">

function iconDelete(cid,name){
	deleteStageLogoBox = divBox.confirmBox({content:'是否删除 ' + name + '?',ok:function(){location.href = '{tpl:$this.sign/}&ac=icon.delete&company_id=' + cid;}});
}
</script>

<div class="br_bottom"></div>
<form id="company_update_form" name="company_update_form" action="{tpl:$this.sign/}&ac=company.update" method="post">
<input type="hidden" name="company_id" value="{tpl:$companyInfo.company_id/}" />
<input type="hidden" name="parent_id" value="{tpl:$companyInfo.parent_id/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>企业名称</td>
<td align="left"><input name="company_name" type="text" class="span2" id="company_name" value="{tpl:$companyInfo.company_name/}" size="50" /></td>
</tr>
<tr class="hover"><td>上级企业</td>
	<td align="left">	<select name="parent_id"  id="parent_id" size="1">
			<option value="0" {tpl:if(0==$companyInfo.parent_id)}selected="selected"{/tpl:if}>无上级</option>
			{tpl:loop $companyList  $company_info}
			<option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$companyInfo.parent_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
<tr class="hover"><td>是否显示</td>
	<td align="left">
	<input type="radio" name="display" id="display" value="1"  {tpl:if($companyInfo.display==1)}checked{/tpl:if}>显示
	<input type="radio" name="display" id="display" value="0"  {tpl:if($companyInfo.display==0)}checked{/tpl:if}>隐藏
</td>
</tr>
        <tr class="hover"><td>上传图片</td>
            <td align="left">
                {tpl:if($companyInfo.icon!="")}
                已选图片:<img src="{tpl:$companyInfo.icon/}" width="30px;" height="30px;"/>
                <br>       
                {/tpl:if}
                更改图片:<input name="upload_img[1]" type="file" class="span4" id="upload_img[1]"/>
            </td>
        </tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="company_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#company_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '企业名称不能为空，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改企业成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#company_update_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
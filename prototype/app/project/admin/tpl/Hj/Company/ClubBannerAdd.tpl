{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="banner_add_form" name="banner_add_form" action="{tpl:$this.sign/}&ac=club.banner.insert" method="post">
	<input type="hidden" name="currentPage" id="currentPage" value="{tpl:$currentPage/}" />
	<input type="hidden" name="company_id" id="company_id" value="{tpl:$companyInfo.company_id/}" />
<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>图片上传：</td>
	<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" />
    </td>
</tr>
<tr class="hover">
	<td>文字</td>
	<td align="left"><input type="text" class="span2" name="detail[text]"  id="detail[text]" value="" size="50" /></td>
</tr>
<td>跳转路径</td>
	<td align="left"><input type="text" class="span4" name="detail[img_jump_url]"  id="detail[img_jump_url]" value="" size="50" /></td>
</tr>
	<td>标签</td>
	<td align="left"><input type="text" class="span2" name="detail[title]"  id="detail[title]" value="" size="50" /></td>
	</tr>
<tr class="hover">
	<td>排序</td>
	<td align="left"><input type="text" class="span2" name="detail[sort]"  id="detail[sort]" value="80" size="50" /></td>
</tr>
<tr class="hover">
	<td>生效时间</td>
	<td align="left"><input type="text" name="detail[start_time]"  id="detail[start_time]" class="input-medium" value="{tpl:$start_time/}"  onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >-<input type="text" name="detail[end_time]" id="detail[end_time]" class="input-medium"  value="{tpl:$end_time/}" onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd HH:mm:ss'})" >
	</td>
</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="banner_add_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#banner_add_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '必须上传图片，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '添加图片成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=club.banner&company_id=' + $('#company_id').val()+ '&currentPage='+$('#currentPage').val());}});
			}
		}
	};
	$('#banner_add_form').ajaxForm(options);
});
</script>
{tpl:tpl contentFooter/}
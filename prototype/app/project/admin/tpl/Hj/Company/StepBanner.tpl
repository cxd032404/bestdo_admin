{tpl:tpl contentHeader/}
<script type="text/javascript">
	$(document).ready(function(){
		$('#add_banner').click(function(){
			addBannerBox = divBox.showBox('{tpl:$this.sign/}&ac=step.banner.add&company_id='+$('#company_id').val(), {title:'添加banner到'+$('#company_name').val(),width:500,height:400});
		});
	});

	function BannerDelete(pos){
		msg = '是否删除?'
		deleteBannerBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=step.banner.delete&company_id=' + $('#company_id').val() + '&pos=' + pos;}});
	}

	function BannerModify(pos){
		modifyBannerBox = divBox.showBox('{tpl:$this.sign/}&ac=step.banner.modify&company_id=' + $('#company_id').val() + '&pos=' + pos, {title:'修改Banner',width:600,height:350});
	}

</script>
<div class="br_bottom"></div>
<form id="company_banner_form" name="company_banner_form" action="{tpl:$this.sign/}&ac=company.banner.update" method="post">
	<input type="hidden" name="company_id" id="company_id" value="{tpl:$companyInfo.company_id/}" />
	<input type="hidden" name="company_name" id="company_name" value="{tpl:$companyInfo.company_name/}" />
	<fieldset>
		[ <a href="{tpl:$this.sign/}&company_id={tpl:$companyInfo.company_id/}">返回</a> | <a href="javascript:;" id="add_banner">添加Banner</a> ]
	</fieldset>
	<fieldset><legend>banner列表</legend>
		<table width="99%" align="center" class="table table-bordered table-striped" >
			<tr class="hover"><td colspan="2">Banner列表</td></tr>
			{tpl:loop $companyInfo.detail.stepBanner $pos $picInfo}
			<tr class="hover">
				<td align="center"><img src="{tpl:$picInfo.img_url/}" width='150' height='130'/><p>文字：{tpl:$picInfo.text/}<p>跳转：{tpl:$picInfo.img_jump_url/}<p>标签：{tpl:$picInfo.title/}</td>
				<td align="center"><a  href="javascript:;" onclick="BannerDelete('{tpl:$pos/}')">删除</a>
					|  <a href="javascript:;" onclick="BannerModify('{tpl:$pos/}');">修改</a></td>
			</tr>
			{/tpl:loop}
		</table>
</form>
<?php Third_ckeditor_ckeditor::render("text")?>
<script type="text/javascript">
	$('#company_banner_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {},
			success:function(jsonResponse) {
				if (jsonResponse.errno) {
					var errors = [];
					errors[1] = '页面元素名称不能为空，请修正后再次提交';
					errors[2] = '必须上传图片，请修正后再次提交';
					errors[9] = '入库失败，请修正后再次提交';
					divBox.alertBox(errors[jsonResponse.errno],function(){});
				} else {
					var message = '修改页面元素成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=page.detail&page_id=' + $('#page_id').val());}});
				}
			}
		};
		$('#company_banner_form').ajaxForm(options);
	});
</script>
{tpl:tpl contentFooter/}
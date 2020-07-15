{tpl:tpl contentHeader/}
<script type="text/javascript">
	$(document).ready(function(){
		$('#add_banner').click(function(){
			addBannerBox = divBox.showBox('{tpl:$this.sign/}&ac=club.banner.add&company_id='+$('#company_id').val()+ '&currentPage='+$('#currentPage').val(), {title:'添加banner到'+$('#company_name').val(),width:500,height:400});
		});
	});

	function BannerDelete(pos){
		msg = '是否删除?'
		deleteBannerBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=club.banner.delete&company_id=' + $('#company_id').val() + '&pos=' + pos+ '&currentPage='+$('#currentPage').val();}});
	}

	function BannerModify(pos){
		modifyBannerBox = divBox.showBox('{tpl:$this.sign/}&ac=club.banner.modify&company_id=' + $('#company_id').val() + '&pos=' + pos+ '&currentPage='+$('#currentPage').val(), {title:'修改Banner',width:600,height:350});
	}

</script>
<div class="br_bottom"></div>
<form id="company_banner_form" name="company_banner_form" action="{tpl:$this.sign/}&ac=company.banner.update" method="post">
	<input type="hidden" name="currentPage" id="currentPage" value="{tpl:$currentPage/}" />
	<input type="hidden" name="company_id" id="company_id" value="{tpl:$companyInfo.company_id/}" />
	<input type="hidden" name="company_name" id="company_name" value="{tpl:$companyInfo.company_name/}" />
	<fieldset>
		[ <a href="?{tpl:$currentPage func='urldecode(@@)'/}&company_id={tpl:$companyInfo.company_id/}">返回L课表</a> | <a href="javascript:;" id="add_banner">添加Banner</a> ]
	</fieldset>
	<fieldset><legend>俱乐部banner列表</legend>
		<table width="99%" align="center" class="table table-bordered table-striped" >
			<tr class="hover"><td colspan="2">Banner列表</td></tr>
			{tpl:loop $companyInfo.detail.clubBanner $pos $picInfo}
			<tr class="hover">
				<td align="center"><img src="{tpl:$picInfo.img_url/}" width='150' height='130'/><p>文字：{tpl:$picInfo.text/}<p>跳转：{tpl:$picInfo.img_jump_url/}<p>标签：{tpl:$picInfo.title/}<p>排序：{tpl:$picInfo.sort/}<p>
				<p>生效时间：<p>{tpl:$picInfo.start_time/}<p>{tpl:$picInfo.end_time/}</td>
				<td align="center"><a  href="javascript:;" onclick="BannerDelete('{tpl:$pos/}')">删除</a>
					|  <a href="javascript:;" onclick="BannerModify('{tpl:$pos/}');">修改</a></td>
			</tr>
			{/tpl:loop}
		</table>
</form>
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
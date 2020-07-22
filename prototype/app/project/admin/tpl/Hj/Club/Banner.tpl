{tpl:tpl contentHeader/}
<script type="text/javascript">
	$(document).ready(function(){
		$('#add_banner').click(function(){
			addBannerBox = divBox.showBox('{tpl:$this.sign/}&ac=banner.add&club_id='+$('#club_id').val(), {title:'添加banner到'+$('#club_name').val(),width:500,height:400});
		});
	});

	function BannerDelete(pos){
		msg = '是否删除?'
		deleteBannerBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=banner.delete&club_id=' + $('#club_id').val() + '&pos=' + pos;}});
	}

	function BannerModify(pos){
		modifyBannerBox = divBox.showBox('{tpl:$this.sign/}&ac=banner.modify&club_id=' + $('#club_id').val() + '&pos=' + pos, {title:'修改Banner',width:600,height:350});
	}

</script>
<div class="br_bottom"></div>
<form id="club_banner_form" name="club_banner_form" action="{tpl:$this.sign/}&ac=club.banner.update" method="post">
	<input type="hidden" name="club_id" id="club_id" value="{tpl:$clubInfo.club_id/}" />
	<input type="hidden" name="club_name" id="club_name" value="{tpl:$clubInfo.club_name/}" />
	<fieldset>
		[ <a href="{tpl:$this.sign/}&company_id={tpl:$clubInfo.company_id/}"><img src="/icon/return.png" width='30' height='30'/></a> | <a class = "pb_btn_dark_1" href="javascript:;" id="add_banner">新增</a> ]
	</fieldset>
	<fieldset><legend>banner列表</legend>
		<table width="99%" align="center" class="table table-bordered table-striped" >
			<tr class="hover"><td colspan="2">Banner列表</td></tr>
			{tpl:loop $clubInfo.detail.banner $pos $picInfo}
			<tr class="hover">
				<td align="center"><img src="{tpl:$picInfo.img_url/}" width='150' height='130'/><p>文字：{tpl:$picInfo.text/}<p>跳转：{tpl:$picInfo.img_jump_url/}<p>标签：{tpl:$picInfo.title/}</td>
				<td align="center"><a  href="javascript:;" onclick="BannerDelete('{tpl:$pos/}')"><img src="/icon/del.png" width='30' height='30'/></a>
					|  <a href="javascript:;" onclick="BannerModify('{tpl:$pos/}');"><img src="/icon/edit2.png" width='30' height='30'/></a></td>
			</tr>
			{/tpl:loop}
			<tr class="noborder"><td></td>
				<td><button type="submit" id="club_banner_submit" class="pb_btn_dark_1">提交</button></td>
			</tr>
		</table>
</form>
<script type="text/javascript">
	$('#club_banner_submit').click(function(){
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
		$('#club_banner_form').ajaxForm(options);
	});
</script>
{tpl:tpl contentFooter/}
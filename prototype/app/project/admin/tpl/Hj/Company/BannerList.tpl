{tpl:tpl contentHeader/}
<script type="text/javascript">
	$(document).ready(function(){
		$('#add_banner').click(function(){
			addBannerBox = divBox.showBox('{tpl:$this.sign/}&ac=banner.add&company_id='+$('#company_id').val()+ '&currentPage='+$('#currentPage').val()+ '&banner_type='+$('#banner_type').val(), {title:'添加banner到'+$('#company_name').val(),width:500,height:400});
		});
	});

	function BannerDelete(pos){
		msg = '是否删除?'
		deleteBannerBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=banner.delete&company_id=' + $('#company_id').val() + '&pos=' + pos+ '&currentPage='+$('#currentPage').val()+ '&banner_type='+$('#banner_type').val();}});
	}

	function BannerModify(pos){
		modifyBannerBox = divBox.showBox('{tpl:$this.sign/}&ac=banner.modify&company_id=' + $('#company_id').val() + '&pos=' + pos+ '&currentPage='+$('#currentPage').val()+ '&banner_type='+$('#banner_type').val(), {title:'修改Banner',width:600,height:350});
	}

</script>
<div class="br_bottom"></div>
<form id="company_banner_form" name="company_banner_form" action="{tpl:$this.sign/}&ac=company.banner.update" method="post">
	<input type="hidden" name="currentPage" id="currentPage" value="{tpl:$currentPage/}" />
	<input type="hidden" name="banner_type" id="banner_type" value="{tpl:$banner_type/}" />
	<input type="hidden" name="company_id" id="company_id" value="{tpl:$companyInfo.company_id/}" />
	<input type="hidden" name="company_name" id="company_name" value="{tpl:$companyInfo.company_name/}" />
	<fieldset>
	<div>
	<span style="float:left;"> <a class = "pb_btn_light_1" href="?{tpl:$currentPage func='urldecode(@@)'/}&company_id={tpl:$companyInfo.company_id/}">返回</a></span>
		<span style="float:right;"><a class = "pb_btn_dark_1" href="javascript:;" id="add_banner">新增</a> </span>
</div>
	</fieldset>
	<fieldset><legend>{tpl:$typeName/}列表</legend>
		<div style="height: auto;overflow: scroll !important;width: 80%;">
			<table  align="center" class="table table-bordered table-striped" style="overflow: scroll;max-width: none;width: 1200px;">
				<tr class="hover">
					<td width = "15%">Banner</td><td>文字</td><td>跳转链接</td><td>标签</td><td width = "5%">排序</td><td width = "12%">生效时间</td><td width = "15%">操作</td></tr>
			{tpl:loop $bannerList $pos $picInfo}
			<tr class="hover">
				<td align="center"><img src="{tpl:$picInfo.img_url/}" width='150' height='130'/></td>
				<td align="center">{tpl:$picInfo.text/}</td>
				<td align="center">{tpl:$picInfo.img_jump_url/}</td>
				<td align="center">{tpl:$picInfo.title/}</td>
				<td align="center">{tpl:$picInfo.sort/}</td>
				<td align="center">{tpl:$picInfo.start_time/}<p>{tpl:$picInfo.end_time/}</td>
				<td align="center"><a class = "pb_btn_grey_1" href="javascript:;" onclick="BannerDelete('{tpl:$pos/}')">删除</a>
					  <a class = "pb_btn_light_1" href="javascript:;" onclick="BannerModify('{tpl:$pos/}');">修改</a></td>
			</tr>
			{/tpl:loop}
			</table>
		</div>
</form>
{tpl:tpl contentFooter/}
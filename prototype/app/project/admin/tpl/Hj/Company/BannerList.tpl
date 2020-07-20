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
		[ <a href="?{tpl:$currentPage func='urldecode(@@)'/}&company_id={tpl:$companyInfo.company_id/}"><img src="/icon/return.png" width='30' height='30'/></a> | <a href="javascript:;" id="add_banner"><img src="/icon/add.png" width='30' height='30'/></a> ]
	</fieldset>
	<fieldset><legend>{tpl:$typeName/}列表</legend>
		<table width="99%" align="center" class="table table-bordered table-striped" >
			<tr class="hover"><td colspan="2">Banner列表</td></tr>
			{tpl:loop $bannerList $pos $picInfo}
			<tr class="hover">
				<td align="center"><img src="{tpl:$picInfo.img_url/}" width='150' height='130'/><p>文字：{tpl:$picInfo.text/}<p>跳转：{tpl:$picInfo.img_jump_url/}<p>标签：{tpl:$picInfo.title/}<p>排序：{tpl:$picInfo.sort/}<p>
				<p>生效时间：<p>{tpl:$picInfo.start_time/}<p>{tpl:$picInfo.end_time/}</td>
				<td align="center"><a  href="javascript:;" onclick="BannerDelete('{tpl:$pos/}')"><img src="/icon/del.png" width='30' height='30'/></a>
					|  <a href="javascript:;" onclick="BannerModify('{tpl:$pos/}');"><img src="/icon/edit2.png" width='30' height='30'/></a></td>
			</tr>
			{/tpl:loop}
		</table>
</form>
{tpl:tpl contentFooter/}
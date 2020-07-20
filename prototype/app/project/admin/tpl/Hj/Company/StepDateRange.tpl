{tpl:tpl contentHeader/}
<script type="text/javascript">
	$(document).ready(function(){
		$('#add_date').click(function(){
			addDateBox = divBox.showBox('{tpl:$this.sign/}&ac=step.date.range.add&company_id='+$('#company_id').val(), {title:'添加日期段到'+$('#company_name').val(),width:500,height:400});
		});
	});

	function DateDelete(did){
		msg = '是否删除?'
		deleteDateBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=step.date.range.delete&date_id=' + did;}});
	}

	function DateModify(did){
		modifyDateBox = divBox.showBox('{tpl:$this.sign/}&ac=step.date.range.modify&date_id=' + did, {title:'修改日期段',width:600,height:350});
	}

</script>
<div class="br_bottom"></div>
<form id="company_banner_form" name="company_banner_form" action="{tpl:$this.sign/}&ac=company.banner.update" method="post">
	<input type="hidden" name="company_id" id="company_id" value="{tpl:$companyInfo.company_id/}" />
	<input type="hidden" name="company_name" id="company_name" value="{tpl:$companyInfo.company_name/}" />
	<fieldset>
		[ <a href="{tpl:$this.sign/}&company_id={tpl:$companyInfo.company_id/}"><img src="/icon/return.png" width='30' height='30'/></a> | <a href="javascript:;" id="add_date"><img src="/icon/add.png" width='30' height='30'/></a> ]
	</fieldset>
	<fieldset><legend>{tpl:$companyInfo.company_name/} 健步走自定义日期段列表列表</legend>
		<table width="99%" align="center" class="table table-bordered table-striped" >
			<tr>
				<th align="center" class="rowtip">开始日期</th>
				<th align="center" class="rowtip">结束如期</th>
				<th align="center" class="rowtip">标题</th>
				<th align="center" class="rowtip">创建时间</th>
				<th align="center" class="rowtip">更新时间</th>
				<th align="center" class="rowtip">操作</th>
			</tr>

			{tpl:loop $DateRange.DateList $key $dateInfo}
			<tr class="hover">
				<td align="center">{tpl:$dateInfo.start_date/}</td>
				<td align="center">{tpl:$dateInfo.end_date/}</td>
				<td align="center">{tpl:$dateInfo.detail.title/}</td>
				<td align="center">{tpl:$dateInfo.create_time/}</td>
				<td align="center">{tpl:$dateInfo.update_time/}</td>
				<td align="center"><a  href="javascript:;" onclick="DateDelete('{tpl:$dateInfo.date_id/}')"><img src="/icon/del.png" width='30' height='30'/></a>
					|  <a href="javascript:;" onclick="DateModify('{tpl:$dateInfo.date_id/}');"><img src="/icon/edit2.png" width='30' height='30'/></a></td>
			</tr>
			{/tpl:loop}
			<tr><th colspan="10" align="center" class="rowtip">{tpl:$page_content/}</th></tr>

		</table>
</form>
{tpl:tpl contentFooter/}
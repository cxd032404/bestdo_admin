{tpl:tpl contentHeader/}
<script type="text/javascript">
	$(document).ready(function(){
		$('#add_page_element_detail').click(function(){
			addPageElementDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=page.element.single.detail.add&element_id='+$('#element_id').val(), {title:'添加元素详情到'+$('#element_type_name').val(),width:500,height:250});
		});
	});

	function pageElementDetailDelete(pos){
		msg = '是否删除?'
		deletePageElementDetailBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=page.element.single.detail.delete&element_id=' + $('#element_id').val() + '&pos=' + pos;}});
	}

	function pageElementDetailModify(pos){
		modifyPageElementDetailBox = divBox.showBox('{tpl:$this.sign/}&ac=page.element.single.detail.modify&element_id=' + $('#element_id').val() + '&pos=' + pos, {title:'修改页面元素',width:600,height:350});
	}

</script>
<div class="br_bottom"></div>
<form id="page_element_detail_update_form" name="page_element_detail_update_form" action="{tpl:$this.sign/}&ac=page.element.detail.update" method="post">
<input type="hidden" id="element_type" name="element_type" value="{tpl:$elementInfo.element_type/}" />
<input type="hidden" id="element_type_name" name="element_type_name" value="{tpl:$elementTypeInfo.element_type_name/}" />
<input type="hidden" id="element_id" name="element_id" value="{tpl:$elementInfo.element_id/}" />
<input type="hidden" name="page_id" id="page_id" value="{tpl:$pageInfo.page_id/}" />

	<fieldset>
		[ <a href="{tpl:$this.sign/}&ac=page.detail&page_id={tpl:$elementInfo.page_id/}"><img src="/icon/return.png" width='30' height='30'/></a> | <a href="javascript:;" id="add_page_element_detail"><img src="/icon/add.png" width='30' height='30'/></a> ]
	</fieldset>
	<fieldset><legend>页面元素详情</legend>
		<table width="99%" align="center" class="table table-bordered table-striped" >
			<tr class="hover">
				<td>元素名称</td>
				<td align="left">{tpl:$elementInfo.element_name/}</td>
			</tr>
			<tr class="hover">
				<td>元素标识</td>
				<td align="left">{tpl:$elementInfo.element_sign/}</td>
			</tr>
			<tr class="hover">
				<td>元素类型</td>
				<td align="left">{tpl:$elementTypeInfo.element_type_name/}</td>
			</tr>
			<tr class="hover"><td colspan="2">元素详情</td></tr>
			{tpl:loop $elementInfo.detail $pos $picInfo}
			<tr class="hover">
				<td align="center"><img src="{tpl:$picInfo.img_url/}" width='150' height='130'/><p>{tpl:$picInfo.img_jump_url/}</td>
				<td align="center"><a  href="javascript:;" onclick="pageElementDetailDelete('{tpl:$pos/}')"><img src="/icon/del.png" width='30' height='30'/></a>
					|  <a href="javascript:;" onclick="pageElementDetailModify('{tpl:$pos/}');"><img src="/icon/edit2.png" width='30' height='30'/></a></td>
			</tr>
			{/tpl:loop}
			<tr class="noborder"><td></td>
				<td><button type="submit" id="page_element_detail_update_submit">提交</button></td>
			</tr>
		</table>
</form>
<script type="text/javascript">
	$('#page_element_detail_update_submit').click(function(){
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
		$('#page_element_detail_update_form').ajaxForm(options);
	});
</script>
{tpl:tpl contentFooter/}
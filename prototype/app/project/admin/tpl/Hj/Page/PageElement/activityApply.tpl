{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="page_element_detail_update_form" name="page_element_detail_update_form" action="{tpl:$this.sign/}&ac=page.element.detail.update" method="post">
<input type="hidden" id="element_type" name="element_type" value="{tpl:$elementInfo.element_type/}" />
<input type="hidden" name="element_id" id="element_id" value="{tpl:$elementInfo.element_id/}" />
<input type="hidden" name="page_id" id="page_id" value="{tpl:$pageInfo.page_id/}" />

<fieldset>
	[ <a href="{tpl:$this.sign/}&ac=page.detail&page_id={tpl:$elementInfo.page_id/}">返回</a> ]
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
<tr class="hover">
<td>来自参数：</td>
	<td align="left">
		<input type="radio" name="detail[id_from]" id="detail[id_from]" value="from_params" {tpl:if(isset($elementInfo.detail.from_params))}checked{/tpl:if}
		 /> 来自参数: <input type="text" class="span2" name="detail[from_params]"  id="detail[from_params]" value="{tpl:$elementInfo.detail.from_params/}" size="50" />
    </td>
</tr>
<tr class="hover">
<td>对应活动：</td>
	<td align="left">		<input type="radio" name="detail[id_from]" id="detail[id_from]" value="from_id" {tpl:if(isset($elementInfo.detail.activity_id))}checked{/tpl:if}
		 />
		<select name="detail[activity_id]"  id="detail[activity_id]" size="1">
			{tpl:loop $activityList.ActivityList  $activityInfo}
			<option value="{tpl:$activityInfo.activity_id/}"{tpl:if($activityInfo.activity_id==$elementInfo.detail.activity_id)}selected="selected"{/tpl:if} >{tpl:$activityInfo.activity_name/}</option>
			{/tpl:loop}
		</select>
    </td>
</tr>
<td>自动报名：</td>
<td align="left">
	自动: <input type="radio" name="detail[auto]" id="detail[auto]" value="1" {tpl:if($elementInfo.detail.auto==1)}checked{/tpl:if}/>
	手动: <input type="radio" name="detail[auto]" id="detail[auto]" value="0" {tpl:if($elementInfo.detail.auto==0)}checked{/tpl:if}/>
</td>
</tr>
<tr class="hover">
	<td>报名按钮图片：</td>
	<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" />
		{tpl:if($elementInfo.detail.apply_img_url!="")}
		已选图片:<img src="{tpl:$elementInfo.detail.apply_img_url/}" width="30px;" height="30px;"/>
	<br>
		{/tpl:if}
	</td>
</tr>
<tr class="hover">
	<td rowspan="2">报名完成按钮图片：</td>
	<td align="left"><input name="upload_img[2]" type="file" id="upload_img[2]" />
		{tpl:if($elementInfo.detail.applied_img_url!="")}
		已选图片:<img src="{tpl:$elementInfo.detail.applied_img_url/}" width="30px;" height="30px;"/>
	<br>
		{/tpl:if}
	</td>
</tr>
<tr class="hover">
	<td align="left">
		跳转链接：<input type="text" class="span4" name="detail[applied_url]"  id="detail[applied_url]" value="{tpl:$elementInfo.detail.from_params/}" size="50" />
	</td>
</tr>
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
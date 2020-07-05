{tpl:tpl contentHeader/}
<script type="text/javascript">
	function headerImgDelete(lid){
		headerImgDeleteBox = divBox.confirmBox({content:'是否确认删除吗?删除后将不可恢复，需要重新上传',ok:function(){location.href = '{tpl:$this.sign/}&ac=header.img.remove&list_id=' + lid;}});
	}
</script>
<div class="br_bottom"></div>
<form id="list_update_form" name="list_update_form" action="{tpl:$this.sign/}&ac=list.update" method="post">
<input type="hidden" name="list_id" value="{tpl:$listInfo.list_id/}" />

<table width="99%" align="center" class="table table-bordered table-striped" >
<tr class="hover">
<td>列表名称</td>
<td align="left"><input name="list_name" type="text" class="span2" id="list_name" value="{tpl:$listInfo.list_name/}" size="50" /></td>
</tr>
	<tr class="hover">
		<td>头部图片上传：</td>
		<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" />
			{tpl:if($listInfo.detail.header_url!="")}
			已选图片:<img src="{tpl:$listInfo.detail.header_url/}" width="30px;" height="30px;"/>
		<br>
			<a  href="javascript:;" onclick="headerImgDelete('{tpl:$listInfo.list_id/}','{tpl:$key/}')">删除</a> {/tpl:if}
		</td>
	</tr>
<tr class="hover"><td>类型</td>
	<td align="left">	<select name="type"  id="type" size="1">
			<option value="vote" {tpl:if("vote"==$listInfo.detail.type)}selected="selected"{/tpl:if}>投票</option>
			<option value="kudo" {tpl:if("kudo"==$listInfo.detail.type)}selected="selected"{/tpl:if}>点赞</option>
		</select></td>
</tr>
<tr class="hover"><td>属于企业</td>
	<td align="left">	<select name="company_id"  id="company_id" size="1" onchange="getActivityByCompany()">
			{tpl:loop $companyList  $company_info}
			<option value="{tpl:$company_info.company_id/}"{tpl:if($company_info.company_id==$listInfo.company_id)}selected="selected"{/tpl:if} >{tpl:$company_info.company_name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
	<tr class="hover"><td>对应活动</td>
		<td align="left">
			<select name="activity_id"  id="activity_id" size="1">
				<option value="0" {tpl:if($listInfo.activity_id==0)}selected="selected"{/tpl:if} >不指定</option>
				{tpl:loop $activityList $activity_id $activity_info}
				<option value="{tpl:$activity_info.activity_id/}" {tpl:if($listInfo.activity_id==$activity_info.activity_id)}selected="selected"{/tpl:if} >{tpl:$activity_info.activity_name/}</option>
				{/tpl:loop}
			</select>
		</td>
	</tr>
	<tr class="hover"><td>仅限管理员提交</td>
		<td align="left">
			<select name="detail[manager_only]"  id="detail[manager_only]" size="1">
				<option value="0"{tpl:if(0==$listInfo.detail.manager_only)}selected="selected"{/tpl:if} >所有人</option>
				<option value="1"{tpl:if(1==$listInfo.detail.manager_only)}selected="selected"{/tpl:if} >仅限管理员</option>
			</select></td>
	</tr>
<tr class="hover"><td>列表分类</td>
	<td align="left">
		<select name="list_type"  id="list_type" size="1">
			{tpl:loop $listTypeList  $type $type_info}
			<option value="{tpl:$type/}"{tpl:if($type==$listInfo.list_type)}selected="selected"{/tpl:if} >{tpl:$type_info.name/}</option>
			{/tpl:loop}
		</select></td>
</tr>
	<tr class="hover"><td>关联列表</td>
		<td align="left">
			<select name="detail[connect]"  id="detail[connect]" size="1">
				<option value="0" {tpl:if($listInfo.detail.connect==0)}selected="selected"{/tpl:if} >不关联</option>
				{tpl:loop $listList $list_id $list_info}
				<option value="{tpl:$list_id/}" {tpl:if($list_id==$listInfo.detail.connect)}selected="selected"{/tpl:if} >{tpl:$list_info.list_name/}</option>
				{/tpl:loop}
			</select>
		</td>
	</tr>
	<td>关联列表名称</td>
	<td align="left"><input name="detail[connect_name]" type="text" class="span2" id="detail[connect_name]" value="{tpl:$listInfo.detail.connect_name/}" size="50" /></td>
	</tr>
	<tr class="hover"><td>提交后动作</td>
		<td align="left">
			<select name="detail[after_action]"  id="after_action" size="1">
				{tpl:loop $afterActionList $action $action_info}
				<option value="{tpl:$action/}" {tpl:if($listInfo.detail.after_action==$action)}selected="selected"{/tpl:if} >{tpl:$action_info.name/}</option>
				{/tpl:loop}
			</select>
		</td>
	</tr>
	<tr class="hover">
		<td>提交后跳转链接</td>
		<td align="left"><input name="detail[after_url]" type="text" class="span4" id="detail[after_url]" value="{tpl:$listInfo.detail.after_url/}" size="50" /></td>
	</tr>
	<tr class="hover"><td>自定义数量</td>
		<td align="left">图片：
			<select name="detail[limit][pic]"  id="detail[limit][pic]" size="1" class="span2">
				<?php for($i=0;$i<=10;$i++){?>
				<option value="{tpl:$i/}" {tpl:if($i==$listInfo.detail.limit.pic)}selected="selected"{/tpl:if} >{tpl:$i/}</option>
				<?php }?>
			</select>
			视频：
			<select name="detail[limit][video]"  id="detail[limit][video]" size="1" class="span2">
				<?php for($i=0;$i<=3;$i++){?>
				<option value="{tpl:$i/}" {tpl:if($i==$listInfo.detail.limit.video)}selected="selected"{/tpl:if}>{tpl:$i/}</option>
				<?php }?>
			</select>
			文本文件：
			<select name="detail[limit][txt]"  id="detail[limit][txt]" size="1" class="span2">
				<?php for($i=0;$i<=3;$i++){?>
				<option value="{tpl:$i/}"{tpl:if($i==$listInfo.detail.limit.txt)}selected="selected"{/tpl:if}>{tpl:$i/}</option>
				<?php }?>
			</select>
			文本域：
			<select name="detail[limit][textarea]"  id="detail[limit][textarea]" size="1" class="span2">
				<option value="1" {tpl:if(1==$listInfo.detail.limit.textarea)}selected="selected"{/tpl:if}>需要</option>
				<option value="0" {tpl:if(0==$listInfo.detail.limit.textarea)}selected="selected"{/tpl:if}>不需要</option>
			</select>
		</td>
	</tr>
	<tr class="hover"><td colspan = 2>备注</td></tr>
	<tr class="hover"><td colspan = 2>
			<textarea style="width:500px; height:200px" name="detail[comment]" id="detail[comment]" >{tpl:$listInfo.detail.comment/}</textarea>
		</td>
	</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="list_update_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#list_update_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '列表名称不能为空，请修正后再次提交';
				errors[2] = '列表名称'+$('#list_name').val()+'已重复，请修正后再次提交';
				errors[3] = '至少要选择一个可上传资源，请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '修改列表成功';
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&company_id=' + $('#company_id').val()+ '&list_type=' + $('#list_type').val()+ '&ac=' + jsonResponse.ac);}});
			}
		}
	};
	$('#list_update_form').ajaxForm(options);
});

function getActivityByCompany()
{
	company=$("#company_id");
	$.ajax
	({
		type: "GET",
		url: "?ctl=hj/activity&ac=get.activity.by.company&company_id="+company.val(),
		success: function(msg)
		{
			$("#activity_id").html(msg);
		}});
}
</script>
{tpl:tpl contentFooter/}
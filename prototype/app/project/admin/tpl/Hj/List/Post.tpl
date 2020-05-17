{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="post_form" name="post_form" enctype="multipart/form-data" action="{tpl:$postUrl/}" method="post">
<input type="hidden" name="list_id" id="list_id" value="{tpl:$listInfo.list_id/}" />
	<input type="hidden" name="UserToken" id="UserToken" value="{tpl:$token/}" />
	<table width="99%" align="center" class="table table-bordered table-striped" >
	{tpl:if(isset($typeInfo.upload.pic)||isset($listInfo.detail.limit.pic))}
	<?php
		if(isset($typeInfo['custom']))
		{
			$limit = $listInfo['detail']['limit']['pic']??0;
		}
		else
		{
			$limit = $typeInfo['upload']['pic']??0;
		}
		for($i=1;$i<=$limit;$i++)
		{
		?>
		<tr class="hover">
			{tpl:if($i==1)}<td rowspan="{tpl:$limit/}">图片上传：</td>{/tpl:if}
		<td align="left"><input name="upload_img[{tpl:$i/}]" type="file" id="upload_img[{tpl:$i/}]" />
		</td>
	</tr>
		<?php
		}?>
	{/tpl:if}
	{tpl:if(isset($typeInfo.upload.video)||isset($listInfo.detail.limit.video))}
	<?php
		if(isset($typeInfo['custom']))
		{
			$limit = $listInfo['detail']['limit']['video']??0;
		}
		else
		{
			$limit = $typeInfo['upload']['video']??0;
		}
		for($i=1;$i<=$limit;$i++)
		{
		?>
	<tr class="hover">
		{tpl:if($i==1)}<td rowspan="{tpl:$limit/}">视频上传：</td>{/tpl:if}
		<td align="left"><input name="upload_video[{tpl:$i/}]" type="file" id="upload_video[{tpl:$i/}]" />
		</td>
	</tr>
	<?php
		}?>
	{/tpl:if}

	{tpl:if(isset($typeInfo.upload.txt)||isset($listInfo.detail.limit.txt))}
	<?php
		if(isset($typeInfo['custom']))
		{
			$limit = $listInfo['detail']['limit']['txt']??0;
		}
		else
		{
			$limit = $typeInfo['upload']['txt']??0;
		}
		for($i=1;$i<=$limit;$i++)
		{
		?>
	<tr class="hover">
		{tpl:if($i==1)}<td rowspan="{tpl:$limit/}">文本上传：</td>{/tpl:if}
		<td align="left"><input name="upload_txt[{tpl:$i/}]" type="file" id="upload_txt[{tpl:$i/}]" />
		</td>
	</tr>
	<?php
		}?>
	{/tpl:if}
</tr>
	{tpl:if($listInfo.detail.limit.textarea==1)}
<tr class="hover"><td colspan = 2>文本</td></tr>
<tr class="hover"><td colspan = 2>
		<textarea style="width:500px; height:200px" name="detail[comment]" id="detail[comment]" ></textarea>
	</td>
</tr>
	{/tpl:if}
	<tr class="noborder"><td></td>
<td><button type="submit" id="post_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
	$('#post_submit').click(function(){
		var options = {
			dataType:'json',
			beforeSubmit:function(formData, jqForm, options) {},
			success:function(jsonResponse) {
				alert(jsonResponse.success);
				if (jsonResponse.success == "false") {
					//divBox.alertBox(jsonResponse.msg,function(){});
				} else {
					var message = '发布成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+'&ac=list&list_id='+$('#list_id').val());}});
				}
			}
		};
		$('#post_form').ajaxForm(options);
	});
</script>
{tpl:tpl contentFooter/}
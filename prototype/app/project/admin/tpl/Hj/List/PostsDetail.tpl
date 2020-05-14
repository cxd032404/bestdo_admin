{tpl:tpl contentHeader/}
<script type="text/javascript">
	function sourceDelete(pid,sid){
		souceDeleteBox = divBox.confirmBox({content:'是否确认删除吗?删除后将不可恢复，需要重新上传',ok:function(){location.href = '{tpl:$sourceRemoveUrl/}&post_id=' + pid + '&sid='+ sid;}});
	}
</script>
[ <a href="{tpl:$this.sign/}&ac=list&list_id={tpl:$postsInfo.list_id/}">返回</a> ]
<div class="br_bottom"></div>
<form id="post_form" name="post_form" enctype="multipart/form-data" action="{tpl:$postUrl/}" method="post">
<input type="hidden" name="post_id" id="post_id" value="{tpl:$postsInfo.post_id/}" />
	<input type="hidden" name="list_id" id="list_id" value="{tpl:$postsInfo.list_id/}" />

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
		{	$name="upload_img";
		?>
	<tr class="hover">
		{tpl:if($i==1)}<td rowspan="{tpl:$limit/}">图片上传：</td>{/tpl:if}
		<td align="left">
			<?php if(isset($postsInfo['source'][$name.".".$i])){?>
			已选图片:<img src="<?php echo $postsInfo['source'][$name.".".$i];?>" width="30px;" height="30px;"/> <a  href="javascript:;" onclick="sourceDelete('{tpl:$postsInfo.post_id/}','{tpl:$name/}.{tpl:$i/}')">删除</a>
			<br>更改图片：
		<br>
			<?php }?>
			<input name="{tpl:$name/}[{tpl:$i/}]" type="file" id="{tpl:$name/}[{tpl:$i/}]" />
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
		{	$name="upload_video";
		?>
		<tr class="hover">
			{tpl:if($i==1)}<td rowspan="{tpl:$limit/}">视频上传：</td>{/tpl:if}
			<td align="left">
				<?php if(isset($postsInfo['source'][$name.".".$i])){?>
				已选视频:<img src="<?php echo $postsInfo['source'][$name.".".$i].$video_suffix;?>" width="30px;" height="30px;"/> <a  href="javascript:;" onclick="sourceDelete('{tpl:$postsInfo.post_id/}','{tpl:$name/}.{tpl:$i/}')">删除</a>
				<br>更改视频：
				<br>
				<?php }?>
				<input name="{tpl:$name/}[{tpl:$i/}]" type="file" id="{tpl:$name/}[{tpl:$i/}]" />
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
		{	$name="upload_txt";
		?>
		<tr class="hover">
			{tpl:if($i==1)}<td rowspan="{tpl:$limit/}">文本上传：</td>{/tpl:if}
			<td align="left">
				<input name="{tpl:$name/}[{tpl:$i/}]" type="file" id="{tpl:$name/}[{tpl:$i/}]" />
			</td>
		</tr>
		<?php
		}?>
		{/tpl:if}
		{tpl:if($listInfo.detail.limit.textarea==1)}
			<tr class="hover"><td colspan = 2>文本</td></tr>
			<tr class="hover"><td colspan = 2>
					<textarea style="width:500px; height:200px" name="detail[comment]" id="detail[comment]" >{tpl:$postsInfo.content/}</textarea>
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
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
	<input type="hidden" name="currentPage" id="currentPage" value="{tpl:$currentPage/}" />
	<input type="hidden" name="list_id" id="list_id" value="{tpl:$postsInfo.list_id/}" />
	<input type="hidden" name="UserToken" id="UserToken" value="{tpl:$token/}" />
	<table width="99%" align="center" class="table table-bordered table-striped" >
		<?php $i = 1;foreach($postsInfo['source'] as $key => $file)
		{
		?>
		<tr class="hover">
			{tpl:if($i==1)}<td rowspan="{tpl:$postsInfo.source func="count(@@)"/}">已上传：</td>{/tpl:if}
			<td align="left">
				<img src="<?php echo $file.(strpos($key,'video')>0?$video_suffix:'');?>" width="30px;" height="30px;"/> <a  href="javascript:;" onclick="sourceDelete('{tpl:$postsInfo.post_id/}','{tpl:$key/}')">删除</a></td>
		</tr>
		<?php $i++;}?>
		<?php
		for($i=1;$i<=$max_files;$i++)
		{	$name="upload_files";
		?>
	<tr class="hover">
		{tpl:if($i==1)}<td rowspan="{tpl:$max_files/}">上传：</td>{/tpl:if}
		<td align="left">
			<input name="{tpl:$name/}[{tpl:$i/}]" type="file" id="{tpl:$name/}[{tpl:$i/}]" />
		</td>
	</tr>
	<?php
		}?>
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
		<tr class="hover"><td>标题</td>
			<td align="left"><input type="text" class="span3" name="title"  id="title" value="{tpl:$postsInfo.title/}" size="50" /></td>
			</td>
		</tr>
		{tpl:if($listInfo.detail.limit.textarea==1)}
			<tr class="hover"><td colspan = 2>文本</td></tr>
			<tr class="hover"><td colspan = 2>
					<textarea style="width:500px; height:200px" name="comment" id="comment" >{tpl:$postsInfo.content/}</textarea>
				</td>
			</tr>
		<script src="js/ckeditor/ckeditor.js"></script>

		<?php Third_ckeditor_ckeditor::render("comment")?>
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
				if (jsonResponse.success == "false") {
					divBox.alertBox(jsonResponse.msg,function(){});
				} else {
					var message = '发布成功';
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}'+'&ac=list&list_id='+$('#list_id').val()+ '&currentPage='+$('#currentPage').val());}});
				}
			}
		};
		$('#post_form').ajaxForm(options);
	});
</script>
{tpl:tpl contentFooter/}
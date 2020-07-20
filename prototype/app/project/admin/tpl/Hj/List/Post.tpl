{tpl:tpl contentHeader/}

<div class="br_bottom"></div>
<form id="post_form" name="post_form" enctype="multipart/form-data" action="{tpl:$postUrl/}" method="post">
<input type="hidden" name="list_id" id="list_id" value="{tpl:$listInfo.list_id/}" />
	<input type="hidden" name="currentPage" id="currentPage" value="{tpl:$currentPage/}" />
	<input type="hidden" name="UserToken" id="UserToken" value="{tpl:$token/}" />
	<table width="99%" align="center" class="table table-bordered table-striped" >
	<?php
		for($i=1;$i<=$max_files;$i++)
		{
		?>
		<tr class="hover">
			{tpl:if($i==1)}<td rowspan="{tpl:$max_files/}">上传：</td>{/tpl:if}
		<td align="left"><input name="upload_files[{tpl:$i/}]" type="file" id="upload_files[{tpl:$i/}]" />
		</td>
	</tr>
		<?php
		}?>
</tr>
		<tr class="hover"><td>标题</td>
			<td align="left"><input type="text" class="span3" name="title"  id="title" value="" size="50" /></td>
			</td>
		</tr>
	{tpl:if($listInfo.detail.limit.textarea==1)}
<tr class="hover"><td colspan = 2>文本</td></tr>
<tr class="hover"><td colspan = 2>
		<textarea style="width:500px; height:200px" name="comment" id="comment" ></textarea>
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
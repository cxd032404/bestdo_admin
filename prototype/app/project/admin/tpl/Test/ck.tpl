{tpl:tpl contentHeader/}
<div class="br_bottom"></div>
<script src="js/ckeditor/ckeditor.js"></script>

<form id="upload_form" name="upload_form" action="{tpl:$this.sign/}&ac=upload" method="post">
	<input type="hidden" name="SportsTypeId" value="1" />
	<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
		<tr class="hover"><td>图片上传1：</td>
			<td align="left"><input name="upload_img[1]" type="file" id="upload_img[1]" /></td>
		</tr>

		<tr class="hover"><td>图片上传2：</td>
			<td align="left"><input name="upload_img[2]" type="file" id="upload_img[2]" /></td>
		</tr>

		<tr class="hover"><td>图片上传3：</td>
			<td align="left"><input name="upload_img[3]" type="file" id="upload_img[3]" /></td>
		</tr>
		<tr class="hover"><td colspan = 2>富文本编辑器：</td></tr>
		<tr class="hover" ><td colspan = 2>
				<textarea name="comment" id="comment" >{tpl:$RaceCatalogInfo.RaceCatalogComment/}</textarea>
			</td>
		</tr>
		<tr class="hover"><td colspan = 2>富文本编辑器：</td></tr>
		<tr class="hover" ><td colspan = 2>
				<textarea name="comment2" id="comment2" >{tpl:$RaceCatalogInfo.RaceCatalogComment/}</textarea>
			</td>
		</tr>
		<tr class="noborder"><td></td>
			<td><button type="submit" id="upload_submit">提交</button></td>
		</tr>
	</table>
</form>

<?php Third_ckeditor_ckeditor::render("comment")?>


<?php Third_ckeditor_ckeditor::render("comment2")?>

<style>
	.ck-editor__editable {
		min-height: 300px;
	}
</style>
{tpl:tpl contentFooter/}






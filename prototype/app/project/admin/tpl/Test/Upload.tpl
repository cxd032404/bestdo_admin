{tpl:tpl contentHeader/}
<div class="br_bottom"></div>

<script src="js/ckeditor5/ckeditor.js"></script>


<form id="upload_form" name="upload_form" action="{tpl:$this.sign/}&ac=upload" method="post">
<table width="99%" align="center" class="table table-bordered table-striped" widtd="99%">
	<tr class="hover"><td>图片上传</td>
		<td align="left"><input name="upload_img[1]" type="file" class="span4" id="upload_img[1]" /></td>
	</tr>
<tr class="noborder"><td></td>
<td><button type="submit" id="upload_submit">提交</button></td>
</tr>
</table>
</form>

<script type="text/javascript">
$('#upload_submit').click(function(){
	var options = {
		dataType:'json',
		beforeSubmit:function(formData, jqForm, options) {
		},
		success:function(jsonResponse) {
			if (jsonResponse.errno) {
				var errors = [];
				errors[1] = '图片未选定请修正后再次提交';
				errors[9] = '入库失败，请修正后再次提交';
				divBox.alertBox(errors[jsonResponse.errno],function(){});
			} else {
				var message = '上传成功，图片路径为'+jsonResponse.url;
				divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}');}});
			}
		}
	};
	$('#upload_form').ajaxForm(options);
});
</script>
<script>
    ClassicEditor
        .create( document.querySelector( '#RaceCatalogComment' ),
			{
    			config: { height: '300px', width: '552px' },
				ckfinder: {
                    uploadUrl: '/callback/upload.php?type=img',
                }
			}
    )
        .catch( error => {
        console.error( error );
    } );

</script>
{tpl:tpl contentFooter/}
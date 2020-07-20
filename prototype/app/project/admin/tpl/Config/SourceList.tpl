{tpl:tpl contentHeader/}
<script type="text/javascript">
	$(document).ready(function(){
		$('#add_source').click(function(){
			addSourceBox = divBox.showBox('{tpl:$this.sign/}&ac=source.detail.add&config_sign='+$('#config_sign').val(), {title:'添加source到'+$('#config_name').val(),width:500,height:400});
		});
	});

	function SourceDelete(pos){
		msg = '是否删除?'
		deleteSourceBox = divBox.confirmBox({content:msg,ok:function(){location.href = '{tpl:$this.sign/}&ac=source.detail.delete&config_sign=' + $('#config_sign').val() + '&pos=' + pos;}});
	}

	function SourceModify(pos){
		modifySourceBox = divBox.showBox('{tpl:$this.sign/}&ac=source.detail.modify&config_sign=' + $('#config_sign').val() + '&pos=' + pos, {title:'修改Source',width:600,height:350});
	}

</script>
<div class="br_bottom"></div>
<form id="config_source_form" name="config_source_form" action="{tpl:$this.sign/}&ac=config.source.update" method="post">
	<input type="hidden" name="config_sign" id="config_sign" value="{tpl:$configInfo.config_sign/}" />
	<input type="hidden" name="config_name" id="config_name" value="{tpl:$configInfo.config_name/}" />
	<fieldset>
		[ <a href="{tpl:$this.sign/}&config_sign={tpl:$configInfo.config_sign/}"><img src="/icon/return.png" width='30' height='30'/></a> | <a href="javascript:;" id="add_source"><img src="/icon/add.png" width='30' height='30'/></a> ]
	</fieldset>
	<fieldset><legend>{tpl:$configInfo.config_name/} 资源列表</legend>
		<table width="99%" align="center" class="table table-bordered table-striped" >
			<tr class="hover"><td colspan="2">资源列表</td></tr>
			{tpl:loop $configInfo.content $pos $picInfo}
			<tr class="hover">
				<td align="center"><img src="{tpl:$picInfo.img_url/}" width='150' height='130'/><p>文字：{tpl:$picInfo.text/}<p>跳转：{tpl:$picInfo.img_jump_url/}<p>标签：{tpl:$picInfo.title/}</td>
				<td align="center"><a  href="javascript:;" onclick="SourceDelete('{tpl:$pos/}')"><img src="/icon/del.png" width='30' height='30'/></a>
					|  <a href="javascript:;" onclick="SourceModify('{tpl:$pos/}');"><img src="/icon/edit2.png" width='30' height='30'/></a></td>
			</tr>
			{/tpl:loop}
		</table>
</form>
<script type="text/javascript">
	$('#config_source_submit').click(function(){
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
					divBox.confirmBox({content:message,ok:function(){windowParent.getRightHtml('{tpl:$this.sign/}' + '&ac=source.detail&config_sign=' + $('#config_sign').val());}});
				}
			}
		};
		$('#config_source_form').ajaxForm(options);
	});
</script>
{tpl:tpl contentFooter/}
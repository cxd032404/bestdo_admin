<?php
# include这两个文件之一：
/*
qrlib.php for full version (also you have to provide all library files
form package plus cache dir)

OR phpqrcode.php for merged version (only one file,
but slower and less accurate code because disabled cache
and quicker masking configured)
*/
# 两句话解释：
# 包含qrlib.php的话需要同其它文件放到一起：文件、文件夹。
# phpqrcode.php是合并后版本，只需要包含这个文件，但生成的图片速度慢而且不太准确
# 以下给出两种用法：
//include 'phpqrcode/phpqrcode.php';

include('phpqrcode/phpqrcode.php');
# 创建一个二维码文件
//QRcode::png('code data text', 'filename.png');
// creates file

# 生成图片到浏览器

QRcode::png(urldecode($_GET['url']));
// creates code image and outputs it directly into browser
?>

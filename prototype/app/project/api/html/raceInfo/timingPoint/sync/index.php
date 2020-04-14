<?php
header("Content-type: application/json; charset=utf-8");
$Name = urldecode($_GET["raceName"]);
$url = "http://api.xrace.cn/?ctl=horizon&ac=get.timing.point.list&raceName=".$Name;
echo file_get_contents($url);

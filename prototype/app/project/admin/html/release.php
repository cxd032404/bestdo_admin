<?php
$command = "git checkout master && git status  && git pull";
(exec($command,$return));
echo implode("\n",$return)."\n";
unset($return);
$command = "cd ../../../../../CommonConfig/ && cp CacheConfigOnline.php CacheConfig.php";
(exec($command,$return));
echo implode("\n",$return)."\n";
unset($return);
$command = "cd ../../../../../CommonConfig/ && cp databaseConfigOnline.php databaseConfig.php";
(exec($command,$return));
unset($return);
$command = "cd ../../../../../CommonConfig/ && cp urlConfigOnline.php urlConfig.php && ls";
(exec($command,$return));
echo implode("\n",$return)."\n";
unset($return);

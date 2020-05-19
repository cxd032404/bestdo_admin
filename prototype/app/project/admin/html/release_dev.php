<?php
$command = "git checkout dev && git status  && git pull";
(exec($command,$return));
echo implode("\n",$return)."\n";
unset($return);
$command = "cd ../../../../../CommonConfig/ && cp CacheConfigDev.php CacheConfig.php";
(exec($command,$return));
echo implode("\n",$return)."\n";
unset($return);
$command = "cd ../../../../../CommonConfig/ && cp databaseConfigDev.php databaseConfig.php";
(exec($command,$return));
unset($return);
$command = "cd ../../../../../CommonConfig/ && cp urlConfigDev.php urlConfig.php && ls";
(exec($command,$return));
echo implode("\n",$return)."\n";
unset($return);

<?php
$command = "git status  && git pull";
(exec($command,$return));
echo implode("\n",$return)."\n";
unset($return);
$command = "cd ../../../../../CommonConfig/ && cp CacheConfigLocal.php CacheConfig.php";
(exec($command,$return));
echo implode("\n",$return)."\n";
unset($return);
$command = "cd ../../../../../CommonConfig/ && cp databaseConfigLocal.php databaseConfig.php";
(exec($command,$return));
unset($return);
$command = "cd ../../../../../CommonConfig/ && cp urlConfigLocal.php urlConfig.php && ls";
(exec($command,$return));
echo implode("\n",$return)."\n";
unset($return);

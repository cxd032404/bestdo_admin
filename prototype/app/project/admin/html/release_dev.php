<?php
$command = "git checkout dev && git status  && git pull";
(exec($command,$return));
echo implode("\n",$return)."\n";
unset($return);
$command = "cd ../../../../../CommonConfig/ && cp CacheConfigDev.php CacheConfig.php && ls";
(exec($command,$return));
echo implode("\n",$return)."\n";
unset($return);
$command = "cd ../../../../../CommonConfig/ && cp databaseConfigDev.php databaseConfig.php && ls";
(exec($command,$return));
echo implode("\n",$return)."\n";
unset($return);

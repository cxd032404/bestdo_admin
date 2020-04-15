<?php
//namespace app\lib\Third\oss;
use OSS\OssClient;
use app\lib\Third\oss\Common;

class Third_aliyun_oss_OssClientFile
{
    private $bucketName;
    private $client;
    private $local_file;
    private $object;

    /*
     *
     */
    public static function upload2Oss($fileArr = [],/*$fileName,$fileFullPath,*/$ossConfig)
    {
        include('Common.php');
        $bucket = $ossConfig['BUCKET'];
        $client = Common::getOssClient($ossConfig);
        $returnArr = [];
        foreach($fileArr as $key => $file)
        {
            if($file['error'] == 0)
            {
                $local_file = $file['path'];
                $object = "public/xrace/images".$file['path_root'];
                try {
                    $res = $client->uploadFile($bucket, $object, $local_file);
                    $returnArr[$key] = $res;
                }catch(\OSS\Core\OssException $e) {
                    $returnArr[$key] = false;
                }
            }
        }
        return $returnArr;
    }
}

<?php
namespace app\lib\Third\oss;
use OSS\OssClient;
//use Log;

class OssClientFile
{
    private $bucketName;
    private $client;
    private $local_file;
    private $object;

    /*
     *
     */
    public function uploadMatchCdn($fileName,$fileFullPath,$ossConfig)
    {
        include('Common.php');
        $bucket = $ossConfig['BUCKET'];
        $client = Common::getOssClient($ossConfig);
        $local_file = $fileFullPath;
        $object = "public/xrace/images".$fileName;
        try {
            $res = $client->uploadFile($bucket, $object, $local_file);
            return $res;
        }catch(OssException $e) {
            //Log::info("update error code:".$e->getCode()." msg:".$e->getMessage());
            // todo 日志
            return false;
        }
    }
}

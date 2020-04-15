<?php
//namespace app\lib\Third\oss;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class Third_aliyun_sms_SmsClient
{
    private $bucketName;
    private $client;
    private $local_file;
    private $object;

    /*
     *
     */
    public static function sendSms($mobile = [],$template = "",$params = [],$smsConfig)
    {
        AlibabaCloud::accessKeyClient($smsConfig['ACCESS_KEY_ID'],$smsConfig['ACCESS_KEY_SECRET'])
            ->regionId('cn-hangzhou')
            ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => $smsConfig['regionId'],
                        'PhoneNumbers' => "18621758237",
                        'SignName' => $smsConfig['signName'],
                        'TemplateCode' => $smsConfig['template'][$template]??$smsConfig['template']["reg"],
                        'TemplateParam' => json_encode($params)
                    ],
                ])
                ->request();
            print_r($result->toArray());
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }
}

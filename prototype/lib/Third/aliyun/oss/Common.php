<?php
namespace app\lib\Third\oss;
use OSS\OssClient;
use OSS\Core\OssException;

/**
 * Class Common
 *
 * 示例程序【Samples/*.php】 的Common类，用于获取OssClient实例和其他公用方法
 */
class Common
{
    /**
     * 根据Config配置，得到一个OssClient实例
     *
     * @return OssClient 一个OssClient实例
     */
    public static function getOssClient($ossConfig)
    {
        try {
            $ossClient = new OssClient(
                $ossConfig['ACCESS_KEY_ID'],
                $ossConfig['ACCESS_KEY_SECRET'],
                $ossConfig['END_POINT'], false);
        } catch (OssException $e) {
            //Log::Info(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            //Log::Info($e->getMessage() . "\n");
            return null;
        }
        return $ossClient;
    }

    public static function getBucketName()
    {
        return getenv('OSS_BUCKET');
    }

    /**
     * 工具方法，创建一个bucket
     */
    public static function createBucket()
    {
        $ossClient = self::getOssClient();
        if (is_null($ossClient)) exit(1);
        $bucket = self::getBucketName();
        $acl = OssClient::OSS_ACL_TYPE_PUBLIC_READ;
        try {
            $ossClient->createBucket($bucket, $acl);
        } catch (OssException $e) {
            //Log::Info(__FUNCTION__ . ": FAILED\n");
            //Log::Info($e->getMessage() . "\n");
            return;
        }
        print(__FUNCTION__ . ": OK" . "\n");
    }

    /**
     * Wait for bucket meta sync
     */
    public static function waitMetaSync()
    {
        if (getenv('TRAVIS')) {
            sleep(10);
        }
    }

    /*
         public static function generateDetailImg($source = '', $matchInfo, $type = 'report') {
        $imgCdnUrlPrefix = 'https://gb01static1qaz2wsx.hexinyuan.com.cn/goodball/team/';
        $source =  getcwd() . "/public/Public/img/BG@2x.png";
        $font = getcwd() ."/public/Public/simsun.ttf";
        $vs = getcwd() . "/public/Public/img/VS@2x.png";
        $zbIma = getcwd() . "/public/Public/img/1@2x.png";
        $defaultTeamA = getcwd() . "/public/Public/img/d1.png";
        $defaultTeamB = getcwd() . "/public/Public/img/d2.png";
        if($type == 'lineup' || $type == "prediction")
        {
            $main = imagecreatefrompng($source);
            $width = imagesx ( $main );
            $height = imagesy ( $main );

            $target = imagecreatetruecolor ( $width, $height );
            $white = imagecolorallocate ( $target, 255, 255, 255 );
            imagefill ( $target, 0, 0, $white );

            imagecopyresampled ( $target, $main, 0, 0, 0, 0, $width, $height, $width, $height );

            $fontSize = 36;//磅值字体
            $fontSizeA = 36;//磅值字体
            $fontSizeB = 36;//磅值字体


            $teamAName = $matchInfo['home_name'];
            $teamBName = $matchInfo['away_name'];
            //
            $size = imagettfbbox($fontSize, 0, $font, $teamAName);
            $start = 374 - ($size[2] - $size[0]);
            if(($size[2] - $size[0])>374)
            {
                $fontSizeA = 24;
                $size = imagettfbbox($fontSizeA, 0, $font, $teamAName);
                $start = 374 - ($size[2] - $size[0]);
            }
            else
            {
                $fontSizeA = $fontSize;
            }

            $size = imagettfbbox($fontSize, 0, $font, $teamBName);
            if(($size[2] - $size[0])>374)
            {
                $fontSizeB = 24;
            }
            else
            {
                $fontSizeB = $fontSize;
            }

            $fontColor = imagecolorallocate ( $target, 255, 255, 255 );//字的RGB颜色
            // $fontBox = imagettfbbox($fontSize, 0, $font, $teamAName);//文字水平居中实质
            imagettftext ( $target, $fontSize, 0, $start, 110, $fontColor, $font, $teamAName);
            imagettftext ( $target, $fontSize, 0, 946, 110 , $fontColor, $font, $teamBName);
            $dateFS = 24;
            $date = date('m-d', strtotime($matchInfo['match_time']));
            imagettftext ( $target, $dateFS, 0, 615, 74, $fontColor, $font, $date);

            $time = date('H:i', strtotime($matchInfo['match_time']));
            $fontColortime = imagecolorallocate ( $target, 254, 238, 187 );//字的RGB颜色
            $teamAId = $matchInfo['home_team_id'];
            $teamBId = $matchInfo['away_team_id'];
            imagettftext ( $target, $fontSize, 0, 594, 136, $fontColortime, $font, $time);
            //bof of 合成图片
            $urlA = $imgCdnUrlPrefix. $teamAId . '.png';
            $urlB = $imgCdnUrlPrefix. $teamBId . '.png';
            $ta = @file_get_contents( $urlA);
            $tb = @file_get_contents( $urlB);
            $teamA = $ta!=false?imagecreatefrompng( $urlA):imagecreatefrompng($defaultTeamA);
            $teamB = $tb!=false?imagecreatefrompng( $urlB):imagecreatefrompng($defaultTeamB);
            $newTeamA = imagecreatetruecolor('100', '100');

            $color = imagecolorallocatealpha($newTeamA, 255, 255, 255,127);
            imagefill($newTeamA, 0, 0, $color);

            imageColorTransparent($newTeamA, $color);

            $newTeamB = imagecreatetruecolor('100', '100');
            $color = imagecolorallocatealpha($newTeamB, 255, 255, 255,127);
            imagefill($newTeamB, 0, 0, $color);
            imageColorTransparent($newTeamB, $color);

            imagecopyresampled($newTeamA, $teamA, 0, 0, 0, 0, '100', '100', imagesx($teamA), imagesy($teamA));
            imagecopyresampled($newTeamB, $teamB, 0, 0, 0, 0, '100', '100', imagesx($teamB), imagesy($teamB));
            // imagecopyresampled($newVs, $vsIma, 0, 0, 0, 0, '32', '28', imagesx($vsIma), imagesy($vsIma));

            imagecopymerge ( $target, $newTeamA, 402, 40, 0, 0, imagesx ( $newTeamA ), imagesy ( $newTeamA ), 100 );
            imagecopymerge ( $target, $newTeamB, 818, 40, 0, 0, imagesx ( $newTeamB ), imagesy ( $newTeamB ), 100 );
            //eof of 合成图片
            @mkdir ( '.public/Public/image' );
            $time = self::msectime();
            $filename = $type.$matchInfo['match_id'].'.png';
            imagepng ( $target, './public/Public/image/' . $filename);
            $img = $filename;
        }
        elseif($type == 'report')
        {
            $main = imagecreatefrompng($source);
            $width = imagesx ( $main );
            $height = imagesy ( $main );

            $target = imagecreatetruecolor ( $width, $height );
            $white = imagecolorallocate ( $target, 255, 255, 255 );
            imagefill ( $target, 0, 0, $white );

            imagecopyresampled ( $target, $main, 0, 0, 0, 0, $width, $height, $width, $height );

            $fontSize = 36;//磅值字体
            $fontSizeA = 36;//磅值字体
            $fontSizeB = 36;//磅值字体

            $teamAName = $matchInfo['home_name'];
            $teamBName = $matchInfo['away_name'];

            $size = imagettfbbox($fontSize, 0, $font, $teamAName);
            $start = 374 - ($size[2] - $size[0]);
            if(($size[2] - $size[0])>374)
            {
                $fontSizeA = 24;
                $size = imagettfbbox($fontSizeA, 0, $font, $teamAName);
                $start = 374 - ($size[2] - $size[0]);
            }
            else
            {
                $fontSizeA = $fontSize;
            }

            $size = imagettfbbox($fontSize, 0, $font, $teamBName);
            if(($size[2] - $size[0])>374)
            {
                $fontSizeB = 24;
            }
            else
            {
                $fontSizeB = $fontSize;
            }
            // var_dump($size[2] - $sina[0]);

            $fontColor = imagecolorallocate ( $target, 255, 255, 255 );//字的RGB颜色
            // $fontBox = imagettfbbox($fontSize, 0, $font, $teamAName);//文字水平居中实质
            imagettftext ( $target, $fontSizeA, 0, $start, 110, $fontColor, $font, $teamAName);
            imagettftext ( $target, $fontSizeB, 0, 946, 110 , $fontColor, $font, $teamBName);

            $dateFS = 24;
            $date = date('m-d', strtotime($matchInfo['match_time']));
            imagettftext ( $target, $dateFS, 0, 615, 74, $fontColor, $font, $date);

            // $time = date('H:i', $matchInfo['match_time']->sec - 3600*8);
            $fontColortime = imagecolorallocate ( $target, 254, 238, 187 );//字的RGB颜色
            $teamAId = $matchInfo['home_team_id'];
            $teamBId = $matchInfo['away_team_id'];
            $matchInfo['score'] = str_replace(':', '-', $matchInfo['score']);
            imagettftext ( $target, $fontSize, 0, 630, 134, $fontColortime, $font, $matchInfo['score']);

            //bof of 合成图片
            $urlA = $imgCdnUrlPrefix. $teamAId . '.png';
            $urlB = $imgCdnUrlPrefix. $teamBId . '.png';
            $ta = @file_get_contents( $urlA);
            $tb = @file_get_contents( $urlB);
            $teamA = ($ta!=false && $ta != "")?(@imagecreatefrompng( $urlA)?imagecreatefrompng( $urlA):imagecreatefrompng($defaultTeamA)):imagecreatefrompng($defaultTeamA);
            $teamB = ($tb!=false && $tb != "")?(@imagecreatefrompng( $urlB)?imagecreatefrompng( $urlB):imagecreatefrompng($defaultTeamB)):imagecreatefrompng($defaultTeamB);
            $newTeamA = imagecreatetruecolor('100', '100');

            $color = imagecolorallocatealpha($newTeamA, 255, 255, 255,127);
            imagefill($newTeamA, 0, 0, $color);

            imageColorTransparent($newTeamA, $color);

            $newTeamB = imagecreatetruecolor('100', '100');
            $color = imagecolorallocatealpha($newTeamB, 255, 255, 255,127);
            imagefill($newTeamB, 0, 0, $color);
            imageColorTransparent($newTeamB, $color);

            imagecopyresampled($newTeamA, $teamA, 0, 0, 0, 0, '100', '100', imagesx($teamA), imagesy($teamA));
            imagecopyresampled($newTeamB, $teamB, 0, 0, 0, 0, '100', '100', imagesx($teamB), imagesy($teamB));
            // imagecopyresampled($newVs, $vsIma, 0, 0, 0, 0, '32', '28', imagesx($vsIma), imagesy($vsIma));

            imagecopymerge ( $target, $newTeamA, 402, 40, 0, 0, imagesx ( $newTeamA ), imagesy ( $newTeamA ), 100 );
            imagecopymerge ( $target, $newTeamB, 818, 40, 0, 0, imagesx ( $newTeamB ), imagesy ( $newTeamB ), 100 );
            //eof of 合成图片
            @mkdir ( '/public/Public/image' );
            $time = self::msectime();
            $filename = $type.$matchInfo['match_id'].'.png';
            imagepng ( $target, './public/Public/image/' . $filename);
            $img = $filename;

        }
        return $img;
    }

     */
}

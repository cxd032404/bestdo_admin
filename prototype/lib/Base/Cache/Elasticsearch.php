<?php
use Elasticsearch\ClientBuilder;

class Base_Cache_Elasticsearch implements Base_Cache_Interface
{
    var $client;
    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    function __construct()
    {
        $CacheConf = (@include dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/CommonConfig/cacheConfig.php");
        if(is_array($CacheConf['ES_SERVER']))
        {
            $this->client = ClientBuilder::create()->setHosts($CacheConf['ES_SERVER'])->build();
        }
    }
    public function get($id)
    {

    }
    public function set($id,$data,$expire = 900)
    {

    }
    public function remove($id)
    {

    }
    public function companyUserIndex($userList,$esConfig = [])
    {
        $success = 0;
        $esConfig = $esConfig['company_user_list'];
        foreach($userList as $user)
        {
            $userInfo = $esConfig + ['id'=>$user['id'],"body"=>$user];
            $userInfo['index'].="_".$user['company_id'];
            $index = $this->client->index($userInfo);
            if(isset($index['_shards']['successful']) && $index['_shards']['successful'] == 1)
            {
                $success++;
            }
        }
        return $success;
    }
}

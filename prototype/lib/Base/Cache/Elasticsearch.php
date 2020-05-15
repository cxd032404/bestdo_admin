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
    public function questionIndex($question,$esConfig = [])
    {
        $success = 0;
        $esConfig = $esConfig['question_list'];
        $questionInfo = $esConfig + ['id'=>$question['question_id'],"body"=>$question];
        $questionInfo['index'].="_".$question['activity_id'];
        $index = $this->client->index($questionInfo);
        if(isset($index['_shards']['successful']) && $index['_shards']['successful'] == 1)
        {
            $return  = true;
        }
        else
        {
            $return = false;
        }
        return $return;
    }
    public function checkIndex($index_name,$params = [])
    {
        if($index_name == "company_user_list")
        {
            $type = $index_name;
            $index = $index_name."_".$params['company_id'];
            //检查是否存在
            $exist = $this->client->indices()->exists(["index"=>$index]);
            if($exist)
            {
                //检查当前索引的关键字段
                $indexDetail = $this->client->indices()->get(["index"=>$index]);
                $name_properties = ($indexDetail[$index_name]['mappings'][$type]['properties']['name']['fields'])??[];
                {
                    //字段缺失 删除
                    //if(1)
                    if(!isset($name_properties['analyzer']))
                    {
                        $delete = $this->client->indices()->delete(["index"=>$index]);
                        //重建
                        $create = 1;
                    }
                    else
                    {
                        return true;
                    }
                    //需要重建
                }
            }
            else
            {
                $create = 1;
            }
            if(isset($create))
            {
                $params = [
                    'index' => $index,
                    'body' => self::getCommonIndexBody()
            ];
                $create = $this->client->indices()->create($params);
                $params = ["index"=>$index,"type"=>$type,
                    'body'=>[
                        'properties'=>[
                            "id"=>["type"=>"integer"],
                            "user_id"=>["type"=>"integer"],
                            "company_id"=>["type"=>"integer"],
                            "name"=>["type"=>"text",
                                "analyzer"=>"ik_smart_pinyin",
                                "search_analyzer"=>"ik_smart_pinyin"],
                            "worker_id"=>["type"=>"text",
                                "analyzer"=>"ik_max_word",
                                "search_analyzer"=>"ik_max_word"],
                            "mobile"=>["type"=>"text",
                                "analyzer"=>"ik_max_word",
                                "search_analyzer"=>"ik_max_word"],
                        ]
                    ]];
                $map = $this->client->indices()->putMapping($params);
                $indexDetail = $this->client->indices()->get(["index"=>$index]);
                return true;
            }
        }
        if($index_name == "question_list")
        {
            $type = $index_name;
            $index = $index_name."_".$params['activity_id'];
            //检查是否存在
            $exist = $this->client->indices()->exists(["index"=>$index]);
            if($exist)
            {
                //检查当前索引的关键字段
                $indexDetail = $this->client->indices()->get(["index"=>$index]);
                $name_properties = ($indexDetail[$index_name]['mappings'][$type]['properties']['name']['fields'])??[];
                {
                    //字段缺失 删除
                    //if(1)
                    if(!isset($name_properties['analyzer']))
                    {
                        $delete = $this->client->indices()->delete(["index"=>$index]);
                        //重建
                        $create = 1;
                    }
                    else
                    {
                        return true;
                    }
                    //需要重建
                }
            }
            else
            {
                $create = 1;
            }
            if(isset($create))
            {
                $params = [
                    'index' => $index,
                    'body' => self::getCommonIndexBody()
                ];
                $create = $this->client->indices()->create($params);
                $params = ["index"=>$index,"type"=>$type,
                    'body'=>[
                        'properties'=>[
                            "id"=>["type"=>"integer"],
                            "question_id"=>["type"=>"integer"],
                            "activity_id"=>["type"=>"integer"],
                            "question"=>["type"=>"text",
                                "analyzer"=>"ik_max_word_pinyin",
                                "search_analyzer"=>"ik_max_word_pinyin"],
                            "answer"=>["type"=>"text",
                                "analyzer"=>"ik_max_word_pinyin",
                                "search_analyzer"=>"ik_max_word_pinyin"],
                            "keywords"=>["type"=>"text",
                                "analyzer"=>"ik_max_word_pinyin",
                                "search_analyzer"=>"ik_max_word_pinyin"],
                        ]
                    ]];
                $map = $this->client->indices()->putMapping($params);
                $indexDetail = $this->client->indices()->get(["index"=>$index]);
                return true;
            }
        }
    }
    private function getCommonIndexBody()
    {
        return
            [
                'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
                'analysis'=>[
                    'analyzer'=>[
                        'ik_smart_pinyin'=>[
                            'type'=>'custom',
                            'tokenizer'=>'ik_smart',
                            'filter'=>['my_pinyin','word_delimiter']
                        ],
                        'ik_max_word_pinyin'=>[
                            'type'=>'custom',
                            'tokenizer'=>'ik_max_word',
                            'filter'=>['my_pinyin','word_delimiter']

                        ]
                    ],
                    'filter'=>[
                        'my_pinyin'=>[
                            'type'=>'pinyin',
                            'keep_separate_first_letter'=>true,
                            'keep_full_pinyin'=>true,
                            'keep_original'=>true,
                            'limit_first_letter_length'=>16,
                            'lowercase'=>true,
                            'remove_duplicated_term'=>true,
                        ]
                    ]
                ]
            ]
        ];
    }
}

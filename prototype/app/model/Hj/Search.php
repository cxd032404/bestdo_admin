<?php
/**
 * 搜索配置相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_Search extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_search_keyword';

    //根据关键字内容获取关键字信息
    public function getAllKeyword($params)
    {
        $fields = '*';
        $Keyword = trim($params['Keyword']);
        //需要查询的表
        $table_to_process = Base_Widget::getDbTable($this->table);
        if(strlen($Keyword))
        {
            $sql = "SELECT $fields FROM ".$table_to_process." where Keyword like '%".$Keyword."%' order by Keyword";
        }
        else
        {
            $sql = "SELECT $fields FROM ".$table_to_process." where 1 order by Keyword";
        }
        //分页参数
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $return = array("KeywordList"=>$this->db->getAll($sql.$limit),"KeywordCount"=>$this->getAllKeywordCount($params));
        return $return;
    }
    //根据关键字内容获取关键字数量
    public function getAllKeywordCount($params)
    {
        $fields = 'Count(1) as KeywordCount';
        $Keyword = trim($params['Keyword']);
        $table_to_process = Base_Widget::getDbTable($this->table_group);
        if(strlen($Keyword))
        {
            $sql = "SELECT $fields FROM ".$table_to_process." where Keyword like '%".$Keyword."%'";
        }
        else
        {
            $sql = "SELECT $fields FROM ".$table_to_process." where 1";
        }
        $return = $this->db->getOne($sql);
        return $return;
    }
	//更新单个关键字
	public function updateKeyword($KeywordId, array $bind)
	{
        $KeywordId = intval($KeywordId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`KeywordId` = ?', $KeywordId);
	}
	//添加单个关键字
	public function insertKeyword(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}
	//删除单个关键字
	public function deleteKeyword($KeywordId)
	{
        $KeywordId = intval($KeywordId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`KeywordId` = ?', $KeywordId);
	}
    //根据关键字内容获取关键字信息
    public function getKeywordByKeyword($Keyword, $fields = '*')
    {
        $Keyword = trim($Keyword);
        $table_to_process = Base_Widget::getDbTable($this->table_group);
        return $this->db->selectRow($table_to_process, $fields, '`Keyword` = ?', $Keyword);
    }
    //格式化处理关键字列表
    public function processKeywordText($text)
    {
        //以|作为分隔符切割关键字
        $KeywordList = explode("|",$text);
        //循环列表
        foreach($KeywordList as $key => $value)
        {
            //两边去头截尾
            $KeywordList[$key] = trim($value);
            if(!strlen($KeywordList[$key]))
            {
                unset($KeywordList[$key]);
            }
        }
        return $KeywordList;
    }
    //更新关键字相关的搜索信息
    public function updateSearchKeyWordInfo($KeywordList,$oldKeywordList,$params)
    {
        //循环新数据
        foreach($KeywordList as $K => $V)
        {
            $found = 0;
            $toSave = 0;
            //循环旧关键字
            foreach($oldKeywordList as $K2 => $V2)
            {
                //如果匹配上
                if($V == $V2)
                {
                    $found = 1;
                }
            }
            //如果没找到
            if($found == 0)
            {
                //根据关键字查找信息
                $KeyWordInfo = $this->getKeywordByKeyword($V);
                //如果找到关键字
                if($KeyWordInfo['KeywordId'])
                {
                    //结果数据解包
                    $KeyWordInfo['SearchParams'] = json_decode($KeyWordInfo['SearchParams'],true);
                    //如果对应分站信息
                    if($params['RaceStageId']>0)
                    {
                        //如果已有记录
                        if(isset($KeyWordInfo['SearchParams']['RaceStageList'][$params['RaceStageId']]))
                        {
                            continue;
                        }
                        else
                        {
                            //保存数据
                            $KeyWordInfo['SearchParams']['RaceStageList'][$params['RaceStageId']] = 1;
                            $toSave = 1;
                        }
                    }
                    //如果需要更新
                    if($toSave)
                    {
                        $KeyWordInfo['SearchParams'] = json_encode($KeyWordInfo['SearchParams']);
                        $this->updateKeyword($KeyWordInfo['KeywordId'],$KeyWordInfo);
                    }
                }
                else
                {
                    //如果对应分站信息
                    if($params['RaceStageId']>0)
                    {
                        $SearchParams = array("RaceStageList"=>array($params['RaceStageId']=>1));
                        $KeyWordInfo = array("Keyword"=>$V,"SearchParams" =>json_encode($SearchParams));
                        $this->insertKeyword($KeyWordInfo);
                    }
                }
            }
            $url = $this->config->apiUrl.Base_Common::getUrl('','xrace.search','rebuild.search.keyword',array('Force'=>1));
            $return = Base_Common::do_post($url);
        }
        //循环新数据
        foreach($oldKeywordList as $K => $V)
        {
            $found = 0;
            $toSave = 0;
            //循环旧关键字
            foreach ($KeywordList as $K2 => $V2)
            {
                //如果匹配上
                if ($V == $V2)
                {
                    $found = 1;
                }
            }
            //如果没找到
            if($found == 0)
            {
                //根据关键字查找信息
                $KeyWordInfo = $this->getKeywordByKeyword($V);
                //如果找到关键字
                if($KeyWordInfo['KeywordId'])
                {
                    //结果数据解包
                    $KeyWordInfo['SearchParams'] = json_decode($KeyWordInfo['SearchParams'],true);
                    //如果对应分站信息
                    if($params['RaceStageId']>0)
                    {
                        //如果已有记录
                        if(!isset($KeyWordInfo['SearchParams']['RaceStageList'][$params['RaceStageId']]))
                        {
                            continue;
                        }
                        else
                        {
                            //保存数据
                            unset($KeyWordInfo['SearchParams']['RaceStageList'][$params['RaceStageId']]);
                            $toSave = 1;
                        }
                    }
                    //如果需要更新
                    if($toSave)
                    {
                        $KeyWordInfo['SearchParams'] = json_encode($KeyWordInfo['SearchParams']);
                        $this->updateKeyword($KeyWordInfo['KeywordId'],$KeyWordInfo);
                    }
                }
                else
                {
                    continue;
                }
            }
        }
    }
    //重建关键字的搜索文件索引
    function rebuildSearchWord($Force)
    {
        //如果强制更新
        if($Force)
        {
            //获取所有关键字
            $KeywordList = $this->getAllKeyword(array("Keyword"=>""));
            $filePath = __APP_ROOT_DIR__."Search"."/";
            $fileName = "KeywordList".".php";
            //生成配置文件
            Base_Common::rebuildConfig($filePath,$fileName,$KeywordList,"KeywordList");
        }
        else
        {
            $filePath = __APP_ROOT_DIR__."Search"."/";
            $fileName = "KeywordList".".php";
            //载入预生成的配置文件
            $KeywordList = Base_Common::loadConfig($filePath,$fileName);
            //如果数组载入成功，并且记录无误
            if(isset($KeywordList['KeywordCount']) && $KeywordList['KeywordCount']== count($KeywordList['KeywordList']))
            {
                //如果是10分钟以前创建的
                if((time()-$KeywordList['LastUpdateTime'])<=600)
                {
                    return true;
                }
                else
                {
                    //获取所有关键字
                    $KeywordList = $this->getAllKeyword(array("Keyword"=>""));
                    $filePath = __APP_ROOT_DIR__."Search"."/";
                    $fileName = "KeywordList".".php";
                    //生成配置文件
                    Base_Common::rebuildConfig($filePath,$fileName,array_merge($KeywordList,array("LastUpdateTime"=>date("Y-m-d,H:i:s"))),"KeywordList");
                }
            }
            else
            {
                //获取所有关键字
                $KeywordList = $this->getAllKeyword(array("Keyword"=>""));
                $filePath = __APP_ROOT_DIR__."Search"."/";
                $fileName = "KeywordList".".php";
                //生成配置文件
                Base_Common::rebuildConfig($filePath,$fileName,array_merge($KeywordList,array("LastUpdateTime"=>date("Y-m-d,H:i:s"))),"KeywordList");
            }
        }
    }
    //获取所有关键字列表供搜索用
    function getAllSearchKeyword()
    {
        $filePath = __APP_ROOT_DIR__."Search"."/";
        $fileName = "KeywordList".".php";
        //载入预生成的配置文件
        $KeywordList = Base_Common::loadConfig($filePath,$fileName);
        //如果数组载入成功，并且记录无误
        if(isset($KeywordList['KeywordCount']) && $KeywordList['KeywordCount']== count($KeywordList['KeywordList']))
        {
            return $KeywordList;
        }
        else
        {
            //获取所有关键字
            $KeywordList = $this->getAllKeyword(array("Keyword"=>""));
            $filePath = __APP_ROOT_DIR__."Search"."/";
            $fileName = "KeywordList".".php";
            //生成配置文件
            Base_Common::rebuildConfig($filePath,$fileName,array_merge($KeywordList,array("LastUpdateTime"=>date("Y-m-d,H:i:s"))),"KeywordList");
            $KeywordList = Base_Common::loadConfig($filePath,$fileName);
            return $KeywordList;
        }
    }
}

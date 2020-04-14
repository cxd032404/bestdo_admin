<?php
/**
 *
 * 
 */
class XraceConfigController extends AbstractController
{
    /**
     *对象声明
     */
    protected $oRace;
    protected $oSports;
    protected $oProduct;
    protected $oUser;

    /**
     * 初始化
     * (non-PHPdoc)
     * @see AbstractController#init()
     */
    public function init()
    {
        parent::init();
        $this->oRace = new Xrace_Race();
        $this->oSports = new Xrace_Sports();
        $this->oProduct = new Xrace_Product();
        $this->oUser = new Xrace_UserInfo();
    }

    /**
     *获取所有赛事的列表(缓存)
     */
    public function getRaceCatalogListAction()
    {
        //是否调用缓存
        $Cache = isset($this->request->Cache) ? abs(intval($this->request->Cache)) : 1;
        //是否显示说明注释 默认为1
        $GetComment = isset($this->request->GetComment) ? abs(intval($this->request->GetComment)) : 1;
        //获得赛事列表
        $RaceCatalogList = $this->oRace->getRaceCatalogList(1,"*",$Cache);
        //如果没有返回值,默认为空数组
        if (!is_array($RaceCatalogList))
        {
            $RaceCatalogList = array();
        }
        //循环赛事列表数组
        foreach ($RaceCatalogList as $RaceCatalogId => $RaceCatalogInfo)
        {
            //如果有输出赛事图标的绝对路径
            if (isset($RaceCatalogInfo['comment']['RaceCatalogIcon']))
            {
                //删除
                unset($RaceCatalogList[$RaceCatalogId]['comment']['RaceCatalogIcon']);
            }
            //如果有输出赛事图标的相对路径
            if (isset($RaceCatalogInfo['comment']['RaceCatalogIcon_root']))
            {
                //拼接上ADMIN站点的域名
                $RaceCatalogList[$RaceCatalogId]['comment']['RaceCatalogIcon'] = $this->config->adminUrl . $RaceCatalogList[$RaceCatalogId]['comment']['RaceCatalogIcon_root'];
                //删除原有数据
                unset($RaceCatalogList[$RaceCatalogId]['comment']['RaceCatalogIcon_root']);
            }
            //如果参数不显示说明文字
            if ($GetComment != 1)
            {
                //则删除该字段
                unset($RaceCatalogList[$RaceCatalogId]['RaceCatalogComment']);
            }
        }
        //结果数组 如果列表中有数据则返回成功，否则返回失败
        $result = array("return" => count($RaceCatalogList) ? 1 : 0, "RaceCatalogList" => $RaceCatalogList);
        echo json_encode($result);
    }

    /*
     * 获取单个赛事信息
     */
    public function getRaceCatalogInfoAction()
    {
        //是否调用缓存
        $Cache = isset($this->request->Cache) ? abs(intval($this->request->Cache)) : 1;
        //是否显示说明注释 默认为1
        $GetComment = isset($this->request->GetComment) ? abs(intval($this->request->GetComment)) : 1;
        //格式化赛事ID,默认为0
        $RaceCatalogId = isset($this->request->RaceCatalogId) ? abs(intval($this->request->RaceCatalogId)) : 0;
        //赛事ID必须大于0
        if ($RaceCatalogId) {
            //获取赛事信息
            $RaceCatalogInfo = $this->oRace->getRaceCatalog($RaceCatalogId,"*",$Cache);
            //检测主键存在,否则值为空
            if (isset($RaceCatalogInfo['RaceCatalogId']))
            {
                //解包数组
                $RaceCatalogInfo['comment'] = isset($RaceCatalogInfo['comment']) ? json_decode($RaceCatalogInfo['comment'], true) : array();
                //如果有输出赛事图标的绝对路径
                if (isset($RaceCatalogInfo['comment']['RaceCatalogIcon']))
                {
                    //删除
                    unset($RaceCatalogInfo['comment']['RaceCatalogIcon']);
                }
                //如果有输出赛事图标的相对路径
                if (isset($RaceCatalogInfo['comment']['RaceCatalogIcon_root']))
                {
                    //拼接上ADMIN站点的域名
                    $RaceCatalogInfo['comment']['RaceCatalogIcon'] = $this->config->adminUrl . $RaceCatalogInfo['comment']['RaceCatalogIcon_root'];
                    //删除原有数据
                    unset($RaceCatalogInfo['comment']['RaceCatalogIcon_root']);
                }
                //根据赛事获取组别列表
                $RaceGroupList = isset($RaceCatalogInfo['RaceCatalogId']) ? $this->oRace->getRaceGroupList($RaceCatalogInfo['RaceCatalogId'], "RaceGroupId,RaceGroupName") : array();
                //根据赛事获取分站列表
                $RaceStageList = isset($RaceCatalogInfo['RaceCatalogId']) ? $this->oRace->getRaceStageList($RaceCatalogInfo['RaceCatalogId'], "RaceStageId,RaceStageName",1) : array();
                //如果参数不显示说明文字
                if ($GetComment != 1)
                {
                    //则删除该字段
                    unset($RaceCatalogInfo['RaceCatalogComment']);
                }
                //结果数组
                $result = array("return" => 1, "RaceCatalogInfo" => $RaceCatalogInfo, 'RaceGroupList' => $RaceGroupList, 'RaceStageList' => $RaceStageList);
            }
            else
            {
                //全部置为空
                $result = array("return" => 0, "RaceCatalog" => array(), 'RaceGroupList' => array(), 'RaceStageList' => array(), "comment" => "请指定一个有效的赛事ID");
            }

        }
        else
        {
            //全部置为空
            $result = array("return" => 0, "RaceCatalog" => array(), 'RaceGroupList' => array(), 'RaceStageList' => array(), "comment" => "请指定一个有效的赛事ID");
        }
        echo json_encode($result);
    }

    /*
     * 根据赛事获取所有分站列表
     */
    public function getRaceStageListAction()
    {
        //比赛-分组的层级规则
        $RaceStructureList  = $this->oRace->getRaceStructure();
        //格式化赛事ID,默认为0
        $RaceCatalogId = isset($this->request->RaceCatalogId) ? abs(intval($this->request->RaceCatalogId)) : 0;
        $RaceStageStatus = isset($this->request->RaceStageStatus) ? abs(intval($this->request->RaceStageStatus)) : 0;
        //赛事ID必须大于0
        if ($RaceCatalogId)
        {
            //获得分站列表
            $RaceStageList = $this->oRace->getRaceStageList($RaceCatalogId,"*",1);
            //如果没有返回值,默认为空数组
            if (!is_array($RaceStageList))
            {
                //全部置为空
                $result = array("return" => 0, "RaceStageList" => array(), "comment" => "请指定一个有效的赛事ID");
            }
            else
            {
                //循环分站数组
                foreach ($RaceStageList as $RaceStageId => $RaceStageInfo)
                {
                    //如果没有配置比赛结构 或者 比赛结构配置不在配置列表中
                    if(!isset($RaceStageInfo['comment']['RaceStructure']) || !isset($RaceStructureList[$RaceStageInfo['comment']['RaceStructure']]))
                    {
                        //默认为分组优先
                        $RaceStageList[$RaceStageId]['comment']['RaceStructure'] = "group";
                    }
                    //说明文字解码
                    $RaceStageList[$RaceStageId]['RaceStageComment'] = urldecode($RaceStageInfo['RaceStageComment']);
                    //解包图片数组
                    $RaceStageList[$RaceStageId]['RaceStageIcon'] = json_decode($RaceStageInfo['RaceStageIcon'], true);
                    //获取当前比赛的时间状态信息
                    $RaceStageList[$RaceStageId]['RaceStageStatus'] = $this->oRace->getRaceStageTimeStatus($RaceStageId, 0);
                    if (($RaceStageStatus > 0 && $RaceStageList[$RaceStageId]['RaceStageStatus']['StageStatus'] == $RaceStageStatus) || ($RaceStageStatus == 0)) {
                        //如果有配置分站图片
                        if (isset($RaceStageList[$RaceStageId]['RaceStageIcon']))
                        {
                            //循环图片列表
                            foreach ($RaceStageList[$RaceStageId]['RaceStageIcon'] as $IconId => $IconInfo)
                            {
                                //拼接上ADMIN站点的域名
                                $RaceStageList[$RaceStageId]['comment']['RaceStageIconList'][$IconId]['RaceStageIcon'] = $this->config->adminUrl . $IconInfo['RaceStageIcon_root'];
                            }
                            //删除原有数据
                            unset($RaceStageList[$RaceStageId]['RaceStageIcon']);
                        }
                        //如果有配置分组信息，暂不输出
                        if (isset($RaceStageList[$RaceStageId]['comment']['SelectedRaceGroup']))
                        {
                            //循环图片列表
                            foreach ($RaceStageList[$RaceStageId]['comment']['SelectedRaceGroup'] as $RaceGroupId)
                            {
                                //获取赛事分组基本信息
                                $RaceGroupInfo = $this->oRace->getRaceGroup($RaceGroupId, "RaceGroupId,RaceGroupName");
                                //如果有获取到分组信息
                                if ($RaceGroupInfo['RaceGroupId'])
                                {
                                    //提取分组名称
                                    $RaceStageList[$RaceStageId]['comment']['SelectedRaceGroup'][$RaceGroupId] = $RaceGroupInfo;
                                }
                                else
                                {
                                    //删除
                                    unset($RaceStageList[$RaceStageId]['comment']['SelectedRaceGroup'][$RaceGroupId]);
                                }
                            }
                        }
                        //不输出产品相关信息
                        unset($RaceStageList[$RaceStageId]['comment']['SelectedProductList']);
                    }
                    else
                    {
                        unset($RaceStageList[$RaceStageId]);
                    }
                }
                //结果数组
                $result = array("return" => 1, "RaceStageList" => $RaceStageList);
            }
        }
        else
        {
            //全部置为空
            $result = array("return" => 0, "RaceStageList" => array(), "comment" => "请指定一个有效的赛事ID");
        }
        echo json_encode($result);
    }
    /*
     * 获取单个赛事分站信息
     */
    public function getRaceStageInfoAction()
    {
        //比赛-分组的层级规则
        $RaceStructureList  = $this->oRace->getRaceStructure();
        //格式化赛事分站ID,默认为0
        $RaceStageId = isset($this->request->RaceStageId) ? abs(intval($this->request->RaceStageId)) : 0;
        //格式化用户ID,默认为空
        $UserId = isset($this->request->UserId) ? trim($this->request->UserId) : "";
        //筛选单人比赛
        $SingleUser = isset($this->request->SingleUser) ? abs(intval($this->request->SingleUser)) : 1;
        //筛选团队比赛
        $TeamUser = isset($this->request->TeamUser) ? abs(intval($this->request->TeamUser)) : 1;
        //筛选通票/单场
        $RacePriceMode = isset($this->request->RacePriceMode) ? trim($this->request->RacePriceMode) : "";
        if($RaceStageId)
        {
            //获得分站信息
            $RaceStageInfo = $this->oRace->getRaceStage($RaceStageId);
            //检测主键存在,否则值为空
            $RaceStageInfo = isset($RaceStageInfo['RaceStageId']) ? $RaceStageInfo : array();
            if (isset($RaceStageInfo['RaceStageId']))
            {
                //说明文字解码
                $RaceStageInfo['RaceStageComment'] = urldecode($RaceStageInfo['RaceStageComment']);
                //解包数组
                $RaceStageInfo['comment'] = isset($RaceStageInfo['comment']) ? json_decode($RaceStageInfo['comment'], true) : array();
                //处理价格列表
                $RaceStageInfo["comment"]['PriceList'] = $this->oRace->getPriceList($RaceStageInfo["comment"]['PriceList']);
                //如果没有配置比赛结构 或者 比赛结构配置不在配置列表中
                if(!isset($RaceStageInfo['comment']['RaceStructure']) || !isset($RaceStructureList[$RaceStageInfo['comment']['RaceStructure']]))
                {
                    //默认为分组优先
                    $RaceStageInfo['comment']['RaceStructure'] = "group";
                }
                //解包图片数组
                $RaceStageInfo['RaceStageIcon'] = isset($RaceStageInfo['RaceStageIcon']) ? json_decode($RaceStageInfo['RaceStageIcon'], true) : array();
                //如果有配置分站图片
                if (isset($RaceStageInfo['RaceStageIcon']))
                {
                    //循环图片列表
                    foreach ($RaceStageInfo['RaceStageIcon'] as $IconId => $IconInfo)
                    {
                        //拼接上ADMIN站点的域名
                        $RaceStageInfo['comment']['RaceStageIconList'][$IconId]['RaceStageIcon'] = $this->config->adminUrl . $IconInfo['RaceStageIcon_root'];
                    }
                    //删除原有数据
                    unset($RaceStageInfo['RaceStageIcon']);
                }
                //如果有配置分组信息
                if (isset($RaceStageInfo['comment']['SelectedRaceGroup']))
                {
                    //循环图片列表
                    foreach ($RaceStageInfo['comment']['SelectedRaceGroup'] as $RaceGroupId)
                    {
                        //获取赛事分组基本信息
                        $RaceGroupInfo = $this->oRace->getRaceGroup($RaceGroupId, "RaceGroupId,RaceGroupName,comment");
                        //如果有获取到分组信息
                        if (isset($RaceGroupInfo['RaceGroupId']))
                        {
                            //默认当前组别可选
                            $RaceGroupInfo['checkable'] = true;
                            //数据解包
                            $RaceGroupInfo['comment'] = json_decode($RaceGroupInfo['comment'], true);
                            //执照条件的审核
                            $RaceGroupInfo['LicenseList'] = $this->oRace->raceLicenseCheck($RaceGroupInfo['comment']['LicenseList'], $UserId, $RaceStageInfo, $RaceGroupInfo);
                            foreach ($RaceGroupInfo['LicenseList'] as $k => $v)
                            {
                                //如果发现条件为不可选
                                if (isset($v['checked']) && $v['checked'] == false)
                                {
                                    //将当前组别置为不可选
                                    $RaceGroupInfo['checkable'] = false;
                                    break;
                                }
                            }
                            //格式化执照的条件，供显示
                            $LicenseListText = $this->oRace->ParthRaceLicenseListToHtml($RaceGroupInfo['LicenseList'], 0, 0, 1);
                            //循环执照审核条件的文字
                            foreach ($LicenseListText as $key => $LicenseInfo)
                            {
                                //分别置入权限审核列表
                                $RaceGroupInfo['LicenseList'][$key]['LicenseTextArr'] = $LicenseInfo;
                            }
                            //提取分组名称
                            unset($RaceGroupInfo['comment']);
                            $RaceStageInfo['comment']['SelectedRaceGroup'][$RaceGroupId] = $RaceGroupInfo;
                            //获取比赛列表
                            $RaceList  = $this->oRace->getRaceList(array("RaceStageId"=>$RaceStageId,"RaceGroupId"=>$RaceGroupId),$fields = 'RaceId,TeamUser,SingleUser,PriceList');
                            //如果有比赛
                            if(count($RaceList))
                            {
                                //循环比赛列表
                                foreach($RaceList as $RaceId => $RaceInfo)
                                {
                                    //如果选定了担任比赛
                                    if($SingleUser == 1 && $RaceInfo['SingleUser'] == 1)
                                    {
                                        break;
                                    }
                                    //如果选定了团队比赛
                                    elseif($TeamUser == 1 && $RaceInfo['TeamUser'] == 1)
                                    {
                                        break;
                                    }
                                    //如果不限定价
                                    elseif($RacePriceMode == "")
                                    {
                                        break;
                                    }
                                    //如果选定了只要比赛独立定价 且 比赛独立定价
                                    elseif($RacePriceMode == "race" && $RaceInfo['PriceList'] != "")
                                    {
                                        break;
                                    }
                                    //如果选定了只要分站通票定价 且 比赛未独立定价
                                    elseif($RacePriceMode == "stage" && $RaceInfo['PriceList'] == "")
                                    {
                                        break;
                                    }
                                    else
                                    {
                                        //删除当前比赛
                                        unset($RaceList[$RaceId]);
                                        //如果比赛列表为空
                                        if(!count($RaceList))
                                        {
                                            //删除当前分组
                                            unset($RaceStageInfo['comment']['SelectedRaceGroup'][$RaceGroupId]);
                                        }
                                    }
                                }
                            }
                            else
                            {
                                //unset($RaceStageInfo['comment']['SelectedRaceGroup'][$RaceGroupId]);
                            }
                        }
                        else
                        {
                            //删除
                            unset($RaceStageInfo['comment']['SelectedRaceGroup'][$RaceGroupId]);
                        }
                    }
                }
                //如果有配置分组信息
                if (isset($RaceStageInfo['comment']['SelectedProductList']))
                {
                    //初始化一个空的产品列表
                    $ProductList = array();
                    //循环产品列表
                    foreach ($RaceStageInfo['comment']['SelectedProductList'] as $ProductId => $SkuList)
                    {
                        //如果产品列表中没有此产品
                        if (!isset($ProductList[$ProductId]))
                        {
                            //获取产品信息
                            $ProductInfo = $this->oProduct->getProduct($ProductId, "ProductId,ProductName");
                            //如果产品信息获取到
                            if (isset($ProductInfo['ProductId']))
                            {
                                //放入产品列表中
                                $ProductList[$ProductId] = $ProductInfo;
                            }
                            else
                            {
                                continue;
                            }
                        }
                        //从产品列表中取出产品
                        $ProductInfo = $ProductList[$ProductId];
                        $ProductSkuList = $this->oProduct->getAllProductSkuList($ProductId);
                        foreach($SkuList as $ProductSkuId => $ProductSkuInfo)
                        {
                            if(isset($ProductSkuList[$ProductId][$ProductSkuId]) && $ProductSkuInfo['Stock']>0)
                            {
                                $SkuList[$ProductSkuId]['ProductSkuName'] =  $ProductSkuList[$ProductId][$ProductSkuId]['ProductSkuName'];
                            }
                            else
                            {
                                unset($SkuList[$ProductSkuId]);
                            }
                        }
                        if(count($SkuList)>0)
                        {
                            $Product = array("SkuList"=>$SkuList,'ProductName'=>$ProductInfo['ProductName']);
                            //存入产品名称
                            $RaceStageInfo['comment']['SelectedProductList'][$ProductId] = $Product;
                        }
                        else
                        {
                            unset($RaceStageInfo['comment']['SelectedProductList'][$ProductId]);
                        }
                    }
                }
                //获取当前比赛的时间状态信息
                $RaceStageInfo['RaceStageStatus'] = $this->oRace->getRaceStageTimeStatus($RaceStageId, 0);
                //结果数组
                $result = array("return" => 1, "RaceStageInfo" => $RaceStageInfo);
            } else {
                //全部置为空
                $result = array("return" => 0, "RaceStageInfo" => array(), "comment" => "请指定一个有效的分站ID");
            }
        } else {
            //全部置为空
            $result = array("return" => 0, "RaceStageInfo" => array(), "comment" => "请指定一个有效的分站ID");
        }
        echo json_encode($result);
    }
    /*
     * 获取单个赛事组别的信息
     */
    public function getRaceGroupInfoAction()
    {
        //格式化赛事组别ID,默认为0
        $RaceGroupId = isset($this->request->RaceGroupId) ? intval($this->request->RaceGroupId) : 0;
        //赛事组别必须大于0
        if ($RaceGroupId) {
            //获取赛事组别信息
            $RaceGroupInfo = $this->oRace->getRaceGroup($RaceGroupId);
            //检测主键存在,否则值为空
            $RaceGroupInfo = isset($RaceGroupInfo['RaceGroupId']) ? $RaceGroupInfo : array();
            //解包数组
            $RaceGroupInfo['comment'] = isset($RaceGroupInfo['comment']) ? json_decode($RaceGroupInfo['comment'], true) : array();
            //如果有配置分组的审核规则
            if (isset($RaceGroupInfo['comment']['LicenseList']))
            {
                //暂时先删除,其后版本再添加
                unset($RaceGroupInfo['comment']['LicenseList']);
            }
            //结果数组
            $result = array("return" => 1, "RaceGroupInfo" => $RaceGroupInfo);
        }
        else
        {
            //全部置为空
            $result = array("return" => 0, "RaceGroupInfo" => array(), "comment" => "请指定一个有效的赛事分组ID");
        }
        echo json_encode($result);
    }

    /*
     * 获取赛事分站和赛事组别获取比赛列表
     */
    public function getRaceListAction()
    {
        //格式化赛事分站和赛事组别ID,默认为0
        $RaceStageId = isset($this->request->RaceStageId) ? abs(intval($this->request->RaceStageId)) : 0;
        $RaceGroupId = isset($this->request->RaceGroupId) ? abs(intval($this->request->RaceGroupId)) : 0;
        //筛选通票/单场
        $RacePriceMode = isset($this->request->RacePriceMode) ? trim($this->request->RacePriceMode) : "";
        //获得分站信息
        $RaceStageInfo = $this->oRace->getRaceStage($RaceStageId);
        //数据解包
        $RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
        //比赛-分组的层级规则
        $RaceStructureList  = $this->oRace->getRaceStructure();
        //如果没有配置比赛结构 或者 比赛结构配置不在配置列表中
        if(!isset($RaceStageInfo['comment']['RaceStructure']) || !isset($RaceStructureList[$RaceStageInfo['comment']['RaceStructure']]))
        {
            //默认为分组优先
            $RaceStageInfo['comment']['RaceStructure'] = "group";
        }
        //分组优先
        if($RaceStageInfo['comment']['RaceStructure'] == "group")
        {
            //赛事分站和赛事组别ID必须大于0
            if ($RaceStageId && $RaceGroupId)
            {
                //获得比赛列表
                $RaceList = $this->oRace->getRaceList(array("RaceStageId"=>$RaceStageId, "RaceGroupId"=>$RaceGroupId), "RaceId,RaceTypeId,RouteInfo,RaceName,PriceList,SingleUser,TeamUser,StartTime,EndTime,ApplyStartTime,ApplyEndTime,comment,RaceComment,MustSelect");
                if (!is_array($RaceList))
                {
                    $RaceList = array();
                }
                //获取运动类型类表
                $SportsTypeList = $this->oSports->getAllSportsTypeList("SportsTypeId,SportsTypeName");
                //解包数组
                foreach ($RaceList as $RaceId => $RaceInfo)
                {
                        //如果不限定价
                    if($RacePriceMode == "")
                    {

                    }
                    //如果选定了只要比赛独立定价 且 比赛独立定价
                    elseif($RacePriceMode == "stage" && $RaceInfo['PriceList'] != "")
                    {
                        unset($RaceList[$RaceId]);
                        break;
                    }
                    //如果选定了只要分站通票定价 且 比赛未独立定价
                    elseif($RacePriceMode == "race" && $RaceInfo['PriceList'] == "")
                    {
                        unset($RaceList[$RaceId]);
                        break;
                    }
                    //说明文字解码
                    $RaceList[$RaceId]['RaceComment'] = urldecode($RaceInfo['RaceComment']);
                    //解包地图数据数组
                    $RaceList[$RaceId]['RouteInfo'] = json_decode($RaceInfo['RouteInfo'], true);
                    //处理价格列表
                    $RaceList[$RaceId]['PriceList'] = $this->oRace->getPriceList($RaceInfo['PriceList']);
                    //获取当前比赛的时间状态信息
                    $RaceList[$RaceId]['RaceStatus'] = $this->oRace->getRaceTimeStatus($RaceInfo);
                    //初始化比赛里程
                    $RaceList[$RaceId]['Distance'] = 0;
                    //获取比赛分类信息
                    $RaceTypeInfo = $RaceInfo['RaceTypeId'] ? $this->oRace->getRaceType($RaceInfo['RaceTypeId'], '*') : array();
                    //如果获取到比赛类型信息
                    if (isset($RaceTypeInfo['RaceTypeId']))
                    {
                        $RaceTypeInfo['comment'] = json_decode($RaceTypeInfo['comment'], true);
                        //如果有输比赛类型图标的相对路径
                        if (isset($RaceTypeInfo['comment']['RaceTypeIcon_root']))
                        {
                            //拼接上ADMIN站点的域名
                            $RaceTypeInfo['RaceTypeIcon'] = $this->config->adminUrl . ($RaceTypeInfo['comment']['RaceTypeIcon_root']);
                        }
                        //删除原有数据
                        unset($RaceTypeInfo['comment']);
                    }
                    //存入结果数组
                    $RaceList[$RaceId]['RaceTypeInfo'] = $RaceTypeInfo;
                    //如果有配置运动分段
                    if (isset($RaceInfo['comment']['DetailList']))
                    {
                        //循环运动分段
                        foreach ($RaceInfo['comment']['DetailList'] as $detailId => $detailInfo)
                        {
                            //如果有配置过该运动分段
                            if (isset($SportsTypeList[$detailInfo['SportsTypeId']]))
                            {
                                //获取运动类型名称
                                $RaceList[$RaceId]['comment']['DetailList'][$detailId]['SportsTypeName'] = $SportsTypeList[$detailInfo['SportsTypeId']]['SportsTypeName'];
                                //初始化运动分段的长度
                                $RaceList[$RaceId]['comment']['DetailList'][$detailId]['Distance'] = 0;
                                //获取计时点信息
                                $TimingInfo = isset($detailInfo['TimingId']) ? $this->oRace->getTimingDetail($detailInfo['TimingId']) : array();
                                //如果获取到计时点信息
                                if (isset($TimingInfo['TimingId']))
                                {
                                    //数据解包
                                    $TimingInfo['comment'] = isset($TimingInfo['comment']) ? json_decode($TimingInfo['comment'], true) : array();
                                    //循环计时点信息
                                    foreach ($TimingInfo['comment'] as $tid => $tInfo)
                                    {
                                        //累加里程到运动分段
                                        $RaceList[$RaceId]['comment']['DetailList'][$detailId]['Distance'] += $tInfo['ToPrevious'] * $tInfo['Round'];
                                        //累加里程到比赛
                                        $RaceList[$RaceId]['Distance'] += $tInfo['ToPrevious'] * $tInfo['Round'];
                                    }
                                }
                            }
                            else
                            {
                                unset($RaceList[$RaceId]['comment']['DetailList'][$detailId]);
                            }
                        }
                    }
                    else
                    {
                        //初始化为空数组
                        $RaceList[$RaceId]['comment']['DetailList'] = array();
                    }
                }
                //结果数组 如果列表有数据则为成功，否则为失败
                $result = array("return" => count($RaceList) ? 1 : 0, "RaceList" => $RaceList);
            }
            else
            {
                $result = array("return" => 0, "RaceList" => array());
            }
        }
        else
        {
            //获取当前赛事下的分组列表
            $RaceGroupList = $this->oRace->getRaceGroupList($RaceStageInfo['RaceCatalogId'],"RaceGroupName,RaceGroupId");
            //获得比赛列表
            $RaceList = $this->oRace->getRaceList(array("RaceStageId"=>$RaceStageId), "RaceId,RaceTypeId,RouteInfo,RaceName,PriceList,SingleUser,TeamUser,StartTime,EndTime,ApplyStartTime,ApplyEndTime,comment,RaceComment,MustSelect");
            if (!is_array($RaceList))
            {
                $RaceList = array();
            }
            //获取运动类型类表
            $SportsTypeList = $this->oSports->getAllSportsTypeList("SportsTypeId,SportsTypeName");
            //解包数组
            foreach ($RaceList as $RaceId => $RaceInfo)
            {
                //说明文字解码
                $RaceList[$RaceId]['RaceComment'] = urldecode($RaceInfo['RaceComment']);
                //解包地图数据数组
                $RaceList[$RaceId]['RouteInfo'] = json_decode($RaceInfo['RouteInfo'], true);
                //处理价格列表
                $RaceList[$RaceId]['PriceList'] = $this->oRace->getPriceList($RaceInfo['PriceList']);
                //获取当前比赛的时间状态信息
                $RaceList[$RaceId]['RaceStatus'] = $this->oRace->getRaceTimeStatus($RaceInfo);
                //初始化比赛里程
                $RaceList[$RaceId]['Distance'] = 0;
                //获取比赛分类信息
                $RaceTypeInfo = $RaceInfo['RaceTypeId'] ? $this->oRace->getRaceType($RaceInfo['RaceTypeId'], '*') : array();
                //如果获取到比赛类型信息
                if (isset($RaceTypeInfo['RaceTypeId']))
                {
                    $RaceTypeInfo['comment'] = json_decode($RaceTypeInfo['comment'], true);
                    //如果有输比赛类型图标的相对路径
                    if (isset($RaceTypeInfo['comment']['RaceTypeIcon_root']))
                    {
                        //拼接上ADMIN站点的域名
                        $RaceTypeInfo['RaceTypeIcon'] = $this->config->adminUrl . ($RaceTypeInfo['comment']['RaceTypeIcon_root']);
                    }
                    //删除原有数据
                    unset($RaceTypeInfo['comment']);
                }
                //存入结果数组
                $RaceList[$RaceId]['RaceTypeInfo'] = $RaceTypeInfo;
                //如果有配置运动分段
                if (isset($RaceInfo['comment']['DetailList']))
                {
                    //循环运动分段
                    foreach ($RaceInfo['comment']['DetailList'] as $detailId => $detailInfo)
                    {
                        //如果有配置过该运动分段
                        if (isset($SportsTypeList[$detailInfo['SportsTypeId']])) {
                            //获取运动类型名称
                            $RaceList[$RaceId]['comment']['DetailList'][$detailId]['SportsTypeName'] = $SportsTypeList[$detailInfo['SportsTypeId']]['SportsTypeName'];
                            //初始化运动分段的长度
                            $RaceList[$RaceId]['comment']['DetailList'][$detailId]['Distance'] = 0;
                            //获取计时点信息
                            $TimingInfo = isset($detailInfo['TimingId']) ? $this->oRace->getTimingDetail($detailInfo['TimingId']) : array();
                            //如果获取到计时点信息
                            if (isset($TimingInfo['TimingId']))
                            {
                                //数据解包
                                $TimingInfo['comment'] = isset($TimingInfo['comment']) ? json_decode($TimingInfo['comment'], true) : array();
                                //循环计时点信息
                                foreach ($TimingInfo['comment'] as $tid => $tInfo)
                                {
                                    //累加里程到运动分段
                                    $RaceList[$RaceId]['comment']['DetailList'][$detailId]['Distance'] += $tInfo['ToPrevious'] * $tInfo['Round'];
                                    //累加里程到比赛
                                    $RaceList[$RaceId]['Distance'] += $tInfo['ToPrevious'] * $tInfo['Round'];
                                }
                            }
                        }
                        else
                        {
                            unset($RaceList[$RaceId]['comment']['DetailList'][$detailId]);
                        }
                    }
                }
                else
                {
                    //初始化为空数组
                    $RaceList[$RaceId]['comment']['DetailList'] = array();
                }
                //如果有配置可选的分组
                if (isset($RaceInfo['comment']['SelectedRaceGroup']))
                {
                    //寻源已经选定的分组列表
                    foreach($RaceInfo['comment']['SelectedRaceGroup'] as $k => $v)
                    {
                        //如果查到就保留
                        if(isset($RaceGroupList[$k]))
                        {
                            $RaceList[$RaceId]['comment']['SelectedRaceGroup'][$k] = $RaceGroupList[$k];
                        }
                        //否则就删除
                        else
                        {
                            unset($RaceList[$RaceId]['comment']['SelectedRaceGroup'][$k]);
                        }
                    }
                }
            }
            //结果数组 如果列表有数据则为成功，否则为失败
            $result = array("return" => count($RaceList) ? 1 : 0, "RaceList" => $RaceList);
        }
        echo json_encode($result);
    }
    /*
     * 获得单个比赛信息
     */
    public function getRaceInfoAction()
    {
        //格式化比赛ID,默认为0
        $RaceId = isset($this->request->RaceId) ? abs(intval($this->request->RaceId)) : 0;
        //比赛ID必须大于0
        if ($RaceId)
        {
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId);
            //检测主键存在,否则值为空
            if (isset($RaceInfo['RaceId']))
            {
                //说明文字解码
                $RaceInfo['RaceComment'] = urldecode($RaceInfo['RaceComment']);
                //解包地图数据数组
                $RaceInfo['RouteInfo'] = json_decode($RaceInfo['RouteInfo'], true);
                //获取当前比赛的时间状态信息
                $RaceInfo['RaceStatus'] = $this->oRace->getRaceTimeStatus($RaceInfo);
                //解包数组
                $RaceInfo['comment'] = json_decode($RaceInfo['comment'], true);
                //处理价格列表
                $RaceInfo['PriceList'] = $this->oRace->getPriceList($RaceInfo['PriceList']);
                //获取当前比赛的时间状态信息
                $RaceInfo['RaceStatus'] = $this->oRace->getRaceTimeStatus($RaceInfo);
                //初始化比赛里程
                $RaceInfo['Distance'] = 0;
                //获取比赛分类信息
                $RaceTypeInfo = $RaceInfo['RaceTypeId'] ? $this->oRace->getRaceType($RaceInfo['RaceTypeId'], '*') : array();
                //如果获取到比赛类型信息
                if ($RaceTypeInfo['RaceTypeId'])
                {
                    $RaceTypeInfo['comment'] = json_decode($RaceTypeInfo['comment'], true);
                    //如果有输比赛类型图标的相对路径
                    if (isset($RaceTypeInfo['comment']['RaceTypeIcon_root']))
                    {
                        //拼接上ADMIN站点的域名
                        $RaceTypeInfo['RaceTypeIcon'] = $this->config->adminUrl . ($RaceTypeInfo['comment']['RaceTypeIcon_root']);
                    }
                    //删除原有数据
                    unset($RaceTypeInfo['comment']);
                }
                //存入结果数组
                $RaceInfo['RaceTypeInfo'] = $RaceTypeInfo;
                //如果有配置运动分段
                if (isset($RaceInfo['comment']['DetailList']))
                {
                    //获取运动类型类表
                    $SportsTypeList = $this->oSports->getAllSportsTypeList("SportsTypeId,SportsTypeName");
                    //循环运动分段
                    foreach ($RaceInfo['comment']['DetailList'] as $detailId => $detailInfo)
                    {
                        //如果有配置过该运动分段
                        if (isset($SportsTypeList[$detailInfo['SportsTypeId']]))
                        {
                            //获取运动类型名称
                            $RaceInfo['comment']['DetailList'][$detailId]['SportsTypeName'] = $SportsTypeList[$detailInfo['SportsTypeId']]['SportsTypeName'];
                            //初始化运动分段的长度
                            $RaceInfo['comment']['DetailList'][$detailId]['Distance'] = 0;
                            //获取计时点信息
                            $TimingInfo = isset($detailInfo['TimingId']) ? $this->oRace->getTimingDetail($detailInfo['TimingId']) : array();
                            //如果获取到计时点信息
                            if (isset($TimingInfo['TimingId'])) {
                                //数据解包
                                $TimingInfo['comment'] = isset($TimingInfo['comment']) ? json_decode($TimingInfo['comment'], true) : array();
                                //循环计时点信息
                                foreach ($TimingInfo['comment'] as $tid => $tInfo) {
                                    //累加里程到运动分段
                                    $RaceInfo['comment']['DetailList'][$detailId]['Distance'] += $tInfo['ToPrevious'] * $tInfo['Round'];
                                    //累加里程到比赛
                                    $RaceInfo['Distance'] += $tInfo['ToPrevious'] * $tInfo['Round'];
                                }
                            }
                        } else {
                            unset($RaceInfo['comment']['DetailList'][$detailId]);
                        }
                    }
                }
                else
                {
                    //初始化为空数组
                    $RaceInfo['comment']['DetailList'] = array();
                }
                //比赛-分组的层级规则
                $RaceStructureList  = $this->oRace->getRaceStructure();
                //获得分站信息
                $RaceStageInfo = $this->oRace->getRaceStage($RaceInfo['RaceStageId'],"RaceStageId,comment,RaceCatalogId");
                //数据解包
                $RaceStageInfo['comment'] = json_decode($RaceStageInfo['comment'],true);
                //如果没有配置比赛结构 或者 比赛结构配置不在配置列表中
                if(!isset($RaceStageInfo['comment']['RaceStructure']) || !isset($RaceStructureList[$RaceStageInfo['comment']['RaceStructure']]))
                {
                    //默认为分组优先
                    $RaceStageInfo['comment']['RaceStructure'] = "group";
                }
                else
                {
                    $RaceStageInfo['comment']['RaceStructure'] = "race";
                }
                //复写赛事结构
                $RaceInfo['RaceStructure'] = $RaceStageInfo['comment']['RaceStructure'];
                //赛事结构为分组优先
                if($RaceStageInfo['comment']['RaceStructure'] == "group")
                {

                }
                else
                {
                    //如果有配置可选的分组
                    if (isset($RaceInfo['comment']['SelectedRaceGroup']))
                    {
                        //获取当前赛事下的分组列表
                        $RaceGroupList = $this->oRace->getRaceGroupList($RaceStageInfo['RaceCatalogId'],"RaceGroupName,RaceGroupId,comment");
                        //寻源已经选定的分组列表
                        foreach($RaceInfo['comment']['SelectedRaceGroup'] as $k => $v)
                        {
                            //如果查到就保留
                            if(isset($RaceGroupList[$k]) && $v['Selected'])
                            {
                                $RaceGroupInfo = array_merge($RaceGroupList[$k],$v);
                                //默认当前组别可选
                                $RaceGroupInfo['checkable'] = true;
                                //数据解包
                                $RaceGroupInfo['comment'] = json_decode($RaceGroupInfo['comment'], true);
                                //执照条件的审核
                                $RaceGroupInfo['comment']['LicenseList'] = $this->oRace->raceLicenseCheck($RaceGroupInfo['comment']['LicenseList'], 0, $RaceStageInfo, $RaceGroupInfo);
                                foreach ($RaceGroupInfo['comment']['LicenseList'] as $k2 => $v2)
                                {
                                    //如果发现条件为不可选
                                    if (isset($v2['checked']) && $v2['checked'] == false)
                                    {
                                        //将当前组别置为不可选
                                        $RaceGroupInfo['checkable'] = false;
                                        break;
                                    }
                                }
                                //格式化执照的条件，供显示
                                $LicenseListText = $this->oRace->ParthRaceLicenseListToHtml($RaceGroupInfo['comment']['LicenseList'], 0, 0, 1);
                                foreach($LicenseListText as $k3 => $v3)
                                {
                                    if(isset($RaceGroupInfo['comment']['LicenseList'][$k3]))
                                    {
                                        $RaceGroupInfo['comment']['LicenseList'][$k3]['LicenseListText'] = $v3;
                                    }
                                }
                                $RaceInfo['comment']['SelectedRaceGroup'][$k] = $RaceGroupInfo;
                            }
                            //否则就删除
                            else
                            {
                                unset($RaceInfo['comment']['SelectedRaceGroup'][$k]);
                            }
                        }
                    }

                }
                //结果数组
                $result = array("return" => 1, "RaceInfo" => $RaceInfo);
            } else {
                //全部置为空
                $result = array("return" => 0, "RaceInfo" => array(), "comment" => "请指定一个有效的比赛ID");
            }
        } else {
            //全部置为空
            $result = array("return" => 0, "RaceInfo" => array(), "comment" => "请指定一个有效的比赛ID");
        }
        echo json_encode($result);
    }

    /*
     * 获取指定赛事组别下的车队列表
     */
    public function getTeamListAction()
    {
        //格式化赛事ID
        $RaceCatalogId = abs(intval($this->request->RaceCatalogId));
        //格式化赛事ID
        $RaceGroupId = abs(intval($this->request->RaceGroupId));
        //赛事ID必须大于0
        if ($RaceCatalogId)
        {
            //获取赛事信息
           // $RaceCatalogInfo = $this->oRace->getRaceCatalog($RaceCatalogId,"RaceCatalogId,RaceCatalgName",1);
           // print_R($RaceCatalogInfo);
            //检测主键存在,否则值为空
           // if (isset($RaceCatalogInfo['RaceCatalogId']))
            {
                $oTeam = new Xrace_Team();
                $TeamList = $oTeam->getTeamList(array("RaceCatalogId"=>$RaceCatalogId), 1);
                    //结果数组
                    if (count($TeamList['TeamList'])) {
                        $result = array("return" => 1, "TeamList" => $TeamList['TeamList']);
                    } else {
                        $result = array("return" => 0, "TeamList" => array(), "comment" => "组别下并未有队伍");
                    }
                }

            // else {
            //    //全部置为空
            //    $result = array("return" => 0, "TeamList" => array(), "comment" => "请指定一个有效的赛事ID");
           // }
        } else {
            //全部置为空
            $result = array("return" => 0, "RaceCatalog" => array(), 'RaceGroupList' => array(), 'RaceStageList' => array(), "comment" => "请指定一个有效的赛事ID");
        }
        echo json_encode($result);
    }

    /*
     * 获取指定比赛报名的车队列表
    */
    public function getRaceUserListByRaceAction()
    {
        //比赛ID
        $RaceId = abs(intval($this->request->RaceId));
        //队伍ID -1表示个人选手 0表示全部
        $TeamId = intval($this->request->TeamId) >= -1 ? intval($this->request->TeamId) : 0;
        //赛事ID必须大于0
        if ($RaceId) {
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId);
            //检测主键存在,否则值为空
            if (isset($RaceInfo['RaceId'])) {
                //获取选手和车队名单
                $params = array('RaceId'=>$RaceId,"RaceStatus"=>"all","TeamId"=>$TeamId,"Cache"=>0);
                $RaceUserList = $this->oUser->getRaceUserListByRace($params);
                if (count($RaceUserList['RaceUserList'])) {
                    //返回车手名单和车队列表
                    $result = array("return" => 1, "RaceUserList" => $RaceUserList['RaceUserList'], "TeamList" => $RaceUserList['TeamList']);
                } else {
                    //全部置为空
                    $result = array("return" => 0, "RaceUserList" => array(), "TeamList" => array(), "comment" => "尚无选手报名");
                }
            } else {
                //全部置为空
                $result = array("return" => 0, "RaceUserList" => array(), "TeamList" => array(), "comment" => "请指定一个有效的比赛ID");
            }
        } else {
            //全部置为空
            $result = array("return" => 0, "RaceUserList" => array(), "TeamList" => array(), "comment" => "请指定一个有效的赛事ID");
        }
        echo json_encode($result);
    }

    /*
     * 获取指定用户或BIB在比赛中的详情
    */
    public function getUserRaceInfoAction()
    {
        //比赛ID
        $RaceId = abs(intval($this->request->RaceId));
        //比赛ID
        $RaceUserId = abs(intval($this->request->RaceUserId));
        //比赛ID
        $RaceGroupId = abs(intval($this->request->RaceGroupId));
        //用户BIB
        $BIB = trim($this->request->BIB);
        //是否强制获取
        $Force = abs(intval($this->request->Force));
        //是否获取特定点位信息
        $Point = intval($this->request->Point)?intval($this->request->Point):0;
        $returnType = intval($this->request->returnType)?intval($this->request->returnType):0;
        //获取比赛信息
        $RaceInfo = $this->oRace->getRace($RaceId);
        //检测主键存在,否则值为空
        if (isset($RaceInfo['RaceId']))
        {
            $RaceInfo['comment'] = json_decode($RaceInfo['comment'], true);
            if ($RaceInfo['comment']['ResultNeedConfirm'] == 1) {
                if ($Force != 1 && $RaceInfo['comment']['RaceResultConfirm']['ConfirmStatus'] != 1) {
                    $result = array("return" => 2, "RaceUserList" => array(), "TeamList" => array(), "comment" => "等待裁判确认成绩后方可公布！");
                    echo json_encode($result);
                    die();
                }
            }
            if (!$RaceUserId && $BIB != "")
            {
                //根据用户的BIB获取比赛报名信息
                $UserApplyInfo = $this->oUser->getRaceApplyUserInfoByBIB($RaceId, $BIB);
                //如果查询到报名记录
                if ($UserApplyInfo['ApplyId']) {
                    //保存用户ID
                    $RaceUserId = $UserApplyInfo['RaceUserId'];
                }
            }
            if ($RaceUserId > 0)
            {
                //获取用户比赛的详情
                $UserRaceInfo = $this->oRace->getUserRaceInfo($RaceId, $RaceUserId);
                //如果有查出数据
                if (!isset($UserRaceInfo['RaceUserInfo']))
                {
                    //重新生成该场比赛所有人的配置数据
                    $this->oRace->genRaceLogToText($RaceId, $RaceUserId);
                    //重新获取比赛详情
                    $UserRaceInfo = $this->oRace->getUserRaceInfo($RaceId, $RaceUserId);
                }
                $oRanking = new Xrace_Ranking();
                $RankingList = $oRanking->getRankingByRace($RaceId,$UserRaceInfo['Total']['RaceGroupId']);
                if(count($RankingList))
                {
                    foreach($RankingList as $key => $RankingInfo)
                    {
                        $RankingDetail = $oRanking->getRaceInfoByRankingByFile($RankingInfo["RankingId"]);
                        foreach($RankingDetail["UserList"] as $key2 => $UserInfo)
                        {
                            if($UserInfo['RaceUserId']!=$RaceUserId)
                            {
                                unset($RankingDetail["UserList"][$key2]);
                            }
                            else
                            {
                                $RankingDetail["UserList"][$key2]['Total']['Rank'] = $key2+1;
                            }
                        }
                        if(count($RankingDetail["UserList"]))
                        {
                            $UserRankingList[$RankingInfo["RankingId"]] = $RankingDetail;
                        }
                    }
                }
                $UserRaceInfo['ApplyInfo']['RaceStatus'] = $this->oRace->getUserRaceStatus($UserRaceInfo);
                $result = array("return" => isset($UserRaceInfo['ApplyInfo']) ? 1 : 0, "UserRaceInfo" => $UserRaceInfo,"UserRankingList" => count($UserRankingList)?$UserRankingList:array());
            }
            else
            {
                $UserRaceInfo = $this->oRace->GetUserRaceTimingInfo($RaceId,$RaceGroupId);
                if(isset($UserRaceInfo['Point'][$Point]))
                {
                    foreach($UserRaceInfo['Point'] as $key => $value)
                    {
                        if($key != $Point)
                        {
                            unset($UserRaceInfo['Point'][$key]);
                        }
                    }
                    if($returnType == 1)
                    {
                        $RaceGroupList = array();
                        $RaceInfo['RouteInfo'] = json_decode($RaceInfo['RouteInfo'], true);
                        $UserList = $this->oRace->getRaceUserListByFile($RaceInfo['RaceId']);
                        foreach($UserRaceInfo['Point'][$Point]['UserList'] as $key => $value)
                        {
                            if($RaceInfo['RouteInfo']['RaceTimingResultType']=="net")
                            {
                                $StartTime = $value['inTime'];
                            }
                            else
                            {
                                $StartTime = $value['inTime']-$value['TotalTime'];
                            }
                            //print_R($UserList);
                            foreach($UserList['RaceUserList'] as $key2 => $UserInfo)
                            {
                                if($UserInfo['RaceUserId'] == $value['RaceUserId'])
                                {
                                    if(!isset($RaceGroupList[$UserInfo['RaceGroupId']]))
                                    {
                                        $RaceGroupList[$UserInfo['RaceGroupId']] = $this->oRace->getRaceGroup($UserInfo['RaceGroupId']);
                                    }
                                    $text = $UserInfo['ChipId'].",".$value['Name'].",".$UserInfo['BIB'].",".$StartTime.",".$UserInfo['TeamName'].",".$RaceGroupList[$UserInfo['RaceGroupId']]['RaceGroupName']."<br>";
                                }
                            }
                            echo $text;
                        }
                        die();
                    }
                }

                $result = array("return" => isset($UserRaceInfo['RaceInfo']) ? 1 : 0, "UserRaceInfo" => $UserRaceInfo);
                $SegmentInfo = $this->oRace->getUserRaceSegmentInfo($RaceId);
                if(is_array($SegmentInfo))
                {
                    $result["Segment"] = $SegmentInfo;
                }
            }
        }
        else
        {
            //全部置为空
            $result = array("return" => 0, "comment" => "请指定一个有效的比赛ID");
        }
        echo json_encode($result);
    }

    /*
     * 获取指定用户的报名记录
    */
    public function getUserRaceListAction()
    {
        //用户ID
        $UserId = abs(intval($this->request->UserId));
        if ($UserId)
        {
            //获取用户信息
                $UserInfo = $this->oUser->getUserInfo($UserId, 'UserId,name');
                //如果有获取到用户信息
                if ($UserInfo['UserId'])
                {
                //根据用户获取报名记录
                $UserApplyList = $this->oUser->getRaceUserList(array('UserId' => $UserInfo['UserId']));
                //获取赛事列表
                $RaceCatalogList = $this->oRace->getRaceCatalogList(0,"RaceCatalogId,RaceCatalogName");
                $RaceGroupList = array();
                $RaceStageList = array();
                $RaceTypeList = array();
                //循环报名列表
                foreach ($UserApplyList as $key => $ApplyInfo)
                {
                    if (isset($RaceCatalogList[$ApplyInfo['RaceCatalogId']]))
                    {
                        $UserApplyList[$key]['comment'] = json_decode($ApplyInfo['comment'], true);
                        $UserApplyList[$key]['RaceCatalogName'] = $RaceCatalogList[$ApplyInfo['RaceCatalogId']]['RaceCatalogName'];
                        if (!isset($RaceGroupList[$ApplyInfo['RaceGroupId']]))
                        {
                            $RaceGroupInfo = $this->oRace->getRaceGroup($ApplyInfo['RaceGroupId'], 'RaceGroupId,RaceGroupName');
                            if (isset($RaceGroupInfo['RaceGroupId']))
                            {
                                $RaceGroupList[$ApplyInfo['RaceGroupId']] = $RaceGroupInfo;
                            }
                            else
                            {
                                unset($UserApplyList[$key]);
                            }
                        }
                        $UserApplyList[$key]['RaceGroupName'] = $RaceGroupList[$ApplyInfo['RaceGroupId']]['RaceGroupName'];
                        if (!isset($RaceStageList[$ApplyInfo['RaceStageId']]))
                        {
                            $RaceStageInfo = $this->oRace->getRaceStage($ApplyInfo['RaceStageId'], 'RaceStageId,RaceStageName');
                            if (isset($RaceStageInfo['RaceStageId']))
                            {
                                $RaceStageList[$ApplyInfo['RaceStageId']] = $RaceStageInfo;
                            }
                            else
                                {
                                unset($UserApplyList[$key]);
                            }
                        }
                        $UserApplyList[$key]['RaceStageName'] = $RaceStageList[$ApplyInfo['RaceStageId']]['RaceStageName'];

                        $RaceInfo = $this->oRace->getRace($ApplyInfo['RaceId'], "*");
                        if (isset($RaceInfo['RaceId']))
                        {
                            $UserApplyList[$key]['RaceName'] = $RaceInfo['RaceName'];
                            if (!isset($RaceTypeList[$RaceInfo['RaceTypeId']]))
                            {
                                $RaceTypeInfo = $this->oRace->getRaceType($RaceInfo['RaceTypeId'], '*');
                                if (isset($RaceTypeInfo['RaceTypeId']))
                                {
                                    $RaceTypeInfo['comment'] = json_decode($RaceTypeInfo['comment'], true);
                                    //拼接上ADMIN站点的域名
                                    $RaceTypeInfo['comment']['RaceTypeIcon'] = $this->config->adminUrl . $RaceTypeInfo['comment']['RaceTypeIcon_root'];
                                    $RaceTypeList[$RaceInfo['RaceTypeId']] = $RaceTypeInfo;
                                }
                                else
                                {
                                    unset($UserApplyList[$key]);
                                }
                            }
                            $UserApplyList[$key]['RaceTypeIcon'] = $RaceTypeList[$RaceInfo['RaceTypeId']]['comment']['RaceTypeIcon'];
                            $UserApplyList[$key]['RaceTypeName'] = $RaceTypeList[$RaceInfo['RaceTypeId']]['RaceTypeName'];
                            $UserApplyList[$key]['RaceStatus'] = $this->oRace->getUserRaceStatus($this->oRace->getUserRaceInfo($ApplyInfo['RaceId'], $UserId));
                        }
                        else
                        {
                            unset($UserApplyList[$key]);
                        }
                    }
                    else
                    {
                        unset($UserApplyList[$key]);
                    }
                }
                $result = array("return" => 1, "UserRaceList" => $UserApplyList);
            }
            else
            {
                $result = array("return" => 0, "UserRaceList" => array(), "comment" => "无此用户");
            }
        }
        else
        {
            $result = array("return" => 0, "UserRaceList" => array(), "comment" => "请指定一个有效的用户ID");
        }
        echo json_encode($result);
    }

    /*
 * 获取指定比赛报名的车队列表
*/
    public function getRaceUserListByBibAction()
    {
        //比赛ID
        $RaceId = abs(intval($this->request->RaceId));
        //分组ID
        $RaceGroupId = abs(intval($this->request->RaceGroupId));
        //队伍ID
        $TeamId = abs(intval($this->request->TeamId));
        //BIB号码
        $BIB = trim(urldecode($this->request->BIB));
        //姓名
        $Name = trim(urldecode($this->request->Name));
        //赛事ID必须大于0
        if ($RaceId)
        {
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId);
            //检测主键存在,否则值为空
            if (isset($RaceInfo['RaceId']))
            {
                $RaceUserList = $this->oRace->getRaceUserListByFile($RaceId);
                //获取选手和车队名单
                $params = array('RaceId'=>$RaceId,"RaceStatus"=>"all","TeamId"=>0,"Cache"=>0);
                $RaceUserList = $this->oUser->getRaceUserListByRace($params);
                if (count($RaceUserList['RaceUserList']))
                {
                    $TeamList = array();
                    $t = array();
                    foreach ($RaceUserList['RaceUserList'] as $ApplyId => $ApplyInfo)
                    {
                        if ((((strlen($BIB)>0) && strstr($ApplyInfo['BIB'], $BIB)) || (strlen($BIB)==0)) && (((strlen($Name)>0) && strstr($ApplyInfo['Name'], $Name)) || (strlen($Name)==0)) && (($RaceGroupId == 0) || (($RaceGroupId >0) && ($RaceGroupId == $ApplyInfo['RaceGroupId']))) && (($TeamId == 0) || (($TeamId >0) && ($TeamId == $ApplyInfo['TeamId']))))
                        {
                            $t[] = $ApplyInfo;
                        }
                    }
                    if(!isset($TeamList[$ApplyInfo['TeamId']]))
                    {
                        $TeamList[$ApplyInfo['TeamId']] = array("TeamName"=>$ApplyInfo['TeamName']);
                    }
                    $RaceUserList = $t;
                    //返回车手名单和车队列表
                    $result = array("return" => 1, "RaceUserList" => count($RaceUserList)>0?$RaceUserList:array());
                } else {
                    //全部置为空
                    $result = array("return" => 0, "RaceUserList" => array(), "TeamList" => array(), "comment" => "尚无选手报名");
                }
            }
            else
            {
                //全部置为空
                $result = array("return" => 0, "RaceUserList" => array(), "TeamList" => array(), "comment" => "请指定一个有效的比赛ID");
            }
        }
        else
        {
            //全部置为空
            $result = array("return" => 0, "RaceUserList" => array(), "TeamList" => array(), "comment" => "请指定一个有效的赛事ID");
        }
        echo json_encode($result);
    }
    /*
    * 测试生成计时点
    */
    public function timingTextAction()
    {
        set_time_limit(0);
        $oMylaps = new Xrace_Mylaps();
        //格式化比赛ID
        $RaceId = abs(intval($this->request->RaceId));
        $Force = abs(intval($this->request->Force));
        $Cache = trim($this->request->Cache);
        //if($Type=="new")
        //{
            $oMylaps->genMylapsTimingInfo($RaceId,$Force,$Cache);
            die();
        //}
    }
    /*
    * 获取指定选手指定分站的签到信息
    */
    public function getRaceUserCheckInAction()
    {
        //用户ID
        $RaceUserId = abs(intval($this->request->RaceUserId));
        //分站ID
        $RaceStageId = abs(intval($this->request->RaceStageId));
        //获取用户签到信息
        $UserCheckInInfo = $this->oUser->getUserCheckInInfo($RaceUserId,$RaceStageId);
        //如果找到记录
        if($UserCheckInInfo['RaceStageId'])
        {
            //获得分站信息
            $RaceStageInfo = $this->oRace->getRaceStage($RaceStageId,"RaceStageId,RaceStageName");
            //如果获取到分站信息
            if(!$RaceStageInfo['RaceStageId'])
            {
                $RaceStageInfo = array();
            }
            //获取用户信息
            $UserInfo = $this->oUser->getUserInfo($UserCheckInInfo['UserId'], 'UserId,name');
            //如果有获取到用户信息
            if (!$UserInfo['UserId'])
            {
                $UserInfo = array();
            }
            //根据用户获取报名记录
            $UserRaceList = $this->oUser->getRaceUserList(array('RaceUserId' => $UserInfo['RaceUserId'],'RaceStageId'=>$RaceStageInfo['RaceStageId']));
            //初始化空的比赛列表
            $RaceList = array();
            //初始化空的分组列表
            $RaceGroupList = array();
            //循环报名记录
            foreach($UserRaceList as $key => $ApplyInfo)
            {
                if(!isset($RaceList[$ApplyInfo['RaceId']]))
                {
                    $RaceInfo = $this->oRace->getRace($ApplyInfo['RaceId'], "RaceId,RaceName");
                    if (isset($RaceInfo['RaceId']))
                    {
                        $RaceList[$ApplyInfo['RaceId']] = $RaceInfo;
                    }
                }
                if(!isset($RaceGroupList[$ApplyInfo['RaceGroupId']]))
                {
                    $RaceGroupInfo = $this->oRace->getRaceGroup($ApplyInfo['RaceGroupId'], "RaceGroupId,RaceGroupName");
                    if (isset($RaceGroupInfo['RaceGroupId']))
                    {
                        $RaceGroupList[$ApplyInfo['RaceGroupId']] = $RaceGroupInfo;
                    }
                }
                $UserRaceList[$key]['RaceName'] = $RaceList[$ApplyInfo['RaceId']]['RaceName'];
                $UserRaceList[$key]['RaceGroupName'] = $RaceGroupList[$ApplyInfo['RaceGroupId']]['RaceGroupName'];
            }
            //全部置为空
            $result = array("return" => 1, "UserCheckInInfo"=>$UserCheckInInfo,"UserRaceList"=>$UserRaceList, "UserInfo" => $UserInfo, "RaceStageInfo" => $RaceStageInfo);
        }
        else
        {
            //全部置为空
            $result = array("return" => 0, "UserInfo" => array(), "RaceStageInfo" => array(), "comment" => "签到信息有误");
        }
        echo json_encode($result);
    }
    /*
     * 获取指定选手指定分站的签到信息
     */
    public function getRaceUserCheckInListAction()
    {
        //用户ID
        $RaceUserId = abs(intval($this->request->RaceUserId));
        //获取用户签到信息
        $UserCheckInList = $this->oUser->getRaceUserCheckInList(array('RaceUserId'=>$RaceUserId));
        //初始化空的分站列表
        $RaceStageList = array();
        foreach($UserCheckInList as $key => $CheckInInfo)
        {
            if(!isset($RaceStageList[$CheckInInfo['RaceStageId']]))
            {
                $RaceStageInfo = $this->oRace->getRaceStage($CheckInInfo['RaceStageId'], "RaceStageId,RaceStageName");
                if(isset($RaceStageInfo['RaceStageId']))
                {
                    $RaceStageList[$CheckInInfo['RaceStageId']] = $RaceStageInfo;
                }
            }
            $UserCheckInList[$key]['RaceStageName'] = $RaceStageList[$CheckInInfo['RaceStageId']]['RaceStageName'];
        }
        $result = array("return" => 1, "UserCheckInList" => $UserCheckInList);
        echo json_encode($result);
    }
    public function getCombinationListByStageAction()
    {
        //格式化赛事分站ID,默认为0
        $RaceStageId = isset($this->request->RaceStageId) ? abs(intval($this->request->RaceStageId)) : 0;
        $CombinationList = $this->oRace->getRaceCombinationList(array("RaceStageId"=>$RaceStageId));
        foreach($CombinationList as $Id => $CombinationInfo)
        {
            print_R($CombinationInfo);
        }
    }
    public function socketTestAction()
    {
        $oMylaps = new Xrace_Mylaps();
        $text = "jjj@Passing@c=CR43438|ct=CX|t=14:38:00.132|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=CR43438|b=-1@8@$";
        $text1 = "jjj@Passing@c=HZ06571|ct=CX|t=14:44:02.841|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=HZ06571|b=-1@c=KW48567|ct=CX|t=14:44:02.906|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=KW48567|b=-1@c=KW47671|ct=CX|t=14:44:02.922|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=KW47671|b=-1@c=SF92741|ct=CX|t=14:44:02.934|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=SF92741|b=-1@c=CC39407|ct=CX|t=14:44:02.843|d=170519|l=3|dv=1|re=0|an=-1|g=-1|n=CC39407|b=-1@c=CX79393|ct=CX|t=14:44:02.964|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=CX79393|b=-1@c=KH14107|ct=CX|t=14:44:02.860|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=KH14107|b=-1@c=KP66964|ct=CX|t=14:44:02.974|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=KP66964|b=-1@15@$";
        $text2 = "L86490|ct=CX|t=14:44:02.831|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=RL86490|b=-1@c=GV59557|ct=CX|t=14:44:02.881|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=GV59557|b=-1@c=HP46938|ct=CX|t=14:44:02.847|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=HP46938|b=-1@14@$";
        $text3 = "jjj@Passing@c=RN12649|ct=CX|t=14:37:59.979|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=RN12649|b=-1@c=FN59312|ct=CX|t=14:38:00.030|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=FN59312|b=-1@c=RX80579|ct=CX|t=14:37:59.976|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=RX80579|b=-1@c=FK59372|ct=CX|t=14:38:00.010|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=FK59372|b=-1@c=GK26410|ct=CX|t=14:37:59.990|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=GK26410|b=-1@c=GK85644|ct=CX|t=14:38:00.065|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=GK85644|b=-1@c=KW81076|ct=CX|t=14:38:00.046|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=KW81076|b=-1@c=KW47671|ct=CX|t=14:38:00.101|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=KW47671|b=-1@c=KP66964|ct=CX|t=14:38:00.111|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=KP66964|b=-1@c=GW97627|ct=CX|t=14:38:00.128|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=GW97627|b=-1@c=HZ50378|ct=CX|t=14:38:00.187|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=HZ50378|b=-1@c=KX88173|ct=CX|t=14:38:00.010|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=KX88173|b=-1@7@$";
        $textArr = array(
            "jjj@Passing@c=CR43438|ct=CX|t=14:38:00.132|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=CR43438|b=-1@8@$",
            "jjj@Passing@c=GV59557|ct=CX|t=14:38:00.246|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=GV59557|b=-1@c=KW48567|ct=CX|t=14:38:00.214|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=KW48567|b=-1@c=LH81075|ct=CX|t=14:38:00.049|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=LH81075|b=-1@c=HL59534|ct=CX|t=14:38:00.135|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=HL59534|b=-1@c=FK79732|ct=CX|t=14:38:00.184|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=FK79732|b=-1@c=HG68397|ct=CX|t=14:38:00.073|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=HG68397|b=-1@c=KW60342|ct=CX|t=14:38:00.273|d=170519|l=2|dv=1|re=0|an=-1|g=-1|n=KW60342|b=-1@9@$",
            "jjj@Passing@c=HX78750|ct=CX|t=14:38:00.135|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=HX78750|b=-1@c=KH14107|ct=CX|t=14:38:00.162|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=KH14107|b=-1@10@$",
            "jjj@Passing@c=FS43924|ct=CX|t=14:38:00.082|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=FS43924|b=-1@",
            "jjj@Passing@c=FS43924|ct=CX|t=14:38:00.082|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=FS43924|b=-1@c=LF82251|ct=CX|t=14:38:00.187|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=LF82251|b=-1@c=GG77645|ct=CX|t=14:38:00.106|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=GG77645|b=-1@c=FK82108|ct=CX|t=14:38:00.244|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=FK82108|b=-1@c=GN29338|ct=CX|t=14:38:00.313|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=GN29338|b=-1@c=HZ06571|ct=CX|t=14:38:00.176|d=170519|l=1|dv=1|re=0|an=-1|g",
            "=-1|n=HZ06571|b=-1@c=HG47276|ct=CX|t=14:38:00.405|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=HG47276|b=-1@c=HP46938|ct=CX|t=14:38:00.181|d=170519|l=1|dv=1|re=0|an=-1|g=-1|n=HP46938|b=-1@11@$");

        $MessageArr =  array("Text" =>$text3,"PassingMessage"=>"");


        $Last = "";
        foreach($textArr as $Key => $Value)
        {
            $MessageArr =  array("Text" =>$Last.$Value,"PassingMessage"=>"");
            echo "LastText:".$MessageArr["Text"]."<br><br>";
            do{
                $MessageArr = $oMylaps->popMylapsPassingMessage($MessageArr['Text']);
                //print_R($MessageArr);
                //echo "<br><br>";
                //die();
                //sleep(1);
                echo "PassingMessage:".$MessageArr['PassingMessage']."<br>";
                //print_R(base_common::parthStrToArr($MessageArr['PassingMessage']));
                print_R(base_common::parthMylapsArr(base_common::parthStrToArr($MessageArr['PassingMessage'])));
                //echo "Text:".$MessageArr['Text']."<br><br>";
            }
            while($MessageArr['PassingMessage'] != "");
            $Last = $MessageArr['Text'];
        }
    }
    /*
 * 更新处理指定排名情况的比赛数据
*/
    public function updateRaceUserListByRankingAction()
    {
        //排名ID
        $oRanking = new Xrace_Ranking();
        //赛事ID
        $RankingId = intval($this->request->RankingId);
        //获取排名信息
        $RankingInfo = $oRanking->getRanking($RankingId,"RankingId");
        //检测主键存在,否则值为空
        if (isset($RankingInfo['RankingId']))
        {
            $RaceUserList = $oRanking->updateRaceInfoByRanking($RankingId);
        }
        else
        {
            //全部置为空
            $result = array("return" => 0, "comment" => "请指定一个有效的排名ID");
        }
        echo json_encode($result);
    }
    /*
* 更新处理指定排名情况的比赛数据
*/
    public function getRaceUserListByRankingAction()
    {
        //排名ID
        $RankingId = abs(intval($this->request->Ranking));
        $oRanking = new Xrace_Ranking();
        //赛事ID
        $RankingId = trim($this->request->RankingId);
        //获取排名信息
        $RankingInfo = $oRanking->getRanking($RankingId);
        //检测主键存在,否则值为空
        if (isset($RankingInfo['RankingId']))
        {
            $RaceUserList = $oRanking->getRaceInfoByRankingByFile($RankingId);
            $result = array("return" => isset($UserRaceInfo['RaceInfo']) ? 1 : 0, "RaceUserList" => $RaceUserList);
        }
        else
        {
            //全部置为空
            $result = array("return" => 0, "comment" => "请指定一个有效的排名ID");
        }
        echo json_encode($result);
    }
    /*
    *更新指定分站的统计数据
    */
    public function updateStageDataAction()
    {
        //分站ID
        $RaceStageId = abs(intval($this->request->RaceStageId));
        $Data = $this->oRace->updateRaceStageData($RaceStageId);
    }
    /*
    *获得指定分站的统计数据
    */
    public function getStageDataAction()
    {
        //分站ID
        $RaceStageId = abs(intval($this->request->RaceStageId));
        $Data = $this->oRace->getRaceStageData($RaceStageId);
        //全部置为空
        $result = array("return" => 1, "Data" => $Data);
        echo json_encode($result);
    }
    /*
    *获得指定分站的统计数据
    */
    public function updateTimingPointAction()
    {
        //比赛ID
        $RaceId = abs(intval($this->request->RaceId));
        //运动类型ID
        $SportsTypeId = intval($this->request->SportsTypeId);
        //计时点ID
        $TimingId = isset($this->request->TimingId)?intval($this->request->TimingId):0;
        //获取 页面参数
        $bind = $this->request->from('TName','TencentX','TencentY');
        //更新计时点
        $UpdateTimingPoint = $this->oRace->updateTimingPoint($RaceId,$SportsTypeId,$TimingId,$bind);
        //全部置为空
        $result = array("return" => $UpdateTimingPoint?1:0);
        echo json_encode($result);
    }
    /*
 * 获得单个比赛信息
 */
    public function getTimingPointInfoAction()
    {
        //格式化比赛ID,默认为0
        $RaceId = isset($this->request->RaceId) ? abs(intval($this->request->RaceId)) : 0;
        //比赛ID必须大于0
        if ($RaceId)
        {
            //获取比赛信息
            $RaceInfo = $this->oRace->getRace($RaceId);
            //检测主键存在,否则值为空
            if (isset($RaceInfo['RaceId']))
            {
                //解包数组
                $RaceInfo['comment'] = json_decode($RaceInfo['comment'], true);
                //如果有配置运动分段
                if (isset($RaceInfo['comment']['DetailList']))
                {
                    $TimingPointInfo = $RaceInfo['comment']['DetailList'];
                    //获取运动类型类表
                    $SportsTypeList = $this->oSports->getAllSportsTypeList("SportsTypeId,SportsTypeName");
                    //循环运动分段
                    foreach ($RaceInfo['comment']['DetailList'] as $detailId => $detailInfo)
                    {
                        //如果有配置过该运动分段
                        if (isset($SportsTypeList[$detailInfo['SportsTypeId']]))
                        {
                            $TimingPointInfo[$detailId]["SportsTypeName"] = $SportsTypeList[$detailInfo['SportsTypeId']]["SportsTypeName"];
                            //获取计时点信息
                            $TimingInfo = isset($detailInfo['TimingId']) ? $this->oRace->getTimingDetail($detailInfo['TimingId']) : array();
                            //如果获取到计时点信息
                            if (isset($TimingInfo['TimingId']))
                            {
                                //数据解包
                                $TimingInfo['comment'] = isset($TimingInfo['comment']) ? json_decode($TimingInfo['comment'], true) : array();
                                $TimingPointInfo[$detailId]["TimingInfo"] = $TimingInfo;
                            }
                        }
                        else
                        {
                            unset($RaceInfo['comment']['DetailList'][$detailId]);
                        }
                    }
                }
                else
                {
                    //初始化为空数组
                    $TimingPointInfo = array();
                }
                //结果数组
                $result = array("return" => 1, "TimingPointInfo" => $TimingPointInfo);
            } else {
                //全部置为空
                $result = array("return" => 0, "RaceInfo" => array(), "comment" => "请指定一个有效的比赛ID");
            }
        } else {
            //全部置为空
            $result = array("return" => 0, "RaceInfo" => array(), "comment" => "请指定一个有效的比赛ID");
        }
        echo json_encode($result);
    }
}
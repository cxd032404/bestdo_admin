<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <link href="/favicon.ico" rel="shortcut icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>标题</title>
    <meta name="keywords" content="关键词">
    <meta name="description" content="描述">
    <link rel="stylesheet" href="echarts/css/common.css" />
    <link rel="stylesheet" href="echarts/css/style.css" />
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" />
    <script type="text/javascript" src='echarts/js/jquery-1.11.0.min.js'></script>
    <script type="text/javascript" src='echarts/js/echarts.min.js'></script>
</head>
<body>

<div class="backup_all">
    <div class="padd_alls">
        <div class="top_cont">
            <div class="padd_all">
                <ul>
                    <li>
                        <img src="echarts/images/cys.png" alt="">
                        <div class="top_ico_rt">
                            <span>参与人数<img src="echarts/images/shuom.png"></span>
                            <p>20000</p>
                        </div>
                    </li>
                    <li>
                        <img src="echarts/images/cys_3.png" alt="">
                        <div class="top_ico_rt">
                            <span>当日步数<img src="echarts/images/shuom.png"></span>
                            <p>10000</p>
                        </div>
                    </li>
                    <li>
                        <img src="echarts/images/cys_4.png" alt="">
                        <div class="top_ico_rt">
                            <span>本月步数<img src="echarts/images/shuom.png"></span>
                            <p>1820000</p>
                        </div>
                    </li>
                    <li>
                        <img src="echarts/images/cys_5.png" alt="">
                        <div class="top_ico_rt">
                            <span>总步数<img src="echarts/images/shuom.png"></span>
                            <p>9820000</p>
                        </div>
                    </li>
                    <li>
                        <img src="echarts/images/cys_2.png" alt="">
                        <div class="top_ico_rt">
                            <span>达成率<img src="echarts/images/shuom.png"></span>
                            <p>200.06%</p>
                        </div>
                    </li>
                </ul>
                <div style="clear:both;"></div>
            </div>
        </div>
        <div class="clear"></div>
        <div class="mi_main">
            <div class="leftbar">
                <div class="padd_all">
                    <div class="ty_tit">
                        <div class="ty_lt">
                            活动报名与上传作品情况分析
                        </div>
                        <div class="ty_rt">
                            <!-- 111 -->
                        </div>
                    </div>
                    <div id="my_charts">
                    </div>
                </div>
                <div class="padd_all">
                    <div class="bus_rank">
                        <div class="bus_rank_list bus_rank_list_first">
                            <div class="ty_tit">
                                <div class="ty_lt">
                                    部门步数排行榜
                                </div>
                                <div class="ty_rt">
                                    <div class="mouth_tog">
                                        <ul>
                                            <li><a href="#">本月</a></li>
                                            <li><a href="#">上月</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="bs_rank_nr">
                                <div class="bs_rank_nr_top">
                                    <ul>
                                        <li>排名</li>
                                        <li>部门</li>
                                        <li>步数</li>
                                    </ul>
                                </div>
                                <div class="bs_rank_nr_cont">
                                    <ul>
                                        <?php
                                  $i = 1;
                                    foreach($StepsStatList['List'] as $key => $value)
                                        {
                                        ?><li>
                                            <div class="pam_a"> <?php echo $i; $i++;?>   </div>
                                            <div class="pam_b">  <?php echo $value['department_name']; ?>  </div>
                                            <div class="pam_c">
                                                <div class="pam_c_a">
                                                    <?php echo number_format($value['totalStep']); ?>
                                                </div>
                                                <div class="pam_c_b">
                                                    <span style="width:<?php echo $value['bar_rate']; ?>%"></span>
                                                </div>
                                            </div>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="bus_rank_list bus_rank_list_zb">
                            <div class="ty_tit">
                                <div class="ty_lt">
                                    部门步数占比排行榜
                                </div>
                                <div class="ty_rt">
                                    <div class="mouth_tog">
                                        <ul>
                                            <li><a href="#">本月</a></li>
                                            <li><a href="#">上月</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="bs_rank_nr">
                                <div class="bs_rank_nr_top bs_rank_crile_top">
                                    <ul>
                                        <li>排名</li>
                                        <li>部门</li>
                                        <li>步数</li>
                                    </ul>
                                </div>
                                <div class="bs_rank_nr_cont bs_rank_crile">
                                    <ul>
                                        <?php
                                  $i = 1;
                                    foreach($StepsStatList['List'] as $key => $value)
                                        {
                                        ?>
                                        <li>
                                            <div class="pam_a">  <?php echo $i; $i++;?>  </div>
                                            <div class="pam_b">  <?php echo $value['department_name']; ?>  </div>
                                            <div class="pam_c">
                                                <div class="pam_c_a">
                                                    <?php echo $value['circle_rate']; ?>%
                                                </div>
                                                <div class="pam_c_d">
                                                    <div id="pcm_cal<?php echo "_".$key;?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <?php } ?>


                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="rightbar">
                <div class="padd_all jlb_fx">
                    <div class="ty_tit">
                        <div class="ty_lt">
                            <img src="echarts/images/ggs.png" alt="">俱乐部活动分析
                        </div>
                    </div>
                    <div class="jlb_fx_nr">
                        <div class="jlb_fx_nr_top">
                            <span>俱乐部</span>
                            <span>举办次数</span>
                            <span>参与人数</span>
                        </div>
                        <div class="jlb_fx_nr_data">
                            <ul>
                                <li>
                                    <div class="jb_a"><span>1</span>沪联邦篮球俱乐部</div>
                                    <div class="jb_b">12</div>
                                    <div class="jb_c">15,465</div>
                                </li>
                                <li>
                                    <div class="jb_a"><span>2</span>沪联邦篮球俱乐部</div>
                                    <div class="jb_b">12</div>
                                    <div class="jb_c">15,465</div>
                                </li>
                                <li>
                                    <div class="jb_a"><span>3</span>沪联邦篮球俱乐部</div>
                                    <div class="jb_b">12</div>
                                    <div class="jb_c">15,465</div>
                                </li>
                                <li>
                                    <div class="jb_a"><span>4</span>沪联邦篮球俱乐部</div>
                                    <div class="jb_b">12</div>
                                    <div class="jb_c">15,465</div>
                                </li>
                                <li>
                                    <div class="jb_a"><span>5</span>沪联邦篮球俱乐部</div>
                                    <div class="jb_b">12</div>
                                    <div class="jb_c">15,465</div>
                                </li>
                                <li>
                                    <div class="jb_a"><span>6</span>沪联邦篮球俱乐部</div>
                                    <div class="jb_b">12</div>
                                    <div class="jb_c">15,465</div>
                                </li>
                                <li>
                                    <div class="jb_a"><span>3</span>沪联邦篮球俱乐部</div>
                                    <div class="jb_b">12</div>
                                    <div class="jb_c">15,465</div>
                                </li>
                                <li>
                                    <div class="jb_a"><span>4</span>沪联邦篮球俱乐部</div>
                                    <div class="jb_b">12</div>
                                    <div class="jb_c">15,465</div>
                                </li>
                                <li>
                                    <div class="jb_a"><span>5</span>沪联邦篮球俱乐部</div>
                                    <div class="jb_b">12</div>
                                    <div class="jb_c">15,465</div>
                                </li>

                            </ul>
                        </div>
                        <div class="jlb_fx_nr_more">
                            <a href="#">查看更多</a>
                        </div>
                    </div>


                </div>
                <div class="padd_all">
                    <div class="ty_tit">
                        <div class="ty_lt">
                            <img src="echarts/images/grs.png" alt="">活动类型占比
                        </div>
                    </div>
                    <div id="my_charts2"></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>

</body>
<script type="text/javascript">
    var myChart = echarts.init(document.getElementById('my_charts'));
    var myChart2 = echarts.init(document.getElementById('my_charts2'));
    //var data_show = ['天籁之音', '绘声绘影', '书香瑰宝', '强身健体', '热血男儿', '玫瑰玫瑰', '职场达人','视觉盛宴','妙笔丹青','巧手灵心','藏品共赏','奇技淫巧','健康分享'];
    var data_show = [<?php echo $nameListText;?>];
    var options = {
        tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        legend: {
            //data: ['数据源1', '数据源2']
            data: ['报名人数', '上传数量']

        },
        grid: {
            left: '0%',
            right: '0%',
            bottom: '0%',
            containLabel: true
        },
        xAxis: [
            {
                type: 'category',
                axisTick:{ //X轴刻度线
                    show:false
                },
                //设置字体样式
                axisLabel: {
                    show: true,
                    textStyle: {
                        fontSize: '14',
                        color: '#999999',
                        lineHeight:20,
                    }
                },
                axisLine: {
                    show: true,
                    lineStyle:{
                        color:' #EBEEF7'
                    }
                },
                //显示虚线分割线

                data: data_show.map(function (str) { return str.replace(/.{2}\x01?/g,"$&\n"); }),
            }
        ],
        yAxis: [
            {
                type: 'value',
                //设置字体样式
                axisLabel: {
                    show: true,
                    textStyle: {
                        fontSize: '14',
                        color: '#CBCBCB',
                    }
                },
                axisTick:{ //y轴刻度线
                    show:false
                },
                axisLine:{ //y轴
                    show:false
                },
                //显示虚线分割线
                splitLine: {
                    show: true,
                    lineStyle:{
                        type:'dashed'
                    }
                }
            },

        ],
        series: [
            {
                name: '报名人数',
                type: 'bar',
                barWidth:20,
                barGap: 0,
                //柱状图的颜色
                itemStyle:{
                    normal:{
                        color:' #1A90FD ',
                        barBorderRadius:  [ 4, 4, 0, 0]
                    }
                },
                data: [<?php  echo $userCountText; ?>]
            },
            {
                name: '上传数量',
                type: 'bar',
                barWidth:20,
                barGap: 0,
                //柱状图的颜色
                stack: '广告',
                itemStyle:{
                    normal:{
                        color:'#35BFB2',
                        barBorderRadius:  [ 4, 4, 0, 0]
                    }
                },
                data: [<?php echo $postCountText;?>]
            },

        ]
    };
    var options2 = {
        tooltip: {
            trigger: 'item',
            formatter: '{a} <br/>{b}: {c} ({d}%)'
        },
        legend: {
            orient: 'vertical',
            top:'center',
            textStyle: {
                // color: '#ccc'
                lineHeight:20,
            },
            right:20,
            icon:'circle',
            itemWidth:12,
            itemHeight:12,
            data: ['文体会:50%(1000人)', '俱乐部:25%(500人)', '健步走:25%(500人)']
        },
        series: [
            {
                name: '活动类型',
                type: 'pie',
                radius: ['45%', '55%'],
                center: ["25%", "50%"],
                avoidLabelOverlap: false,

                label: {
                    show: false,
                    position: 'center',
                },
                emphasis: {
                    label: {
                        show: false,
                        fontSize: '15',
                        fontWeight: 'bold'
                    }
                },
                labelLine: {
                    show: false
                },

                data: [
                    {value: 50, name: '文体会:50%(1000人)',itemStyle: {color: '#0486FE'}},
                    {value: 25, name: '俱乐部:25%(500人)',itemStyle: {color: '#35BFB2'}},
                    {value: 25, name: '健步走:25%(500人)',itemStyle: {color: '#DDDDDD'}}
                ]
            }
        ]
    };
    //部门占比排行榜
    <?php
    foreach($StepsStatList['List'] as $key => $value)
    { ?>
        var Chart<?php echo $key?> = echarts.init(document.getElementById('pcm_cal<?php echo "_".$key?>'));
        var	options<?php echo $key;?> = {
            title:{
                show:true,
                x:'center',
                y:'center',

            },
            tooltip: {
                trigger: 'item',
                formatter: "{d}%",
                show:false
            },
            legend: {
                orient: 'vertical',
                x: 'left',
                show:false
            },
            series:
                {
                    name:'',
                    type:'pie',
                    radius: ['65%', '85%'],
                    avoidLabelOverlap: true,
                    hoverAnimation:false,
                    label: {
                        normal: {
                            show: false,
                            position: 'center'
                        },
                        emphasis: {
                            show: false
                        }
                    },
                    labelLine: {
                        normal: {
                            show: false
                        }
                    },
                    data:[
                        {value:<?php echo intval($value['circle_rate']);?>, name:'',itemStyle: {color: '#0486FE'}},
                        {value:100-<?php echo intval($value['circle_rate']);?>, name:'',itemStyle: {color: '#DDDDDD'}}
                    ]
                }
        };
        Chart<?php echo $key?>.setOption(options<?php echo $key;?>);

        <?php } ?>


    myChart.setOption(options);
    myChart2.setOption(options2);
</script>
</html>

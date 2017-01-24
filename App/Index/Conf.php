<?php
/**
 * @Copyright (C) 2016.
 * @Description Conf
 * @FileName Conf.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
use \Libs\Frame\Conf;
//模块配置
Conf::setModule('index',                            '\App\Index\Index');                        //默认模块
Conf::setModule('yunjia',                           '\App\Index\Yunjia');                       //运价
Conf::setModule('huopan',                           '\App\Index\Huopan');                       //货盘
Conf::setModule('about',                            '\App\Index\About');                        //关于
Conf::setModule('banlie',                           '\App\Index\Banlie');                       //班列
Conf::setModule('daili',                            '\App\Index\Daili');                        //代理
Conf::setModule('kouan',                            '\App\Index\Kouan');                        //口岸
Conf::setModule('ydyl',                             '\App\Index\Ydyl');                         //一带一路
Conf::setModule('expert',                           '\App\Index\Expert');                       //专家在线
Conf::setModule('zhaobiao',                         '\App\Index\Zhaobiao');                     //招标
Conf::setModule('tools',                            '\App\Index\Tools');                        //工具
Conf::setModule('gjmy',                             '\App\Index\Gjmy');                         //国际贸易
Conf::setModule('gtb',                              '\App\Index\Gtb');                          //港通宝
Conf::setModule('yzcx',                             '\App\Index\Yzcx');                         //运踪查询

//别名配置
Conf::setAliase('index',                            'index-main');              //首页
Conf::setAliase('index-data',                       'index-data');              //数据

Conf::setAliase('tool-chuanyun',                    'tools-chuanyun');          //船运
Conf::setAliase('tool-czdm',                        'tools-czdm');              //车站代码
Conf::setAliase('tool-hangyun',                     'tools-hangyun');           //航运
Conf::setAliase('tool-jizaiyinsu',                  'tools-jizaiyinsu');        //积载因数
Conf::setAliase('tool-jizhuangxiang',               'tools-jizhuangxiang');     //集装箱参数
Conf::setAliase('tool-kouan',                       'tools-kouan');             //口岸
Conf::setAliase('tool-kouanzafei',                  'tools-kouanzafei');        //口岸杂费
Conf::setAliase('tool-youzhengquhao',               'tools-youzhengquhao');     //邮政区号

Conf::setAliase('about',                            'about-main');          //关于我们
Conf::setAliase('about-assistant',                  'about-assistant');     //多联助手
Conf::setAliase('about-careers',                    'about-careers');       //友情链接
Conf::setAliase('about-zhaopin',                    'about-zhaopin');       //招贤纳士
Conf::setAliase('about-contact',                    'about-contact');       //联系我们
Conf::setAliase('about-customer',                   'about-customer');      //客服中心
Conf::setAliase('about-help',                       'about-help');          //帮助中心
Conf::setAliase('about-open',                       'about-open');          //开放平台
Conf::setAliase('about-service',                    'about-service');       //服务协议
Conf::setAliase('about-sitemap',                    'about-sitemap');       //站点地图

Conf::setAliase('yunjia',                           'yunjia-main');         //运价
Conf::setAliase('yunjia-air',                       'yunjia-air');          //空运
Conf::setAliase('yunjia-detect',                    'yunjia-detect');       //报关报检
Conf::setAliase('yunjia-land',                      'yunjia-land');         //陆运
Conf::setAliase('yunjia-multi',                     'yunjia-multi');        //多联
Conf::setAliase('yunjia-railway',                   'yunjia-railway');      //铁运
Conf::setAliase('yunjia-sea',                       'yunjia-sea');          //海运
Conf::setAliase('yunjia-storage',                   'yunjia-storage');      //仓库

Conf::setAliase('yunjia-airview',                   'yunjia-airview');          //空运详细
Conf::setAliase('yunjia-detectview',                'yunjia-detectview');       //报关报检详细
Conf::setAliase('yunjia-landview',                  'yunjia-landview');         //陆运详细
Conf::setAliase('yunjia-multiview',                 'yunjia-multiview');        //多联详细
Conf::setAliase('yunjia-railwayview',               'yunjia-railwayview');      //铁运详细
Conf::setAliase('yunjia-seaview',                   'yunjia-seaview');          //海运详细
Conf::setAliase('yunjia-storageview',               'yunjia-storageview');      //仓库详细

Conf::setAliase('huopan',                           'huopan-main');         //货盘
Conf::setAliase('huopan-air',                       'huopan-air');          //空运
Conf::setAliase('huopan-detect',                    'huopan-detect');       //报关报检
Conf::setAliase('huopan-land',                      'huopan-land');         //陆运
Conf::setAliase('huopan-multi',                     'huopan-multi');        //多联
Conf::setAliase('huopan-railway',                   'huopan-railway');      //铁运
Conf::setAliase('huopan-sea',                       'huopan-sea');          //海运
Conf::setAliase('huopan-storage',                   'huopan-storage');      //仓库

Conf::setAliase('huopan-airview',                   'huopan-airview');          //空运详细
Conf::setAliase('huopan-detectview',                'huopan-detectview');       //报关报检详细
Conf::setAliase('huopan-landview',                  'huopan-landview');         //陆运详细
Conf::setAliase('huopan-multiview',                 'huopan-multiview');        //多联详细
Conf::setAliase('huopan-railwayview',               'huopan-railwayview');      //铁运详细
Conf::setAliase('huopan-seaview',                   'huopan-seaview');          //海运详细
Conf::setAliase('huopan-storageview',               'huopan-storageview');      //仓库详细

Conf::setAliase('banlie',                           'banlie-main');         //班列
Conf::setAliase('banlie-view',                      'banlie-view');         //班列查看

Conf::setAliase('daili',                            'daili-main');          //代理

Conf::setAliase('kouan',                            'kouan-main');          //口岸
Conf::setAliase('kouan-search',                     'kouan-search');        //口岸搜索

Conf::setAliase('ydyl',                             'ydyl-main');           //一带一路
Conf::setAliase('ydyl-slfq',                        'ydyl-slfq');           //丝路风情
Conf::setAliase('ydyl-news',                        'ydyl-news');           //资讯
Conf::setAliase('ydyl-slfqview',                    'ydyl-slfqview');       //丝路风情查看

Conf::setAliase('expert-lists',                     'expert-lists');        //专家列表
Conf::setAliase('expert-view',                      'expert-view');         //专家详情


Conf::setAliase('zhaobiao',                         'zhaobiao-main');       //招标
Conf::setAliase('zhaobiao-view',                    'zhaobiao-view');       //招标查看
Conf::setAliase('zhaobiao-download',                'zhaobiao-download');   //招标下载

Conf::setAliase('guojimaoyi',                       'gjmy-main');           //国际贸易
Conf::setAliase('gjmy-gongying',                    'gjmy-gongying');       //供应
Conf::setAliase('gjmy-qiugou',                      'gjmy-qiugou');         //求购
Conf::setAliase('gjmy-list',                        'gjmy-lists');          //列表筛选
Conf::setAliase('gjmy-view',                        'gjmy-view');           //详细
Conf::setAliase('gjmy-viewcg',                      'gjmy-viewcg');         //详细采购
Conf::setAliase('gjmy-buy',                         'gjmy-buy');            //购买
Conf::setAliase('gjmy-buypay',                      'gjmy-buypay');         //购买支付

Conf::setAliase('gtb',                              'gtb-main');            //港通宝
Conf::setAliase('gtb-fbrz',                         'gtb-fbrz');            //投资发布
Conf::setAliase('gtb-wyrz',                         'gtb-wyrz');            //我要融资
Conf::setAliase('gtb-zxtb',                         'gtb-zxtb');            //在线投保

Conf::setAliase('yzcx',                             'yzcx-main');            //运踪查询

//权限配置
/**
NULL:   任意通过
LOGIN:  只需要登录即可通过
DENY:   永远禁止通过
其他值:  按权限检查结果判定
*/
Conf::setAuth('index-main',                         'NULL');        //首页
Conf::setAuth('index-data',                         'NULL');        //数据

Conf::setAuth('tools-chuanyun',                     'NULL');        //船运
Conf::setAuth('tools-czdm',                         'NULL');        //车站代码
Conf::setAuth('tools-hangyun',                      'NULL');        //航运
Conf::setAuth('tools-jizaiyinsu',                   'NULL');        //积载因数
Conf::setAuth('tools-jizhuangxiang',                'NULL');        //集装箱参数
Conf::setAuth('tools-kouan',                        'NULL');        //口岸
Conf::setAuth('tools-kouanzafei',                   'NULL');        //口岸杂费
Conf::setAuth('tools-youzhengquhao',                'NULL');        //邮政区号

Conf::setAuth('about-main',                         'NULL');        //关于我们
Conf::setAuth('about-assistant',                    'NULL');        //多联助手
Conf::setAuth('about-careers',                      'NULL');        //友情链接
Conf::setAuth('about-zhaopin',                      'NULL');        //招贤纳士
Conf::setAuth('about-contact',                      'NULL');        //联系我们
Conf::setAuth('about-customer',                     'NULL');        //客服中心
Conf::setAuth('about-help',                         'NULL');        //帮助中心
Conf::setAuth('about-open',                         'NULL');        //开放平台
Conf::setAuth('about-service',                      'NULL');        //服务协议
Conf::setAuth('about-sitemap',                      'NULL');        //站点地图

Conf::setAuth('yunjia-main',                        'NULL');        //运价
Conf::setAuth('yunjia-air',                         'NULL');        //空运
Conf::setAuth('yunjia-detect',                      'NULL');        //报关报检
Conf::setAuth('yunjia-land',                        'NULL');        //陆运
Conf::setAuth('yunjia-multi',                       'NULL');        //多联
Conf::setAuth('yunjia-railway',                     'NULL');        //铁运
Conf::setAuth('yunjia-sea',                         'NULL');        //海运
Conf::setAuth('yunjia-storage',                     'NULL');        //仓库

Conf::setAuth('yunjia-airview',                     'NULL');        //空运详细
Conf::setAuth('yunjia-detectview',                  'NULL');        //报关报检详细
Conf::setAuth('yunjia-landview',                    'NULL');        //陆运详细
Conf::setAuth('yunjia-multiview',                   'NULL');        //多联详细
Conf::setAuth('yunjia-railwayview',                 'NULL');        //铁运详细
Conf::setAuth('yunjia-seaview',                     'NULL');        //海运详细
Conf::setAuth('yunjia-storageview',                 'NULL');        //仓库详细

Conf::setAuth('huopan-main',                        'NULL');        //货盘
Conf::setAuth('huopan-air',                         'NULL');        //空运
Conf::setAuth('huopan-detect',                      'NULL');        //报关报检
Conf::setAuth('huopan-land',                        'NULL');        //陆运
Conf::setAuth('huopan-multi',                       'NULL');        //多联
Conf::setAuth('huopan-railway',                     'NULL');        //铁运
Conf::setAuth('huopan-sea',                         'NULL');        //海运
Conf::setAuth('huopan-storage',                     'NULL');        //仓库

Conf::setAuth('huopan-airview',                     'NULL');        //空运详细
Conf::setAuth('huopan-detectview',                  'NULL');        //报关报检详细
Conf::setAuth('huopan-landview',                    'NULL');        //陆运详细
Conf::setAuth('huopan-multiview',                   'NULL');        //多联详细
Conf::setAuth('huopan-railwayview',                 'NULL');        //铁运详细
Conf::setAuth('huopan-seaview',                     'NULL');        //海运详细
Conf::setAuth('huopan-storageview',                 'NULL');        //仓库详细

Conf::setAuth('banlie-main',                        'NULL');        //班列
Conf::setAuth('banlie-view',                        'NULL');        //班列查看

Conf::setAuth('daili-main',                         'NULL');        //代理

Conf::setAuth('kouan-main',                         'NULL');        //口岸
Conf::setAuth('kouan-search',                       'NULL');        //口岸搜索

Conf::setAuth('ydyl-main',                          'NULL');        //一带一路
Conf::setAuth('ydyl-slfq',                          'NULL');        //丝路风情
Conf::setAuth('ydyl-news',                          'NULL');        //动态
Conf::setAuth('ydyl-slfqview',                      'NULL');        //丝路风情查看

Conf::setAuth('expert-lists',                       'NULL');        //专家列表
Conf::setAuth('expert-view',                        'NULL');        //专家详情

Conf::setAuth('zhaobiao-main',                      'NULL');        //招标
Conf::setAuth('zhaobiao-view',                      'NULL');        //招标查看
Conf::setAuth('zhaobiao-download',                  'NULL');        //招标下载

Conf::setAuth('gjmy-main',                          'NULL');        //国际贸易
Conf::setAuth('gjmy-gongying',                      'NULL');        //供应
Conf::setAuth('gjmy-qiugou',                        'NULL');        //求购
Conf::setAuth('gjmy-lists',                         'NULL');        //列表筛选
Conf::setAuth('gjmy-view',                          'NULL');        //详细
Conf::setAuth('gjmy-viewcg',                        'NULL');        //详细采购
Conf::setAuth('gjmy-buy',                           'NULL');        //购买
Conf::setAuth('gjmy-buypay',                        'NULL');        //购买支付

Conf::setAuth('gtb-main',                           'NULL');        //港通宝
Conf::setAuth('gtb-fbrz',                           'NULL');        //投资发布
Conf::setAuth('gtb-wyrz',                           'NULL');        //我要融资
Conf::setAuth('gtb-zxtb',                           'NULL');        //在线投保

Conf::setAuth('yzcx-main',                          'NULL');        //运踪查询


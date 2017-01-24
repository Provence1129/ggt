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
Conf::setModule('ent',                             '\App\Ent\Ent');         //企业网站模块

//别名配置
Conf::setAliase('ent',                              'ent-main');            //企业首页
Conf::setAliase('ent-about',                        'ent-about');           //企业介绍
Conf::setAliase('ent-case',                         'ent-case');            //企业案例
Conf::setAliase('ent-caseview',                     'ent-caseView');         //企业案例详细
Conf::setAliase('ent-contact',                      'ent-contact');         //联系我们
Conf::setAliase('ent-honor',                        'ent-honor');           //企业荣誉
Conf::setAliase('ent-news',                         'ent-news');            //资讯新闻
Conf::setAliase('ent-newsview',                     'ent-newsView');        //资讯新闻详细
Conf::setAliase('ent-gjmy',                         'ent-gjmy');            //国际贸易
Conf::setAliase('ent-huopan',                       'ent-huopan');          //货盘
Conf::setAliase('ent-xkjs',                         'ent-xkjs');            //箱卡集市
Conf::setAliase('ent-yunjia',                       'ent-yunjia');          //运价

//权限配置
Conf::setAuth('ent-main',                           'NULL');                //企业首页
Conf::setAuth('ent-about',                          'NULL');                //企业介绍
Conf::setAuth('ent-case',                           'NULL');                //企业案例
Conf::setAuth('ent-caseView',                       'NULL');                //企业案例详细
Conf::setAuth('ent-contact',                        'NULL');                //联系我们
Conf::setAuth('ent-honor',                          'NULL');                //企业荣誉
Conf::setAuth('ent-news',                           'NULL');                //资讯新闻
Conf::setAuth('ent-newsView',                       'NULL');                //资讯新闻详细
Conf::setAuth('ent-gjmy',                           'NULL');                //国际贸易
Conf::setAuth('ent-huopan',                         'NULL');                //货盘
Conf::setAuth('ent-xkjs',                           'NULL');                //箱卡集市
Conf::setAuth('ent-yunjia',                         'NULL');                //运价


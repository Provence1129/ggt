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
Conf::setModule('article',                          '\App\Article\Article');        //默认模块

//别名配置
Conf::setAliase('news',                             'article-index');               // 首页
Conf::setAliase('lists',                            'article-lists');               // 列表
Conf::setAliase('detail',                           'article-detail');              // 详情
//权限配置
Conf::setAuth('article-index',                      'NULL');
Conf::setAuth('article-lists',                      'NULL');
Conf::setAuth('article-detail',                     'NULL');

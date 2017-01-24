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
Conf::setModule('Kouan',                           '\App\Kouan\Kouan');          //默认模块

//别名配置
Conf::setAliase('kouan',                      'Kouan-main');             //口岸信息列表
Conf::setAliase('kouan-search',                      'Kouan-search');             //口岸信息搜索页
Conf::setAliase('kouan-view',                      'Kouan-view');             //口岸信息详情页

//权限配置
Conf::setAuth('Kouan-main',                        'NULL');                    //口岸信息列表
//权限配置
Conf::setAuth('Kouan-search',                        'NULL');                    //口岸列表
//权限配置
Conf::setAuth('Kouan-view',                        'NULL');                    //口岸信息详情

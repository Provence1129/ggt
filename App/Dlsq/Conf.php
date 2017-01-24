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
Conf::setModule('Dlsq',                           '\App\Dlsq\Dlsq');          //默认模块

//别名配置
Conf::setAliase('dlsq',                      'Dlsq-main');             //口岸信息列表
Conf::setAliase('dlsq-view',                      'Dlsq-view');             //口岸信息详情页

//权限配置
Conf::setAuth('Dlsq-main',                        'NULL');                    //口岸信息列表
//权限配置
Conf::setAuth('Dlsq-view',                        'NULL');                    //口岸信息详情

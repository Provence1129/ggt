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
Conf::setModule('Zhidao',                           '\App\Zhidao\Zhidao');          //默认模块

//别名配置
Conf::setAliase('zhidao',                           'Zhidao-main');             //知道
Conf::setAliase('zhidao-lists',                     'Zhidao-lists');             //知道
Conf::setAliase('zhidao-view',                      'Zhidao-view');             //知道

//权限配置
Conf::setAuth('Zhidao-main',                        'NULL');           //知道
Conf::setAuth('Zhidao-lists',                       'NULL');           //知道
Conf::setAuth('Zhidao-view',                        'NULL');           //知道

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
Conf::setModule('Daili',                           '\App\Daili\Daili');          //默认模块

//别名配置
Conf::setAliase('daili',                      'Daili-main');             //国外代理列表

//权限配置
Conf::setAuth('Daili-main',                        'NULL');                    //国外代理列表

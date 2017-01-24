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
Conf::setModule('Banlie',                           '\App\Banlie\Banlie');          //默认模块

//别名配置
Conf::setAliase('banlie',                      'Banlie-main');             //班列信息列表
Conf::setAliase('banlie-view',                       'Banlie-view');              //班列信息详细

//权限配置
Conf::setAuth('Banlie-main',                        'NULL');                    //班列信息列表
Conf::setAuth('Banlie-view',                         'NULL');                    //班列信息详细

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
Conf::setModule('renwu',                           '\App\Renwu\Renwu');          //默认模块

//别名配置
Conf::setAliase('renwu',                            'renwu-main');                  //人物首页
Conf::setAliase('renwu-lists',                      'renwu-lists');                  //人物首页
Conf::setAliase('renwu-jingying',                   'renwu-jingying');                  //人物首页

//权限配置
Conf::setAuth('renwu-main',                         'NULL');
Conf::setAuth('renwu-lists',                       'NULL');                  //人物首页
Conf::setAuth('renwu-jingying',                   'NULL');                  //人物首页

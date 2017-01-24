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
Conf::setModule('Baike',                           '\App\Baike\Baike');          //默认模块

//别名配置
Conf::setAliase('baike',                            'Baike-main');              //百科首页
Conf::setAliase('baike_lists',                      'Baike-lists');             //百科列表
Conf::setAliase('baike_view',                       'Baike-view');              //百科详细

//权限配置
Conf::setAuth('Baike-main',                         'NULL');                    //百科
Conf::setAuth('Baike-lists',                        'NULL');                    //百科列表
Conf::setAuth('Baike-view',                         'NULL');                    //百科详细

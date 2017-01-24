<?php
/**
 * @Copyright (C) 2016.
 * @Description customer
 * @FileName customer.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
use \Libs\Load;
require dirname(dirname(__FILE__)).'/Libs/Load.php';
Load::conf(dirname(dirname(__FILE__)).'/Libs/Config.php');
if(strlen(trim($_GET['A'] ?? '')) < 1) $_GET['A'] = 'about-customer';
Load::run();
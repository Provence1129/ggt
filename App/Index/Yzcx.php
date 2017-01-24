<?php
/**
 * @Copyright (C) 2016.
 * @Description Yzcx
 * @FileName Yzcx.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Index;
use Libs\Comm\From;
use \Libs\Frame\Action;
use \Libs\Frame\Conf;
use \Libs\Tag\Db;
class Yzcx extends Action{
    //配置
    public function conf(){
        $Tpl = $this -> getTpl();
        $Tpl->assign('modelName', '运踪查询');
    }

    //MAIN
    public function main(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('Yzcx/index.html');
    }
}
<?php
/**
 * @Copyright (C) 2016.
 * @Description Kouan
 * @FileName Kouan.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Index;
use \Libs\Frame\Action;
class Kouan extends Action{
    //配置
    public function conf(){
    }

    //Main
    public function main(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('Kouan/index.html');
    }

    //search
    public function search(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('Kouan/search.html');
    }
}
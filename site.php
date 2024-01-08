<?php

use Hcode\Page;
use Hcode\Model\Product;

$app->get('/', function () {

    $products = Product::listAll();

    $page = new Page();
    $page->setTpl("index",[
        'products'=>Product::checkList($products)
    ]);

    //$sql = new Hcode\DB\Sql();
    //$results = $sql->select("Select * from tb_users");
    //echo json_encode($results);
});


?>

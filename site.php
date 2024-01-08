<?php

use Hcode\Page;
use Hcode\Model\Product;
use Hcode\Model\Category;

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

$app->get("/categories/:idcategory", function ($idcategory) {


    $category = new Category();
    $category->get((int) $idcategory);

    $page = new Page();
    $page->setTpl("category", [
        'category' => $category->getValues(),
        'products' => Product::checkList($category->getProducts())
    ]);

});


?>

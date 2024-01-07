<?php

use Hcode\Page;

$app->get('/', function () {

    $page = new Page();
    $page->setTpl("index");

    //$sql = new Hcode\DB\Sql();
    //$results = $sql->select("Select * from tb_users");
    //echo json_encode($results);
});


?>

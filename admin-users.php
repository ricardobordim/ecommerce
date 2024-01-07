<?php

use Hcode\PageAdmin;
use Hcode\Model\User;

$app->get("/admin/users", function () {
    User::verifyLogin();

    $users = User::listAll();

    $page = new PageAdmin();
    $page->setTpl("users", array(
        "users" => $users,
    ));

});

$app->get("/admin/users/create", function () {
    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("users-create");

});

$app->get("/admin/users/:iduser/delete", function ($iduser) {
    User::verifyLogin();
    $user = new User();

    $user->get((int) $iduser);
    $user->delete();

    header("Location: /admin/users");
    exit;

});

//Só o fato de colocar como parâmetro obrigatório de rota já entende que a função vai receber por parâmetro
$app->get("/admin/users/:iduser", function ($iduser) {
    User::verifyLogin();

    $user = new User();

    $user->get((int) $iduser);

    $page = new PageAdmin();
    $page->setTpl("users-update", array(
        "user" => $user->getValues(),
    ));

});

//Se acessar via Get vem o HTML e se vier via Post será enviado para o banco e salvar no banco
$app->post("/admin/users/create", function () {
    User::verifyLogin();
    // var_dump($_POST);
    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

    $user->setData($_POST);

    $user->save();

    header("Location: /admin/users");
    exit;

    //var_dump($user);

});

$app->post("/admin/users/:iduser", function ($iduser) {
    User::verifyLogin();
    $user = new User();
    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

    $user->get((int) $iduser);
    $user->setData($_POST);
    $user->update();
    header("Location: /admin/users");
    exit;

});

?>

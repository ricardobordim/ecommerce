<?php

session_start();
require_once "vendor/autoload.php";

use \Hcode\Model\Category;
use \Hcode\Model\User;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Slim\Slim;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function () {

    $page = new Page();
    $page->setTpl("index");

    //$sql = new Hcode\DB\Sql();
    //$results = $sql->select("Select * from tb_users");
    //echo json_encode($results);
});

$app->get('/admin', function () {

    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("index");

    //$sql = new Hcode\DB\Sql();
    //$results = $sql->select("Select * from tb_users");
    //echo json_encode($results);
});

$app->get('/admin/login', function () {

    $page = new PageAdmin([
        "header" => false,
        "footer" => false,
    ]);
    $page->setTpl("login");

});

$app->post('/admin/login', function () {

    User::login($_POST['login'], $_POST['password']);
    header("Location: /admin");
    exit;

});

$app->get('/admin/logout', function () {

    User::logout();
    header("Location: /admin/login");
    exit;

});

$app->get("/admin/users", function () {
    User::verifyLogin();

    $users = User::listAll();

    $page = new PageAdmin();
    $page->setTpl("users",array(
        "users"=>$users
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

    $user->get((int)$iduser);
    $user->delete();
    
    header("Location: /admin/users");
    exit;

  
});

//Só o fato de colocar como parâmetro obrigatório de rota já entende que a função vai receber por parâmetro
$app->get("/admin/users/:iduser", function($iduser) {
    User::verifyLogin();

    $user = new User();

    $user->get((int)$iduser);

    $page = new PageAdmin();
    $page->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));

});

//Se acessar via Get vem o HTML e se vier via Post será enviado para o banco e salvar no banco
$app->post("/admin/users/create", function () {
    User::verifyLogin();
   // var_dump($_POST);
    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

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

    $user->get((int)$iduser);
    $user->setData($_POST);
    $user->update();
    header("Location: /admin/users");
    exit;


});

$app->get("/admin/forgot", function(){
$page = new PageAdmin([
    "header" => false,
    "footer" => false,
    ]);
    $page->setTpl("forgot");
});
$app->post("/admin/forgot",function(){
    
    $user=User::getForgot($_POST["email"]);
    header("Location: /admin/forgot/sent");
    exit;
});

$app->get("/admin/forgot/sent",function()
{
    $page = new PageAdmin([
    "header" => false,
    "footer" => false,
]);
    $page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset",function(){
    
    $user = User::validForgotDecrypt($_GET["code"]);
    $page = new PageAdmin([
        "header" => false,
        "footer" => false,
    ]);
    $page->setTpl("forgot-reset", array(
        "name"=>$user["desperson"],
        "code"=>$_GET["code"]
    ));

});


$app->post("/admin/forgot/reset",function(){
    $forgot = User::validForgotDecrypt($_GET["code"]);
    User::setForgotUsed($forgot["idrecovery"]);
    $user = new User();
    $user->get((int)$forgot["iduser"]);

    $password = password_hash($_POST["password"],PASSWORD_DEFAULT,[
            "cost"=>12
    ]);
    $user->setPassword($_POST["password"]);

    
    $page = new PageAdmin([
    "header" => false,
    "footer" => false,
    ]);
    $page->setTpl("forgot-reset-success");

});

$app->get("/admin/categories",function()
{
    User::verifyLogin();

    $categories = Category::listAll();
    $page = new PageAdmin();
    $page->setTpl("categories",[
        'categories'=>$categories
    ]);

});

$app->get("/admin/categories/create", function () {

    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("categories-create");

});

$app->post("/admin/categories/create", function () {

    User::verifyLogin();

    $category = new Category();
    $category->setData($_POST);
    $category->save();

    header("Location: /admin/categories");
    exit;

  

});


$app->get("/admin/categories/:idcategory/delete",function($idcategory)
{
    $category = new Category();
    $category->get((int)$idcategory);
    $category->delete();

    header("Location: /admin/categories");
    exit;
});


$app->get("/admin/categories/:idcategory", function ($idcategory) {
    
    $category = new Category();
    $category->get((int) $idcategory);

    $page = new PageAdmin();
    $page->setTpl("categories-update",[
        'category'=>$category->getValues()
    ]);
});


$app->post("/admin/categories/:idcategory", function ($idcategory) {
    
    $category = new Category();
    $category->get((int) $idcategory);

    $category->setData($_POST);

    $category->save();
    header("Location: /admin/categories");
    exit;
});


$app->get("/categories/:idcategory", function ($idcategory) {

    $category = new Category();
    $category->get((int) $idcategory);

    $page = new Page();
    $page->setTpl("category",[
        'category'=>$category->getValues(),
        'products'=>[]
    ]);

});



$app->run();

?>

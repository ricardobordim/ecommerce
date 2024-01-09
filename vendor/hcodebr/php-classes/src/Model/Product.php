<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Product extends Model
{

    public static function listAll()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

    }

    public static function checkList($list)
    {
        foreach ($list as &$row) {
            $p = new Product();
            $p->setData($row);
            $row = $p->getValues();
        }
        return $list;

    }
    

    public function save()
    {

        $sql = new Sql();

        $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct" => $this->getidproduct(),
            ":desproduct" => $this->getdesproduct(),
            ":vlprice" => $this->getvlprice(),
            ":vlwidth" => $this->getvlwidth(),
            ":vlheight" => $this->getvlheight(),
            ":vllength" => $this->getvllength(),
            ":vlweight" => $this->getvlweight(),
            ":desurl" => $this->getdesurl()               
        ));

        $this->setData($results[0]);

    }

    public function get($idproduct)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct' => $idproduct,
        ]);

        $this->setData($results[0]);

    }

    public function delete()
    {

        $sql = new Sql();

        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct' => $this->getidproduct(),
        ]);

  
    }


    public function getProducts($related = true)
    {

        $sql = new Sql();

        if ($related === true) {

            return $sql->select("
				SELECT * FROM tb_products WHERE idproduct IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				);
			", [
                ':idcategory' => $this->getidcategory(),
            ]);

        } else {

            return $sql->select("
				SELECT * FROM tb_products WHERE idproduct NOT IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				);
			", [
                ':idcategory' => $this->getidcategory(),
            ]);

        }

    }

    public function getProductsPage($page = 1, $itemsPerPage = 8)
    {

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products a
			INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
			INNER JOIN tb_categories c ON c.idcategory = b.idcategory
			WHERE c.idcategory = :idcategory
			LIMIT $start, $itemsPerPage;
		", [
            ':idcategory' => $this->getidcategory(),
        ]);

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            'data' => Product::checkList($results),
            'total' => (int) $resultTotal[0]["nrtotal"],
            'pages' => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage),
        ];

    }

    public function addProduct(Product $product)
    {

        $sql = new Sql();

        $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)", [
            ':idcategory' => $this->getidcategory(),
            ':idproduct' => $product->getidproduct(),
        ]);

    }

    public function removeProduct(Product $product)
    {

        $sql = new Sql();

        $sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [
            ':idcategory' => $this->getidcategory(),
            ':idproduct' => $product->getidproduct(),
        ]);

    }

    public static function getPage($page = 1, $itemsPerPage = 10)
    {

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories
			ORDER BY descategory
			LIMIT $start, $itemsPerPage;
		");

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            'data' => $results,
            'total' => (int) $resultTotal[0]["nrtotal"],
            'pages' => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage),
        ];

    }

    public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
    {

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories
			WHERE descategory LIKE :search
			ORDER BY descategory
			LIMIT $start, $itemsPerPage;
		", [
            ':search' => '%' . $search . '%',
        ]);

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            'data' => $results,
            'total' => (int) $resultTotal[0]["nrtotal"],
            'pages' => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage),
        ];

    }
    public function checkPhoto()
    {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
        "res" . DIRECTORY_SEPARATOR . 
        "site" . DIRECTORY_SEPARATOR . 
        "img" . DIRECTORY_SEPARATOR . 
        "products" . DIRECTORY_SEPARATOR .
        $this->getidproduct() . ".jpg" ))
        {
            $url =  "/res/site/img/products/" . $this->getidproduct() . ".jpg";
        }else {
            $url =  "/res/site/img/product.jpg";

        }
        return $this->setdesphoto($url);
    
    }

    //reescrevendo a classe getValues()

    public function getValues()
    {
        $this->checkPhoto();
        $values = parent::getValues();
        return $values;
    }

    public function setPhoto($file)
    {
        $extension = explode('.',$file['name']);
        $extension = end($extension);

        switch ($extension)
        {
            case "jpg":
            case "jpeg":
                $image = imagecreatefromjpeg($file["tmp_name"]);
            break;
            case "gif":
                $image = imagecreatefromgif($file['tmp_name']);
            break;
            case "png":
                $image = imagecreatefrompng($file["tmp_name"]);
            break;
        }
        $dist = ($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
        "res" . DIRECTORY_SEPARATOR . 
        "site" . DIRECTORY_SEPARATOR . 
        "img" . DIRECTORY_SEPARATOR . 
        "products" . DIRECTORY_SEPARATOR .
        $this->getidproduct() . ".jpg" );

        imagejpeg($image,$dist);

        imagedestroy($image);

        $this->checkPhoto();


    }
   
    public function  getFromURL($desurl){
        $sql = new Sql();
        $rows= $sql->select("select * from tb_products where desurl = :desurl limit 1",[
            ':desurl'=>$desurl
            ]);
        $this->setData($rows[0]);

    }
    public function getCategories()
    {
        $sql = new Sql();
        return $sql->select(" select * from tb_categories a inner join tb_productscategories b on a.idcategory = b.idcategory where b.idproduct= :idproduct",[
            ':idproduct'=>$this->getidproduct()
        ]);
    }

}

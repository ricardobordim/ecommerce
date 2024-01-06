<?php
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model
{
    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";
    const SECRET_IV = "HcodePhp7_Secret_IV";
    
    public static function login($login, $password)
    {
        $sql = new Sql();
        $results = $sql->select("select * from tb_users where deslogin = :LOGIN", array(
            ":LOGIN" => $login,
        ));
        if (count($results) === 0) {
            throw new \Exception("Usuário inexiste ou senha inválida.");
            // a Contrabarra acima é para pegar a exceção principal

        }
        $data =  $results[0];
        //Confere os Hash
        if (password_verify($password,$data["despassword"]) ===true)
        {
            $user = new User();
            $user->setData($data);
            //var_dump($user);
            //exit;
            //Precisa criar uma sessão quando tem login para manter conectado ou redirecionar
            $_SESSION[User::SESSION]= $user->getValues();
            return $user;
        }else {
            throw new \Exception("Usuário inexiste ou senha inválida.");

        }


    }
    public static function verifyLogin($inadmin=true){
       // var_dump($inadmin);
  
        if (!isset($_SESSION[User::SESSION]) || 
            !$_SESSION[User::SESSION]  || 
            !(int)$_SESSION[User::SESSION]["iduser"]>0 || 
            (bool)$_SESSION[User::SESSION]["inadmin"] !==$inadmin){

               
            header("Location: /admin/login");


            exit;
        }
    }
    public static function logout()
    {
        $_SESSION[User::SESSION]=NULL;
    }

    public static function listAll(){
       $sql = new Sql();
       return $sql->select("select * from tb_users a inner join tb_persons b using(idperson) order by b.desperson");
    }
    public function save()
    {
        $sql= new Sql();
        //var_dump($this->getdesperson(),$this->getdeslogin(),$this->getdespassword(),$this->getdesemail(),$this->getnrphone(),$this->getinadmin());

        $results = $sql->select("call sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));
        $this->setData($results[0]);
    }
    public function get($iduser)
    {
        $sql = new Sql();
        $results = $sql->select("select * from tb_users a inner join tb_persons b using(idperson) where a.iduser = :iduser",array(
            ":iduser"=>$iduser
        ));
        $this->setData($results[0]);
    }
    public function update()
    {
        $sql = new Sql();
        //var_dump($this->getdesperson(),$this->getdeslogin(),$this->getdespassword(),$this->getdesemail(),$this->getnrphone(),$this->getinadmin());

        $results = $sql->select("call sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            ":desperson" => $this->getdesperson(),
            ":deslogin" => $this->getdeslogin(),
            ":despassword" => $this->getdespassword(),
            ":desemail" => $this->getdesemail(),
            ":nrphone" => $this->getnrphone(),
            ":inadmin" => $this->getinadmin(),
        ));
        $this->setData($results[0]);


    }
    public function delete()
    {
        $sql = new Sql();
        $sql->query("call sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }
    public static function getForgot($email)
    {
        $sql = new Sql();
        $results = $sql->select("select * from tb_persons a inner join tb_users b using(idperson) where a.desemail = :email;",array(
            ":email"=>$email
        ));
        if (count($results)===0)
        {
            throw new \Exception("Não foi possível recuperar a senha.");
        }
        else 
        {
            $data= $results[0];
            $results2 = $sql->select("Call sp_userspasswordsrecoveries_create(:iduser, :desip", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            )); 
            if (count($results2)===0)
            {
                throw new \Exception("Não foi possível recuperar a senha.");               
            }
            else {
                $dataRecovery = $results2[0];
                $code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

                $code = base64_encode($code);

                if ($inadmin === true) {

                    $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

                } else {

                    $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";

                }

                $mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Hcode Store", "forgot", array(
                    "name" => $data['desperson'],
                    "link" => $link,
                ));

                $mailer->send();

                return $link;
                                


            }
        }
    }
    public static function validForgotDecrypt($code)
	{

		$code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
			":idrecovery"=>$idrecovery
		));

		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{

			return $results[0];

		}

	}
    public static function setForgotUsed($idrecovery)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));

	}
    	public function setPassword($password)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));

	}




}

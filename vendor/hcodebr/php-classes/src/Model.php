<?php
namespace Hcode;

class Model
{
    private $values = [];

    public function __call($name, $args)
    {
        $method = substr($name, 0, 3);
        $fieldname = substr($name, 3, strlen($name));

        //var_dump($method . '  ' . $fieldname);
        //exit;

        switch ($method) {
            case 'get':
                return $this->values[$fieldname];
                break;
            case 'set':
                $this->values[$fieldname] = $args[0];
                break;
        }

    }
    public function setData($data= array())
    {
        foreach ($data as $key => $value) {
            //Para criar dinâmico precisa das chaves, não dá certo fazer $this->"set".$key
            $this->{"set".$key}($value);
        }
    }
    public function getValues(){
        return $this->values;
    }
}

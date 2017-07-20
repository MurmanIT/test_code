<?php

class Model
{

    private $_db;

    public function __construct($db)
    {
        $this->_db = $db;
    }

    public function auth($username='',$password=''){
        $sql = 'select getUserId(:username,:password) as id';
        $sth = $this->_db->prepare($sql);
        $sth->bindParam(':username',$username,PDO::PARAM_STR);
        $sth->bindParam(':password',$password,PDO::PARAM_STR);
        $sth->execute();
        $res = $sth->fetch()['id'];
        if (!$res) { throw new Exception('Нет такого пользователя'); }
        return $this->encrypt((string)$res);
    }

    public function costs($user_id=0,$money=0,$percentage=0){
        if (preg_match('/[^0-9]+$/', $money)) throw new Exception('Это не число!');
        if (preg_match('/[^0-9]+$/', $percentage)) throw new Exception('Это не процент!');
        try{
            $this->_db->exec('LOCK TABLES costs_users WRITE');
            $sth = $this->_db->prepare("call add_cost(:user_id,:income,:percetage)");
            $sth->bindValue(':user_id', $this->decrypt($user_id), PDO::PARAM_INT);
            $sth->bindValue(':income', $money);
            $sth->bindValue(':percetage', $percentage);
            $sth->execute();
            $this->_db->exec('UNLOCK TABLES');
//            if (is_array($err))  { throw new Exception($err['error']); }
        }catch (Exception $e){
            throw  $e;
        }

    }


    public function income($user_id=0,$money=0){
        try{
            if (preg_match('/[^0-9]+$/', $money)) throw new Exception('Это не число!');
            $sth = $this->_db->prepare("call add_income(:user_id,:income)");
            $sth->bindValue(':user_id', $this->decrypt($user_id), PDO::PARAM_INT);
            $sth->bindValue(':income', $money);
            $sth->execute();
        }catch (Exception $e){
            print_r($e);
            throw  $e;
        }
    }

    public function getIncome($user_id=0){
        $sth = $this->_db->prepare("select getIncome(:user_id) as income");
        $sth->bindValue(':user_id',$this->decrypt($user_id),PDO::PARAM_INT);
        $sth->execute();
        $balans = $sth->fetch()['income'];
        return $balans;
    }
    private function encrypt($str){
        $iv = mcrypt_create_iv(
            mcrypt_get_iv_size(MCRYPT_TWOFISH256, MCRYPT_MODE_CBC),
            MCRYPT_DEV_URANDOM
        );
        $encrypted = base64_encode(
            $iv .
            mcrypt_encrypt(
                MCRYPT_RIJNDAEL_256,
                hash('gost-crypto',KEY, true),
                $str,
                MCRYPT_MODE_CBC,
                $iv
            )
        );
        return $encrypted;
    }

    private function decrypt($str){
        $data = base64_decode($str);
        $iv = substr($data, 0, mcrypt_get_iv_size(MCRYPT_TWOFISH256, MCRYPT_MODE_CBC));

        $decrypted = rtrim(
            mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256,
                hash('gost-crypto', KEY, true),
                substr($data, mcrypt_get_iv_size(MCRYPT_TWOFISH256, MCRYPT_MODE_CBC)),
                MCRYPT_MODE_CBC,
                $iv
            ),
            "\0"
        );
        return $decrypted;
    }
}
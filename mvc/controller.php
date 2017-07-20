<?php
class BaseController
{
    protected $_db = null;
    protected $_model = null;
    protected $_views = null;

    function __construct()
    {
       $this->openConnector();
       $this->openModel();
    }
    private function openConnector(){
        try{
            $this->_db = new PDO(DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET,DB_USER,DB_PASS,
                array(
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
                ));
        } catch (PDOException $e){
            echo $e->getMessage();
            die();
        }
    }
    private function openModel(){
        require APP . 'mvc/model.php';
        $this->_model = new Model($this->_db);
    }
}

class Controller extends BaseController
{
    private $url_action = null;

    function __construct()
    {
        parent::__construct();
        $this->routeUrl();
        $this->authUser();

    }

    private function routeUrl(){

        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])){ session_write_close(); }

        if (isset($_POST['action'])){
            $this->{$_POST['action']}();
        }
    }

    private function authUser(){
        include APP . 'mvc/view.php';
        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])){
            $incomde = $this->_model->getIncome($_SESSION['user_id']);
            View::viewCabinet($incomde);
        }else{
            View::viewAuth();
        }
    }

    public function auth(){
        try{
            $_SESSION['user_id'] = $this->_model->auth($_POST['username'],$_POST['password']);
            session_write_close();
            header("Location: http://" . URL_DOMAIN);
            exit();
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }

    public function costs(){
        try{
            $this->_model->costs($_SESSION['user_id'],$_POST['income'],PERCENTAGE);
            header("Location: http://" . URL_DOMAIN);
        }catch (Exception $e){
            echo $e->getMessage();
        }

    }

    public function income(){
        try{
            $this->_model->income($_SESSION['user_id'],$_POST['income']);
            header("Location: http://" . URL_DOMAIN);
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: rail
 * Date: 25.04.14
 * Time: 23:44
 */

class myDB {

    private $mysql_host = 'localhost';
    private $mysql_db = 'simple';
    private $mysql_user = 'simple';
    private $mysql_pass = 'simple';

    private $user_id = '';
    private $pdo;

    function __construct() {

        $this->pdo = new PDO(
            'mysql:host='.$this->mysql_host.';dbname='.$this->mysql_db,
            $this->mysql_user,
            $this->mysql_pass
        );

        if(isset($_SESSION['user_id'])) $this->user_id = $_SESSION['user_id'];

    }

    public static function logout() {

        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    public function log() {
        $result = $this->pdo->prepare("INSERT INTO user_logons (user_id,ip,date) VALUES (?,?,?)");
        $result->execute(array($this->user_id,$_SERVER['REMOTE_ADDR'],date('Y-m-d H:i:s')));
    }

    public function validateHash($login,$pass) {

        $result = $this->pdo->prepare('SELECT id,login,pass FROM users WHERE login = ? LIMIT 1');
        $result->bindParam(1, $login, PDO::PARAM_STR);
        $result->execute();
        $row = $result->fetch(PDO::FETCH_ASSOC);

        if(!empty($row)) {
            if(crypt($pass,$row['pass'])===$row['pass']) {
                $this->user_id = $row['id'];
                $_SESSION['user_id'] = $this->user_id;
                return true;
            }
        }
        return false;
    }

    public function validatePlain($login,$pass) {
        $result = $this->pdo->prepare('SELECT id,login,pass FROM users WHERE login = ? AND pass = ? LIMIT 1');
        $result->bindParam(1, $login, PDO::PARAM_STR);
        $result->bindParam(2, $pass, PDO::PARAM_STR);
        $result->execute();
        $row = $result->fetch(PDO::FETCH_ASSOC);

        if(!empty($row)) {
            $this->user_id = $row['id'];
            $_SESSION['user_id'] = $this->user_id;
            return true;
        }
        return false;
    }

    private function hashPassword($password)
    {
        return crypt($password, $this->blowfishSalt());
    }

    /**
     * Generate a random salt in the crypt(3) standard Blowfish format.
     *
     * @param int $cost Cost parameter from 4 to 31.
     *
     * @throws Exception on invalid cost parameter.
     * @return string A Blowfish hash salt for use in PHP's crypt()
     */
    private function blowfishSalt($cost = 13)
    {
        if (!is_numeric($cost) || $cost < 4 || $cost > 31) {
            throw new Exception("cost parameter must be between 4 and 31");
        }
        $rand = array();
        for ($i = 0; $i < 8; $i += 1) {
            $rand[] = pack('S', mt_rand(0, 0xffff));
        }
        $rand[] = substr(microtime(), 2, 6);
        $rand = sha1(implode('', $rand), true);
        $salt = '$2a$' . sprintf('%02d', $cost) . '$';
        $salt .= strtr(substr(base64_encode($rand), 0, 22), array('+' => '.'));
        return $salt;
    }

    public function addUsersHash($login,$pass) {
       $hash = $this->hashPassword($pass);
       $result = $this->pdo->prepare("INSERT INTO users (login,pass) VALUES (?,?)");
       $result->execute(array($login,$hash));
    }

    public function addUsersPlain($login,$pass) {
        $result = $this->pdo->prepare("INSERT INTO users (login,pass) VALUES (?,?)");
        $result->execute(array($login,$pass));
    }

} 
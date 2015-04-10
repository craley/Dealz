<?php

/*
 * Members: uid, username, firstName, lastName, email, hash, salt, phone, carrier, autolog
 * Products: uid, asin, title, maker, priority
 * 
 * Members uses auto-increment. Only email cant be null.
 * Autolog: 0 = No, 1 = Yes
 * Priority: Normal = 0, Email = 1, Text = 2
 */

class Database {

    public $dsn;
    public $user;
    public $pswd;
    public $host;
    public $db_name;

    public function __construct($credentials) {
        $this->dsn = $credentials['dsn'];
        $this->host = $credentials['host'];
        $this->user = $credentials['user'];
        $this->pswd = $credentials['pswd'];
        $this->db_name = $credentials['database'];
    }

    public function insertUser($username, $password, $email) {//ok
        try {
            $conn = new PDO($this->dsn, $this->user, $this->pswd);

            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //only for testing purposes.
            $salt = $this->generateRandomSalt();

            $pstmt = $conn->prepare("INSERT INTO members VALUES(NULL, :user, NULL, NULL, :email, :digest, :salt, NULL, NULL, :autolog)");

            $pstmt->bindValue(":user", $username);
            $pstmt->bindValue(":digest", md5($password . $salt));
            $pstmt->bindValue(":salt", $salt);
            $pstmt->bindValue(":email", $email);
            $pstmt->bindValue(":autolog", 0);
            $pstmt->execute();
            return $pstmt->rowCount();
        } catch (PDOException $exc) {
            echo $exc->getTraceAsString();
        }
    }
    /**
     * First name and last name are optional.
     * @param type $email
     * @param type $first
     * @param type $last
     * @return type
     */
    public function insertUserVendor($email, $first, $last){
        try {
            $conn = new PDO($this->dsn, $this->user, $this->pswd);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//testing.

            $pstmt = $conn->prepare("INSERT INTO members VALUES(NULL, :user, :first, :last, :email, NULL, NULL, NULL, NULL, :autolog)"); //auto-increment requires NULL
            $pstmt->bindValue(":user", $first . $last);
            
            if(isset($first) && !empty($first)){
                $pstmt->bindValue(":first", $first);
            } else {
                $pstmt->bindValue(":first", NULL);
            }
            if(isset($last) && !empty($last)){
                $pstmt->bindValue(":last", $last);
            } else {
                $pstmt->bindValue(":last", NULL);
            }
            
            $pstmt->bindValue(":email", $email);
            $pstmt->bindValue(":autolog", 0);
            $pstmt->execute();
            return $pstmt->rowCount();
        } catch (PDOException $exc) {
            echo $exc->getTraceAsString();
        }
    }
    /*
     * Returns -1 for invalid attempt.
     * Returns the uid for valid attempts.
     */
    public function emailExists($email){//ok
        try {
            $conn = new PDO($this->dsn, $this->user, $this->pswd);
            $sql = "SELECT uid FROM members WHERE email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(1, $email);
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                return $stmt->fetch()[0];
            }
        } catch (PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        return -1;
    }
    /*
     * Returns -1 for invalid attempt.
     * Returns the uid for valid attempts.
     */
    public function validateUser($username, $password){//ok
        try {
            $conn = new \PDO($this->dsn, $this->user, $this->pswd);
            $sql = "SELECT salt FROM members WHERE username=?";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(1, $username);
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(); //only gets 1 result. shouldnt that be associative??
                $salt = $row['salt'];
                $saltstmt = $conn->prepare("SELECT uid FROM members WHERE username=? AND hash=?");
                $saltstmt->bindValue(1, $username);
                $saltstmt->bindValue(2, md5($password . $salt));
                $saltstmt->execute();
                if ($saltstmt->rowCount() == 1) {
                    
                    return $saltstmt->fetch()[0];
                }
                //print_r($salt);
            }
        } catch (PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        return -1;
    }
    public function setUserPswd($uid, $username, $pswd){
        try {
            $conn = new PDO($this->dsn, $this->user, $this->pswd);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //only for testing purposes.

            $salt = $this->generateRandomSalt();
            $pstmt = $conn->prepare("UPDATE members SET username=:us, hash=:digest, salt=:slt WHERE uid=:uid"); //auto-increment requires NULL

            $pstmt->bindValue(":us", $username);
            $pstmt->bindValue(":digest", md5($password . $salt));
            $pstmt->bindValue(":slt", $salt);
            $pstmt->bindValue(":uid", $uid);
            $pstmt->execute();
            return $pstmt->rowCount();
        } catch (PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        return -1;
    }
    public function setFirstLast($uid, $first, $last){//ok
        try {
            $conn = new PDO($this->dsn, $this->user, $this->pswd);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //only for testing purposes.

            $pstmt = $conn->prepare("UPDATE members SET firstName=:first, lastName=:last WHERE uid=:uid"); //auto-increment requires NULL

            $pstmt->bindValue(":first", $first);
            $pstmt->bindValue(":last", $last);
            $pstmt->bindValue(":uid", $uid);
            $pstmt->execute();
            return $pstmt->rowCount();
        } catch (PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        return -1;
    }
    public function setPhone($uid, $phone, $carrier){//ok
        try {
            $conn = new PDO($this->dsn, $this->user, $this->pswd);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //only for testing purposes.

            $pstmt = $conn->prepare("UPDATE members SET phone=:phone, carrier=:carrier WHERE uid=:uid"); //auto-increment requires NULL

            $pstmt->bindValue(":phone", $phone);
            $pstmt->bindValue(":carrier", $carrier);
            $pstmt->bindValue(":uid", $uid);
            $pstmt->execute();
            return $pstmt->rowCount();
        } catch (PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        return -1;
    }

    //Data Export
    public function getUserByCredentials($username, $pswd){
        
    }
    public function getUser($uid){//ok
        try {
            $conn = new \PDO($this->dsn, $this->user, $this->pswd);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //disable after testing

            $query = "SELECT username, firstName, lastName, email, phone, carrier, autolog "
                    . "FROM members "
                    . "WHERE uid=$uid ";

            $result = $conn->query($query);
            if ($result->rowCount() == 1) {
                $row = $result->fetch(\PDO::FETCH_ASSOC);
                $profile = [
                    'username' => $row['username'], 
                    'firstName' => $row['firstName'], 
                    'lastName' => $row['lastName'], 
                    'email' => $row['email'],
                    'phone' => $row['phone'], 
                    'carrier' => $row['carrier'], 
                    'autolog' => $row['autolog'], 
                ];
                return $profile;
            }
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        return null;
    }
    
    //Product Interface
    public function insertProduct($uid, $asin, $title, $maker, $priority = 0){
        try {
            $conn = new PDO($this->dsn, $this->user, $this->pswd);

            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $pstmt = $conn->prepare("INSERT INTO products VALUES(:uid, :asin, :title, :maker, :priority)");

            $pstmt->bindValue(":uid", $uid);
            $pstmt->bindValue(":asin", $asin);
            $pstmt->bindValue(":title", $title);
            $pstmt->bindValue(":maker", $maker);
            $pstmt->bindValue(":priority", $priority);
            $pstmt->execute();
            return $pstmt->rowCount();
        } catch (PDOException $exc) {
            echo $exc->getTraceAsString();
        }
    }
    public function removeProduct($uid, $asin){
        try {
            $conn = new PDO($this->dsn, $this->user, $this->pswd);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $pstmt = $conn->prepare("DELETE FROM products WHERE uid=:uid and asin=:asin");

            $pstmt->bindValue(":uid", $uid);
            $pstmt->bindValue(":asin", $asin);
            $pstmt->execute();
            return $pstmt->rowCount();
        } catch (PDOException $exc) {
            echo $exc->getTraceAsString();
        }
    }
    public function getProducts($uid){
        try {
            $conn = new \PDO($this->dsn, $this->user, $this->pswd);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //disable after testing

            $query = "SELECT asin, title, maker, priority "
                    . "FROM products "
                    . "WHERE uid=$uid ";

            $result = $conn->query($query);
            if ($result->rowCount() > 0) {
                
                $prods = array();
                while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                    array_push($prods, ['asin' => $row['asin'], 'title' => $row['title'], 'maker' => $row['maker'], 'priority' => $row['priority']]);
                }
                return $prods;
            }
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        return null;
    }

    //Utilities
    public static function generateRandomSalt() {
        return base64_encode(mcrypt_create_iv(12, MCRYPT_DEV_URANDOM)); //apache mcrypt mod enabled.
    }
}

//testing
//$config = json_decode(file_get_contents('../app/config.json'));
//$credents = [
//    'dsn' => $config->test_db_dsn, 
//    'host' => $config->test_db_host,
//    'user' => $config->test_db_user,
//    'pswd' => $config->test_db_pswd,
//    'database' => $config->test_db_name 
//];
//$db = new Database($credents);
////$db->insertUser('bob', '3222', 'bob@gmail.com');
////$res = $db->emailExists('----');
////$res = $db->validateUser('bob', '3222');
////$res = $db->setFirstLast(1, 'bob', 'smith');
////$res = $db->setPhone(1, '563802077', 'verizon');
//$userArray = $db->getUser(1);
//var_dump($userArray);
////echo $res;
<?php

/*
 * Must have apache mcrypt mod enabled.
 */

/**
 * Description of Database
 *
 * Default: demodb
 * 
 * @author chris
 */

namespace app\db;

class Query {

    const SELECT = 1;
    const INSERT = 2;
    const UPDATE = 3;
    const DELETE = 4;

}
/*
 * Tables:
 *  members:  uid,    username, firstName, lastName, email,  hash,  salt
 *  category: catid,  name,     pid,       level,    uid
 *  task:     taskid, name,     complete,  uid,      catid
 */

class Database {
    
    //local versions
    const DB_SERVER = "-------";
    const DB_USER = "----";
    const DB_PASSWORD = "-------";
    const DB_NAME = "-------------";
    
    const NO_OP = null;

    //start
    /*
     * Insertion Abstraction Layer
     * user/pswd: insert([ 'username' => 'bob', 'pswd' => 'blah', 'email' => 's@gm.com' ])
     * google:    insert([ 'firstName' => 'bob', 'lastName' => 'Smith', 'email' => 's@gm.com' ])
     */
    public static function insertUserNew($data){
        $username = $email = Database::NO_OP;
        $firstName = $lastName = Database::NO_OP;
        $hash = $salt = Database::NO_OP;
        if(isset($data['username']) and isset($data['pswd'])){
            $username = $data['username'];
            $salt = self::generateRandomSalt();
            $hash = md5($data['pswd'] . $salt);
        }
        if(isset($data['firstName'])) $firstName = $data['firstName'];
        if(isset($data['lastName'])) $lastName = $data['lastName'];
        if(isset($data['email'])) $email = $data['email'];
        
        if(empty($data['email'])) return;//MUST HAVE EMAIL!!
        Database::connect('demodb', 'members', Query::INSERT, 
               ['uid' => 'NULL', 'username' => $username,
                'firstName' => $firstName, 'lastName' => $lastName,
                'email' => $email, 'hash' => $hash, 'salt' => $salt]);
    }
    /*
     * select: connect('demodb', 'members', 1, [ 'username', 'email' ], [ 'uid' => 1 ], [ 'orderby' => 'uid', 'order' => 'ASC' ]
     * insert: connect('demodb', 'members', 1, [ ]
     * 
     * update: connect('demodb', 'members', 1,
     * delete: connect('demodb', 'members', 1,
     * options: LIMIT, ORDER BY, etc
     */
    public static function connect($db_name, $table, $type, $params = [], $wheres = [], $options = []) {

        try {
            $conn = new \PDO("mysql:host=localhost;dbname=$db_name;charset=utf8", Database::DB_USER, Database::DB_PASSWORD);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //only for testing purposes.
            $query = "";
            switch ($type) {
                case Query::SELECT:
                    Database::prepareSelect($query, $table, $params, $wheres, $options);
                    break;
                case Query::INSERT:
                    Database::prepareInsert($query, $table, $params);
                    break;
                case Query::UPDATE:
                    Database::prepareUpdate($query, $table, $params, $wheres);
                    break;
                case Query::DELETE:
                    Database::prepareDelete($query, $table, $wheres);
            }
            echo "query: $query <br/>";
            $pstmt = $conn->prepare($query);
            if ($type == Query::SELECT && !empty($wheres))
                Database::bindValues($pstmt, $wheres);
            if ($type == Query::INSERT)
                Database::bindValues($pstmt, $params);
            if ($type == Query::UPDATE) {
                Database::bindValues($pstmt, $params);
                Database::bindValues($pstmt, $wheres);
            }
            if ($type == Query::DELETE)
                Database::bindValues($pstmt, $wheres);
            $pstmt->execute();
            $snagged = $pstmt->rowCount();
            //echo "got: $snagged";
            if ($type > 1 || $snagged == 0)
                return $snagged;
            //only select with results here
            $result = [];
            while($row = $pstmt->fetch(\PDO::FETCH_ASSOC)){//fixx here
                $result[] = $row;
            }
            //var_dump($result);
            return $result;
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        
        return null;
    }

    /*
     * params can do SQL rename:         [ col1 => name, col2 => age]
     * params can be just columns:       [ col1, col2 ]
     */

    private static function prepareSelect(&$query, &$table, &$params, &$wheres, &$options) {//wheres is optional
        $query = "SELECT ";
        $isAss = Database::isAssociative($params);
        $size = count($params);
        $index = 0;
        foreach ($params as $key => $value) {
            $query .= ($isAss ? "$key AS $value" : "$value");
            if ($index < $size - 1)
                $query .= ', ';
            $index++;
        }
        $query .= " FROM $table";
        if (isset($wheres) && !empty($wheres)) {
            Database::processWheres($query, $wheres);
        }
        if(isset($options['orderby'])) {
            $query .= ' ORDER BY ' . $options['orderby'];
            if(isset($options['order'])){//if not specified, order is ASC
                $query .= ' ' . $options['order'];
            }
        }
    }

    private static function processWheres(&$query, &$wheres) {
        $query .= ' WHERE ';
        $size = count($wheres);
        $index = 0;
        foreach ($wheres as $key => $value) {
            $query .= "$key=:$key";
            if ($index < $size - 1)
                $query .= ' AND ';
            $index++;
        }
    }

    private static function bindValues(&$pstmt, &$array) {
        foreach ($array as $key => $value) {
            $pstmt->bindValue(":$key", $value);
        }
    }

    private static function prepareInsert(&$query, &$table, &$params) {
        $keys = array_keys($params);
        $query = "INSERT INTO $table VALUES(";
        $size = count($keys);
        for ($k = 0; $k < $size; $k++) {
            $query .= ':' . $keys[$k];
            if ($k < $size - 1) {
                $query .= ', ';
            }
        }
        $query .= ')';
        //no return
    }

    private static function prepareUpdate(&$query, &$table, &$params, &$wheres) {
        $query = "UPDATE $table SET ";
        $size = count($params);
        $index = 0;
        foreach ($params as $key => $value) {
            $query .= "$key=:$key";
            if ($index < $size - 1)
                $query .= ', ';
            $index++;
        }
        Database::processWheres($query, $wheres);
    }

    private static function prepareDelete(&$query, &$table, &$wheres) {
        $query = "DELETE FROM $table";
        Database::processWheres($query, $wheres);
    }

    /*
     * INSERT INTO table VALUES(v1,v2,v3,...)
     *   [ user => jane, pswd => 333 ]
     * Returns numRows affected.
     */

    public static function dInsert($db_name, $table, $params = []) {//phasing out
        $keys = array_keys($params);
        $query = "INSERT INTO $table VALUES(";
        $size = count($keys);
        for ($k = 0; $k < $size; $k++) {
            $query .= ':' . $keys[$k];
            if ($k < $size - 1) {
                $query .= ', ';
            }
        }
        $conn = new \PDO("mysql:host=localhost;dbname=$db_name;charset=utf8", Database::DB_USER, Database::DB_PASSWORD);
        $pstmt = $conn->prepare($query . ')');
        foreach ($params as $key => $value) {
            $pstmt->bindValue(":$key", $value);
        }
        $pstmt->execute();
        return $pstmt->rowCount();
    }

    //Utilities
    private static function isAssociative($array) {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    private static function db_exists($db) {
        $conn = new \PDO("mysql:host=localhost", DB_USER, DB_PASSWORD);
        $rset = $conn->query('SHOW DATABASES');
        while ($cdb = $rset->fetchColumn(0)) {
            if ($cdb == $db)
                return true;
        }
        return false;
    }

    private static function getColumnNames($db, $table) {
        $columns = [];
    }

    //end


    public static function insert($db_name, $table_name, $params) {
        //verify db name
        $len = count($params);
        if (!array_key_exists($db_name, self::listDbs()) || $len <= 0) {
            return;
        }
        try {
            $conn = new \PDO('mysql:host=localhost;' . self::$db_prefix . $db_name, Database::DB_USER, Database::DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //only for testing purposes.
            $sql = "INSERT INTO $table_name VALUES(?";
            $w = 1; //account for first param
            while ($w < $len) {
                $sql .= self::$par;
            }
            $pstmt = $conn->prepare($sql . ')');

            for ($i = 0; $i < $len; $i++) {
                $pstmt->bindValue($i, $params[$i]);
            }
            $pstmt->execute();

            # Affected Rows?
            echo $pstmt->rowCount(); // 1
        } catch (\PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public static function listDbs() {//tested. leave off db name
        $conn = new \PDO("mysql:host=localhost", DB_USER, DB_PASSWORD);
        $rset = $conn->query('SHOW DATABASES');
        $dbs = array();
        while (($db = $rset->fetchColumn(0)) != false) {
            array_push($dbs, $db);
            echo $db . "<br>";
        }
        if (count($dbs) > 0) {
            return $dbs;
        }
        return NULL;
    }

    /*
     * 
     */

    public static function insertUser($username, $password, $email) {//tested
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //only for testing purposes.
            $salt = self::generateRandomSalt();

            $pstmt = $conn->prepare("INSERT INTO members VALUES(NULL, :user, NULL, NULL, :email, :digest, :salt, NULL, NULL)"); //auto-increment requires NULL
            //$pstmt->execute(array(':user' => $username, ':digest' => md5($password . $salt), ':salt' => $salt));

            $pstmt->bindValue(":user", $username);
            $pstmt->bindValue(":digest", md5($password . $salt));
            $pstmt->bindValue(":salt", $salt);
            $pstmt->bindValue(":email", $email);
            $pstmt->execute();
            return $pstmt->rowCount();
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public static function insertVendorUser($email, $first, $last) {
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //only for testing purposes.


            $pstmt = $conn->prepare("INSERT INTO members VALUES(NULL, :user, :first, :last, :email, NULL, NULL)"); //auto-increment requires NULL

            $pstmt->bindValue(":user", $first . $last);
            $pstmt->bindValue(":first", $first);
            $pstmt->bindValue(":last", $last);
            $pstmt->bindValue(":email", $email);
            $pstmt->execute();
            return $pstmt->rowCount();
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public static function generateRandomSalt() {
        return base64_encode(mcrypt_create_iv(12, MCRYPT_DEV_URANDOM)); //apache mcrypt mod enabled.
    }

    /*
     * Returns -1 for invalid attempt.
     * Returns the uid for valid attempts.
     */

    public static function validateUser($username, $password) {//tested
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
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
                    //echo "Its a fucking miracle";
                    return $saltstmt->fetch()[0];
                }
                //print_r($salt);
            }
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        return -1;
    }

    public static function appendUserPswd($email, $username, $pswd) {
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //only for testing purposes.

            $salt = self::generateRandomSalt();
            $pstmt = $conn->prepare("UPDATE members SET username=:us, hash=:digest, salt=:slt WHERE email=:em"); //auto-increment requires NULL

            $pstmt->bindValue(":us", $username);
            $pstmt->bindValue(":digest", md5($password . $salt));
            $pstmt->bindValue(":slt", $salt);
            $pstmt->bindValue(":em", $email);
            $pstmt->execute();
            return $pstmt->rowCount();
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public static function isUserUnique($username, $email) {
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
            $pstmt = $conn->prepare("SELECT uid FROM members WHERE username=? OR email=?");
            $pstmt->bindValue(1, $username);
            $pstmt->bindValue(2, $email);
            $pstmt->execute();
            return $pstmt->rowCount() == 0;
        } catch (\PDOException $exc) {
            //ignore
        }
        return false;
    }

    public static function isUserNameUnique($username) {
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
            $pstmt = $conn->prepare("SELECT uid FROM members WHERE username=?");
            $pstmt->bindValue(1, $username);
            $pstmt->execute();
            return $pstmt->rowCount() == 0;
        } catch (\PDOException $exc) {
            //ignore
        }
        return false;
    }

    public static function isUserEmailUnique($email) {
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
            $pstmt = $conn->prepare("SELECT uid FROM members WHERE email=?");
            $pstmt->bindValue(1, $email);
            $pstmt->execute();
            return $pstmt->rowCount() == 0;
        } catch (\PDOException $exc) {
            //ignore
        }
        return false;
    }

    public static function isVendorUserUnique($email) {
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
            $pstmt = $conn->prepare("SELECT uid FROM members WHERE email=?");
            $pstmt->bindValue(1, $email);
            $pstmt->execute();
            return $pstmt->rowCount() == 0;
        } catch (\PDOException $exc) {
            //ignore
        }
        return false;
    }

    //Returns assoc array  or null on error
    public static function getUserInfoByEmail($email) {
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
            $pstmt = $conn->prepare("SELECT uid, username FROM members WHERE email=?");
            $pstmt->bindValue(1, $email);
            $pstmt->execute();
            if ($pstmt->rowCount() > 0) {
                $row = $pstmt->fetch(\PDO::FETCH_ASSOC);
                return [ 'uid' => $row['uid'], 'username' => $row['username']];
            }
        } catch (\PDOException $exc) {
            //ignore
        }
        return null;
    }

    /*
     * Normal sql results: indexed array
     * To specify object or associative array, use flags
     * 
     */

    public static function getCategories($uid) {
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //disable after testing
            //query does not produce 'All' cuz it has no parent
            $query = "SELECT a.name AS name, b.pid AS parent, a.level, a.catid "
                    . "FROM category AS a "
                    . "INNER JOIN category AS b "
                    . "ON a.pid=b.catid "
                    . "WHERE a.uid=$uid "
                    . "ORDER BY a.level"; //default is ASC, just making clear.

            $result = $conn->query($query);
            if ($result->rowCount() > 0) {
                require_once 'app/models/primary.php';
                $cats = array();
                while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                    array_push($cats, new \app\model\Category((int) $row['catid'], $row['name'], (int) $row['parent'], (int) $row['level']));
                }
                return $cats;
            }
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        return null;
    }

    /*
     * Handles the missing 'All'
     */

    public static function getCategoriesImproved($uid) {
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //disable after testing

            $query = "SELECT name, pid, level, catid "
                    . "FROM category "
                    . "WHERE uid=$uid "
                    . "ORDER BY level"; //default is ASC, just making clear.

            $result = $conn->query($query);
            if ($result->rowCount() > 0) {
                require_once 'app/models/primary.php';
                $cats = array();
                while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                    array_push($cats, new \app\model\Category((int) $row['catid'], $row['name'], (int) $row['pid'], (int) $row['level']));
                }
                return $cats;
            }
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        return null;
    }

    public static function getCategoriesJson($uid) {
        return json_encode($this->getCategories($uid));
    }

    /*
     * 0 means no parent(pid) and highest level(level)
     */

    public static function createAllCategory($uid) {
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //only for testing purposes.


            $pstmt = $conn->prepare("INSERT INTO category VALUES(NULL, :name, :pid, :level, :uid)"); //auto-increment requires NULL

            $pstmt->bindValue(":name", "All");
            $pstmt->bindValue(":pid", 0);
            $pstmt->bindValue(":level", 0);
            $pstmt->bindValue(":uid", $uid);
            $pstmt->execute();
            return $pstmt->rowCount();
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /*
     * Pull tasks by uid allows only 1 database pull.
     */

    public static function getTasks($uid) {
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=demodb;charset=utf8', Database::DB_USER, Database::DB_PASSWORD);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //disable after testing
            $query = "SELECT taskid, name, complete, catid "
                    . "FROM task "
                    . "WHERE uid=$uid";
            $result = $conn->query($query);
            if ($result->rowCount() > 0) {
                require_once 'app/models/primary.php';
                $tasks = [];
                while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                    $tasks[] = new \app\model\Task((int) $row['taskid'], $row['name'], (int) $row['catid'], (int) $row['complete']); //technically, complete is ENUM
                }
                return $tasks;
            }
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        return null;
    }

    public static function getAllUsers() {
        try {
            $conn = new \PDO('mysql:host=localhost;dbname=tasklist', Database::DB_USER, Database::DB_PASSWORD);
            $result = $conn->query("SELECT username FROM members");
            $users = array();
            while ($row = $result->fetch()) {//PDO::FETCH_ASSOC
                $u = new User($row[0]);
                array_push($users, $u);
            }
            return $users;
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public static function updateUserName() {
        
    }

    public static function getCount() {
        return self::$count;
    }

}

function creation() {
    $mem_stat = "CREATE TABLE IF NOT EXISTS members (" .
            "id INT NOT NULL AUTO_INCREMENT, " .
            "username VARCHAR(16) NOT NULL, " .
            "email VARCHAR(255) NOT NULL, " .
            "password TEXT, " .
            "activated ENUM('0','1') NOT NULL DEFAULT '0', " .
            "PRIMARY KEY (id), " .
            "UNIQUE KEY username (username, email) " .
            ")";
    if ($db->query($mem_stat)) {
        //table created
    } else {
        //blow up
    }
}

function testInsert() {
    Database::insertUser("mary", "love");
}

//testInsert();
//Database::validateUser("mary", "love");

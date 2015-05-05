<?php

/*
 * Members: uid, username, firstName, lastName, email, hash, salt, phone, carrier, autolog
 * Products: uid, asin, title, maker, priority, category, reputation, price_below, lowest_price
 * 
 * DateTime: YYYY-MM-DD HH:MM:SS
 * Date: YYYY-MM-DD
 */

class Db_Query {

    const SELECT = 1;
    const INSERT = 2;
    const UPDATE = 3;
    const DELETE = 4;

}

class DatabaseProto {

    public $dsn;
    public $user;
    public $pswd;
    public $host;
    public $db_name;
    
    private $db = 'mysql';

    const FAILURE = -2;
    const ACCESS = '../app/config.json';

    //handle changing credentials
    private $debug = true; //live: false
    //force table columns and provides reference to tables and handles
    //auto-increment fields.
    private $params = [
        'members' => [
            'uid' => null,
            'username' => null,
            'firstName' => null,
            'lastName' => null,
            'birthday' => null,
            'email' => null,
            'hash' => null,
            'salt' => null,
            'phone' => null,
            'carrier' => null,
            'autolog' => null,
        ],
        'products' => [
            'uid' => null,
            'asin' => null,
            'title' => null,
            'maker' => null,
            'priority' => null,
            'category' => null,
            'reputation' => null,
            'price_below' => null,
            'lowest_price' => null
        ]
    ];

    public function __construct() {
        $config = json_decode(file_get_contents(self::ACCESS));
        if ($this->debug) {
            $this->dsn = $config->test_db_dsn;
            $this->host = $config->test_db_host;
            $this->user = $config->test_db_user;
            $this->pswd = $config->test_db_pswd;
            $this->db_name = $config->test_db_name;
        } else {
            $this->dsn = $config->db_dsn;
            $this->host = $config->db_host;
            $this->user = $config->db_user;
            $this->pswd = $config->db_pswd;
            $this->db_name = $config->db_name;
        }
    }
    /*
     * Features:
     * 1. All tables.
     * 2. Any amount and ordering or column values.
     * 3. Acts as a buffer between client and db.
     * 
     * example: insert('members', [ 'username' => 'bob', 'pswd' => '111' ]);
     */
    public function insert($table, $pairs) {
        if(!$this->validate($table, $pairs)) return null;
        $this->connect($table, Db_Query::INSERT, $this->params[$table]);
        $this->resetParams($table);
    }
    public function select($table, $pairs, $where, $options){
        
    }
    public function update($table, $sets, $wheres){
        if(!$this->tableExists($table)) return false;
        $columns = $this->getColumnNames($table);
        if(!$this->check($columns, $sets) || !$this->check($columns, $wheres)){
            return false;
        }
        $this->connect($table, Db_Query::UPDATE, $sets, $wheres);
    }
    public function delete($table, $wheres){
        if(!$this->tableExists($table)) return false;
        $columns = $this->getColumnNames($table);
        if(!$this->check($columns, $wheres)){
            return false;
        }
        $this->connect($table, Db_Query::DELETE, $wheres);
    }

    private function tableExists($table){
        return true;
    }
    private function check(&$columns, &$data){
        foreach ($data as $col => $value) {
            if(!in_array($col, $columns)){
                return false;
            }
        }
        return true;
    }
    /*
     * Determines whether table exists and at least some
     * parameters provided.
     */
    private function validate($table, $pairs){//needs more factoring
        //block non-existent table and no params.
        if (empty($this->params[$table]) || empty($pairs))
            return false;
        $tripwire = true;
        //loop thru known columns substituting valid values in pairs.
        foreach ($this->params[$table] as $col => $value) {
            if(!empty($pairs[$col])){
                $this->params[$table][$col] = $pairs[$col];
                $tripwire = false;
            }
        }
        return !$tripwire;
    }
    /*
     * flips the logic: verify that every key in pairs exist.
     * Does not depend on $params for column names.
     * Solved table check without actually verifying.
     */
    private function validate2($table, $pairs){//factor out getColumns!
        //false if table doesnt exist or no pairs provided.
        if (empty($table) || empty($pairs))
            return false;
        $columns = $this->getColumnNames($table);
        if(empty($columns)) return false;
        foreach ($pairs as $col => $value) {
            if(!in_array($col, $columns)){
                return false;
            }
        }
    }

    private function resetParams($table){
        //reset column vals for next time
        foreach($this->params[$table] as $col => $value){
            $this->params[$table][$col] = null;
        }
    }

    /*
     * select: connect('demodb', 'members', 1, [ 'username', 'email' ], [ 'uid' => 1 ], [ 'orderby' => 'uid', 'order' => 'ASC' ]
     * insert: connect('demodb', 'members', 1, [ ]
     * 
     * update: connect('demodb', 'members', 1,
     * delete: connect('demodb', 'members', 1,
     * options: LIMIT, ORDER BY, etc
     */
    private function connect($table, $type, $params = [], $wheres = [], $options = []) {

        try {
            $conn = new \PDO($this->dsn, $this->user, $this->pswd);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //only for testing purposes.
            $query = "";
            switch ($type) {
                case Db_Query::SELECT:
                    $this->prepareSelect($query, $table, $params, $wheres, $options);
                    break;
                case Db_Query::INSERT:
                    $this->prepareInsert($query, $table, $params);
                    break;
                case Db_Query::UPDATE:
                    $this->prepareUpdate($query, $table, $params, $wheres);
                    break;
                case Db_Query::DELETE:
                    $this->prepareDelete($query, $table, $wheres);
            }
            //echo "query: $query <br/>";
            $pstmt = $conn->prepare($query);
            if ($type == Db_Query::SELECT && !empty($wheres))
                $this->bindValues($pstmt, $wheres);
            if ($type == Db_Query::INSERT)
                $this->bindValues($pstmt, $params);
            if ($type == Db_Query::UPDATE) {
                $this->bindValues($pstmt, $params);
                $this->bindValues($pstmt, $wheres);
            }
            if ($type == Db_Query::DELETE)
                $this->bindValues($pstmt, $wheres);
            $pstmt->execute();
            $snagged = $pstmt->rowCount();
            
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
    //Abstract further
    private function connect2($db, $table, $query){
        try {
            $conn = new \PDO("mysql:host=localhost;dbname=$db;charset=utf8", $this->user, $this->pswd);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare($query);
            //bind??
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
    }
    private function connect3($db, $table, $obj){
        try {
            $conn = new \PDO("mysql:host=localhost;dbname=$db;charset=utf8", $this->user, $this->pswd);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare($query);
            return $stmt->execute((array)$obj);
        } catch (\PDOException $exc) {
            echo $exc->getTraceAsString();
        }
    }
    /*
     * params can do SQL rename:         [ col1 => name, col2 => age]
     * params can be just columns:       [ col1, col2 ]
     */

    private function prepareSelect(&$query, &$table, &$params, &$wheres, &$options) {//wheres is optional
        $query = "SELECT ";
        $isAss = $this->isAssociative($params);
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
            $this->processWheres($query, $wheres);
        }
        if(isset($options['orderby'])) {
            $query .= ' ORDER BY ' . $options['orderby'];
            if(isset($options['order'])){//if not specified, order is ASC
                $query .= ' ' . $options['order'];
            }
        }
    }

    private function processWheres(&$query, &$wheres) {
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

    private function bindValues(&$pstmt, &$array) {
        foreach ($array as $key => $value) {
            $pstmt->bindValue(":$key", $value);
        }
    }

    private function prepareInsert(&$query, &$table, &$params) {
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

    private function prepareUpdate(&$query, &$table, &$params, &$wheres) {
        $query = "UPDATE $table SET ";
        $size = count($params);
        $index = 0;
        foreach ($params as $key => $value) {
            $query .= "$key=:$key";
            if ($index < $size - 1)
                $query .= ', ';
            $index++;
        }
        $this->processWheres($query, $wheres);
    }

    private function prepareDelete(&$query, &$table, &$wheres) {
        $query = "DELETE FROM $table";
        $this->processWheres($query, $wheres);
    }
    
    //Utilities
    private static function isAssociative($array) {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function db_exists($db) {
        $conn = new \PDO($this->dsn, $this->user, $this->pswd);
        $rset = $conn->query('SHOW DATABASES');
        while ($cdb = $rset->fetchColumn(0)) {
            if ($cdb == $db)
                return true;
        }
        return false;
    }
    private function table_exists($table){
        $conn = new \PDO($this->dsn, $this->user, $this->pswd);
        $rset = $conn->query('SHOW TABLES');
        
    }
    //Way to dynamically determine column names.
    public function getColumnNames($table) {
        $conn = new \PDO($this->dsn, $this->user, $this->pswd);
        $rset = $conn->query("DESCRIBE $table");
        $columnNames = $rset->fetchAll(PDO::FETCH_COLUMN);
        return $columnNames;
    }
    private function clean($input){
        $search = array(
            '@<script[^>]*?>.*?</script>@si', //strip js
            '@<[\/\!]*?[^<>]*?>@si',          //strip html tags
            '@<style[^>]*?>.*?</style>@siU',  //strip style tags
            '@<![\s\S]*?--[\t\n\r]*>@'        //strip multi-line comments
        );
        $output = preg_replace($search, '', $input);
        return $output;
    }
    private function sanitize($input){
        if(is_array($input)){
            foreach ($input as $var => $val) {
                $output[$var] = $this->sanitize($val);
            }
        } else {
            if(get_magic_quotes_gpc()){
                $input = stripslashes($input);
            }
            $input = $this->clean($input);
            $output = mysql_real_escape_string($input);
        }
        return $output;
    }
    private function createDate($month, $day, $year){
        $date = new DateTime("$month/$day/$year");
        return $date->format('Y-m-d H:i:s');
    }
}
//exit();
$db = new DatabaseProto;
//$db->insert('members', ['firstName' => 'Bob', 'lastName' => 'Smith']);
//$db->update('members', [ 'username' => 'fuggles' ], [ 'uid' => 4 ]);// set username=fuggles where uid=4


//$columns = $db->getColumnNames('members');
//var_dump($columns);
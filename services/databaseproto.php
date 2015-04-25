<?php

/*
 * Members: uid, username, firstName, lastName, email, hash, salt, phone, carrier, autolog
 * Products: uid, asin, title, maker, priority, category, reputation, price_below, lowest_price
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

    const FAILURE = -2;
    const ACCESS = '../app/config.json';

    //handle changing credentials
    private $debug = true; //live: false
    //provides reference to tables and handles
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
        self::connect($table, Db_Query::INSERT, $this->params[$table]);
        $this->resetParams($table);
    }
    public function select($table, $pairs, $where, $options){
        
    }
    /*
     * Determines whether table exists and at least some
     * parameters provided.
     */
    private function validate($table, $pairs){
        //block non-existent table and no params.
        if (empty($this->params[$table]) || empty($pairs))
            return false;
        $tripwire = true;
        //loop thru known columns substituting valid values in pairs.
        foreach ($this->params[$table] as $col => $value) {
            if($pairs[$col]){
                $this->params[$table][$col] = $pairs[$col];
                $tripwire = false;
            }
        }
        return !$tripwire;
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
    private static function connect($table, $type, $params = [], $wheres = [], $options = []) {

        try {
            $conn = new \PDO($this->dsn, $this->user, $this->pswd);
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
            //echo "query: $query <br/>";
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
}

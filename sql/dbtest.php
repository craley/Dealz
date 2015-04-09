<?php

        const DB_SERVER = "mysql.chrisraley24.com";
        const DB_USER = "www_data";
        const DB_PASSWORD = "tuna0324";
        const DB_NAME = "cr_demodb";

function generateRandomSalt() {
    return base64_encode(mcrypt_create_iv(12, MCRYPT_DEV_URANDOM)); //apache mcrypt mod enabled.
}

function insertUser($username, $password, $email) {//tested
    try {
        $conn = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASSWORD);
        
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //only for testing purposes.
        $salt = generateRandomSalt();

        $pstmt = $conn->prepare("INSERT INTO members VALUES(NULL, :user, NULL, NULL, :email, :digest, :salt, NULL, NULL)"); //auto-increment requires NULL
        //$pstmt->execute(array(':user' => $username, ':digest' => md5($password . $salt), ':salt' => $salt));

        $pstmt->bindValue(":user", $username);
        $pstmt->bindValue(":digest", md5($password . $salt));
        $pstmt->bindValue(":salt", $salt);
        $pstmt->bindValue(":email", $email);
        $pstmt->execute();
        return $pstmt->rowCount();
    } catch (PDOException $exc) {
        echo $exc->getTraceAsString();
    }
}

insertUser('chris', '0324', 'siliconcuriosity@gmail.com');

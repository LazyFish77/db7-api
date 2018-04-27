<?php
    header('Access-Control-Allow-Origin: *');
    require 'connection.php';
    $dbh = getDB();
    if(!empty($_POST['loginUser'])){
        verifyLogin();
    } else if($_POST['registerUser'] == 'true') {
        createNewAccount();
    }
    function passwordHash($strPassword) {
        // $salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
        // $salt = base64_encode($salt);
        // $salt = str_replace('+', '.', $salt);
        $hash = crypt($strPassword, '$2y$10$' . '$');
        return $hash;
    }
    function createNewAccount() {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $hash = passwordHash($password);
        $dbh = getDB();
        $statement = $dbh->prepare("INSERT INTO User(username, password)
                VALUES('$username','$hash')");
        $statement->execute();
        $response;
        try {
            $response = json_encode(true);
        } catch (Exception $e) {
            $response = json_encode(false);
        }
        echo $json;
    }
    function verifyLogin() {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $hash = passwordHash($password);
        $dbh = getDB();
        $statement = $dbh->prepare('SELECT username
            FROM User
            WHERE username = :username AND password = :password');
        $statement->bindValue(':username', $username);
        $statement->bindValue(':password',  $hash);
        $statement->execute();
        $results = $statement->fetchAll();
        $json = json_encode($results);
         echo $json;
    }
?>
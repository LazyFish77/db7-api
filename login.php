<?php
    header('Access-Control-Allow-Origin: *');
    require 'connection.php';
    $dbh = getDB();
    if(!empty($_POST['loginUser'])){
        verifyLogin();
    } else if($_POST['registerUser'] == 'true') {
        createNewAccount();
    }
    function passwordHash($strPassword,$salt) {
      
        $hash = crypt($strPassword, '$2y$10$' . '$'.$salt);
        return $hash;
    }
    function getSalt(){
        $salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
        $salt = base64_encode($salt);
        $salt = str_replace('+', '.', $salt);
        return $salt;
    };
    function createNewAccount() {
        $dbh = getDB();
        $username = $_POST['username'];
        $password = $_POST['password'];
        $salt = getSalt();
        $hash = passwordHash($password, $salt);
        $statement = $dbh->prepare("INSERT INTO User(username, password, salt, privilege_level)
                VALUES('$username','$hash', '$salt', 'read')");
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
        $dbh = getDB();
        $username = $_POST['username'];
        $password = $_POST['password'];
        $salt = getUserSalt($username);
        $hash = passwordHash($password, $salt);
        $statement = $dbh->prepare('SELECT username
            FROM User
            WHERE username = :username AND password = :password');
        $statement->bindValue(':username', $username);
        $statement->bindValue(':password',  $hash);
        $statement->execute();
        $results = $statement->fetchAll();
        if(!empty($results)) {
            $response = json_encode(true);    
        } else {
            $response = json_encode(false);
        }
        
         echo $response;
    }
    function getUserSalt($username) {
        $dbh = getDB();
        $statement = $dbh->prepare('SELECT salt
            FROM User
            WHERE username = :username');
        $statement->bindValue(':username', $username);
        $statement->execute();
        $results = $statement->fetch();
        // print_r($results);
        return $results['salt'];
        // echo $results['salt'];
    }
?>
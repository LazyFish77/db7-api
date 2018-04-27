<?php
    header('Access-Control-Allow-Origin: *');
    require 'connection.php';
    $dbh = getDB();
     if(!empty($_POST['getSameLastName'])){
        getSameLastName();
    } else if(!empty($_POST['getParents'])) {
        getParents();
    }
    function getSameLastName($lastName) {
        $statement = $dbh->prepare('SELECT first_name, middle_name, last_name
        FROM Person WHERE last_name = :lastName')
        $statement->bindValue(':lastName', $lastName);
        $statement->execute();
        $results = $statement->fetchAll();
        $json = json_encode($results);
        echo $json;
    }

    function getParents($id) {
        $statement = $dbh->prepare('SELECT first_name, middle_name, last_name
        FROM Person
        WHERE id IN (
        SELECT Person_id2
        FROM (
            SELECT *
            FROM Person, Relationship
            WHERE Person.id = Relationship.Person_id1 AND relationship = 'child'
             ) AS t1
        WHERE id = :id
    );');
    $statement->bindValue(':id', $id);
    }
?>
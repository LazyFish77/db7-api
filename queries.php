<?php
    header('Access-Control-Allow-Origin: *');
    require 'connection.php';
    $dbh = getDB();
     if(!empty($_POST['getSameLastName'])){
        getSameLastName($_POST['getSameLastName']);
    } else if(!empty($_POST['getParents'])) {
        getParents($_POST['getParents']);
    } else if(!empty($_POST['getChildern']))) {
        getChildern($_POST['getChildern']);
    } else if(!empty($_POST['getSiblings']))) {
        getSiblings($_POST['getSiblings']);
    } else if(!empty($_POST['getBothSetsOfGrandParents']))) {
        getBothSetsOfGrandParents($_POST['getBothSetsOfGrandParents']);
    } else if(!empty($_POST['getAllGrandKids']))) {
        getAllGrandKids($_POST['getAllGrandKids']);
    } else if(!empty($_POST['getSpouse']))) {
        getSpouse($_POST['getSpouse']);
    } else if(!empty($_POST['getAllEvents']))) {
        getAllEvents($_POST['getAllEvents']);
    } else if(!empty($_POST['getFamilyMembersWithKeyword']))) {
        getFamilyMembersWithKeyword($_POST['getFamilyMembersWithKeyword']);
    } else if(!empty($_POST['getEventsOnDate']))) {
        getEventsOnDate($_POST['getEventsOnDate']);
    }

    //1
    function getSameLastName($lastName) {
        $statement = $dbh->prepare(
            `
            SELECT first_name, middle_name, last_name
            FROM Person WHERE last_name = :lastName
        `
        );
        $statement->bindValue(':lastName', $lastName);
        $statement->execute();
        $results = $statement->fetchAll();
        $json = json_encode($results);
        echo $json;
    }

    //2
    function getParents($id) {
        $statement = $dbh->prepare(
            `
            SELECT first_name, middle_name, last_name
            FROM Person
            WHERE id IN (
            SELECT Person_id2
            FROM (
                SELECT *
                FROM Person, Relationship
                WHERE Person.id = Relationship.Person_id1 AND relationship = 'child'
                ) AS t1
            WHERE id = :id
            );
            `
        );
        $statement->bindValue(':id', $id);
    }

    //3
    function getChildern($id) {
        $statement = $dbh->prepare(
            `
            SELECT first_name, middle_name, last_name
            FROM Person
            WHERE id IN (
                SELECT Person_id2 
                FROM (
                    SELECT * FROM Person, Relationship
                    WHERE Person.id = Relationship.Person_id1 AND relationship = 'parent'
                ) AS t1
            WHERE id = :id
            `
        );
        $statement->bindValue(':id', $id);
    }

    //4
    function getSiblings($id) {
        $statement = $dbh->prepare(
            `
            SELECT first_name, middle_name, last_name
            FROM Person
            WHERE id IN (
                SELECT DISTINCT Person_id2
                FROM Relationship
                WHERE relationship = 'parent' AND Person_id1 IN (
                    SELECT Person_id2
                    FROM Relationship
                    WHERE Person_id1 = '$id'
                    and Relationship = 'child'
                )
                );`
            );
        $statement->bindValue(':id', $id);
    }

    //5
    function getBothSetsOfGrandParents($id) {
        $statement = $dbh->prepare(
            `
            SELECT first_name, middle_name, last_name
            FROM Person
            WHERE id IN (
                SELECT Person_id2 
                FROM (
                    SELECT * 
                    FROM Person, Relationship 
                    WHERE Person.id = Relationship.Person_id1 AND relationship = 'child'
                ) AS grandparents
                WHERE id IN (
                    SELECT Person_id2
                    FROM (
                        SELECT *
                        FROM Person, Relationship
                        WHERE Person.id = Relationship.Person_id1 AND relationship = 'child'
                    ) AS parents
                    WHERE id = :id
                ));`
        );
        $statement->bindValue(':id', $id);
    }

    //6
    function getAllGrandKids($id) {
        $statement = $dbh->prepare(
            `SELECT first_name, middle_name, last_name
                FROM Person
                WHERE id IN (
                    SELECT Person_id2 
                    FROM (
                        SELECT * 
                        FROM Person, Relationship 
                        WHERE Person.id = Relationship.Person_id1 AND relationship = 'parent'
                    ) AS grandparents
                    WHERE id IN (
                        SELECT Person_id2
                        FROM (
                            SELECT *
                            FROM Person, Relationship
                            WHERE Person.id = Relationship.Person_id1 AND relationship = 'parent'
                        ) AS parents
                        WHERE id = :id
                    )
                );`
        );
        $statement->bindValue(':id', $id);
    }

    //7
    function getSpouse($id) {
        $statement = $dbh->prepare(
            ` SELECT first_name, middle_name, last_name
                FROM Person
                WHERE id IN (
                    SELECT Person_id
                    FROM PeopleAssocation
                    WHERE Association_id IN (
                        SELECT MAX(id)
                        FROM (
                            SELECT *
                            FROM Association
                            WHERE Association.id IN (
                                SELECT PeopleAssocation.Association_id
                                FROM PeopleAssocation
                                WHERE PeopleAssocation.Person_id = :id
                            )
                        ) AS asso
                        WHERE type = 'marriage'
                    ) 
                    AND Person_id <> :id
                );`
        );
        $statement->bindValue(':id', $id);
    }

    //8
    function getAllEvents($id) {
        $statement = $dbh->prepare(`SELECT * FROM Event WHERE Person_id = :id;`);
        $statement->bindValue(':id', $id);
    }

    //9
    function getFamilyMembersWithKeyword($keyword) {
        $statement = $dbh->prepare(
            `
            SELECT first_name, middle_name, last_name
                FROM Person
                WHERE Person.notes
                LIKE '%:keyword%';
            `
        );
        $statement->bindValue(':keyword', $keyword);
    }
    //10

    function getEventsOnDate($date) {
        $statement = $dbh->prepare(`SELECT * FROM Event WHERE date = :date;`);
        $statement->bindValue(':date', $id);
    }


    
);
?>
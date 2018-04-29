<?php
    header('Access-Control-Allow-Origin: *');
    require 'connection.php';
     if(!empty($_POST['getSameLastName'])){
        getSameLastName($_POST['getSameLastName']);
    } else if(!empty($_POST['getParents'])) {
        getParents($_POST['getParents']);
    } else if(!empty($_POST['getChildern'])) {
        getChildern($_POST['getChildern']);
    } else if(!empty($_POST['getSiblings'])) {
        getSiblings($_POST['getSiblings']);
    } else if(!empty($_POST['getBothSetsOfGrandParents'])) {
        getBothSetsOfGrandParents($_POST['getBothSetsOfGrandParents']);
    } else if(!empty($_POST['getAllGrandKids'])) {
        getAllGrandKids($_POST['getAllGrandKids']);
    } else if(!empty($_POST['getSpouse'])) {
        getSpouse($_POST['getSpouse']);
    } else if(!empty($_POST['getAllEvents'])) {
        getAllEvents($_POST['getAllEvents']);
    } else if(!empty($_POST['getFamilyMembersWithKeyword'])) {
        getFamilyMembersWithKeyword($_POST['getFamilyMembersWithKeyword']);
    } else if(!empty($_POST['getEventsOnDate'])) {
        getEventsOnDate($_POST['getEventsOnDate']);
    } else if (!empty($_POST['addPerson'])) {
        addPerson($_POST['addPerson']);
    } else if (!empty($_POST['setPrivilegeLevel'])) {
        setPrivilegeLevel($_POST['setPrivilegeLevel']);
    } else if (!empty($_POST['deletePerson'])) {
        deletPerson($_POST['deletePerson']);
    } else if (!empty($_POST['getPrivilegeLevel'])){
        getPrivilegeLevel($_POST['getPrivilegeLevel']);
    }
    

    //1
    function getSameLastName($lastName) {
        $dbh = getDB();
        $statement = $dbh->prepare(
            'SELECT first_name, middle_name, last_name
            FROM Person WHERE last_name = :lastName
            '
        );
        $statement->bindValue(':lastName', $lastName);
        $statement->execute();
        $results = $statement->fetchAll();
        $results = json_encode($results);
        echo $results;
    }

    //2
    function getParents($id) {
        $dbh = getDB();
        $statement = $dbh->prepare(
            '
            SELECT first_name, middle_name, last_name
            FROM Person
            WHERE id IN (
            SELECT Person_id2
            FROM (
                SELECT *
                FROM Person, Relationship
                WHERE Person.id = Relationship.Person_id1 AND relationship = :child
                ) AS t1
            WHERE id = :id
            );
            '
        );
        $statement->bindValue(':id', $id);
        $statement->bindValue(':child', 'child');
        $statement->execute();
        $results = $statement->fetchAll();
        $results = json_encode($results);
        echo $results;
    }

    //3
    function getChildern($id) {
        $dbh = getDB();
        $statement = $dbh->prepare(
            '
            SELECT first_name, middle_name, last_name
            FROM Person
            WHERE id IN (
                SELECT Person_id2 
                FROM (
                    SELECT * FROM Person, Relationship
                    WHERE Person.id = Relationship.Person_id1 AND relationship = :parent
                ) AS t1
                WHERE id = :id
            );
            '
        );
        $statement->bindValue(':id', $id);
        $statement->bindValue(':parent', 'parent');
        $statement->execute();
        $results = $statement->fetchAll();
        $results = json_encode($results);
        echo $results;
    }

    //4
    function getSiblings($id) {
        $dbh = getDB();
        $statement = $dbh->prepare(
            '
            SELECT first_name, middle_name, last_name
            FROM Person
            WHERE id IN (
                SELECT DISTINCT Person_id2
                FROM Relationship
                WHERE relationship = :parent AND Person_id1 IN (
                    SELECT Person_id2
                    FROM Relationship
                    WHERE Person_id1 = :id
                    and Relationship = :child
                )
                );
            '
            );
        $statement->bindValue(':id', $id);
        $statement->bindValue(':child', 'child');
        $statement->bindValue(':parent', 'parent');
        $statement->execute();
        $results = $statement->fetchAll();
        $results = json_encode($results);
        echo $results;
    }

    //5
    function getBothSetsOfGrandParents($id) {
        $dbh = getDB();
        $statement = $dbh->prepare(
            '
            SELECT first_name, middle_name, last_name
            FROM Person
            WHERE id IN (
                SELECT Person_id2 
                FROM (
                    SELECT * 
                    FROM Person, Relationship 
                    WHERE Person.id = Relationship.Person_id1 AND relationship = :child
                ) AS grandparents
                WHERE id IN (
                    SELECT Person_id2
                    FROM (
                        SELECT *
                        FROM Person, Relationship
                        WHERE Person.id = Relationship.Person_id1 AND relationship = :child
                    ) AS parents
                    WHERE id = :id
                ));
                '
        );
        $statement->bindValue(':id', $id);
        $statement->bindValue(':child', 'child');
        $statement->execute();
        $results = $statement->fetchAll();
        $results = json_encode($results);
        echo $results;
    }

    //6
    function getAllGrandKids($id) {
        $dbh = getDB();
        $statement = $dbh->prepare(
            'SELECT first_name, middle_name, last_name
                FROM Person
                WHERE id IN (
                    SELECT Person_id2 
                    FROM (
                        SELECT * 
                        FROM Person, Relationship 
                        WHERE Person.id = Relationship.Person_id1 AND relationship = :parent
                    ) AS grandparents
                    WHERE id IN (
                        SELECT Person_id2
                        FROM (
                            SELECT *
                            FROM Person, Relationship
                            WHERE Person.id = Relationship.Person_id1 AND relationship = :parent
                        ) AS parents
                        WHERE id = :id
                    )
                );'
        );
        $statement->bindValue(':id', $id);
        $statement->bindValue(':parent', 'parent');
        $statement->execute();
        $results = $statement->fetchAll();
        $results = json_encode($results);
        echo $results;
    }

    //7
    function getSpouse($id) {
        $dbh = getDB();
        $statement = $dbh->prepare(
            ' SELECT first_name, middle_name, last_name
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
                        WHERE type = :marriage
                    ) 
                    AND Person_id <> :id
                );'
        );
        $statement->bindValue(':id', $id);
        $statement->bindValue(':marriage', 'marriage');
        $statement->execute();
        $results = $statement->fetchAll();
        $results = json_encode($results);
        echo $results;
    }

    //8
    function getAllEvents($id) {
        $dbh = getDB();
        $statement = $dbh->prepare('SELECT * FROM Event WHERE Person_id = :id;');
        $statement->bindValue(':id', $id);
        $statement->execute();
        $results = $statement->fetchAll();
        $results = json_encode($results);
        echo $results;
    }

    //9
    function getFamilyMembersWithKeyword($keyword) {
        $dbh = getDB();
        $keyword = '%'.$keyword.'%';
        $statement = $dbh->prepare(
            'SELECT first_name, middle_name, last_name
            FROM Person
            WHERE Person.notes
            LIKE :keyword;
            '
        );
        $statement->bindValue(':keyword', $keyword);
        $statement->execute();
        $results = $statement->fetchAll();
        $results = json_encode($results);
        echo $results;
    }

    //10
    function getEventsOnDate($date) {
        $dbh = getDB();
        $statement = $dbh->prepare('SELECT * FROM Event WHERE date = :date;');
        $statement->bindValue(':date', $date);
        $statement->execute();
        $results = $statement->fetchAll();
        $results = json_encode($results);
        echo $results;
    }

    function addPerson($person) {
        $person = json_decode($person);
        // echo $person->firstName;
        $dbh = getDb();
        $statement = $dbh-> prepare(
            'INSERT INTO Person(first_name, last_name, middle_name, gender, address, phone, notes )
                VALUES(?,?,?,?,?,?,?)
            ');
        $statement->execute(array(
            $person->firstName,
            $person->lastName,
            $person->middleName,
            $person->gender,
            $person->address,
            $person->phone,
            $person->notes
        ));
        $results = $statement->fetchAll();
        $results = json_encode($results);
        echo $results;
    }

    function setPrivilegeLevel($data) {
        $data = json_decode($data);
        $dbh = getDB();
        $sql = "UPDATE User SET privilege_level='$data->level' WHERE username= '$data->username' ";
        $stmt = $dbh->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            echo $e;
        }
        // $results = json_encode($results);
        // echo $results;
    }

    function deletPerson($person) {
        $dbh = getDB();
        $person = json_decode($person);
        $sql = "DELETE FROM Person WHERE first_name = '$person->firstName'
            AND middle_name = '$person->middleName'
            AND last_name = '$person->lastName'";
            echo $sql;
        $stmt = $dbh->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            echo $e;
        }
    }

    function getPrivilegeLevel($userName) {
        $dbh = getDB();
        $userName = json_decode($userName);
        $sql = "SELECT privilege_level FROM User WHERE username = '$userName'";
        $stmt = $dbh->prepare($sql);
        try {
            $stmt->execute();
            $results = $stmt->fetchAll();
            $results = json_encode($results);
            echo $results;
        } catch (Exception $e) {
            echo $e;
        }
    }
?>
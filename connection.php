<?php
    $user = 'fischt77';
    $pass = 'j533977';
    function getDb() {   
    try {
        $dbh = new PDO('mysql:host=localhost;dbname=fischt77',$user,$pass);  
        return $dbh;   
        // foreach($dbh->query('SELECT * from Venture') as $row){
        //     print_r(json_encode($row));
        // };    
    } catch(PDOException $e) {
        print "Error!: ".$e->getMessage()."<br/>";
        die();
    }
}
    

    ?>
<?php

function db_connect() {
                                                                // Define connection as a static variable, to avoid connecting more than once 
    static $connection;
                                                                // Try and connect to the database, if a connection has not been established yet
    if(!isset($connection)) {
                                                                // Load configuration as an array. Use the actual file-system location of the configuration file - 
    $config = parse_ini_file('/etc/mysqli.php.config'); 
                                                                // File kept outside of server root for DB security
    $connection = mysqli_connect('localhost',$config['username'],$config['password'],'s7art');
    }
                                                                // If connection was not successful, handle the error
    if($connection === false) {
                                                                // Handle error - notify administrator, log to a file, show an error screen, etc.
    return mysqli_connect_error();
    echo "error connecting to database in opendb.php"; 
    }
    return $connection;
    }

function db_query($query) {
                                                                // Connect to the database
    $connection = db_connect();
                                                                // Query the database
    $result = mysqli_query($connection,$query);
    return $result;
}

function db_select($query) {
    $rows = array();
    $result = db_query($query);
                                                                // If query failed, return `false`
    if($result === false) {
    return false;
    echo "failed in db_select statement";
    }
                                                                // If query was successful, retrieve all the rows into an array
    while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
    }
 return $rows;
}

function db_error() {
    $connection = db_connect();
    return mysqli_error($connection);
}

function db_quote($value) {
    $connection = db_connect();
    return "'" . mysqli_real_escape_string($connection,$value) . "'";
}
// Reference: https://www.binpress.com/tutorial/using-php-with-mysql-the-right-way/17

?>

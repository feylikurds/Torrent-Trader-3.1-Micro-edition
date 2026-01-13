<?php

function getDBConnection () {
    include("mysql.php"); # contains the given DB setup $database, $dbhost, $dbuser, $dbpass
    
    $conn = ($GLOBALS["DBconnector"] =  mysqli_connect($mysql_host, $mysql_user, $mysql_pass));
    if (!$conn) {
            echo "Connection to DB was not possible!";
            end;
        }
        if (!mysqli_select_db( $conn,$mysql_db)) {
            echo "No DB with that name seems to exist on the server!";
            end;
        }
        return $conn;
}

# establishes a connection to a mySQL Database accroding to the details specified in settings.php
function his_getDBConnection () {
    include("mysql.php"); # contains the given DB setup $database, $dbhost, $dbuser, $dbpass
    $conn = ($GLOBALS["DBconnector"] = mysqli_connect($mysql_host, $mysql_user, $mysql_pass));
    if (!$conn) {
            echo "Connection to DB was not possible!";
            end;
        }
        if (!mysqli_select_db( $conn,$mysql_db)) {
            echo "No DB with that name seems to exist at the server!";
            end;
        }
        return $conn;
}
?>
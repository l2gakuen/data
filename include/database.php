<?php

//DATABASE
$sql_serveur = "localhost"; //Nom du host
$sql_login = "root";  // Nom d'utilisateur pour la base de donnÈe
$sql_pass = "root";      // Le mot de passe  pour la base de donnÈe
$sql_bdd = "";  // Nom de la base de donnÈe*/  


$useMySQL = false;
$connection = $useMySQL ?  mysqli_connect($sql_serveur, $sql_login, $sql_pass, $sql_bdd) : sqlite_open('include/database.db', SQLITE3_OPEN_READWRITE);

if ($useMySQL == false) { 
    //recreate missing MYSQL <-> SQLITE3 functions
    sqlite_create_function($connection, 'DATEDIFF');
    sqlite_create_function($connection, 'DAYOFWEEK');
    sqlite_create_function($connection, 'DATE_FORMAT', 'DATEFORMAT');
    sqlite_create_function($connection, 'WEEKDAY');
    sqlite_create_function($connection, 'SUBDATE');
    sqlite_create_function($connection, 'NOW');
    sqlite_create_function($connection, 'RAND', 'MY_RAND');
}
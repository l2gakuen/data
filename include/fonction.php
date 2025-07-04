<?php

setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
$localFolder = "/www/pop2022/";

/****************************************
    SWAP DATABASES
 ****************************************/

function fetch_array($sql)
{
    global $useMySQL;
    if ($useMySQL) {
        return mysqli_fetch_array($sql);
    } else {
        return sqlite_fetch_array($sql);
    }
}


$ContextOpt = array( //File_get_contents on HTTPS servers
    "ssl" => array(
        "verify_peer" => false,
        "verify_peer_name" => false,
    ),
);

/****************************************
    Pour manipuler du texte trop long...
 ****************************************/

function mk_text($longText, $chars, $alt_or_title = false)
{
    if ($alt_or_title) {
        return $longText;
    } else {
        return html2plain(strlen($longText) < $chars ? $longText : substr($longText, 0, $chars) . '...');
        return;
    }
}
function html2plain($html)
{
    return preg_replace("/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($html))));
}
/**************************************
    Pour manipuler les liens en PHP
    Current Page / RootPage / ThatPage
 ****************************************/

function isLocalhost()
{
    return $_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '10.147.17.18' || $_SERVER['HTTP_HOST'] == '192.168.1.14'|| $_SERVER['HTTP_HOST'] == 'localhost'|| $_SERVER['HTTP_HOST'] == '127.0.0.1' ? true : false;
}

function curPageURL()
{
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    // if ($_SERVER["SERVER_PORT"] != "80") {
    // $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    // } else {
    $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    // }
    return $pageURL;
}

function rootURL()
{
    global $localFolder;
    $rootURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $rootURL .= "s";
    }
    $rootURL .= "://";
    $rootURL .= $_SERVER["SERVER_NAME"];
    return isLocalhost() ? $rootURL . $localFolder : $rootURL;
}
 
function thatURL()
{
    $thatURL = $_SERVER["REQUEST_URI"];
    return $thatURL;
}

function pathURL()
{
    return preg_replace('/[&|?](.*)(\d+)/i', '', curPageURL());
}

/**************************************
    Pour manipuler l'URL propre
    si HTACCESS n'existe pas
 ****************************************/
// $is_htaccess = file_exists('.htaccess');
$is_htaccess = false;
function url($module, $isManager = true)
{ 
    global $is_htaccess;
    if ($is_htaccess && !isLocalhost()) {
        return rootURL() . '/' . ($isManager ? "/pop2022" : "") .  $module;
    } else {
        return rootURL() . ($isManager ? "/pop2022" : "") . "/index.php?mod=" . $module;
    }
}

/**************************************
    Selecteur d'image
    retourne une url propre si htaccess 

    .htaccess ----> ^safeimage/([^/\.]+)/([0-9]+)/([0-9]+)/([0-9A-Za-z\\/_.()\d-]+)$ /image.php?effect=$1&w=$2&h=$3&f=$4
 ****************************************/

function IMAGE($effect, $width, $height, $img_with_fullpath)
{
    global $is_htaccess;
    if (!$is_htaccess || isLocalhost()) {
        return 'image.php?effect=' . $effect . '&w=' . $width . '&h=' . $height . '&f=' . urlencode($img_with_fullpath);
    } else {
        //return 'image/'.$effect.'/'.$width.'/'.$height.'/'.$img_with_fullpath;
        return 'safeimage/' . $effect . '/' . $width . '/' . $height . '/' . $img_with_fullpath;
    }
}

/* IS MOBILE ?*/
// function isMobile()
// {
//     return preg_match("/\b(?:a(?:ndroid|vantgo)|b(?:lackberry|olt|o?ost)|cricket|docomo|hiptop|i(?:emobile|p[ao]d)|kitkat|m(?:ini|obi)|palm|(?:i|smart|windows )phone|symbian|up\.(?:browser|link)|tablet(?: browser| pc)|(?:hp-|rim |sony )tablet|w(?:ebos|indows ce|os))/i", $_SERVER["HTTP_USER_AGENT"]);
//     /* true */
// }
function isMobile()
{
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pad|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

/********************************
 *
 *
 *   HELPERS FONCTIONS
 *
 *
 *********************************/

function UPDATE($table_name, $form_data, $where_clause = '', $debug = false)
{
    global $connection;
    global $useMySQL;
    $whereSQL = '';
    if (!empty($where_clause)) {
        if (substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE') {
            $whereSQL = " WHERE " . $where_clause;
        } else {
            $whereSQL = " " . trim($where_clause);
        }
    }
    $sql = "UPDATE " . $table_name . " SET ";
    $sets = array();
    foreach ($form_data as $column => $value) {
        $sets[] = "`" . $column . "` = '" . $value . "'";
    }
    $sql .= implode(', ', $sets);
    $sql .= $whereSQL;

    // return mysqli_query($connection, $sql);
    // echo '<pre>'.$sql.'</pre>';

    //SI DEBUG MODE
    if ($debug) {
        //PRINT ONLY THE QUERY
        echo $sql . '<br>';
    } else {
        //RUN QUERY
        return $useMySQL ? mysqli_query($connection, $sql) : sqlite_query($connection, $sql);
    }
}

function INSERT($table_name, $form_data, $debug = false)
{
    //Login stuffs
    global $connection;
    global $useMySQL;
    // retrieve the keys of the array (column titles)
    $fields = array_keys($form_data);
    // build the query
    $sql = "INSERT INTO " . $table_name . "
    (`" . implode('`,`', $fields) . "`)
    VALUES ('" . implode("','", $form_data) . "')";

    //SI DEBUG MODE
    if ($debug) {
        //PRINT ONLY THE QUERY
        echo $sql . '<br>';
    } else {
        //RUN QUERY
        return $useMySQL ? mysqli_query($connection, $sql) : sqlite_query($connection, $sql);
    }
}
function SELECT($table_name, $select = '*', $where_clause = '', $debug = false)
{
    global $connection;
    global $useMySQL;
    // check for optional where clause
    $whereSQL = '';
    if (!empty($where_clause)) {
        // check to see if the 'where' keyword exists
        // if (substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE') {   // not found, add key word
        //     $whereSQL = " WHERE " . $where_clause;
        // } else {
        //     $whereSQL = " " . trim($where_clause);
        // }
        $whereSQL = " " . trim($where_clause);
    }
    // start the actual SQL statement
    $sql = "SELECT " . $select . " FROM " . $table_name . " ";
    // append the where statement
    $sql .= $whereSQL;

    /*
        FIX MINOR DIFFERENCES - MYSQL ==> SQLITE
    */

    if ($useMySQL == false) {
        $searchMySQL    = array(
            'RAND()',
            'NOW()',
            '%k',
            '%u'
        );
        $replaceSQLite  = array(
            'RANDOM()',
            '"now"',
            '%H',
            '%W'
        );

        $sql = str_replace($searchMySQL, $replaceSQLite, $sql);

        $search  = array(
            '/(DATEDIFF)\((.*),(.*)\)/',
            '/DAYOFWEEK\(([^\)]+)\)/',
            '/WEEKDAY\(([^\)]+)\)/',

            '/MONTH\(([^\)]+)\)/',
            '/YEAR\(([^\)]+)\)/',
            '/DAY\(([^\)]+)\)/',
            '/DATE_FORMAT\(([^\)]+),([^\)]+)\)/',
            '/SUBDATE\(([^\)]+),([^\)]+)\)/'
        );
        $replace = array(
            "CAST(julianday($2) - julianday($3) As Integer)",
            "CAST(strftime('%w', $1) As Integer)",
            "CAST(strftime('%w', $1) As Integer)-1",

            "CAST(strftime('%m', $1) As Integer)",
            "CAST(strftime('%Y', $1) As Integer)",
            "CAST(strftime('%d', $1) As Integer)",
            "strftime($2, $1)",
            "datetime($2, '-' || $1 || ' days')"
        );
        $sql = trim(preg_replace($search, $replace, $sql));
    }

    //SI DEBUG MODE
    if ($debug) {
        //PRINT ONLY THE QUERY
        echo $sql . '<br>';
    } else {
        //RUN QUERY
        return $useMySQL ? mysqli_query($connection, $sql) : sqlite_query($connection, $sql);
    }
    // run and return the query result
    //return mysqli_query($connection, $sql);
}

function DELETE($table_name, $where_clause = '', $debug = false)
{
    //Login stuffs
    global $connection;
    global $useMySQL;
    // check for optional where clause
    $whereSQL = '';
    if (!empty($where_clause)) {
        // check to see if the 'where' keyword exists
        if (substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE') {
            // not found, add keyword
            $whereSQL = " WHERE " . $where_clause;
        } else {
            $whereSQL = " " . trim($where_clause);
        }
    }
    // build the query
    $sql = "DELETE FROM " . $table_name . $whereSQL;
    // run and return the query result resource
    //SI DEBUG MODE
    if ($debug) {
        //PRINT ONLY THE QUERY
        echo $sql . '<br>';
    } else {
        //RUN QUERY
        return $useMySQL ? mysqli_query($connection, $sql) : sqlite_query($connection, $sql);
    }
}

function ENCRYPT($simple_string, $encryption_key = "pop2022@&é'(§è(§èè!çà&&é", $encryption_iv = '1234567891011121', $ciphering = "AES-128-CTR", $options = 0)
{

    // Use OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);

    // Use openssl_encrypt() function to encrypt the data
    $encryption = openssl_encrypt(
        $simple_string,
        $ciphering,
        $encryption_key,
        $options,
        $encryption_iv
    );
    return $encryption;
}

function DECRYPT($encryption, $decryption_key = "pop2022@&é'(§è(§èè!çà&&é", $decryption_iv = '1234567891011121', $ciphering = "AES-128-CTR", $options = 0)
{

    // Use OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);

    // Use openssl_decrypt() function to decrypt the data
    $decryption = openssl_decrypt(
        $encryption,
        $ciphering,
        $decryption_key,
        $options,
        $decryption_iv
    );
    return $decryption;
}

function CLEAN($string)
{
    global $connection;
    global $useMySQL;
    //Cleanup ! Add more safety to this !
    $sanitized_data = $useMySQL ? mysqli_real_escape_string($connection, (trim($string))) : sqlite_escape_string(trim($string));
    // $sanitized_data = mysqli_real_escape_string($connection, htmlentities(htmlspecialchars($string)));
    return $sanitized_data;
}

function CLEANCHARS($string, $clean = true)
{
    if ($clean) {

        /***********
         * 
         * ENCODE 
         * 
         **********/
        //Accents and <tags> -- BEFORE
        // $string = htmlspecialchars($string, ENT_QUOTES);
        //-- THEN Prevent backslash escaping in json multiline string
        $string = str_replace(
            array("\r\n"),
            array("<br>"),
            $string
        );


        $string = preg_replace(
            array('/<(font) (color)="(.*?)">(.*?)<\/(font)>/', '/<(p) (align)="(.*?)">(.*?)<\/(p)>/'),
            array('<span style="color:$3">$4</span>', '<p class="text-$3">$4</p>'),
            $string
        );
        $string = htmlspecialchars($string, ENT_QUOTES);
    } else {

        /***********
         * 
         * DECODE : REVERSE 
         * 
         **********/
        //false
        $string = str_replace(
            array("<br>"),
            array("\r\n"),
            $string
        );


        $regex_get_font_tag_color = '/<font color="(.*?)">(.*?)<\/font>/';
        $string = urldecode($string);
        $string = htmlspecialchars_decode($string);
    }

    //More to go
    return $string;
}

function base64toImage($base64_string, $output_file)
{
    echo $base64_string . '***' . $output_file;
}
function NORMALIZE_ACCENTS($string)
{
    return str_replace(
        array(' ', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'í', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'š', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý'),
        array('_', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 's', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y'),
        $string
    );
}

function UPLOAD($file, $dest, $customName = "")
{
    $destination =  $dest;
    $destination2 = "images/photo";
    $date = gmdate("yis");
    $tmp_name1 = $file["tmp_name"];
    $name1 = $file["name"];

    //get file original extention
    $ext = pathinfo($name1);

    //NAAANIIIIII ???!
    $dest1 = $destination . "/" . (!empty($customName) ? $customName : $name1 . $date); // -- chemin pour la copie du fichier
    $output = NORMALIZE_ACCENTS($dest1);

    if (!file_exists($destination)) {
        mkdir($destination);
    }


    // if ($b64) {
    // $data = explode(',', $file);
    // return base64toImage(base64_decode($data[1]), $dest1);
    // } else {
    return move_uploaded_file($tmp_name1, $output); //returns true or false
    // }
    //return  $dest1;	
}




function in_array_any($needles, $haystack)
{
    return !empty(array_intersect($needles, $haystack));
}

/*
    Liste les "?query=&query2=" pour mieux gérer les url dynamiques
        EX: une page "produits.php?cat=1id=12" avec un bouton retour vers "produits.php?cat=1" ou "produits.php"
        c'est donc l'URL actuelle MOINS la query "cat" ou "id".

*/
function allURLqueries($minus = [], $str = false)
{
    //get all queries
    $strQuery = parse_url($str ? $str : curPageURL(), PHP_URL_QUERY); //cat=3&pag=4
    $arrQuery = explode('&', $strQuery); //[0]cat=3 [1]pag=4
    $urlQueries = [];

    if (!empty($strQuery)) {
        foreach ($arrQuery as $i => $j) {
            $q  = explode('=', $j);
            if (!in_array($q[0], $minus)) {
                $urlQueries[] = $q[0] . (!empty($q[1]) ? '=' . $q[1] : '');
            }
        }
        return rootURL() . '?' . implode('&', $urlQueries);
    } else {

        return curPageURL();
    }
}


/** 
 * RE-REMPLISSAGE des inputs apres un POST
 * 
 **/
function FillPostValue($field)
{
    return (isset($_POST[$field]) ? $_POST[$field] : '');
}

function EVENT_TYPE($type)
{
    switch ($type) {
        case '0':
            $string = 'Facile';
            $class  = ' green';
            break;
        case '1':
            $string = 'Moyen';
            $class  = ' orange';
            break;
        case '2':
            $string = 'Expérimenté';
            $class  = ' red';
            break;
        case '3':
            $string = 'Repas';
            $class  = ' blue';
            break;
        default:
            $string = '---';
            $class  = ' green';
            break;
    }
    return array($string, $class);
};


function MAILER($to, $subject, $content, $sender = array('Nom', 'ne.pas.repondre@plaisir-rando2p.re'))
{
    ## -- envoie du mail

    //    $message  = nl2br($content);
    $headers  = 'MIME-Version: 1.0' . "\r\n"; // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'From: ' . $sender[0] . ' <' . $sender[1] . '>' . "\r\n"; // En-têtes additionnels

    $message = nl2br($content);


    ## -- On envoie le mail
    return mail($to, $subject, $message, $headers);    //-- on envoie le mail 1 
}


function sendMail($to, $subject, $message, $from)
{
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "Return-Path: $from\r\n";
    $headers .= "X-Mailer: PHP\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "Date: " . date('r', time()) . "\r\n";
    $headers .= "Message-ID: <" . time() . rand(1, 1000) . "@" . $_SERVER['SERVER_NAME'] . ">\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= "X-MSMail-Priority: Normal\r\n";
    $headers .= "X-Mailer: Microsoft Office Outlook, Build 11.0.5510\r\n";
    $headers .= "X-MimeOLE: Produced By Microsoft MimeOLE V6.00.2800.1441\r\n";
    $headers .= "X-Sender: $from\r\n";
    return mail($to, $subject, $message, $headers);
}

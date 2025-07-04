<?php
header('Content-Type: text/html; charset=utf-8');
// error_reporting(E_ERROR | E_PARSE);
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

//Script starts NOW, start counting...
$start = microtime(true);

$isManager = false;

// echo "QQ1x";
require('include/sqlite.php');
// echo "QQ2";
require('include/database.php');
// echo "QQ3";
require('include/parse.php');
// echo "QQ4";
require('include/fonction.php');
require('include/dafunc.php');
require('include/class.magic-min.php');
require('include/fpdm/fpdm.php');
// require('include/fpdf/fpdf.php');




function is_base64($s)
{
    return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
}

$phpFileUploadErrors = array(
    0 => 'There is no error, the file uploaded with success',
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'The uploaded file was only partially uploaded',
    4 => 'No file was uploaded',
    6 => 'Missing a temporary folder',
    7 => 'Failed to write file to disk.',
    8 => 'A PHP extension stopped the file upload.'
);

$phpFileUploadErrorsInFrench = array(
    0 => 'Aucune erreur, le fichier a été envoyé avec succès',
    1 => 'Le fichier téléchargé dépasse la taille maximale autorisée par le serveur',
    2 => 'Le fichier téléchargé dépasse la taille maximale autorisée par le formulaire',
    3 => 'Le fichier a été partiellement envoyé',
    4 => 'Aucun fichier n\'a été envoyé',
    6 => 'Il manque un dossier temporaire',
    7 => 'Echec de l\'écriture du fichier sur le disque',
    8 => 'Une extension PHP a arrêté l\'envoi de fichier.'
);

//unset($_SESSION['panier']);
// unset($_SESSION['panier']['2246']);



//SETUP TEMPLATE
$html     = str_get_html(file_get_contents('index_manager.html'));
$head     = $html->find('head', 0);
$body     = $html->find('body', 0);
$main     = $body->find('.main', 0);
$cards    = $html->find('.cards', 0);
$card     = $cards->find('.card', 0);
$checker  = $html->find('[id=checker]', 0);

$SCRIPTS = (string) null;

//SETUP PANIER
$panier = isset($_SESSION['panier']) ? $_SESSION['panier'] : [];

//LOGIN 
if (!isset($_SESSION["access"])) {
    $login   = file_get_html('login.html');
    $loading = file_get_html('loading.html');

    foreach ($login->find('script') as $script) {
        $script->{'src'} .= "?v=" . date('his');
    }
    if (isset($_POST["logon"])) {
        $_CHECK_USER = SELECT('utilisateurs', 'id, password, type, name', 'WHERE email="' . CLEAN($_POST['email']) . '"', false);

        if ($_CHECK_USER) {
            $Results = fetch_array($_CHECK_USER);
            if (password_verify($_POST['password'], $Results['password'])) {
                //Login Good
                //Create Sessions
                $_SESSION["access"] = true;
                $_SESSION['userID'] = $Results['id'];
                $_SESSION['type']   = $Results['type'];
                $_SESSION['name']   = $Results['name'];

                echo minify_html($loading);
                //JS ON
                header("HTTP/1.1 202 Accepted"); //202 = ACCEPTED, js listens to 202 then refresh
                //JS OFF
                header('refresh:2;url=' . curPageURL()); //PHP refresh
                //Legacy Code
                // echo '<script>setTimeout(function(){ window.location = "' . url('participer') . '"; }, 200);</script>';

                //Safe Exit
                exit;
            } else {
                //Login Bad
                $checker->innertext = 'Mot de passe incorrect';
                $checker->innertext = 'Identifiants incorrects';
                $LOGS_ARRAY = array(
                    'date' => date('Y-m-d H:i:s'),
                    'info' => 'Attack',
                    'cmd'  => ENCRYPT(json_encode(array(
                        'ip'   => $_SERVER['REMOTE_ADDR'],
                        'url'  => $_SERVER['REQUEST_URI'],
                        'user' => $_SERVER['HTTP_USER_AGENT'],
                        'post' => $_POST,
                        'get' => $_GET
                    ))),
                );
                $INSERT_LOG = INSERT('logs', $LOGS_ARRAY, false);
            }
        } else {
            //Login Bad
            $checker->innertext = 'Identifiants incorrects';
            $LOGS_ARRAY = array(
                'date' => date('Y-m-d H:i:s'),
                'info' => 'Attack',
                'cmd'  => ENCRYPT(json_encode(array(
                    'ip'   => $_SERVER['REMOTE_ADDR'],
                    'url'  => $_SERVER['REQUEST_URI'],
                    'user' => $_SERVER['HTTP_USER_AGENT'],
                    'post' => $_POST,
                    'get' => $_GET
                ))),
            );
            $INSERT_LOG = INSERT('logs', $LOGS_ARRAY, false);
        }
    }
    // if (isset($_POST["logon"])) {
    //     if ($_POST["email"] == $user && $_POST["password"] == $pass) {
    //         $_SESSION["access"] = true;
    //         $_SESSION["type"]   = 1;
    //         header("HTTP/1.1 202"); //202 = ACCEPTED, js refresh

    //         header('refresh:0;url=' . curPageURL()); //PHP refresh
    //     } else {
    //         foreach ($login->find('.field') as $field) {
    //             $field->{'style'} = "border:1px solid red";
    //         }
    //     }
    // }
    echo minify_html($login);
    exit;
}
//LOGOFF
if (isset($_GET["logoff"])) {
    session_destroy();
    header('refresh:0;url=' . rootURL()); //PHP refresh
    exit;
}
//Align FORMS dynamically
foreach ($html->find('form') as $form) {
    $form->{'action'} = curPageURL();
}


/* MODULES CHANGES */
$module     = (isset($_GET['mod']) ? $_GET['mod'] : "manager_home");
$incmod     = "modules/" . $module . ".php";
// $mod        = (isset($_GET['mod']) ? $_GET['mod'] : "manager_home"); //dafuk

if (file_exists($incmod) && file_exists('pages/' . $module . '.html')) {

    $template   = MODULE($module);
    $main->{'class'} .= ' ' . $module;
    //PHP FILE
    include($incmod);
    // $page->innertext = $template;
}



//Conditions
if (isset($_POST) && !empty($_POST)) {

    /**
     * 
     *  POST
     * 
     */


    if (isset($_POST['action'])) {

        //Edit Mentions and general HTML
        if ($_POST['action'] == "editMentions" || $_POST['action'] == "editHTML") {
            $qq = [];
            foreach ($_POST as $name => $value) {
                if ($name == "role" || $name == "action") {
                    //WTF I can't reverse it ?
                } else {
                    $qq[$name] = base64_encode($value);
                }
            }
            $homeData = array(
                'content' => json_encode($qq, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE  | JSON_UNESCAPED_SLASHES)
            );
            $update = UPDATE('contents', $homeData, 'WHERE role="' . $_POST['role'] . '"');
            if ($update) {
                $checker->addClass('green');
                $checker->innertext = "Les données ont bien été modifiées.";
            }
        }

        /* Edit Theme Couleur */
        if ($_POST['action'] == "editHome") {
            $qq = [];
            foreach ($_POST as $name => $value) {
                if ($name == "role" || $name == "action") { //Ignore useless inputs
                    //WTF I can't reverse it ?
                } else {
                    $qq[$name] = CLEANCHARS(trim($value));
                }
            }
            $homeData = array(
                'content' => json_encode($qq, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );

            // echo "<pre>";
            // print_r($homeData['content']);
            // echo "</pre>";
            // exit;
            $update = UPDATE('contents', $homeData, 'WHERE role="' . $_POST['role'] . '"');

            if ($update) {
                $checker->innertext = "Le Contenu à été modifié avec succès.";
                $checker->{'class'} .= ' green';
            }
            // if (mysqli_affected_rows($connection) > 0) {
            //     $checker->{'class'} = "green";
            //     $checker->innertext = "Le contenu à bien été modifié.";
            // } else {
            //     $homeData['role'] = $_POST['role'];
            //     $create = INSERT('contents', $homeData);
            // }
        }
        /* Edit Images ici et là */
        if ($_POST['action'] == "changeimage") {
            if ($_FILES) {

                $safeoutput = array(
                    0  => "../upload/gallery/0",
                    1  => "../upload/gallery/1",
                    10 => "../upload/images/" . $_POST['role'] . '/'
                );

                $output   = (isset($_POST['output']) && in_array($_POST['output'], $safeoutput) ? $safeoutput[$_POST['output']] : $safeoutput[10]); //"../upload/images/"

                // echo $output;
                $clearput = str_replace('../', '', $output);

                foreach ($_FILES as $index => $file) {
                    if ($_FILES[$index]['size'] == 0 && $_FILES[$index]['error'] == 0) {
                        // Same bullshit here
                    } else {

                        $ext        = pathinfo($_FILES[$index]['name']);
                        $UPLOADFILE = UPLOAD($_FILES[$index], $output, $_POST['role'] . date('Ymdi') . '.' . $ext['extension']);
                        if ($UPLOADFILE) {
                            $sql = 'update contents set content = JSON_SET(content, "$.' . $_POST['role'] . '", "' . $clearput . $_POST['role'] . date('Ymdi') . '.' . $ext['extension'] . '") where role = "images"';
                            $update = mysqli_query($connection, $sql);
                            // echo $sql;
                            // if($update){
                            //     // print_r($_FILES[$index]);
                            //     // echo "updated";
                            // }
                        }
                        if ($_FILES[$index]['size'] == 0) {
                            $sql = 'update contents set content = JSON_SET(content, "$.' . $_POST['role'] . '", "' . $_POST['selectimage'] . '") where role = "images"';
                            $update = mysqli_query($connection, $sql);
                        }
                    }
                }
            }
        }
        if ($_POST['action'] == "selectImage") {

            if ($_FILES  && $_FILES['image']['error'] == 0) {
                $path               = 'upload/' . (CLEAN(isset($_POST['role']) ? $_POST['role'] : 'hero')) . '/';

                $ext        = pathinfo($_FILES['image']['name']);
                $UPLOADFILE = UPLOAD($_FILES['image'], '../' . $path, $_POST['role'] . date('Ymdis') . '.' . $ext['extension']);

                if ($UPLOADFILE) {
                    $checker->innertext =  "Le fichier " . $_FILES['image']['name'] . " à bien été uploadé.";
                    $checker->{'class'} .= " green";
                } else {
                    $checker->innertext = "something went wrong";
                }
            } else {
                // echo "<pre>";
                // echo (json_encode($_POST['images'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                // echo "</pre>";

                // $UPDATE_IMAGES = UPDATE(
                //     'contents',
                //     array('content' => json_encode(array(
                //         "introimage" => $_POST['images']
                //     ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                //     'WHERE role="images"',
                //     true
                // );
                //OMG SQL REALLY?

                // $UPDATE_IMAGES = UPDATE('contents', array(
                //     'content' => 'json_set(content, \'$.introimages\', json(\'["1", "2","3"]\'))',
                // ), 'WHERE "role" = "images"', true);

                $SQL = "update contents set content = JSON_SET(content, '$." . CLEAN($_POST['role']) . "', json('" . json_encode($_POST['images']) . "')) where role = 'images'";

                //execute query
                $result = sqlite_query($connection, $SQL);
                if ($result) {
                    $checker->innertext = "Selection d'image modifiée avec succès";
                    $checker->{'class'} .= ' green';
                } else {
                    $checker->innertext = 'Une erreur est survenue';
                    $checker->{'class'} .= ' red';
                }

                echo $SQL;

                // UPDATE contents set "content" = json_set(content, '$.introimages', json('["1", "2","3"]')) WHERE "role" = "images";



            }
        }
        if ($_POST['action'] == "deleteImage") {
            $sql    = 'update contents set content = JSON_REMOVE(content, "$.' . $_POST['role'] . '") where role = "images"';
            $delete = mysqli_query($connection, $sql);
            // if($delete){

            // }
        }
    }
} else {

    /**
     * 
     *  GET
     * 
     */

    if (isset($_GET) && !empty($_GET)) {
    }
}

/**
 * 
 *  BOTH
 * 
 */


$navigation = array(
    'home' => array(
        'title' => "Factures",
        'desc'  => "Edition de Facture",
        'url'   => "manager_home",
        'icon'  => 'fal fa-file-invoice-dollar',
        'inner' => 'Factures'
    ),
    'articles' => array(
        'title' => "Articles",
        'desc'  => "Gestion des Articles",
        'url'   => "manager_articles",
        'icon'  => 'fal fa-boxes',
        'inner' => 'Articles'
    ),
    'lycees' => array(
        'title' => "Etablissements",
        'desc'  => "Listes des Etablissements",
        'url'   => "manager_lycees",
        'icon'  => 'fal fa-graduation-cap',
        'inner' => 'Etablissements'
    ),
    // 'factures' => array(
    //     'title' => "Messages",
    //     'desc'  => "Factures Clients",
    //     'url'   => "manager_factures",
    //     'icon'  => 'fal fa-envelope',
    //     'inner' => 'Factures'
    // ),
    //     'messages' => array(
    //     'title' => "Messages",
    //     'desc'  => "Formulaire de Contact",
    //     'url'   => "manager_messages",
    //     'icon'  => 'fal fa-envelope',
    //     'inner' => 'Messagerie'
    // ),
    // 'planning' => array(
    //     'title' => "Planning évènements",
    //     'desc'  => "Randonnées et évènements",
    //     'url'   => "manager_planning",
    //     'icon'  => 'fal fa-calendar',
    //     'inner' => 'Évènements'
    // ),
    // 'users' => array(
    //     'title' => "Utilisateurs",
    //     'desc'  => "Gestion des Utilisateurs",
    //     'url'   => "manager_users",
    //     'icon'  => 'fal fa-users', 
    //     'inner' => 'Adhérents'
    // ),
    // 'chantiers' => array(
    //     'title' => "Chantiers",
    //     'desc'  => "--",
    //     'url'   => "manager_chantiers",
    //     'icon'  => 'fal fa-user-hard-hat',
    //     'inner' => 'Chantiers'
    // ),
    // 'theme' => array(
    //     'title' => "Galerie",
    //     'desc'  => "Galerie Photo",
    //     'url'   => "manager_galerie",
    //     'icon'  => 'fal fa-photo-video',
    //     'inner' => 'Galerie Photo'
    // ),
    // 'apropos' => array(
    //     'title' => "À Propos",
    //     'desc'  => "Page",
    //     'url'   => "manager_apropos",
    //     'icon'  => 'fal fa-info',
    //     "inner" => "À Propos"
    // ),
    // 'mention' => array(
    //     'title' => "Mentions & Conditions",
    //     'desc'  => "Mentions et Conditions du Site",
    //     'url'   => "manager_mention",
    //     'icon'  => 'fal fa-gavel',
    //     'inner' => 'Mentions Légales'
    // ),
    // 'mention2' => array(
    //     'title' => "Conditions Ventes",
    //     'desc'  => "Conditions Générales de vente",
    //     'url'   => "manager_mention2",
    //     'icon'  => 'fal fa-mask',
    //     'inner' => 'Protection des données'
    // ),
    // 'navigation' => array(
    //     'title' => "Navigation",
    //     'desc'  => "Menu général du Site",
    //     'url'   => "manager_menu",
    //     'icon'  => 'fal fa-ship',
    //     'inner' => 'Navigation'
    // ),
    // 'general' => array(
    //     'title' => "Paramètres Généraux",
    //     'desc'  => "Informations générales du Site",
    //     'url'   => "manager_general",
    //     'icon'  => 'fal fa-cogs',
    //     'inner' => 'Général'
    // ),
    // 'magic' => array(
    //     'title' => "Crop Image",
    //     'desc'  => "Traitement des images",
    //     'url'   => "manager_image",
    //     'icon'  => 'fal fa-wand-magic',
    //     'inner' => "Traitement des images"
    // ),
    // ,
    // 'magic' => array(
    //     'title' => "Editeur",
    //     'desc'  => "Experimental",
    //     'url'   => "manager_editor",
    //     'icon'  => 'fal fa-wand-magic'
    // )
);


/***********************************
 * 
 *      GENERAL NAVIGATION
 * 
 ***********************************/

if (1 < 2) {
    $NAVSTACK = "";
    $navul  = $html->find('.navigation ul', 0);
    $navli  = $navul->find('li', 1);
    $title  = (string) null;
    foreach ($navigation as $nav) {
        $navli->find('.title',   0)->innertext = $nav['title'];
        $navli->find('a',        0)->{'href'}  = url($nav['url']);
        $navli->find('a',        0)->{'title'} = $nav['desc'];
        $navli->find('.icon i ', 0)->{'class'} = $nav['icon'];
        $navli->{'class'}        = ($module == $nav['url'] ? 'hovered' : null);

        $_EXPORT = array($module, $nav['url'], ($module == $nav['url'] ? $nav['inner'] : "null"));
        //SETUP Page Title
        if ($module == $nav['url']) {
            $title = $nav['inner'];
        }
        $navli->find('.icon', 0)->{'data-after'} = (function ($_EXPORT) {
            $_COUNT = null;
            switch ($_EXPORT[1]) {
                case 'manager_messages':
                    $_COUNTMSGS = SELECT('messages', 'COUNT(*) as count', 'WHERE status=0', false);
                    if ($_COUNTMSGS) {
                        while ($DATA = fetch_array($_COUNTMSGS)) {
                            $_COUNT = $DATA['count'] == 0 ? null : $DATA['count'];
                        }
                    }
                    break;

                case 'manager_planning':
                    $_COUNTPLANNING = SELECT('agendas', 'COUNT(*) as count', 'WHERE MONTH(start) = MONTH(NOW()) AND status=1', false);
                    if ($_COUNTPLANNING) {
                        while ($DATA = fetch_array($_COUNTPLANNING)) {
                            $_COUNT = $DATA['count'];
                        }
                    }
                    break;

                case 'manager_users':
                    $_COUNTUSERS = SELECT('adherents', '(SELECT queue_cotisations.status FROM queue_cotisations WHERE userID = adherents.id ORDER BY date DESC LIMIT 1) as queue_status', 'WHERE status>0 AND queue_status=0', false);
                    if ($_COUNTUSERS) {
                        while ($DATA = fetch_array($_COUNTUSERS)) {
                            $_COUNT++;
                        }
                    }
                    break;
                default:
                    $_COUNT = null;
                    break;
            }
            return $_COUNT;
        })($_EXPORT);
        $NAVSTACK .= $navli;
    }
    $navli->outertext = $NAVSTACK;
    $html->find('.modtitle', 0)->innertext = $title . (isset($_SESSION['name']) ? ' <span class="text-gr-1"> (' . $_SESSION['name'] . ')<span>' : "");
}



$page = $html->find('[id=page]', 0);
$page->innertext .= $template;

/***************
 *   MINIFY
 ***************/

$minified = new Minifier(
    array(
        'gzip'    => false,
        'closure' => false,
        'echo'    => false
    )
);
$exclude_styles = array(
    'css/svg.css'
);
$exclude = array(
    'js/exclude.js'
);

$head->find('[rel=stylesheet]', -1)->{'href'} .= '?v=' . date('myis');
$body->find('script[src]',      -1)->{'src'}   = $minified->merge('js/packed.min.js',  'js',  'js',  $exclude) . '?v=' . date('myis');

//REPARSE
$html = str_get_html($html);

// $mixQuery   = mixQuery();
$SELECTCONTENTS = SELECT('contents', '*', 'WHERE 1');
$SELECTTYPE     = SELECT('produits', 'DISTINCT type', 'WHERE 1');
$SELECTCATG     = SELECT('produits', 'DISTINCT categorie', 'WHERE categorie<>"" ORDER BY categorie ASC');


$CONTENT = [];

function statusFromID($id)
{
    switch ($id) {
        case 0:
            $status = "zero";
            break;
        case 1:
            $status = "one";
            break;
        case 2:
            $status = "two";
            break;
        default:
            $status = "none";
    }
    return "status " . $status;
}

if ($SELECTCONTENTS) {
    /**
     * PASTES EVERY PAGES CONTENT
     */
    while ($data = fetch_array($SELECTCONTENTS)) {
        $CONTENT[$data['role']] = $data;
    }
    foreach ($CONTENT as $ind => $val) {
        $cms = json_decode($CONTENT[$ind]['content'], true);
        foreach ($cms as $ctname => $ctvalue) {
            $target = $html->find('[name=' . $ctname . ']', 0);
            // $targets = $html->find('[name=' . $ctname . ']');
            if ($target) {
                // foreach ($targets as $targer) {
                if ($target->tag == "textarea") {
                    $target->innertext = str_replace('<br>', "\r\n", CLEANCHARS($ctvalue, false)); //(is_base64($ctvalue) ? base64_decode($ctvalue) : $ctvalue)); //($ctvalue);
                } elseif ($target->tag == "input") {
                    $target->value = CLEANCHARS($ctvalue, false);
                } elseif ($target->tag == "div") {
                    // $target->innertext = str_replace('<br>', "\r\n", CLEANCHARS($ctvalue, false)); //(is_base64($ctvalue) ? base64_decode($ctvalue) : $ctvalue)); //($ctvalue);
                    $target->innertext = str_replace('<br>', "\r\n", CLEANCHARS((is_base64($ctvalue) ? base64_decode($ctvalue) : $ctvalue), false)); //; //($ctvalue);
                }
                if ($target->{'class'} == "editor") {
                    $html->find('[data-edit=#' . $target->id . ']', 0)->innertext = (is_base64($ctvalue) ? base64_decode($ctvalue) : $ctvalue);
                }
                // }
            }
        }
    }
    $images = json_decode($CONTENT['images']['content'],  true);
    foreach ($images as $name => $value) {
        $t = $html->find('.' . $name, 0);
        if ($t) {
            $html->find('.' . $name, 0)->{'src'} = "../images.php?w=150&h=150&src=" . $value;
        }
    }
}
foreach ($html->find('.selectImage') as $select) {
    $directory = "upload/" . $select->{'data-folder'}; // path to loop images folder
    $role      = $select->parent()->find('[name=role]', 0)->value; //Path to uploaded folder also
    $images    = glob('{' . '../' . $directory . ',../upload/images/' . $role . '}/*.{jpg,jpeg,png,gif}', GLOB_BRACE); //some regex
    $SQL_IMAGE = SELECT("contents", "json_extract(content, '$." . $role . "') as images_files", 'WHERE role="images"', false);
    $UI_GRID  = $select->find('.images', 0);
    $UI_LABEL = $UI_GRID->find('label', 0);
    $UI_BOX   = $select->find('[type=checkbox]', 0);
    $STACK_INPUTS = (string) null;
    $STACK_GRIDS  = (string) null;

    if ($SQL_IMAGE) { //Check DB - then loop through folder
        while ($DATA = fetch_array($SQL_IMAGE)) {
            $DB_IMGS = json_decode($DATA['images_files'], true);
            foreach ($images as $index => $src) {

                //Clean url because of manager/client path difference
                $URL = str_replace('../', '', $src);

                //Checkbox
                $UI_BOX->{'id'} = $role . $index . 'CI' . ($index + 1);
                $UI_BOX->{'checked'} = in_array($URL, $DB_IMGS) ? 'checked' : null; //If image curently used, checkbox is checked
                $UI_BOX->value  = $URL;

                //image preview
                $UI_LABEL->{'for'} = $role . $index . 'CI' . ($index + 1);
                $UI_LABEL->find('figure', 0)->{'style'}     = "background-image:url('" . '../' . IMAGE('crop', 150, 150, $URL) . "')";
                $UI_LABEL->find('figure', 0)->{'data-uri'}  = preg_replace('/upload\/(.*)\//', '', $URL);
                $UI_LABEL->find('figure', 0)->{'data-post'} = '#checkbar' . (isset($select->id) ? ', #' . $select->id : '');
                $UI_LABEL->find('figure', 0)->{'data-role'} = $role;

                //Stacking
                $STACK_INPUTS .= $UI_BOX;
                $STACK_GRIDS  .= $UI_LABEL;
            }
        }

        // $UI_GRID
        $UI_BOX->outertext  = $STACK_INPUTS;
        $UI_GRID->innertext = $STACK_GRIDS;
    }
}
foreach ($html->find('.iframe') as $iframe) {
    $iframe->{'src'} = $iframe->{'data-src'} ? url($iframe->{'data-src'}) : rootURL(); //. "?mod=" . $mod;
}
foreach ($html->find('form') as $form) {
    if (empty($form->{'action'})) {
        $form->{'action'} = $form->{'data-action'} ? url($form->{'data-action'}) : curPageURL();
    }
    $form->{'data-action'} = null;
}
/* Pareil, marre de taper des urls dynamiques, on va le faire depuis HTML */
foreach ($html->find('[data-url]') as $dataurl) {
    $attr              = $dataurl->{'data-url'};
    $dataurl->{'href'} = (function ($attr) {
        switch ($attr) {
            case "root":
                return rootURL();
                break;
            case "current":
                return curPageURL();
                break;
            default:
                return url($attr, true);
                break;
        }
    })($attr);
    $dataurl->{'data-url'} = null;
}

foreach ($head->find('[rel=stylesheet]') as $css) {
    $css->{'href'} .= '?v=' . date('his');
}

foreach ($html->find('script') as $js) {
    // $js->{'src'} .= '?v=' . date('his');
}

$html->find('[id=main]', 0)->{'class'} = 'row-start-1 col-start-1 row-end-4 col-end-12 col-md-end-13 col-lg-end-11 col-xl-end-10';
$html->find('[id=side]', 0)->{'class'} = 'row-start-1 row-end-4 col-start-1 col-end-13 col-md-start-5 col-lg-start-8 col-xl-start-10';

// $html->find('[id=main]', 0)->addClass('box d-flex flex-column');



//Script ends now, stop counting AND DISPLAY TIME
$time_elapsed_secs = round(microtime(true) - $start, 2);
// $footer->find('[id=time]', 0)->innertext = 'Cette page à été générée en ' . $time_elapsed_secs . 's ! <span class="fwb color-2">AMAZING !</span>';

$html->find('script', 0)->innertext .= 'console.log("Cette page à été générée en ' . $time_elapsed_secs . 's !")';
$html->find('body', 0)->innertext   .= $SCRIPTS;
//DISPLAY
echo minify_html($html);

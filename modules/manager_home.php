<?php

/*

    Pour eviter de perdre des données, bien qu'improbable,
    DECRYPT/ENCRYPT on été remplacés par des fonctions vides.
    convertir XDECRYPT et XENCRYPT en ENCRYPT et DECRYPT pour activer le cryptage des données personelles.

*/

function XDECRYPT($input)
{
    return $input;
}
function XENCRYPT($input)
{
    return $input;
}

/*
 EXPORT ARRAY
 on met un array en dehors du scope 
 pour récuperer les données partout dans le code php mais le remonter plus haut dans le html,
 sans faire 3 requetes supplémentaires pour récupérer les Résumés

 Because I AM SPEEEEEEEEEEEEEED.
*/

$_EXPORT_ARRAY = (array) null;

/*
    GET CP ET VILLES FROM BIG ARRAY
*/

$CPCODES = array(array("97400" => " Saint-Denis"), array("97400" => " Saint-François"), array("97400" => " Le Brûle"), array("97410" => " Basse Terre"), array("97410" => " Mont Vert les Haut"), array("97410" => " Mont Vert les Bas"), array("97410" => " Saint-Pierre"), array("97410" => " Terre Sainte"), array("97410" => " Grand Bois"), array("97411" => " Bois de Nèfles St Paul"), array("97412" => " La Rivière du Mât"), array("97412" => " Bras Panon"), array("97413" => " Cilaos"), array("97413" => " Palmiste Rouge"), array("97414" => " Entre-Deux"), array("97416" => " La Chaloupe Saint-Leu"), array("97417" => " Saint Bernard"), array("97417" => " La Montagne"), array("97418" => " La Plaine des Cafres"), array("97419" => " La Possession"), array("97419" => " La Rivière des Galets"), array("97419" => " Mafate - Aurère"), array("97419" => " Mafate - Grand Place"), array("97419" => " Dos d âne"), array("97419" => " Mafate - îlet à Bourses"), array("97419" => " Sainte-Thérèse"), array("97419" => " Mafate - îlet à Malheur"), array("97420" => " Le Port"), array("97421" => " La Rivière Saint Louis"), array("97421" => " Les Makes"), array("97422" => " La Saline"), array("97423" => " Le Guillaume"), array("97424" => " Piton Saint-Leu"), array("97424" => " Le Plate"), array("97425" => " Les Avirons"), array("97425" => " Tévelave"), array("97426" => " Les Trois-Bassins"), array("97427" => " Etang Sale"), array("97428" => " La Nouvelle"), array("97428" => " Mafate"), array("97428" => " Mafate - La Nouvelle"), array("97429" => " La Petite-Ile"), array("97430" => " Les Trois Mares"), array("97430" => " Le Tampon"), array("97430" => " Pont d yves"), array("97430" => " Le Quatorzième"), array("97431" => " La Plaine des Palmistes"), array("97432" => " La Ravine des Cabris"), array("97433" => " Salazie - Hell-bourg"), array("97433" => " Salazie - Grand-Ilet"), array("97433" => " Salazie - Mare à Vieille Place"), array("97433" => " Salazie"), array("97433" => " Hell Bourg"), array("97434" => " Saint-Gilles-les-Bains"), array("97434" => " La Saline-les-Bains"), array("97435" => " Saint-Gilles-les-Hauts"), array("97435" => " Bernica"), array("97435" => " Tan Rouge"), array("97436" => " Saint-Leu"), array("97437" => " Sainte-Anne"), array("97438" => " La Rivière des Pluies"), array("97438" => " Sainte-Marie"), array("97438" => " Gillot"), array("97439" => " Le Piton Sainte-Rose"), array("97439" => " Sainte-Rose"), array("97440" => " La Cressonnière"), array("97440" => " Cambuston"), array("97440" => " Saint-Andre"), array("97441" => " Sainte-Suzanne"), array("97442" => " Basse Vallée"), array("97442" => " Saint-Philippe"), array("97450" => " Saint-Louis"), array("97460" => " Saint-Paul"), array("97460" => " Bellemene St-Paul"), array("97460" => " Mafate - Roche Plate"), array("97460" => " Mafate - Marla"), array("97460" => " Mafate - Les Lataniers"), array("97460" => " Mafate - Îlet des Orangers"), array("97470" => " Saint-Benoît"), array("97480" => " Vincendo"), array("97480" => " Saint-Joseph"), array("97480" => " Les Lianes"), array("97490" => " La Bretagne"), array("97490" => " Moufia"), array("97490" => " Bois de Nèfles Ste-Clotilde"), array("97490" => " Sainte-Clotilde"));
$modal1  = $template->find('.modal', 0);
$villes = $modal1->find('[id=villes]', 0);
$STACK_VILLES = (string) null;
foreach ($CPCODES as $index => $value) {
    foreach ($value as $CP => $commune_ville) {
        $villes->find('option', 0)->innertext = $CP . ',' . $commune_ville;
        $villes->find('option', 0)->value     = $CP . ',' . $commune_ville;
        $STACK_VILLES .= $villes->find('option', 0);
    }
}
$villes->innertext = $STACK_VILLES;

/*
    GET LYCEES FROM DATABASE
*/
$_GET_LYCEES = SELECT('lycees', '*', 'WHERE 1', false);
$UI_LYCEES = $template->find('[id=lycees]', 0);
$STACK_LYCEES = (string) null;

if ($_GET_LYCEES) {
    while ($DATA = fetch_array($_GET_LYCEES)) {
        $UI_LYCEES->find('option', 0)->innertext = $DATA['lycee_ref'] . ' - ' . $DATA['lycee_nom'];
        $UI_LYCEES->find('option', 0)->value     = $DATA['lycee_ref'];
        $STACK_LYCEES .= $UI_LYCEES->find('option', 0);
        $_EXPORT_ARRAY['Établissements'] += 1; // <----------------------- EXPORT NUM LYCEES
    }
    $UI_LYCEES->innertext = $STACK_LYCEES;
}



if (isset($_POST) && !empty($_POST)) {

    /**
     * 
     *  POST only
     * 
     */

    if ($_POST['action'] == 'new') {
        $toggle1 = $template->find('[id=toggle-modal1]', 0)->{'checked'} = 'checked';
    }
    if ($_POST['action'] == "addFacture") {
        $arrData = array(
            "numero"    => date("Ymd") . "' || (SELECT COUNT(*)+1 as next FROM factures WHERE strftime('%Y-%m-%d', factures.date) = date('now')) || '",

            //Numero de facture, [Ymd]+[count(Ymd)+1]
            //ya un drôle d'ESCAPE"' et un CONCAT|| au debut et a la fin, 
            //pcq la fonction INSERT est très brute : KEY=>'STRINGVALUE' et ce petit apostrophe me gênait, le inner SELECT passait en string, 
            //donc ici --> KEY=>'20220101'||(INNERSQL)||'', efficace et pas cher :)

            "bon"       => CLEAN($_POST['bon']),
            "nom"       => XENCRYPT(CLEAN($_POST['nomclient'])),
            "adresse"   => XENCRYPT(CLEAN($_POST['adresse'])),
            "ville"     => XENCRYPT(CLEAN($_POST['ville'])),
            "telephone" => XENCRYPT(CLEAN($_POST['telephone'])),
            "articleID" => CLEAN($_POST['articleID']),
            "date"      => date('Y-m-d H:i:s', strtotime('+4 hours')),
            "eleve_nom" => XENCRYPT(CLEAN($_POST['eleve_nom'])),
            "lycee"     => XENCRYPT(CLEAN($_POST['lycee'])),
            "sn"        => XENCRYPT(CLEAN($_POST['numserie']))
        );

        $_INSERT_FACTURE = INSERT("factures", $arrData, false);

        if ($_INSERT_FACTURE) {
            $checker->innertext = "La facture a bien été ajoutée";
            $checker->{'class'} .= " green";

            //Set Historique
            $_INSERT_JOURNAL = INSERT("journal", array(
                "date"   => date('Y-m-d H:i:s', strtotime('+4 hours')),
                "login"  => $_SESSION['userID'],
                "action" => json_encode(array('Creation', $arrData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE  | JSON_UNESCAPED_SLASHES)),
                "factureID" => CLEAN($_GET['f'])
            ), false);
        } else {
            $checker->innertext = "Un problème est survenu lors de l'ajout de la facture";
            $checker->{'class'} .= " red";
        }
    }

    //editFacture
    if ($_POST['action'] == "editFacture") {
        $arrData = array(
            // "numero"    => dateToFrench(date("Y-m-d H:i:s"), "Ymd"),
            "bon"       => CLEAN($_POST['bon']),
            "nom"       => XENCRYPT(CLEAN($_POST['nomclient'])),
            "adresse"   => XENCRYPT(CLEAN($_POST['adresse'])),
            "ville"     => XENCRYPT(CLEAN($_POST['ville'])),
            "telephone" => XENCRYPT(CLEAN($_POST['telephone'])),
            "articleID" => CLEAN($_POST['articleID']),
            "eleve_nom" => XENCRYPT(CLEAN($_POST['eleve_nom'])),
            "lycee"     => XENCRYPT(CLEAN($_POST['lycee'])),
            "sn"        => XENCRYPT(CLEAN($_POST['numserie']))
        );

        $_UPDATE_FACTURE = UPDATE("factures", $arrData, "numero = " . CLEAN($_GET['f']), false);

        if ($_UPDATE_FACTURE) {
            $checker->innertext = 'La facture a bien été modifiée <a href="' . url("manager_home" . '&p=' . $_GET['f']) . '" target="_blank">Imprimer</a>';
            $checker->{'class'} .= " green";

            //Set Historique
            $_INSERT_JOURNAL = INSERT("journal", array(
                "date"   => date('Y-m-d H:i:s', strtotime('+4 hours')),
                "login"  => $_SESSION['userID'],
                "action" => json_encode(array('edition', $arrData)),
                "factureID" => CLEAN($_GET['f'])

            ), false);
        } else {
            $checker->innertext = "Un problème est survenu lors de la modification de la facture";
            $checker->{'class'} .= " red";
        }
    }
} else {

    /**
     * 
     *  GET only
     * 
     */

    $_EXPORT_ID = (string) null;
    if (isset($_GET['f']) && !empty($_GET['f'])) {

        //Open modal window
        $toggle1 = $template->find('[id=toggle-modal1]', 0)->{'checked'} = 'checked';

        $journal = $modal1->find('.journal', 0);

        $modal1->find('h3', 0)->innertext = "Editer Facture";

        $_GET_SINGLE_FACTURE = SELECT(
            "factures",
            "factures.*, 
             journal.login, 
             journal.date as jdate, 
             journal.action, 
             utilisateurs.name",
            "LEFT JOIN journal ON journal.factureID = factures.numero 
             LEFT JOIN utilisateurs ON utilisateurs.id = journal.login 
             WHERE numero=" . CLEAN($_GET['f']) . ' ORDER by jdate DESC',
            false
        );

        if ($_GET_SINGLE_FACTURE) {
            $modal1->find('[name=action]', 0)->value = "editFacture";
            $STACK_JOURNAL = (string) null;

            while ($DATA = fetch_array($_GET_SINGLE_FACTURE)) {
                $modal1->find('[name=bon]', 0)->value       = $DATA['bon'];
                $modal1->find('[name=numero]', 0)->value    = $DATA['numero'];
                $modal1->find('[name=nomclient]', 0)->value = XDECRYPT($DATA['nom']);
                $modal1->find('[name=adresse]', 0)->value   = XDECRYPT($DATA['adresse']);
                $modal1->find('[name=ville]', 0)->value     = XDECRYPT($DATA['ville']);
                $modal1->find('[name=telephone]', 0)->value = XDECRYPT($DATA['telephone']);

                $modal1->find('[name=eleve_nom]', 0)->value = XDECRYPT($DATA['eleve_nom']);
                $modal1->find('[name=lycee]',     0)->value = ($DATA['lycee']);
                $modal1->find('[name=numserie]',     0)->value = ($DATA['sn']);

                $modal1->find('[id=journal]', 0)->removeClass('d-none'); //Affiche le journal et le bouton d'impression caché par défaut, à faire à l'envers : ViewList et AddNew detruit le journal et le bouton d'impression
                $modal1->find('.print', 0)->removeClass('d-none');
                $modal1->find('.print', 0)->{'href'} = url("manager_home" . '&p=' . $DATA['numero']);

                $journal->find('td', 0)->innertext = dateToFrench($DATA['jdate'], "d/m H:i:s");
                $journal->find('td', 1)->innertext = $DATA['name'];




                $json = json_decode($DATA['action'], true);
                //map array to new array with index and value as value

                $newArray = array_map(function ($v, $k) {
                    return strtoupper($k) . ' : <span class="fwb">' . $v . '</span>';
                }, $json[1], array_keys($json[1]));
                $journal->find('td', 2)->innertext = implode('; ', $newArray);

                //Stack
                $_EXPORT_ID = $DATA['articleID'];
                $STACK_JOURNAL .= $journal->find('tr', 0);
            }
            //Display
            $journal->innertext = $STACK_JOURNAL;
        } else {

            $checker->innertext = "Un problème est survenu lors de la récupération de la facture";
            $checker->{'class'} .= " red";
        }
    }

    if (isset($_GET['p']) && !empty($_GET['p'])) {

        //Print
        $_GET_PRINT_FACTURE = SELECT(
            "factures",
            "factures.*, 
            articles.reference, 
            articles.designation,
            lycees.lycee_ref,
            lycees.lycee_nom",
            "LEFT JOIN lycees ON lycees.lycee_ref = factures.lycee
            LEFT JOIN articles ON factures.articleID = articles.id WHERE numero='" . CLEAN($_GET['p']) . "' ORDER BY date DESC",
            false
        );

        $pdf  = new FPDM("modele.pdf"); //Source

        if ($_GET_PRINT_FACTURE) {

            $TEMP = (string) null;
            while ($DATA = fetch_array($_GET_PRINT_FACTURE)) {

                $FIELDS = array(

                    'nomclient' => XDECRYPT($DATA['nom']),
                    'adresse'   => XDECRYPT($DATA['adresse']),
                    'ville'     => XDECRYPT($DATA['ville']),
                    'telephone' => XDECRYPT($DATA['telephone']),
                    'date'      => dateToFrench($DATA['date'], "d/m/Y"),
                    'numfact'   => $DATA['numero'],
                    'numpop'    => $DATA['bon'],
                    'reference' => $DATA['reference'],
                    'designation' => $DATA['designation'],
                    'nomeleve' => $DATA['eleve_nom'],
                    'nomlycee' => $DATA['lycee_ref'] . ' - ' . $DATA['lycee_nom'],
                    'numserie'  => XDECRYPT($DATA['sn'])

                );

                $TEMP = "Facture N° FC " . $DATA['numero'];
            }

            //DATA ARRAY
            $pdf->Load($FIELDS, true);
            //MERGE
            $pdf->Merge();
            //FLATTEN
            // $pdf->Flatten();
            //OUTPUT
            $pdf->Output('I', $TEMP . '.pdf'); //I pour Inline (dans le browser), D pour Download, F pour save sur le serveur

        } else {

            $checker->innertext = "Un problème est survenu lors de la récupération de la facture";
            $checker->addClass('red');
        }
    }
}


/**
 * 
 *  BOTH
 * 
 */


$_GET_FACTURES = SELECT(
    "factures",
    "factures.*, 
    articles.reference, 
    articles.designation",
    "LEFT JOIN articles ON factures.articleID = articles.id ORDER BY date DESC",
    false
);

if ($_GET_FACTURES) {
    $UI_TABLE = $template->find('table', 0);
    $UI_TBODY = $UI_TABLE->find('tbody', 0);
    $UI_TR    = $UI_TBODY->find('tr', 0);

    //String stack
    $STACK_TR = (string) null;
    while ($DATA = fetch_array($_GET_FACTURES)) {

        $UI_TR->find('td', 0)->find('a', 0)->innertext = dateToFrench($DATA['date'], 'd/m H:i');
        $UI_TR->find('td', 1)->find('a', 0)->innertext = 'F-' . ($DATA['numero']);
        $UI_TR->find('td', 2)->find('a', 0)->innertext = ($DATA['bon']);
        $UI_TR->find('td', 3)->find('a', 0)->innertext = XDECRYPT($DATA['nom']) . ' - <span class="text-1" style="color:#333">(' . XDECRYPT($DATA['eleve_nom']) . ')</span>';

        foreach ($UI_TR->find('a') as $link) {

            //Si le <a> a la class "print"
            if (strpos($link->{'class'}, 'print') !== false) {
                $link->{'href'} = '?p=' . $DATA['numero'];
                $link->{'id'}   = 'p' . $DATA['numero'];
            } else {
                $link->{'href'} = url('manager_home&f=' . $DATA['numero']);
            }
        }

        $STACK_TR .= $UI_TR;

        $_EXPORT_ARRAY['Factures']               += 1; //<----------------------------- Export NB Factures
        $_EXPORT_ARRAY['lycees'][$DATA['lycee']] += 1; //<----------------------------- Export Lycee PAR facture

    }
    $UI_TBODY->innertext = $STACK_TR;
}

$_GET_ARTICLES = SELECT(
    "articles",
    "articles.*",
    "WHERE 1",
    false
);
if ($_GET_ARTICLES) {
    $UI_SELECT = $template->find('[id=articleID]', 0);
    $STACK_OPTION = (string) '<option disabled>Choisir</option>';
    while ($DATA = fetch_array($_GET_ARTICLES)) {

        $UI_SELECT->find('option', 0)->{'data-aid'}     = $DATA['id'];
        $UI_SELECT->find('option', 0)->{'selected'}     = ($_EXPORT_ID == $DATA['id'] ? 'selected' : null);
        $UI_SELECT->find('option', 0)->value     = $DATA['id'];
        $UI_SELECT->find('option', 0)->innertext = $DATA['reference'];
        $STACK_OPTION .= $UI_SELECT->find('option', 0);

        $_EXPORT_ARRAY['Articles'] += 1; //<----------------------------- Export NB Articles
    }
    $UI_SELECT->innertext = $STACK_OPTION;
} else {
    $checker->innertext = "Un problème est survenu lors de la récupération des articles";
    $checker->{'class'} .= " red";
}




/******
 * 
 *  USING EXPORTED DATA
 * 
 */
//Unhide cardbox
$cards->removeClass('d-none');

//String stack
$STACK_CARD = (string) null;

//Loop through the array
foreach ($_EXPORT_ARRAY as $key => $value) {

    //Set the title
    $card->find('span', 0)->innertext = $key;

    //Set the value
    $card->find('span', 1)->innertext = $value;

    if ($key !== 'lycees') {
        //Add the card to the stack
        $STACK_CARD .= $card;
    }
}
//reinsert the stack
$cards->innertext = $STACK_CARD;
//DONE.
if (isset($_SESSION['type']) &&  $_SESSION['type'] == '1') {
    
}
// echo "<pre>";
// print_r($_EXPORT_ARRAY['lycees']);
// echo "</pre>";

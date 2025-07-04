<?php
if (isset($_SESSION['type']) &&  $_SESSION['type'] == '1') { //as string, not "true" else if fails very badly
    if ((isset($_POST) && !empty($_POST))) {

        /***********************************
         * 
         *      POST
         * 
         ***********************************/

        if ($_POST['action'] == 'new') {
            $template->find('[id=toggle-modal1]', 0)->{'checked'} = 'checked';
        }

        //action is addArticle
        if ($_POST['action'] == 'addLycee') {
            $_INSERT_ARTICLE = INSERT(
                'lycees',
                array(
                    'lycee_ref'   => CLEAN($_POST['lycee_ref']),
                    'lycee_nom'   => CLEAN($_POST['lycee_nom'])
                ), false
            );

            if($_INSERT_ARTICLE){
                $checker->innertext = "l'etablissement ajouté avec succès";
                $checker->addClass('green');
            }
        }

        if ($_POST['action'] == 'editLycee') {
            $_EDIT_ARTICLE = UPDATE(
                'lycees',
                array(
                    'lycee_ref'   => CLEAN($_POST['lycee_ref']),
                    'lycee_nom' => CLEAN($_POST['lycee_nom'])
                ), "WHERE id = " . $_POST['uid']
            , false);

            if($_EDIT_ARTICLE){
                $checker->innertext = "l'etablissement à été modifié avec succès";
                $checker->addClass('blue');
            }
        }

    } else {

        /***********************************
         * 
         *      GET
         * 
         ***********************************/


        if (isset($_GET['o']) && !empty($_GET['o'])) {

            $_GET_lycees = SELECT('lycees', '*', 'WHERE id='.CLEAN($_GET['o']));

            if ($_GET_lycees) {
                $template->find('[id=toggle-modal1]', 0)->{'checked'} = 'checked';
                $template->find('[value=addLycee]', 0)->value = 'editLycee';

                while ($DATA = fetch_array($_GET_lycees)) {
                    $template->find('[name=lycee_ref]',   0)->value   = $DATA['lycee_ref'];
                    $template->find('[name=lycee_nom]', 0)->innertext = $DATA['lycee_nom'];
                    $template->find('[name=uid]',         0)->value   = $DATA['id'];
                }
            }
            
        }
    }

    /***********************************
     * 
     *      BOTH
     * 
     ***********************************/

    $_GET_lycees = SELECT('lycees', '*', 'WHERE 1', false);

    if ($_GET_lycees) {

        $table = $template->find('table', 0)->find('tbody', 0);
        $tr    = $table->find('tr', 0);
        $STACK_lycees = (string) null;

        while ($DATA = fetch_array($_GET_lycees)) {

            $tr->find('td', 0)->find('a', 0)->innertext = $DATA['id'];
            $tr->find('td', 1)->find('a', 0)->innertext = $DATA['lycee_ref'];
            $tr->find('td', 2)->find('a', 0)->innertext = $DATA['lycee_nom'];

            foreach ($tr->find('a') as $link) {
                $link->{'href'} = url('manager_lycees&o=' . $DATA['id']);
            }
            $STACK_lycees .= $tr;
        }
        $table->innertext = $STACK_lycees;

    } 
    // else {
    //     $checker->innertext = 'Une erreur est survenue dans la récupération des données';
    //     $checker->addClass('red');
    // }

} else {

    $template->find('[id=main]', 0)->find('div', 0)->addClass("p-4");
    $template->find('[id=main]', 0)->find('div', 0)->innertext =  "Vous n'avez pas les droits pour accéder à cette page";
}

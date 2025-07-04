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
        if ($_POST['action'] == 'addArticle') {
            $_INSERT_ARTICLE = INSERT(
                'articles',
                array(
                    'reference'   => CLEAN($_POST['reference']),
                    'designation' => CLEAN($_POST['designation'])
                )
            );

            if($_INSERT_ARTICLE){
                $checker->innertext = "l'Article ajouté avec succès";
                $checker->addClass('green');
            }
        }

        if ($_POST['action'] == 'editArticle') {
            $_EDIT_ARTICLE = UPDATE(
                'articles',
                array(
                    'reference'   => CLEAN($_POST['reference']),
                    'designation' => CLEAN($_POST['designation'])
                ), "WHERE id = " . $_POST['uid']
            , false);
            if($_EDIT_ARTICLE){
                $checker->innertext = "l'Article à été modifié avec succès";
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

            $_GET_ARTICLES = SELECT('articles', '*', 'WHERE id='.CLEAN($_GET['o']));

            if ($_GET_ARTICLES) {
                $template->find('[id=toggle-modal1]', 0)->{'checked'} = 'checked';
                $template->find('[value=addArticle]', 0)->value = 'editArticle';

                while ($DATA = fetch_array($_GET_ARTICLES)) {
                    $template->find('[name=reference]',   0)->value       = $DATA['reference'];
                    $template->find('[name=designation]', 0)->innertext   = $DATA['designation'];
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

    $_GET_ARTICLES = SELECT('articles', '*', 'WHERE 1');

    if ($_GET_ARTICLES) {

        $table = $template->find('table', 0)->find('tbody', 0);
        $tr    = $table->find('tr', 0);
        $STACK_ARTICLES = (string) null;

        while ($DATA = fetch_array($_GET_ARTICLES)) {

            $tr->find('td', 0)->find('a', 0)->innertext = $DATA['id'];
            $tr->find('td', 1)->find('a', 0)->innertext = $DATA['reference'];
            $tr->find('td', 2)->find('a', 0)->innertext = $DATA['designation'];

            foreach ($tr->find('a') as $link) {
                $link->{'href'} = url('manager_articles&o=' . $DATA['id']);
            }
            $STACK_ARTICLES .= $tr;
        }
        $table->innertext = $STACK_ARTICLES;
    } else {
        $checker->innertext = 'Une erreur est survenue';
        $checker->addClass('red');
    }
} else {

    $template->find('[id=main]', 0)->find('div', 0)->addClass("p-4");
    $template->find('[id=main]', 0)->find('div', 0)->innertext =  "Vous n'avez pas les droits pour accéder à cette page";
}

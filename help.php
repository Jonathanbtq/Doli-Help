<?php

/**
 * This Code is here to help developpers to build dolibarr modules
 */

$action = isset($_POST['action']) ? $_POST['action'] : '';
$findPost = isset($_POST['help_input']) ? $_POST['help_input'] : '';
$wordfindarray = ['trigger', 'triggers', 'hook', 'hooks'];
/**
 * ACTION
 */
if ($action == 'find') {
    if (!empty($findPost)) {
        foreach ($wordfindarray as $mot) {
            $findPost = strtolower($findPost);
            if (isQuestion($findPost)) {
                // Ajouter le traitement pour les questions ici si nécessaire
            }
            $capreturn = isCapitale($findPost);
            $response = '';
            $responseCap = [];
            if ($capreturn && count($capreturn) > 0) {
                foreach ($capreturn as $capital) {
                    $capital = trim(strtolower($capital));
                    $response .= ' '.$capital; 
                }
                $responseCap[] = $capreturn;
            }
            $patternHook = '';
            if (strpos($findPost, $mot) !== false) {
                if ($mot == 'trigger' || $mot == 'triggers') {
                    $responseTrigger = [];
                    $responseTrigger[] = 'TRIGGER';

                    $responseHook[] = "Vous parlez de trigger. Pour commencer, défini le context dans le fichier mod du module.".PHP_EOL." 'Custom/module/core/triggers/interfaceç99_modMODULE_MODULETriggers.class.php'".PHP_EOL;
                    $responseHook[] = " Il existe une multitude de trigger : ";
                    $responseHook[] = $patternHook.PHP_EOL;
                    $responseHook[] = " Ce code permet d'éviter d'affecter toutes les pages.";
                }
                if ($mot == 'hook' || $mot == 'hooks') {
                    $responseHook = [];
                    $responseHook[] = 'HOOK';
                    $responseHook[] = "Vous parlez de Hook. Pour commencer, défini le context dans le fichier mod du module." . PHP_EOL . " 'Custom/module/class/action_module.class.php'" . PHP_EOL;
                    $responseHook[] = " Puis dans ce script ajouter une condition : ";
                    
                    $hooks = file_get_contents('hooks.txt');
                    $hooksArray = preg_split('/[\s,]+/', $hooks, -1, PREG_SPLIT_NO_EMPTY);
                    $findPost = strtolower($findPost);

                    $hookList = [];
                    foreach ($hooksArray as $hook) {
                        $findPost = str_replace(',', '', $findPost);
                        if (strpos($findPost, $hook) !== false) {
                            $hookList[] = $hook;
                        }
                    }
                    if (!empty($hookList)) {
                        $hookused = '';
                        $count = 0;
                        if (count($hookList) > 1) {
                            foreach ($hookList as $hk) {
                                $hookused .= " '" . $hk . "' ";
                                if ($count < count($hookList) - 1) {
                                    $hookused .= ', ';
                                }
                                $count++;
                            }
                            $patternHook = "if (array_intersect([$hookused], \$contexts)) {";
                            $patternHook .= " Dans ta demande tu utilises plusieurs hooks donc array_intersect sera utilisé";
                        } else {
                            $patternHook = "if (in_array(".$hookList[0].", \$contexts)) { //code }";
                            $patternHook .= " \nDans ta demande tu utilises un seul hook donc in_array sera utilisé";
                        }
                    } else {
                        $patternHook = "if (in_array('hook', \$contexts)) { //code }";
                    }
                    $responseHook[] = $patternHook.PHP_EOL;
                    $responseHook[] = " Ce code permet d'éviter d'affecter toutes les pages.";

                    if (preg_match('/bouton/', $findPost)) {
                        $responseHookButton = "L'ajout de bouton dans un Hook :";
                        $responseHookButton .= "\$this->resprints = dolGetButtonTitle(\$langs->trans('TITRE'), '', 'fa fa-file paddingleft', \$_SERVER['PHP_SELF'].'?action=' . \$parameters['param']);";
                    }
                    if (preg_match('/context/', $findPost)) {
                        $responseRight = "La récupération des contexts :<br>";
                        $responseRight .= " \$contexts = explode(':', \$parameters['context']);<br>";
                    }
                    if (preg_match('/creer un context/', $findPost)) {
                        $responseRight = "La récupération des contexts :<br>";
                        $responseRight .= "\$contexts = explode(':', \$parameters['context']);<br>";
                    }
                }
                if (preg_match('/droit/', $findPost) ||preg_match('/right/', $findPost) || preg_match('/hasRight/', $findPost)) {
                    $responseRight = "Les droits dans Dolibarr :";
                    $responseRight .= " \$user->hasRight('facture', 'creer') / \$user->admin";
                }
            }
        }
    }

    // Gestion de la réponse finale en fonction des cas de TRIGGER et HOOK
    if (!empty($responseHook[1]) || !empty($responseTrigger[1])) {
        switch ($responseHook[0]) {
            case 'TRIGGER':
                $response = $responseTrigger[1] . "<br>";
                $response .= $responseTrigger[2] . "<br>";
                $response .= $responseTrigger[3] . "<br>";
                break;
            case 'HOOK':
                $response = $responseHook[1] . "<br>";
                $response .= $responseHook[2] . "<br>";
                $response .= $responseHook[3] . "<br>";
                if (!empty($responseHookButton)) {
                    $response .= $responseHookButton;
                }
                break;
            default:
                // Si aucun des cas précédents ne correspond, vous pouvez envisager une combinaison
                if ($responseHook[0] == 'TRIGGER' && $responseTrigger[0] == 'HOOK') {
                    $response = $responseHook[1] . "<br>";
                    $response .= $responseHook[2] . "<br>";
                    $response .= $responseHook[3] . "<br>";
                    $response .= $responseTrigger[1] . "<br>";
                    $response .= $responseTrigger[2] . "<br>";
                    $response .= $responseTrigger[3] . "<br>";
                    if (!empty($responseHookButton)) {
                        $response .= $responseHookButton;
                    }
                }
                break;
        }
        if (!empty($responseRight)) {
            $response .= $responseRight;
        }
    }    
}

function isQuestion($text) {
    return strpos($text, '?') !== false;
}

/**
 * Fonction pour vérifier si une phrase est une exclamation
 */
function isExclamation($text) {
    return strpos($text, '!') !== false;
}

/**
 * Fonction pour trouver une capitale
 */
function isCapitale($text) {
    $capitals = file_get_contents('capital.txt');
    $capitalsArray = preg_split('/[\s,]+/', $capitals, -1, PREG_SPLIT_NO_EMPTY);
    $text = strtolower($text);

    $foundCapitales = [];
    foreach ($capitalsArray as $capitalword) {
        $capitalword = trim(strtolower($capitalword));
        $text = str_replace(',', '', $text);

        if (strpos($text, $capitalword) !== false) {
            $foundCapitales[] = $capitalword;
        }
    }

    return !empty($foundCapitales) ? $foundCapitales : false;
}
// $text = 'if (array_intersect(['bookkeepingbyaccountlist', 'bookkeepinglist'], $contexts)) {
// 			$removeAllFilters = (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')); // All tests are required to be compatible with all browsers
// 			if ($removeAllFilters) {
// 				unset($_POST['search_account_class_start']);
// 				unset($_POST['search_account_class_end']);
// 			}
// 		}';

$question = 'Creer moi une fonctionnalite/ qui me permeterai de supprimer un POST de ma page/ bookkeepingbyaccountlist et bookkeepinglist';
// Premiere partie
// Récupération du premier mot pour analyse.
// creer ajouter verifier
// Verifier si la phrase contient 'creer module', 'ajouter un module', 'module'
// Deuxieme partie
// Millieu de phrase
// recherche de pleins de mots possible ()
/**
 * VIEW
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doli'Help</title>
    <link href="style/help.css" rel="stylesheet" type="text/css">
</head>
<body>
    <nav>
        <h1>Doli'Help</h1>
        <ul>
            <li></li>
            <li></li>
            <li></li>
        </ul>
    </nav>
    <div class="help_main">
        <div class="help_ctn">
            <div class="help_warning">
                <p>Toujours coder dans un module, il n'est pas conseillé de modifier le core de Dolibarr, une mise à jours de ce dernier et votre code disparait</p>
            </div>
                    
            <div class="help_response">
                <?php if (!empty($response)):
                    echo $response;
                        ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <form action="" method="POST">
                <div>
                    <input type="text" name="help_input" placeholder="Message Doli'Help" value="<?php if(!empty($findPost)) : echo $findPost; endif ?>">
                </div>
                <input type="hidden" name="action" value="find">
                <input type="submit" value="->">
            </form>
        </div>
    </div>

    <footer>
        <div class="help_footer">
            <p>Jonathanbtq Certified reserved</p>
        </div>
    </footer>
</body>
</html>
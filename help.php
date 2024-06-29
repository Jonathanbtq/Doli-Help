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

                    $hookList = isWhatHook($findPost);
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
                    if (preg_match('/\bquelle\b\s*([^?]+)/i', $findPost, $matches)) {
                        $question = trim($matches[1]);
                        switch (true){
                            case preg_match('/hook utiliser pour la page/', $question):
                                $hook = isWhatHook($findPost);
                                $count = 0;
                                if (count($hook) > 1) {
                                    $hookTab = [];
                                    $hooktxt = '';
                                    $hookname = '<span class="help_hookname">';
                                    foreach ($hook as $hk) {
                                        $hookPage = whatPageItIs($hk);
                                        $hooktxt .= $hookPage;
                                        if ($count < count($hook) - 1) {
                                            $hooktxt .= ', ';
                                        }
                                        $count++;
                                        $hookname .= ' ' . $hk;
                                    }
                                    $hookname .=  '</span> ';
                                    $responseQuestion = 'Si vous recherchez la page qui utilise ce hook '.$hookname . ' : '.$hooktxt;
                                } else {
                                    $hookPage = whatPageItIs($hook[0]);
                                    $responseQuestion = 'Si vous recherchez la page qui utilise ce hook '.$hook[0].' : '.$hookPage;
                                }
                        }
                        // $responseQuestion = trim(str_replace(["\n", "\r"], '', $responseQuestion));
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
        if (!empty($responseQuestion)) {
            $response .= $responseQuestion;
        }
    }    
}

function isWhatHook($text) {
    $hooks = file_get_contents('hooks.txt');
    $hooksArray = preg_split('/[\s,]+/', $hooks, -1, PREG_SPLIT_NO_EMPTY);
    $text = strtolower($text);

    $hookList = [];
    foreach ($hooksArray as $hook) {
        $text = str_replace(',', '', $text);
        if (strpos($text, $hook) !== false) {
            $hookList[] = $hook;
        }
    }
    return $hookList;
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

function whatPageItIs($text) {
    switch ($text) {
        case 'membercard':
        case 'membertypecard':
            $term = 'Member';
            break;
        case 'categorycard':
            $term = 'Category';
            break;
        case 'commcard':
            $term = 'Communication';
            break;
        case 'propalcard':
            $term = 'Proposal';
            break;
        case 'actioncard':
            $term = 'Action';
            break;
        case 'agenda':
            $term = 'Agenda';
            break;
        case 'mailingcard':
            $term = 'Mailing';
            break;
        case 'ordercard':
            $term = 'Order';
            break;
        case 'invoicecard':
            $term = 'Invoice';
            break;
        case 'paiementcard':
            $term = 'Payment';
            break;
        case 'tripsandexpensescard':
            $term = 'Trips and Expenses';
            break;
        case 'doncard':
            $term = 'Donation';
            break;
        case 'externalbalance':
            $term = 'External Balance';
            break;
        case 'salarycard':
            $term = 'Salary';
            break;
        case 'taxvatcard':
            $term = 'Tax/VAT';
            break;
        case 'contactcard':
            $term = 'Contact';
            break;
        case 'contractcard':
            $term = 'Contract';
            break;
        case 'expeditioncard':
            $term = 'Expedition';
            break;
        case 'interventioncard':
            $term = 'Intervention';
            break;
        case 'suppliercard':
            $term = 'Supplier';
            break;
        case 'ordersuppliercard':
        case 'orderstoinvoicesupplier':
            $term = 'Supplier Orders';
            break;
        case 'invoicesuppliercard':
            $term = 'Supplier Invoices';
            break;
        case 'paymentsupplier':
            $term = 'Supplier Payments';
            break;
        case 'deliverycard':
            $term = 'Delivery';
            break;
        case 'productcard':
            $term = 'Product';
            break;
        case 'productcompositioncard':
            $term = 'Product Composition';
            break;
        case 'pricesuppliercard':
            $term = 'Price Supplier';
            break;
        case 'productstatsorder':
            $term = 'Product Stats Order';
            break;
        case 'productstatssupplyorder':
            $term = 'Product Stats Supply Order';
            break;
        case 'productstatscontract':
            $term = 'Product Stats Contract';
            break;
        case 'productstatsinvoice':
            $term = 'Product Stats Invoice';
            break;
        case 'productstatssupplyinvoice':
            $term = 'Product Stats Supply Invoice';
            break;
        case 'productstatspropal':
            $term = 'Product Stats Proposal';
            break;
        case 'warehousecard':
            $term = 'Warehouse';
            break;
        case 'projectcard':
            $term = 'Project';
            break;
        case 'projecttaskcard':
            $term = 'Project Task';
            break;
        case 'resource_card':
        case 'element_resource':
            $term = 'Resource Card';
            break;
        case 'agendathirdparty':
            $term = 'Agenda Third Party';
            break;
        case 'salesrepresentativescard':
            $term = 'Sales Representatives';
            break;
        case 'consumptionthirdparty':
            $term = 'Consumption Third Party';
            break;
        case 'infothirdparty':
            $term = 'Info Third Party';
            break;
        case 'thirdpartycard':
            $term = 'Third Party';
            break;
        case 'usercard':
        case 'userlist':
            $term = 'User';
            break;
        case 'passwordforgottenpage':
            $term = 'Password Forgotten Page';
            break;
        default:
            $term = 'Page inconnue';
            break;
    }
    return $term;
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
                <p>
                     <?php if (!empty($response)):
                    echo $response;
                        ?>
                    </p>
                    <?php endif; ?>
                </p>
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
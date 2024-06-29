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

                    $hookList = isWhatHook($findPost, 'hook');
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
                    if (preg_match('/\bquelle\b\s*([^?]+)/i', $findPost, $matches) || preg_match('/\bquel\b\s*([^?]+)/i', $findPost, $matches)) {
                        $question = trim($matches[1]);
                        $result = checkWordsInPhrase($findPost);
                        $patternsHook = [
                            '/\bquel\b.*\bhook\b.*\bpour\b.*\bcette page\b/i',    // Quel hook pour cette page ?
                            '/\bquel\b.*\bhook\b.*\bpour\b.*\bpage\b/i',           // Quel hook pour cette page X ?
                            '/\bquel\b.*\bhook\b.*\butiliser\b.*\bpour\b.*\bpage\b/i',  // Quel hook utiliser pour la page X ?
                            '/\bquel\b.*\bhook\b.*\bsera utilisé\b.*\bpour\b.*\bpage\b/i',  // Quel hook sera utilisé pour la page X ?
                            '/\bquel\b.*\bhook\b.*\bserait utilisé\b.*\bpour\b.*\bpage\b/i',  // Quel hook serait utilisé pour la page X ?
                            '/\bquel\b.*\bhook\b.*\bsera utilisé\b.*\bsur\b.*\bpage\b/i',    // Quel hook sera utilisé sur la page X ?
                        ];

                        $matched = false;

                        var_dump($question);
                        foreach ($patternsHook as $pattern) {
                            if (preg_match($pattern, 'quel '.$question)) {
                                $matched = true;
                                break;
                            }
                        }

                        if ($matched) {
                            /**
                             * Quel hook sera utilisé pour cette page
                             */
                            $hook = isWhatHook($findPost, 'page');
                            $count = 0;
                            if (!empty($hook)) {
                                if (count($hook) > 1) {
                                    $hookTab = [];
                                    $hooktxt = '';
                                    $hooktxt .= '<br>';
                                    $hookname = '<span class="help_hookname">';
                                    foreach ($hook as $hk) {
                                        $hookname .= ' "' . $hk . '"';
                                        $hookPage = whatHookItIs(strtolower($hk));
                                        $hooktxt .= '<span class="bolder">'.$hookPage.'</span>';
                                        if ($count < count($hook) - 1) {
                                            $hooktxt .= ', ';
                                            $hookname .= ' - ';
                                        }
                                        $count++;
                                    }
                                    $hookname .=  '</span> ';
                                    $responseQuestion = '<br>Si vous recherchez le hook Pour ces pages '.$hookname . ' : '.$hooktxt;
                                } else {
                                    var_dump(strtolower($hook[0]));
                                    $hookPage = whatHookItIs(strtolower($hook[0]));
                                    var_dump($hookPage);
                                    $responseQuestion = '<br>Si vous recherchez le hook Pour ces pages '.$hook[0].' : '.$hookPage;
                                }
                            }
                        }

                        // Utilisation de preg_match pour identifier les motifs clés
                        if (preg_match('/fonction.*hook/i', $question)) {
                            echo "Les hooks dans Dolibarr permettent de personnaliser le comportement de l'application en interceptant certaines actions.\n";
                        } elseif (preg_match('/différence.*trigger.*hook/i', $question)) {
                            echo "Un trigger est déclenché par une action spécifique, tandis qu'un hook intercepte et modifie le comportement d'une action existante dans Dolibarr.\n";
                        } elseif (preg_match('/\bquel\b.*\bpage\b.*\butilise\b.*\bhook\b/i', $question)) {
                            /**
                             * Quel page pour ce hook
                             */
                            $hook = isWhatHook($findPost, 'page');
                            $count = 0;
                            if (!empty($hook)) {
                                if (count($hook) > 1) {
                                    $hookTab = [];
                                    $hooktxt = '';
                                    $hooktxt .= '<br>';
                                    $hookname = '<span class="help_hookname">';
                                    foreach ($hook as $hk) {
                                        $hookname .= ' "' . $hk . '"';
                                        $hookPage = getCardName(strtolower($hk));
                                        $hooktxt .= '<span class="bolder">'.$hookPage.'</span>';
                                        if ($count < count($hook) - 1) {
                                            $hooktxt .= ', ';
                                            $hookname .= ' - ';
                                        }
                                        $count++;
                                    }
                                    $hookname .=  '</span> ';
                                    $responseQuestion = '<br>Si vous recherchez la page qui utilise ce hook '.$hookname . ' : '.$hooktxt;
                                } else {
                                    var_dump(strtolower($hook[0]));
                                    $hookPage = getCardName(strtolower($hook[0]));
                                    var_dump($hookPage);
                                    $responseQuestion = '<br>Si vous recherchez la page qui utilise ce hook '.$hook[0].' : '.$hookPage;
                                }
                            }
                        } elseif (preg_match('/personnaliser.*page.*commande/i', $question)) {
                            echo "Pour personnaliser la page de commande dans Dolibarr, utilisez le hook approprié dédié à cette fonctionnalité.\n";
                        } elseif (preg_match('/liste.*complète.*hooks/i', $question)) {
                            echo "La liste complète des hooks disponibles dans Dolibarr peut être trouvée dans la documentation officielle ou dans le code source de l'application.\n";
                        } elseif (preg_match('/hook.*création.*utilisateur/i', $question)) {
                            echo "Lors de la création d'un utilisateur dans Dolibarr, le hook approprié à utiliser dépendra du moment où vous souhaitez interagir avec le processus de création.\n";
                        } else {
                            echo "Désolé, je ne peux pas répondre à cette question spécifique sur les hooks dans Dolibarr.\n";
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

function checkWordsInPhrase($phrase) {
    $phrase = strtolower($phrase);
    $wordFrequency = [];

    $words = preg_split('/[\s,.;:!?]+/', $phrase, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($words as $word) {
        if (array_key_exists($word, $wordFrequency)) {
            $wordFrequency[$word]++;
        } else {
            $wordFrequency[$word] = 1;
        }
        
    }

    arsort($wordFrequency);

    return $wordFrequency;
}

/**
 * Permet la detection de mot qui correspondent a des hook
 *
 * @param [type] $text
 * @param [type] $type
 * @return array
 */
function isWhatHook($text, $type) {
    if ($type === 'page') {
        $hooks = file_get_contents('page.txt');
    } else {
        $hooks = file_get_contents('hooks.txt');
    }

    $hooksArray = preg_split('/[\s,]+/', $hooks, -1, PREG_SPLIT_NO_EMPTY);
    $text = strtolower($text);
    $hookList = [];
    foreach ($hooksArray as $hook) {
        $text = str_replace(',', '', $text);
        // Vérifier l'existance du hook dans la phrase
        if (strpos($text, strtolower($hook)) !== false) {
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

/**
 * Récupération de la page en fonction du hook
 *
 * @param [type] $text
 * @return void
 */
function getCardName($text) {
    $cards = [
        'membercard' => 'member',
        'membertypecard' => 'member',
        'categorycard' => 'category',
        'commcard' => 'communication',
        'propalcard' => 'proposal',
        'actioncard' => 'action',
        'agenda' => 'agenda',
        'mailingcard' => 'mailing',
        'ordercard' => 'order',
        'invoicecard' => 'invoice',
        'paiementcard' => 'payment',
        'tripsandexpensescard' => 'tripsandexpenses',
        'doncard' => 'donation',
        'externalbalance' => 'externalbalance',
        'salarycard' => 'salary',
        'taxvatcard' => 'taxvat',
        'contactcard' => 'contact',
        'contractcard' => 'contract',
        'expeditioncard' => 'expedition',
        'interventioncard' => 'intervention',
        'suppliercard' => 'supplier',
        'ordersuppliercard' => 'supplierorders',
        'orderstoinvoicesupplier' => 'supplierorders',
        'invoicesuppliercard' => 'supplierinvoices',
        'paymentsupplier' => 'supplierpayments',
        'deliverycard' => 'delivery',
        'productcard' => 'product',
        'productcompositioncard' => 'productcomposition',
        'pricesuppliercard' => 'pricesupplier',
        'productstatsorder' => 'productstatsorder',
        'productstatssupplyorder' => 'productstatssupplyorder',
        'productstatscontract' => 'productstatscontract',
        'productstatsinvoice' => 'productstatsinvoice',
        'productstatssupplyinvoice' => 'productstatssupplyinvoice',
        'productstatspropal' => 'productstatsproposal',
        'warehousecard' => 'warehouse',
        'projectcard' => 'project',
        'projecttaskcard' => 'projecttask',
        'resource_card' => 'resource',
        'element_resource' => 'resource',
        'agendathirdparty' => 'agendathirdparty',
        'salesrepresentativescard' => 'salesrepresentatives',
        'consumptionthirdparty' => 'consumptionthirdparty',
        'infothirdparty' => 'infothirdparty',
        'thirdpartycard' => 'thirdparty',
        'usercard' => 'user',
        'userlist' => 'user',
        'passwordforgottenpage' => 'passwordforgottenpage',
    ];

    return isset($cards[$text]) ? $cards[$text] : 'Page inconnue';
}


/**
 * Récupération du hook en fonction de la page
 *
 * @param [type] $text
 * @return void
 */
function whatHookItIs($text) {
    switch ($text) {
        case 'member':
        case 'membre':
            $desc = 'membercard, membertypecard';
            break;
        case 'category':
        case 'categorie':
            $desc = 'categorycard';
            break;
        case 'communication':
            $desc = 'commcard';
            break;
        case 'proposal':
        case 'propal':
        case 'proposition commercial':
            $desc = 'propalcard';
            break;
        case 'action':
            $desc = 'actioncard';
            break;
        case 'agenda':
            $desc = 'agenda';
            break;
        case 'mailing':
            $desc = 'mailingcard';
            break;
        case 'order':
        case 'commande':
            $desc = 'ordercard';
            break;
        case 'invoice':
        case 'facture':
            $desc = 'invoicecard';
            break;
        case 'payment':
        case 'paiement':
            $desc = 'paiementcard';
            break;
        case 'tripsandexpenses':
            $desc = 'tripsandexpensescard';
            break;
        case 'donation':
            $desc = 'doncard';
            break;
        case 'externalbalance':
            $desc = 'externalbalance';
            break;
        case 'salary':
        case 'salaire':
            $desc = 'salarycard';
            break;
        case 'taxvat':
            $desc = 'taxvatcard';
            break;
        case 'contact':
            $desc = 'contactcard';
            break;
        case 'contract':
        case 'contrat':
            $desc = 'contractcard';
            break;
        case 'expedition':
        case 'livraison':
            $desc = 'expeditioncard';
            break;
        case 'intervention':
            $desc = 'interventioncard';
            break;
        case 'supplier':
        case 'fournisseur':
            $desc = 'suppliercard';
            break;
        case 'supplierorders':
        case 'commandefournisseur':
            $desc = 'ordersuppliercard, orderstoinvoicesupplier';
            break;
        case 'supplierinvoices':
        case 'facturefournisseur':
            $desc = 'invoicesuppliercard';
            break;
        case 'supplierpayments':
        case 'paiementfournisseur':
            $desc = 'paymentsupplier';
            break;
        case 'delivery':
            $desc = 'deliverycard';
            break;
        case 'product':
        case 'produit':
            $desc = 'productcard';
            break;
        case 'productcomposition':
            $desc = 'productcompositioncard';
            break;
        case 'pricesupplier':
            $desc = 'pricesuppliercard';
            break;
        case 'productstatsorder':
            $desc = 'productstatsorder';
            break;
        case 'productstatssupplyorder':
            $desc = 'productstatssupplyorder';
            break;
        case 'productstatscontract':
            $desc = 'productstatscontract';
            break;
        case 'productstatsinvoice':
            $desc = 'productstatsinvoice';
            break;
        case 'productstatssupplyinvoice':
            $desc = 'productstatssupplyinvoice';
            break;
        case 'productstatsproposal':
            $desc = 'productstatspropal';
            break;
        case 'warehouse':
        case 'entrepot':
            $desc = 'warehousecard';
            break;
        case 'project':
            $desc = 'projectcard';
            break;
        case 'projecttask':
            $desc = 'projecttaskcard';
            break;
        case 'resource':
            $desc = 'resource_card, element_resource';
            break;
        case 'agendathirdparty':
            $desc = 'agendathirdparty';
            break;
        case 'salesrepresentatives':
            $desc = 'salesrepresentativescard';
            break;
        case 'consumptionthirdparty':
            $desc = 'consumptionthirdparty';
            break;
        case 'infothirdparty':
            $desc = 'infothirdparty';
            break;
        case 'thirdparty':
        case 'entreprise':
            $desc = 'thirdpartycard';
            break;
        case 'user':
            $desc = 'usercard, userlist';
            break;
        case 'passwordforgottenpage':
            $desc = 'passwordforgottenpage';
            break;
        default:
            $desc = 'Page inconnue';
            break;
    }
    return $desc;
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
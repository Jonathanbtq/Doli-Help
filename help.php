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
        $intention = getIntention($findPost);
        switch($intention) {
            case 'curiosité':
                $humeurtxt = 'Pour répondre a votre intérrogation :';
                break;
            case 'frustration':
                $humeurtxt = 'Je vois de la frustration dans votre message... ';
                break;
            case 'politesse':
                $humeurtxt = 'Bien-sûr, ';
                break;
            case 'impatience':
                $humeurtxt = 'Calmez-vous...';
                break;
            default:
                $humeurtxt = '';
                break;
        }
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

                    $hookList = isWhatHook($findPost, 'hookused');
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

                        // Question sur les hook a utiliser
                        $patternsHook = [
                            '/\bquel\b.*\bhook\b.*\bpour\b.*\bcette page\b/i',    // Quel hook pour cette page ?
                            '/\bquel\b.*\bhook\b.*\bpour\b.*\bpage\b/i',           // Quel hook pour cette page X ?
                            '/\bquel\b.*\bhook\b.*\butiliser\b.*\bpour\b.*\bpage\b/i',  // Quel hook utiliser pour la page X ?
                            '/\bquel\b.*\bhook\b.*\bsera utilisé\b.*\bpour\b.*\bpage\b/i',  // Quel hook sera utilisé pour la page X ?
                            '/\bquel\b.*\bhook\b.*\bserait utilisé\b.*\bpour\b.*\bpage\b/i',  // Quel hook serait utilisé pour la page X ?
                            '/\bquel\b.*\bhook\b.*\bsera utilisé\b.*\bsur\b.*\bpage\b/i',    // Quel hook sera utilisé sur la page X ?
                        ];

                        $matched = false;

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
                            $responseQuestion = isWhatHook($findPost, 'page');
                        }

                        // Question sur les page a utiliser
                        $patternsPage = [
                            '/\bquelle\b.*\bpage\b.*\butilise\b.*\bce hook\b/i',    // Quelle page utilise ce hook ?
                            '/\bquelle\b.*\bpage\b.*\butilise\b.*\ble hook\b/i',           // Quelle page utilise le hook X ?
                            '/\bquel\b.*\bpage\b.*\butilise\b.*\bce hook\b/i',  // Quel page utilise ce hook ?
                            '/\bquel\b.*\bpage\b.*\butilise\b.*\ble hook\b/i',  // Quel page utilise le hook X ?
                            '/\bquelle est\b.*\bla page\b.*\bqui utilise\b.*\bce hook\b/i',  // Quelle est la page qui utilise ce hook ?
                            '/\bquelle est\b.*\bla page\b.*\bqui utilise\b.*\ble hook\b/i',  // Quelle est la page qui utilise le hook X ?
                            '/\bquel est\b.*\bla page\b.*\bqui utilise\b.*\bce hook\b/i',  // Quel est la page qui utilise ce hook ?
                            '/\bquel est\b.*\bla page\b.*\bqui utilise\b.*\ble hook\b/i',  // Quel est la page qui utilise le hook X ?
                            '/\bquelle\b.*\bpage\b.*\bfait usage de\b.*\bce hook\b/i',  // Quelle page fait usage de ce hook ?
                            '/\bquelle\b.*\bpage\b.*\bfait usage de\b.*\ble hook\b/i',  // Quelle page fait usage du hook X ?
                            '/\bquel(le)?\b.*\bpage\b.*\butilise\b.*\bhook\b/i',  // Quelle page fait usage du hook X ?
                        ];

                        $matched = false;

                        foreach ($patternsHook as $pattern) {
                            $question = 'quel '.$question;
                            if (preg_match($pattern, $question)) {
                                $matched = true;
                                break;
                            }
                        }

                        if ($matched) {
                            /**
                             * Quel hook sera utilisé pour cette page
                             */
                            $responseQuestion = isWhatHook($findPost, 'page');
                        }

                        // Utilisation de preg_match pour identifier les motifs clés
                        if (preg_match('/fonction.*hook/i', $question)) {
                            $responseDivers = "Les hooks dans Dolibarr permettent de personnaliser le comportement de l'application en interceptant certaines actions.\n";
                        } elseif (preg_match('/différence.*trigger.*hook/i', $question)) {
                            $responseDivers = "Un trigger est déclenché par une action spécifique, tandis qu'un hook intercepte et modifie le comportement d'une action existante dans Dolibarr.\n";
                        } elseif (preg_match('/\bquel\b.*\bpage\b.*\butilise\b.*\bhook\b/i', $question)) {
                            /**
                             * Quel page pour ce hook
                             */
                            $responseDivers = isWhatHook($findPost, 'page');
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
                                    $responseDivers = '<br>Si vous recherchez la page qui utilise ce hook '.$hookname . ' : '.$hooktxt;
                                } else {
                                    var_dump(strtolower($hook[0]));
                                    $hookPage = getCardName(strtolower($hook[0]));
                                    var_dump($hookPage);
                                    $responseDivers = '<br>Si vous recherchez la page qui utilise ce hook '.$hook[0].' : '.$hookPage;
                                }
                            }
                        } elseif (preg_match('/personnaliser.*page.*commande/i', $question)) {
                            $responseDivers = "Pour personnaliser la page de commande dans Dolibarr, utilisez le hook approprié dédié à cette fonctionnalité.\n";
                        } elseif (preg_match('/liste.*complète.*hooks/i', $question)) {
                            $responseDivers = file_get_contents('hooks.txt');
                            $responseDivers = str_replace(', ', '<br>', $responseDivers);
                        } elseif (preg_match('/hook.*création.*utilisateur/i', $question) || preg_match('/hook.*creation.*utilisateur/i', $question)) {
                            $responseDivers = "Lors de la création d'un utilisateur dans Dolibarr, le hook approprié à utiliser dépendra du moment où vous souhaitez interagir avec le processus de création.\n";
                        } else {
                            if (empty($responseQuestion)) {
                                $responseDivers = "Désolé, je ne peux pas répondre à cette question spécifique sur les hooks dans Dolibarr.\n";
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
        if (!empty($responseDivers)) {
            $response .= $responseDivers;
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
    $text = str_replace(',', '', $text);

    // Transformer le texte en tableau de mots, en séparant par les espaces
    $words = explode(' ', $text);

    // Remplacer 'facturefournisseur' par 'facturesfournisseur' dans le tableau des mots
    foreach ($words as &$word) {
        if ($word == 'facturesfournisseur') {
            $word = 'facturefournisseur';
        }
    }
    unset($word); // Unset la référence

    // Reconstituer le texte modifié
    $modifiedText = implode(' ', $words);
    $hookList = [];
    foreach ($hooksArray as $hook) {
        // Vérifier l'existence du hook dans le texte modifié
        if (strpos($modifiedText, strtolower($hook)) !== false) {
            $hookList[] = $hook;
        }
    }
    if ($type == 'hookused') {
        return $hookList;
    }

    $count = 0;
    $responseQuestion = '';
    if (!empty($hookList)) {
        if (count($hookList) > 1) {
            $hookTab = [];
            $hooktxt = '';
            $hooktxt .= '<br>';
            $hookname = '<span class="help_hookname">';
            foreach ($hookList as $hk) {
                $hookname .= ' "' . $hk . '"';
                $hookPage = whatHookItIs(strtolower($hk));
                $hooktxt .= '<span class="bolder">'.$hookPage.'</span>';
                if ($count < count($hookList) - 1) {
                    $hooktxt .= ', ';
                    $hookname .= ' - ';
                }
                $count++;
            }
            $hookname .=  '</span> ';
            if ($type === 'hook') {
                $responseQuestion = '<br>Si vous recherchez le hook Pour ces pages '.$hookname . ' : '.$hooktxt;
            } else {
                $responseQuestion = '<br>Si vous recherchez la page qui va avec ces hook '.$hookname . ' : '.$hooktxt;
            }
        } else {
            $hookPage = whatHookItIs(strtolower($hookList[0]));
            if ($type === 'hook') {
                $responseQuestion = '<br>Si vous recherchez le hook Pour ces pages '.$hookList[0].' : '.$hookPage;
            } else {
                $responseQuestion = '<br>Si vous recherchez la page qui va avec ce hook '.$hookList[0].' : '.$hookPage;
            }
        }
    }

    return $responseQuestion;
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

function getIntention($question) {
    $question = strtolower($question);

    // Mots-clés et phrases indicatives avec leurs poids
    $keywords = [
        'curiosité' => [
            'comment' => 1, 'pourquoi' => 1, 'quel' => 1, 'quelle' => 1, 'où' => 1, 'quand' => 1
        ],
        'frustration' => [
            'encore' => 1, 'toujours' => 1, 'jamais' => 1, 'pourquoi encore' => 2, '!' => 1
        ],
        'politesse' => [
            'pourriez-vous' => 1, 's\'il vous plaît' => 1, 'svp' => 1, 'merci' => 1
        ],
        'impatience' => [
            'quand' => 1, 'dépêchez-vous' => 2, 'vite' => 1, 'rapidement' => 1
        ]
    ];

    $scores = [
        'curiosité' => 0,
        'frustration' => 0,
        'politesse' => 0,
        'impatience' => 0
    ];

    // Calculer les scores pour chaque humeur
    foreach ($keywords as $mood => $words) {
        foreach ($words as $word => $weight) {
            if (strpos($question, $word) !== false) {
                $scores[$mood] += $weight;
            }
        }
    }

    if (strtoupper($question) === $question && preg_match('/[a-z]/i', $question)) {
        $scores['frustration'] += 2;
    }

    // Déterminer l'humeur avec le score le plus élevé
    $highestMood = array_keys($scores, max($scores));

    return $highestMood[0];
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
    <div class="help_main_ctn">
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
                    <p>Pour une question, commencez votre phrase par "Quel" ou "Quelle" et finissez par "?"</p>
                </div>
                        
                <div class="help_response">
                    <p>
                        <?php if (!empty($response) && !empty($humeurtxt)): ?>
                            <p>
                                <?php echo $humeurtxt . ' ' . $response; ?>
                            </p>
                        <?php elseif (!empty($response)): ?>
                            <p>
                                <?php echo $response; ?>
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
    </div>
    <footer>
        <div class="help_footer">
            <p>Jonathanbtq Certified reserved</p>
        </div>
    </footer>
    
</body>
</html>
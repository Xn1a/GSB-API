<?php
/**
 * Fonctions pour l'application GSB
 *
 * PHP Version 7
 *
 * @category  PPE
 * @package   GSB
 * @author    Cheri Bibi - Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

/**
 * Teste si un quelconque Utilisateur est connecté
 *
 * @return Boolean
 */
function estConnecte()
{
    return isset($_SESSION['idUtilisateur']);
}

/**
 * Enregistre dans une variable session les infos d'un Utilisateur
 *
 * @param String $idVisiteur ID du Utilisateur
 * @param String $nom        Nom du Utilisateur
 * @param String $prenom     Prénom du Utilisateur
 * @param Int $fonction Fonction de l'utilisateur (0 pour visiteur et 1 pour comptable)
 *
 * @return void
 */
function connecter($idUtilisateur, $nom, $prenom, $fonction)
{
    $_SESSION['idUtilisateur'] = $idUtilisateur;
    $_SESSION['nom'] = $nom;
    $_SESSION['prenom'] = $prenom;
    $_SESSION['fonction'] = $fonction;
}

/**
 * Détruit la session active
 *
 * @return void
 */
function deconnecter()
{
    session_destroy();
}

/**
 * Transforme une date au format français jj/mm/aaaa vers le format anglais
 * aaaa-mm-jj
 *
 * @param String $maDate au format  jj/mm/aaaa
 *
 * @return Date au format anglais aaaa-mm-jj
 */
function dateFrancaisVersAnglais($maDate)
{
    @list($jour, $mois, $annee) = explode('/', $maDate);
    return date('Y-m-d', mktime(0, 0, 0, $mois, $jour, $annee));
}

/**
 * Transforme une date au format format anglais aaaa-mm-jj vers le format
 * français jj/mm/aaaa
 *
 * @param String $maDate au format  aaaa-mm-jj
 *
 * @return Date au format format français jj/mm/aaaa
 */
function dateAnglaisVersFrancais($maDate)
{
    @list($annee, $mois, $jour) = explode('-', $maDate);
    $date = $jour . '/' . $mois . '/' . $annee;
    return $date;
}

/**
 * Retourne le mois au format aaaamm selon le jour dans le mois
 *
 * @param String $date au format  jj/mm/aaaa
 *
 * @return String Mois au format aaaamm
 */
function getMois($date)
{
    @list($jour, $mois, $annee) = explode('/', $date);
    unset($jour);
    if (strlen($mois) == 1) {
        $mois = '0' . $mois;
    }
    return $annee . $mois;
}

/* gestion des erreurs */

/**
 * Indique si une valeur est un entier positif ou nul
 *
 * @param Integer $valeur Valeur
 *
 * @return Boolean vrai ou faux
 */
function estEntierPositif($valeur)
{
    return preg_match('/[^0-9]/', $valeur) == 0;
}

/**
 * Indique si un tableau de valeurs est constitué d'entiers positifs ou nuls
 *
 * @param Array $tabEntiers Un tableau d'entier
 *
 * @return Boolean vrai ou faux
 */
function estTableauEntiers($tabEntiers)
{
    $boolReturn = true;
    foreach ($tabEntiers as $unEntier) {
        if (!estEntierPositif($unEntier)) {
            $boolReturn = false;
        }
    }
    return $boolReturn;
}

/**
 * Vérifie si une date est inférieure d'un an à la date actuelle
 *
 * @param String $dateTestee Date à tester
 *
 * @return Boolean vrai ou faux
 */
function estDateDepassee($dateTestee)
{
    $dateActuelle = date('d/m/Y');
    @list($jour, $mois, $annee) = explode('/', $dateActuelle);
    $annee--;
    $anPasse = $annee . $mois . $jour;
    @list($jourTeste, $moisTeste, $anneeTeste) = explode('/', $dateTestee);
    return ($anneeTeste . $moisTeste . $jourTeste < $anPasse);
}

/**
 * Vérifie la validité du format d'une date française jj/mm/aaaa
 *
 * @param String $date Date à tester
 *
 * @return Boolean vrai ou faux
 */
function estDateValide($date)
{
    $tabDate = explode('/', $date);
    $dateOK = true;
    if (count($tabDate) != 3) {
        $dateOK = false;
    } else {
        if (!estTableauEntiers($tabDate)) {
            $dateOK = false;
        } else {
            if (!checkdate($tabDate[1], $tabDate[0], $tabDate[2])) {
                $dateOK = false;
            }
        }
    }
    return $dateOK;
}

/**
 * Vérifie que le tableau de frais ne contient que des valeurs numériques
 *
 * @param Array $lesFrais Tableau d'entier
 *
 * @return Boolean vrai ou faux
 */
function lesQteFraisValides($lesFrais)
{
    return estTableauEntiers($lesFrais);
}

/**
 * Vérifie la validité des trois arguments : la date, le libellé du frais
 * et le montant
 *
 * Des message d'erreurs sont ajoutés au tableau des erreurs
 *
 * @param String $dateFrais Date des frais
 * @param String $libelle   Libellé des frais
 * @param Float  $montant   Montant des frais
 *
 * @return void
 */
function valideInfosFrais($dateFrais, $libelle, $montant)
{
    if ($dateFrais == '') {
        ajouterErreur('Le champ date ne doit pas être vide');
    } else {
        if (!estDatevalide($dateFrais)) {
            ajouterErreur('Date invalide');
        } else {
            if (estDateDepassee($dateFrais)) {
                ajouterErreur(
                    "date d'enregistrement du frais dépassé, plus de 1 an"
                );
            }
        }
    }
    if ($libelle == '') {
        ajouterErreur('Le champ description ne peut pas être vide');
    }
    if ($montant == '') {
        ajouterErreur('Le champ montant ne peut pas être vide');
    } elseif (!is_numeric($montant)) {
        ajouterErreur('Le champ montant doit être numérique');
    }
}

/**
 * Ajoute le libellé d'une erreur au tableau des erreurs
 *
 * @param String $msg Libellé de l'erreur
 *
 * @return void
 */
function ajouterErreur($msg)
{
    if (!isset($_REQUEST['erreurs'])) {
        $_REQUEST['erreurs'] = array();
    }
    $_REQUEST['erreurs'][] = $msg;
}

/**
 * Ajoute le libellé d'un message d'information au tableau des méssages d'information
 *
 * @param String $msg Libellé du message d'information
 *
 * @return void
 */
function ajouterInfo($msg)
{
    if (!isset($_REQUEST['infos'])) {
        $_REQUEST['infos'] = array();
    }
    $_REQUEST['infos'][] = $msg;
}

/**
 * Retoune le nombre de lignes du tableau des erreurs
 *
 * @return Integer Le nombre d'erreurs
 */
function nbErreurs()
{
    if (!isset($_REQUEST['erreurs'])) {
        return 0;
    } else {
        return count($_REQUEST['erreurs']);
    }
}

/**
 * Teste la connexion d'un visiteur
 *
 * @param PdoGsb $pdo L'objet représentant la base de données de l'application GSB
 * @param String $login Le nom d'utilisateur du visiteur qui souhaite tester ses identifiants
 * @param String $mdp Le mot de passe du visiteur
 * @return Int L'identifiant du visiteur qui souhaite se connecter
 */
function testerConnexion($pdo, $login, $mdp)
{
    $utilisateur = $pdo->getInfosUtilisateur($login, $mdp);
    // Si les identifiants sont incorrectes ou qu'une erreur est survenue
    if (!is_array($utilisateur)) {
        return false;
    } else {
        // Si l'utilisateur est un comptable
        if($utilisateur['fonction'] == 1) {
            $reponse['estConnecte'] = false;
            $reponse['erreur'] = true;
            $reponse['message'] = 'Seuls les visiteurs peuvent utiliser cette API';
            echo json_encode($reponse);
            die();
        }
    }
    return $utilisateur['id'];
}

/**
 * Renvoie une erreur en JSon à l'application cliente
 *
 * @param String $message
 * @return void
 */
function envoyerErreur($message) {
    $reponse = array();
    $reponse['erreur'] = true;
    $reponse['message'] = $message;
    echo json_encode($reponse);
}

/**
 * Cherche l'élement correspondant à la recherche dans un tableau associatif
 *
 * @param Object $valeur La valeur a rechercher pour la $cle donnée
 * @param Object $cle La clé pour laquelle on cherche la $valeur
 * @param Array $assoc Le tableau associatif
 * @return void
 */
function chercheDansAssoc($valeur, $cle, $assoc)
{
    foreach ($assoc as $elem) {
        if ($elem[$cle] == $valeur) {
            return $elem;
        }
    }
    return null;
}
?>
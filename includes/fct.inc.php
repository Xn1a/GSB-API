<?php
/**
 * Fonctions pour l'API de la BD de GSB
 *
 * PHP Version 7
 *
 * @category  PPE
 * @package   GSB
 * @author    Pauline Gaonac'h <pauline.gaod@gmail.com>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */


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
    $reponse['estConnecte'] = false;
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
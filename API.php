<?php
require_once 'includes/fct.inc.php';
require_once 'includes/class.pdogsb.inc.php';

header('Content-Type: application/json');

$pdo = PdoGsb::getPdoGsb();
$operation = $_GET['operation'];

if (empty($_GET['login']) && empty($_GET['mdp'])) {
    envoyerErreur("Vous n'etes pas autorisé à accéder à ce serveur");
    die();
}

if (empty($_GET['operation'])) {
    envoyerErreur("Aucune opération n'a été spécifiée");
    die();
}

$idUtilisateur = testerConnexion($pdo, $_GET['login'], $_GET['mdp']);
if (!$idUtilisateur) {
    $reponse['estConnecte'] = false;
    $reponse['erreur'] = true;
    $reponse['message'] = 'Identifiants incorrectes';
    die();
    echo json_encode($reponse);
}

$reponse['erreur'] = false;

switch ($operation) {

    case 'testerConnexion':
        $reponse['estConnecte'] = true;
        break;

    case 'creerFraisHf':
        if (!isset($_POST['mois']) || !isset($_POST['libelle']) || !isset($_POST['date'])
            || !isset($_POST['montant'])) {
            envoyerErreur("Certains champs sont manquants");
            die();
        }
        $pdo->creeNouveauFraisHorsForfait($idUtilisateur, $_POST['mois'], $_POST['libelle']
            , $_POST['date'], $_POST['montant']);
        break;

    case 'creerFraisForfait':
        if (!isset($_POST['mois']) || !isset($_POST['qteEtape']) || !isset($_POST['qteRepas'])
            || !isset($_POST['qteNuitee']) || !isset($_POST['qteKm'])) {
            envoyerErreur("Certains champs sont manquants");
            die();
        }

        // On crée les lignes de frais forfait si elles n'existent pas encore
        $lesFraisBD = $pdo->getLesFraisForfait($idUtilisateur, $_POST['mois']);
        if (count($lesFraisBD) == 0) {
            $pdo->creeNouvellesLignesFrais($idUtilisateur, $_POST['mois']);
        }

        // Mise à jour des frais forfait
        $lesFraisForfait = array();

        if ($_POST['qteEtape'] != -1) {
            $lesFraisForfait['ETP'] = $_POST['qteEtape'];
        } else {
            $lesFraisForfait['ETP'] = chercheDansAssoc("ETP", "idfrais", $lesFraisBD)['quantite'];
        }

        if ($_POST['qteRepas'] != -1) {
            $lesFraisForfait['REP'] = $_POST['qteRepas'];
        } else {
            $lesFraisForfait['REP'] = chercheDansAssoc("REP", "idfrais", $lesFraisBD)['quantite'];
        }

        if ($_POST['qteKm'] != -1) {
            $lesFraisForfait['KM'] = $_POST['qteKm'];
        } else {
            $lesFraisForfait['KM'] = chercheDansAssoc("KM", "idfrais", $lesFraisBD)['quantite'];
        }

        if ($_POST['qteNuitee'] != -1) {
            $lesFraisForfait['NUI'] = $_POST['qteNuitee'];
        } else {
            $lesFraisForfait['NUI'] = chercheDansAssoc("NUI", "idfrais", $lesFraisBD)['quantite'];
        }

        $pdo->majFraisForfait($idUtilisateur, $_POST['mois'], $lesFraisForfait);
        break;

    default:
        $reponse['erreur'] = true;
        $reponse['message'] = "L'opération demandée est inconnue";
        break;
}

echo json_encode($reponse);

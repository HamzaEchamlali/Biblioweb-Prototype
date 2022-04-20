<?php 
// Lance la session
session_start();

// -- AUTHENTIFICATION START --

// Si l'utilisateur est connecté à un compte
if(!empty($_SESSION["login"])){

    // Creer un message personaliser pour les membres existant
    if($_SESSION["compte"]=="existant"){
        $accueil = "Salut ".$_SESSION["login"].". Heureux de te revoir 😋";
        $login = $_SESSION["login"];
        $statut = $_SESSION["statut"];
    
    // Creer un message personaliser pour les nouveaux membres    
    }else{
        $accueil = "Bienvenu ".$_SESSION["login"].". Ton compte est désormais activé 🥳";
        $login = $_SESSION["login"];
        $statut = $_SESSION["statut"];
    }

// Sinon renvoie l'utilisateur vers la page de connexion et arrète le script
}else{
    header("Status: 301 Moved Permanently");
    header("location: login.php");
    exit();
}

// -- AUTHENTIFICATION END --


// -- DATABASE START --

// Inclus les informations de configuration de la base de donnée
require "config.php";

// Lance la connexion à la base de donnée
$db = @mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DBNAME);

// Si la connexion à la base de donnée à réussis
if($db){

    // Si le champs de recherche n'est pas vide ou que des auteurs on déjà été rechercher
    if(!empty($_GET["rechercher_auteur"]) || !empty($_SESSION["auteur_rechercher"])){

        // Si le champs de recherche n'est pas vide
        if(!empty($_GET["rechercher_auteur"])){
            // Recherche l'auteur dans la database
            $auteur_rechercher = $_GET["rechercher_auteur"];
            $query = "SELECT `lastname` FROM `authors` WHERE `lastname` LIKE '%".$auteur_rechercher."%';";
            $result = mysqli_fetch_assoc(mysqli_query($db,$query));
            
            // Si l'auteur existe stocke son nom dans une variable
            if(!empty($result)){
                $auteur_rechercher = $result["lastname"];
            // Sinon stocke une erreur
            }else{
                $auteur_rechercher .= "❌";
            }
        }

        $separateur = "|";

        // Sauve les auteurs recherché dans une variable de session et les stocke dans un tableau d'auteurs si
        // Une seule recherche effectué
        if(!isset($_SESSION["auteur_rechercher"]) && !empty($auteur_rechercher)){
            $_SESSION["auteur_rechercher"] = $auteur_rechercher.$separateur;
            $tab_auteurs = tableau_auteur($_SESSION["auteur_rechercher"], $separateur);

        // Moins de 3 recherche on été éffectuer
        }else if(nombre_auteur($_SESSION["auteur_rechercher"], $separateur) < 3 && !empty($auteur_rechercher)){
            $_SESSION["auteur_rechercher"] .= $auteur_rechercher.$separateur;
            $tab_auteurs = tableau_auteur($_SESSION["auteur_rechercher"], $separateur);

        // Pas de recherche effectué
        }else if(empty($auteur_rechercher)){
            $tab_auteurs = tableau_auteur($_SESSION["auteur_rechercher"], $separateur);

        // Plus de 3 recherche éffectué
        }else{
            $tab_auteurs = tableau_auteur($_SESSION["auteur_rechercher"], $separateur);
            $substitut = $tab_auteurs[1];
            $tab_auteurs[1] = $tab_auteurs[2];
            $tab_auteurs[0] = $substitut;
            $tab_auteurs[2] = $auteur_rechercher; 
            $_SESSION["auteur_rechercher"] = tableau_conversion_chaine($tab_auteurs,$separateur);
        }

        //Stocke les livres des auteurs recherché dans un tableau
        $auteurs_livres = array();
        $livres = array();

        for($i = 0; $i < count($tab_auteurs); $i++){
            $query = "SELECT `title` FROM `books` JOIN `authors` on `author_id` = `authors`.id WHERE `lastname` = '".$tab_auteurs[$i]."';";
            $result = mysqli_query($db,$query);

            while($row = mysqli_fetch_assoc($result)){
                $livres[] = $row;
            }
    
            $auteurs_livres[] = $livres;
            $livres = [];
        }
        //Libère la mémoire et ferme la connexion a la database
        mysqli_free_result($result);
        mysqli_close($db);

        // Evite de relancer la query string et de remplir avec un doublon la variable de session, si l'utilisateur rafraichis la page 
        if($_GET["reload"]==0){
            header("location:index.php?reload=1");
        }

    // Le champs de recherche est vide et qu'aucuns auteurs n'a déjà été rechercher
    }else{
    }

// La connexion à la base de donnée à échoué
}else{
}

// -- DATABASE END --


// -- FUNCTION START --

// Compte le nombre d'auteurs dans une chaine de caractère contenant des séparateurs
function nombre_auteur($chaine, $separateur){
    $nombre_auteur = 0;
    for($i = 0; $i < strlen($chaine); $i++){
        if($chaine[$i] == $separateur){
            $nombre_auteur++;
        }
    }
    return $nombre_auteur;
}

// Crée un tableau d'auteurs a partir d'une chaine de caractère contenant des séparateurs
function tableau_auteur($chaine, $separateur){
    $mot = "";
    $tab = [];
    for($i = 0; $i < strlen($chaine); $i++){
        if($chaine[$i] != $separateur){
            $mot .= $chaine[$i];
        }else{
            $tab[] = $mot;
            $mot = "";
        }
    }
    return $tab;
}

// Convertit un tableau en chaine de caractère et ajoute des séparateurs 
function tableau_conversion_chaine($tab, $separateur){
    $chaine_auteurs = "";
    for($i = 0; $i < count($tab); $i++){
        $chaine_auteurs .= $tab[$i].$separateur;
    }
    return $chaine_auteurs;
}

// -- FUNCTION END --
?>

<!DOCTYPE html>

<html lang="fr">

    <head>
        <link rel="stylesheet" href="css/style.css?v=1">
        <meta charset="utf-8">
        <title> Accueil - Biblioweb </title>
    </head>

    <body>
       <!-- Header -->
       <?php include "include/header.php"; ?>

       <!-- Deconnexion -->
       <p> 
            <a style="background-color:rgb(87, 13, 13);" class="boutton_administration" href="login.php"> Deconnexion </a>
        </p>

        <!-- Session -->
       <h2>Session 💻</h2>
       <p><?= $accueil; ?></p>
       <ul>
           <li>Compte : <?= $login; ?></li>
           <li>Statut : <?= $statut; ?></li>
       </ul>

       <!-- Presentation -->
       <h2>Présentation 🙋🏽</h2>
       <p> Sur Biblioweb, tu peux utiliser les fonctionalités suivantes : </p>
        <ul>
            <li>Rechercher les livres d’un auteur donné ; </li>
            <li>Tu peux taper entièrement ou partiellement sont nom ; </li>
            <li>Les 3 dernières recherches seront enregistrer dans ta session ; </li>
            <li style="font-weight:bold; color:green">Si tu es admin, utilise le bouton "modération" dans la section "Administration".</li>
        </ul>
        
        <!-- Administration -->
        <h2>Administration 👮🏻</h2>

        <!-- Si l'utilisateur à le statut admin -->
        <?php if(!empty($_SESSION["statut"]) && $_SESSION["statut"]=="admin"){ ?>
            <p>En tant qu'administrateur tu as accès au droits d'administration via le bouton suivant :</p>
            <p style="text-align:center">
                <a style="background-color:rgb(20, 60, 112);" class="boutton_administration" href="users.php"> Modération </a> 
            </p>

        <!-- Sinon -->
        <?php }else{?>
            <p>Pour afficher le bouton d'administration, déconnecte toi et utilise les informations de connexion suivantes : </p>
            <ul>
                <li>login : root </li>
                <li>Mot de passe : epfc</li>
            </ul>
        <?php } ?>

        <!-- Recherche -->
        <h2>Recherche ✍</h2>
        <form action="" method="get"> 
            <div>
                <input type="text" name="rechercher_auteur" placeholder="Inscrire un nom d'auteur" required>
                <input type="submit" value="Rechercher">
            </div>
        </form>

        <!-- Si des auteurs on été recherché -->
        <?php if(!empty($tab_auteurs)){?>
            <h2> Auteurs recherchés 💾</h2>
            <ul>
                <?php for($i = 0; $i < count($tab_auteurs); $i++){ ?>
                    <li> <?= $tab_auteurs[$i];?> </li> 
                <?php } ?>
            </ul>
        <?php } ?>
        
        <!-- Si des livres d'auteur on été trouvé -->
        <?php if(!empty($auteurs_livres)){?>
            <h2> Livres des auteurs recherchés </h2>
            <?php for($i = 0; $i < count($tab_auteurs) ; $i++){?>
                <ul>  
                    <li><?= $tab_auteurs[$i];?>    
                        <?php for($e = 0; $e < count($auteurs_livres[$i]); $e++){ ?> 
                            <ul> 
                                <li> <?= $auteurs_livres[$i][$e]["title"]; ?> </li> 
                            </ul>
                        <?php } ?>
                    </li>
                </ul>
            <?php } ?>
        <?php } ?>

        <!-- Footer -->
        <?php include "include/footer.php"; ?>

    </body>

</html>
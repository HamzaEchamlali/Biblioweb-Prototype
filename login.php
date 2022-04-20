<?php

// Lance la session
session_start();

// Supprime les variables de session
unset($_SESSION["login"]);
unset($_SESSION["statut"]);
unset($_SESSION["compte"]);
unset($_SESSION["auteur_rechercher"]);


// -- DATABASE START --

// Inclus les informations de configuration de la base de donnée
require "config.php";

// Lance la connexion à la base de donnée
$db = @mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DBNAME);

// Si la connexion à la base de donnée à réussis
if($db){
    
    // Si un login à été envoyer
    if(!empty($_POST["login"])){
        $login = $_POST["login"];
        $password = $_POST["password"];
        
        // Recupère le mdp et le statut qui correspond au login envoyer
        $query="SELECT `password`, `statut` FROM `users` WHERE `login` = '".$login."';";
        $result = mysqli_fetch_assoc(mysqli_query($db,$query));

        // Si ceux-ci existent
        if($result){

            $hash = $result["password"];

            // Si le mdp entré correspond à l'algorytme de hashage 
            if(password_verify($password,$hash)){

                // Creer les variables de session du compte utilisateur
                $compte = "existant";
                $statut = $result["statut"];

                $_SESSION["login"] = $login;
                $_SESSION["compte"] = $compte;
                $_SESSION["statut"] = $statut;
                
                // Envoie l'utilisateur vers la page d'accueil et arrète le script courant
                header("Status: 301 Moved Permanently");
                header("location: index.php");
                exit();

            // Le mdp ne correspond pas à l'algorytme de hashage     
            }else{
                $erreur_connexion = "Le nom d'utilisateur ou le mot de passe est incorrect ❌";
            }

        // Aucun utilisateurs enregistré corresponds au login entré       
        }else{
            $erreur_connexion = "Le nom d'utilisateur ou le mot de passe est incorrect ❌";
        }
    
    // Aucun login n'a été envoyer    
    }else{
    
    }

// La connexion à la base de donnée à échoué
}else{
    exit;
}

// -- DATABASE END --
?>

<!DOCTYPE html>

<html lang="fr">

    <head>
        <link rel="stylesheet" href="css/style.css?v=1">
        <meta charset="utf-8">
        <title> Connexion - Biblioweb </title>
    </head>

    <body>
        <!-- Header -->
        <?php include "include/header.php"; ?>

        <!-- Connexion -->
        <h2> Connexion </h2>

        <!-- Si une information n'est pas valide -->
        <?php if(!empty($erreur_connexion)){?>
            <p> <?= $erreur_connexion; ?> </p>
        <?php } ?>

        <form action="" method="post">
            <div>
                <label> Login </label>
                <input type="text" name="login" required>
            </div>
    
            <div>
                <label> Mot de passe </label>
                <input type="password" name="password" required>
            </div>

            <div>
                <input type="submit" value="Se connecter"> 
            </div>
        </form>

        <!-- Redirection vers la page d'inscription -->
        <p>Vous n'avez pas de compte ? <a href="register.php">  Inscrivez-vous </a> </p>

        <!-- Footer -->
        <?php include "include/footer.php"; ?>
    </body>

</html>
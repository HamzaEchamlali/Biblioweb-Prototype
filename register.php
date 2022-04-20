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
        $email = $_POST["email"];
        $email_confirmation = $_POST["email_confirmation"];

        // Si le mail correspond au mail de confirmation
        if($email == $email_confirmation){
            $login = $_POST["login"];

            $query = "SELECT `email` FROM `users` WHERE `email` = '".$email."';";
            $result_email = mysqli_fetch_assoc(mysqli_query($db,$query));

            $query = "SELECT `login` FROM `users` WHERE `login` = '".$login."';";
            $result_login = mysqli_fetch_assoc(mysqli_query($db,$query));

            // Si un autre utilisateur utilise déjà cette email
            if($result_email){
                $erreur_inscription = "Un compte existant utilise déjà cette adresse email ❌";
            
            // Si un autre utilisateur utilise déjà ce login
            }else if($result_login){
                $erreur_inscription = "Un compte existant utilise déjà ce login ❌";
            
            // Si le login et l'email sont libre 
            }else{
            $statut = "novice";
            $compte = "nouveau";

            // Chiffre le mdp
            $password = $_POST["password"];
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert les informations de l'utilisateur dans la base de donnée
            $query = "INSERT INTO `users` (`login`,`email`,`statut`,`password`,`created_at`) VALUES('".$login."','".$email."', '".$statut."','".$hash."',NOW());";
            $result = mysqli_query($db,$query);

            // Creer les variables de session du compte utilisateur
            $_SESSION["login"] = $login;
            $_SESSION["statut"] = $statut;
            $_SESSION["compte"] = $compte;
            
            // Envoie l'utilisateur vers la page d'accueil et arrète le script courant
            header("Status: 301 Moved Permanently");            
            header("location:index.php");
            exit();
            }

        // Le mail ne correspond pas au mail de confirmation
        }else{
            $erreur_inscription = "Votre email doit être identique au mail de confirmation ❗❗";
        }
        
        // Ferme la connexion a la base de donnée
        mysqli_close($db);
    
    // Aucun login n'a été envoyer    
    }else{

    }

// La connexion à la base de donnée à échoué
}else{
   
}
?>

<!DOCTYPE html>

<html lang="fr">

    <head>
        <link rel="stylesheet" href="css/style.css?v=1">
        <meta charset="utf-8">
        <title> Inscription - Biblioweb </title>
    </head>

    </body>
        
        <!-- Header -->
        <?php include "include/header.php"; ?>

        <!-- Inscription -->
        <h2> Inscription </h2>
        
        <!-- Si une informations n'est pas valide -->
        <?php if(!empty($erreur_inscription)){?>
            <p> <?= $erreur_inscription; ?> </p>
        <?php } ?>

        <form action="" method="post">
            <div>
                <label> Login </label>
                <input type="text" name="login" required>
            </div>

            <div>
                <label> Email </label>
                <input type="email" name="email" required>
            </div>

            <div>
                <label> Confirmation de l'email </label>
                <input type="email" name="email_confirmation" required>
            </div>

            <div>
                <label> Mot de passe </label>
                <input type="password" name="password" required>
            </div>

            <div>
                <input type="submit" value="S'inscrire">
            </div>
        </form>

        <!-- Redirection vers la page de connexion -->
        <p>Vous avez déjà un compte ? <a href="login.php"> Connectez-vous </a> </p>

        <!-- Footer -->
        <?php include "include/footer.php"; ?>

    </body>

</html>
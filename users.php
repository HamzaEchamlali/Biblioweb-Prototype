<?php 

// Lance la session
session_start();

// -- AUTHENTIFICATION START --

// Si l'utilisateur est connecté à un compte admin
if(!empty($_SESSION["statut"]) && $_SESSION["statut"]=="admin"){
    
// Sinon renvoie l'utilisateur vers la page de connexion et arrète le script courant
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

    // Si une action a choisie
    if(!empty($_GET["action"])){
        $login = $_GET["login"];
        $action = $_GET["action"];
        $statut = $_GET["statut"];

        // Promouvoir novice => habitué
        if($action == "promouvoir" && $statut == "novice"){
            $query = "UPDATE `users` SET `statut` = 'habitué' WHERE `login` = '".$login."'; ";
            $nouveau_statut = "habitué 🥈";
            $color = "green";
            $ancien_grade = "🥉";
        
        // Promouvoir habitué => expert
        }else if($action == "promouvoir" && $statut == "habitué"){
            $query = "UPDATE `users` SET `statut` = 'expert' WHERE `login` = '".$login."'; ";
            $nouveau_statut = "expert 🥇";
            $color = "green";
            $ancien_grade = "🥈";

        // Rétrograder habitué => novice
        }else if($action == "retrograder" && $statut == "habitué"){
            $query = "UPDATE `users` SET `statut` = 'novice' WHERE `login` = '".$login."'; ";
            $nouveau_statut = "novice 🥉";
            $color = "red";
            $ancien_grade = "🥈";

        // Rétrograder expert => habitué
        }else if($action == "retrograder" && $statut == "expert"){
            $query = "UPDATE `users` SET `statut` = 'habitué' WHERE `login` = '".$login."'; ";
            $nouveau_statut = "habitué 🥈";
            $color = "red";
            $ancien_grade = "🥇";
        }

        // Modifie le statut en fonction de l'action choisie
        mysqli_query($db,$query);
    
    // Aucune action n'a été choisie
    }else{
    }

    // Récupère les login et statut des utilisateur
    $query = "SELECT `login`, `statut` FROM `users`;";
    $result = mysqli_query($db,$query);

    while($row = mysqli_fetch_assoc($result)){
       $infos_user[] = $row;  
    }

    // Libère la mémoire et ferme l'accès a la base de donnée
    mysqli_free_result($result);
    mysqli_close($db);
    
// Si la connexion à la base de donnée à échoué
}else{
}

// -- DATABASE END --
?>

<!DOCTYPE html>

<html>

    <head>
        <link rel="stylesheet" href="css/style.css">
        <meta charset="utf-8">
        <title> Menu administrateur - biblioweb </title>
    </head>

    <body>

        <!-- Header -->
        <?php include "include/header.php"; ?>

        <!-- Déconnexion -->
        <p>
            <a style="background-color:rgb(87, 13, 13);" class="boutton_administration" href="login.php"> Deconnexion </a>
        </p>

        <!-- Retour accueil -->
        <p>
            <a style="background-color:rgb(4, 90, 4)" class="boutton_administration" href="index.php"> Retour à l'accueil </a> 
        </p>

        <!-- Modération -->
        <h1> Modération 👮🏻 </h1>
        <p>Vous pouvez promouvoir ou rétrograder un utilisateur.<br> Chaque statut est classé selon la hierarchie suivante : </p>
        <ol>
            <li>expert 🥇</li>
            <li>habitué🥈</li>
            <li>novice 🥉</li>
        </ol>

        <!-- Si une action est choisie -->
        <?php if(!empty($action)){?>
            <h2>Changement enregistrer 💾 </h2>
            <p> Statut modifier avec succès ✅ </p>
            <ul>
                <li>Compte : <?= $login ?></li>
                <li>Action : <span style="color:<?=$color;?>"> <?= $action; ?><span></li>
                <li>Ancien statut : <?= $statut; ?> <?= $ancien_grade; ?></li>
                <li>Nouveau statut : <?= $nouveau_statut; ?></li>
            </ul>
        <?php } ?>
        
        <!-- Comptes -->
        <h2> Compte 💻 </h2>
        <!-- Affiche l'ensemble des comptes avec le login et le statut -->
        <?php for($i = 0; $i < count($infos_user); $i++){ 
            $login = $infos_user[$i]["login"];
            $statut = $infos_user[$i]["statut"];
            
            // Statut novice
            if($statut == "novice"){ ?>
                <p> [ <?= $login;?> - <?= $statut;?> 🥉 ] 
                    <a class="boutton_administration" style="background-color:green" href="users.php?login=<?=$login;?>&action=promouvoir&statut=<?=$statut;?>">Promouvoir</a> 
                </p>
            
            <!-- Statut habitué -->    
            <?php }else if($statut == "habitué"){ ?>
                <p>[ <?= $login;?> - <?= $statut;?> 🥈 ] 
                    <a class="boutton_administration" style="background-color:green" href="users.php?login=<?=$login;?>&action=promouvoir&statut=<?=$statut;?>">Promouvoir</a> <a class="boutton_administration" style="background-color:rgb(116, 13, 13)" href="users.php?login=<?=$login;?>&action=retrograder&statut=<?=$statut;?>">Retrograder</a>
                </p>
            
            <!-- Statut expert --> 
            <?php }else if($statut == "expert"){ ?>
                <p> [ <?= $login;?> - <?= $statut;?> 🥇 ] 
                    <a class="boutton_administration" style="background-color:rgb(116, 13, 13)" href="users.php?login=<?=$login;?>&action=retrograder&statut=<?=$statut;?>">Retrograder</a>
                </p>
            <?php } ?>
        <?php } ?>
        
        <!-- Footer -->
        <?php include "include/footer.php"; ?>

    </body>

</html>
<?php
include "db.php";

if($_POST){
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $confirm = $_POST['confirm'];

    if($pass !== $confirm){
        die("Mot de passe non identique !");
    }

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO Client (Nom_Client, Prenom_Client, Email_Client, MotDePasse_Client) VALUES (?,?,?,?)");
    $stmt->execute([$nom, $prenom, $email, $hash]);

    header("Location: login.html?success=1");
}
?>

<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['ajouter'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $nationnalite = $_POST['nationnalite'];
    $adresse = $_POST['adresse'];
    $date_naissance = $_POST['date_naissance'];
    $motdepasse = password_hash($_POST['motdepasse'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO client (Nom_Client, Prenom_Client, Email_Client, Telephone_Client, Nationnalite_Client, Adresse_Client, Date_Naissance_Client, MotDePasse_Client)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $prenom, $email, $telephone, $nationnalite, $adresse, $date_naissance, $motdepasse]);

    header("Location: index.php?added=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter un client</title>
<link rel="stylesheet" href="../style.css">
<style>
body { background: #0b0b0b; color: white; font-family: Arial, sans-serif; }
.form-container {
    max-width: 600px; margin: 60px auto; background: #111; padding: 30px;
    border-radius: 12px; border: 1px solid #d4a72c;
    box-shadow: 0 0 15px rgba(212,167,44,0.3);
}
label { font-weight: bold; display:block; margin-top:10px; }
input, textarea {
    width: 100%; padding: 10px; background: #000; color: white;
    border: 1px solid #555; border-radius: 6px;
}
button {
    background: #d4a72c; border: none; color: #000; font-weight: bold;
    padding: 12px; border-radius: 8px; cursor: pointer;
    margin-top: 20px; width: 100%;
}
button:hover { opacity: .85; }
a { color:#d4a72c; text-decoration:none; display:block; text-align:center; margin-top:15px; }
</style>
</head>
<body>
<div class="form-container">
<h2>➕ Ajouter un client</h2>
<form method="POST">
    <label>Nom :</label>
    <input type="text" name="nom" required>

    <label>Prénom :</label>
    <input type="text" name="prenom" required>

    <label>Email :</label>
    <input type="email" name="email" required>

    <label>Téléphone :</label>
    <input type="text" name="telephone" required>

    <label>Nationalité :</label>
    <input type="text" name="nationnalite" required>

    <label>Adresse :</label>
    <input type="text" name="adresse" required>

    <label>Date de naissance :</label>
    <input type="date" name="date_naissance" required>

    <label>Mot de passe :</label>
    <input type="password" name="motdepasse" required>

    <button name="ajouter">💾 Enregistrer</button>
</form>
<a href="index.php">⬅ Retour</a>
</div>
</body>
</html>

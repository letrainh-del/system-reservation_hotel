<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

if(isset($_POST['save'])){
    $numero = $_POST['numero'];
    $type = $_POST['type'];
    $prix = $_POST['prix'];
    $capacite = $_POST['capacite'];
    $description = $_POST['description'];

    $sql = $conn->prepare("INSERT INTO chambre (Numero_Chambre, Type_Chambre, Prix_Chambre, Capacite_Chambre, Description_Chambre, Statut_Chambre)
                           VALUES (?, ?, ?, ?, ?, 'Disponible')");
    $sql->execute([$numero, $type, $prix, $capacite, $description]);

    header("Location: index.php?ajout=success");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter une chambre</title>
<link rel="stylesheet" href="../style.css">
<style>
.form-container {
    max-width: 600px;
    margin: 60px auto;
    background: #111;
    padding: 30px;
    border: 1px solid #d4a72c;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(212,167,44,0.3);
}
.form-container h2 {
    text-align: center;
    color: #d4a72c;
    margin-bottom: 25px;
}
.form-container label {
    font-weight: bold;
    margin: 8px 0 4px;
    display: block;
}
.form-container input,
.form-container textarea {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #555;
    background: #000;
    color: #fff;
}
.form-container textarea {
    resize: none;
    height: 80px;
}
.btn-save {
    display: block;
    width: 100%;
    padding: 12px;
    margin-top: 20px;
    background: #d4a72c;
    border: none;
    font-weight: bold;
    cursor: pointer;
    border-radius: 8px;
}
.btn-save:hover {
    opacity: .9;
}
.back-link {
    color: #d4a72c;
    text-decoration: none;
}
.back-link:hover {
    text-decoration: underline;
}
</style>
</head>
<body>

<div class="form-container">
    <h2>🛏️ Ajouter une chambre</h2>

    <form method="POST">
        <label>Numéro :</label>
        <input type="number" name="numero" required>

        <label>Type :</label>
        <input type="text" name="type" required>

        <label>Prix (FDJ) :</label>
        <input type="number" name="prix" required>

        <label>Capacité :</label>
        <input type="number" name="capacite" required>

        <label>Description :</label>
        <textarea name="description"></textarea>

        <button type="submit" name="save" class="btn-save">✅ Enregistrer</button>
    </form>

    <br>
    <a class="back-link" href="index.php">⬅ Retour</a>
</div>

</body>
</html>

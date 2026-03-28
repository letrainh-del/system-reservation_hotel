<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM chambre WHERE ID_Chambre = ?");
$stmt->execute([$id]);
$chambre = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chambre) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['update'])) {
    $numero = $_POST['numero'];
    $type = $_POST['type'];
    $prix = $_POST['prix'];
    $capacite = $_POST['capacite'];
    $description = $_POST['description'];
    $disponible = $_POST['disponible'];

    $update = $conn->prepare("
        UPDATE chambre 
        SET Numero_Chambre = ?, Type_Chambre = ?, Prix = ?, Capacite = ?, Description = ?, Disponible = ?
        WHERE ID_Chambre = ?
    ");
    $update->execute([$numero, $type, $prix, $capacite, $description, $disponible, $id]);

    header("Location: index.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier une chambre</title>
<link rel="stylesheet" href="../style.css">
<style>
body {
    background: #0b0b0b;
    color: white;
    font-family: Arial, sans-serif;
}
.form-container {
    max-width: 600px;
    margin: 60px auto;
    background: #111;
    padding: 30px;
    border-radius: 12px;
    border: 1px solid #d4a72c;
    box-shadow: 0 0 15px rgba(212,167,44,0.3);
}
h2 {
    text-align: center;
    color: #d4a72c;
    margin-bottom: 20px;
}
label {
    font-weight: bold;
    display:block;
    margin-top:10px;
}
input, textarea, select {
    width: 100%;
    padding: 10px;
    background: #000;
    color: white;
    border: 1px solid #555;
    border-radius: 6px;
}
button {
    background: #d4a72c;
    border: none;
    color: #000;
    font-weight: bold;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 20px;
    width: 100%;
}
button:hover {
    opacity: .85;
}
.back-link {
    color: #d4a72c;
    text-decoration: none;
    display: block;
    text-align: center;
    margin-top: 15px;
}
</style>
</head>
<body>

<div class="form-container">
<h2>🛠️ Modifier la chambre</h2>
<form method="POST">
    <label>Numéro :</label>
    <input type="number" name="numero" value="<?= htmlspecialchars($chambre['Numero_Chambre']) ?>">

    <label>Type :</label>
    <input type="text" name="type" value="<?= htmlspecialchars($chambre['Type_Chambre']) ?>">

    <label>Prix (FDJ) :</label>
    <input type="number" name="prix" value="<?= htmlspecialchars($chambre['Prix']) ?>">

    <label>Capacité :</label>
    <input type="number" name="capacite" value="<?= htmlspecialchars($chambre['Capacite']) ?>">

    <label>Description :</label>
    <textarea name="description"><?= htmlspecialchars($chambre['Description']) ?></textarea>

    <label>Statut :</label>
    <select name="disponible">
        <option value="1" <?= $chambre['Disponible'] == 1 ? 'selected' : '' ?>>Disponible</option>
        <option value="0" <?= $chambre['Disponible'] == 0 ? 'selected' : '' ?>>Occupée</option>
    </select>

    <button name="update">💾 Enregistrer les modifications</button>
</form>
<a href="index.php" class="back-link">⬅ Retour</a>
</div>
</body>
</html>

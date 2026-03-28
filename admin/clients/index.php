<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

// Supprimer un client
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->prepare("DELETE FROM client WHERE ID_Client = ?")->execute([$id]);
    header("Location: index.php?deleted=1");
    exit;
}

// Charger tous les clients
$stmt = $conn->query("SELECT * FROM client ORDER BY ID_Client DESC");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des clients</title>
<link rel="stylesheet" href="../style.css">
<style>
.page-container {
    max-width: 1000px;
    margin: 60px auto;
    background: #111;
    padding: 30px;
    border-radius: 12px;
    border: 1px solid #d4a72c;
    box-shadow: 0 0 15px rgba(212,167,44,0.3);
}
h1 {
    text-align: center;
    color: #d4a72c;
    margin-bottom: 25px;
}
.add-btn {
    display: inline-block;
    padding: 10px 18px;
    background: #d4a72c;
    color: #000;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
}
.add-btn:hover { opacity: .85; }
.table-container { margin-top: 20px; overflow-x: auto; }
table {
    width: 100%;
    border-collapse: collapse;
    color: #fff;
}
th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #444;
}
th { background: #222; color: #d4a72c; }
tr:hover { background: rgba(212,167,44,0.1); }
.action-links a {
    color: #d4a72c;
    text-decoration: none;
    margin: 0 5px;
}
.action-links a:hover { text-decoration: underline; }
.alert {
    background:#003000;
    color:#5fd35f;
    padding:10px;
    border-radius:6px;
    text-align:center;
    margin-bottom:10px;
}
.alert.del {
    background:#2b0000;
    color:#ff4444;
}
</style>
</head>
<body>
<div class="page-container">

<?php if (isset($_GET['deleted'])): ?>
<div class="alert del">🗑️ Client supprimé avec succès.</div>
<?php elseif (isset($_GET['added'])): ?>
<div class="alert">✅ Client ajouté avec succès.</div>
<?php elseif (isset($_GET['updated'])): ?>
<div class="alert">✅ Modifications enregistrées.</div>
<?php endif; ?>

<h1>👥 Gestion des clients</h1>

<a href="ajouter.php" class="add-btn">+ Ajouter un client</a>

<div class="table-container">
<table>
<thead>
<tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Prénom</th>
    <th>Email</th>
    <th>Téléphone</th>
    <th>Nationalité</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php if ($clients): ?>
<?php foreach ($clients as $c): ?>
<tr>
    <td><?= htmlspecialchars($c['ID_Client'] ?? '') ?></td>
    <td><?= htmlspecialchars($c['Nom_Client'] ?? '') ?></td>
    <td><?= htmlspecialchars($c['Prenom_Client'] ?? '') ?></td>
    <td><?= htmlspecialchars($c['Email_Client'] ?? '') ?></td>
    <td><?= htmlspecialchars($c['Telephone_Client'] ?? '') ?></td>
    <td><?= htmlspecialchars($c['Nationnalite_Client'] ?? '') ?></td>
    <td class="action-links">
        <a href="modifier.php?id=<?= $c['ID_Client'] ?>">Modifier</a> |
        <a href="index.php?delete=<?= $c['ID_Client'] ?>" onclick="return confirm('Supprimer ce client ?')">Supprimer</a>
    </td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="7">Aucun client trouvé.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
<br>
<a href="../index.php" class="add-btn">⬅ Retour au tableau de bord</a>
</div>
</body>
</html>

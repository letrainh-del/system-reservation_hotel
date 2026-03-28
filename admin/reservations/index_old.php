<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

// 🔄 Libération automatique des chambres dont la date de départ est passée
$conn->query("
    UPDATE chambre ch
    JOIN reserver r ON ch.ID_Chambre = r.ID_Chambre
    SET ch.Disponible = 1
    WHERE r.Date_Dapart_Reserver < CURDATE()
");

// 🗑️ Suppression d’une réservation
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Libérer la chambre avant suppression
    $stmt = $conn->prepare("SELECT ID_Chambre FROM reserver WHERE ID_Reserver = ?");
    $stmt->execute([$id]);
    $chambre = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($chambre) {
        $conn->prepare("UPDATE chambre SET Disponible = 1 WHERE ID_Chambre = ?")->execute([$chambre['ID_Chambre']]);
    }

    $conn->prepare("DELETE FROM reserver WHERE ID_Reserver = ?")->execute([$id]);
    header("Location: index.php?deleted=1");
    exit;
}

// 🔍 Charger toutes les réservations avec jointure
$sql = "
SELECT r.ID_Reserver, r.Date_Reservation_Reserver, r.Date_Arrive_Reserver, r.Date_Dapart_Reserver, r.Etat_Reserver,
       c.Nom_Client, c.Prenom_Client,
       ch.Numero_Chambre, ch.Type_Chambre
FROM reserver r
JOIN client c ON r.ID_Client = c.ID_Client
JOIN chambre ch ON r.ID_Chambre = ch.ID_Chambre
ORDER BY r.ID_Reserver DESC
";
$stmt = $conn->query($sql);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des réservations</title>
<link rel="stylesheet" href="../style.css">
<style>
.page-container {
    max-width: 1100px;
    margin: 60px auto;
    background: #111;
    padding: 30px;
    border-radius: 12px;
    border: 1px solid #d4a72c;
    box-shadow: 0 0 15px rgba(212,167,44,0.3);
}
h1 { text-align: center; color: #d4a72c; margin-bottom: 25px; }
.add-btn {
    display: inline-block; padding: 10px 18px; background: #d4a72c;
    color: #000; text-decoration: none; border-radius: 8px; font-weight: bold;
}
.add-btn:hover { opacity: .85; }
.table-container { margin-top: 20px; overflow-x: auto; }
table {
    width: 100%; border-collapse: collapse; color: #fff;
}
th, td {
    padding: 12px; text-align: center; border-bottom: 1px solid #444;
}
th { background: #222; color: #d4a72c; }
tr:hover { background: rgba(212,167,44,0.1); }
.action-links a {
    color: #d4a72c; text-decoration: none; margin: 0 5px;
}
.action-links a:hover { text-decoration: underline; }
.alert {
    background:#003000; color:#5fd35f; padding:10px; border-radius:6px;
    text-align:center; margin-bottom:10px;
}
.alert.del { background:#2b0000; color:#ff4444; }
</style>
</head>
<body>

<div class="page-container">
<?php if (isset($_GET['deleted'])): ?>
<div class="alert del">🗑️ Réservation supprimée et chambre libérée avec succès.</div>
<?php elseif (isset($_GET['added'])): ?>
<div class="alert">✅ Réservation ajoutée avec succès.</div>
<?php elseif (isset($_GET['updated'])): ?>
<div class="alert">✅ Modifications enregistrées.</div>
<?php endif; ?>

<h1>📅 Gestion des réservations</h1>

<a href="ajouter.php" class="add-btn">+ Ajouter une réservation</a>

<div class="table-container">
<table>
<thead>
<tr>
    <th>#</th>
    <th>Client</th>
    <th>Chambre</th>
    <th>Date réservation</th>
    <th>Date arrivée</th>
    <th>Date départ</th>
    <th>État</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php if ($reservations): ?>
<?php foreach ($reservations as $r): ?>
<tr>
    <td><?= $r['ID_Reserver'] ?></td>
    <td><?= htmlspecialchars($r['Nom_Client'] . " " . $r['Prenom_Client']) ?></td>
    <td><?= htmlspecialchars($r['Type_Chambre']) ?> (N°<?= $r['Numero_Chambre'] ?>)</td>
    <td><?= htmlspecialchars($r['Date_Reservation_Reserver']) ?></td>
    <td><?= htmlspecialchars($r['Date_Arrive_Reserver']) ?></td>
    <td><?= htmlspecialchars($r['Date_Dapart_Reserver']) ?></td>
    <td><?= htmlspecialchars($r['Etat_Reserver']) ?></td>
    <td class="action-links">
        <a href="modifier.php?id=<?= $r['ID_Reserver'] ?>">Modifier</a> |
        <a href="index.php?delete=<?= $r['ID_Reserver'] ?>" onclick="return confirm('Supprimer cette réservation ?')">Supprimer</a>
    </td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="8">Aucune réservation enregistrée.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<br>
<a href="../index.php" class="add-btn">⬅ Retour au tableau de bord</a>
</div>
</body>
</html>

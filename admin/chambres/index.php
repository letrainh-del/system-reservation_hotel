<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

// Supprimer une chambre
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->prepare("DELETE FROM chambre WHERE ID_Chambre = ?")->execute([$id]);
    header("Location: index.php?deleted=1");
    exit;
}

// Charger toutes les chambres
$stmt = $conn->query("SELECT * FROM chambre ORDER BY ID_Chambre DESC");
$chambres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des chambres</title>
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
.add-btn:hover {
    opacity: .85;
}
.table-container {
    margin-top: 20px;
    overflow-x: auto;
}
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
th {
    background: #222;
    color: #d4a72c;
}
tr:hover {
    background: rgba(212,167,44,0.1);
}
.action-links a {
    color: #d4a72c;
    text-decoration: none;
    margin: 0 5px;
}
.action-links a:hover {
    text-decoration: underline;
}
.status {
    font-weight: bold;
    color: #5dbb63;
}
</style>
</head>
<body>

<div class="page-container">

    <?php if (isset($_GET['deleted'])): ?>
    <div style="background:#2b0000;color:#ff4444;padding:10px;border-radius:6px;text-align:center;margin-bottom:10px;">
        🗑️ Chambre supprimée avec succès.
    </div>
    <?php elseif (isset($_GET['updated'])): ?>
    <div style="background:#003000;color:#5fd35f;padding:10px;border-radius:6px;text-align:center;margin-bottom:10px;">
        ✅ Modifications enregistrées.
    </div>
    <?php endif; ?>

    <h1>🏨 Gestion des chambres</h1>


    <a href="ajouter.php" class="add-btn">+ Ajouter une chambre</a>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Prix (FDJ)</th>
                    <th>Capacité</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($chambres): ?>
                    <?php foreach ($chambres as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Numero_Chambre']) ?></td>
                            <td><?= htmlspecialchars($row['Type_Chambre']) ?></td>
                            <td><?= number_format($row['Prix'], 0, ',', ' ') ?></td>
                            <td><?= htmlspecialchars($row['Capacite']) ?></td>
                            <td class="status"><?= $row['Disponible'] ? 'Disponible' : 'Occupée' ?></td>
                            <td class="action-links">
                                <a href="modifier.php?id=<?= $row['ID_Chambre'] ?>">Modifier</a> |
                                <a href="index.php?delete=<?= $row['ID_Chambre'] ?>" onclick="return confirm('Supprimer cette chambre ?')">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">Aucune chambre enregistrée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <br>
    <a href="../index.php" class="add-btn">⬅ Retour au tableau de bord</a>
</div>

</body>
</html>

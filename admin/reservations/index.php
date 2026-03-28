<?php
session_start();
include "../../config/db.php";
require_once "../../api/send_email.php";

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

$message = '';
$message_type = '';

try {
    // 🔄 Libération automatique des chambres
    $conn->exec("
        UPDATE chambre ch
        JOIN reserver r ON ch.ID_Chambre = r.ID_Chambre
        SET ch.Disponible = 1
        WHERE r.Date_Dapart_Reserver < CURDATE()
    ");

    // 🗑️ Suppression
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
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

    // ✏️ Mise à jour
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
        $id_reserver = (int)$_POST['id_reserver'];
        $etat = $_POST['etat'] ?? '';
        
        if (!in_array($etat, ['En attente', 'Confirmée', 'Annulée', 'Complétée'])) {
            throw new Exception("État invalide");
        }

        $stmt = $conn->prepare("UPDATE reserver SET Etat_Reserver = ? WHERE ID_Reserver = ?");
        $stmt->execute([$etat, $id_reserver]);
        
        $message = "✅ Réservation mise à jour";
        $message_type = "success";
    }

    // 🔍 Chargement avec filtres
    $filtre_etat = $_GET['filtre_etat'] ?? '';
    $filtre_date = $_GET['filtre_date'] ?? '';

    $sql = "
    SELECT r.ID_Reserver, r.Date_Reservation_Reserver, r.Date_Arrive_Reserver, r.Date_Dapart_Reserver, 
           r.Etat_Reserver, r.Prix_Total, r.ID_Client,
           c.Nom_Client, c.Prenom_Client, c.Email_Client, c.Telephone_Client,
           ch.Numero_Chambre, ch.Type_Chambre, ch.Prix
    FROM reserver r
    JOIN client c ON r.ID_Client = c.ID_Client
    JOIN chambre ch ON r.ID_Chambre = ch.ID_Chambre
    WHERE 1=1
    ";
    
    $params = [];
    if ($filtre_etat) {
        $sql .= " AND r.Etat_Reserver = ?";
        $params[] = $filtre_etat;
    }
    if ($filtre_date) {
        $sql .= " AND DATE(r.Date_Arrive_Reserver) >= ?";
        $params[] = $filtre_date;
    }
    $sql .= " ORDER BY r.Date_Arrive_Reserver DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $message = "❌ Erreur: " . $e->getMessage();
    $message_type = "error";
    $reservations = [];
}

// Calculer les stats
$totalReservations = count($reservations);
$confirmeesCount = count(array_filter($reservations, fn($r) => $r['Etat_Reserver'] === 'Confirmée'));
$revenus = array_sum(array_map(fn($r) => floatval($r['Prix_Total'] ?? 0), $reservations));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion des réservations</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .page-container {
            max-width: 1200px;
            margin: 60px auto;
            background: #111;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #d4a72c;
        }

        h1 {
            color: #d4a72c;
            margin: 0 0 20px 0;
            font-size: 2em;
        }

        .header-controls {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .header-controls a {
            padding: 10px 18px;
            background: #d4a72c;
            color: #000;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: opacity 0.2s;
        }

        .header-controls a:hover {
            opacity: 0.85;
        }

        .filters {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
            align-items: center;
        }

        .filters select, .filters input {
            padding: 8px 12px;
            background: #222;
            color: #d4a72c;
            border: 1px solid #d4a72c;
            border-radius: 4px;
            font-size: 0.95em;
        }

        .filters button {
            padding: 8px 15px;
            background: #d4a72c;
            color: #000;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .stat-card {
            background: #222;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #d4a72c;
            text-align: center;
        }

        .stat-card p {
            color: #d4a72c;
            font-size: 1.5em;
            font-weight: bold;
            margin: 5px 0;
        }

        .stat-card small {
            color: #999;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }

        .alert.success {
            background: #003000;
            color: #5fd35f;
            border-left: 4px solid #5fd35f;
        }

        .alert.error {
            background: #2b0000;
            color: #ff4444;
            border-left: 4px solid #ff4444;
        }

        .table-container {
            overflow-x: auto;
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        th {
            background: #222;
            color: #d4a72c;
            font-weight: bold;
        }

        tr:hover {
            background: rgba(212, 167, 44, 0.1);
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .actions a, .actions button {
            padding: 6px 12px;
            background: #333;
            color: #d4a72c;
            text-decoration: none;
            border: 1px solid #d4a72c;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background 0.2s;
        }

        .actions a:hover, .actions button:hover {
            background: #d4a72c;
            color: #000;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .status-attente {
            background: #ffbb33;
            color: #000;
        }

        .status-confirmee {
            background: #00c851;
            color: #000;
        }

        .status-annulee {
            background: #ff4444;
            color: #fff;
        }

        .status-completee {
            background: #5d5d5d;
            color: #fff;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: #222;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            border: 1px solid #d4a72c;
        }

        .modal-content h2 {
            color: #d4a72c;
            margin-top: 0;
        }

        .modal-content select {
            width: 100%;
            padding: 10px;
            background: #111;
            color: #d4a72c;
            border: 1px solid #d4a72c;
            border-radius: 4px;
            margin: 10px 0;
        }

        .modal-content button {
            width: 100%;
            padding: 10px;
            background: #d4a72c;
            color: #000;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }

        .close-btn {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #d4a72c;
            cursor: pointer;
        }

        .close-btn:hover {
            color: #fff;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #555;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
        }

        .back-btn:hover {
            background: #777;
        }

        @media (max-width: 768px) {
            .page-container {
                margin: 10px;
                padding: 20px;
            }

            h1 {
                font-size: 1.5em;
            }

            table {
                font-size: 0.9em;
            }

            th, td {
                padding: 8px;
            }

            .header-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .header-controls a {
                text-align: center;
            }

            .filters {
                flex-direction: column;
            }

            .filters select, .filters input, .filters button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="page-container">
    <?php if ($message): ?>
        <div class="alert <?= $message_type ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="header-controls">
        <h1>📅 Réservations</h1>
        <a href="ajouter.php">➕ Nouvelle réservation</a>
        <a href="../index.php">⬅ Tableau de bord</a>
    </div>

    <!-- Stats rapides -->
    <div class="stats">
        <div class="stat-card">
            <small>Total réservations</small>
            <p><?= $totalReservations ?></p>
        </div>
        <div class="stat-card">
            <small>Confirmées</small>
            <p><?= $confirmeesCount ?></p>
        </div>
        <div class="stat-card">
            <small>Revenus totaux</small>
            <p><?= number_format($revenus, 0, ',', ' ') ?> DJF</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="filters">
        <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; width: 100%;">
            <select name="filtre_etat">
                <option value="">-- Tous les états --</option>
                <option value="En attente" <?= $filtre_etat === 'En attente' ? 'selected' : '' ?>>En attente</option>
                <option value="Confirmée" <?= $filtre_etat === 'Confirmée' ? 'selected' : '' ?>>Confirmée</option>
                <option value="Annulée" <?= $filtre_etat === 'Annulée' ? 'selected' : '' ?>>Annulée</option>
                <option value="Complétée" <?= $filtre_etat === 'Complétée' ? 'selected' : '' ?>>Complétée</option>
            </select>

            <input type="date" name="filtre_date" value="<?= htmlspecialchars($filtre_date) ?>" placeholder="À partir du...">

            <button type="submit">🔍 Filtrer</button>
            <?php if ($filtre_etat || $filtre_date): ?>
                <a href="index.php" style="padding: 8px 15px; background: #555; text-decoration: none; border-radius: 4px;">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tableau -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Téléphone</th>
                    <th>Chambre</th>
                    <th>Arrivée</th>
                    <th>Départ</th>
                    <th>État</th>
                    <th>Prix</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reservations): ?>
                    <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td><strong>#<?= (int)$r['ID_Reserver'] ?></strong></td>
                            <td><?= htmlspecialchars(($r['Prenom_Client'] ?? '') . ' ' . ($r['Nom_Client'] ?? '')) ?></td>
                            <td><?= htmlspecialchars($r['Telephone_Client'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['Type_Chambre'] ?? '') ?> (N°<?= (int)$r['Numero_Chambre'] ?>)</td>
                            <td><?= date('d/m/Y', strtotime($r['Date_Arrive_Reserver'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($r['Date_Dapart_Reserver'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '', $r['Etat_Reserver'])) ?>">
                                    <?= htmlspecialchars($r['Etat_Reserver'] ?? '') ?>
                                </span>
                            </td>
                            <td><?= number_format((float)$r['Prix_Total'], 0, ',', ' ') ?> DJF</td>
                            <td>
                                <div class="actions">
                                    <button onclick="openModal(<?= (int)$r['ID_Reserver'] ?>, '<?= htmlspecialchars($r['Etat_Reserver'] ?? '') ?>')">
                                        Modifier
                                    </button>
                                    <a href="?delete=<?= (int)$r['ID_Reserver'] ?>" 
                                       onclick="return confirm('Supprimer cette réservation ?')">
                                        Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 30px; color: #999;">
                            Aucune réservation trouvée.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="../index.php" class="back-btn">⬅ Retour au tableau de bord</a>
</div>

<!-- Modal pour modifier l'état -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Modifier l'état</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="modalReservationId" name="id_reserver">

            <label>Nouvel état:</label>
            <select name="etat" required>
                <option value="">-- Sélectionner --</option>
                <option value="En attente">📋 En attente</option>
                <option value="Confirmée">✅ Confirmée</option>
                <option value="Annulée">❌ Annulée</option>
                <option value="Complétée">✔️ Complétée</option>
            </select>

            <button type="submit">💾 Enregistrer</button>
        </form>
    </div>
</div>

<script>
function openModal(reservationId, currentState) {
    document.getElementById('modalReservationId').value = reservationId;
    document.querySelector('select[name="etat"]').value = currentState;
    document.getElementById('editModal').classList.add('active');
}

function closeModal() {
    document.getElementById('editModal').classList.remove('active');
}

window.onclick = function(event) {
    var modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.classList.remove('active');
    }
}
</script>

</body>
</html>

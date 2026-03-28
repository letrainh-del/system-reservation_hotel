<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$message_type = '';

try {
    // Charger la réservation existante
    $stmt = $conn->prepare("SELECT * FROM reserver WHERE ID_Reserver = ?");
    $stmt->execute([$id]);
    $reservationData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservationData) {
        throw new Exception("Réservation introuvable");
    }

    // Traiter le formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_client = (int)($_POST['id_client'] ?? 0);
        $id_chambre = (int)($_POST['id_chambre'] ?? 0);
        $date_arrive = $_POST['date_arrive'] ?? '';
        $date_depart = $_POST['date_depart'] ?? '';
        $etat = $_POST['etat'] ?? 'En attente';

        // Validation
        if (!$id_client || !$id_chambre) {
            throw new Exception("Client et chambre requis");
        }

        if (empty($date_arrive) || empty($date_depart)) {
            throw new Exception("Dates requises");
        }

        // Vérifier que la chambre n'est pas réservée (sauf cette réservation)
        $sql_check = "SELECT COUNT(*) as count FROM reserver WHERE ID_Chambre = ? AND ID_Reserver != ? 
                      AND ((Date_Arrive_Reserver <= ? AND Date_Dapart_Reserver > ?) 
                           OR (Date_Arrive_Reserver < ? AND Date_Dapart_Reserver >= ?))";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$id_chambre, $id, $date_depart, $date_arrive, $date_depart, $date_arrive]);
        $conflict = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($conflict['count'] > 0) {
            throw new Exception("Cette chambre n'est pas disponible pour ces dates");
        }

        // Récupérer le prix de la chambre
        $stmt_prix = $conn->prepare("SELECT Prix FROM chambre WHERE ID_Chambre = ?");
        $stmt_prix->execute([$id_chambre]);
        $chambre_data = $stmt_prix->fetch(PDO::FETCH_ASSOC);

        if (!$chambre_data) {
            throw new Exception("Chambre introuvable");
        }

        // Calculer le prix total
        $date_arrive_obj = new DateTime($date_arrive);
        $date_depart_obj = new DateTime($date_depart);
        $nuits = $date_arrive_obj->diff($date_depart_obj)->days;
        
        if ($nuits <= 0) {
            throw new Exception("La date de départ doit être après l'arrivée");
        }

        $prix_total = $nuits * $chambre_data['Prix'];

        // Mettre à jour
        $stmt_update = $conn->prepare("
            UPDATE reserver 
            SET ID_Client = ?, ID_Chambre = ?, Date_Arrive_Reserver = ?, 
                Date_Dapart_Reserver = ?, Etat_Reserver = ?, Prix_Total = ?
            WHERE ID_Reserver = ?
        ");
        $stmt_update->execute([$id_client, $id_chambre, $date_arrive, $date_depart, $etat, $prix_total, $id]);

        $message = "✅ Réservation mise à jour";
        $message_type = "success";
        
        // Rechargez les données
        $stmt = $conn->prepare("SELECT * FROM reserver WHERE ID_Reserver = ?");
        $stmt->execute([$id]);
        $reservationData = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Charger les clients et chambres
    $stmt_clients = $conn->query("SELECT ID_Client, CONCAT(Prenom_Client, ' ', Nom_Client) as name FROM client ORDER BY Prenom_Client");
    $clients = $stmt_clients->fetchAll(PDO::FETCH_ASSOC);

    $stmt_chambres = $conn->query("SELECT ID_Chambre, Numero_Chambre, Type_Chambre, Prix, Disponible FROM chambre ORDER BY Numero_Chambre");
    $chambres = $stmt_chambres->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $message = "❌ Erreur: " . $e->getMessage();
    $message_type = "error";
    $reservationData = null;
    $clients = [];
    $chambres = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier réservation</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .page-container {
            max-width: 600px;
            margin: 60px auto;
            background: #111;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #d4a72c;
        }

        h1 {
            color: #d4a72c;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #d4a72c;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 10px;
            background: #222;
            color: #fff;
            border: 1px solid #d4a72c;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box;
        }

        input:focus, select:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(212, 167, 44, 0.5);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
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

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        button, .btn-cancel {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1em;
            transition: opacity 0.2s;
        }

        button {
            background: #d4a72c;
            color: #000;
        }

        button:hover {
            opacity: 0.85;
        }

        .btn-cancel {
            background: #555;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-cancel:hover {
            background: #777;
        }

        .price-preview {
            background: #222;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
            border-left: 3px solid #d4a72c;
        }

        .price-preview p {
            margin: 5px 0;
            color: #999;
        }

        .price-preview .total {
            color: #d4a72c;
            font-size: 1.3em;
            font-weight: bold;
            margin-top: 10px;
        }

        @media (max-width: 600px) {
            .page-container {
                margin: 10px;
                padding: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .buttons {
                flex-direction: column;
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

    <?php if ($reservationData): ?>
        <h1>✏️ Modifier réservation #<?= (int)$reservationData['ID_Reserver'] ?></h1>

        <form method="POST">
            <div class="form-group">
                <label for="id_client">👤 Client:</label>
                <select name="id_client" id="id_client" required>
                    <option value="">-- Sélectionner un client --</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= (int)$client['ID_Client'] ?>" 
                                <?= (int)$reservationData['ID_Client'] == (int)$client['ID_Client'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($client['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_chambre">🛏️ Chambre:</label>
                <select name="id_chambre" id="id_chambre" required onchange="updatePrice()">
                    <option value="">-- Sélectionner une chambre --</option>
                    <?php foreach ($chambres as $chambre): ?>
                        <option value="<?= (int)$chambre['ID_Chambre'] ?>" 
                                data-prix="<?= (float)$chambre['Prix'] ?>"
                                <?= (int)$reservationData['ID_Chambre'] == (int)$chambre['ID_Chambre'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($chambre['Type_Chambre']) ?> (N°<?= (int)$chambre['Numero_Chambre'] ?>) - <?= (int)$chambre['Prix'] ?> DJF/nuit
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date_arrive">📅 Date d'arrivée:</label>
                    <input type="date" name="date_arrive" id="date_arrive" 
                           value="<?= htmlspecialchars($reservationData['Date_Arrive_Reserver']) ?>" 
                           onchange="updatePrice()" required>
                </div>

                <div class="form-group">
                    <label for="date_depart">📅 Date de départ:</label>
                    <input type="date" name="date_depart" id="date_depart" 
                           value="<?= htmlspecialchars($reservationData['Date_Dapart_Reserver']) ?>" 
                           onchange="updatePrice()" required>
                </div>
            </div>

            <div class="form-group">
                <label for="etat">📊 État:</label>
                <select name="etat" id="etat">
                    <option value="En attente" <?= $reservationData['Etat_Reserver'] === 'En attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="Confirmée" <?= $reservationData['Etat_Reserver'] === 'Confirmée' ? 'selected' : '' ?>>Confirmée</option>
                    <option value="Annulée" <?= $reservationData['Etat_Reserver'] === 'Annulée' ? 'selected' : '' ?>>Annulée</option>
                    <option value="Complétée" <?= $reservationData['Etat_Reserver'] === 'Complétée' ? 'selected' : '' ?>>Complétée</option>
                </select>
            </div>

            <div class="price-preview">
                <p>📏 Nombre de nuits: <span id="nuits">-</span></p>
                <p>💰 Prix par nuit: <span id="prix_nuit">-</span> DJF</p>
                <p class="total">💵 Total: <span id="prix_total">-</span> DJF</p>
            </div>

            <div class="buttons">
                <button type="submit">💾 Enregistrer</button>
                <a href="index.php" class="btn-cancel">❌ Annuler</a>
            </div>
        </form>
    <?php else: ?>
        <p style="color: #ff4444; text-align: center;">Réservation introuvable</p>
    <?php endif; ?>
</div>

<script>
function updatePrice() {
    const chambreSelect = document.getElementById('id_chambre');
    const dateArrive = document.getElementById('date_arrive').value;
    const dateDepart = document.getElementById('date_depart').value;

    if (!chambreSelect.value || !dateArrive || !dateDepart) {
        document.getElementById('nuits').textContent = '-';
        document.getElementById('prix_nuit').textContent = '-';
        document.getElementById('prix_total').textContent = '-';
        return;
    }

    const option = chambreSelect.options[chambreSelect.selectedIndex];
    const prix = parseFloat(option.dataset.prix);

    const arrive = new Date(dateArrive);
    const depart = new Date(dateDepart);
    const nuits = Math.ceil((depart - arrive) / (1000 * 60 * 60 * 24));

    if (nuits <= 0) {
        document.getElementById('nuits').textContent = '❌ Invalide';
        document.getElementById('prix_nuit').textContent = prix;
        document.getElementById('prix_total').textContent = '-';
        return;
    }

    const total = nuits * prix;
    document.getElementById('nuits').textContent = nuits;
    document.getElementById('prix_nuit').textContent = prix;
    document.getElementById('prix_total').textContent = total.toLocaleString();
}

// Appeler au chargement
document.addEventListener('DOMContentLoaded', updatePrice);
</script>

</body>
</html>

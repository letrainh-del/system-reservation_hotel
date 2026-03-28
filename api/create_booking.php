<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
session_start();

// DB connection
require_once "../config/db.php";
require_once "send_email.php";

// Check login
if (!isset($_SESSION['client_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Non connecté']));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$id_chambre = $data['id_chambre'] ?? null;
$date_arrive = $data['date_arrive'] ?? null;
$date_depart = $data['date_depart'] ?? null;
$mode_paiement = $data['mode_paiement'] ?? 'Espèces';

// Validate
if (!$id_chambre || !$date_arrive || !$date_depart) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Données manquantes']));
}

try {
    // Get room info
    $stmt = $conn->prepare("SELECT * FROM chambre WHERE ID_Chambre = ?");
    $stmt->execute([$id_chambre]);
    $chambre = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$chambre) {
        http_response_code(404);
        die(json_encode(['success' => false, 'message' => 'Chambre introuvable']));
    }
    
    // Check availability
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM reserver 
        WHERE ID_Chambre = ? 
        AND Date_Arrive_Reserver < ? 
        AND Date_Dapart_Reserver > ? 
        AND Etat_Reserver IN ('Confirmée', 'En attente')
    ");
    $stmt->execute([$id_chambre, $date_depart, $date_arrive]);
    $conflict = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($conflict['count'] > 0) {
        http_response_code(409);
        die(json_encode(['success' => false, 'message' => 'Chambre occupée']));
    }
    
    // Calculate price
    $nuits = (strtotime($date_depart) - strtotime($date_arrive)) / 86400;
    $prix_total = (float)$chambre['Prix'] * $nuits;
    
    // Create booking
    $stmt = $conn->prepare("
        INSERT INTO reserver (ID_Client, ID_Chambre, Date_Reservation_Reserver, 
                              Date_Arrive_Reserver, Date_Dapart_Reserver, 
                              Etat_Reserver, Prix_Total, Mode_Paiement)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['client_id'],
        $id_chambre,
        date('Y-m-d'),
        $date_arrive,
        $date_depart,
        'Confirmée',
        $prix_total,
        $mode_paiement
    ]);
    
    $id_reservation = $conn->lastInsertId();
    
    // Get client email
    $stmt = $conn->prepare("SELECT Email_Client, Nom_Client, Prenom_Client FROM client WHERE ID_Client = ?");
    $stmt->execute([$_SESSION['client_id']]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Send confirmation email
    $email_sent = false;
    if ($client && $client['Email_Client']) {
        $email_sent = send_confirmation_email(
            $client['Email_Client'],
            $client['Nom_Client'],
            $client['Prenom_Client'],
            $chambre['Type_Chambre'],
            $chambre['Numero_Chambre'],
            $date_arrive,
            $date_depart,
            $prix_total,
            $id_reservation,
            $nuits
        );
    }
    
    // Response
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Réservation confirmée',
        'reservation_id' => $id_reservation,
        'prix_total' => $prix_total,
        'nuits' => $nuits,
        'email_sent' => $email_sent
    ]);
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>

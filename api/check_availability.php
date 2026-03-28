<?php
header('Content-Type: application/json; charset=utf-8');
require_once "../config/db.php";

// Récupérer les paramètres
$date_arrive = $_POST['date_arrive'] ?? $_GET['date_arrive'] ?? null;
$date_depart = $_POST['date_depart'] ?? $_GET['date_depart'] ?? null;
$capacite = $_POST['capacite'] ?? $_GET['capacite'] ?? 1;

// Validation basique
if (!$date_arrive || !$date_depart) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Les dates sont requises'
    ]);
    exit;
}

// Convertir les dates (format d/m/Y → Y-m-d)
$date_arrive = DateTime::createFromFormat('d/m/Y', $date_arrive);
$date_depart = DateTime::createFromFormat('d/m/Y', $date_depart);

if (!$date_arrive || !$date_depart) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Format de date invalide (utilisez d/m/Y)'
    ]);
    exit;
}

// Vérifier que la date de départ est après l'arrivée
if ($date_depart <= $date_arrive) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'La date de départ doit être après la date d\'arrivée'
    ]);
    exit;
}

$date_arrive_str = $date_arrive->format('Y-m-d');
$date_depart_str = $date_depart->format('Y-m-d');

try {
    // Requête: Chambres disponibles et pas réservées sur cette période
    $sql = "
        SELECT DISTINCT c.ID_Chambre, c.Numero_Chambre, c.Type_Chambre, c.Prix, 
                        c.Capacite, c.Description, c.Image, c.Disponible, c.Statut
        FROM chambre c
        WHERE c.Capacite >= :capacite
        AND c.Disponible = 1
        AND c.ID_Chambre NOT IN (
            SELECT ID_Chambre FROM reserver
            WHERE Date_Arrive_Reserver < :date_depart
            AND Date_Dapart_Reserver > :date_arrive
            AND Etat_Reserver IN ('Confirmée', 'En attente')
        )
        ORDER BY c.Prix ASC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':capacite' => (int)$capacite,
        ':date_arrive' => $date_arrive_str,
        ':date_depart' => $date_depart_str
    ]);
    
    $chambres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculer le nombre de nuits
    $nuits = $date_depart->diff($date_arrive)->days;
    
    // Ajouter les informations supplémentaires
    foreach ($chambres as &$chambre) {
        $chambre['nuits'] = $nuits;
        $chambre['prix_total'] = (float)$chambre['Prix'] * $nuits;
    }
    
    echo json_encode([
        'success' => true,
        'chambres' => $chambres,
        'date_arrive' => $date_arrive_str,
        'date_depart' => $date_depart_str,
        'nuits' => $nuits,
        'capacite_requise' => (int)$capacite
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>

<?php
require_once __DIR__ . '/../config/db.php';

$stmt = $conn->query("
  SELECT r.ID_Reserver, CONCAT(c.Nom_Client, ' ', c.Prenom_Client) AS client, 
         ch.Type_Chambre, r.Date_Arrive_Reserver, r.Date_Dapart_Reserver, r.Etat_Reserver
  FROM reserver r
  JOIN client c ON r.ID_Client = c.ID_Client
  JOIN chambre ch ON r.ID_Chambre = ch.ID_Chambre
");
$events = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $color = '#c5a028';
  if ($row['Etat_Reserver'] === 'Annulée') $color = '#FF4560';
  elseif ($row['Etat_Reserver'] === 'En attente') $color = '#F1C232';
  $events[] = [
    'title' => $row['client'] . " — " . $row['Type_Chambre'],
    'start' => $row['Date_Arrive_Reserver'],
    'end'   => date('Y-m-d', strtotime($row['Date_Dapart_Reserver'] . ' +1 day')),
    'color' => $color
  ];
}
echo json_encode($events);
?>

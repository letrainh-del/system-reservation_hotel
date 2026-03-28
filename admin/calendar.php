<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

try {
    // Récupérer toutes les réservations
    $sql = "
        SELECT 
            r.ID_Reserver,
            CONCAT(c.Nom_Client, ' ', c.Prenom_Client) AS client,
            ch.Type_Chambre,
            r.Date_Arrive_Reserver,
            r.Date_Dapart_Reserver,
            r.Etat_Reserver
        FROM reserver r
        JOIN client c ON r.ID_Client = c.ID_Client
        JOIN chambre ch ON r.ID_Chambre = ch.ID_Chambre
    ";
    $stmt = $conn->query($sql);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Calendrier des Réservations</title>
<link rel="stylesheet" href="style.css">

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js"></script>

</head>
<body>
<div class="calendar-page">
  <aside class="sidebar">
    <h2>📅 Calendrier</h2>
    <nav>
      <ul>
        <li><a href="index.php">🏠 Tableau de bord</a></li>
        <li><a href="chambres/index.php">🛏 Chambres</a></li>
        <li><a href="reservations/index.php">📋 Réservations</a></li>
        <li><a href="clients/index.php">👥 Clients</a></li>
        <li><a href="logout.php">🚪 Déconnexion</a></li>
      </ul>
    </nav>
  </aside>

  <main class="calendar-main">
    <h1>Calendrier des Réservations</h1>
    <div id="calendar"></div>
  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 650,
    locale: 'fr',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: [
      <?php foreach($reservations as $r): 
        $color = '#c5a028'; // doré par défaut
        if ($r['Etat_Reserver'] === 'Annulée') $color = '#ff4d4d';
        elseif ($r['Etat_Reserver'] === 'En attente') $color = '#f1c232';
      ?>
      {
        title: '<?= addslashes($r['client'] . " — " . $r['Type_Chambre']) ?>',
        start: '<?= $r['Date_Arrive_Reserver'] ?>',
        end: '<?= date("Y-m-d", strtotime($r['Date_Dapart_Reserver']." +1 day")) ?>',
        color: '<?= $color ?>'
      },
      <?php endforeach; ?>
    ]
  });
  calendar.render();
});
</script>
</body>
</html>

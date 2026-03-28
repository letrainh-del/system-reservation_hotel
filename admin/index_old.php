<?php
require_once "../config/db.php";

// --- Statistiques principales ---
$totalReservations = $conn->query("SELECT COUNT(*) FROM reserver")->fetchColumn();
$totalChambres = $conn->query("SELECT COUNT(*) FROM chambre")->fetchColumn();
$totalClients = $conn->query("SELECT COUNT(*) FROM client")->fetchColumn();

// Arrivées & départs du jour
$today = date('Y-m-d');
$arrivees = $conn->query("SELECT COUNT(*) FROM reserver WHERE Date_Arrive_Reserver = '$today'")->fetchColumn();
$departs = $conn->query("SELECT COUNT(*) FROM reserver WHERE Date_Dapart_Reserver = '$today'")->fetchColumn();

// Revenu total
$revenu = $conn->query("
    SELECT SUM(c.Prix) 
    FROM reserver r 
    JOIN chambre c ON r.ID_Chambre = c.ID_Chambre
")->fetchColumn();
$revenu = $revenu ? number_format($revenu, 0, ' ', ' ') : 0;

// État des chambres
$etat = $conn->query("
    SELECT Statut, COUNT(*) AS total 
    FROM chambre 
    GROUP BY Statut
")->fetchAll(PDO::FETCH_ASSOC);

// Préparer données pour le graphique
$labels = [];
$values = [];
foreach ($etat as $row) {
    $labels[] = $row['Statut'];
    $values[] = $row['total'];
}
$labelsJSON = json_encode($labels);
$valuesJSON = json_encode($values);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tableau de bord — Royal Plaze</title>
<link rel="stylesheet" href="style.css">

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="logo">Royal Plaze</div>
  <nav>
    <ul>
      <li><a href="#" class="active">🏠 Tableau de bord</a></li>
      <li><a href="chambres/index.php">🔁 Chambres</a></li>
      <li><a href="clients/index.php">🧑‍🤝‍🧑 Clients</a></li>
      <li><a href="reservations/index.php">🧾 Réservations</a></li>
      <li><a href="calendar.php">📅 Calendrier</a></li>
      <li><a href="parametres.php">⚙️ Paramètres</a></li>
      <li><a href="logout.php">🚪 Déconnexion</a></li>
    </ul>
  </nav>
</aside>

<!-- Contenu principal -->
<main class="dashboard">
  <header>
    <h1>Tableau de bord administratif</h1>
  </header>

  <section class="stats">
    <div class="card gold">
      <h3>Total Réservations</h3>
      <p><?= $totalReservations ?></p>
    </div>
    <div class="card blue">
      <h3>Arrivées du jour</h3>
      <p><?= $arrivees ?></p>
    </div>
    <div class="card green">
      <h3>Départs du jour</h3>
      <p><?= $departs ?></p>
    </div>
    <div class="card dark">
      <h3>Revenu total</h3>
      <p><?= $revenu ?> DJF</p>
    </div>
  </section>

  <section class="charts">
    <div class="chart-container">
      <h3>Réservations sur les 7 derniers jours</h3>
      <div id="chartReservations"></div>
    </div>

    <div class="chart-container">
      <h3>État des chambres</h3>
      <div id="chartEtat"></div>
    </div>
  </section>
</main>

<script>
// === Réservations 7 derniers jours ===
var optionsReservations = {
  series: [{ name: "Réservations", data: [1, 2, 1, 3, 2, 1, 4] }],
  chart: { type: "bar", height: 300, toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: "45%" } },
  dataLabels: { enabled: false },
  xaxis: { categories: ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"] },
  colors: ["#c5a028"]
};
new ApexCharts(document.querySelector("#chartReservations"), optionsReservations).render();

// === État des chambres ===
var optionsEtat = {
  series: <?= $valuesJSON ?>,
  chart: { type: 'donut', height: 300 },
  labels: <?= $labelsJSON ?>,
  colors: ['#00c851', '#ffbb33', '#ff4444'],
  legend: { position: 'bottom', labels: { colors: '#fff' } },
  plotOptions: { pie: { donut: { size: '70%' } } }
};
new ApexCharts(document.querySelector("#chartEtat"), optionsEtat).render();
</script>

</body>
</html>

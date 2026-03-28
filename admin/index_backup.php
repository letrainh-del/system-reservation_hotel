<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

try {
    // Statistiques principales
    $today = date('Y-m-d');
    
    // Comptes
    $totalReservations = (int)$conn->query("SELECT COUNT(*) FROM reserver")->fetchColumn();
    $totalChambres = (int)$conn->query("SELECT COUNT(*) FROM chambre")->fetchColumn();
    $totalClients = (int)$conn->query("SELECT COUNT(*) FROM client")->fetchColumn();
    
    // Arrivées & départs du jour
    $stmt_arrivees = $conn->prepare("SELECT COUNT(*) FROM reserver WHERE DATE(Date_Arrive_Reserver) = ?");
    $stmt_arrivees->execute([$today]);
    $arrivees = (int)$stmt_arrivees->fetchColumn();
    
    $stmt_departs = $conn->prepare("SELECT COUNT(*) FROM reserver WHERE DATE(Date_Dapart_Reserver) = ?");
    $stmt_departs->execute([$today]);
    $departs = (int)$stmt_departs->fetchColumn();
    
    // Réservations en attente
    $stmt_attente = $conn->prepare("SELECT COUNT(*) FROM reserver WHERE Etat_Reserver = ?");
    $stmt_attente->execute(['En attente']);
    $en_attente = (int)$stmt_attente->fetchColumn();
    
    // Revenus totaux
    $stmt_revenu = $conn->query("SELECT COALESCE(SUM(Prix_Total), 0) FROM reserver");
    $revenu_total = (float)$stmt_revenu->fetchColumn();
    
    // Revenus du mois
    $stmt_revenu_mois = $conn->prepare("
        SELECT COALESCE(SUM(Prix_Total), 0) FROM reserver 
        WHERE YEAR(Date_Reservation_Reserver) = YEAR(NOW()) 
        AND MONTH(Date_Reservation_Reserver) = MONTH(NOW())
    ");
    $stmt_revenu_mois->execute();
    $revenu_mois = (float)$stmt_revenu_mois->fetchColumn();
    
    // Taux d'occupation
    $stmt_occupation = $conn->query("
        SELECT COUNT(ID_Chambre) FROM chambre WHERE Disponible = 0
    ");
    $chambres_occupees = (int)$stmt_occupation->fetchColumn();
    $taux_occupation = $totalChambres > 0 ? round(($chambres_occupees / $totalChambres) * 100, 1) : 0;
    
    // Graphique: Réservations 7 derniers jours
    $reservations_7j = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmt = $conn->prepare("SELECT COUNT(*) FROM reserver WHERE DATE(Date_Reservation_Reserver) = ?");
        $stmt->execute([$date]);
        $count = (int)$stmt->fetchColumn();
        $reservations_7j[] = $count;
    }
    $reservations_7j_json = json_encode($reservations_7j);
    
    // Graphique: État des réservations
    $stmt_etat = $conn->query("
        SELECT Etat_Reserver, COUNT(*) as total FROM reserver GROUP BY Etat_Reserver
    ");
    $etat_data = $stmt_etat->fetchAll(PDO::FETCH_ASSOC);
    
    $etat_labels = [];
    $etat_values = [];
    foreach ($etat_data as $row) {
        $etat_labels[] = $row['Etat_Reserver'];
        $etat_values[] = (int)$row['total'];
    }
    $etat_labels_json = json_encode($etat_labels);
    $etat_values_json = json_encode($etat_values);
    
    // Graphique: Revenus par chambre type
    $stmt_revenus_chambre = $conn->query("
        SELECT ch.Type_Chambre, COALESCE(SUM(r.Prix_Total), 0) as total
        FROM chambre ch
        LEFT JOIN reserver r ON ch.ID_Chambre = r.ID_Chambre
        GROUP BY ch.Type_Chambre
    ");
    $revenus_chambre = $stmt_revenus_chambre->fetchAll(PDO::FETCH_ASSOC);
    
    $chambre_labels = [];
    $chambre_values = [];
    foreach ($revenus_chambre as $row) {
        $chambre_labels[] = $row['Type_Chambre'];
        $chambre_values[] = (float)$row['total'];
    }
    $chambre_labels_json = json_encode($chambre_labels);
    $chambre_values_json = json_encode($chambre_values);
    
    // Dernières réservations
    $stmt_dernieres = $conn->query("
        SELECT r.ID_Reserver, c.Prenom_Client, c.Nom_Client, ch.Type_Chambre, 
               r.Date_Arrive_Reserver, r.Etat_Reserver, r.Prix_Total
        FROM reserver r
        JOIN client c ON r.ID_Client = c.ID_Client
        JOIN chambre ch ON r.ID_Chambre = ch.ID_Chambre
        ORDER BY r.Date_Reservation_Reserver DESC
        LIMIT 5
    ");
    $dernieres_reservations = $stmt_dernieres->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die("Erreur base de données: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tableau de bord - Royal Plaze Hotel</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        body {
            background: #0a0a0a;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #d4a72c;
        }

        header h1 {
            font-size: 2.5em;
            color: #d4a72c;
            margin: 0;
        }

        .admin-info {
            text-align: right;
        }

        .admin-info p {
            margin: 5px 0;
            color: #999;
        }

        .admin-info a {
            color: #d4a72c;
            text-decoration: none;
            margin-left: 15px;
        }

        .admin-info a:hover {
            text-decoration: underline;
        }

        /* Statistiques */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #222 0%, #1a1a1a 100%);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #333;
            border-left: 4px solid #d4a72c;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(212, 167, 44, 0.2);
        }

        .stat-card h3 {
            color: #999;
            font-size: 0.9em;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .value {
            font-size: 2.5em;
            color: #d4a72c;
            font-weight: bold;
            margin: 10px 0;
        }

        .stat-card .subtitle {
            color: #666;
            font-size: 0.85em;
        }

        .stat-card.accent1 { border-left-color: #00c851; }
        .stat-card.accent1 .value { color: #00c851; }

        .stat-card.accent2 { border-left-color: #ffbb33; }
        .stat-card.accent2 .value { color: #ffbb33; }

        .stat-card.accent3 { border-left-color: #ff4444; }
        .stat-card.accent3 .value { color: #ff4444; }

        /* Charts */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .chart-container {
            background: linear-gradient(135deg, #222 0%, #1a1a1a 100%);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #333;
        }

        .chart-container h3 {
            color: #d4a72c;
            margin: 0 0 20px 0;
            font-size: 1.2em;
        }

        .chart-container #chartReservations,
        .chart-container #chartEtat,
        .chart-container #chartRevenus {
            height: 300px;
        }

        /* Tableau des dernières réservations */
        .recent-section {
            background: linear-gradient(135deg, #222 0%, #1a1a1a 100%);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #333;
        }

        .recent-section h3 {
            color: #d4a72c;
            margin: 0 0 20px 0;
            font-size: 1.2em;
        }

        .recent-table {
            width: 100%;
            border-collapse: collapse;
        }

        .recent-table th {
            background: #1a1a1a;
            color: #d4a72c;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #333;
            font-weight: bold;
        }

        .recent-table td {
            padding: 12px;
            border-bottom: 1px solid #333;
            color: #ddd;
        }

        .recent-table tr:hover {
            background: rgba(212, 167, 44, 0.05);
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .status-attente {
            background: rgba(255, 187, 51, 0.2);
            color: #ffbb33;
        }

        .status-confirmee {
            background: rgba(0, 200, 81, 0.2);
            color: #00c851;
        }

        .status-completee {
            background: rgba(93, 93, 93, 0.2);
            color: #999;
        }

        /* Responsif */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-info {
                text-align: left;
                margin-top: 15px;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-card .value {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>

<div class="dashboard">
    <header>
        <h1>🏨 Tableau de bord</h1>
        <div class="admin-info">
            <p>👤 Admin: <?= htmlspecialchars($_SESSION['admin'] ?? 'Administrateur') ?></p>
            <a href="chambres/index.php">Chambres</a>
            <a href="reservations/index.php">Réservations</a>
            <a href="logout.php">🚪 Déconnexion</a>
        </div>
    </header>

    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>📊 Total Réservations</h3>
            <div class="value"><?= $totalReservations ?></div>
            <p class="subtitle"><?= $en_attente ?> en attente</p>
        </div>

        <div class="stat-card accent1">
            <h3>📅 Aujourd'hui</h3>
            <div class="value"><?= $arrivees + $departs ?></div>
            <p class="subtitle">🔵 <?= $arrivees ?> arrivées | 🔴 <?= $departs ?> départs</p>
        </div>

        <div class="stat-card accent2">
            <h3>🏘️ Occupation</h3>
            <div class="value"><?= $taux_occupation ?>%</div>
            <p class="subtitle"><?= $chambres_occupees ?>/<?= $totalChambres ?> chambres</p>
        </div>

        <div class="stat-card accent3">
            <h3>💰 Revenu du mois</h3>
            <div class="value"><?= number_format($revenu_mois, 0, ',', ' ') ?></div>
            <p class="subtitle">Total: <?= number_format($revenu_total, 0, ',', ' ') ?> DJF</p>
        </div>

        <div class="stat-card">
            <h3>🛏️ Chambres</h3>
            <div class="value"><?= $totalChambres ?></div>
            <p class="subtitle"><?= $chambres_occupees ?> occupées</p>
        </div>

        <div class="stat-card">
            <h3>👥 Clients</h3>
            <div class="value"><?= $totalClients ?></div>
            <p class="subtitle">Base de données</p>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="charts-grid">
        <div class="chart-container">
            <h3>📈 Réservations - 7 derniers jours</h3>
            <div id="chartReservations"></div>
        </div>

        <div class="chart-container">
            <h3>📊 État des réservations</h3>
            <div id="chartEtat"></div>
        </div>
    </div>

    <div class="charts-grid" style="grid-template-columns: 1fr;">
        <div class="chart-container">
            <h3>💵 Revenus par type de chambre</h3>
            <div id="chartRevenus"></div>
        </div>
    </div>

    <!-- Dernières réservations -->
    <div class="recent-section">
        <h3>🕐 Dernières réservations</h3>
        <table class="recent-table">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Client</th>
                    <th>Chambre</th>
                    <th>Arrivée</th>
                    <th>État</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dernieres_reservations as $r): ?>
                    <tr>
                        <td>#<?= (int)$r['ID_Reserver'] ?></td>
                        <td><?= htmlspecialchars($r['Prenom_Client'] . ' ' . $r['Nom_Client']) ?></td>
                        <td><?= htmlspecialchars($r['Type_Chambre']) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['Date_Arrive_Reserver'])) ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower(str_replace(' ', '', $r['Etat_Reserver'])) ?>">
                                <?= htmlspecialchars($r['Etat_Reserver']) ?>
                            </span>
                        </td>
                        <td><?= number_format((float)$r['Prix_Total'], 0, ',', ' ') ?> DJF</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Réservations 7 derniers jours - Bar chart
var optionsReservations = {
    series: [{
        name: 'Réservations',
        data: <?= $reservations_7j_json ?>
    }],
    chart: {
        type: 'bar',
        height: 300,
        toolbar: { show: false },
        foreColor: '#999'
    },
    plotOptions: {
        bar: {
            borderRadius: 6,
            columnWidth: '50%',
            colors: {
                ranges: [{
                    from: -Infinity,
                    to: 0,
                    color: '#d4a72c'
                }]
            }
        }
    },
    dataLabels: { enabled: true },
    xaxis: {
        categories: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']
    },
    colors: ['#d4a72c']
};
new ApexCharts(document.querySelector('#chartReservations'), optionsReservations).render();

// État des réservations - Donut
var optionsEtat = {
    series: <?= $etat_values_json ?>,
    chart: { type: 'donut', height: 300, foreColor: '#999' },
    labels: <?= $etat_labels_json ?>,
    colors: ['#ffbb33', '#00c851', '#ff4444', '#5d5d5d'],
    plotOptions: {
        pie: { donut: { size: '70%' } }
    },
    legend: { position: 'bottom' }
};
new ApexCharts(document.querySelector('#chartEtat'), optionsEtat).render();

// Revenus par chambre - Bar
var optionsRevenus = {
    series: [{
        name: 'Revenu (DJF)',
        data: <?= $chambre_values_json ?>
    }],
    chart: {
        type: 'bar',
        height: 300,
        toolbar: { show: false },
        foreColor: '#999'
    },
    plotOptions: {
        bar: {
            borderRadius: 6,
            columnWidth: '50%'
        }
    },
    dataLabels: { enabled: true, formatter: (val) => val.toLocaleString() },
    xaxis: {
        categories: <?= $chambre_labels_json ?>
    },
    colors: ['#00c851']
};
new ApexCharts(document.querySelector('#chartRevenus'), optionsRevenus).render();
</script>

</body>
</html>

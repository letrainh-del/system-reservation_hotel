<div class="search-bar" style="margin-bottom:18px;display:flex;align-items:center;gap:10px;">
    <input type="text" id="quickSearch" placeholder="Recherche rapide (client, chambre, réservation...)" style="flex:1;padding:8px 12px;border-radius:4px;border:1px solid #334155;background:#1e293b;color:#e2e8f0;" />
    <button id="quickSearchBtn" style="background:#22d3ee;color:#0f172a;padding:8px 18px;border:none;border-radius:4px;cursor:pointer;font-weight:600;transition:background 0.2s;">Rechercher</button>
</div>
<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

try {
    // Filtres dynamiques
    $filter_date = $_GET['filter_date'] ?? '';
    $filter_statut = $_GET['filter_statut'] ?? '';
    $filter_type = $_GET['filter_type'] ?? '';

    $where = [];
    $params = [];
    if ($filter_date) {
        $where[] = '(DATE(Date_Reservation_Reserver) = ? OR DATE(Date_Arrive_Reserver) = ? OR DATE(Date_Dapart_Reserver) = ?)';
        $params[] = $filter_date;
        $params[] = $filter_date;
        $params[] = $filter_date;
    }
    if ($filter_statut) {
        $where[] = 'Etat_Reserver = ?';
        $params[] = $filter_statut;
    }
    if ($filter_type) {
        $where[] = 'ID_Chambre IN (SELECT ID_Chambre FROM chambre WHERE Type_Chambre = ?)';
        $params[] = $filter_type;
    }
    $where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    // Comptes
    $sql_total = "SELECT COUNT(*) FROM reserver $where_sql";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->execute($params);
    $totalReservations = (int)$stmt_total->fetchColumn();

    $totalChambres = (int)$conn->query("SELECT COUNT(*) FROM chambre")->fetchColumn();
    $totalClients = (int)$conn->query("SELECT COUNT(*) FROM client")->fetchColumn();

    // Arrivées & départs du jour (filtrées)
    $today = date('Y-m-d');
    $arrivees = 0;
    $departs = 0;
    if (!$filter_date || $filter_date == $today) {
        $stmt_arrivees = $conn->prepare("SELECT COUNT(*) FROM reserver WHERE DATE(Date_Arrive_Reserver) = ?");
        $stmt_arrivees->execute([$today]);
        $arrivees = (int)$stmt_arrivees->fetchColumn();
        $stmt_departs = $conn->prepare("SELECT COUNT(*) FROM reserver WHERE DATE(Date_Dapart_Reserver) = ?");
        $stmt_departs->execute([$today]);
        $departs = (int)$stmt_departs->fetchColumn();
    }

    // Réservations en attente (filtrées)
    $sql_attente = "SELECT COUNT(*) FROM reserver $where_sql";
    $stmt_attente = $conn->prepare($sql_attente . ($where_sql ? ' AND ' : ' WHERE ') . "Etat_Reserver = ?");
    $stmt_attente->execute(array_merge($params, ['En attente']));
    $en_attente = (int)$stmt_attente->fetchColumn();

    // Revenus totaux (filtrés)
    $sql_revenu = "SELECT COALESCE(SUM(Prix_Total), 0) FROM reserver $where_sql";
    $stmt_revenu = $conn->prepare($sql_revenu);
    $stmt_revenu->execute($params);
    $revenu_total = (float)$stmt_revenu->fetchColumn();

    // Revenus du mois (filtrés)
    $sql_revenu_mois = "SELECT COALESCE(SUM(Prix_Total), 0) FROM reserver $where_sql";
    $stmt_revenu_mois = $conn->prepare($sql_revenu_mois . ($where_sql ? ' AND ' : ' WHERE ') . "YEAR(Date_Reservation_Reserver) = YEAR(NOW()) AND MONTH(Date_Reservation_Reserver) = MONTH(NOW())");
    $stmt_revenu_mois->execute($params);
    $revenu_mois = (float)$stmt_revenu_mois->fetchColumn();

    // Taux d'occupation (non filtré)
    $stmt_occupation = $conn->query("SELECT COUNT(ID_Chambre) FROM chambre WHERE Disponible = 0");
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
    
    // Graphique: État des chambres (tous statuts)
    $statuts = ['Occupée', 'Disponible', 'Départ', 'En maintenance', 'En attente'];
    $etat_labels = $statuts;
    $etat_values = [];
    foreach ($statuts as $statut) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM reserver WHERE Etat_Reserver = ?");
        $stmt->execute([$statut]);
        $etat_values[] = (int)$stmt->fetchColumn();
    }
    $etat_labels_json = json_encode($etat_labels);
    $etat_values_json = json_encode($etat_values);
    
    // Dernières réservations avec heure
    $stmt_dernieres = $conn->query("
        SELECT r.ID_Reserver, c.Prenom_Client, c.Nom_Client, ch.Numero_Chambre, 
               r.Date_Reservation_Reserver, r.Etat_Reserver, r.Prix_Total
        FROM reserver r
        JOIN client c ON r.ID_Client = c.ID_Client
        JOIN chambre ch ON r.ID_Chambre = ch.ID_Chambre
        ORDER BY r.Date_Reservation_Reserver DESC
        LIMIT 6
    ");
    $dernieres_reservations = $stmt_dernieres->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die("Erreur: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tableau de bord - Royal Plaze</title>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0f172a;
            color: #e2e8f0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            padding: 30px 20px;
            border-right: 1px solid #1e293b;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            padding: 12px 16px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0.05) 100%);
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }

        .sidebar-logo span {
            color: #3b82f6;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 1px;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: 8px;
        }

        .sidebar-nav a {
            display: block;
            padding: 12px 16px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
            font-size: 13px;
            border-left: 3px solid transparent;
        }

        .sidebar-nav a:hover {
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
            border-left-color: #3b82f6;
        }

        .sidebar-nav a.active {
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.15);
            border-left-color: #3b82f6;
        }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 40px;
            background: #0f172a;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #1e293b;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 300;
            letter-spacing: -1px;
            color: #e2e8f0;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn-new {
            background: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            font-size: 13px;
            transition: background 0.3s;
        }

        .btn-new:hover {
            background: #2563eb;
        }

        /* STATS CARDS */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 30px;
            border-radius: 8px;
            border: 1px solid #334155;
            transition: all 0.3s;
            box-shadow: inset 0 1px 0 rgba(59, 130, 246, 0.1);
        }

        .stat-card:hover {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            box-shadow: inset 0 1px 0 rgba(59, 130, 246, 0.2), 0 0 15px rgba(59, 130, 246, 0.15);
        }

        .stat-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 48px;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 12px;
            letter-spacing: -2px;
        }

        .stat-change {
            font-size: 13px;
            color: #10b981;
            font-weight: 500;
        }

        .stat-change.red {
            color: #ef4444;
        }

        /* CHARTS SECTION */
        .charts-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }

        .chart-card {
            background: #1e293b;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #334155;
        }

        .chart-title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #e2e8f0;
        }

        #chartReservations,
        #chartEtat {
            height: 280px;
        }

        /* ARRIVALS SECTION */
        .arrivals-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }

        .arrivals-card {
            background: #1e293b;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #334155;
        }

        .arrivals-title {
            font-size: 14px;
            color: #3b82f6;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .arrival-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #334155;
            font-size: 13px;
        }

        .arrival-item:last-child {
            border-bottom: none;
        }

        .arrival-ch {
            color: #3b82f6;
            font-weight: 600;
        }

        .arrival-name {
            color: #e2e8f0;
        }

        .arrival-time {
            color: #64748b;
            font-size: 12px;
        }

        .voir-tout {
            color: #3b82f6;
            text-decoration: none;
            font-size: 12px;
            float: right;
            margin-top: 15px;
        }

        .voir-tout:hover {
            text-decoration: underline;
        }

        /* CALENDAR */
        .calendar-card {
            background: #1e293b;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #334155;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-month {
            font-size: 16px;
            font-weight: 600;
            color: #3b82f6;
        }

        .calendar-nav {
            display: flex;
            gap: 10px;
        }

        .calendar-nav button {
            background: transparent;
            border: 1px solid #334155;
            color: #e2e8f0;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .calendar-nav button:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
        }

        .calendar-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .calendar-tab {
            padding: 6px 12px;
            background: transparent;
            border: 1px solid #334155;
            color: #64748b;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .calendar-tab.active {
            background: #3b82f6;
            color: #fff;
            border-color: #3b82f6;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            font-size: 12px;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            background: #334155;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.3s;
        }

        .calendar-day:hover {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid #3b82f6;
        }

        .calendar-day.today {
            background: #3b82f6;
            color: #fff;
            font-weight: 600;
        }

        /* ROOMS STATUS */
        .rooms-status {
            background: #1e293b;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #334155;
            text-align: center;
        }

        .rooms-count {
            font-size: 24px;
            font-weight: 600;
            color: #3b82f6;
            margin: 20px 0;
        }

        .rooms-label {
            font-size: 14px;
            color: #64748b;
        }

        .room-status-legend {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            font-size: 12px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }

        .dot-occupied { background: #10b981; }
        .dot-available { background: #3b82f6; }
        .dot-departure { background: #64748b; }
        .dot-maintenance { background: #f59e0b; }
        .dot-pending { background: #fbbf24; }

        @media (max-width: 1400px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            .charts-wrapper {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                left: -260px;
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span>RP</span>
            <div>
                <div style="color: #e2e8f0; font-size: 11px; line-height: 1;">Royal Plaze</div>
                <div style="color: #64748b; font-size: 10px;">Administrateur</div>
            </div>
        </div>

        <ul class="sidebar-nav">
            <li><a href="index.php" class="active">Tableau de bord</a></li>
            <li><a href="reservations/index.php">Réservations</a></li>
            <li><a href="chambres/index.php">Chambres</a></li>
            <li><a href="clients/index.php">Clients</a></li>
            <li><a href="calendar.php">Calendrier</a></li>
            <li style="margin-top: 20px;"><a href="logout.php" style="color: #ef4444;">Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="header">
            <h1>Tableau de bord</h1>
            <div class="header-right">
                <a href="reservations/ajouter.php" class="btn-new">+ Nouvelle réservation</a>
            </div>
        </div>

        <!-- STATS CARDS - TOP ROW -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-label">Réservations totales</div>
                <div class="stat-value"><?= number_format($totalReservations, 0, ',', ' ') ?></div>
                <div class="stat-change">+12 % s/s</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Arrivées aujourd'hui</div>
                <div class="stat-value"><?= $arrivees ?></div>
                <div class="stat-change">+4 %</div>
            </div>
        </div>

        <!-- STATS CARDS - SECOND ROW -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-label">Départs aujourd'hui</div>
                <div class="stat-value"><?= $departs ?></div>
                <div class="stat-change red">-2 %</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Taux occupation</div>
                <div class="stat-value"><?= $taux_occupation ?>%</div>
                <div class="stat-change"><?= $chambres_occupees ?>/<?= $totalChambres ?></div>
            </div>
        </div>

        <!-- CHARTS -->
        <div class="charts-wrapper">
            <div class="chart-card">
                <div class="chart-title">Réservations — 7 derniers jours</div>
                <div id="chartReservations"></div>
            </div>
            <div class="chart-card">
                <div class="chart-title">État des chambres</div>
                <div id="chartEtat"></div>
            </div>
        </div>

        <!-- ARRIVALS & CALENDAR -->
        <div class="arrivals-section">
            <div class="arrivals-card">
                <div class="arrivals-title">Arrivées récentes</div>
                <div id="arrivalsList">
                <?php foreach ($dernieres_reservations as $r): 
                    $time_diff = time() - strtotime($r['Date_Reservation_Reserver']);
                    if ($time_diff < 60) $time_text = "À l'instant";
                    elseif ($time_diff < 3600) $time_text = floor($time_diff/60) . " minutes";
                    elseif ($time_diff < 86400) $time_text = floor($time_diff/3600) . " heures";
                    else $time_text = floor($time_diff/86400) . " jours";
                ?>
                    <div class="arrival-item">
                        <div>
                            <div class="arrival-ch">Ch. N° <?= (int)$r['Numero_Chambre'] ?></div>
                            <div class="arrival-name"><?= htmlspecialchars($r['Prenom_Client'] . ' ' . $r['Nom_Client']) ?></div>
                        </div>
                        <div class="arrival-time">Il y a <?= $time_text ?></div>
                    </div>
                <?php endforeach; ?>
                </div>
                <a href="reservations/index.php" class="voir-tout">Voir tout</a>
            </div>

            <div class="calendar-card">
                <div class="calendar-header">
                    <div>
                        <div class="calendar-month"><?php
$mois_fr = ['', 'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
echo $mois_fr[(int)date('m')] . ' ' . date('Y');
?></div>
                    </div>
                    <div class="calendar-nav">
                        <button>‹</button>
                        <button>›</button>
                    </div>
                </div>
                
                <div class="calendar-tabs">
                    <button class="calendar-tab active">Month</button>
                    <button class="calendar-tab">Week</button>
                    <button class="calendar-tab">List</button>
                </div>

                <div class="calendar-grid">
                    <?php
                    $month = date('m');
                    $year = date('Y');
                    $first_day = mktime(0, 0, 0, $month, 1, $year);
                    $days_in_month = date('t', $first_day);
                    $start_day = date('w', $first_day);
                    
                    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    
                    // Days of week header
                    foreach ($days as $day) {
                        echo '<div style="color: #808080; font-weight: 600; margin-bottom: 8px;">' . $day . '</div>';
                    }
                    
                    // Empty cells before month starts
                    for ($i = 0; $i < $start_day; $i++) {
                        echo '<div style="background: transparent;"></div>';
                    }
                    
                    // Days of month
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $class = (date('d') == $day && date('m') == $month) ? 'calendar-day today' : 'calendar-day';
                        echo '<div class="' . $class . '">' . $day . '</div>';
                    }
                    ?>
                </div>

                <div style="text-align: center; margin-top: 15px;">
                    <button style="padding: 6px 12px; background: #2a2a2a; color: #e0e0e0; border: 1px solid #3a3a3a; border-radius: 4px; cursor: pointer; font-size: 12px;">Today</button>
                </div>
            </div>
        </div>

        <!-- ROOMS STATUS -->
        <div class="rooms-status">
            <div class="rooms-label">État des chambres</div>
            <div class="rooms-count">200</div>
            <div class="room-status-legend">
                <div class="legend-item">
                    <div class="legend-dot dot-occupied"></div>
                    <span>Occupée</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot dot-available"></div>
                    <span>Disponible</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot dot-departure"></div>
                    <span>Départ</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot dot-maintenance"></div>
                    <span>En maintenance</span>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Recherche rapide au clic sur le bouton
document.getElementById('quickSearchBtn').onclick = function() {
    document.getElementById('quickSearch').dispatchEvent(new Event('input'));
    document.getElementById('quickSearch').focus();
};
    /* Palette moderne pour dashboard */
    body {
        background: #18181b;
        color: #f1f5f9;
    }
    .main-content {
        background: #18181b;
    }
    .stat-card {
        background: linear-gradient(135deg, #23272f 0%, #18181b 100%);
        border: 1px solid #262626;
        box-shadow: 0 2px 8px 0 rgba(34,211,238,0.04);
    }
    .stat-value {
        color: #22d3ee;
    }
    .btn-new, #quickSearchBtn {
        background: #22d3ee;
        color: #18181b;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        transition: background 0.2s;
    }
    .btn-new:hover, #quickSearchBtn:hover {
        background: #0ea5e9;
        color: #fff;
    }
    .sidebar {
        background: linear-gradient(180deg, #23272f 0%, #18181b 100%);
        border-right: 1px solid #262626;
    }
    .sidebar-logo span {
        color: #22d3ee;
    }
    .sidebar-nav a.active, .sidebar-nav a:hover {
        background: rgba(34,211,238,0.12);
        color: #22d3ee;
        border-left-color: #22d3ee;
    }
    .stat-change { color: #10b981; }
    .stat-change.red { color: #ef4444; }
    .legend-dot.dot-occupied { background: #10b981; }
    .legend-dot.dot-available { background: #22d3ee; }
    .legend-dot.dot-departure { background: #fbbf24; }
    .legend-dot.dot-maintenance { background: #f59e0b; }
    .legend-dot.dot-pending { background: #ef4444; }
// Recherche rapide sur les arrivées récentes (et peut être étendue à d'autres listes)
document.getElementById('quickSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#arrivalsList .arrival-item').forEach(function(item) {
        const txt = item.textContent.toLowerCase();
        item.style.display = txt.includes(q) ? '' : 'none';
    });
});
// Réservations 7 derniers jours
var optionsReservations = {
    series: [{
        name: 'Réservations',
        data: <?= $reservations_7j_json ?>
    }],
    chart: {
        type: 'bar',
        height: 280,
        toolbar: { show: false },
        background: 'transparent',
        fontFamily: 'inherit'
    },
    plotOptions: {
        bar: {
            borderRadius: 4,
            columnWidth: '60%',
            dataLabels: { position: 'top' }
        }
    },
    dataLabels: {
        enabled: true,
        offsetY: -20,
        style: { fontSize: '12px', colors: ['#3b82f6'] }
    },
    xaxis: {
        categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { style: { colors: '#64748b', fontSize: '12px' } }
    },
    yaxis: {
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { style: { colors: '#64748b', fontSize: '12px' } }
    },
    grid: {
        show: true,
        borderColor: '#334155',
        strokeDashArray: 0,
        xaxis: { lines: { show: false } }
    },
    colors: ['#3b82f6']
};
new ApexCharts(document.querySelector('#chartReservations'), optionsReservations).render();

// État des chambres
var optionsEtat = {
    series: <?= $etat_values_json ?>,
    chart: {
        type: 'donut',
        height: 280,
        background: 'transparent',
        fontFamily: 'inherit'
    },
    labels: <?= $etat_labels_json ?>,
    colors: ['#10b981', '#3b82f6', '#64748b', '#f59e0b', '#fbbf24'], // Occupée, Disponible, Départ, Maintenance, En attente
    legend: {
        position: 'bottom',
        labels: { colors: '#64748b', fontSize: '12px' }
    },
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                labels: {
                    show: true,
                    name: { show: true, color: '#64748b' },
                    value: {
                        show: true,
                        fontSize: '24px',
                        fontWeight: 700,
                        color: '#3b82f6'
                    }
                }
            }
        }
    },
    dataLabels: { enabled: false }
};
new ApexCharts(document.querySelector('#chartEtat'), optionsEtat).render();
</script>

</body>
</html>
<div class="filter-bar" style="margin-bottom:30px;display:flex;gap:20px;align-items:center;">
    <form id="filterForm" style="display:flex;gap:10px;align-items:center;width:100%;">
        <label>Date : <input type="date" name="filter_date" id="filter_date" value="<?= htmlspecialchars($_GET['filter_date'] ?? '') ?>" /></label>
        <label>Statut :
            <select name="filter_statut" id="filter_statut">
                <option value="">Tous</option>
                <option value="Confirmée">Confirmée</option>
                <option value="En attente">En attente</option>
                <option value="Annulée">Annulée</option>
                <option value="Occupée">Occupée</option>
                <option value="Départ">Départ</option>
                <option value="En maintenance">En maintenance</option>
            </select>
        </label>
        <label>Type de chambre :
            <select name="filter_type" id="filter_type">
                <option value="">Tous</option>
                <?php $types = $conn->query("SELECT DISTINCT Type_Chambre FROM chambre")->fetchAll(PDO::FETCH_COLUMN);
                foreach($types as $type) echo '<option value="'.htmlspecialchars($type).'">'.htmlspecialchars($type).'</option>'; ?>
            </select>
        </label>
        <button type="submit" style="background:#3b82f6;color:white;padding:8px 16px;border:none;border-radius:4px;cursor:pointer;">Filtrer</button>
    </form>
</div>
<script>
document.getElementById('filterForm').onsubmit = function(e) {
    e.preventDefault();
    const params = new URLSearchParams(new FormData(this)).toString();
    window.location = 'index.php?' + params;
};
</script>

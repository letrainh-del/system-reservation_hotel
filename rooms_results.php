<?php
session_start();
require_once "config/db.php";

$date_arrive = $_GET['checkin'] ?? null;
$date_depart = $_GET['checkout'] ?? null;
$capacite = $_GET['guests'] ?? 1;

if (!$date_arrive || !$date_depart) {
    header("Location: index.php");
    exit;
}

// Convertir les dates - support d/m/Y et Y-m-d
if (strpos($date_arrive, '/') !== false) {
    $date_arrive_obj = DateTime::createFromFormat('d/m/Y', $date_arrive);
    $date_arrive_display = $date_arrive;
} else {
    $date_arrive_obj = DateTime::createFromFormat('Y-m-d', $date_arrive);
    $date_arrive_display = $date_arrive_obj->format('d/m/Y');
}

if (strpos($date_depart, '/') !== false) {
    $date_depart_obj = DateTime::createFromFormat('d/m/Y', $date_depart);
    $date_depart_display = $date_depart;
} else {
    $date_depart_obj = DateTime::createFromFormat('Y-m-d', $date_depart);
    $date_depart_display = $date_depart_obj->format('d/m/Y');
}

$nuits = $date_depart_obj->diff($date_arrive_obj)->days;
$date_arrive_sql = $date_arrive_obj->format('Y-m-d');
$date_depart_sql = $date_depart_obj->format('Y-m-d');

// Récupérer les chambres disponibles
$sql = "
    SELECT DISTINCT c.ID_Chambre, c.Numero_Chambre, c.Type_Chambre, c.Prix, 
                    c.Capacite, c.Description, c.Image, c.Disponible
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
    ':date_arrive' => $date_arrive_sql,
    ':date_depart' => $date_depart_sql
]);

$chambres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chambres Disponibles - Royal Plaze</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #0b0b0b;
            color: #fff;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: #111;
            border-bottom: 1px solid #d4a72c;
            margin-bottom: 20px;
        }
        .navbar .logo h2 {
            color: #d4a72c;
            margin: 0;
        }
        .navbar ul {
            display: flex;
            list-style: none;
            gap: 30px;
            margin: 0;
            padding: 0;
        }
        .navbar ul li a {
            color: #d4a72c;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        .navbar ul li a:hover {
            opacity: 0.8;
        }
        .navbar .navright a {
            color: #000;
            background: #d4a72c;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
        }
        .search-results-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 20px;
        }
        .search-criteria {
            background: #111;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #d4a72c;
            margin-bottom: 30px;
            color: #fff;
        }
        .search-criteria p {
            margin: 5px 0;
        }
        .search-criteria strong {
            color: #d4a72c;
        }
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        .room-card {
            background: #111;
            border: 1px solid #d4a72c;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 0 15px rgba(212,167,44,0.2);
        }
        .room-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 0 25px rgba(212,167,44,0.4);
        }
        .room-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #d4a72c, #8b6914);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            font-weight: bold;
            position: relative;
            overflow: hidden;
        }
        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .room-image.type-f3 {
            background: linear-gradient(135deg, #9b8b3f, #6b5b1f);
        }
        .room-image.type-suite {
            background: linear-gradient(135deg, #d4a72c, #c4971c);
        }
        .room-image.type-studio {
            background: linear-gradient(135deg, #a89a3f, #7a6b2f);
        }
        .room-image.type-simple {
            background: linear-gradient(135deg, #8b7b1f, #5b4b0f);
        }
        .room-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 100%;
            font-size: 12px;
        }
        .room-info {
            padding: 20px;
            color: #fff;
        }
        .room-info h3 {
            color: #d4a72c;
            margin: 0 0 8px 0;
        }
        .room-type {
            font-size: 13px;
            color: #aaa;
            margin-bottom: 10px;
        }
        .room-capacity {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #aaa;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .room-description {
            font-size: 12px;
            color: #999;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        .price-section {
            border-top: 1px solid #333;
            padding-top: 15px;
            margin-top: 15px;
        }
        .price-per-night {
            font-size: 12px;
            color: #aaa;
            margin-bottom: 8px;
        }
        .total-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .total-price .amount {
            font-size: 20px;
            font-weight: bold;
            color: #d4a72c;
        }
        .book-btn {
            width: 100%;
            padding: 12px;
            background: #d4a72c;
            color: #000;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        .book-btn:hover {
            opacity: 0.85;
        }
        .no-results {
            text-align: center;
            color: #aaa;
            padding: 40px;
            background: #111;
            border-radius: 12px;
            border: 1px solid #333;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: #333;
            color: #d4a72c;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .back-btn:hover {
            background: #444;
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo">
        <h2>Royal Plaze</h2>
    </div>
    <ul>
        <li><a href="index.php">Accueil</a></li>
        <li><a href="#">À propos</a></li>
        <li><a href="rooms_results.php">Chambres</a></li>
        <li><a href="contact.html">Contact</a></li>
    </ul>
    <div class="navright">
        <?php if(isset($_SESSION['client_id'])): ?>
            <a href="logout.php">Déconnexion</a>
        <?php else: ?>
            <a href="login.html">Connexion</a>
        <?php endif; ?>
    </div>
</nav>

<div class="search-results-container">
    <a href="index.php" class="back-btn">⬅ Retour</a>
    
    <div class="search-criteria">
        <p><strong>📅 Dates:</strong> <?php echo $date_arrive_display; ?> → <?php echo $date_depart_display; ?></p>
        <p><strong>👥 Personnes:</strong> <?php echo $capacite; ?></p>
        <p><strong>🌙 Nuits:</strong> <?php echo $nuits; ?></p>
    </div>

    <?php if (count($chambres) > 0): ?>
        <div class="rooms-grid">
            <?php foreach ($chambres as $chambre): 
                $prix_total = (float)$chambre['Prix'] * $nuits;
                // Déterminer la classe CSS basée sur le type
                $type_class = 'type-simple';
                if (stripos($chambre['Type_Chambre'], 'suite') !== false) {
                    $type_class = 'type-suite';
                } elseif (stripos($chambre['Type_Chambre'], 'f3') !== false) {
                    $type_class = 'type-f3';
                } elseif (stripos($chambre['Type_Chambre'], 'studio') !== false) {
                    $type_class = 'type-studio';
                }
            ?>
                <div class="room-card">
                    <div class="room-image <?php echo $type_class; ?>">
                        <?php if ($chambre['Image']): ?>
                            <img src="<?php echo htmlspecialchars($chambre['Image']); ?>" alt="Chambre">
                        <?php else: ?>
                            <div class="room-placeholder">
                                <span style="font-size: 40px;">🏨</span>
                                <span>Chambre <?php echo htmlspecialchars($chambre['Numero_Chambre']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="room-info">
                        <h3><?php echo htmlspecialchars($chambre['Type_Chambre']); ?></h3>
                        <div class="room-type">N° <?php echo htmlspecialchars($chambre['Numero_Chambre']); ?></div>
                        
                        <div class="room-capacity">
                            👥 Capacité: <?php echo $chambre['Capacite']; ?> personne(s)
                        </div>

                        <?php if ($chambre['Description']): ?>
                            <div class="room-description">
                                <?php echo htmlspecialchars($chambre['Description']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="price-section">
                            <div class="price-per-night">
                                💰 <?php echo number_format($chambre['Prix'], 2, ',', ' '); ?> FDJ/nuit
                            </div>
                            <div class="total-price">
                                <span>Total pour <?php echo $nuits; ?> nuit(s):</span>
                                <span class="amount"><?php echo number_format($prix_total, 2, ',', ' '); ?> FDJ</span>
                            </div>
                            <button class="book-btn" onclick="reserver(<?php echo $chambre['ID_Chambre']; ?>, '<?php echo $date_arrive_sql; ?>', '<?php echo $date_depart_sql; ?>', <?php echo $prix_total; ?>)">
                                Réserver cette chambre
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <p>❌ Aucune chambre disponible pour cette période et cette capacité.</p>
            <p>Veuillez modifier vos critères de recherche.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function reserver(idChambre, dateArrive, dateDepart, prixTotal) {
    <?php if (!isset($_SESSION['client_id'])): ?>
        alert('Veuillez vous connecter pour réserver');
        window.location.href = 'login.html';
        return;
    <?php endif; ?>

    const confirmText = `Confirmer la réservation?\n\nChambre: ${idChambre}\nPériode: ${dateArrive} → ${dateDepart}\nTotal: ${prixTotal.toFixed(2)} FDJ`;
    
    if (confirm(confirmText)) {
        fetch('api/create_booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_chambre: idChambre,
                date_arrive: dateArrive,
                date_depart: dateDepart,
                mode_paiement: 'Espèces'
            })
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    alert('✅ Réservation confirmée!\n\nNuméro de réservation: ' + data.reservation_id + '\n\nTotal: ' + prixTotal.toFixed(2) + ' FDJ');
                    window.location.href = 'index.php';
                } else {
                    alert('❌ Erreur: ' + data.message);
                }
            } catch (e) {
                alert('❌ Réponse inattendue du serveur (JSON attendu):\n' + text);
            }
        })
        .catch(error => {
            alert('❌ Erreur réseau: ' + error.message);
        });
    }
}
</script>
</body>
</html>

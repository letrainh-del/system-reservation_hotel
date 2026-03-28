<?php
// Script pour assigner les images aux chambres
require_once "../config/db.php";

// Mapping des chambres avec leurs images
$images = [
    1 => "assets/images/room1.jpg",      // ID_Chambre 1
    2 => "assets/images/room2.jpg",      // ID_Chambre 2
];

// Ou si tu as les infos de la table, on peut le faire dynamiquement
// Pour l'instant, voici le mapping basé sur les types:

$stmt = $conn->query("SELECT ID_Chambre, Type_Chambre FROM chambre");
$chambres = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($chambres as $chambre) {
    $id = $chambre['ID_Chambre'];
    $type = $chambre['Type_Chambre'];
    
    // Attribution des images basée sur le type
    if (stripos($type, 'suite') !== false) {
        $image = "assets/images/room2.jpg";
    } elseif (stripos($type, 'f3') !== false) {
        $image = "assets/images/room1.jpg";
    } elseif (stripos($type, 'studio') !== false) {
        $image = "assets/images/room3.jpg";
    } else {
        $image = "assets/images/room4.jpg";
    }
    
    // Mettre à jour la BD
    $update = $conn->prepare("UPDATE chambre SET Image = ? WHERE ID_Chambre = ?");
    $update->execute([$image, $id]);
    echo "✅ Chambre $id ($type) → $image\n";
}

echo "\n<a href='../index.php'>Retour</a>";
?>

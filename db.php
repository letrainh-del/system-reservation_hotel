<?php
try {
    $conn = new PDO("mysql:host=localhost;dbname=hotel_reservation;charset=utf8","root","");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e){
    die("Erreur connexion : " . $e->getMessage());
}
?>

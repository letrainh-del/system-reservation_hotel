<?php
session_start();
include "db.php";

$email = $_POST['email'];
$password = $_POST['password'];

// 1️⃣ Vérifier client
$stmt = $conn->prepare("SELECT * FROM Client WHERE Email_Client = ?");
$stmt->execute([$email]);
$client = $stmt->fetch();

if($client && password_verify($password, $client['MotDePasse_Client'])){
    $_SESSION['client_id'] = $client['ID_Client'];
    header("Location: index.php");
    exit;
}

// 2️⃣ Vérifier admin/employé
$stmt = $conn->prepare("SELECT * FROM Employe WHERE Email_Employe = ?");
$stmt->execute([$email]);
$emp = $stmt->fetch();

if($emp && password_verify($password, $emp['MotDePasse_Employe'])){
    $_SESSION['admin_id'] = $emp['ID_Entite'];
    header("Location: admin/dashboard.php");
    exit;
}

header("Location: login.html?error=1");
exit;
?>

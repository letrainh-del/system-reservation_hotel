<?php
session_start();
include "../config/db.php";

if(isset($_POST['login'])){

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $poste = trim($_POST['poste']);
    $email = trim($_POST['email']);

    $sql = "SELECT * FROM Employe 
            WHERE Nom_Employe = :nom 
            AND Prenom_Employe = :prenom
            AND Poste_Employe = :poste
            AND Email_Employe = :email
            LIMIT 1";

    $query = $conn->prepare($sql);
    $query->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':poste' => $poste,
        ':email' => $email
    ]);

    $data = $query->fetch(PDO::FETCH_ASSOC);

    if($data){
        $_SESSION['admin'] = $data['ID_Entite'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Informations incorrectes.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion Admin</title>
<link rel="stylesheet" href="css/admin-login.css">

</head>
<body>

<div class="login-box">
    <h2>Admin Login</h2>

    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <input type="text" name="poste" placeholder="Poste" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password1" placeholder="Mot de passe" required>

        <button name="login">Connexion</button>
    </form>
</div>


</body>
</html>

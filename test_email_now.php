<?php
/**
 * Test Email - Vérifier que Gmail fonctionne avec l'App Password
 */

require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Email - Royal Plaze</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        h1 { 
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        button:active {
            transform: translateY(0);
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            margin-top: -20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #667eea;
        }
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Test Email</h1>
        <p class="subtitle">Envoyer un email de test via Gmail</p>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $nom = trim($_POST['nom'] ?? 'Test');
            $prenom = trim($_POST['prenom'] ?? 'User');
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo '<div class="alert alert-danger">❌ Email invalide</div>';
            } else {
                echo '<div class="alert alert-info">⏳ Envoi en cours...</div>';
                
                try {
                    $mail = new PHPMailer(true);
                    
                    // Configuration SMTP
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'abdourahmanibrahim176@gmail.com';
                    $mail->Password = 'jvpqjtyovkzwbnwg';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->SMTPDebug = 0;
                    
                    // Email
                    $mail->setFrom('abdourahmanibrahim176@gmail.com', 'Royal Plaze Hotel');
                    $mail->addAddress($email, "$prenom $nom");
                    $mail->isHTML(true);
                    $mail->Subject = '🎉 Test Email - Royal Plaze Hotel';
                    $mail->Body = "
                    <html>
                    <head><meta charset='UTF-8'></head>
                    <body style='font-family: Arial, sans-serif; color: #333;'>
                        <div style='max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #667eea;'>Bonjour $prenom $nom!</h2>
                            <p>Cet email de test confirme que <strong>le système d'email est opérationnel</strong>! ✅</p>
                            <p style='background: #f0f0f0; padding: 15px; border-left: 4px solid #667eea; margin: 20px 0;'>
                                <strong>Détails:</strong><br>
                                Email reçu à: " . date('Y-m-d H:i:s') . "<br>
                                Adresse: $email
                            </p>
                            <p>Tu peux maintenant faire une réservation et recevoir les confirmations automatiquement!</p>
                            <p style='color: #999; font-size: 12px; margin-top: 30px;'>
                                Royal Plaze Hotel 🏨
                            </p>
                        </div>
                    </body>
                    </html>";
                    
                    if ($mail->send()) {
                        echo '<div class="alert alert-success">✅ Email envoyé avec succès! Vérifie ta boîte mail.</div>';
                    }
                    
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">❌ Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" value="Test" required>
            </div>
            
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" value="User" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email de destination</label>
                <input type="email" id="email" name="email" placeholder="ton@email.com" required>
            </div>
            
            <button type="submit">Envoyer un email de test 📨</button>
        </form>
    </div>
</body>
</html>

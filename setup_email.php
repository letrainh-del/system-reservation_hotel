<?php
/**
 * Setup Email Guide - Instructions pour configurer Gmail avec App Password
 */
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Gmail - App Password</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-width: 800px;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
            margin: 0 0 30px 0;
        }
        
        h2 {
            color: #667eea;
            font-size: 1.2em;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        .step {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        
        .step-number {
            display: inline-block;
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            line-height: 30px;
            text-align: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .link-button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin: 10px 10px 10px 0;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }
        
        .link-button:hover {
            background: #764ba2;
        }
        
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            color: #333;
        }
        
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            overflow-x: auto;
            margin: 10px 0;
        }
        
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            color: #155724;
        }
        
        .form-group {
            margin: 20px 0;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box;
        }
        
        button {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #218838;
        }
        
        .result {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            color: #004085;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ Configuration Gmail - App Password</h1>
        
        <div class="warning">
            ⚠️ <strong>IMPORTANT:</strong> Gmail a refusé la connexion avec le mot de passe du compte.
            <br>Tu dois utiliser un <strong>App Password</strong> spécifique.
        </div>
        
        <h2>🔧 Comment obtenir ton App Password:</h2>
        
        <div class="step">
            <span class="step-number">1</span>
            <strong>Accéder à Google Account</strong>
            <br>
            <a href="https://myaccount.google.com" target="_blank" class="link-button">
                Ouvrir Google Account (myaccount.google.com)
            </a>
        </div>
        
        <div class="step">
            <span class="step-number">2</span>
            <strong>Aller à Security</strong>
            <br>
            Clique sur le menu "Security" dans le sidebar gauche
        </div>
        
        <div class="step">
            <span class="step-number">3</span>
            <strong>Vérifier 2-Step Verification</strong>
            <br>
            Assure-toi que "2-Step Verification" est <strong>ACTIVÉ</strong>
            <br>
            (Les App Passwords ne sont disponibles que si 2FA est actif)
            <br>
            <a href="https://support.google.com/accounts/answer/185839" target="_blank" class="link-button">
                Guide 2FA de Google
            </a>
        </div>
        
        <div class="step">
            <span class="step-number">4</span>
            <strong>Générer App Password</strong>
            <br>
            Clique sur "App passwords"
            <br>
            Sélectionne:
            <ul>
                <li>App: <strong>Mail</strong></li>
                <li>Device: <strong>Windows Computer</strong></li>
            </ul>
            Google te donnera un password de 16 caractères (exemple: <code>abcd efgh ijkl mnop</code>)
        </div>
        
        <div class="success">
            ✅ Copie ce password et colle-le ci-dessous pour tester
        </div>
        
        <h2>🧪 Tester avec le nouveau password:</h2>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="app_password">App Password (16 caractères avec espaces):</label>
                <input 
                    type="text" 
                    id="app_password" 
                    name="app_password" 
                    placeholder="xxxx xxxx xxxx xxxx"
                    autocomplete="off"
                >
            </div>
            
            <button type="submit">🚀 Tester l'envoi d'email</button>
        </form>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['app_password'])) {
            $appPassword = $_POST['app_password'];
            
            // Nettoyer les espaces
            $cleanPassword = str_replace(' ', '', $appPassword);
            
            if (strlen($cleanPassword) !== 16) {
                echo '<div class="result" style="color: #dc3545; border-left-color: #dc3545; background: #f8d7da;">
                    ❌ Le password doit faire 16 caractères (sans espaces)
                </div>';
            } else {
                echo '<div class="result">
                    <strong>Testing...</strong>
                    <br><br>';
                
                // Test PHPMailer
                try {
                    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
                    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
                    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
                    
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'abdourahmanibrahim176@gmail.com';
                    $mail->Password = $cleanPassword;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    
                    $mail->setFrom('abdourahmanibrahim176@gmail.com', 'Royal Plaze Hotel');
                    $mail->addAddress('test@example.com', 'Test User');
                    $mail->isHTML(true);
                    $mail->Subject = 'Test App Password';
                    $mail->Body = '<h1>✅ Test réussi!</h1><p>Ton App Password fonctionne correctement.</p>';
                    $mail->AltBody = 'Test email';
                    
                    if ($mail->send()) {
                        echo '<strong style="color: #28a745;">✅ SUCCESS! L\'email a été envoyé!</strong>
                        <br><br>
                        <strong>Prochaine étape:</strong> Mets à jour <code>api/send_email.php</code> avec ce password';
                    } else {
                        echo '<strong style="color: #dc3545;">❌ Erreur:</strong> ' . htmlspecialchars($mail->ErrorInfo);
                    }
                    
                } catch (Exception $e) {
                    echo '<strong style="color: #dc3545;">❌ Exception:</strong> ' . htmlspecialchars($e->getMessage());
                }
                
                echo '</div>';
            }
        }
        ?>
        
        <h2>📝 Après avoir le bon password:</h2>
        
        <div class="step">
            <span class="step-number">5</span>
            <strong>Mettre à jour le code</strong>
            <br>
            Édite <code>api/send_email.php</code> et remplace:
            <div class="code-block">
$mail->Password = 'VIADUCHOLHOL123';
            </div>
            Par:
            <div class="code-block">
$mail->Password = 'tonapppassword16c';  // 16 caractères
            </div>
        </div>
        
        <div class="step">
            <span class="step-number">6</span>
            <strong>Tester la réservation complète</strong>
            <br>
            Va sur <a href="index.php">la page d'accueil</a>, fait une réservation et vérifie que tu reçois l'email de confirmation.
        </div>
    </div>
</body>
</html>

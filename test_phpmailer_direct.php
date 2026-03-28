<?php
/**
 * Test direct de PHPMailer - Version robuste
 */

// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclusion AVANT use
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;

echo "<pre style='background:#111;color:#d4a72c;padding:20px;font-family:monospace;border-radius:8px;'>";
echo "=== TEST PHPMAILER VERSION 2 ===\n\n";

// Étape 1: Vérifier les fichiers
echo "1️⃣ Vérification des fichiers...\n";

$paths = [
    __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php',
    __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php',
    __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php'
];

foreach ($paths as $path) {
    if (file_exists($path)) {
        echo "   ✅ " . basename($path) . "\n";
    } else {
        echo "   ❌ " . basename($path) . " - MANQUANT!\n";
        echo "      Chemin attendu: $path\n";
    }
}

echo "\n2️⃣ Inclusion des fichiers...\n";

try {
    echo "   ✅ Exception.php inclus\n";
    echo "   ✅ SMTP.php inclus\n";
    echo "   ✅ PHPMailer.php inclus\n";
    
    echo "\n3️⃣ Import namespace...\n";
    echo "   ✅ Namespace importé\n";
    
    echo "\n4️⃣ Création instance...\n";
    
    $mail = new PHPMailer(true);
    echo "   ✅ Instance PHPMailer créée\n";
    
    echo "\n5️⃣ Configuration SMTP...\n";
    
    $mail->isSMTP();
    echo "   ✅ Mode SMTP activé\n";
    
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'abdourahmanibrahim176@gmail.com';
    $mail->Password = 'VIADUCHOLHOL123';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    echo "   ✅ Paramètres SMTP définis\n";
    echo "      Host: smtp.gmail.com\n";
    echo "      Port: 587\n";
    echo "      Username: abdourahmanibrahim176@gmail.com\n";
    
    echo "\n6️⃣ Configuration message...\n";
    
    $mail->setFrom('abdourahmanibrahim176@gmail.com', 'Royal Plaze Hotel');
    $mail->addAddress('test@example.com', 'Test User');
    $mail->isHTML(true);
    $mail->Subject = 'Test Email PHPMailer';
    $mail->Body = '<h1>Test</h1><p>Ceci est un test PHPMailer</p>';
    $mail->AltBody = 'Test email';
    
    echo "   ✅ Message configuré\n";
    
    echo "\n7️⃣ ENVOI...\n";
    
    if ($mail->send()) {
        echo "   ✅✅✅ EMAIL ENVOYÉ AVEC SUCCÈS!\n";
        echo "\n   Vérifie ton Gmail: abdourahmanibrahim176@gmail.com\n";
    } else {
        echo "   ❌ Erreur lors de l'envoi\n";
        echo "   Erreur: " . $mail->ErrorInfo . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ EXCEPTION PHPMailer:\n";
    echo "   " . $e->getMessage() . "\n";
    
} catch (Error $e) {
    echo "❌ ERREUR FATALE:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . "\n";
    echo "   Ligne: " . $e->getLine() . "\n";
    
} catch (Throwable $t) {
    echo "❌ THROWABLE:\n";
    echo "   " . $t->getMessage() . "\n";
}

echo "\n</pre>";
?>

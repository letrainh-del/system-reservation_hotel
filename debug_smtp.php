<?php
/**
 * Débogue la connexion SMTP Gmail en détail
 */

require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;

echo "<pre style='background:#111;color:#d4a72c;padding:20px;font-family:monospace;border-radius:8px;'>";
echo "=== DEBUG CONNEXION SMTP ===\n\n";

try {
    $mail = new PHPMailer(true);
    
    // Mode DEBUG SMTP (2 = show SMTP commands and responses)
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'html';
    
    echo "Configuration SMTP...\n";
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'abdourahmanibrahim176@gmail.com';
    
    // ⚠️ CHANGE CE PASSWORD!
    // Utilise un App Password Google, pas le mot de passe du compte
    $mail->Password = 'VIADUCHOLHOL123';  // À REMPLACER PAR APP PASSWORD
    
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    echo "En cours de connexion à smtp.gmail.com:587...\n";
    echo "Utilisateur: abdourahmanibrahim176@gmail.com\n";
    echo "================================\n\n";
    
    $mail->setFrom('abdourahmanibrahim176@gmail.com', 'Hotel');
    $mail->addAddress('test@example.com', 'Test');
    $mail->isHTML(true);
    $mail->Subject = 'Test';
    $mail->Body = 'Test';
    
    echo "Tentative d'envoi...\n\n";
    
    if ($mail->send()) {
        echo "\n✅ EMAIL ENVOYÉ!\n";
    } else {
        echo "\n❌ Erreur:\n";
        echo $mail->ErrorInfo . "\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ EXCEPTION:\n";
    echo $e->getMessage() . "\n";
}

echo "\n</pre>";
?>

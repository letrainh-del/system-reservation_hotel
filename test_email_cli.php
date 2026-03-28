<?php
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    $mail->SMTPDebug = 2; // Debug level

    // Destinataires
    $mail->setFrom('abdourahmanibrahim176@gmail.com', 'Royal Plaze Hotel');
    $mail->addAddress('test@example.com', 'Test User'); // Change to your email for testing

    // Contenu
    $mail->isHTML(true);
    $mail->Subject = 'Test Email - Royal Plaze Hotel';
    $mail->Body = '<h1>Test réussi!</h1><p>Si vous recevez cet email, la configuration fonctionne.</p>';

    $mail->send();
    echo "Email envoyé avec succès!\n";

} catch (Exception $e) {
    echo "Erreur: " . $mail->ErrorInfo . "\n";
}
?>
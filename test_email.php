<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tester Email - Royal Plaze</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #0b0b0b;
            color: #fff;
            padding: 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #111;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #d4a72c;
        }
        h1 {
            color: #d4a72c;
            text-align: center;
        }
        .form-group {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="email"], input[type="text"] {
            width: 100%;
            padding: 10px;
            background: #1a1a1a;
            border: 1px solid #d4a72c;
            color: #fff;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #d4a72c;
            color: #000;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            opacity: 0.85;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 6px;
            display: none;
        }
        .result.success {
            background: #003000;
            color: #5fd35f;
            display: block;
        }
        .result.error {
            background: #2b0000;
            color: #ff4444;
            display: block;
        }
        .info {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            border-left: 4px solid #d4a72c;
        }
        .info h3 {
            color: #d4a72c;
            margin-top: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>📧 Tester l'envoi d'email</h1>
    
    <form id="emailForm">
        <div class="form-group">
            <label for="email">Adresse email de test:</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                placeholder="test@example.com" 
                value="abdourahmanibrahim176@gmail.com"
                required
            >
        </div>
        
        <button type="submit">🚀 Envoyer email de test</button>
    </form>
    
    <div id="result" class="result"></div>
    
    <div class="info">
        <h3>ℹ️ Comment ça marche?</h3>
        <p>
            1. Entre une adresse email<br>
            2. Clique sur "Envoyer email de test"<br>
            3. Attends le résultat<br>
            <br>
            <strong>Si ça échoue:</strong> C'est que WAMP n'a pas de serveur SMTP configuré.
        </p>
    </div>
</div>

<script>
document.getElementById('emailForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const resultDiv = document.getElementById('result');
    
    try {
        const response = await fetch('api/send_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'test_email=' + encodeURIComponent(email)
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.className = 'result success';
            resultDiv.innerHTML = '✅ ' + data.message;
        } else {
            resultDiv.className = 'result error';
            resultDiv.innerHTML = '❌ ' + data.message;
        }
    } catch (error) {
        resultDiv.className = 'result error';
        resultDiv.innerHTML = '❌ Erreur: ' + error.message;
    }
});
</script>

</body>
</html>

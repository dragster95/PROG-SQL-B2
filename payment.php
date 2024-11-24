<?php
session_start();
require 'vendor/autoload.php';

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost:3308;dbname=e-commerce', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// ID de l'utilisateur connecté
$userId = $_SESSION['user_id'] ?? 1; 

// Ajouter un moyen de paiement
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    $iban = $_POST['iban'];
    $card_number = $_POST['card_number'];
    $expiry_date = $_POST['expiry_date'];

    // Enregistrer le moyen de paiement dans la base de données
    $query = $pdo->prepare("INSERT INTO payment (user_id, payment_method, iban, card_number, expiry_date, created_at, updated_at) VALUES (:user_id, :payment_method, :iban, :card_number, :expiry_date, NOW(), NOW())");
    $query->execute([
        'user_id' => $userId,
        'payment_method' => $payment_method,
        'iban' => $iban,
        'card_number' => $card_number,
        'expiry_date' => $expiry_date,
    ]);

    echo "<p style='color: green;'>Moyen de paiement ajouté avec succès !</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Moyen de Paiement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: auto;
            padding: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        label, input, select {
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <h2>Ajouter un Moyen de Paiement</h2>
    <form method="POST" action="">
        <label for="payment_method">Méthode de paiement :</label>
        <select name="payment_method" id="payment_method" required>
            <option value="carte">Carte</option>
            <option value="virement">Virement</option>
        </select>
        
        <label for="iban">IBAN :</label>
        <input type="text" id="iban" name="iban" placeholder="Ex : FR76 3000 6000 1100 1234 5678 901" pattern="[A-Z0-9]+" maxlength="34">

        <label for="card_number">Numéro de carte :</label>
        <input type="text" id="card_number" name="card_number" placeholder="Numéro de carte" pattern="\d{16}" maxlength="16">

        <label for="expiry_date">Date d'expiration :</label>
        <input type="month" id="expiry_date" name="expiry_date">

        <button type="submit">Ajouter le Moyen de Paiement</button>
    </form>
</body>
</html>

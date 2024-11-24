<?php

require 'vendor/autoload.php';

use Faker\Factory;

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost:3308;dbname=e-commerce', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Créer une instance de Faker
$faker = Factory::create();

// Nombre d'entrées fictives à insérer
$nbEntries = 10;

// Insérer des données fictives dans la table "invoice"
for ($i = 0; $i < $nbEntries; $i++) {
    $command_id = $faker->numberBetween(1, 50); // Supposons que command_id correspond à des commandes existantes
    $amount = $faker->randomFloat(2, 10, 1000); // Montant entre 10€ et 1000€
    $issued_at = $faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d H:i:s'); // Date aléatoire dans la dernière année

    // Prépare l'insertion
    $query = $pdo->prepare("
        INSERT INTO invoice (command_id, amount, issued_at) 
        VALUES (:command_id, :amount, :issued_at)
    ");
    $query->execute([
        'command_id' => $command_id,
        'amount' => $amount,
        'issued_at' => $issued_at,
    ]);
}

echo "$nbEntries factures fictives ajoutées avec succès dans la base de données !";

?>

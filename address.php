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

// Insérer des données fictives dans la table
for ($i = 0; $i < $nbEntries; $i++) {
    $user_id = $faker->numberBetween(1, 50); // Supposons que user_id correspond à des utilisateurs existants
    $street = $faker->streetAddress;
    $city = $faker->city;
    $postal_code = $faker->postcode;
    $country = $faker->country;
    $created_at = $faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d H:i:s');
    $updated_at = $faker->dateTimeBetween($created_at, 'now')->format('Y-m-d H:i:s');

    // Prépare l'insertion
    $query = $pdo->prepare("
        INSERT INTO address (user_id, street, city, postal_code, country, created_at, updated_at) 
        VALUES (:user_id, :street, :city, :postal_code, :country, :created_at, :updated_at)
    ");
    $query->execute([
        'user_id' => $user_id,
        'street' => $street,
        'city' => $city,
        'postal_code' => $postal_code,
        'country' => $country,
        'created_at' => $created_at,
        'updated_at' => $updated_at,
    ]);
}

echo "$nbEntries adresses fictives ajoutées avec succès dans la base de données !";

?>

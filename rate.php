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

// Créer une instance de Faker en français
$faker = Factory::create('fr_FR');

// Nombre d'entrées fictives à insérer
$nbEntries = 20;

// Insérer des données fictives dans la table "rate"
for ($i = 0; $i < $nbEntries; $i++) {
    $user_id = $faker->numberBetween(1, 50); // Supposons que les IDs utilisateurs vont de 1 à 50
    $product_id = $faker->numberBetween(1, 100); // Supposons que les IDs produits vont de 1 à 100
    $rating = $faker->numberBetween(1, 5); // Notes de 1 à 5
    $comment = $faker->randomElement([
        "Produit de très bonne qualité, je suis satisfait.",
        "Moyennement satisfait, la livraison a pris du retard.",
        "Le produit correspond parfaitement à la description.",
        "Très déçu, l'article est arrivé endommagé.",
        "Un excellent rapport qualité-prix, je recommande !",
        "Le produit ne fonctionne pas comme attendu.",
        "Livraison rapide, rien à redire. Parfait !",
        "Le produit est correct, mais peut mieux faire.",
        "Un peu cher pour la qualité, mais ça passe.",
        "Je suis ravi de mon achat, tout est parfait !"
    ]); // Une sélection de commentaires en français
    $created_at = $faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d H:i:s'); // Date aléatoire sur la dernière année

    // Prépare l'insertion
    $query = $pdo->prepare("
        INSERT INTO rate (user_id, product_id, rating, comment, created_at) 
        VALUES (:user_id, :product_id, :rating, :comment, :created_at)
    ");
    $query->execute([
        'user_id' => $user_id,
        'product_id' => $product_id,
        'rating' => $rating,
        'comment' => $comment,
        'created_at' => $created_at,
    ]);
}

echo "$nbEntries avis fictifs ajoutés avec succès dans la base de données !";

?>

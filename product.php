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

// Gérer l'ajout de produit à partir du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Vérifie si le produit existe déjà
    $query = $pdo->prepare("SELECT COUNT(*) FROM product WHERE name = :name");
    $query->execute(['name' => $name]);
    $exists = $query->fetchColumn();

    if ($exists) {
        echo "<p style='color: red;'>Un produit avec ce nom existe déjà. Veuillez en choisir un autre.</p>";
    } else {
        // Prépare et exécute l'insertion
        $query = $pdo->prepare("INSERT INTO product (name, description, price) VALUES (:name, :description, :price)");
        $query->execute([
            'name' => $name,
            'description' => $description,
            'price' => $price,
        ]);
        echo "<p style='color: green;'>Produit ajouté avec succès.</p>";
    }
}

// Récupérer les produits de la base de données
$query = $pdo->query("SELECT id, name, description, price FROM product");
$products = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter des Produits</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 80%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ccc;
        }
        th {
            background-color: #f2f2f2;
        }
        form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Ajouter un Produit</h1>

    <form method="POST" action="">
        <label for="name">Nom du Produit :</label><br>
        <input type="text" id="name" name="name" required><br><br>
        
        <label for="description">Description :</label><br>
        <textarea id="description" name="description" required></textarea><br><br>
        
        <label for="price">Prix :</label><br>
        <input type="number" id="price" name="price" step="0.01" required><br><br>
        
        <input type="submit" value="Ajouter le Produit">
    </form>

    <h1 style="text-align: center;">Liste des Produits</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Prix</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['description']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($product['price'], 2, ',', ' ')); ?> €</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

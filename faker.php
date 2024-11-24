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

// Insérer des données fictives dans la table "user"
for ($i = 0; $i < 10; $i++) {
    $name = $faker->name;
    $email = $faker->unique()->safeEmail; // Utiliser unique() pour éviter les doublons
    $password = password_hash($faker->password, PASSWORD_DEFAULT); // Hash du mot de passe
    $phone_number = $faker->phoneNumber; // Utilisation correcte de phoneNumber

    // Prépare l'insertion
    $query = $pdo->prepare("INSERT INTO user (name, email, password, phone_number) VALUES (:name, :email, :password, :phone_number)");
    $query->execute([
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'phone_number' => $phone_number,
    ]);
}

//echo "10 utilisateurs fictifs ajoutés dans la base de données !";


// Récupérer les utilisateurs de la base de données
$query = $pdo->query("SELECT name, email, phone_number FROM user");
$users = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Utilisateurs</title>
    <style>
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
    </style>
</head>
<body>
    <h1 style="text-align: center;">Liste des Utilisateurs</h1>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Téléphone</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
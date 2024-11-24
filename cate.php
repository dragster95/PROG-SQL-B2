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

// ID de l'utilisateur connecté (à remplacer par le vrai ID utilisateur lors de la connexion)
$userId = $_SESSION['user_id'] ?? 1;

// Initialiser le panier dans la session si non existant
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Gérer l'ajout de produit au panier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Récupérer les informations du produit
    $query = $pdo->prepare("SELECT id, name, price FROM product WHERE id = :id");
    $query->execute(['id' => $product_id]);
    $product = $query->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $product['quantity'] = $quantity;
        
        // Ajouter le produit au panier (ou mettre à jour si déjà existant)
        $_SESSION['cart'][$product_id] = $product;
    }
}

// Calculer le coût total du panier
$totalCost = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalCost += $item['price'] * $item['quantity'];
}

// Afficher le formulaire de paiement si le panier n'est pas vide
if (!empty($_SESSION['cart']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay'])) {
    $paymentMethod = $_POST['payment_method'];
    $cardNumber = $_POST['card_number'];
    $expiryDate = $_POST['expiry_date'];

    // Enregistrer les informations de paiement
    $query = $pdo->prepare("INSERT INTO payment (user_id, payment_method, card_number, expiry_date) VALUES (:user_id, :payment_method, :card_number, :expiry_date)");
    $query->execute([
        'user_id' => $userId,
        'payment_method' => $paymentMethod,
        'card_number' => $cardNumber,
        'expiry_date' => $expiryDate
    ]);

    // Créer une commande avec le total
    $pdo->beginTransaction();
    try {
        $query = $pdo->prepare("INSERT INTO cart (user_id) VALUES (:user_id)");
        $query->execute(['user_id' => $userId]);
        $cartId = $pdo->lastInsertId();

        // Ajouter les produits de la session dans cart_product
        foreach ($_SESSION['cart'] as $item) {
            $query = $pdo->prepare("INSERT INTO cart_product (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :quantity)");
            $query->execute([
                'cart_id' => $cartId,
                'product_id' => $item['id'],
                'quantity' => $item['quantity']
            ]);
        }

        // Créer une commande avec le total
        $query = $pdo->prepare("INSERT INTO command (user_id, cart_id, total_amount) VALUES (:user_id, :cart_id, :total_amount)");
        $query->execute([
            'user_id' => $userId,
            'cart_id' => $cartId,
            'total_amount' => $totalCost
        ]);

        // Mettre à jour le statut de la commande
        $query = $pdo->prepare("UPDATE command SET status = 'terminé' WHERE user_id = :user_id AND cart_id = :cart_id");
        $query->execute([
            'user_id' => $userId,
            'cart_id' => $cartId // Utiliser le cartId généré
        ]);

        // Confirmer la transaction
        $pdo->commit();

        // Vider le panier après la commande
        $_SESSION['cart'] = [];
        echo "<p style='color: green;'>Commande passée avec succès ! Montant total : " . number_format($totalCost, 2, ',', ' ') . " €</p>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<p style='color: red;'>Erreur lors du passage de la commande : " . $e->getMessage() . "</p>";
    }
}

// Récupérer tous les produits pour l'affichage
$query = $pdo->query("SELECT id, name, description, price FROM product");
$products = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: space-between;
            margin: 20px;
        }
        .product-list, .cart {
            width: 45%;
        }
        .cart {
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .product {
            margin-bottom: 15px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="product-list">
        <h2>Produits Disponibles</h2>
        <?php foreach ($products as $product): ?>
            <div class="product">
                <form method="POST" action="">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <p>Prix : <?php echo htmlspecialchars(number_format($product['price'], 2, ',', ' ')); ?> €</p>
                    <label for="quantity_<?php echo $product['id']; ?>">Quantité:</label>
                    <input type="number" id="quantity_<?php echo $product['id']; ?>" name="quantity" value="1" min="1" required>
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit">Ajouter au panier</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="cart">
        <h2>Panier</h2>
        <?php if (!empty($_SESSION['cart'])): ?>
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <div class="cart-item">
                    <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                    <span><?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2, ',', ' ')); ?> €</span>
                </div>
            <?php endforeach; ?>
            <hr>
            <h3>Total : <?php echo htmlspecialchars(number_format($totalCost, 2, ',', ' ')); ?> €</h3>
            
            <!-- Formulaire de paiement -->
            <h2>Informations de Paiement</h2>
            <form method="POST" action="">
                <label for="payment_method">Méthode de paiement:</label>
                <select name="payment_method" required>
                    <option value="carte">Carte de Crédit</option>
                    <option value="iban">IBAN</option>
                </select><br>
                <label for="card_number">Numéro de carte:</label>
                <input type="text" name="card_number" required><br>
                <label for="expiry_date">Date d'expiration (MM/AA):</label>
                <input type="text" name="expiry_date" required><br>
                <button type="submit" name="pay">Valider et Payer</button>
            </form>
        <?php else: ?>
            <p>Votre panier est vide.</p>
        <?php endif; ?>
    </div>
</body>
</html>

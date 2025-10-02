<?php

require_once __DIR__ . '/../database/database.php';

$db = getDB();

if (isset($_GET['bike_id'])) {
    $bike_id = (int) $_GET['bike_id'];

    $sql = "SELECT * FROM bikes WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $bike_id]);
    $bike = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$bike) {
        die("Vélo introuvable.");
    }
} else {
    die("Aucun vélo sélectionné.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation</title>
    <link rel="stylesheet" href="/../style.css">
</head>

<body>
    <header class="site-header">
        <div class="wrap">
            <a href="../index.php" class="brand">
                <img src="../logo.png" alt="RentalBike" class="brand-logo">
            </a>
        </div>
    </header>

    <main class="container">
        <h1>Réservation du vélo <?= $bike->code ?></h1>

        <form action="add_reservation.php" method="post" class="form">
            <input type="hidden" name="bike_id" value="<?= $bike->id ?>">

            <div class="form-group">
                <label for="renter_name">Votre nom</label>
                <input type="text" name="renter_name" id="renter_name" required>
            </div>

            <div class="form-group">
                <label for="expected_end_time">Heure de retour prévue</label>
                <input type="datetime-local" name="expected_end_time" id="expected_end_time" required>
            </div>

            <button type="submit" class="btn btn-primary">Confirmer la réservation</button>
        </form>
    </main>

    <footer class="site-footer">
        <div class="wrap">
            <p>© <?= date('Y'); ?> RentalBike</p>
        </div>
    </footer>
</body>

</html>
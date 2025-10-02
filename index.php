<?php

require_once __DIR__ . '/database/database.php';
date_default_timezone_set('Europe/Paris');

$db = getDB();

$sql = "SELECT 
            stations.id, stations.name, stations.location,
            bikes.id AS bike_id, bikes.code, bikes.status, bikes.in_use, bikes.station_id,
            rentals.expected_end_time,
            IF(bikes.in_use = 1 AND rentals.end_time IS NULL AND rentals.expected_end_time < NOW(), 1, 0) AS is_late
        FROM stations
        LEFT JOIN bikes ON bikes.station_id = stations.id
        LEFT JOIN rentals ON rentals.bike_id = bikes.id AND rentals.end_time IS NULL
        ORDER BY stations.name,
            CASE
                WHEN bikes.in_use = 0 AND bikes.status = 'available' THEN 1
                WHEN bikes.in_use = 1 AND IF(rentals.expected_end_time < NOW(), 1, 0) = 0 THEN 2
                WHEN bikes.in_use = 1 AND rentals.expected_end_time < NOW() THEN 3 -- en retard
                ELSE 4
            END";

$query = $db->prepare($sql);
$query->execute();
$rows = $query->fetchAll(PDO::FETCH_OBJ);

$stations = [];

foreach ($rows as $row) {
    if (!isset($stations[$row->id])) {
        $stations[$row->id] = [
            'name' => $row->name,
            'location' => $row->location,
            'bikes' => []
        ];
    }

    if ($row->code) {
        $stations[$row->id]['bikes'][] = $row;
    }
}

// echo '<pre>';
// print_r($stations);
// echo '</pre>';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalBike</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="site-header">
        <div class="wrap">
            <a class="brand" href="index.php">
                <img src="logo.png" alt="RentalBike" class="brand-logo">
            </a>
            <nav class="nav">
                <a href="index.php" class="nav-link is-active">Accueil</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="page-head">
            <h1>Stations & vélos</h1>
            <p class="sub">Trouvez un vélo disponible en un clin d’œil.</p>
        </div>

        <?php foreach ($stations as $station): ?>
            <section class="station">
                <header class="station-header">
                    <h2><?= $station['name'] ?></h2>
                    <span class="station-location"><?= $station['location'] ?></span>
                </header>

                <?php if (!empty($station['bikes'])): ?>
                    <div class="grid cards">
                        <?php foreach ($station['bikes'] as $bike): ?>
                            <?php
                            // Déterminer classe + badge d’état
                            $now = new DateTime();
                            $expected = $bike->expected_end_time ? new DateTime($bike->expected_end_time) : null;

                            $cardClass = "card";
                            $badgeLabel = "Indisponible";
                            $badgeClass = "badge";

                            if (!$bike->in_use && $bike->status === 'available') {
                                $cardClass .= " available";
                                $badgeLabel = "Disponible";
                                $badgeClass .= " badge-available";
                            } elseif ($bike->in_use && $expected && $now > $expected) {
                                $cardClass .= " late";
                                $badgeLabel = "En retard";
                                $badgeClass .= " badge-late";
                            } elseif ($bike->in_use) {
                                $cardClass .= " inuse";
                                $badgeLabel = "Loué";
                                $badgeClass .= " badge-inuse";
                            }
                            ?>

                            <article class="<?= $cardClass ?>">
                                <div class="card-top">
                                    <span class="<?= $badgeClass ?>"><?= $badgeLabel ?></span>
                                    <span class="code">#<?= $bike->code ?></span>
                                </div>

                                <div class="card-body">
                                    <ul class="meta">
                                        <li><strong>Statut</strong> <span><?= $bike->status ?></span></li>
                                        <li><strong>En utilisation</strong> <span><?= $bike->in_use ? "Oui" : "Non" ?></span></li>
                                        <?php if ($bike->in_use && $bike->expected_end_time): ?>
                                            <li><strong>Retour prévu</strong> <span><?= $bike->expected_end_time ?></span></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                <div class="card-actions">
                                    <?php if (!$bike->in_use && $bike->status === 'available'): ?>
                                        <form action="/gestion/reservation.php" method="get">
                                            <input type="hidden" name="bike_id" value="<?= $bike->bike_id ?>">
                                            <button type="submit" class="btn btn-primary">Réserver</button>
                                        </form>
                                    <?php elseif ($bike->in_use): ?>
                                        <form action="/gestion/return.php" method="post">
                                            <input type="hidden" name="bike_id" value="<?= $bike->bike_id ?>">
                                            <button type="submit" class="btn btn-warning">Rendre</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-disabled" disabled>Indisponible</button>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="empty">Aucun vélo dans cette station.</p>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </main>

    <footer class="site-footer">
        <div class="wrap">
            <p>© <?= date('Y'); ?> RentalBike — Ridez malin, ridez libre 🚴‍♀️</p>
            <nav class="footer-nav">
                <a href="#" class="footer-link">Mentions légales</a>
                <a href="#" class="footer-link">Contact</a>
                <a href="#" class="footer-link">Aide</a>
            </nav>
        </div>
    </footer>
</body>


</html>
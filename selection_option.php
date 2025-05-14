<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['voyage-id'])) {
    header("Location: voyager.php");
    exit();
}

// Chargement des données JSON
$tripId = $_POST['voyage-id'];
$voyagesData = json_decode(file_get_contents('../json/voyages_complets.json'), true);
$etapesData = json_decode(file_get_contents('../json/etapes.json'), true);
$optionsData = json_decode(file_get_contents('../json/options.json'), true);

// Trouver le voyage sélectionné
$selected_voyage = null;
foreach ($voyagesData['voyages'] as $v) {
    if ($v['id'] === $tripId) {
        $selected_voyage = $v;
        break;
    }
}

if (!$selected_voyage) {
    header("Location: voyager.php?error=voyage_not_found");
    echo "<p>Voyage introuvable</p>";
    exit();
}

// Traitement des données de formulaire
$dates = [
    'depart' => $_POST['date-voyage'] ?? date('Y-m-d'),
    'arrivee' => $_POST['date-arrivee'] ?? date('Y-m-d', strtotime('+7 days')),
    'duree' => (strtotime($_POST['date-arrivee'] ?? '+7 days') - strtotime($_POST['date-voyage'] ?? 'today')) / 86400,
];

// Enregistrer les données pour la récapitulation
$_SESSION['current_voyage'] = [
    'voyage_id' => $tripId,
    'titre' => $selected_voyage['titre'],
    'dates' => $dates,
    'prix' => $selected_voyage['prix'],
    'classe' => $_POST['flight-class'] ?? 'economy',
    'sans_escale' => isset($_POST['no-escale']),
    'passagers' => [
        'adultes' => $_POST['adultes'] ?? 1,
        'enfants' => $_POST['enfants'] ?? 0,
        'bebes' => $_POST['bebes'] ?? 0
    ],
    'options' => $selected_voyage['etapes'] ?? []
];
    
// Traitement des options sélectionnées
if (isset($_POST['options'])) {
    foreach ($_POST['options'] as $etape_id => $option_id) {
        $current_etape = $etapesData[$etape_id] ?? null;
        $current_option = $optionsData[$option_id] ?? null;

        if ($current_etape && $current_option) {
            $recap_data['options'][] = [
                'etape' => $current_etape['titre'],
                'nom' => implode(', ', $current_option['activités']),
                'prix' => $current_option['prix_par_personne']
            ];
        }
    }
}

if(!$tripId) {
    echo "<p>Aucun voyage sélectionnée.</p>";
    exit;
}

$imagePath = "../php/map/images/" . ucfirst(strtolower(str_replace(' ', '', $selected_voyage['titre']))) . ".png";
if (!file_exists($imagePath)) {
    $imagePath = "../php/map/images/Mars.png";
}

// Vérification de la session utilisateur
$pp = "../../img/default.png";
$isLoggedIn = false;
$isAdmin = false;

if (isset($_SESSION['email'])) {
    $isLoggedIn = true;
    $json_file = "../json/utilisateurs.json";
    $json = file_get_contents($json_file);
    $data = json_decode($json, true);

    if ($data !== null) {
        $email = $_SESSION['email'];
        foreach ($data["admin"] as $admin) {
            if ($admin["email"] === $email) {
                $isAdmin = true;
                if (!empty($admin["pp"])) {
                    $pp = $admin["pp"];
                }
                break;
            }
        }
        if (!$isAdmin) {
            foreach ($data["user"] as $user) {
                if ($user["email"] === $email) {
                    if (!empty($user["pp"])) {
                        $pp = $user["pp"];
                    }
                    break;
                }
            }
        }
    }
}

// Redirection vers la page récapitulative
header("Location: recapitulatif.php");
exit();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>A.L.I.X. - Options de voyage</title>
    <meta charset="UTF-8">
    <link href="../../../css/style.css" rel="stylesheet" />
    </head>
<body>
    <div class="fondpage">
        <video class="fond" autoplay loop muted>
            <source src="../../../img/video.mp4">
        </video>
        <div class="topv2">
            <div class="topleft">
                <a href="index.php">
                    <video class="logo" autoplay muted>
                        <source src="../../../img/Logo-3-[cut](site).mp4" type="video/mp4">
                    </video>
                </a>
            </div>
            <ul>
                <li><a href="../../aboutus.php">A propos</a></li>
                <li>|</li>
                <li><a href="../../voyager.php">Voyager</a></li>
                <?php if (!$isLoggedIn): ?>
                    <li>|</li>
                    <li><a href="../../login.php">Connexion</a></li>
                    <li>|</li>
                    <li><a href="../../sign-up.php">Inscription</a></li>
                <?php else: ?>
                    <?php if ($isAdmin): ?>
                        <li>|</li>
                        <li><a href="admin.php">Admin</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <a href="../user.php">
                <img src="<?php echo htmlspecialchars($pp); ?>" alt="Profil" class="pfp" onerror="this.src='../../img/default.png'">
            </a>
        </div>

        <img class="imgplanete" src="../../../img/<?php echo strtolower(str_replace(' ', '', $selected_voyage['titre'])); ?>.jpg" onerror="this.src='../../../img/mercure2.jpg'">
        <h1><?php echo htmlspecialchars($selected_voyage['titre']); ?></h1>
        <p class="voyage-dates">
            Du <?php echo date('d/m/Y', strtotime($dates['depart'])); ?> 
            au <?php echo date('d/m/Y', strtotime($dates['arrivee'])); ?>
            (<?php echo $dates['duree']; ?> jours)
        </p>

        <form id="options-form" method="POST">
            <input type="hidden" name="voyage-id" value="<?php echo htmlspecialchars($voyage_id); ?>">
            <input type="hidden" name="date-voyage" value="<?php echo htmlspecialchars($dates['depart']); ?>">
            <input type="hidden" name="date-arrivee" value="<?php echo htmlspecialchars($dates['arrivee']); ?>">
            <input type="hidden" name="adultes" value="<?php echo htmlspecialchars($_POST['adultes'] ?? 1); ?>">
            <input type="hidden" name="enfants" value="<?php echo htmlspecialchars($_POST['enfants'] ?? 0); ?>">
            <input type="hidden" name="bebes" value="<?php echo htmlspecialchars($_POST['bebes'] ?? 0); ?>">
            <input type="hidden" name="flight-class" value="<?php echo htmlspecialchars($_POST['flight-class'] ?? 'economy'); ?>">
            <input type="hidden" name="no-escale" value="<?php echo isset($_POST['no-escale']) ? '1' : '0'; ?>">
            <input type="hidden" name="prix" value="<?php echo htmlspecialchars($selected_voyage['prix']); ?>">


            <?php foreach ($selected_voyage['etapes'] as $etape_id): 
                $current_etape = null;
                foreach ($etapes_data['etapes'] as $etape) {
                    if ($etape['id'] === $etape_id) {
                        $current_etape = $etape;
                        break;
                    }
                }
                if (!$current_etape) continue;
            ?>
            
            <div class="etape-container">
                <h2><?php echo htmlspecialchars($current_etape['titre']); ?></h2>
                <div class="options-container">
                    <?php foreach ($current_etape['options'] as $option_id): 
                        $current_option = null;
                        foreach ($options_data['options'] as $option) {
                            if ($option['id'] === $option_id) {
                                $current_option = $option;
                                break;
                            }
                        }
                        if (!$current_option) continue;
                    ?>
                    <label class="option-card">
                        <input type="radio" name="options[<?php echo $etape_id; ?>]" value="<?php echo $option_id; ?>" required>
                        <div class="option-content">
                            <div class="option-details">
                                <h3><?php echo htmlspecialchars(implode(', ', $current_option['activités'])); ?></h3>
                                <p class="price"><?php echo htmlspecialchars($current_option['prix_par_personne']); ?> € / personne</p>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="form-actions">
                <button type="submit" class="btn-envoyer">Voir le récapitulatif</button>
            </div>
        </form>

        <div class="bottom">
            <h1>Crédits</h1>
            <div class="textebot">
                <h2>Nassim</h2>
                <h2>Atahan</h2>
                <h2>Romain</h2>
                <h2>Gabin</h2>
            </div>
        </div>
    </div>
</body>
</html>
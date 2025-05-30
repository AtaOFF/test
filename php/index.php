<?php
session_start();

$pp = "../img/default.png";
$isLoggedIn = false;
$isAdmin = false; // Nouvelle variable pour vérifier le statut admin

if (isset($_SESSION['email'])) {
    $isLoggedIn = true;
    $json_file = "../json/utilisateurs.json";
    $json = file_get_contents($json_file);
    $data = json_decode($json, true);

    if ($data !== null) {
        $email = $_SESSION['email'];
        
        // Vérification dans la section admin
        foreach ($data["admin"] as $admin) {
            if ($admin["email"] === $email) {
                $isAdmin = true;
                if (!empty($admin["pp"])) {
                    $pp = $admin["pp"];
                }
                break;
            }
        }
        
        // Si pas admin, vérification dans la section user
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
?>

<!DOCTYPE html>
<html>
<head>	
	<meta charset="UTF-8">
	<title>A.L.I.X.</title>
	<link href="../css/style.css" rel="stylesheet" />
</head>
<body>
	<video class="fond" autoplay loop muted>
		<source src="../img/video.mp4">
	</video>
	<div class="top">
		<div class="topleft">
			<a href="index.php">
				<video class="logo" autoplay muted>
					<source src="../img/Logo-3-[cut](site).mp4" type="video/mp4">
				</video>
			</a>
		</div>
		<ul>
			<li><a href="aboutus.php">A propos</a></li>
			<li>|</li>
			<li><a href="voyager.php">Voyager</a></li>
			<?php if (!$isLoggedIn): ?>
				<li>|</li>
				<li><a href="login.php">Connexion</a></li>
				<li>|</li>
				<li><a href="sign-up.php">Inscription</a></li>
			<?php else: ?>
				<?php if ($isAdmin): ?>
					<li>|</li>
					<li><a href="admin.php">Admin</a></li>
				<?php endif; ?>
			<?php endif; ?>
		</ul>
		<a href="user.php">
            <img src="<?php echo htmlspecialchars($pp); ?>" alt="Profil" class="pfp" onerror="this.src='../img/default.png'">
		</a>
	</div>
	<div class="en-tete"></div>
	<div class="espace"></div>
	<div class="recherche">
		<table class="recherche">
			<tr>
				<td class="recherche">
					<label for="depart">Date de départ :</label>
					<input type="date" id="depart" name="depart">
				</td>
				<td class="recherche">
					<label for="arrivee">Date d'arrivée :</label>
					<input type="date" id="arrivee" name="arrivee">
				</td>
				<td class="recherche">
					<input type="text" id="searchInput" placeholder="Recherche de destination">
					<button onclick="search()">🔍</button>
				</td>
			</tr>
			<tr>
				<table class="nbr-passager">
					<tr>
						<td class="passager">
							<div>
								<label>Adultes :</label>
								<button onclick="changeValue('adultes', -1)">-</button>
								<span id="adultes">1</span>
								<button onclick="changeValue('adultes', 1)">+</button> 
							</div>
						</td>
						<td class="passager">
							<div class="passager">
								<label>Enfants :</label>
								<button onclick="changeValue('enfants', -1)">-</button>
								<span id="enfants">0</span>
								<button onclick="changeValue('enfants', 1)">+</button> 
							</div>
						</td>
						<td class="passager">
							<div class="passager">
								<label>Bébés :</label>
								<button onclick="changeValue('bebes', -1)">-</button>
								<span id="bebes">0</span>
								<button onclick="changeValue('bebes', 1)">+</button> 
							</div>
						</td>
					</tr>
				</table>
			</tr>
		</table>
	</div>
	<div class="promotion">
		<div class="promotion2">
			<h1>Planètes à découvrir</h1>
			<a href="#" class="promo-wrapper">
				<img src="../img/Terre.png" class="promo">
				<div class="overlay">Voir plus</div>
			</a>
			<a href="#" class="promo-wrapper">
				<img src="../img/Solidays.png" class="promo">
				<div class="overlay">Voir plus</div>
			</a>
			<a href="#" class="promo-wrapper">
				<img src="../img/Scofiled.png" class="promo">
				<div class="overlay">Voir plus</div>
			</a>
			<a href="#" class="promo-wrapper">
				<img src="../img/Malm.png" class="promo">
				<div class="overlay">Voir plus</div>
			</a>
		</div>
	</div>
	
	<div class = "boutique">
		<table>
			<tr>
				<td><a href="produit1.html"> <img src = "../img/produit1.jpg" class="produit"> </a></td>
				<td><a href="produit2.html"> <img src = "../img/produit2.jpg" class="produit"> </a></td>
				<td><a href="produit3.html"> <img src = "../img/produit3.jpg" class="produit"> </a></td>
				<td><a href="produit4.html"> <img src = "../img/produit4.jpg" class="produit"> </a></td>
				<td class="boutton"><a href = "boutique.html" ><h2><strong>Aller a la boutique</strong></h2></a></td>
			</tr>
		</table>
	</div>
	
	<div class="bottom">
		<h1>Crédits</h1>
		<div class="textebot">
			<h2>Nassim</h2>
			<h2>Atahan</h2>
			<h2>Romain</h2>
			<h2>Gabin</h2>
		</div>
	</div>
</body>
</html>



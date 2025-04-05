<?php
session_start();
$isConnected = isset($_SESSION['user']);
?>
<!DOCTYPE HTML>

<html>
	<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>SaveurHub</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="author" content="ZEROUAL Mohammed" />

	

	<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Kaushan+Script" rel="stylesheet">
	
	<!-- Animate.css -->
	<link rel="stylesheet" href="css/animate.css">
	<!-- Icomoon Icon Fonts-->
	<link rel="stylesheet" href="css/icomoon.css">
	<!-- Themify Icons-->
	<link rel="stylesheet" href="css/themify-icons.css">
	<!-- Bootstrap  -->
	<link rel="stylesheet" href="css/bootstrap.css">

	<!-- Magnific Popup -->
	<link rel="stylesheet" href="css/magnific-popup.css">

	<!-- Bootstrap DateTimePicker -->
	<link rel="stylesheet" href="css/bootstrap-datetimepicker.min.css">

	<!-- Owl Carousel  -->
	<link rel="stylesheet" href="css/owl.carousel.min.css">
	<link rel="stylesheet" href="css/owl.theme.default.min.css">

	<!-- Theme style  -->
	<link rel="stylesheet" href="css/style.css">

	<!-- Modernizr JS -->
	<script src="js/modernizr-2.6.2.min.js"></script>
	<!-- FOR IE9 below -->
	<script src="script.js"></script>
	<!-- CSS Du j'aime -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
	


	<style>
		.heart-icon {
		color: red;
		font-size: 24px;
		margin-left: 10px;
		transition: transform 0.3s ease-in-out;
		}

		.heart-icon:hover {
		transform: scale(1.2);
		color: darkred;
		}
	</style>



	</head>
	<body>
		
	<div class="gtco-loader"></div>
	
	<div id="page">

	
	<!-- <div class="page-inner"> -->
	<nav class="gtco-nav" role="navigation">
			<div class="gtco-container">
				
				<div class="row" style="display: flex;">
					<div class="col-sm-4 col-xs-12" style="flex: 1;">
						<div id="gtco-logo" style="padding-top: 13px;"><a href="index.php">SaveurHub <em>.</em></a></div>
					</div>
	
					<div class="col-sm-4 col-xs-12" style="flex: 2;">
						<form class="navbar-form" role="search" action="search.php" method="GET" style="display: flex;">	
							<input type="text" class="form-control" name="query" placeholder="Rechercher une recette..." style="flex-grow: 1; padding: 2px; font-size: 16px; width: 100%;">
							<button class="btn-cta" type="submit" style="height: 45px; font-size: 14px;">Rechercher</button>
						</form>
					</div>
	
		
					<div class="col-xs-8 text-right menu-1" style="flex: 1;"  >
						<ul style="padding-top: 13px;">
							<li><a href="menu.html">Menu</a></li>
							<li><a href="contact.php">Contact</a></li>
							<li class="has-dropdown">
								<?php if (!$isConnected): ?>
									<a href="connexion.php">Se connecter</a>
									<ul class="dropdown">
										<li><a href="connexion.php">Se Connecter</a></li>
										<li><a href="inscription.php">S'inscrire</a></li>
									</ul>
								<?php else: ?>
									<a href="user.php"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></a>
									<ul class="dropdown">
										<li><a href="user.php">Profil</a></li>
										<li><a href="deconnexion.php">Se deconnecter</a></li>
									</ul>
								<?php endif; ?>

							</li>
							
						</ul>
					</div>
				</div>
				
			</div>
		</nav>

        <header id="gtco-header" class="gtco-cover gtco-cover-md" role="banner" style="background-image: url(images/img_bg_1.jpg); height:90px;" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		
	    </header>

        <?php
            $recipes = json_decode(file_get_contents('recipes.json'), true);
            $name = isset($_GET['name']) ? urldecode($_GET['name']) : null;
            $langue = isset($_GET['lang']) ? $_GET['lang'] : 'anglais';

            // Recherche de la recette par son nom
                $recipe = null;
                foreach ($recipes as $r) {
                    if (($r['name'] ?? $r['name']) === $name) {
                        $recipe = $r;
                        break;
                    }
                }

                if (!$recipe) {
                    echo "<h1>Recette non trouvée</h1>";
                    exit;
                }
            //recette recuperer dans recipe
            $name="";
            $author="";
            $image="";
            $steps="";
            $ingredients="";
            if($langue==='francais')
            {
                if (isset($recipe['imageURL']))
                    $image=$recipe['imageURL'];
                else 
                    $image="images/img_6.jpg";
        
                $name = $recipe['nameFR'];
        
                if (isset($recipe['Author'])&& $recipe['Author']!=='Unknown')
                    $author=$recipe['Author'];
                else
                    $author='inconnu';
                if(is_array($recipe['stepsFR']))
                    $steps=implode("| ", $recipe['stepsFR']);
                else
                    $steps="";
                if(is_array($recipe['ingredientsFR']))
                {
                    foreach ($recipe['ingredientsFR'] as $ingredient) {
                        $ingredients.="| ". $ingredient['quantity'] . ' ' . $ingredient['name'] .' ' . $ingredient['type'].".";
                    }
                    $ingredients = substr($ingredients, 1);
                }
                else    
                    $ingredients="";
            }
            else //en anglais
            {
                if (isset($recipe['imageURL']))
                    $image=$recipe['imageURL'];
                else 
                    $image="images/img_6.jpg";

                $name = $recipe['name'];

                if (isset($recipe['Author']))
                    $author=$recipe['Author'];
                else
                    $author='Auteur inconnu';
                if(is_array($recipe['steps']))
                    $steps=implode("| ", $recipe['steps']);
                else
                    $steps="";
                if(is_array($recipe['ingredients']))
                {
                    foreach ($recipe['ingredients'] as $ingredient) {
                        $ingredients.="| ". $ingredient['quantity'] . ' ' . $ingredient['name'] .' (' . $ingredient['type']. ').';
                        
                    }
                    $ingredients = substr($ingredients, 1);
                }
                else    
                    $ingredients="";
            }


            ?>
        <div class="recipe-container">
            <div class="recipe-card">
                <div class="recipe-image">
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Image de <?php echo htmlspecialchars($name); ?>">
                </div>
                    <div class="recipe-content">
                        <h2>
							<?php echo htmlspecialchars($name); ?>
							<span class="heart-icon"><i class="fa-solid fa-heart"></i></span>
						</h2>
                        <p><strong>
							<?php if($langue==='francais')
									echo"Auteur:";
								else
									echo"Author";?>
						</strong> <?php echo htmlspecialchars($author); ?></p>
                        
                        <h3>
							<?php if($langue==='francais')
									echo"Ingrédients: ";
								else
									echo"Ingredients: ";?>
						</h3>
                        <ul>
                            <?php 
                            foreach (explode("| ", $ingredients) as $ingredient) {
                                echo "<li>" . htmlspecialchars($ingredient) . "</li>";
                            }
                            ?>
                        </ul>

                        <h3>
							<?php if($langue==='francais')
									echo"Etapes: ";
								else
									echo"Steps: ";?>
						</h3>
                        <ol>
                            <?php 
                            foreach (explode("| ", $steps) as $step) {
                                echo "<li>" . htmlspecialchars($step) . "</li>";
                            }
                            ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>                  



        <footer id="gtco-footer" role="contentinfo" style="background-image: url(images/img_bg_1.jpg)" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="gtco-container">
			<div class="row row-pb-md">

				

				
				<div class="col-md-12 text-center">
					<div class="gtco-widget">
						<h3>Get In Touch</h3>
						<ul class="gtco-quick-contact">
							<li><a> +33 7 58 33 87 80</a></li>
							<li><a>contact@saveurhub.com</a></li>
						</ul>
					</div>
					<div class="gtco-widget">
						<h3></h3>
						<h3>Get Social</h3>
						<ul class="gtco-social-icons">
							<li><a href="#"><i class="icon-twitter"></i></a></li>
							<li><a href="#"><i class="icon-facebook"></i></a></li>
							<li><a href="#"><i class="icon-linkedin"></i></a></li>
							<li><a href="#"><i class="icon-dribbble"></i></a></li>
						</ul>
					</div>
				</div>

				<div class="col-md-12 text-center copyright">
					<p><small class="block">&copy; 2025 Université Paris Saclay. All Rights Reserved.</small> 
						<small class="block">Realisé par <a href="https://www.linkedin.com/in/zeroual-mohammed/" target="_blank">ZEROUAL Mohammed</a>  Et  <a href="https://www.linkedin.com/in/souleimaneelqodsi/" target="_blank">Souleimane El Qodsi</a></small></p>
				</div>

			</div>


            


			

		</div>
	</footer>
    </div>

	<div class="gototop js-top">
		<a href="#" class="js-gotop"><i class="icon-arrow-up"></i></a>
	</div>
	
	<!-- jQuery -->
	<script src="js/jquery.min.js"></script>
	<!-- jQuery Easing -->
	<script src="js/jquery.easing.1.3.js"></script>
	<!-- Bootstrap -->
	<script src="js/bootstrap.min.js"></script>
	<!-- Waypoints -->
	<script src="js/jquery.waypoints.min.js"></script>
	<!-- Carousel -->
	<script src="js/owl.carousel.min.js"></script>
	<!-- countTo -->
	<script src="js/jquery.countTo.js"></script>

	<!-- Stellar Parallax -->
	<script src="js/jquery.stellar.min.js"></script>

	<!-- Magnific Popup -->
	<script src="js/jquery.magnific-popup.min.js"></script>
	<script src="js/magnific-popup-options.js"></script>
	
	<script src="js/moment.min.js"></script>
	<script src="js/bootstrap-datetimepicker.min.js"></script>


	<!-- Main -->
	<script src="js/main.js"></script>
    </body>
</html>

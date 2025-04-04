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
	
	<link rel="stylesheet" href="css/animate.css">
	<link rel="stylesheet" href="css/icomoon.css">
	<link rel="stylesheet" href="css/themify-icons.css">
	<link rel="stylesheet" href="css/bootstrap.css">

	<link rel="stylesheet" href="css/magnific-popup.css">

	<link rel="stylesheet" href="css/bootstrap-datetimepicker.min.css">

	<link rel="stylesheet" href="css/owl.carousel.min.css">
	<link rel="stylesheet" href="css/owl.theme.default.min.css">

	<link rel="stylesheet" href="css/style.css">

	<script src="js/modernizr-2.6.2.min.js"></script>
	<script src="script.js"></script>
	

	</head>
	<body>
		
	<div class="gtco-loader"></div>
	
	<div id="page">

	
		<nav class="gtco-nav" role="navigation">
			<div class="gtco-container">
				
				<div class="row" style="display: flex;">
					<div class="col-sm-4 col-xs-12" style="flex: 1;">
						<div id="gtco-logo" style="padding-top: 13px;"><a href="index.html">SaveurHub <em>.</em></a></div>
					</div>
	
					<div class="col-sm-4 col-xs-12" style="flex: 2;">
						<form class="navbar-form" role="search" action="search.php" method="GET" style="display: flex;">	
							<input type="text" class="form-control" name="query" placeholder="Rechercher une recette..." style="flex-grow: 1; padding: 2px; font-size: 16px; width: 100%;">
							<button class="btn-cta" type="submit" style="padding: 1px 1px; font-size: 14px;">Rechercher</button>
						</form>
					</div>
	
		
					<div class="col-xs-8 text-right menu-1" style="flex: 1;"  >
						<ul style="padding-top: 13px;">
							<li><a href="menu.html">Menu</a></li>
							<li><a href="contact.html">Contact</a></li>
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
										<li><a href="connexion.php">Profil</a></li>
										<li><a href="inscription.php">Se deconnecter</a></li>
									</ul>
								<?php endif; ?>

							</li>
							
						</ul>
					</div>
				</div>
				
			</div>
		</nav>
	
	<header id="gtco-header" class="gtco-cover gtco-cover-md" role="banner" style="background-image: url(images/img_bg_1.jpg)" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="gtco-container">
			<div class="row">
				<div class="col-md-12 col-md-offset-0 text-left">
					

					<div class="row row-mt-15em">
						<div class="col-md-7 mt-text animate-box" data-animate-effect="fadeInUp">
							<span class="intro-text-small">Bienvenue sur  <a href="http://midou.alwaysdata.net" target="_blank">SaveurHub</a></span>
							<h1 class="cursive-font">Explorez, cuisinez, régalez-vous !</h1>	
						
					</div>
							
					
				</div>
			</div>
		</div>
	</header>

	
	
	<div class="gtco-section">
		<div class="gtco-container">
			<div class="row">
				<div class="col-md-8 col-md-offset-2 text-center gtco-heading">
					<h2 class="cursive-font primary-color">Nos Recettes</h2>
					<p>Envie d'inspiration en cuisine ? Parcourez nos recettes savoureuses et faciles à réaliser, adaptées à toutes les envies !</p>
				</div>
			</div>



		<div class="row" id="midou">
    		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   		    <script>
     			$(document).ready(function() {
            	 var langue_ = 'anglais';
           		 $.ajax({
                url: 'traitement.php',
                method: 'POST',
                data : {
                    langue : langue_
                },
                success: function(response) {
                    $('#midou').html(response);
                },
                error: function() {
                    $('#midou').html('<p>Erreur lors du chargement des recettes.</p>');
                }
           		 });
      			  });

   			 </script>



			 
			
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
	
	<script src="js/jquery.min.js"></script>
	<script src="js/jquery.easing.1.3.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/jquery.waypoints.min.js"></script>
	<script src="js/owl.carousel.min.js"></script>
	<script src="js/jquery.countTo.js"></script>
	<script src="js/jquery.stellar.min.js"></script>
	<script src="js/jquery.magnific-popup.min.js"></script>
	<script src="js/magnific-popup-options.js"></script>
	<script src="js/moment.min.js"></script>
	<script src="js/bootstrap-datetimepicker.min.js"></script>


	<script src="js/main.js"></script>

	</body>
</html>


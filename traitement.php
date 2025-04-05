
<?php
$recipes = json_decode(file_get_contents('recipes.json'), true);
$langue = isset($_POST['langue']) ? $_POST['langue'] : 'anglais';
foreach ($recipes as $recipe) {
    $name="";
    $author="";
    $image="";
    $steps="";
    $ingredients="";
    
    if($langue === 'francais' && isset($recipe['nameFR']) && isset($recipe['ingredientsFR']) && isset($recipe['stepsFR']))
    {

        if (isset($recipe['imageURL']))
            $image=$recipe['imageURL'];
        else 
            $image="images/img_6.jpg";

        $name = $recipe['nameFR'];

        if (isset($recipe['Author']))
            $author=$recipe['Author'];
        else
            $author='Auteur inconnu';
        if(is_array($recipe['stepsFR']))
            $steps=implode("| ", $recipe['stepsFR']);
        else
            $steps="";
        if(is_array($recipe['ingredientsFR']))
        {
            foreach ($recipe['ingredientsFR'] as $ingredient) {
                $ingredients.="  ". $ingredient['quantity'] . ' ' . $ingredient['name'] .' ' . $ingredient['type'];
            }
        }
        else    
            $ingredients="";


            echo '    <div class="col-lg-4 col-md-4 col-sm-6">';
            echo '<a href="recette.php?name=' . urlencode($name) . '&lang=' . $langue . '" class="fh5co-card-item">';
        
            echo '<figure><div class="overlay"><i class="ti-plus"></i></div>';
            echo '<img src="' . $image . '" alt="Image" class="img-responsive" width="400" height="300">';
            echo '</figure>';
        
            echo '<div class="fh5co-text">';
            echo '<h2 style="font-size: 20px;">' . $name . '</h2>';
            echo '<p><strong>Auteur :</strong> ' . $author . '</p>';
            echo '<p><span class="price cursive-font">♡</span></p>';
            echo '</div></a></div>';
        


    }
    elseif ($langue === 'anglais' && isset($recipe['name'])&& isset($recipe['ingredients']) && isset($recipe['steps']))
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
                $ingredients.="  ". $ingredient['quantity'] . ' ' . $ingredient['name'] .' ' . $ingredient['type'];
            }
        }
        else    
            $ingredients="";


            echo '    <div class="col-lg-4 col-md-4 col-sm-6">';
            echo '<a href="recette.php?name=' . urlencode($name) . '&lang=' . $langue . '" class="fh5co-card-item">';
        
            echo '<figure><div class="overlay"><i class="ti-plus"></i></div>';
            echo '<img src="' . $image . '" alt="Image" class="img-responsive" width="400" height="300">';
            echo '</figure>';
        
            echo '<div class="fh5co-text">';
            echo '<h2 style="font-size: 20px;">' . $name . '</h2>';
            echo '<p><strong>Auteur :</strong> ' . $author . '</p>';
            echo '<p><span class="price cursive-font">♡</span></p>';
            echo '</div></a></div>';
    }


    
   
     
}
?>









document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".recipe-popup").forEach(link => {
        link.addEventListener("click", function(event) {
            event.preventDefault();

            // Récupérer les données de la recette
            let title = this.getAttribute("data-name");
            let image = this.getAttribute("data-image");
            let ingredients = this.getAttribute("data-ingredients").split(", ");
            let steps = this.getAttribute("data-steps").split("|");
            let author = this.getAttribute("data-author");

            // Mettre à jour la pop-up
            document.getElementById("modal-title").textContent = title;
            document.getElementById("modal-image").src = image;
            document.getElementById("modal-author").textContent = author;

            // Afficher les ingrédients
            let ingredientsList = document.getElementById("modal-ingredients");
            ingredientsList.innerHTML = "";
            ingredients.forEach(ing => {
                let li = document.createElement("li");
                li.textContent = ing;
                ingredientsList.appendChild(li);
            });

            // Afficher les étapes
            let stepsList = document.getElementById("modal-steps");
            stepsList.innerHTML = "";
            steps.forEach(step => {
                let li = document.createElement("li");
                li.textContent = step;
                stepsList.appendChild(li);
            });

            // Afficher la pop-up
            document.getElementById("recipe-modal").style.display = "flex";
        });
    });

    // Fermer la pop-up
    document.querySelector(".close-btn").addEventListener("click", function() {
        document.getElementById("recipe-modal").style.display = "none";
    });

    // Fermer la pop-up si on clique en dehors
    document.getElementById("recipe-modal").addEventListener("click", function(event) {
        if (event.target === this) {
            this.style.display = "none";
        }
    });
});


//chargement des recetttes
function chargerRecettes() {
    fetch('api/recipes') 
      .then(response => response.json())
      .then(data => {
        const container = document.getElementById('midou');
        container.innerHTML = ''; 
  
        data.forEach(recette => {
          const col = document.createElement('div');
          col.className = 'col-lg-4 col-md-4 col-sm-6';
  
          col.innerHTML = `
            <a href="recette.php?name=${encodeURIComponent(recette.nameFR)}" class="fh5co-card-item">
                <figure>
                <div class="overlay"><i class="ti-plus"></i></div>
                <img src="${recette.imageURL}" alt="Image" class="img-responsive" width="400" height="300">
                </figure>
                <div class="fh5co-text">
                <h2 style="font-size: 20px;">${recette.nameFR}</h2>
                <p><strong>Auteur :</strong> ${recette.Author}</p>
                <p><span class="price cursive-font">♡</span></p>
                </div>
            </a>
            `;

  
          container.appendChild(col);
        });
      })
      .catch(error => console.error('Erreur lors du chargement des recettes :', error));
  }
  
  window.addEventListener('DOMContentLoaded', chargerRecettes);
  
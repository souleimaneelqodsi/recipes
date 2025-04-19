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
            <a href="recette.html?id=${recette.id}" class="fh5co-card-item">
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
  



  //affichage d'une recette en détailles
  document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const recetteId = params.get('id');
  
    const container = document.getElementById('recipe-container');
    const btnAnglais = document.getElementById('btn-anglais');
  
    if (!recetteId) {
      container.innerText = 'Aucune recette sélectionnée.';
      btnAnglais.disabled = true;
      return;
    }
  
    fetch(`api/recipes/${recetteId}`)
      .then(response => {
        if (!response.ok) {
          throw new Error('Recette non trouvée');
        }
        return response.json();
      })
      .then(recette => {
        // Recette en français
        afficherRecetteFR(recette);
  
        if (!recette.steps || recette.steps.length === 0) {
          btnAnglais.disabled = true;
        } else {
          btnAnglais.disabled = false;
          btnAnglais.addEventListener('click', () => {
            afficherRecetteEN(recette);
          });
        }
      })
      .catch(error => {
        container.innerText = 'Erreur : ' + error.message;
        btnAnglais.disabled = true;
      });
  
      function afficherRecetteFR(recette) {
        container.innerHTML = `
          <div class="recipe-card">
            <div class="recipe-image">
              <img src="${recette.imageURL}" alt="Image de ${recette.nameFR}">
            </div>
            <div class="recipe-content">
              <h2>
                ${recette.nameFR}
                <span class="heart-icon"><i class="fa-solid fa-heart"></i></span>
              </h2>
              <p><strong>Auteur:</strong> ${recette.Author}</p>
      
              <h3>Ingrédients:</h3>
              <ul>
                ${recette.ingredientsFR.map(ing => `<li>${ing.quantity} ${ing.name} ${ing.type}</li>`).join('')}
              </ul>
      
              <h3>Etapes:</h3>
              <ol>
                ${recette.etapesFR.map(step => `<li>${step}</li>`).join('')}
              </ol>
            </div>
          </div>
        `;
      }
      
  
      function afficherRecetteEN(recette) {
        container.innerHTML = `
          <div class="recipe-card">
            <div class="recipe-image">
              <img src="${recette.imageURL}" alt="Image of ${recette.name}">
            </div>
            <div class="recipe-content">
              <h2>
                ${recette.nameEN}
                <span class="heart-icon"><i class="fa-solid fa-heart"></i></span>
              </h2>
              <p><strong>Author:</strong> ${recette.Author}</p>
      
              <h3>Ingredients:</h3>
              <ul>
                ${recette.ingredients.map(ing => `<li>${ing.quantity} ${ing.name} ${ing.type}</li>`).join('')}
              </ul>
      
              <h3>Steps:</h3>
              <ol>
                ${recette.etapesEN.map(step => `<li>${step}</li>`).join('')}
              </ol>
            </div>
          </div>
        `;
      }
      
  });
  
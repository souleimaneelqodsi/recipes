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

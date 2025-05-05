//chargement des recetttes

// script.js

document.addEventListener("DOMContentLoaded", () => {
    const recipesContainer = document.getElementById("midou");

    fetchRecipes();

    function fetchRecipes() {
        fetch("/recipes/api/recipes/published")
            .then((response) => {
                // Log de la réponse pour analyser le contenu
                console.log("Réponse de l'API:", response);

                // Vérifier si la réponse est vide ou n'a pas de corps JSON
                if (response.status === 204) {
                    console.log("Aucune recette disponible (204)");
                    return []; // Retourner un tableau vide
                }

                // Si la réponse est valide (200), traiter le JSON
                if (response.status === 200) {
                    return response
                        .text() // Lire la réponse comme texte brut
                        .then((text) => {
                            // Si la réponse contient du texte, essayer de la parser
                            try {
                                const data = text ? JSON.parse(text) : []; // Essayer de parser le texte si ce n'est pas vide
                                return data;
                            } catch (e) {
                                throw new Error("Erreur de parsing JSON");
                            }
                        });
                } else {
                    // Si statut différent, traiter comme une erreur
                    throw new Error("Erreur inconnue: " + response.status);
                }
            })
            .then((data) => {
                displayRecipes(data);
            })
            .catch((error) => {
                console.error(
                    "Erreur lors de la récupération des recettes :",
                    error,
                );
                recipesContainer.innerHTML = `<p style="color:red;">${error.message}</p>`;
            });
    }

    function displayRecipes(recipes) {
        recipesContainer.innerHTML = ""; // On vide

        if (!Array.isArray(recipes) || recipes.length === 0) {
            recipesContainer.innerHTML = "<p>Aucune recette trouvée.</p>";
            return;
        }

        recipes.forEach((recipe) => {
            const col = document.createElement("div");
            col.className = "col-lg-4 col-md-4 col-sm-6";

            col.innerHTML = `
                <a href="recette.html?id=${recipe.id}" class="fh5co-card-item">
                    <figure>
                        <div class="overlay"><i class="ti-plus"></i></div>
                        <img src="${recipe.imageURL || "https://via.placeholder.com/400x300?text=Pas+de+photo"}" alt="Image" class="img-responsive" width="400" height="300">
                    </figure>
                    <div class="fh5co-text">
                        <h2 style="font-size: 20px;">${recipe.nameFR || recipe.name || "Nom non disponible"}</h2>
                        <p><strong>Auteur :</strong> ${recipe.Author || "Inconnu"}</p>
                        <p><span class="price cursive-font">♡</span></p>
                    </div>
                </a>
            `;

            recipesContainer.appendChild(col);
        });
    }
});

/*
document.addEventListener("DOMContentLoaded", () => {
    const recipesContainer = document.getElementById("midou");

    fetchRecipes();

    function fetchRecipes() {
        fetch('/recipes/api/recipes')
            .then(response => {
                if (response.status === 200) {
                    return response.json();
                } else if (response.status === 500) {
                    throw new Error('Erreur serveur interne (500)');
                } else {
                    throw new Error('Erreur inconnue: ' + response.status);
                }
            })
            .then(data => {
                displayRecipes(data);
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des recettes :', error);
                recipesContainer.innerHTML = `<p style="color:red;">${error.message}</p>`;
            });
    }

    function displayRecipes(recipes) {
        recipesContainer.innerHTML = ''; // On vide

        if (!Array.isArray(recipes) || recipes.length === 0) {
            recipesContainer.innerHTML = '<p>Aucune recette trouvée.</p>';
            return;
        }

        recipes.forEach(recipe => {
            const col = document.createElement('div');
            col.className = 'col-lg-4 col-md-4 col-sm-6';

            col.innerHTML = `
                <a href="recette.html?id=${recipe.id}" class="fh5co-card-item">
                    <figure>
                        <div class="overlay"><i class="ti-plus"></i></div>
                        <img src="${recipe.imageURL || 'https://via.placeholder.com/400x300?text=Pas+de+photo'}" alt="Image" class="img-responsive" width="400" height="300">
                    </figure>
                    <div class="fh5co-text">
                        <h2 style="font-size: 20px;">${recipe.nameFR || recipe.name || "Nom non disponible"}</h2>
                        <p><strong>Auteur :</strong> ${recipe.Author || "Inconnu"}</p>
                        <p><span class="price cursive-font">♡</span></p>
                    </div>
                </a>
            `;

            recipesContainer.appendChild(col);
        });
    }
});

*/

//affichzge detaillé d'un recette

document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    const recetteId = params.get("id");

    const container = document.getElementById("recipe-container");
    const btnAnglais = document.getElementById("btn-anglais");

    if (!recetteId) {
        container.innerText = "Aucune recette sélectionnée.";
        btnAnglais.disabled = true;
        return;
    }

    fetch(`/recipes/api/recipes/${recetteId}`) // Remplacer par l'URL réelle
        .then((response) => {
            if (response.status === 200) {
                return response.json();
            } else if (response.status === 400) {
                throw new Error("ID de recette invalide (400)");
            } else if (response.status === 404) {
                throw new Error("Recette non trouvée (404)");
            } else if (response.status === 500) {
                throw new Error("Erreur serveur interne (500)");
            } else {
                throw new Error("Erreur inconnue: " + response.status);
            }
        })
        .then((recette) => {
            afficherRecetteFR(recette);

            // Gestion du bouton anglais
            if (!recette.steps || recette.steps.length === 0) {
                btnAnglais.disabled = true;
            } else {
                btnAnglais.disabled = false;
                btnAnglais.addEventListener("click", () => {
                    afficherRecetteEN(recette);
                });
            }
        })
        .catch((error) => {
            container.innerHTML = `<p style="color:red;">Erreur : ${error.message}</p>`;
            btnAnglais.disabled = true;
        });

    function afficherRecetteFR(recette) {
        container.innerHTML = `
            <div class="recipe-card">
                <div class="recipe-image">
                    <img src="${recette.imageURL || "https://via.placeholder.com/400x300?text=Pas+de+photo"}" alt="Image de ${recette.nameFR || recette.name || "Nom indisponible"}">
                </div>
                <div class="recipe-content">
                    <h2>
                        ${recette.nameFR || recette.name || "Nom non disponible"}
                        <span class="heart-icon"><i class="fa-solid fa-heart"></i></span>
                    </h2>
                    <p><strong>Auteur:</strong> ${recette.Author || "Inconnu"}</p>

                    <h3>Ingrédients:</h3>
                    <ul>
                        ${(recette.ingredientsFR || []).map((ing) => `<li>${ing.quantity} ${ing.name} ${ing.type}</li>`).join("") || "<li>Aucun ingrédient trouvé.</li>"}
                    </ul>

                    <h3>Étapes:</h3>
                    <ol>
                        ${(recette.stepsFR || []).map((step) => `<li>${step}</li>`).join("") || "<li>Aucune étape trouvée.</li>"}
                    </ol>
                </div>
            </div>
        `;
    }

    function afficherRecetteEN(recette) {
        container.innerHTML = `
            <div class="recipe-card">
                <div class="recipe-image">
                    <img src="${recette.imageURL || "https://via.placeholder.com/400x300?text=No+Image"}" alt="Image of ${recette.name || "Name unavailable"}">
                </div>
                <div class="recipe-content">
                    <h2>
                        ${recette.name || "Name unavailable"}
                        <span class="heart-icon"><i class="fa-solid fa-heart"></i></span>
                    </h2>
                    <p><strong>Author:</strong> ${recette.Author || "Unknown"}</p>

                    <h3>Ingredients:</h3>
                    <ul>
                        ${(recette.ingredients || []).map((ing) => `<li>${ing.quantity} ${ing.name} ${ing.type}</li>`).join("") || "<li>No ingredients found.</li>"}
                    </ul>

                    <h3>Steps:</h3>
                    <ol>
                        ${(recette.steps || []).map((step) => `<li>${step}</li>`).join("") || "<li>No steps found.</li>"}
                    </ol>
                </div>
            </div>
        `;
    }
});

//recherche de recette

document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("searchInput");
    const btnRechercher = document.getElementById("btn-rechercher");

    function redirigerVersRecherche() {
        const motCle = searchInput.value.trim();
        if (motCle !== "") {
            window.location.href = `rechercher_recette.html?search=${encodeURIComponent(motCle)}`;
        } else {
            alert("Veuillez entrer un mot-clé");
        }
    }

    if (btnRechercher) {
        btnRechercher.addEventListener("click", redirigerVersRecherche);
    }
});

// Partie 2 : Affichage des résultats sur la page rechercher_recette.html

document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    const motCle = params.get("search");
    const container = document.getElementById("midou");

    if (!motCle) {
        container.innerHTML =
            "<p>Veuillez entrer un mot-clé dans la barre de recherche.</p>";
        return;
    }

    fetch(`/recipes/api/recipes?search=${encodeURIComponent(motCle)}`)
        .then((response) => {
            if (response.status === 204) {
                // Aucun contenu trouvé
                container.innerHTML = `<p>Aucune recette trouvée pour "${motCle}".</p>`;
                throw new Error("Aucun résultat"); // On arrête ici volontairement
            }
            if (!response.ok) {
                throw new Error("Erreur de récupération des recettes.");
            }
            return response.json();
        })
        .then((data) => {
            container.innerHTML = `<p>${data.length} résultat(s) trouvé(s) pour "${motCle}".</p>`;

            data.forEach((recetteId) => {
                fetch(`/recipes/api/recipes/${recetteId}`)
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(
                                "Erreur de récupération de la recette.",
                            );
                        }
                        return response.json();
                    })
                    .then((recette) => {
                        const col = document.createElement("div");
                        col.className = "col-lg-4 col-md-4 col-sm-6";

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
                    })
                    .catch((error) => {
                        console.error(
                            "Erreur lors du chargement de la recette :",
                            error.message,
                        );
                    });
            });
        })
        .catch((error) => {
            // On n'affiche pas un message d'erreur si c'était juste "aucun résultat"
            if (error.message !== "Aucun résultat") {
                console.error(
                    "Erreur lors du chargement des résultats :",
                    error.message,
                );
                container.innerHTML =
                    "<p>Erreur de chargement des résultats.</p>";
            }
        });
});

//page inscription

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("form-inscription");

    form.addEventListener("submit", function (e) {
        e.preventDefault(); // Empêche l'envoi classique du formulaire

        const username = document.getElementById("username").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value;
        const confirmPassword =
            document.getElementById("confirm_password").value;
        const messageContainer = document.getElementById("message");
        alert("misou");

        messageContainer.innerHTML = "";

        if (!username || !email || !password || !confirmPassword) {
            messageContainer.innerHTML =
                '<p style="color:red;">Veuillez remplir tous les champs.</p>';
            return;
        }

        if (password !== confirmPassword) {
            messageContainer.innerHTML =
                '<p style="color:red;">Les mots de passe ne correspondent pas.</p>';
            return;
        }

        const payload = { username, email, password };

        fetch("/recipes/api/auth/register", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(payload),
        })
            .then((response) => {
                console.log("Payload envoyé:", payload);
                console.log(response);
                if (response.status === 201) {
                    return response.json();
                } else if (response.status === 400) {
                    throw new Error("Champs manquants ou invalides.");
                } else if (response.status === 409) {
                    throw new Error(
                        "Nom d’utilisateur ou e-mail déjà utilisé.",
                    );
                } else {
                    throw new Error(
                        "Erreur serveur. Veuillez réessayer plus tard.",
                    );
                }
            })
            .then((user) => {
                // Succès : on stocke l'état connecté
                localStorage.setItem("estConnecte", "true");
                localStorage.setItem("utilisateur", JSON.stringify(user));
                messageContainer.innerHTML =
                    '<p style="color:green;">Inscription réussie. Redirection...</p>';
                // Redirection après un petit délai
                setTimeout(() => {
                    window.location.href = "index.html";
                }, 1500);
            })
            .catch((error) => {
                messageContainer.innerHTML = `<p style="color:red;">${error.message}</p>`;
            });
    });
});

//affichage de nom utilisateur lors de la connexion

document.addEventListener("DOMContentLoaded", () => {
    const estConnecte = localStorage.getItem("estConnecte") === "true";
    const menuConnexion = document.getElementById("cont-nom");

    if (estConnecte && menuConnexion) {
        const utilisateur = JSON.parse(localStorage.getItem("utilisateur"));
        menuConnexion.innerHTML = `

        <li class="has-dropdown" id="cont-nom">
                                    <a href="services.html">${utilisateur.username}</a>
                                    <ul class="dropdown">
                                        <li><a href="profil.html" id="profil">Mon Profil</a></li>
                                        <li><a href="deconnexion.html" id="lien-deconnexion">Se deconnecter</a></li>
                                    </ul>
                                </li>
      `;
    }
});

//deconnexion d'un utilisateur
//je ne recois aucune reponse de l'api
document.addEventListener("DOMContentLoaded", () => {
    const lienDeconnexion = document.getElementById("lien-deconnexion");

    if (lienDeconnexion) {
        lienDeconnexion.addEventListener("click", (e) => {
            e.preventDefault();

            fetch("/recipes/api/auth/logout", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
            })
                .then((response) => {
                    // Don't consume response body here
                    console.log("Response status:", response.status);

                    if (response.status === 200) {
                        return response.json();
                    } else if (response.status === 401) {
                        throw new Error("Vous n'êtes pas connecté.");
                    } else if (response.status === 404) {
                        throw new Error("Endpoint non trouvé.");
                    } else if (response.status === 500) {
                        throw new Error("Erreur interne du serveur.");
                    } else {
                        throw new Error("Erreur inconnue : " + response.status);
                    }
                })
                .then((data) => {
                    console.log("Logout successful:", data);
                    localStorage.setItem("estConnecte", "false");
                    localStorage.removeItem("utilisateur");
                    window.location.href = "index.html";
                })
                .catch((error) => {
                    console.error(
                        "Erreur lors de la déconnexion:",
                        error.message,
                    );
                    // Still logout locally even if server logout fails
                    localStorage.setItem("estConnecte", "false");
                    localStorage.removeItem("utilisateur");
                    window.location.href = "index.html";
                });
        });
    }
});


//la page connexion

document.addEventListener('DOMContentLoaded', () => {
  const formConnexion = document.getElementById('form-connexion');

  if (formConnexion) {
    formConnexion.addEventListener('submit', (e) => {
      e.preventDefault(); 

      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value;

      // Vérifie si les champs sont remplis
      if (!username || !password) {
        alert('Veuillez remplir tous les champs.');
        return;
      }

      const payload = { username, password };

      fetch('/recipes/api/auth/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      })
      .then(response => {
        if (response.status === 200) {
          return response.json();
        } else if (response.status === 400) {
          throw new Error("Champs manquants.");
        } else if (response.status === 401) {
          throw new Error("Nom d'utilisateur ou mot de passe incorrect.");
        } else {
          throw new Error("Erreur serveur.");
        }
      })
      .then(data => {
        console.log('Connexion réussie :', data);
        localStorage.setItem('estConnecte', 'true');
        localStorage.setItem('utilisateur', JSON.stringify(data));
        window.location.href = 'index.html';
      })
      .catch(error => {
        console.error('Erreur de connexion :', error.message);
        alert(error.message);
      });
    });
  }
});

//la page profil

document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.includes('profil.html')) {
      const utilisateurJSON = localStorage.getItem('utilisateur');
      const estConnecte = localStorage.getItem('estConnecte');
  
      if (!estConnecte || estConnecte !== 'true' || !utilisateurJSON) {
        alert("Vous devez être connecté pour accéder à votre profil.");
        window.location.href = 'connexion.html';
        return;
      }
  
      const utilisateur = JSON.parse(utilisateurJSON);
      const profilContainer = document.getElementById('profil-container');
  
      profilContainer.innerHTML = `
        <div class="profil-card">
          <h2>Mon Profil</h2>
          <p><strong>Nom d'utilisateur :</strong> ${utilisateur.username || 'Inconnu'}</p>
          <p><strong>Email :</strong> ${utilisateur.email || 'Inconnu'}</p>
          <p><strong>Rôle :</strong> ${utilisateur.role || 'Utilisateur'}</p>
        </div>
      `;
    }
  });
  



/*

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



  //recherche de recettes
  // Redirection vers la page de recherche
function redirigerVersRecherche() {
    const motCle = document.getElementById('searchInput').value.trim();
    if (motCle !== '') {
        window.location.href = `rechercher_recette.html?query=${encodeURIComponent(motCle)}`;
    } else {
        alert('Veuillez entrer un mot-clé');
    }
}

// Chargement des recettes à partir de la recherche
function chargerRecettesRecherche() {
    const params = new URLSearchParams(window.location.search);
    const motCle = params.get('query');

    const container = document.getElementById('midou');

    if (!motCle) {
        container.innerHTML = '<p>Pas de mot-clé fourni.</p>';
        return;
    }

    fetch(`URL_DE_TON_API/recipes?search=${encodeURIComponent(motCle)}`) // Remplacer par ton vrai chemin API
        .then(response => {
            if (response.status === 200) {
                return response.json();
            } else if (response.status === 204) {
                throw new Error('Aucune recette trouvée (204)');
            } else if (response.status === 400) {
                throw new Error('Recherche invalide : terme manquant (400)');
            } else if (response.status === 500) {
                throw new Error('Erreur serveur (500)');
            } else {
                throw new Error('Erreur inconnue : ' + response.status);
            }
        })
        .then(ids => {
            container.innerHTML = '';

            if (ids.length === 0) {
                container.innerHTML = `<p>Aucune recette trouvée pour "${motCle}".</p>`;
                return;
            }

            // Pour chaque ID, charger la recette
            ids.forEach(id => {
                fetch(`URL_DE_TON_API/recipes/${id}`) // Encore une fois, corrige avec ton vrai chemin
                    .then(response => {
                        if (response.status === 200) {
                            return response.json();
                        } else {
                            throw new Error('Erreur lors du chargement d\'une recette : ' + response.status);
                        }
                    })
                    .then(recette => {
                        const col = document.createElement('div');
                        col.className = 'col-lg-4 col-md-4 col-sm-6';

                        col.innerHTML = `
                            <a href="recette.html?id=${recette.id}" class="fh5co-card-item">
                                <figure>
                                    <div class="overlay"><i class="ti-plus"></i></div>
                                    <img src="${recette.imageURL || 'https://via.placeholder.com/400x300?text=No+Image'}" alt="Image" class="img-responsive" width="400" height="300">
                                </figure>
                                <div class="fh5co-text">
                                    <h2 style="font-size: 20px;">${recette.nameFR || recette.name || "Nom inconnu"}</h2>
                                    <p><strong>Auteur :</strong> ${recette.Author || "Inconnu"}</p>
                                    <p><span class="price cursive-font">♡</span></p>
                                </div>
                            </a>
                        `;

                        container.appendChild(col);
                    })
                    .catch(error => {
                        console.error('Erreur en récupérant la recette:', error.message);
                    });
            });
        })
        .catch(error => {
            container.innerHTML = `<p style="color:red;">Erreur : ${error.message}</p>`;
            console.error('Erreur lors de la recherche :', error.message);
        });
}


  //redirection vers la page de recherche
  function redirigerVersRecherche() {
    const motCle = document.getElementById('searchInput').value.trim();
    if (motCle !== '') {
      window.location.href = `rechercher_recette.html?query=${encodeURIComponent(motCle)}`;
    } else {
      alert('Veuillez entrer un mot-clé');
    }
  }


  //chzrgement des recettte à partir de la barre de recherche

  function chargerRecettesRecherche() {
    const params = new URLSearchParams(window.location.search);
    const motCle = params.get('query');

    if (!motCle) {
      document.getElementById('midou').innerHTML = '<p>Pas de mot-clé fourni.</p>';
      return;
    }

    fetch(`api/recipes/search?lang=fr&query=${encodeURIComponent(motCle)}`)
      .then(response => response.json())
      .then(data => {
        const container = document.getElementById('midou');
        container.innerHTML = '';

        if (data.length === 0) {
          container.innerHTML = `<p>Aucune recette trouvée pour "${motCle}".</p>`;
          return;
        }

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
      .catch(error => {
        console.error('Erreur lors du chargement des résultats :', error);
      });
  }


  //connexion

  document.getElementById("connexion").addEventListener("submit", async function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    try {
      const response = await fetch("api/auth/login", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          email: email,
          password: password
        })
      });

      if (!response.ok) {
        throw new Error("Email ou mot de passe incorrect");
      }

      const data = await response.json();

      // Stocker le token + infos user
      localStorage.setItem("token", data.token);
      localStorage.setItem("user", JSON.stringify(data.user));

      document.getElementById("message").textContent = "Connexion réussie !";
      window.location.href = "index.html";
    } catch (error) {
      document.getElementById("message").textContent = error.message;
    }
  });


  //inscription

  const registerForm = document.getElementById("inscription");
 registerForm.addEventListener("submit", async function (e) {
  e.preventDefault();
  const username = document.getElementById("username").value;
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;
  const confirm_password = document.getElementById("confirm_password").value;

  const role = "Cuisinier"; // Rôle par défaut

  if (password !== confirm_password) {
    document.getElementById("message").textContent = "❌ Les mots de passe ne correspondent pas.";
    return;
  }

  try {
    const response = await fetch("api/auth/login", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        username,
        email,
        password,
        confirm_password,
        role
      })
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || "❌ Une erreur est survenue.");
    }

    const data = await response.json();
    localStorage.setItem("token", data.token);
    localStorage.setItem("user", JSON.stringify(data.user));

    window.location.href = "index.html"; // Redirection après inscription
  } catch (error) {
    document.getElementById("message").textContent = error.message;
  }
});



//deconnexion

document.getElementById("logoutButton").addEventListener("click", async function () {
    try {
      const response = await fetch("api/auth/logout", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${localStorage.getItem("token")}`
        }
      });

      const data = await response.json();

      if (response.ok) {
        localStorage.removeItem("token");
        localStorage.removeItem("user");

        document.getElementById("message").textContent = "Déconnexion réussie ! Vous allez être redirigé vers la page d'acceuil.";

        setTimeout(function () {
          window.location.href = "index.html";
        }, 2000);
      } else {
        document.getElementById("message").textContent = `❌ Erreur : ${data.message}`;
      }
    } catch (error) {
      document.getElementById("message").textContent = "❌ Une erreur est survenue pendant la déconnexion.";
    }
  });

*/

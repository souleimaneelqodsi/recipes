# Recipe Management Web Application

[![Build Passing](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://github.com/souleimaneelqodsi/recipes/actions)
[![Version: 1.0](https://img.shields.io/badge/version-1.0-blue.svg)](https://opensource.org/licenses/MIT)
[![License: MIT](https://img.shields.io/badge/License-MIT-orange.svg)](https://opensource.org/licenses/MIT)

![Tech Stack](https://skillicons.dev/icons?i=html,css,js,php)

## Overview
This web application enables users to manage cooking recipes in French and English using JSON files stored in a `data/` folder. It supports multiple user roles—cooks, chefs, translators, and administrators—each with distinct permissions to create, edit, translate, and oversee recipes. The platform offers a modern, responsive interface with features like searching, filtering, commenting, and photo uploads.

## Purpose
The goal is to provide an intuitive tool for recipe management, fostering collaboration across roles while ensuring a seamless bilingual experience, all without requiring a database setup.

## Installation and Running Instructions (using XAMPP)

Follow these steps to set up and run the project locally using XAMPP:

1.  **Prerequisites:**
    * Ensure you have [XAMPP](https://www.apachefriends.org/index.html) installed on your system. We only need the Apache and PHP components for this project.
    * Ensure you have [Git](https://git-scm.com/) installed (optional, if you prefer cloning over downloading).

2.  **Get the Project Code:**
    * **Option A (Git Clone):** Open your terminal or command prompt and clone the repository:
        ```bash
        git clone https://github.com/souleimaneelqodsi/recipes.git
        ```
        *(This command clones the repository directly into a folder named `recipes`.)*
    * **Option B (Download ZIP):** Download the project ZIP file from `https://github.com/souleimaneelqodsi/recipes`. Extract it, and ensure the resulting project folder is named `recipes`.

3.  **Place Project in XAMPP:**
    * Navigate to your XAMPP installation directory. Inside, find the `htdocs` folder (e.g., `C:\xampp\htdocs` on Windows, `/Applications/XAMPP/htdocs` on macOS).
    * Move or copy the project folder, ensuring it is named `recipes`, directly into the `htdocs` directory. The final path should look like `htdocs/recipes/`.

4.  **Start XAMPP Services:**
    * Open the XAMPP Control Panel.
    * Start the **Apache Web Server** module.

5.  **Check File Permissions:**
    * The application needs to read and write data to JSON files located within the `recipes/api/data/` folder.
    * Standard XAMPP installations usually allow this by default. If you encounter errors when saving recipes, comments, or user data, you might need to ensure the web server (Apache/PHP) has write permissions specifically for the `htdocs/recipes/api/data/` directory.

6.  **Access the Application:**
    * Open your web browser and navigate *specifically* to:
        `http://localhost/recipes/index.html`

You should now see the entry page of the Recipe Management Web Application! All data will be read from and saved to JSON files within the `recipes/api/data/` directory.

## Key Features
- **User Roles**: Register/login as Cook, Chef, Translator, or Admin with role-specific access.
- **Recipe Management**: Create, edit, and translate recipes (French/English).
- **Interaction**: Comment, upload photos, and "like" recipes.
- **Search & Filter**: Browse recipes by ingredients, dietary preferences, or status.
- **Admin Tools**: Validate recipes and manage users.
- **Data Storage**: Uses JSON files within a `data/` folder for storing recipes, users, comments, etc. (No database required).

## License
This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).

![SpongeBob cooking](https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExN3RtdG02c3hxcHVhdzVmMmF2aGVoY3R2OHBsZDU4b3c4cjBqdTJpeiZlcD12MV9naWZzX3NlYXJjaCZjdD1n/N23cG6apipMmQ/giphy.gif)

# 🎯 Quiz App - Plateforme de Quiz Multi-Utilisateurs

Application web de quiz interactive développée en PHP avec MySQL. Système de gestion complet avec deux types de comptes : administrateur et utilisateur.

## 🚀 Aperçu rapide

Plateforme permettant aux administrateurs de créer et gérer des quiz, et aux utilisateurs de participer, consulter leurs scores et exporter leurs résultats.

**Technologies clés** : PHP, MySQL, XAMPP, JavaScript

## ✨ Fonctionnalités

### 👨‍💼 Compte Administrateur
- ➕ **Créer des quiz** - Ajout de nouveaux quiz avec questions et réponses
- ✏️ **Modifier des quiz** - Mise à jour des questions existantes
- 🗑️ **Supprimer des quiz** - Gestion complète CRUD
- 📊 **Gérer les questions** - Ajout/modification/suppression de questions
- 👥 **Voir les statistiques** - Suivi des participations et scores

### 👤 Compte Utilisateur
- 🎮 **Choisir un quiz** - Sélection parmi les quiz disponibles
- ✅ **Participer aux quiz** - Interface interactive de réponse
- 📈 **Consulter les scores** - Historique personnel des résultats
- 📊 **Statistiques détaillées** - Taux de réussite, progression
- 💾 **Export de résultats** - Téléchargement des scores en fichier

## 🛠️ Stack technique

| Composant | Technologie |
|-----------|-------------|
| **Backend** | PHP 8+ |
| **Base de données** | MySQL 8.0 |
| **Serveur local** | XAMPP |
| **Frontend** | HTML5, CSS3, JavaScript |
| **Sessions** | PHP Sessions |
| **Sécurité** | Prepared Statements |

## 🏗️ Structure du projet

```
QuizApp/
├── admin/
│   ├── dashboard.php           // Tableau de bord admin
│   ├── create_quiz.php         // Création de quiz
│   ├── edit_quiz.php           // Modification de quiz
│   ├── delete_quiz.php         // Suppression
│   └── manage_questions.php    // Gestion questions
│
├── user/
│   ├── dashboard.php           // Tableau de bord utilisateur
│   ├── quiz_list.php           // Liste des quiz disponibles
│   ├── take_quiz.php           // Interface de participation
│   ├── results.php             // Résultats et scores
│   ├── statistics.php          // Statistiques personnelles
│   └── export_results.php      // Export des données
│
├── includes/
│   ├── db_connect.php          // Connexion base de données
│   ├── functions.php           // Fonctions utilitaires
│   └── header.php / footer.php // Templates
│
├── auth/
│   ├── login.php               // Connexion
│   ├── register.php            // Inscription
│   └── logout.php              // Déconnexion
│
└── assets/
    ├── css/
    ├── js/
    └── exports/                 // Fichiers exportés
```

## 📊 Modèle de base de données

### Table `users`
```sql
- user_id (PK)
- username (UNIQUE)
- password (HASHED)
- email
- role (admin/user)
- created_at
```

### Table `quizzes`
```sql
- quiz_id (PK)
- title
- description
- created_by (FK → users)
- created_at
- is_active
```

### Table `questions`
```sql
- question_id (PK)
- quiz_id (FK → quizzes)
- question_text
- correct_answer
- points
```

### Table `options`
```sql
- option_id (PK)
- question_id (FK → questions)
- option_text
- is_correct
```

### Table `user_results`
```sql
- result_id (PK)
- user_id (FK → users)
- quiz_id (FK → quizzes)
- score
- total_questions
- percentage
- completed_at
```

## 🚀 Installation

### Prérequis
- XAMPP (inclut Apache + MySQL + PHP)
- Navigateur web moderne

### Configuration rapide

1. **Installer XAMPP**
   - Télécharger depuis [apachefriends.org](https://www.apachefriends.org/)
   - Installer et démarrer Apache et MySQL

2. **Cloner le projet**
```bash
cd C:/xampp/htdocs/
git clone https://github.com/ssow520/QuizApp.git
```

3. **Créer la base de données**
   - Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
   - Créer une nouvelle base : `quizapp_db`
   - Importer le fichier : `database/quizapp_db.sql`

4. **Configurer la connexion**

Modifier `includes/db_connect.php` :
```php
<?php
$host = 'localhost';
$dbname = 'quizapp_db';
$username = 'root';
$password = '';  // Vide par défaut pour XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
```

5. **Accéder à l'application**
```
http://localhost/QuizApp/
```

### Comptes de test

**Administrateur :**
- Username: `admin`
- Password: `admin123`

**Utilisateur :**
- Username: `user`
- Password: `user123`

## 💡 Fonctionnalités techniques

### 1. Sécurité
```php
// Protection contre SQL Injection
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

// Hash des mots de passe
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Vérification
if (password_verify($input, $hashed)) {
    // Connexion autorisée
}
```

### 2. Gestion des sessions
```php
session_start();
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role'] = $user['role'];
```

### 3. Export de résultats
```php
// Génération CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="results.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Quiz', 'Score', 'Date']);
foreach($results as $row) {
    fputcsv($output, $row);
}
```

### 4. Calcul automatique des scores
```php
$score = 0;
foreach($answers as $question_id => $answer) {
    if($answer === $correct_answers[$question_id]) {
        $score += $points[$question_id];
    }
}
$percentage = ($score / $total_points) * 100;
```

## 🎨 Interface utilisateur

- Design responsive (mobile-friendly)
- Navigation intuitive par rôle
- Feedback visuel pour les réponses
- Graphiques de statistiques (Chart.js)
- Messages de confirmation/erreur clairs

## 🔒 Sécurité implémentée

✅ **Mots de passe hashés** - password_hash() / password_verify()  
✅ **Requêtes préparées** - Protection SQL Injection  
✅ **Validation côté serveur** - Toutes les entrées validées  
✅ **Sessions sécurisées** - session_regenerate_id()  
✅ **Protection CSRF** - Tokens pour formulaires critiques  
✅ **Contrôle d'accès** - Vérification des rôles

## 📈 Système de statistiques

### Pour les utilisateurs
- Nombre total de quiz complétés
- Score moyen
- Meilleur score
- Progression dans le temps
- Quiz réussis/échoués

### Pour les administrateurs
- Nombre total d'utilisateurs
- Quiz les plus populaires
- Taux de réussite moyen par quiz
- Activité récente

## 💾 Format d'export

**CSV (Comma-Separated Values)**
```csv
Quiz,Score,Total,Pourcentage,Date
"Quiz PHP Basics",18,20,90%,2025-04-02
"Quiz MySQL",15,20,75%,2025-04-01
```

## 🚧 Améliorations futures

- [ ] Timer pour limiter le temps des quiz
- [ ] Questions à choix multiples
- [ ] Classement (leaderboard)
- [ ] Badges et réalisations
- [ ] Mode pratique (sans enregistrement de score)
- [ ] Export PDF avec graphiques
- [ ] API REST pour intégrations

## 🧪 Tests

### Tester la création de quiz (Admin)
1. Se connecter en tant qu'admin
2. Créer un nouveau quiz avec 5 questions
3. Vérifier l'affichage côté utilisateur

### Tester la participation (User)
1. Se connecter en tant qu'utilisateur
2. Participer à un quiz
3. Vérifier l'enregistrement du score
4. Exporter les résultats

## 🤝 Compétences démontrées

✅ Développement backend PHP procédural et orienté objet  
✅ Conception et gestion de bases de données relationnelles  
✅ Architecture CRUD complète  
✅ Gestion de sessions et authentification  
✅ Sécurité web (SQL Injection, XSS, CSRF)  
✅ Génération et export de fichiers  
✅ Interface utilisateur dynamique  
✅ Séparation des rôles et permissions  


## 👨‍💻 Auteur

**Souleymane Sow**  
Développeur Full-Stack  
📧 [GitHub](https://github.com/ssow520) | 💼 [Portfolio](https://ssow520.github.io/Portfolio/)

---

*Projet développé dans le cadre de la formation en Techniques de l'informatique au Collège LaSalle Montréal - Hiver 2025*

<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../classes/Auth.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

// Vérifier si l'utilisateur est admin
$query = "SELECT role FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['type'] !== 'text/csv') {
        $message = "Le fichier doit être au format CSV";
    } else {
        if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ",");
            $required_columns = ['quiz_id', 'question', 'bonne_reponse', 'mauvaises_reponses', 'image_filename'];
            if (count(array_intersect($header, $required_columns)) !== count($required_columns)) {
                $message = "Structure du fichier CSV invalide";
            } else {
                try {
                    $db->beginTransaction();
                    $query = "INSERT INTO questions (quiz_id, question_text, bonne_reponse, mauvaises_reponses, image_url)
                              VALUES (?, ?, ?, ?, ?)";
                    $stmt = $db->prepare($query);

                    $row = 2;
                    $imported = 0;

                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $quiz_check = $db->prepare("SELECT id FROM quiz WHERE id = ?");
                        $quiz_check->execute([$data[0]]);

                        if ($quiz_check->rowCount() > 0) {
                            $image_filename = trim($data[4]);
                            $image_url = null;

                            if (!empty($image_filename)) {
                                foreach ($_FILES['image_files']['name'] as $index => $name) {
                                    if ($name === $image_filename) {
                                        $tmp_name = $_FILES['image_files']['tmp_name'][$index];
                                        $upload_dir = '../uploads/';
                                        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                                        
                                        $target_path = $upload_dir . basename($image_filename);

                                        if (move_uploaded_file($tmp_name, $target_path)) {
                                            $image_url = $target_path;
                                        } else {
                                            throw new Exception("Erreur lors du transfert de l'image '$image_filename' à la ligne $row");
                                        }
                                        break;
                                    }
                                }
                            }

                            $stmt->execute([
                                $data[0], // quiz_id
                                $data[1], // question
                                $data[2], // bonne_reponse
                                $data[3], // mauvaises_reponses
                                $image_url // image_url (peut être null)
                            ]);
                            $imported++;
                        } else {
                            throw new Exception("Quiz ID invalide à la ligne $row");
                        }

                        $row++;
                    }

                    $db->commit();
                    $success = true;
                    $message = "$imported questions importées avec succès";
                } catch (Exception $e) {
                    $db->rollBack();
                    $message = "Erreur lors de l'import: " . $e->getMessage();
                }
            }
            fclose($handle);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Import de Questions</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Import de Questions</h1>

        <?php if ($message): ?>
            <div class="alert <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="csv_file">Fichier CSV:</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
            </div>
            <div class="form-group">
                <label for="image_files">Images associées (optionnel):</label>
                <input type="file" id="image_files" name="image_files[]" multiple accept="image/*">
            </div>

            <button type="submit" class="btn">Importer</button>
        </form>

        <div class="info-box">
            <h3>Format du fichier CSV:</h3>
            <p>Le fichier doit contenir les colonnes suivantes:</p>
            <ul>
                <li>quiz_id</li>
                <li>question</li>
                <li>bonne_reponse</li>
                <li>mauvaises_reponses (séparées par des points-virgules)</li>
                <li>image_filename (nom exact de l'image téléchargée)</li>
            </ul>
        </div>

        <a href="../pages/dashboard.php" class="btn">Retour au tableau de bord</a>
    </div>
</body>
</html>

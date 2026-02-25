<?php
// classes/FileManager.php
class FileManager {
    private $conn;
    private $upload_dir;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->upload_dir = UPLOAD_DIR;
    }
    
    public function uploadImage($file, $destination = 'questions') {
        try {
            // Vérifier si le fichier a été uploadé
            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                return ['success' => false, 'message' => 'Aucun fichier uploadé'];
            }
            
            // Vérifier le type de fichier
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowed)) {
                return ['success' => false, 'message' => 'Type de fichier non autorisé'];
            }
            
            // Créer le dossier de destination s'il n'existe pas
            $target_dir = $this->upload_dir . '/' . $destination . '/';
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Générer un nom unique pour le fichier
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $target_file = $target_dir . $filename;
            
            // Déplacer le fichier
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                return [
                    'success' => true,
                    'filename' => $filename,
                    'path' => $target_file
                ];
            }
            
            return ['success' => false, 'message' => 'Erreur lors du téléchargement'];
            
        } catch(Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function deleteFile($filename, $destination = 'questions') {
        $filepath = $this->upload_dir . '/' . $destination . '/' . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
    
    public function getFilePath($filename, $destination = 'questions') {
        return $this->upload_dir . '/' . $destination . '/' . $filename;
    }
}
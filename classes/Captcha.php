<?php
class Captcha {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function genererCaptcha() {
        try {
            // Générer un code aléatoire
            $code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

            // Créer l'image du CAPTCHA
            $image = imagecreatetruecolor(120, 30);
            $bg = imagecolorallocate($image, 255, 255, 255);
            $text_color = imagecolorallocate($image, 0, 0, 0);
            imagefilledrectangle($image, 0, 0, 120, 30, $bg);
            imagestring($image, 5, 20, 5, $code, $text_color);

            // Sauvegarder l'image
            ob_start();
            imagepng($image);
            $image_data = ob_get_clean();
            imagedestroy($image);

            // Sauvegarder dans la base de données
            $query = "INSERT INTO captcha (code, image) VALUES (:code, :image)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':code' => $code,
                ':image' => base64_encode($image_data)
            ]);

            return $code;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getCaptchaImage($code) {
        try {
            $query = "SELECT image FROM captcha WHERE code = :code";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':code' => $code]);
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function verifierCaptcha($code) {
        try {
            // Vérifier si le code existe et n'a pas expiré
            $query = "SELECT id FROM captcha WHERE code = :code";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':code' => $code]);
            
            if($stmt->rowCount() > 0) {
                // Supprimer le code après utilisation
                $query = "DELETE FROM captcha WHERE code = :code";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([':code' => $code]);
                return true;
            }
            
            return false;
            
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function nettoyerAnciensCaptcha() {
        try {
            // Supprimer les captchas plus vieux que 5 minutes
            $query = "DELETE FROM captcha WHERE created_at < (NOW() - INTERVAL 5 MINUTE)";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute();
            
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>
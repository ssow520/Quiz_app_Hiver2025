<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../controllers/QuizController.php';

$database = new Database();
$db = $database->getConnection();
$quizController = new QuizController($db);

if (!isset($_GET['id'])) {
    header('Location: quizList.php');
    exit();
}

$quiz_id = $_GET['id'];
$quiz_data = $quizController->commencerQuiz($quiz_id);

if (isset($quiz_data['error'])) {
    header('Location: quizList.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($quiz_data['quiz']['titre']); ?> - Quiz App</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="quiz-container">
        <header>
            <div class="header-top">
                <a href="quizList.php" class="btn back">Retour aux quiz</a>
                <h1><?php echo htmlspecialchars($quiz_data['quiz']['titre']); ?></h1>
            </div>
            <div class="quiz-info">
                <span id="question-counter">Question 1/<?php echo $quiz_data['total_questions']; ?></span>
                <span id="timer">Temps: 00:00</span>
                <span id="score">Score: 0</span>
            </div>
        </header>

        <div id="quiz-content">
            <?php if (!empty($quiz_data['questions'])): ?>
                <div class="question" id="question-container">
                    <h2><?php echo htmlspecialchars($quiz_data['questions'][0]['question_text']); ?></h2>
                    <?php if (!empty($quiz_data['questions'][0]['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($quiz_data['questions'][0]['image_url']); ?>"
                             alt="Question image" class="question-image">
                    <?php endif; ?>
                    <div class="reponses" id="reponses-container"></div>
                </div>
                <div class="quiz-controls">
                    <!-- Ajout du bouton indice -->
                    <button id="hint-btn" class="btn">Indice</button>
                    <button id="next-btn" class="btn" style="display: none;">Question suivante</button>
                    <button id="finish-btn" class="btn" style="display: none;">Terminer le quiz</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentQuestion = 0;
        let score = 0;
        const questions = <?php echo json_encode($quiz_data['questions']); ?>;
        const totalQuestions = questions.length;
        let timer;
        let seconds = 0;

        function updateTimer() {
            seconds++;
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            document.getElementById('timer').textContent = 
                `Temps: ${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
        }

        function displayQuestion(questionIndex) {
            const question = questions[questionIndex];
            document.getElementById('question-counter').textContent = 
                `Question ${questionIndex + 1}/${totalQuestions}`;
            
            const container = document.getElementById('question-container');
            container.querySelector('h2').textContent = question.question_text;
            
            const reponsesContainer = document.getElementById('reponses-container');
            reponsesContainer.innerHTML = '';
            
            const reponses = [question.bonne_reponse, ...question.mauvaises_reponses.split(',')];
            reponses.sort(() => Math.random() - 0.5);
            
            reponses.forEach(reponse => {
                const btn = document.createElement('button');
                btn.className = 'reponse-btn';
                btn.textContent = reponse;
                btn.onclick = () => verifierReponse(reponse, question.bonne_reponse);
                reponsesContainer.appendChild(btn);
            });

            // Afficher le bouton Indice
            document.getElementById('hint-btn').style.display = 'inline-block';
        }

        // Afficher l'indice
        document.getElementById('hint-btn').onclick = function() {
            const question = questions[currentQuestion];
            const hint = question.bonne_reponse.charAt(0); // Première lettre de la bonne réponse
            alert("L'indice : La réponse commence par " + hint); // Afficher l'indice (première lettre)
            this.disabled = true; // Désactiver le bouton après l'utilisation
        };

        function verifierReponse(reponseChoisie, bonneReponse) {
            const buttons = document.querySelectorAll('.reponse-btn');
            buttons.forEach(btn => {
                btn.disabled = true;
                if (btn.textContent === bonneReponse) {
                    btn.classList.add('correct');
                } else if (btn.textContent === reponseChoisie && reponseChoisie !== bonneReponse) {
                    btn.classList.add('incorrect');
                }
            });

            if (reponseChoisie === bonneReponse) {
                score++;
                document.getElementById('score').textContent = `Score: ${score}`;
            }

            document.getElementById('next-btn').style.display = 
                currentQuestion < totalQuestions - 1 ? 'block' : 'none';
            document.getElementById('finish-btn').style.display = 
                currentQuestion === totalQuestions - 1 ? 'block' : 'none';
        }

        document.getElementById('next-btn').onclick = () => {
            currentQuestion++;
            displayQuestion(currentQuestion);
            document.getElementById('next-btn').style.display = 'none';
        };

        document.getElementById('finish-btn').onclick = () => {
            clearInterval(timer);
            const finalScore = Math.round((score / totalQuestions) * 100);
            
            fetch('../ajax/submit_answer.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `quiz_id=<?php echo $quiz_id; ?>&score=${finalScore}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `results.php?quiz_id=<?php echo $quiz_id; ?>&score=${finalScore}`;
                }
            });
        };

        timer = setInterval(updateTimer, 1000);
        displayQuestion(currentQuestion);
    });
    </script>
</body>
</html>

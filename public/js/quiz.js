// quiz.js
document.addEventListener('DOMContentLoaded', function() {
    let currentQuestion = 0;
    let score = 0;
    let questions = [];
    let timer;
    let timeLeft;

    // Initialisation du quiz
    function initQuiz() {
        const quizId = document.querySelector('#quiz-data').dataset.quizId;
        fetch(`../ajax/get_questions.php?quiz_id=${quizId}`)
            .then(response => response.json())
            .then(data => {
                questions = data.questions;
                timeLeft = data.time_limit || 30;
                displayQuestion();
                startTimer();
            });
    }

    // Affichage de la question
    function displayQuestion() {
        if (currentQuestion >= questions.length) {
            endQuiz();
            return;
        }

        const question = questions[currentQuestion];
        document.querySelector('#question-text').textContent = question.question_text;
        document.querySelector('#question-counter').textContent = 
            `Question ${currentQuestion + 1}/${questions.length}`;

        // Mélanger les réponses
        const answers = [
            question.bonne_reponse,
            ...question.mauvaises_reponses.split(',')
        ].sort(() => Math.random() - 0.5);

        const answersContainer = document.querySelector('#answers-container');
        answersContainer.innerHTML = '';
        
        answers.forEach(answer => {
            const button = document.createElement('button');
            button.className = 'reponse-btn';
            button.textContent = answer;
            button.addEventListener('click', () => checkAnswer(answer));
            answersContainer.appendChild(button);
        });
    }

    // Vérification de la réponse
    function checkAnswer(selectedAnswer) {
        const correctAnswer = questions[currentQuestion].bonne_reponse;
        const buttons = document.querySelectorAll('.reponse-btn');
        
        buttons.forEach(button => {
            button.disabled = true;
            if (button.textContent === correctAnswer) {
                button.classList.add('correct');
            } else if (button.textContent === selectedAnswer && selectedAnswer !== correctAnswer) {
                button.classList.add('incorrect');
            }
        });

        if (selectedAnswer === correctAnswer) {
            score++;
            showFeedback('Correct !', 'success');
        } else {
            showFeedback('Incorrect. La bonne réponse était : ' + correctAnswer, 'error');
        }

        setTimeout(() => {
            currentQuestion++;
            displayQuestion();
        }, 2000);
    }

    // Minuteur
    function startTimer() {
        const timerDisplay = document.querySelector('#timer');
        timer = setInterval(() => {
            timeLeft--;
            timerDisplay.textContent = `Temps: ${timeLeft}s`;
            
            if (timeLeft <= 0) {
                endQuiz();
            }
        }, 1000);
    }

    // Fin du quiz
    function endQuiz() {
        clearInterval(timer);
        const finalScore = Math.round((score / questions.length) * 100);
        
        const quizContainer = document.querySelector('#quiz-container');
        quizContainer.innerHTML = `
            <div class="quiz-results">
                <h2>Quiz terminé !</h2>
                <p>Votre score : ${finalScore}%</p>
                <p>Bonnes réponses : ${score}/${questions.length}</p>
                <button onclick="window.location.href='quiz_list.php'" class="btn">
                    Retour aux quiz
                </button>
            </div>
        `;

        // Sauvegarder le score
        const quizId = document.querySelector('#quiz-data').dataset.quizId;
        fetch('../ajax/save_score.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `quiz_id=${quizId}&score=${finalScore}`
        });
    }

    // Affichage du feedback
    function showFeedback(message, type) {
        const feedback = document.createElement('div');
        feedback.className = `feedback ${type}`;
        feedback.textContent = message;
        document.querySelector('#feedback-container').appendChild(feedback);
        
        setTimeout(() => feedback.remove(), 2000);
    }

    // Bouton d'indice
    document.querySelector('#hint-btn')?.addEventListener('click', function() {
        fetch(`../ajax/get_hint.php?question_id=${questions[currentQuestion].id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFeedback(data.hint, 'warning');
                }
            });
    });

    // Démarrer le quiz
    initQuiz();
});
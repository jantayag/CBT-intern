document.addEventListener("DOMContentLoaded", function() {
    const scoreText = document.querySelector('.time-limit').textContent;
    const scores = scoreText.match(/(\d+\.?\d*)\s*\/\s*(\d+\.?\d*)/);
    
    if (!scores) return;
    
    const userScore = parseFloat(scores[1]);
    const totalScore = parseFloat(scores[2]);

    function calculatePercentage(score, total) {
        return Math.round((score / total) * 100);
    }

    const percentage = calculatePercentage(userScore, totalScore);

    let message = '';

    if (percentage >= 90) {
        message = "Excellent work! You have great potential.";
    } else if (percentage >= 75) {
        message = "Great job! You're doing well.";
    } else if (percentage >= 50) {
        message = "Not bad! Keep working on improving your score.";
    } else if (percentage >= 25) {
        message = "You can improve with a bit more effort. Keep practicing!";
    } else {
        message = "Don't worry! Learning is a journey. Keep trying!";
    }

    const messageDiv = document.createElement("div");
    messageDiv.classList.add('score-message');
    messageDiv.innerHTML = `<h2>${message}</h2>`;
    const mainDiv = document.querySelector('.main');
    if (mainDiv) {
        mainDiv.appendChild(messageDiv);
    }
});
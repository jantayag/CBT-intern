<?php
class Question {
    private $id;
    private $questionText;
    private $difficulty;
    private $points;
    private $type;
    private $correctAnswer;

    public function __construct($id, $question_text, $difficulty, $points, $type, $correctAnswer = null) {
        $this->id = $id;
        $this->questionText = $question_text;
        $this->difficulty = $difficulty;
        $this->points = $points;
        $this->type = $type;
        $this->correctAnswer = $correctAnswer;
    }

    public function getCorrectAnswer() {
        return $this->correctAnswer;
    }

    public function getId() {
        return $this->id;
    }

    public function getQuestionText() {
        return $this->questionText;
    }

    public function getDifficulty() {
        return $this->difficulty;
    }

    public function getPoints() {
        return $this->points;
    }

    public function getType() {
        return $this->type;
    }
}
?>

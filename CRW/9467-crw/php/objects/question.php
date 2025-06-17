<?php
class Question {
    private $id;
    private $questionText;
    private $difficulty;
    private $points;
    private $type;

    public function __construct($id, $questionText, $difficulty, $points, $type) {
        $this->id = $id;
        $this->questionText = $questionText;
        $this->difficulty = $difficulty;
        $this->points = $points;
        $this->type = $type;
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

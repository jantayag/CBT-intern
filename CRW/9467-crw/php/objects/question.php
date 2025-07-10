<?php
class Question {
    private $id, $text, $difficulty, $points, $type, $answer, $imagePath;

    public function __construct($id, $text, $difficulty, $points, $type, $answer, $imagePath = null) {
        $this->id = $id;
        $this->text = $text;
        $this->difficulty = $difficulty;
        $this->points = $points;
        $this->type = $type;
        $this->answer = $answer;
        $this->imagePath = $imagePath;
    }

    public function getId() { return $this->id; }
    public function getQuestionText() { return $this->text; }
    public function getDifficulty() { return $this->difficulty; }
    public function getPoints() { return $this->points; }
    public function getType() { return $this->type; }
    public function getCorrectAnswer() { return $this->answer; }
    public function getImagePath() { return $this->imagePath; } 
}

?>

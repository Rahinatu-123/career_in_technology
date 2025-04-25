class QuizQuestion {
  final String id;
  final String question;
  final List<QuizAnswer> answers;
  final String? imagePath;

  QuizQuestion({
    required this.id,
    required this.question,
    required this.answers,
    this.imagePath,
  });
}

class QuizAnswer {
  final String id;
  final String text;
  final Map<String, int> careerScores; // Maps career ID to score

  QuizAnswer({
    required this.id,
    required this.text,
    required this.careerScores,
  });
} 
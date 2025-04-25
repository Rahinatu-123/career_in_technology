import 'package:flutter/material.dart';
import '../data/quiz_data.dart';
import '../models/quiz_question.dart';
import 'quiz_result_page.dart';

class CareerQuizPage extends StatefulWidget {
  const CareerQuizPage({super.key});

  @override
  State<CareerQuizPage> createState() => _CareerQuizPageState();
}

class _CareerQuizPageState extends State<CareerQuizPage> {
  int _currentQuestionIndex = 0;
  Map<String, int> _careerScores = {};
  String? _selectedAnswerId;

  @override
  void initState() {
    super.initState();
    // Initialize scores for all careers
    _careerScores = {
      '1': 0, // Software Developer
      '2': 0, // Data Scientist
      '3': 0, // UI/UX Designer
      '4': 0, // Network Engineer
    };
  }

  void _selectAnswer(String answerId, Map<String, int> scores) {
    setState(() {
      _selectedAnswerId = answerId;
      // Add scores to each career
      scores.forEach((careerId, score) {
        _careerScores[careerId] = (_careerScores[careerId] ?? 0) + score;
      });
    });
  }

  void _nextQuestion() {
    if (_currentQuestionIndex < QuizData.questions.length - 1) {
      setState(() {
        _currentQuestionIndex++;
        _selectedAnswerId = null;
      });
    } else {
      // Navigate to results page
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (context) => QuizResultPage(scores: _careerScores),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final question = QuizData.questions[_currentQuestionIndex];
    final progress = (_currentQuestionIndex + 1) / QuizData.questions.length;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Career Quiz'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Theme.of(context).colorScheme.onPrimary,
      ),
      body: Column(
        children: [
          LinearProgressIndicator(
            value: progress,
            backgroundColor: Theme.of(context).colorScheme.primaryContainer,
            valueColor: AlwaysStoppedAnimation<Color>(
              Theme.of(context).colorScheme.primary,
            ),
          ),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Question ${_currentQuestionIndex + 1} of ${QuizData.questions.length}',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      color: Colors.grey[600],
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    question.question,
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 24),
                  ...question.answers.map((answer) => Padding(
                    padding: const EdgeInsets.only(bottom: 12.0),
                    child: AnswerCard(
                      answer: answer,
                      isSelected: _selectedAnswerId == answer.id,
                      onTap: () => _selectAnswer(answer.id, answer.careerScores),
                    ),
                  )),
                ],
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: ElevatedButton(
              onPressed: _selectedAnswerId == null ? null : _nextQuestion,
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
              ),
              child: Text(
                _currentQuestionIndex < QuizData.questions.length - 1
                    ? 'Next Question'
                    : 'See Results',
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class AnswerCard extends StatelessWidget {
  final QuizAnswer answer;
  final bool isSelected;
  final VoidCallback onTap;

  const AnswerCard({
    super.key,
    required this.answer,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: isSelected ? 4 : 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(
          color: isSelected
              ? Theme.of(context).colorScheme.primary
              : Colors.transparent,
          width: 2,
        ),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Row(
            children: [
              Container(
                width: 24,
                height: 24,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: isSelected
                        ? Theme.of(context).colorScheme.primary
                        : Colors.grey[300]!,
                    width: 2,
                  ),
                  color: isSelected
                      ? Theme.of(context).colorScheme.primary
                      : Colors.transparent,
                ),
                child: isSelected
                    ? Icon(
                        Icons.check,
                        size: 16,
                        color: Theme.of(context).colorScheme.onPrimary,
                      )
                    : null,
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Text(
                  answer.text,
                  style: Theme.of(context).textTheme.bodyLarge,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
} 
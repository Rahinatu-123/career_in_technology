import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class CareerQuizPage extends StatefulWidget {
  const CareerQuizPage({super.key});

  @override
  State<CareerQuizPage> createState() => _CareerQuizPageState();
}

class _CareerQuizPageState extends State<CareerQuizPage> {
  int _currentQuestionIndex = 0;
  final Map<int, String> _answers = {};
  
  final List<Map<String, dynamic>> _questions = [
    {
      'question': 'What type of activities do you enjoy most?',
      'options': [
        'Working with people and helping others',
        'Creating and designing things',
        'Analyzing and solving problems',
        'Working with technology and machines',
        'Managing and organizing things'
      ],
    },
    {
      'question': 'What are your strongest skills?',
      'options': [
        'Communication and leadership',
        'Creativity and artistic ability',
        'Logical thinking and analysis',
        'Technical and mechanical skills',
        'Planning and organization'
      ],
    },
    {
      'question': 'What is your preferred work environment?',
      'options': [
        'Collaborative team setting',
        'Creative and flexible space',
        'Structured and organized office',
        'Technical laboratory or workshop',
        'Independent and quiet space'
      ],
    },
    {
      'question': 'What are your career goals?',
      'options': [
        'Making a positive impact on society',
        'Expressing creativity and innovation',
        'Solving complex problems',
        'Building and creating things',
        'Leading and managing teams'
      ],
    },
  ];

  void _answerQuestion(String answer) {
    setState(() {
      _answers[_currentQuestionIndex] = answer;
      if (_currentQuestionIndex < _questions.length - 1) {
        _currentQuestionIndex++;
      }
    });
  }

  String _getCareerRecommendation() {
    // Simple recommendation logic based on answers
    int peopleScore = 0;
    int creativeScore = 0;
    int analyticalScore = 0;
    int technicalScore = 0;
    int managementScore = 0;

    _answers.forEach((index, answer) {
      switch (answer) {
        case 'Working with people and helping others':
        case 'Communication and leadership':
        case 'Collaborative team setting':
        case 'Making a positive impact on society':
          peopleScore++;
          break;
        case 'Creating and designing things':
        case 'Creativity and artistic ability':
        case 'Creative and flexible space':
        case 'Expressing creativity and innovation':
          creativeScore++;
          break;
        case 'Analyzing and solving problems':
        case 'Logical thinking and analysis':
        case 'Structured and organized office':
        case 'Solving complex problems':
          analyticalScore++;
          break;
        case 'Working with technology and machines':
        case 'Technical and mechanical skills':
        case 'Technical laboratory or workshop':
        case 'Building and creating things':
          technicalScore++;
          break;
        case 'Managing and organizing things':
        case 'Planning and organization':
        case 'Independent and quiet space':
        case 'Leading and managing teams':
          managementScore++;
          break;
      }
    });

    // Return career recommendation based on highest score
    if (peopleScore >= 3) return 'Social Work, Counseling, or Education';
    if (creativeScore >= 3) return 'Design, Arts, or Creative Media';
    if (analyticalScore >= 3) return 'Data Science, Research, or Consulting';
    if (technicalScore >= 3) return 'Engineering, IT, or Technical Fields';
    if (managementScore >= 3) return 'Business, Management, or Entrepreneurship';
    
    return 'Based on your answers, you might enjoy a career that combines multiple interests. Consider exploring interdisciplinary fields or roles that allow you to use various skills.';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Career Quiz',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
      ),
      body: _currentQuestionIndex < _questions.length
          ? _buildQuestion()
          : _buildResults(),
    );
  }

  Widget _buildQuestion() {
    final question = _questions[_currentQuestionIndex];
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          LinearProgressIndicator(
            value: (_currentQuestionIndex + 1) / _questions.length,
            backgroundColor: Colors.grey[200],
            valueColor: AlwaysStoppedAnimation<Color>(Theme.of(context).primaryColor),
          ),
          const SizedBox(height: 32),
          Text(
            'Question ${_currentQuestionIndex + 1} of ${_questions.length}',
            style: GoogleFonts.poppins(
              fontSize: 16,
              color: Colors.grey[600],
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          Text(
            question['question'],
            style: GoogleFonts.poppins(
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 32),
          ...question['options'].map<Widget>((option) => Padding(
            padding: const EdgeInsets.only(bottom: 16.0),
            child: ElevatedButton(
              onPressed: () => _answerQuestion(option),
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: Text(
                option,
                style: GoogleFonts.poppins(
                  fontSize: 16,
                ),
                textAlign: TextAlign.center,
              ),
            ),
          )).toList(),
        ],
      ),
    );
  }

  Widget _buildResults() {
    return Padding(
      padding: const EdgeInsets.all(24.0),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Icon(
            Icons.psychology,
            size: 80,
            color: Theme.of(context).primaryColor,
          ),
          const SizedBox(height: 24),
          Text(
            'Your Career Recommendation',
            style: GoogleFonts.poppins(
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          Text(
            _getCareerRecommendation(),
            style: GoogleFonts.poppins(
              fontSize: 18,
              height: 1.5,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 32),
          ElevatedButton(
            onPressed: () {
              setState(() {
                _currentQuestionIndex = 0;
                _answers.clear();
              });
            },
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: Text(
              'Take Quiz Again',
              style: GoogleFonts.poppins(
                fontSize: 16,
              ),
            ),
          ),
        ],
      ),
    );
  }
} 
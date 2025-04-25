import '../models/quiz_question.dart';

class QuizData {
  static final List<QuizQuestion> questions = [
    QuizQuestion(
      id: '1',
      question: 'How do you prefer to solve problems?',
      answers: [
        QuizAnswer(
          id: '1a',
          text: 'By breaking them down into smaller parts and finding logical solutions',
          careerScores: {
            '1': 5, // Software Developer
            '2': 4, // Data Scientist
            '3': 2, // UI/UX Designer
            '4': 3, // Network Engineer
          },
        ),
        QuizAnswer(
          id: '1b',
          text: 'By analyzing patterns and data',
          careerScores: {
            '1': 3,
            '2': 5,
            '3': 2,
            '4': 4,
          },
        ),
        QuizAnswer(
          id: '1c',
          text: 'By creating visual solutions and designs',
          careerScores: {
            '1': 2,
            '2': 3,
            '3': 5,
            '4': 2,
          },
        ),
        QuizAnswer(
          id: '1d',
          text: 'By understanding and fixing technical systems',
          careerScores: {
            '1': 3,
            '2': 2,
            '3': 2,
            '4': 5,
          },
        ),
      ],
    ),
    QuizQuestion(
      id: '2',
      question: 'What type of projects interest you the most?',
      answers: [
        QuizAnswer(
          id: '2a',
          text: 'Building applications and software',
          careerScores: {
            '1': 5,
            '2': 3,
            '3': 2,
            '4': 2,
          },
        ),
        QuizAnswer(
          id: '2b',
          text: 'Analyzing data and finding insights',
          careerScores: {
            '1': 2,
            '2': 5,
            '3': 2,
            '4': 3,
          },
        ),
        QuizAnswer(
          id: '2c',
          text: 'Designing user interfaces and experiences',
          careerScores: {
            '1': 2,
            '2': 2,
            '3': 5,
            '4': 2,
          },
        ),
        QuizAnswer(
          id: '2d',
          text: 'Setting up and maintaining networks',
          careerScores: {
            '1': 2,
            '2': 2,
            '3': 2,
            '4': 5,
          },
        ),
      ],
    ),
    QuizQuestion(
      id: '3',
      question: 'How do you feel about working with numbers and data?',
      answers: [
        QuizAnswer(
          id: '3a',
          text: 'I enjoy working with numbers and data analysis',
          careerScores: {
            '1': 3,
            '2': 5,
            '3': 2,
            '4': 4,
          },
        ),
        QuizAnswer(
          id: '3b',
          text: 'I prefer working with code and algorithms',
          careerScores: {
            '1': 5,
            '2': 4,
            '3': 2,
            '4': 3,
          },
        ),
        QuizAnswer(
          id: '3c',
          text: 'I like working with visual elements and design',
          careerScores: {
            '1': 2,
            '2': 2,
            '3': 5,
            '4': 2,
          },
        ),
        QuizAnswer(
          id: '3d',
          text: 'I enjoy working with technical systems and hardware',
          careerScores: {
            '1': 3,
            '2': 2,
            '3': 2,
            '4': 5,
          },
        ),
      ],
    ),
    QuizQuestion(
      id: '4',
      question: 'What is your preferred work environment?',
      answers: [
        QuizAnswer(
          id: '4a',
          text: 'Working independently on coding projects',
          careerScores: {
            '1': 5,
            '2': 4,
            '3': 3,
            '4': 3,
          },
        ),
        QuizAnswer(
          id: '4b',
          text: 'Collaborating with teams on data analysis',
          careerScores: {
            '1': 3,
            '2': 5,
            '3': 4,
            '4': 3,
          },
        ),
        QuizAnswer(
          id: '4c',
          text: 'Working on creative design projects',
          careerScores: {
            '1': 2,
            '2': 2,
            '3': 5,
            '4': 2,
          },
        ),
        QuizAnswer(
          id: '4d',
          text: 'Working with technical infrastructure',
          careerScores: {
            '1': 2,
            '2': 2,
            '3': 2,
            '4': 5,
          },
        ),
      ],
    ),
  ];
} 
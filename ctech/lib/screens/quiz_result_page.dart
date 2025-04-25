import 'package:flutter/material.dart';
import '../data/career_profiles_data.dart';
import '../models/career_profile.dart';
import 'career_detail_page.dart';

class QuizResultPage extends StatelessWidget {
  final Map<String, int> scores;

  const QuizResultPage({
    super.key,
    required this.scores,
  });

  CareerProfile _getRecommendedCareer() {
    String recommendedId = '1';
    int highestScore = 0;

    scores.forEach((careerId, score) {
      if (score > highestScore) {
        highestScore = score;
        recommendedId = careerId;
      }
    });

    return CareerProfilesData.profiles.firstWhere(
      (profile) => profile.id == recommendedId,
    );
  }

  @override
  Widget build(BuildContext context) {
    final recommendedCareer = _getRecommendedCareer();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Quiz Results'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Theme.of(context).colorScheme.onPrimary,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24.0),
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.primaryContainer,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  Icon(
                    Icons.emoji_events,
                    size: 64,
                    color: Theme.of(context).colorScheme.primary,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Your Recommended Career',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    recommendedCareer.title,
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                      color: Theme.of(context).colorScheme.primary,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'Why This Career?',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Based on your answers, this career path aligns well with your interests and skills. You show a strong inclination towards ${recommendedCareer.title.toLowerCase()} work.',
              style: Theme.of(context).textTheme.bodyLarge,
            ),
            const SizedBox(height: 24),
            Text(
              'Next Steps',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Learn more about this career path and what it takes to succeed in this field.',
              style: Theme.of(context).textTheme.bodyLarge,
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => CareerDetailPage(
                      profile: recommendedCareer,
                    ),
                  ),
                );
              },
              icon: const Icon(Icons.arrow_forward),
              label: const Text('Explore Career Details'),
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
              ),
            ),
            const SizedBox(height: 16),
            OutlinedButton.icon(
              onPressed: () {
                Navigator.pushNamedAndRemoveUntil(
                  context,
                  '/home',
                  (route) => false,
                );
              },
              icon: const Icon(Icons.home),
              label: const Text('Back to Home'),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
              ),
            ),
          ],
        ),
      ),
    );
  }
} 
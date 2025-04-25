import 'package:flutter/material.dart';
import '../widgets/feature_card.dart';

class HomePage extends StatelessWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('CTech'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Theme.of(context).colorScheme.onPrimary,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Welcome to CTech!',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Explore your future in technology',
              style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 24),
            Expanded(
              child: GridView.count(
                crossAxisCount: 2,
                mainAxisSpacing: 16,
                crossAxisSpacing: 16,
                children: [
                  FeatureCard(
                    icon: Icons.work,
                    title: 'Career Profiles',
                    onTap: () => Navigator.pushNamed(context, '/career-profiles'),
                  ),
                  FeatureCard(
                    icon: Icons.quiz,
                    title: 'Career Quiz',
                    onTap: () => Navigator.pushNamed(context, '/career-quiz'),
                  ),
                  FeatureCard(
                    icon: Icons.people,
                    title: 'Inspiring Stories',
                    onTap: () => Navigator.pushNamed(context, '/stories'),
                  ),
                  FeatureCard(
                    icon: Icons.lightbulb,
                    title: 'Tech Word of the Day',
                    onTap: () => Navigator.pushNamed(context, '/tech-word'),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
} 
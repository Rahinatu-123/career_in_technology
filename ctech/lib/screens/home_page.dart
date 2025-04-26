import 'package:flutter/material.dart';
import '../widgets/feature_card.dart';

class HomePage extends StatelessWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 200,
            floating: false,
            pinned: true,
            flexibleSpace: FlexibleSpaceBar(
              title: Text(
                'CTech',
                style: TextStyle(
                  color: Theme.of(context).colorScheme.onPrimary,
                  fontWeight: FontWeight.bold,
                ),
              ),
              background: Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [
                      Theme.of(context).colorScheme.primary,
                      Theme.of(context).colorScheme.primaryContainer,
                    ],
                  ),
                ),
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Welcome to CTech!',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: Theme.of(context).colorScheme.primary,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Explore your future in technology',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      color: Theme.of(context).colorScheme.onSurfaceVariant,
                    ),
                  ),
                  const SizedBox(height: 32),
                  GridView.count(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    crossAxisCount: 2,
                    mainAxisSpacing: 16,
                    crossAxisSpacing: 16,
                    childAspectRatio: 1.1,
                    children: [
                      FeatureCard(
                        icon: Icons.work,
                        title: 'Career Profiles',
                        subtitle: 'Explore tech careers',
                        onTap: () => Navigator.pushNamed(context, '/career-profiles'),
                      ),
                      FeatureCard(
                        icon: Icons.quiz,
                        title: 'Career Quiz',
                        subtitle: 'Find your path',
                        onTap: () => Navigator.pushNamed(context, '/career-quiz'),
                      ),
                      FeatureCard(
                        icon: Icons.people,
                        title: 'Inspiring Stories',
                        subtitle: 'Learn from others',
                        onTap: () => Navigator.pushNamed(context, '/stories'),
                      ),
                      FeatureCard(
                        icon: Icons.lightbulb,
                        title: 'Tech Word of the Day',
                        subtitle: 'Expand your knowledge',
                        onTap: () => Navigator.pushNamed(context, '/tech-word'),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
} 
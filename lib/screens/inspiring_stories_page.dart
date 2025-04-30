import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class InspiringStoriesPage extends StatelessWidget {
  const InspiringStoriesPage({super.key});

  static const List<Map<String, dynamic>> _stories = [
    {
      'name': 'Sarah Chen',
      'role': 'Software Engineer at Google',
      'icon': Icons.person,
      'quote': 'Never let your background limit your potential. I started coding in my late 20s and now I\'m living my dream.',
      'story': 'After working as a teacher for 5 years, Sarah decided to switch careers to technology. She taught herself programming through online courses and bootcamps. Within 2 years, she landed her dream job at Google, working on AI-powered education tools.',
      'advice': 'Focus on building practical projects and networking with professionals in your target industry.',
      'tags': ['Career Change', 'Self-Taught', 'Technology'],
    },
    {
      'name': 'Marcus Rodriguez',
      'role': 'Data Science Lead at Microsoft',
      'icon': Icons.person,
      'quote': 'The best career decisions come from following your curiosity and being open to new opportunities.',
      'story': 'Marcus started as a business analyst but found himself drawn to data science. He pursued a master\'s degree while working full-time and gradually transitioned into data science roles. Today, he leads a team of data scientists working on AI solutions.',
      'advice': 'Don\'t be afraid to invest in your education and take calculated risks in your career journey.',
      'tags': ['Education', 'Leadership', 'Data Science'],
    },
    {
      'name': 'Aisha Patel',
      'role': 'UX Design Director at Spotify',
      'icon': Icons.person,
      'quote': 'Design is about solving problems and creating experiences that make a difference in people\'s lives.',
      'story': 'Aisha began her career in graphic design but discovered her passion for user experience design. She worked her way up from junior designer to design director, leading teams that create products used by millions.',
      'advice': 'Build a strong portfolio and focus on user-centered design principles.',
      'tags': ['Design', 'Leadership', 'Innovation'],
    },
    {
      'name': 'James Wilson',
      'role': 'Founder of TechStart Academy',
      'icon': Icons.person,
      'quote': 'Entrepreneurship is about solving problems and creating value for others.',
      'story': 'After working in tech for 10 years, James founded a coding bootcamp to help others break into the industry. His academy has helped hundreds of students transition into tech careers.',
      'advice': 'Find a problem you\'re passionate about solving and build a business around it.',
      'tags': ['Entrepreneurship', 'Education', 'Technology'],
    },
    {
      'name': 'Emily Kim',
      'role': 'Digital Marketing Manager at Nike',
      'icon': Icons.person,
      'quote': 'Success in marketing comes from understanding your audience and telling compelling stories.',
      'story': 'Emily started in social media management and worked her way up to leading digital marketing campaigns for major brands. Her innovative strategies have won several industry awards.',
      'advice': 'Stay current with digital trends and focus on data-driven decision making.',
      'tags': ['Marketing', 'Digital', 'Leadership'],
    },
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Inspiring Stories',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _stories.length,
        itemBuilder: (context, index) {
          final story = _stories[index];
          return Card(
            margin: const EdgeInsets.only(bottom: 24),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  height: 200,
                  decoration: BoxDecoration(
                    borderRadius: const BorderRadius.vertical(
                      top: Radius.circular(12),
                    ),
                    color: Theme.of(context).primaryColor.withOpacity(0.1),
                  ),
                  child: Center(
                    child: Icon(
                      story['icon'] as IconData,
                      size: 80,
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        story['name'] as String,
                        style: GoogleFonts.poppins(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        story['role'] as String,
                        style: GoogleFonts.poppins(
                          fontSize: 16,
                          color: Colors.grey[600],
                        ),
                      ),
                      const SizedBox(height: 16),
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Theme.of(context).primaryColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          story['quote'] as String,
                          style: GoogleFonts.poppins(
                            fontSize: 16,
                            fontStyle: FontStyle.italic,
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Their Story',
                        style: GoogleFonts.poppins(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        story['story'] as String,
                        style: GoogleFonts.poppins(
                          fontSize: 14,
                          height: 1.5,
                        ),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Career Advice',
                        style: GoogleFonts.poppins(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        story['advice'] as String,
                        style: GoogleFonts.poppins(
                          fontSize: 14,
                          height: 1.5,
                        ),
                      ),
                      const SizedBox(height: 16),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: (story['tags'] as List<String>).map((tag) => Chip(
                          label: Text(
                            tag,
                            style: GoogleFonts.poppins(
                              fontSize: 12,
                            ),
                          ),
                          backgroundColor: Theme.of(context).primaryColor.withOpacity(0.1),
                        )).toList(),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
} 
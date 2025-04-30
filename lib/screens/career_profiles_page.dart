import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class CareerProfilesPage extends StatelessWidget {
  const CareerProfilesPage({super.key});

  static const List<Map<String, dynamic>> _careerProfiles = [
    {
      'title': 'Software Engineering',
      'icon': Icons.code,
      'description': 'Design and develop software applications and systems.',
      'skills': ['Programming', 'Problem Solving', 'System Design', 'Team Collaboration'],
      'education': 'Bachelor\'s in Computer Science or related field',
      'growth': 'High growth potential with opportunities in AI, mobile, and cloud computing',
      'salary': 'Entry: \$60,000 - \$90,000\nMid: \$90,000 - \$150,000\nSenior: \$150,000+',
    },
    {
      'title': 'Data Science',
      'icon': Icons.analytics,
      'description': 'Analyze data to help organizations make better decisions.',
      'skills': ['Statistics', 'Python', 'Machine Learning', 'Data Visualization'],
      'education': 'Bachelor\'s or Master\'s in Data Science, Statistics, or related field',
      'growth': 'Rapidly growing field with applications across industries',
      'salary': 'Entry: \$65,000 - \$95,000\nMid: \$95,000 - \$160,000\nSenior: \$160,000+',
    },
    {
      'title': 'UX/UI Design',
      'icon': Icons.design_services,
      'description': 'Create user-friendly interfaces and engaging user experiences.',
      'skills': ['Design Thinking', 'Prototyping', 'User Research', 'Visual Design'],
      'education': 'Bachelor\'s in Design, HCI, or related field',
      'growth': 'Growing demand for digital product design',
      'salary': 'Entry: \$55,000 - \$85,000\nMid: \$85,000 - \$140,000\nSenior: \$140,000+',
    },
    {
      'title': 'Digital Marketing',
      'icon': Icons.trending_up,
      'description': 'Develop and execute online marketing strategies.',
      'skills': ['SEO', 'Social Media', 'Content Creation', 'Analytics'],
      'education': 'Bachelor\'s in Marketing, Communications, or related field',
      'growth': 'Expanding field with focus on digital presence',
      'salary': 'Entry: \$45,000 - \$70,000\nMid: \$70,000 - \$120,000\nSenior: \$120,000+',
    },
    {
      'title': 'Project Management',
      'icon': Icons.assignment,
      'description': 'Lead and coordinate projects to ensure successful delivery.',
      'skills': ['Leadership', 'Communication', 'Planning', 'Risk Management'],
      'education': 'Bachelor\'s in Business, PMP certification recommended',
      'growth': 'Consistent demand across industries',
      'salary': 'Entry: \$60,000 - \$85,000\nMid: \$85,000 - \$130,000\nSenior: \$130,000+',
    },
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Career Profiles',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _careerProfiles.length,
        itemBuilder: (context, index) {
          final profile = _careerProfiles[index];
          return Card(
            margin: const EdgeInsets.only(bottom: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            child: ExpansionTile(
              leading: Icon(
                profile['icon'] as IconData,
                color: Theme.of(context).primaryColor,
                size: 32,
              ),
              title: Text(
                profile['title'] as String,
                style: GoogleFonts.poppins(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                ),
              ),
              subtitle: Text(
                profile['description'] as String,
                style: GoogleFonts.poppins(
                  fontSize: 14,
                  color: Colors.grey[600],
                ),
              ),
              children: [
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildSection(
                        'Required Skills',
                        (profile['skills'] as List<String>),
                        Icons.psychology,
                      ),
                      const SizedBox(height: 16),
                      _buildSection(
                        'Education',
                        [profile['education'] as String],
                        Icons.school,
                      ),
                      const SizedBox(height: 16),
                      _buildSection(
                        'Career Growth',
                        [profile['growth'] as String],
                        Icons.trending_up,
                      ),
                      const SizedBox(height: 16),
                      _buildSection(
                        'Salary Range',
                        [profile['salary'] as String],
                        Icons.attach_money,
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

  Widget _buildSection(String title, List<String> items, IconData icon) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(
              icon,
              size: 20,
              color: Colors.grey[600],
            ),
            const SizedBox(width: 8),
            Text(
              title,
              style: GoogleFonts.poppins(
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        ...items.map((item) => Padding(
          padding: const EdgeInsets.only(left: 28, bottom: 4),
          child: Text(
            item,
            style: GoogleFonts.poppins(
              fontSize: 14,
              height: 1.5,
            ),
          ),
        )).toList(),
      ],
    );
  }
} 
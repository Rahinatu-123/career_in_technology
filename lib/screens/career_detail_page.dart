import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../models/career_profile.dart';
import '../data/career_profiles_data.dart';

class CareerDetailPage extends StatelessWidget {
  final String careerId;

  const CareerDetailPage({
    Key? key,
    required this.careerId,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Consumer<CareerProfilesData>(
      builder: (context, careerData, _) {
        final career = careerData.getCareerProfileById(careerId);
        if (career == null) {
          return Scaffold(
            appBar: AppBar(
              title: Text(
                'Career Not Found',
                style: GoogleFonts.poppins(
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            body: Center(
              child: Text(
                'The requested career profile could not be found.',
                style: GoogleFonts.poppins(),
              ),
            ),
          );
        }

        return Scaffold(
          body: CustomScrollView(
            slivers: [
              SliverAppBar(
                expandedHeight: 200,
                pinned: true,
                flexibleSpace: FlexibleSpaceBar(
                  title: Text(
                    career.title,
                    style: GoogleFonts.poppins(
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  background: Image.asset(
                    career.imageUrl,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, stackTrace) {
                      return Container(
                        color: Theme.of(context).primaryColor,
                        child: Icon(
                          Icons.work,
                          size: 64,
                          color: Colors.white,
                        ),
                      );
                    },
                  ),
                ),
              ),
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildSection(
                        context,
                        'Overview',
                        career.description,
                      ),
                      const SizedBox(height: 24),
                      _buildInfoCard(context, career),
                      const SizedBox(height: 24),
                      _buildSection(
                        context,
                        'Required Skills',
                        null,
                        items: career.skills,
                      ),
                      const SizedBox(height: 24),
                      _buildSection(
                        context,
                        'Responsibilities',
                        null,
                        items: career.responsibilities,
                      ),
                      const SizedBox(height: 24),
                      _buildSection(
                        context,
                        'Requirements',
                        null,
                        items: career.requirements,
                      ),
                      const SizedBox(height: 24),
                      _buildSection(
                        context,
                        'Related Careers',
                        null,
                        items: career.relatedCareers,
                      ),
                      const SizedBox(height: 32),
                    ],
                  ),
                ),
              ),
            ],
          ),
          floatingActionButton: FloatingActionButton.extended(
            onPressed: () {
              careerData.toggleSaveCareer(careerId);
            },
            icon: Icon(
              career.isSaved ? Icons.bookmark : Icons.bookmark_border,
            ),
            label: Text(
              career.isSaved ? 'Saved' : 'Save',
              style: GoogleFonts.poppins(),
            ),
          ),
        );
      },
    );
  }

  Widget _buildSection(
    BuildContext context,
    String title,
    String? description, {
    List<String>? items,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: GoogleFonts.poppins(
            fontSize: 20,
            fontWeight: FontWeight.w600,
          ),
        ),
        if (description != null) ...[
          const SizedBox(height: 8),
          Text(
            description,
            style: GoogleFonts.poppins(
              fontSize: 16,
              color: Colors.grey[600],
            ),
          ),
        ],
        if (items != null) ...[
          const SizedBox(height: 16),
          ...items.map((item) => Padding(
                padding: const EdgeInsets.only(bottom: 8.0),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(
                      Icons.check_circle_outline,
                      size: 20,
                      color: Colors.green,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        item,
                        style: GoogleFonts.poppins(
                          fontSize: 16,
                        ),
                      ),
                    ),
                  ],
                ),
              )),
        ],
      ],
    );
  }

  Widget _buildInfoCard(BuildContext context, CareerProfile career) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            _buildInfoRow(
              context,
              'Salary',
              career.formattedSalary,
              Icons.attach_money,
            ),
            const Divider(height: 24),
            _buildInfoRow(
              context,
              'Growth Rate',
              career.growthRateText,
              Icons.trending_up,
            ),
            const Divider(height: 24),
            _buildInfoRow(
              context,
              'Education',
              career.educationLevel,
              Icons.school,
            ),
            const Divider(height: 24),
            _buildInfoRow(
              context,
              'Experience',
              career.experienceText,
              Icons.work,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(
    BuildContext context,
    String label,
    String value,
    IconData icon,
  ) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: Theme.of(context).primaryColor.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(
            icon,
            color: Theme.of(context).primaryColor,
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: GoogleFonts.poppins(
                  fontSize: 14,
                  color: Colors.grey[600],
                ),
              ),
              const SizedBox(height: 4),
              Text(
                value,
                style: GoogleFonts.poppins(
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
} 
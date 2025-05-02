import 'package:flutter/material.dart';
import '../models/inspiring_story.dart';
import '../models/career_profile.dart';
import '../services/api_service.dart';
import 'career_detail_page.dart';

class StoryDetailPage extends StatefulWidget {
  final InspiringStory story;

  const StoryDetailPage({
    super.key,
    required this.story,
  });

  @override
  State<StoryDetailPage> createState() => _StoryDetailPageState();
}

class _StoryDetailPageState extends State<StoryDetailPage> {
  final ApiService _apiService = ApiService();
  List<CareerProfile> relatedCareers = [];
  bool isLoading = true;
  String error = '';

  @override
  void initState() {
    super.initState();
    _loadRelatedCareers();
  }

  Future<void> _loadRelatedCareers() async {
    try {
      final careers = await _apiService.getCareers();
      if (mounted) {
        setState(() {
          relatedCareers = careers.where((career) => 
            widget.story.relatedCareers.contains(career.id.toString())
          ).toList();
          isLoading = false;
          error = '';
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          error = e.toString();
          isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Success Story'),
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
                  Container(
                    width: 100,
                    height: 100,
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.primary,
                      shape: BoxShape.circle,
                      image: widget.story.imagePath.isNotEmpty
                          ? DecorationImage(
                              image: NetworkImage(widget.story.imagePath),
                              fit: BoxFit.cover,
                            )
                          : null,
                    ),
                    child: widget.story.imagePath.isEmpty
                        ? Icon(
                            Icons.person,
                            size: 50,
                            color: Theme.of(context).colorScheme.onPrimary,
                          )
                        : null,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    widget.story.name,
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${widget.story.role} at ${widget.story.company}',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'Their Story',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              widget.story.fullStory,
              style: Theme.of(context).textTheme.bodyLarge,
            ),
            if (widget.story.audioPath != null) ...[
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: () {
                  // TODO: Implement audio playback using just_audio package
                },
                icon: const Icon(Icons.headphones),
                label: const Text('Listen to Their Story'),
                style: ElevatedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 48),
                ),
              ),
            ],
            const SizedBox(height: 24),
            Text(
              'Related Careers',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            if (isLoading)
              const Center(
                child: CircularProgressIndicator(),
              )
            else if (error.isNotEmpty)
              Center(
                child: Text(
                  'Failed to load related careers: $error',
                  style: TextStyle(color: Theme.of(context).colorScheme.error),
                ),
              )
            else if (relatedCareers.isEmpty)
              const Center(
                child: Text('No related careers found'),
              )
            else
              ...relatedCareers.map((career) => Padding(
                padding: const EdgeInsets.only(bottom: 8.0),
                child: Card(
                  child: ListTile(
                    leading: Icon(
                      Icons.work,
                      color: Theme.of(context).colorScheme.primary,
                    ),
                    title: Text(career.title),
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => CareerDetailPage(
                            career: career,
                          ),
                        ),
                      );
                    },
                  ),
                ),
              )),
            const SizedBox(height: 24),
            OutlinedButton.icon(
              onPressed: () {
                Navigator.pop(context);
              },
              icon: const Icon(Icons.arrow_back),
              label: const Text('Back to Stories'),
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
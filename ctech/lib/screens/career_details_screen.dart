import 'package:flutter/material.dart';
import '../models/career_profile.dart';
import '../models/tech_word.dart';
import '../repositories/career_repository.dart';

class CareerDetailsScreen extends StatefulWidget {
  final CareerProfile career;

  const CareerDetailsScreen({
    super.key,
    required this.career,
  });

  @override
  State<CareerDetailsScreen> createState() => _CareerDetailsScreenState();
}

class _CareerDetailsScreenState extends State<CareerDetailsScreen> {
  final _repository = CareerRepository();
  List<TechWord> _techWords = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadTechWords();
  }

  Future<void> _loadTechWords() async {
    setState(() => _isLoading = true);
    try {
      final words = await _repository.getTechWords(careerId: widget.career.id);
      setState(() {
        _techWords = words;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading tech words: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.career.title),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Description',
                      style: Theme.of(context).textTheme.titleLarge,
                    ),
                    const SizedBox(height: 8),
                    Text(widget.career.description),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Requirements',
                      style: Theme.of(context).textTheme.titleLarge,
                    ),
                    const SizedBox(height: 8),
                    ListTile(
                      leading: const Icon(Icons.school),
                      title: const Text('Education'),
                      subtitle: Text(widget.career.education),
                    ),
                    ListTile(
                      leading: const Icon(Icons.engineering),
                      title: const Text('Skills'),
                      subtitle: Text(widget.career.skills),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Career Outlook',
                      style: Theme.of(context).textTheme.titleLarge,
                    ),
                    const SizedBox(height: 8),
                    ListTile(
                      leading: const Icon(Icons.attach_money),
                      title: const Text('Salary Range'),
                      subtitle: Text(widget.career.salaryRange),
                    ),
                    ListTile(
                      leading: const Icon(Icons.trending_up),
                      title: const Text('Job Outlook'),
                      subtitle: Text(widget.career.jobOutlook),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'Related Tech Terms',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 8),
            if (_isLoading)
              const Center(child: CircularProgressIndicator())
            else
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: _techWords.length,
                itemBuilder: (context, index) {
                  final word = _techWords[index];
                  return Card(
                    child: ExpansionTile(
                      title: Text(word.word),
                      children: [
                        Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(word.definition),
                            ],
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
          ],
        ),
      ),
    );
  }
} 
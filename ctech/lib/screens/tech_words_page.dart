import 'package:flutter/material.dart';
import '../models/tech_word.dart';
import '../services/api_service.dart';

class TechWordsPage extends StatefulWidget {
  const TechWordsPage({super.key});

  @override
  State<TechWordsPage> createState() => _TechWordsPageState();
}

class _TechWordsPageState extends State<TechWordsPage> {
  final ApiService _apiService = ApiService();
  List<TechWord> words = [];
  bool isLoading = true;
  String error = '';

  @override
  void initState() {
    super.initState();
    fetchWords();
  }

  Future<void> fetchWords() async {
    try {
      final fetchedWords = await _apiService.getTechWords();
      if (mounted) {
        setState(() {
          words = fetchedWords;
          isLoading = false;
          error = '';
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          error = e.toString().replaceAll('Exception: ', '');
          isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0A2A36),
      appBar: AppBar(
        title: const Text(
          'Tech Words',
          style: TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
        backgroundColor: const Color(0xFF0A2A36),
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          setState(() {
            isLoading = true;
            error = '';
          });
          await fetchWords();
        },
        child: isLoading
            ? const Center(
                child: CircularProgressIndicator(
                  color: Colors.white,
                ),
              )
            : error.isNotEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 32.0),
                          child: Text(
                            error,
                            textAlign: TextAlign.center,
                            style: const TextStyle(
                              color: Colors.white70,
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () {
                            setState(() {
                              isLoading = true;
                              error = '';
                            });
                            fetchWords();
                          },
                          icon: const Icon(Icons.refresh),
                          label: const Text('Retry'),
                          style: ElevatedButton.styleFrom(
                            foregroundColor: const Color(0xFF0A2A36),
                            backgroundColor: Colors.white,
                          ),
                        ),
                      ],
                    ),
                  )
                : words.isEmpty
                    ? const Center(
                        child: Text(
                          'No tech words available',
                          style: TextStyle(color: Colors.white70),
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.all(16.0),
                        itemCount: words.length,
                        itemBuilder: (context, index) {
                          final word = words[index];
                          return TechWordCard(word: word);
                        },
                      ),
      ),
    );
  }
}

class TechWordCard extends StatelessWidget {
  final TechWord word;

  const TechWordCard({
    super.key,
    required this.word,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16.0),
      color: const Color(0xFF153B4D),
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.white10,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.code,
                    size: 24,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        word.word,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      if (word.category?.isNotEmpty == true)
                        Container(
                          margin: const EdgeInsets.only(top: 4),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.white10,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            word.category!,
                            style: const TextStyle(
                              color: Colors.white70,
                              fontSize: 12,
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              word.definition,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 14,
              ),
            ),
          ],
        ),
      ),
    );
  }
} 
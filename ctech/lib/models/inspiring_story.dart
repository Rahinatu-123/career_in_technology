class InspiringStory {
  final String id;
  final String name;
  final String role;
  final String company;
  final String imagePath;
  final String shortQuote;
  final String fullStory;
  final String? audioPath;
  final List<String> relatedCareers;

  InspiringStory({
    required this.id,
    required this.name,
    required this.role,
    required this.company,
    required this.imagePath,
    required this.shortQuote,
    required this.fullStory,
    this.audioPath,
    required this.relatedCareers,
  });
} 
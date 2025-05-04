class InspiringStory {
  final int id;
  final String name;
  final String role;
  final String company;
  final String? imagePath;
  final String shortQuote;
  final String fullStory;
  final String? audioPath;
  final List<String> relatedCareers;
  final String createdAt;

  InspiringStory({
    required this.id,
    required this.name,
    required this.role,
    required this.company,
    this.imagePath,
    required this.shortQuote,
    required this.fullStory,
    this.audioPath,
    required this.relatedCareers,
    required this.createdAt,
  });

  factory InspiringStory.fromJson(Map<String, dynamic> json) {
    return InspiringStory(
      id: json['id'] as int,
      name: json['name'] as String,
      role: json['role'] as String,
      company: json['company'] as String,
      imagePath: json['image_path'] as String?,
      shortQuote: json['short_quote'] as String,
      fullStory: json['full_story'] as String,
      audioPath: json['audio_path'] as String?,
      relatedCareers: List<String>.from(json['related_careers'] ?? []),
      createdAt: json['created_at'] as String,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'role': role,
      'company': company,
      'image_path': imagePath,
      'short_quote': shortQuote,
      'full_story': fullStory,
      'audio_path': audioPath,
      'related_careers': relatedCareers,
      'created_at': createdAt,
    };
  }
} 
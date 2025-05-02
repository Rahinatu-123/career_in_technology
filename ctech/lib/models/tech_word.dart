class TechWord {
  final int id;
  final String word;
  final String definition;
  final String? category;
  final List<int> relatedCareers;
  final String createdAt;

  TechWord({
    required this.id,
    required this.word,
    required this.definition,
    this.category,
    required this.relatedCareers,
    required this.createdAt,
  });

  factory TechWord.fromJson(Map<String, dynamic> json) {
    return TechWord(
      id: json['id'],
      word: json['word'],
      definition: json['definition'],
      category: json['category'],
      relatedCareers: List<int>.from(json['related_careers'] ?? []),
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'word': word,
      'definition': definition,
      'category': category,
      'related_careers': relatedCareers,
      'created_at': createdAt,
    };
  }
} 
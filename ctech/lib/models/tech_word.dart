class TechWord {
  final int id;
  final String word;
  final String definition;
  final String? category;
  final String createdAt;

  TechWord({
    required this.id,
    required this.word,
    required this.definition,
    this.category,
    required this.createdAt,
  });

  factory TechWord.fromJson(Map<String, dynamic> json) {
    return TechWord(
      id: json['id'] as int,
      word: json['word'] as String,
      definition: json['definition'] as String,
      category: json['category']?.toString(),
      createdAt: json['created_at']?.toString() ?? DateTime.now().toIso8601String(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'word': word,
      'definition': definition,
      'category': category,
      'created_at': createdAt,
    };
  }
} 
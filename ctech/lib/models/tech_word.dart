class TechWord {
  final int id;
  final String term;
  final String definition;
  final String? example;
  final String? category;
  final List<int> relatedCareers;

  TechWord({
    required this.id,
    required this.term,
    required this.definition,
    this.example,
    this.category,
    this.relatedCareers = const [],
  });

  factory TechWord.fromJson(Map<String, dynamic> json) {
    return TechWord(
      id: json['id'],
      term: json['term'],
      definition: json['definition'],
      example: json['example'],
      category: json['category'],
      relatedCareers: List<int>.from(json['related_careers'] ?? []),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'term': term,
      'definition': definition,
      'example': example,
      'category': category,
      'related_careers': relatedCareers,
    };
  }
} 
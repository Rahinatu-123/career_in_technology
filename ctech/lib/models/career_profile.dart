import 'tech_word.dart';

class CareerProfile {
  final int id;
  final String title;
  final String description;
  final String skills;
  final String education;
  final String salaryRange;
  final String jobOutlook;
  final DateTime createdAt;
  final DateTime updatedAt;
  final List<TechWord> relatedTechWords;

  CareerProfile({
    required this.id,
    required this.title,
    required this.description,
    required this.skills,
    required this.education,
    required this.salaryRange,
    required this.jobOutlook,
    required this.createdAt,
    required this.updatedAt,
    this.relatedTechWords = const [],
  });

  factory CareerProfile.fromJson(Map<String, dynamic> json) {
    return CareerProfile(
      id: json['id'],
      title: json['title'],
      description: json['description'],
      skills: json['skills'],
      education: json['education'],
      salaryRange: json['salary_range'],
      jobOutlook: json['job_outlook'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      relatedTechWords: (json['related_tech_words'] as List?)
          ?.map((word) => TechWord.fromJson(word))
          .toList() ?? [],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'skills': skills,
      'education': education,
      'salary_range': salaryRange,
      'job_outlook': jobOutlook,
    };
  }
} 
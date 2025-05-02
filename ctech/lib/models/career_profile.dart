import 'tech_word.dart';

class CareerProfile {
  final int id;
  final String title;
  final String description;
  final String skills;
  final String education;
  final String salaryRange;
  final String jobOutlook;
  final String createdAt;
  final String updatedAt;
  final List<TechWord> relatedTechWords;
  final List<String> applications;
  final String imagePath;
  final String? videoPath;
  final String? audioPath;

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
    this.applications = const [],
    required this.imagePath,
    this.videoPath,
    this.audioPath,
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
      createdAt: json['created_at']?.toString() ?? '',
      updatedAt: json['updated_at']?.toString() ?? '',
      relatedTechWords: (json['related_tech_words'] as List?)
          ?.map((word) => TechWord.fromJson(word))
          .toList() ?? [],
      applications: (json['applications'] as List?)?.cast<String>() ?? [],
      imagePath: json['image_path'] ?? '',
      videoPath: json['video_path'],
      audioPath: json['audio_path'],
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
      'created_at': createdAt,
      'updated_at': updatedAt,
      'applications': applications,
      'image_path': imagePath,
      'video_path': videoPath,
      'audio_path': audioPath,
      'related_tech_words': relatedTechWords.map((word) => word.toJson()).toList(),
    };
  }
} 
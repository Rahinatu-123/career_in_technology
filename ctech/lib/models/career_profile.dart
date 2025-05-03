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
    required this.imagePath,
    this.videoPath,
    this.audioPath,
  });

  factory CareerProfile.fromJson(Map<String, dynamic> json) {
    // Validate required fields
    final requiredFields = ['id', 'title', 'description', 'skills', 'education', 'salary_range', 'job_outlook'];
    for (final field in requiredFields) {
      if (json[field] == null) {
        throw FormatException('Missing required field: $field');
      }
    }

    return CareerProfile(
      id: json['id'] as int,
      title: json['title'] as String,
      description: json['description'] as String,
      skills: json['skills'] as String,
      education: json['education'] as String,
      salaryRange: json['salary_range'] as String,
      jobOutlook: json['job_outlook'] as String,
      createdAt: json['created_at']?.toString() ?? DateTime.now().toIso8601String(),
      updatedAt: json['updated_at']?.toString() ?? DateTime.now().toIso8601String(),
      imagePath: json['image_path']?.toString() ?? '',
      videoPath: json['video_path']?.toString(),
      audioPath: json['audio_path']?.toString(),
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
      'image_path': imagePath,
      'video_path': videoPath,
      'audio_path': audioPath,
    };
  }

  // Helper method to get formatted skills list
  List<String> get skillsList => skills.split(',').map((s) => s.trim()).toList();

  // Helper method to check if career has media content
  bool get hasMedia => videoPath != null || audioPath != null;

  // Helper method to get formatted salary range
  String get formattedSalaryRange {
    if (salaryRange.isEmpty) return 'Not specified';
    return salaryRange;
  }

  // Helper method to get formatted job outlook
  String get formattedJobOutlook {
    if (jobOutlook.isEmpty) return 'Not specified';
    return jobOutlook;
  }
} 
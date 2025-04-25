class CareerProfile {
  final String id;
  final String title;
  final String description;
  final List<String> skills;
  final List<String> applications;
  final String imagePath;
  final String? videoPath;
  final String? audioPath;

  CareerProfile({
    required this.id,
    required this.title,
    required this.description,
    required this.skills,
    required this.applications,
    required this.imagePath,
    this.videoPath,
    this.audioPath,
  });
} 
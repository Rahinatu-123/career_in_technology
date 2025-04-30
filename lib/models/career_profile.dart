class CareerProfile {
  final String id;
  final String title;
  final String description;
  final String imageUrl;
  final List<String> skills;
  final List<String> responsibilities;
  final List<String> requirements;
  final double averageSalary;
  final String salaryCurrency;
  final List<String> relatedCareers;
  final int growthRate; // Percentage growth rate
  final String educationLevel;
  final int yearsOfExperience;
  final bool isSaved;

  CareerProfile({
    required this.id,
    required this.title,
    required this.description,
    required this.imageUrl,
    required this.skills,
    required this.responsibilities,
    required this.requirements,
    required this.averageSalary,
    required this.salaryCurrency,
    required this.relatedCareers,
    required this.growthRate,
    required this.educationLevel,
    required this.yearsOfExperience,
    this.isSaved = false,
  });

  factory CareerProfile.fromJson(Map<String, dynamic> json) {
    return CareerProfile(
      id: json['id'] as String,
      title: json['title'] as String,
      description: json['description'] as String,
      imageUrl: json['imageUrl'] as String,
      skills: List<String>.from(json['skills'] as List),
      responsibilities: List<String>.from(json['responsibilities'] as List),
      requirements: List<String>.from(json['requirements'] as List),
      averageSalary: (json['averageSalary'] as num).toDouble(),
      salaryCurrency: json['salaryCurrency'] as String,
      relatedCareers: List<String>.from(json['relatedCareers'] as List),
      growthRate: json['growthRate'] as int,
      educationLevel: json['educationLevel'] as String,
      yearsOfExperience: json['yearsOfExperience'] as int,
      isSaved: json['isSaved'] as bool? ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'imageUrl': imageUrl,
      'skills': skills,
      'responsibilities': responsibilities,
      'requirements': requirements,
      'averageSalary': averageSalary,
      'salaryCurrency': salaryCurrency,
      'relatedCareers': relatedCareers,
      'growthRate': growthRate,
      'educationLevel': educationLevel,
      'yearsOfExperience': yearsOfExperience,
      'isSaved': isSaved,
    };
  }

  CareerProfile copyWith({
    String? id,
    String? title,
    String? description,
    String? imageUrl,
    List<String>? skills,
    List<String>? responsibilities,
    List<String>? requirements,
    double? averageSalary,
    String? salaryCurrency,
    List<String>? relatedCareers,
    int? growthRate,
    String? educationLevel,
    int? yearsOfExperience,
    bool? isSaved,
  }) {
    return CareerProfile(
      id: id ?? this.id,
      title: title ?? this.title,
      description: description ?? this.description,
      imageUrl: imageUrl ?? this.imageUrl,
      skills: skills ?? this.skills,
      responsibilities: responsibilities ?? this.responsibilities,
      requirements: requirements ?? this.requirements,
      averageSalary: averageSalary ?? this.averageSalary,
      salaryCurrency: salaryCurrency ?? this.salaryCurrency,
      relatedCareers: relatedCareers ?? this.relatedCareers,
      growthRate: growthRate ?? this.growthRate,
      educationLevel: educationLevel ?? this.educationLevel,
      yearsOfExperience: yearsOfExperience ?? this.yearsOfExperience,
      isSaved: isSaved ?? this.isSaved,
    );
  }

  String get formattedSalary {
    return '$salaryCurrency ${averageSalary.toStringAsFixed(0)}';
  }

  String get growthRateText {
    return '$growthRate% growth';
  }

  String get experienceText {
    if (yearsOfExperience == 0) {
      return 'Entry Level';
    } else if (yearsOfExperience <= 2) {
      return '$yearsOfExperience year${yearsOfExperience == 1 ? '' : 's'}';
    } else if (yearsOfExperience <= 5) {
      return '$yearsOfExperience years (Mid-level)';
    } else {
      return '$yearsOfExperience years (Senior)';
    }
  }
} 
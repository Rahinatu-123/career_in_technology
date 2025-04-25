class User {
  final String id;
  final String email;
  final String firstName;
  final String lastName;
  final String phoneNumber;
  final String? profileImage;
  final List<String> savedCareers;
  final List<String> completedQuizzes;

  User({
    required this.id,
    required this.email,
    required this.firstName,
    required this.lastName,
    required this.phoneNumber,
    this.profileImage,
    this.savedCareers = const [],
    this.completedQuizzes = const [],
  });

  String get fullName => '$firstName $lastName';

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      email: json['email'],
      firstName: json['firstName'],
      lastName: json['lastName'],
      phoneNumber: json['phoneNumber'],
      profileImage: json['profileImage'],
      savedCareers: List<String>.from(json['savedCareers'] ?? []),
      completedQuizzes: List<String>.from(json['completedQuizzes'] ?? []),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'email': email,
      'firstName': firstName,
      'lastName': lastName,
      'phoneNumber': phoneNumber,
      'profileImage': profileImage,
      'savedCareers': savedCareers,
      'completedQuizzes': completedQuizzes,
    };
  }
} 
import 'dart:convert';
import 'dart:io';
import 'dart:async';
import 'package:http/http.dart' as http;
import '../models/career_profile.dart';
import '../models/tech_word.dart';
import '../models/inspiring_story.dart';

class ApiService {
  // TODO: Change this to your actual API server URL when deploying
  static const String baseUrl = 'http://localhost/ctech-web/api';
  // static const String baseUrl = 'http://localhost/ctech-web/api'; // iOS simulator
  // static const String baseUrl = 'https://your-production-server.com/api'; // Production

  final http.Client _client;
  final Duration timeout = const Duration(seconds: 10);

  ApiService({http.Client? client}) : _client = client ?? http.Client();

  // Helper method to handle common error cases
  String _handleError(dynamic error) {
    if (error is http.ClientException) {
      return 'Failed to connect to server. Please check your internet connection.';
    } else if (error is TimeoutException) {
      return 'Connection timed out. Please try again.';
    } else if (error is SocketException) {
      return 'Network error. Please check your internet connection.';
    } else {
      return 'An unexpected error occurred. Please try again.';
    }
  }

  // Career Profiles API
  Future<List<CareerProfile>> getCareers({
    String? search,
    double? minSalary,
    String? educationLevel,
  }) async {
    final queryParams = <String, String>{};
    if (search != null) queryParams['search'] = search;
    if (minSalary != null) queryParams['min_salary'] = minSalary.toString();
    if (educationLevel != null) queryParams['education_level'] = educationLevel;

    final uri = Uri.parse('$baseUrl/career_profiles.php').replace(queryParameters: queryParams);
    final response = await _client.get(uri);

    if (response.statusCode == 200) {
      final List<dynamic> data = json.decode(response.body);
      return data.map((json) => CareerProfile.fromJson(json)).toList();
    } else {
      throw Exception('Failed to load careers');
    }
  }

  Future<CareerProfile> createCareer(CareerProfile career) async {
    final response = await _client.post(
      Uri.parse('$baseUrl/career_profiles.php'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode(career.toJson()),
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return CareerProfile.fromJson({...career.toJson(), 'id': data['id']});
    } else {
      throw Exception('Failed to create career');
    }
  }

  // Tech Words API
  Future<List<TechWord>> getTechWords() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/tech_words.php'));
      if (response.statusCode == 200) {
        final List<dynamic> data = json.decode(response.body);
        return data.map((json) => TechWord.fromJson(json)).toList();
      } else {
        throw Exception('Failed to load tech words');
      }
    } catch (e) {
      throw Exception('Error: $e');
    }
  }

  Future<TechWord> createTechWord(TechWord word) async {
    final response = await _client.post(
      Uri.parse('$baseUrl/tech_words.php'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode(word.toJson()),
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return TechWord.fromJson({...word.toJson(), 'id': data['id']});
    } else {
      throw Exception('Failed to create tech word');
    }
  }

  // Update methods
  Future<void> updateCareer(CareerProfile career) async {
    final response = await _client.put(
      Uri.parse('$baseUrl/career_profiles.php'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode({...career.toJson(), 'id': career.id}),
    );

    if (response.statusCode != 200) {
      throw Exception('Failed to update career');
    }
  }

  Future<void> updateTechWord(TechWord word) async {
    final response = await _client.put(
      Uri.parse('$baseUrl/tech_words.php'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode(word.toJson()),
    );

    if (response.statusCode != 200) {
      throw Exception('Failed to update tech word');
    }
  }

  // Delete methods
  Future<void> deleteCareer(int id) async {
    final response = await _client.delete(
      Uri.parse('$baseUrl/career_profiles.php?id=$id'),
    );

    if (response.statusCode != 200) {
      throw Exception('Failed to delete career');
    }
  }

  Future<void> deleteTechWord(int id) async {
    final response = await _client.delete(
      Uri.parse('$baseUrl/tech_words.php?id=$id'),
    );

    if (response.statusCode != 200) {
      throw Exception('Failed to delete tech word');
    }
  }

  // Inspiring Stories
  Future<List<InspiringStory>> fetchInspiringStories() async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/inspiring_stories.php'),
        headers: {'Content-Type': 'application/json'},
      ).timeout(timeout);

      if (response.statusCode == 200) {
        final List<dynamic> data = json.decode(response.body);
        return data.map((story) => InspiringStory(
          id: story['id'].toString(),
          name: story['name'],
          role: story['role'],
          company: story['company'],
          imagePath: story['image_path'],
          shortQuote: story['short_quote'],
          fullStory: story['full_story'],
          audioPath: story['audio_path'],
          relatedCareers: List<String>.from(story['related_careers'] ?? []),
        )).toList();
      } else {
        throw Exception('Server returned status code: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception(_handleError(e));
    }
  }

  // Authentication
  Future<bool> verifyOTP(String email, String otp) async {
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/verify_otp.php'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'email': email, 'otp': otp}),
      );
      final data = jsonDecode(response.body);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to verify OTP: $e');
    }
  }

  Future<bool> sendOTP(String email) async {
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/send_otp.php'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'email': email}),
      );
      final data = jsonDecode(response.body);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to send OTP: $e');
    }
  }

  // Fetch Career Profiles
  Future<List<CareerProfile>> getCareerProfiles({String? search}) async {
    try {
      String url = '$baseUrl/career_profiles.php';
      if (search != null && search.isNotEmpty) {
        url += '?search=$search';
      }
      final response = await http.get(Uri.parse(url));
      if (response.statusCode == 200) {
        final Map<String, dynamic> responseData = json.decode(response.body);
        if (responseData['success'] == true) {
          final List<dynamic> data = responseData['data'];
          return data.map((json) => CareerProfile.fromJson(json)).toList();
        } else {
          throw Exception(responseData['error'] ?? 'Failed to load careers');
        }
      } else {
        throw Exception('Failed to load careers');
      }
    } catch (e) {
      throw Exception('Error: $e');
    }
  }

  // Fetch Inspiring Stories
  Future<List<dynamic>> getInspiringStories() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/inspiring_stories.php'));
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('Failed to load stories');
      }
    } catch (e) {
      throw Exception('Error: $e');
    }
  }

  // Get Tech Words by Career
  Future<List<TechWord>> getTechWordsByCareer(int careerId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/tech_words.php?career_id=$careerId')
      );
      if (response.statusCode == 200) {
        final List<dynamic> data = json.decode(response.body);
        return data.map((json) => TechWord.fromJson(json)).toList();
      } else {
        throw Exception('Failed to load tech words for career');
      }
    } catch (e) {
      throw Exception('Error: $e');
    }
  }

  // Signup API
  Future<Map<String, dynamic>> signup({
    required String firstname,
    required String lastname,
    required String email,
    required String password,
  }) async {
    final response = await _client.post(
      Uri.parse('$baseUrl/signup.php'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode({
        'firstname': firstname,
        'lastname': lastname,
        'email': email,
        'password': password,
      }),
    );
    return json.decode(response.body);
  }

  // Login API
  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final response = await _client.post(
      Uri.parse('$baseUrl/login.php'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode({
        'email': email,
        'password': password,
      }),
    );
    return json.decode(response.body);
  }
} 
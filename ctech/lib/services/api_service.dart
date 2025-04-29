import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/career_profile.dart';
import '../models/tech_word.dart';

class ApiService {
  static const String baseUrl = 'http://localhost/ctech-web/api';
  final http.Client _client;

  ApiService({http.Client? client}) : _client = client ?? http.Client();

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
  Future<List<TechWord>> getTechWords({int? careerId}) async {
    final queryParams = <String, String>{};
    if (careerId != null) queryParams['career_id'] = careerId.toString();

    final uri = Uri.parse('$baseUrl/tech_words.php').replace(queryParameters: queryParams);
    final response = await _client.get(uri);

    if (response.statusCode == 200) {
      final List<dynamic> data = json.decode(response.body);
      return data.map((json) => TechWord.fromJson(json)).toList();
    } else {
      throw Exception('Failed to load tech words');
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
} 
import 'dart:convert';
import 'dart:io';
import 'dart:async';
import 'package:http/http.dart' as http;
import '../models/career_profile.dart';
import '../models/tech_word.dart';
import '../models/inspiring_story.dart';

class ApiService {
  // For Android emulator:
  static const String baseUrl = 'http://10.0.2.2:8000/ctech-web/api';
  // For web/desktop, use:
  // static const String baseUrl = 'http://localhost:8000/ctech-web/api';

  // Common headers for all requests
  static const Map<String, String> _headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  // Timeout configuration
  static const Duration _connectionTimeout = Duration(seconds: 15);
  static const Duration _receiveTimeout = Duration(seconds: 15);
  static const int _maxRetries = 3;
  static const Duration _retryDelay = Duration(seconds: 2);

  final http.Client _client;
  
  ApiService({http.Client? client}) : _client = client ?? http.Client();

  // Helper method to handle common error cases
  String _handleError(dynamic error) {
    if (error is http.ClientException) {
      return 'Failed to connect to server. Please check your internet connection and make sure XAMPP is running.';
    } else if (error is TimeoutException) {
      return 'Connection timed out. Please try again.';
    } else if (error is SocketException) {
      return 'Network error. Please check your internet connection and make sure XAMPP is running.';
    } else {
      return 'An unexpected error occurred. Please try again.';
    }
  }

  // Helper method to handle timeouts and retries
  Future<T> _withTimeoutAndRetry<T>(Future<T> Function() operation) async {
    int attempts = 0;
    while (true) {
      try {
        return await operation().timeout(_receiveTimeout);
      } catch (e) {
        attempts++;
        if (attempts >= _maxRetries) {
          rethrow;
        }
        await Future.delayed(_retryDelay);
      }
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
      final response = await _client.get(
        Uri.parse('$baseUrl/tech_words.php'),
        headers: {'Content-Type': 'application/json'},
      ).timeout(_connectionTimeout);

      if (response.statusCode == 200) {
        final List<dynamic> data = json.decode(response.body);
        return data.map((json) => TechWord.fromJson(json)).toList();
      } else {
        throw Exception('Failed to load tech words');
      }
    } catch (e) {
      throw Exception(_handleError(e));
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

  // Inspiring Stories API
  Future<List<InspiringStory>> fetchInspiringStories() async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/inspiring_stories.php'),
        headers: {'Content-Type': 'application/json'},
      ).timeout(_connectionTimeout);

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

  // Authentication API
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
      final response = await _client.get(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
      ).timeout(_connectionTimeout);

      if (response.statusCode == 200) {
        final List<dynamic> data = json.decode(response.body);
        return data.map((json) => CareerProfile.fromJson(json)).toList();
      } else {
        throw Exception('Failed to load careers');
      }
    } catch (e) {
      throw Exception(_handleError(e));
    }
  }

  // Fetch Inspiring Stories
  Future<List<dynamic>> getInspiringStories() async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/inspiring_stories.php'),
        headers: {'Content-Type': 'application/json'},
      ).timeout(_connectionTimeout);

      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('Failed to load stories');
      }
    } catch (e) {
      throw Exception(_handleError(e));
    }
  }

  // Get Tech Words by Career
  Future<List<TechWord>> getTechWordsByCareer(int careerId) async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/tech_words.php?career_id=$careerId'),
        headers: {'Content-Type': 'application/json'},
      ).timeout(_connectionTimeout);

      if (response.statusCode == 200) {
        final List<dynamic> data = json.decode(response.body);
        return data.map((json) => TechWord.fromJson(json)).toList();
      } else {
        throw Exception('Failed to load tech words');
      }
    } catch (e) {
      throw Exception(_handleError(e));
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

  // Login API with improved timeout handling
  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    print('Attempting login for email: $email');
    return _withTimeoutAndRetry(() async {
      try {
        print('Sending login request to: $baseUrl/login.php');
        final response = await _client.post(
          Uri.parse('$baseUrl/login.php'),
          headers: _headers,
          body: jsonEncode({
            'email': email,
            'password': password,
          }),
        ).timeout(_connectionTimeout);

        print('Received response with status: ${response.statusCode}');
        print('Response body: ${response.body}');

        if (response.statusCode == 200) {
          final data = jsonDecode(response.body);
          
          if (data['success'] == true && data['user'] != null) {
            print('Login successful');
            return {
              'success': true,
              'user': data['user'],
            };
          } else if (data['error'] != null) {
            print('Login failed: ${data['error']}');
            return {
              'success': false,
              'error': data['error'],
            };
          } else {
            print('Invalid response format');
            return {
              'success': false,
              'error': 'Invalid response format',
            };
          }
        } else if (response.statusCode == 401) {
          print('Invalid credentials');
          return {
            'success': false,
            'error': 'Invalid email or password',
          };
        } else if (response.statusCode == 404) {
          print('Login endpoint not found');
          return {
            'success': false,
            'error': 'Login endpoint not found',
          };
        } else {
          print('Server error: ${response.statusCode}');
          return {
            'success': false,
            'error': 'Server error: ${response.statusCode}',
          };
        }
      } catch (e) {
        print('Login error: $e');
        if (e is TimeoutException) {
          return {
            'success': false,
            'error': 'Connection timed out. Please check your internet connection and try again.',
          };
        }
        return {
          'success': false,
          'error': _handleError(e),
        };
      }
    });
  }
}

class ContactService {
  // Environment-specific base URLs
  static const String _devBaseUrl = 'http://10.0.2.2/contactmgt/actions';
  static const String _prodBaseUrl = 'https://apps.ashesi.edu.gh/contactmgt/actions';
  
  // Use the appropriate base URL based on environment
  final String baseUrl = const bool.fromEnvironment('dart.vm.product') 
      ? _prodBaseUrl 
      : _devBaseUrl;

  final http.Client _client;
  final Duration timeout = const Duration(seconds: 10);

  ContactService({http.Client? client}) : _client = client ?? http.Client();

  // Common headers for all requests
  static const Map<String, String> _headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  // Retry configuration
  static const int _maxRetries = 3;
  static const Duration _retryDelay = Duration(seconds: 2);

  // Helper method to retry failed requests
  Future<T> _retry<T>(Future<T> Function() operation) async {
    int attempts = 0;
    while (true) {
      try {
        return await operation();
      } catch (e) {
        attempts++;
        if (attempts >= _maxRetries) {
          rethrow;
        }
        await Future.delayed(_retryDelay);
      }
    }
  }

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

  Future<List<Map<String, dynamic>>> getAllContacts() async {
    return _retry(() async {
      try {
        final response = await _client.get(
          Uri.parse('$baseUrl/get_all_contact_mob'),
          headers: _headers,
        ).timeout(timeout);

        if (response.statusCode == 200) {
          final dynamic decodedData = json.decode(response.body);
          
          if (decodedData is List) {
            return List<Map<String, dynamic>>.from(decodedData);
          } else if (decodedData is Map && decodedData.containsKey('data')) {
            final List<dynamic> data = decodedData['data'];
            return List<Map<String, dynamic>>.from(data);
          }
          return [];
        } else {
          throw Exception('Failed to load contacts: ${response.statusCode}');
        }
      } catch (e) {
        throw Exception(_handleError(e));
      }
    });
  }

  Future<Map<String, dynamic>> getSingleContact(int contactId) async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/get_a_contact_mob?contid=$contactId'),
        headers: _headers,
      ).timeout(timeout);

      if (response.statusCode == 200) {
        final dynamic decodedData = json.decode(response.body);
        
        if (decodedData is List) {
          return decodedData.isNotEmpty ? Map<String, dynamic>.from(decodedData[0]) : {};
        } else if (decodedData is Map) {
          return Map<String, dynamic>.from(decodedData);
        }
        return {};
      } else {
        throw Exception('Failed to load contact: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception(_handleError(e));
    }
  }

  // Helper method to validate response
  Map<String, dynamic> _validateResponse(http.Response response) {
    if (response.statusCode == 200) {
      try {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': 'Operation successful',
          'data': data,
        };
      } catch (e) {
        return {
          'success': false,
          'error': 'Invalid response format',
        };
      }
    } else if (response.statusCode == 401) {
      return {
        'success': false,
        'error': 'Unauthorized access',
      };
    } else if (response.statusCode == 404) {
      return {
        'success': false,
        'error': 'Resource not found',
      };
    } else {
      return {
        'success': false,
        'error': 'Server error: ${response.statusCode}',
      };
    }
  }

  Future<Map<String, dynamic>> addContact({
    required String fullName,
    required String phoneNumber,
  }) async {
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/add_contact_mob'),
        headers: _headers,
        body: json.encode({
          'ufullname': fullName,
          'uphonename': phoneNumber,
        }),
      ).timeout(timeout);

      return _validateResponse(response);
    } catch (e) {
      return {
        'success': false,
        'error': _handleError(e),
      };
    }
  }

  Future<Map<String, dynamic>> updateContact({
    required int id,
    required String fullName,
    required String phoneNumber,
  }) async {
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/update_contact'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'cname': fullName,
          'cnum': phoneNumber,
          'cid': id,
        }),
      ).timeout(timeout);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': 'Contact updated successfully',
          'data': data,
        };
      } else {
        return {
          'success': false,
          'error': 'Failed to update contact: ${response.statusCode}',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'error': _handleError(e),
      };
    }
  }

  Future<Map<String, dynamic>> deleteContact(int contactId) async {
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/delete_contact'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'cid': contactId}),
      ).timeout(timeout);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': 'Contact deleted successfully',
          'data': data,
        };
      } else {
        return {
          'success': false,
          'error': 'Failed to delete contact: ${response.statusCode}',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'error': _handleError(e),
      };
    }
  }
} 
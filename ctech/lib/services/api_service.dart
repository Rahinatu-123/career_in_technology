import 'dart:convert';
import 'dart:io';
import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class ApiService {
  // Base URL configuration for different platforms
  String get baseUrl {
    if (kIsWeb) {
      // For web
      return 'http://localhost/ctech-web/api';
    } else if (Platform.isAndroid) {
      // For Android emulator
      return 'http://10.0.2.2/ctech-web/api';
    } else {
      // For iOS simulator or physical devices
      return 'http://localhost/ctech-web/api';
    }
  }

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
    debugPrint('API Error: $error');
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
        debugPrint('API retry attempt $attempts: $e');
        if (attempts >= _maxRetries) {
          rethrow;
        }
        await Future.delayed(_retryDelay);
      }
    }
  }

  // Login API with improved debugging
  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    debugPrint('Attempting login for email: $email');
    final loginUrl = '$baseUrl/login.php';
    debugPrint('Login URL: $loginUrl');
    
    return _withTimeoutAndRetry(() async {
      try {
        final response = await _client.post(
          Uri.parse(loginUrl),
          headers: _headers,
          body: jsonEncode({
            'email': email,
            'password': password,
          }),
        ).timeout(_connectionTimeout);

        debugPrint('Received response with status: ${response.statusCode}');
        
        // Only log the first 500 characters to avoid flooding logs with large responses
        final truncatedBody = response.body.length > 500 
            ? '${response.body.substring(0, 500)}...(truncated)'
            : response.body;
        debugPrint('Response body: $truncatedBody');

        if (response.statusCode == 200) {
          try {
            final data = jsonDecode(response.body);
            
            if (data['success'] == true && data['user'] != null) {
              debugPrint('Login successful');
              return {
                'success': true,
                'user': data['user'],
              };
            } else if (data['error'] != null) {
              debugPrint('Login failed: ${data['error']}');
              return {
                'success': false,
                'error': data['error'],
              };
            } else {
              debugPrint('Invalid response format');
              return {
                'success': false,
                'error': 'Invalid response format',
              };
            }
          } catch (e) {
            debugPrint('JSON parsing error: $e');
            return {
              'success': false,
              'error': 'Failed to process server response',
            };
          }
        } else if (response.statusCode == 401) {
          debugPrint('Invalid credentials');
          return {
            'success': false,
            'error': 'Invalid email or password',
          };
        } else if (response.statusCode == 404) {
          debugPrint('Login endpoint not found');
          return {
            'success': false,
            'error': 'Login endpoint not found. Check API path and server configuration.',
          };
        } else {
          debugPrint('Server error: ${response.statusCode}');
          return {
            'success': false,
            'error': 'Server error: ${response.statusCode}. Please try again later.',
          };
        }
      } catch (e) {
        debugPrint('Login error: $e');
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

  // Add a test connection method to verify API connectivity
  Future<bool> testConnection() async {
    try {
      final response = await _client.get(
        Uri.parse('$baseUrl/test_connection.php'),
        headers: _headers,
      ).timeout(const Duration(seconds: 5));
      
      debugPrint('Test connection result: ${response.statusCode}, ${response.body}');
      return response.statusCode == 200;
    } catch (e) {
      debugPrint('Test connection failed: $e');
      return false;
    }
  }
}
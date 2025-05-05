import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:developer' as developer;

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      home: Scaffold(
        appBar: AppBar(title: const Text('API Configuration Check')),
        body: const ConfigurationChecker(),
      ),
    );
  }
}

class ConfigurationChecker extends StatefulWidget {
  const ConfigurationChecker({super.key});

  @override
  State<ConfigurationChecker> createState() => _ConfigurationCheckerState();
}

class _ConfigurationCheckerState extends State<ConfigurationChecker> {
  String _status = 'Checking configuration...';
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _checkConfiguration();
  }

  Future<void> _checkConfiguration() async {
    try {
      // Check test connection endpoint
      final testConnectionResponse = await http.get(
        Uri.parse('http://20.251.152.247/career_in_technology/ctech-web/api/test_connection.php'),
        headers: {'Accept': 'application/json'},
      );

      developer.log('Test connection response: ${testConnectionResponse.statusCode}');
      developer.log('Test connection body: ${testConnectionResponse.body}');

      if (testConnectionResponse.statusCode != 200) {
        throw Exception('Test connection failed: ${testConnectionResponse.statusCode}');
      }

      // Check login endpoint
      final loginResponse = await http.post(
        Uri.parse('http://20.251.152.247/career_in_technology/ctech-web/api/login.php'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': 'rahinatulawal02@gmail.com',
          'password': 'admin123',
        }),
      );

      developer.log('Login response: ${loginResponse.statusCode}');
      developer.log('Login body: ${loginResponse.body}');

      if (loginResponse.statusCode != 200) {
        throw Exception('Login test failed: ${loginResponse.statusCode}');
      }

      final loginData = jsonDecode(loginResponse.body);
      if (!loginData['success']) {
        throw Exception('Login test failed: ${loginData['error']}');
      }

      setState(() {
        _status = 'All checks passed successfully!';
        _isLoading = false;
      });
    } catch (e) {
      developer.log('Configuration check failed: $e');
      setState(() {
        _status = 'Error: $e';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          if (_isLoading)
            const CircularProgressIndicator()
          else
            const Icon(Icons.check_circle, color: Colors.green, size: 48),
          const SizedBox(height: 16),
          Text(
            _status,
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 16),
          ),
        ],
      ),
    );
  }
} 
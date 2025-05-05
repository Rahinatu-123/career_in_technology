import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:developer' as developer;

class TestConnection extends StatefulWidget {
  const TestConnection({super.key});

  @override
  State<TestConnection> createState() => _TestConnectionState();
}

class _TestConnectionState extends State<TestConnection> {
  String _status = 'Testing connection...';
  bool _isLoading = true;
  List<String> _testResults = [];

  @override
  void initState() {
    super.initState();
    _testAllEndpoints();
  }

  Future<Map<String, dynamic>> _testEndpoint(String url) async {
    try {
      developer.log('Testing URL: $url');
      final response = await http.get(
        Uri.parse(url),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ).timeout(const Duration(seconds: 10));

      developer.log('Response for $url: ${response.statusCode}');
      developer.log('Response body: ${response.body}');

      return {
        'url': url,
        'statusCode': response.statusCode,
        'body': response.body,
        'success': response.statusCode == 200,
      };
    } catch (e) {
      developer.log('Error for $url: $e');
      return {
        'url': url,
        'error': e.toString(),
        'success': false,
      };
    }
  }

  Future<void> _testAllEndpoints() async {
    setState(() {
      _isLoading = true;
      _status = 'Testing multiple endpoints...';
      _testResults = [];
    });

    final urls = [
      'http://20.251.152.247/career_in_technology/ctech-web/api/test_connection.php',
      'http://20.251.152.247/htdocs/career_in_technology/ctech-web/api/test_connection.php',
      'http://20.251.152.247/xampp/htdocs/career_in_technology/ctech-web/api/test_connection.php',
      'http://20.251.152.247/career_in_technology/api/test_connection.php',
      'http://20.251.152.247/api/test_connection.php',
    ];

    for (final url in urls) {
      final result = await _testEndpoint(url);
      final resultText = 'URL: ${result['url']}\n'
          'Status: ${result['success'] ? 'Success' : 'Failed'}\n'
          '${result['statusCode'] != null ? 'Status Code: ${result['statusCode']}\n' : ''}'
          '${result['body'] != null ? 'Response: ${result['body']}\n' : ''}'
          '${result['error'] != null ? 'Error: ${result['error']}\n' : ''}'
          '----------------------------------------';
      
      setState(() {
        _testResults.add(resultText);
      });
    }

    setState(() {
      _isLoading = false;
      _status = 'Test completed';
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Connection Test'),
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              if (_isLoading)
                const Column(
                  children: [
                    CircularProgressIndicator(),
                    SizedBox(height: 16),
                    Text('Testing multiple endpoints...'),
                  ],
                )
              else
                Column(
                  children: [
                    Text(
                      _status,
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 20),
                    ..._testResults.map((result) => Padding(
                          padding: const EdgeInsets.only(bottom: 16.0),
                          child: Container(
                            padding: const EdgeInsets.all(16.0),
                            decoration: BoxDecoration(
                              border: Border.all(color: Colors.grey),
                              borderRadius: BorderRadius.circular(8.0),
                            ),
                            child: Text(
                              result,
                              style: const TextStyle(fontSize: 14),
                            ),
                          ),
                        )),
                  ],
                ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: _testAllEndpoints,
                child: const Text('Test Again'),
              ),
            ],
          ),
        ),
      ),
    );
  }
} 
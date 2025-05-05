import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:developer' as developer;

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  bool _isInitialized = false;
  String _errorMessage = '';

  @override
  void initState() {
    super.initState();
    developer.log('SplashScreen: initState called');
    _initializeApp();
  }

  Future<void> _initializeApp() async {
    developer.log('SplashScreen: Starting initialization');
    try {
      developer.log('SplashScreen: Testing API connection');
      // Test API connection
      final response = await http.get(
        Uri.parse('http://20.251.152.247/career_in_technology/ctech-web/api/test_connection.php'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ).timeout(const Duration(seconds: 10));

      developer.log('SplashScreen: API response received - ${response.statusCode}');
      developer.log('SplashScreen: Response body - ${response.body}');

      if (response.statusCode == 200) {
        developer.log('SplashScreen: Connection successful, setting initialized to true');
        setState(() {
          _isInitialized = true;
        });
        // Navigate to login screen after a short delay
        developer.log('SplashScreen: Waiting 2 seconds before navigation');
        await Future.delayed(const Duration(seconds: 2));
        if (mounted) {
          developer.log('SplashScreen: Navigating to login screen');
          Navigator.pushReplacementNamed(context, '/login');
        } else {
          developer.log('SplashScreen: Widget not mounted, skipping navigation');
        }
      } else {
        developer.log('SplashScreen: Connection failed with status ${response.statusCode}');
        setState(() {
          _errorMessage = 'Server returned status code: ${response.statusCode}';
        });
      }
    } catch (e) {
      developer.log('SplashScreen: Error during initialization - $e');
      setState(() {
        _errorMessage = 'Failed to connect to server: $e';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    developer.log('SplashScreen: Building widget');
    return Scaffold(
      backgroundColor: const Color(0xFF0A2A36),
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'CTECH',
              style: TextStyle(
                color: Colors.white,
                fontSize: 44,
                fontWeight: FontWeight.bold,
                letterSpacing: 4,
              ),
            ),
            const SizedBox(height: 8),
            // Accent underline
            Container(
              width: 80,
              height: 5,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(3),
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 28),
            const Text(
              'Ignite Your Passion For Tech',
              style: TextStyle(
                color: Colors.white70,
                fontSize: 16,
                fontWeight: FontWeight.w400,
                letterSpacing: 1,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 36),
            if (!_isInitialized && _errorMessage.isEmpty)
              const CircularProgressIndicator(
                color: Colors.white,
              )
            else if (_errorMessage.isNotEmpty)
              Column(
                children: [
                  const Icon(
                    Icons.error_outline,
                    color: Colors.red,
                    size: 48,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    _errorMessage,
                    style: const TextStyle(
                      color: Colors.red,
                      fontSize: 14,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: _initializeApp,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: const Color(0xFF0A2A36),
                      padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(25),
                      ),
                    ),
                    child: const Text(
                      'Retry',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
            const SizedBox(height: 24),
            // Get Started Button
            if (_isInitialized)
              ElevatedButton(
                onPressed: () {
                  developer.log('SplashScreen: Get Started button pressed');
                  Navigator.pushReplacementNamed(context, '/login');
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: const Color(0xFF0A2A36),
                  padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(25),
                  ),
                ),
                child: const Text(
                  'Get Started',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
} 
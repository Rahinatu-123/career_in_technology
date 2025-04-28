import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'screens/splash_screen.dart';
import 'screens/home_page.dart';
import 'screens/career_profiles_page.dart';
import 'screens/career_quiz_page.dart';
import 'screens/inspiring_stories_page.dart';
import 'screens/login_screen.dart';
import 'screens/signup_screen.dart';
import 'screens/personal_info_screen.dart';
import 'screens/forgot_password_screen.dart';
import 'screens/verify_otp_screen.dart';
import 'screens/reset_password_screen.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'CTech',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF2E7D32), // Green theme
          brightness: Brightness.light,
        ),
        useMaterial3: true,
        textTheme: GoogleFonts.poppinsTextTheme(),
      ),
      initialRoute: '/',
      routes: {
        '/': (context) => const SplashScreen(),
        '/login': (context) => const LoginScreen(),
        '/signup': (context) => const SignupScreen(),
        '/personal-info': (context) => PersonalInfoScreen(
          email: '',
          password: '',
        ),
        '/home': (context) => const HomePage(),
        '/career-profiles': (context) => const CareerProfilesPage(),
        '/career-quiz': (context) => const CareerQuizPage(),
        '/inspiring-stories': (context) => const InspiringStoriesPage(),
        '/forgot-password': (context) => const ForgotPasswordScreen(),
        '/verify-otp': (context) => VerifyOTPScreen(
          email: '',
        ),
        '/reset-password': (context) => const ResetPasswordScreen(
          email: '',
          otp: '',
        ),
        // Add other routes here as we create them
      },
    );
  }
}

Future<bool> verifyOTP(String email, String otp) async {
  final response = await http.post(
    Uri.parse('http://your-server.com/verify_otp.php'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({'email': email, 'otp': otp}),
  );
  final data = jsonDecode(response.body);
  return data['success'] == true;
}

Future<bool> sendOTP(String email) async {
  final response = await http.post(
    Uri.parse('http://your-server.com/send_otp.php'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({'email': email}),
  );
  final data = jsonDecode(response.body);
  return data['success'] == true;
}

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences.dart';
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
import 'screens/career_list_screen.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final prefs = await SharedPreferences.getInstance();
  final isLoggedIn = prefs.getBool('isLoggedIn') ?? false;
  
  runApp(MyApp(isLoggedIn: isLoggedIn));
}

class MyApp extends StatelessWidget {
  final bool isLoggedIn;

  const MyApp({super.key, required this.isLoggedIn});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Career in Technology',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.blue),
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
        '/career-profiles': (context) => const CareerListScreen(),
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
      },
    );
  }
}

// API endpoints for authentication
const String baseUrl = 'http://localhost/ctech-web/api';

Future<bool> verifyOTP(String email, String otp) async {
  final response = await http.post(
    Uri.parse('$baseUrl/verify_otp.php'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({'email': email, 'otp': otp}),
  );
  final data = jsonDecode(response.body);
  return data['success'] == true;
}

Future<bool> sendOTP(String email) async {
  final response = await http.post(
    Uri.parse('$baseUrl/send_otp.php'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({'email': email}),
  );
  final data = jsonDecode(response.body);
  return data['success'] == true;
}

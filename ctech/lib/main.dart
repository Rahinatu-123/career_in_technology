import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'providers/theme_provider.dart';
import 'screens/splash_screen.dart';
import 'screens/home_page.dart';
import 'screens/career_profiles_page.dart';
import 'screens/career_quiz_page.dart';
import 'screens/inspiring_stories_page.dart';
import 'screens/tech_words_page.dart';
import 'screens/login_screen.dart';
import 'screens/signup_screen.dart';
import 'screens/personal_info_screen.dart';
import 'screens/forgot_password_screen.dart';
import 'screens/verify_otp_screen.dart';
import 'screens/reset_password_screen.dart';
import 'screens/profile_page.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:developer' as developer;

void main() async {
  developer.log('Starting app initialization');
  WidgetsFlutterBinding.ensureInitialized();
  developer.log('Flutter binding initialized');
  
  runApp(
    ChangeNotifierProvider(
      create: (_) => ThemeProvider(),
      child: const MyApp(),
    ),
  );
  developer.log('App started');
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    developer.log('Building MyApp widget');
    final themeProvider = Provider.of<ThemeProvider>(context);
    
    return MaterialApp(
      title: 'CTech',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF0A2A36),
          brightness: Brightness.light,
        ),
        useMaterial3: true,
        textTheme: GoogleFonts.poppinsTextTheme(),
      ),
      darkTheme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF0A2A36),
          brightness: Brightness.dark,
        ),
        useMaterial3: true,
        textTheme: GoogleFonts.poppinsTextTheme(ThemeData.dark().textTheme),
      ),
      themeMode: themeProvider.isDarkMode ? ThemeMode.dark : ThemeMode.light,
      home: const SplashScreen(),
      routes: {
        '/home': (context) => const HomePage(),
        '/career-profiles': (context) => const CareerProfilesPage(),
        '/career-quiz': (context) => const CareerQuizPage(),
        '/inspiring-stories': (context) => const InspiringStoriesPage(),
        '/tech-words': (context) => const TechWordsPage(),
        '/login': (context) => const LoginScreen(),
        '/signup': (context) => const SignupScreen(),
        '/personal-info': (context) => PersonalInfoScreen(
          email: '',
          password: '',
        ),
        '/forgot-password': (context) => const ForgotPasswordScreen(),
        '/verify-otp': (context) => VerifyOTPScreen(
          email: '',
        ),
        '/reset-password': (context) => const ResetPasswordScreen(
          email: '',
          otp: '',
        ),
        '/profile': (context) => const ProfilePage(),
      },
    );
  }
}

// Base URL for API endpoints
const String baseUrl = 'http://20.251.152.247/career_in_technology/ctech-web/api';

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

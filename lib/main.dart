import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'providers/settings_provider.dart';
import 'data/career_profiles_data.dart';
import 'screens/splash_screen.dart';
import 'screens/home_page.dart';
import 'screens/career_profiles_page.dart';
import 'screens/career_quiz_page.dart';
import 'screens/inspiring_stories_page.dart';
import 'screens/login_screen.dart';
import 'screens/signup_screen.dart';
import 'screens/personal_info_screen.dart';
import 'screens/forgot_password_screen.dart';
import 'screens/settings_page.dart';
import 'screens/career_detail_page.dart';
import 'screens/about_page.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<SharedPreferences>(
      future: SharedPreferences.getInstance(),
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const MaterialApp(
            home: Scaffold(
              body: Center(
                child: CircularProgressIndicator(),
              ),
            ),
          );
        }

        final prefs = snapshot.data!;
        return MultiProvider(
          providers: [
            ChangeNotifierProvider(
              create: (_) => SettingsProvider(),
            ),
            ChangeNotifierProvider(
              create: (_) => CareerProfilesData(),
            ),
          ],
          child: Consumer<SettingsProvider>(
            builder: (context, settings, _) {
              final isDark = settings.isDarkMode;
              
              return MaterialApp(
                title: 'CTech',
                debugShowCheckedModeBanner: false,
                themeMode: isDark ? ThemeMode.dark : ThemeMode.light,
                theme: ThemeData.light().copyWith(
                  primaryColor: const Color(0xFF0A2A36),
                  scaffoldBackgroundColor: Colors.white,
                  appBarTheme: const AppBarTheme(
                    backgroundColor: Color(0xFF0A2A36),
                    foregroundColor: Colors.white,
                    elevation: 0,
                  ),
                  colorScheme: const ColorScheme.light(
                    primary: Color(0xFF0A2A36),
                    secondary: Color(0xFF1B3B4B),
                  ),
                  textTheme: GoogleFonts.poppinsTextTheme(ThemeData.light().textTheme),
                ),
                darkTheme: ThemeData.dark().copyWith(
                  primaryColor: const Color(0xFF0A2A36),
                  scaffoldBackgroundColor: const Color(0xFF15202B),
                  appBarTheme: const AppBarTheme(
                    backgroundColor: Color(0xFF1B2939),
                    foregroundColor: Colors.white,
                    elevation: 0,
                  ),
                  colorScheme: const ColorScheme.dark(
                    primary: Color(0xFF0A2A36),
                    secondary: Color(0xFF1B3B4B),
                    surface: Color(0xFF1B2939),
                    background: Color(0xFF15202B),
                    onSurface: Color(0xFFE7E9EA),
                  ),
                  cardColor: const Color(0xFF1B2939),
                  dialogTheme: const DialogTheme(
                    backgroundColor: Color(0xFF1B2939),
                  ),
                  dividerColor: const Color(0xFF38444D),
                  textTheme: GoogleFonts.poppinsTextTheme(ThemeData.dark().textTheme).apply(
                    bodyColor: const Color(0xFFE7E9EA),
                    displayColor: const Color(0xFFE7E9EA),
                  ),
                  inputDecorationTheme: InputDecorationTheme(
                    filled: true,
                    fillColor: const Color(0xFF1B2939),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFF38444D)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFF1B3B4B), width: 2),
                    ),
                  ),
                ),
                initialRoute: '/',
                routes: {
                  '/': (context) => const SplashScreen(),
                  '/login': (context) => const LoginScreen(),
                  '/signup': (context) => const SignupScreen(),
                  '/personal-info': (context) => const PersonalInfoScreen(
                    email: '',
                    password: '',
                  ),
                  '/home': (context) => const HomePage(),
                  '/career-profiles': (context) => const CareerProfilesPage(),
                  '/career-quiz': (context) => const CareerQuizPage(),
                  '/inspiring-stories': (context) => const InspiringStoriesPage(),
                  '/forgot-password': (context) => const ForgotPasswordScreen(),
                  '/settings': (context) => const SettingsPage(),
                  '/career-detail': (context) => CareerDetailPage(
                    careerId: ModalRoute.of(context)!.settings.arguments as String,
                  ),
                  '/about': (context) => const AboutPage(),
                },
              );
            },
          ),
        );
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
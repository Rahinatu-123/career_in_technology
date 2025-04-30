import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../data/career_profiles_data.dart';
import '../widgets/feature_card.dart';
import '../models/career_profile.dart';
import '../providers/user_provider.dart';
import 'career_detail_page.dart';
import 'settings_page.dart';
import 'career_quiz_page.dart';
import 'career_profiles_page.dart';
import 'inspiring_stories_page.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  static const darkBlue = Color(0xFF0A2A36);
  int _selectedIndex = 0;

  final List<Widget> _pages = [
    const CareerQuizPage(),
    const CareerProfilesPage(),
    const InspiringStoriesPage(),
    const SettingsPage(),
  ];

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  List<CareerProfile> get _filteredCareers {
    final careerData = Provider.of<CareerProfilesData>(context);
    if (_searchQuery.isEmpty) {
      return careerData.careerProfiles.take(4).toList();
    }
    return careerData.searchCareers(_searchQuery);
  }

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    final userProvider = Provider.of<UserProvider>(context);
    final user = userProvider.user;

    return Scaffold(
      body: _pages[_selectedIndex],
      bottomNavigationBar: BottomNavigationBar(
        items: const <BottomNavigationBarItem>[
          BottomNavigationBarItem(
            icon: Icon(Icons.quiz_outlined),
            activeIcon: Icon(Icons.quiz),
            label: 'Quiz',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.work_outline),
            activeIcon: Icon(Icons.work),
            label: 'Careers',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.auto_stories_outlined),
            activeIcon: Icon(Icons.auto_stories),
            label: 'Stories',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.settings_outlined),
            activeIcon: Icon(Icons.settings),
            label: 'Settings',
          ),
        ],
        currentIndex: _selectedIndex,
        selectedItemColor: Theme.of(context).primaryColor,
        unselectedItemColor: Colors.grey,
        type: BottomNavigationBarType.fixed,
        onTap: _onItemTapped,
      ),
      appBar: AppBar(
        title: Text(
          'CareerTech',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
          ),
        ),
        actions: [
          if (user != null)
            Padding(
              padding: const EdgeInsets.only(right: 16.0),
              child: CircleAvatar(
                backgroundColor: Theme.of(context).primaryColor,
                child: Text(
                  user.firstName[0].toUpperCase(),
                  style: GoogleFonts.poppins(
                    color: Colors.white,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
} 
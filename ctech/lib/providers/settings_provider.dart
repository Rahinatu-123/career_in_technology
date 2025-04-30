import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class SettingsProvider extends ChangeNotifier {
  bool _isDarkMode = false;
  bool _notificationsEnabled = true;
  final SharedPreferences _prefs;

  SettingsProvider(this._prefs) {
    _loadSettings();
  }

  bool get isDarkMode => _isDarkMode;
  bool get notificationsEnabled => _notificationsEnabled;

  void _loadSettings() {
    _isDarkMode = _prefs.getBool('isDarkMode') ?? false;
    _notificationsEnabled = _prefs.getBool('notificationsEnabled') ?? true;
    notifyListeners();
  }

  Future<void> toggleDarkMode(bool value) async {
    _isDarkMode = value;
    await _prefs.setBool('isDarkMode', value);
    notifyListeners();
  }

  Future<void> toggleNotifications(bool value) async {
    _notificationsEnabled = value;
    await _prefs.setBool('notificationsEnabled', value);
    notifyListeners();
  }

  ThemeData get theme {
    return _isDarkMode
        ? ThemeData.dark().copyWith(
            primaryColor: const Color(0xFF0A2A36),
            colorScheme: const ColorScheme.dark(
              primary: Color(0xFF0A2A36),
              secondary: Color(0xFF1B3B4B),
            ),
          )
        : ThemeData.light().copyWith(
            primaryColor: const Color(0xFF0A2A36),
            colorScheme: const ColorScheme.light(
              primary: Color(0xFF0A2A36),
              secondary: Color(0xFF1B3B4B),
            ),
          );
  }
} 
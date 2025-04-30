import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';

class UserProvider with ChangeNotifier {
  final SharedPreferences _prefs;
  String? _token;
  String? _email;
  String? _firstName;
  String? _lastName;
  User? _user;
  bool _isLoading = false;
  String? _error;

  UserProvider(this._prefs) {
    _loadUserData();
  }

  void _loadUserData() {
    _token = _prefs.getString('token');
    _email = _prefs.getString('email');
    _firstName = _prefs.getString('firstName');
    _lastName = _prefs.getString('lastName');
  }

  bool get isLoggedIn => _token != null;
  String? get token => _token;
  String? get email => _email;
  String? get firstName => _firstName;
  String? get lastName => _lastName;
  String get fullName => '$_firstName $_lastName'.trim();

  User? get user => _user;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _user != null;

  Future<void> setUserData({
    required String token,
    required String email,
    String? firstName,
    String? lastName,
  }) async {
    _token = token;
    _email = email;
    _firstName = firstName;
    _lastName = lastName;

    await _prefs.setString('token', token);
    await _prefs.setString('email', email);
    if (firstName != null) await _prefs.setString('firstName', firstName);
    if (lastName != null) await _prefs.setString('lastName', lastName);

    notifyListeners();
  }

  void setUser(User? user) {
    _user = user;
    notifyListeners();
  }

  void setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void setError(String? error) {
    _error = error;
    notifyListeners();
  }

  Future<void> login(String email, String password) async {
    try {
      setLoading(true);
      setError(null);
      
      // TODO: Implement actual login logic with your backend
      // This is just a mock implementation
      await Future.delayed(const Duration(seconds: 2));
      
      _user = User(
        id: '1',
        email: email,
        name: 'Test User',
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
      );
      
      notifyListeners();
    } catch (e) {
      setError(e.toString());
    } finally {
      setLoading(false);
    }
  }

  Future<void> logout() async {
    try {
      setLoading(true);
      setError(null);
      
      // TODO: Implement actual logout logic with your backend
      await Future.delayed(const Duration(milliseconds: 500));
      
      _user = null;
      notifyListeners();
    } catch (e) {
      setError(e.toString());
    } finally {
      setLoading(false);
    }
  }

  Future<void> updateProfile({
    String? name,
    String? bio,
    List<String>? interests,
    List<String>? skills,
    String? education,
    String? experience,
  }) async {
    try {
      setLoading(true);
      setError(null);
      
      if (_user == null) {
        throw Exception('User not authenticated');
      }
      
      // TODO: Implement actual profile update logic with your backend
      await Future.delayed(const Duration(seconds: 1));
      
      _user = _user!.copyWith(
        name: name,
        bio: bio,
        interests: interests,
        skills: skills,
        education: education,
        experience: experience,
        updatedAt: DateTime.now(),
      );
      
      notifyListeners();
    } catch (e) {
      setError(e.toString());
    } finally {
      setLoading(false);
    }
  }
} 
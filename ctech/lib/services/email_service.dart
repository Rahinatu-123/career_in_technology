import 'package:flutter/material.dart';

class EmailService {
  static final EmailService _instance = EmailService._internal();
  factory EmailService() => _instance;
  EmailService._internal();

  // Store sent emails to prevent spam
  final Map<String, DateTime> _lastSentTime = {};
  final Map<String, int> _failedAttempts = {};

  // Rate limiting: 1 email per minute, max 3 attempts per hour
  static const _minTimeBetweenEmails = Duration(minutes: 1);
  static const _maxAttemptsPerHour = 3;
  static const _attemptWindow = Duration(hours: 1);

  Future<bool> sendOTPEmail(String email, String otp) async {
    try {
      // Check rate limiting
      if (!_canSendEmail(email)) {
        debugPrint('Rate limit exceeded for $email');
        return false;
      }

      // TODO: Replace with actual email sending implementation
      // For now, just simulate email sending
      await Future.delayed(const Duration(seconds: 1));
      
      // Update last sent time
      _lastSentTime[email] = DateTime.now();
      
      // Log the email (in production, this would actually send the email)
      debugPrint('''
        To: $email
        Subject: Your CTech Password Reset OTP
        Body: Your OTP for password reset is: $otp
        This OTP will expire in 5 minutes.
      ''');
      
      return true;
    } catch (e) {
      debugPrint('Error sending email: $e');
      _incrementFailedAttempts(email);
      return false;
    }
  }

  bool _canSendEmail(String email) {
    final lastSent = _lastSentTime[email];
    final failedAttempts = _failedAttempts[email] ?? 0;
    
    // Check if too many failed attempts
    if (failedAttempts >= _maxAttemptsPerHour) {
      return false;
    }

    // Check if enough time has passed since last email
    if (lastSent != null) {
      final timeSinceLastEmail = DateTime.now().difference(lastSent);
      if (timeSinceLastEmail < _minTimeBetweenEmails) {
        return false;
      }
    }

    return true;
  }

  void _incrementFailedAttempts(String email) {
    final attempts = (_failedAttempts[email] ?? 0) + 1;
    _failedAttempts[email] = attempts;

    // Reset attempts after the window expires
    Future.delayed(_attemptWindow, () {
      if (_failedAttempts[email] != null) {
        _failedAttempts[email] = (_failedAttempts[email]! - 1).clamp(0, _maxAttemptsPerHour);
      }
    });
  }

  int getRemainingAttempts(String email) {
    final attempts = _failedAttempts[email] ?? 0;
    return _maxAttemptsPerHour - attempts;
  }

  Duration getTimeUntilNextEmail(String email) {
    final lastSent = _lastSentTime[email];
    if (lastSent == null) return Duration.zero;

    final timeSinceLastEmail = DateTime.now().difference(lastSent);
    if (timeSinceLastEmail >= _minTimeBetweenEmails) {
      return Duration.zero;
    }

    return _minTimeBetweenEmails - timeSinceLastEmail;
  }
} 
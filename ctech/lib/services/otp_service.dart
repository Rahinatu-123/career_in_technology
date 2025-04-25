import 'dart:math';
import 'package:flutter/material.dart';
import 'email_service.dart';

class OTPService {
  static final OTPService _instance = OTPService._internal();
  factory OTPService() => _instance;
  OTPService._internal();

  final _emailService = EmailService();

  // Store OTPs temporarily (in a real app, this would be in a database)
  final Map<String, String> _otpStore = {};
  final Map<String, DateTime> _otpExpiry = {};
  final Map<String, int> _failedVerifications = {};

  // Rate limiting: max 3 failed verifications per hour
  static const _maxFailedVerifications = 3;
  static const _verificationWindow = Duration(hours: 1);

  // Generate a 6-digit OTP
  String generateOTP() {
    final random = Random();
    final otp = List.generate(6, (index) => random.nextInt(10)).join();
    return otp;
  }

  // Send OTP to email
  Future<bool> sendOTP(String email) async {
    try {
      // Generate new OTP
      final otp = generateOTP();
      
      // Send email
      final emailSent = await _emailService.sendOTPEmail(email, otp);
      if (!emailSent) {
        return false;
      }
      
      // Store OTP with expiry time (5 minutes)
      _otpStore[email] = otp;
      _otpExpiry[email] = DateTime.now().add(const Duration(minutes: 5));

      return true;
    } catch (e) {
      debugPrint('Error sending OTP: $e');
      return false;
    }
  }

  // Verify OTP
  bool verifyOTP(String email, String otp) {
    // Check rate limiting
    if ((_failedVerifications[email] ?? 0) >= _maxFailedVerifications) {
      debugPrint('Too many failed verification attempts for $email');
      return false;
    }

    final storedOTP = _otpStore[email];
    final expiryTime = _otpExpiry[email];

    if (storedOTP == null || expiryTime == null) {
      _incrementFailedVerifications(email);
      return false;
    }

    // Check if OTP has expired
    if (DateTime.now().isAfter(expiryTime)) {
      // Clean up expired OTP
      _otpStore.remove(email);
      _otpExpiry.remove(email);
      _incrementFailedVerifications(email);
      return false;
    }

    // Verify OTP
    final isValid = storedOTP == otp;

    // Clean up used OTP
    if (isValid) {
      _otpStore.remove(email);
      _otpExpiry.remove(email);
      _failedVerifications.remove(email);
    } else {
      _incrementFailedVerifications(email);
    }

    return isValid;
  }

  void _incrementFailedVerifications(String email) {
    final attempts = (_failedVerifications[email] ?? 0) + 1;
    _failedVerifications[email] = attempts;

    // Reset attempts after the window expires
    Future.delayed(_verificationWindow, () {
      if (_failedVerifications[email] != null) {
        _failedVerifications[email] = (_failedVerifications[email]! - 1).clamp(0, _maxFailedVerifications);
      }
    });
  }

  // Check if OTP exists and is valid
  bool hasValidOTP(String email) {
    final expiryTime = _otpExpiry[email];
    if (expiryTime == null) return false;
    return !DateTime.now().isAfter(expiryTime);
  }

  // Get remaining time in seconds
  int getRemainingTime(String email) {
    final expiryTime = _otpExpiry[email];
    if (expiryTime == null) return 0;
    
    final remaining = expiryTime.difference(DateTime.now()).inSeconds;
    return remaining > 0 ? remaining : 0;
  }

  // Get remaining verification attempts
  int getRemainingVerificationAttempts(String email) {
    final attempts = _failedVerifications[email] ?? 0;
    return _maxFailedVerifications - attempts;
  }

  // Get time until next email can be sent
  Duration getTimeUntilNextEmail(String email) {
    return _emailService.getTimeUntilNextEmail(email);
  }
} 
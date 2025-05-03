import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:image_cropper/image_cropper.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:path_provider/path_provider.dart';
import 'package:path/path.dart' as path;
import 'package:permission_handler/permission_handler.dart';

class ProfilePage extends StatefulWidget {
  const ProfilePage({super.key});

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  File? _image;
  final ImagePicker _picker = ImagePicker();
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _bioController = TextEditingController();
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _bioController.dispose();
    super.dispose();
  }

  Future<void> _loadUserData() async {
    setState(() => _isLoading = true);
    try {
      final prefs = await SharedPreferences.getInstance();
      setState(() {
        _nameController.text = prefs.getString('user_name') ?? '';
        _emailController.text = prefs.getString('user_email') ?? '';
        _phoneController.text = prefs.getString('user_phone') ?? '';
        _bioController.text = prefs.getString('user_bio') ?? '';
        final imagePath = prefs.getString('profile_image_path');
        if (imagePath != null && imagePath.isNotEmpty) {
          _image = File(imagePath);
        }
      });
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error loading profile: $e')),
        );
      }
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _saveUserData() async {
    setState(() => _isLoading = true);
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('user_name', _nameController.text);
      await prefs.setString('user_email', _emailController.text);
      await prefs.setString('user_phone', _phoneController.text);
      await prefs.setString('user_bio', _bioController.text);
      if (_image != null) {
        await prefs.setString('profile_image_path', _image!.path);
      }
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Profile updated successfully')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error saving profile: $e')),
        );
      }
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _logout() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.clear();
      if (mounted) {
        Navigator.pushReplacementNamed(context, '/login');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error logging out: $e')),
        );
      }
    }
  }

  Future<void> _getImage(ImageSource source) async {
    try {
      // Request permissions based on the source
      if (source == ImageSource.camera) {
        final cameraStatus = await Permission.camera.status;
        if (!cameraStatus.isGranted) {
          final result = await Permission.camera.request();
          if (!result.isGranted) {
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Camera permission is required to take photos')),
              );
            }
            return;
          }
        }
      } else {
        if (Platform.isAndroid) {
          final storageStatus = await Permission.storage.status;
          if (!storageStatus.isGranted) {
            final result = await Permission.storage.request();
            if (!result.isGranted) {
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Storage permission is required to access photos')),
                );
              }
              return;
            }
          }
        } else if (Platform.isIOS) {
          final photosStatus = await Permission.photos.status;
          if (!photosStatus.isGranted) {
            final result = await Permission.photos.request();
            if (!result.isGranted) {
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Photo library permission is required')),
                );
              }
              return;
            }
          }
        }
      }

      // Pick image
      final XFile? pickedFile = await _picker.pickImage(
        source: source,
        maxWidth: 1800,
        maxHeight: 1800,
        imageQuality: 85,
      );

      if (pickedFile != null) {
        // Show loading indicator
        if (mounted) {
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (context) => const Center(
              child: CircularProgressIndicator(),
            ),
          );
        }

        try {
          // Crop image
          final croppedFile = await ImageCropper().cropImage(
            sourcePath: pickedFile.path,
            aspectRatio: const CropAspectRatio(ratioX: 1, ratioY: 1),
            uiSettings: [
              AndroidUiSettings(
                toolbarTitle: 'Crop Image',
                toolbarColor: const Color(0xFF0A2A36),
                toolbarWidgetColor: Colors.white,
                initAspectRatio: CropAspectRatioPreset.square,
                lockAspectRatio: true,
                hideBottomControls: true,
                showCropGrid: false,
              ),
              IOSUiSettings(
                title: 'Crop Image',
                aspectRatioLockEnabled: true,
                resetAspectRatioEnabled: false,
                aspectRatioPickerButtonHidden: true,
              ),
            ],
          );

          if (croppedFile != null) {
            // Save image to app directory
            final appDir = await getApplicationDocumentsDirectory();
            final fileName = 'profile_${DateTime.now().millisecondsSinceEpoch}.jpg';
            final savedImage = await File(croppedFile.path).copy('${appDir.path}/$fileName');
            
            setState(() {
              _image = savedImage;
            });
          }
        } catch (e) {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text('Error cropping image: $e')),
            );
          }
        } finally {
          // Dismiss loading indicator
          if (mounted) {
            Navigator.pop(context);
          }
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error picking image: $e')),
        );
      }
    }
  }

  void _showImagePickerModal() {
    showModalBottomSheet(
      context: context,
      builder: (BuildContext context) {
        return SafeArea(
          child: Wrap(
            children: [
              ListTile(
                leading: const Icon(Icons.photo_library),
                title: const Text('Choose from Gallery'),
                onTap: () {
                  Navigator.pop(context);
                  _getImage(ImageSource.gallery);
                },
              ),
              ListTile(
                leading: const Icon(Icons.camera_alt),
                title: const Text('Take a Photo'),
                onTap: () {
                  Navigator.pop(context);
                  _getImage(ImageSource.camera);
                },
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Profile'),
        actions: [
          IconButton(
            icon: const Icon(Icons.save),
            onPressed: _isLoading ? null : _saveUserData,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  const SizedBox(height: 20),
                  // Profile Image
                  GestureDetector(
                    onTap: _showImagePickerModal,
                    child: CircleAvatar(
                      radius: 60,
                      backgroundColor: Colors.grey[200],
                      backgroundImage: _image != null ? FileImage(_image!) : null,
                      child: _image == null
                          ? const Icon(Icons.person, size: 60, color: Colors.grey)
                          : null,
                    ),
                  ),
                  const SizedBox(height: 20),
                  // User Info Display
                  if (_nameController.text.isNotEmpty)
                    Card(
                      margin: const EdgeInsets.symmetric(vertical: 4),
                      child: ListTile(
                        leading: const Icon(Icons.person),
                        title: Text(_nameController.text),
                        subtitle: const Text('Name'),
                      ),
                    ),
                  if (_emailController.text.isNotEmpty)
                    Card(
                      margin: const EdgeInsets.symmetric(vertical: 4),
                      child: ListTile(
                        leading: const Icon(Icons.email),
                        title: Text(_emailController.text),
                        subtitle: const Text('Email'),
                      ),
                    ),
                  if (_phoneController.text.isNotEmpty)
                    Card(
                      margin: const EdgeInsets.symmetric(vertical: 4),
                      child: ListTile(
                        leading: const Icon(Icons.phone),
                        title: Text(_phoneController.text),
                        subtitle: const Text('Phone'),
                      ),
                    ),
                  const SizedBox(height: 20),
                  // Change Photo Button
                  TextButton.icon(
                    onPressed: _showImagePickerModal,
                    icon: const Icon(Icons.camera_alt),
                    label: const Text('Change Photo'),
                  ),
                  const SizedBox(height: 30),
                  // Name Field
                  TextFormField(
                    controller: _nameController,
                    decoration: InputDecoration(
                      labelText: 'Name',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      prefixIcon: const Icon(Icons.person),
                    ),
                  ),
                  const SizedBox(height: 16),
                  // Email Field
                  TextFormField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    decoration: InputDecoration(
                      labelText: 'Email',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      prefixIcon: const Icon(Icons.email),
                    ),
                  ),
                  const SizedBox(height: 16),
                  // Phone Field
                  TextFormField(
                    controller: _phoneController,
                    keyboardType: TextInputType.phone,
                    decoration: InputDecoration(
                      labelText: 'Phone',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      prefixIcon: const Icon(Icons.phone),
                    ),
                  ),
                  const SizedBox(height: 16),
                  // Bio Field
                  TextFormField(
                    controller: _bioController,
                    maxLines: 3,
                    decoration: InputDecoration(
                      labelText: 'Bio',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      prefixIcon: const Icon(Icons.info),
                    ),
                  ),
                  const SizedBox(height: 24),
                  // Save Button
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: _saveUserData,
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: const Text(
                        'Save Profile',
                        style: TextStyle(fontSize: 16),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  // Logout Button
                  SizedBox(
                    width: double.infinity,
                    child: TextButton(
                      onPressed: _logout,
                      child: const Text(
                        'Logout',
                        style: TextStyle(color: Colors.red),
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
} 
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/career_profile.dart';

class CareerProfilesData extends ChangeNotifier {
  final List<CareerProfile> _careerProfiles = [
    CareerProfile(
      id: '1',
      title: 'Software Developer',
      description: 'Design, develop, and maintain software applications and systems. Work with various programming languages and technologies to create efficient and scalable solutions.',
      imageUrl: 'assets/images/careers/software_developer.jpg',
      skills: [
        'Programming Languages (Java, Python, JavaScript)',
        'Web Development',
        'Database Management',
        'Problem Solving',
        'Version Control',
        'Team Collaboration',
      ],
      responsibilities: [
        'Write clean, efficient code',
        'Debug and fix software issues',
        'Collaborate with team members',
        'Participate in code reviews',
        'Design software architecture',
        'Test and deploy applications',
      ],
      requirements: [
        'Bachelor\'s degree in Computer Science or related field',
        'Strong programming skills',
        'Knowledge of software development methodologies',
        'Experience with version control systems',
        'Problem-solving abilities',
      ],
      averageSalary: 85000,
      salaryCurrency: 'USD',
      relatedCareers: [
        'Web Developer',
        'Mobile Developer',
        'DevOps Engineer',
        'Data Engineer',
        'Full Stack Developer',
      ],
      growthRate: 25,
      educationLevel: 'Bachelor\'s Degree',
      yearsOfExperience: 2,
    ),
    CareerProfile(
      id: '2',
      title: 'Data Scientist',
      description: 'Analyze complex data sets to help organizations make better decisions. Use statistical methods and machine learning to extract insights from data.',
      imageUrl: 'assets/images/careers/data_scientist.jpg',
      skills: [
        'Python/R Programming',
        'Machine Learning',
        'Statistical Analysis',
        'Data Visualization',
        'SQL',
        'Big Data Technologies',
      ],
      responsibilities: [
        'Collect and analyze large datasets',
        'Develop predictive models',
        'Create data visualizations',
        'Present findings to stakeholders',
        'Optimize data collection processes',
        'Stay updated with latest ML techniques',
      ],
      requirements: [
        'Master\'s degree in Data Science or related field',
        'Strong mathematical background',
        'Programming experience',
        'Knowledge of machine learning algorithms',
        'Data visualization skills',
      ],
      averageSalary: 95000,
      salaryCurrency: 'USD',
      relatedCareers: [
        'Data Analyst',
        'Machine Learning Engineer',
        'Business Intelligence Analyst',
        'Data Engineer',
        'Research Scientist',
      ],
      growthRate: 36,
      educationLevel: 'Master\'s Degree',
      yearsOfExperience: 3,
    ),
    CareerProfile(
      id: '3',
      title: 'UX/UI Designer',
      description: 'Create user-friendly interfaces and experiences for digital products. Focus on user needs and behavior to design intuitive and engaging interfaces.',
      imageUrl: 'assets/images/careers/ux_designer.jpg',
      skills: [
        'User Research',
        'Wireframing',
        'Prototyping',
        'Visual Design',
        'Interaction Design',
        'Design Systems',
      ],
      responsibilities: [
        'Conduct user research',
        'Create wireframes and prototypes',
        'Design user interfaces',
        'Test and iterate designs',
        'Collaborate with developers',
        'Maintain design systems',
      ],
      requirements: [
        'Bachelor\'s degree in Design or related field',
        'Portfolio of work',
        'Knowledge of design tools',
        'Understanding of user-centered design',
        'Communication skills',
      ],
      averageSalary: 75000,
      salaryCurrency: 'USD',
      relatedCareers: [
        'Graphic Designer',
        'Product Designer',
        'Interaction Designer',
        'Visual Designer',
        'Design Researcher',
      ],
      growthRate: 13,
      educationLevel: 'Bachelor\'s Degree',
      yearsOfExperience: 2,
    ),
    CareerProfile(
      id: '4',
      title: 'Digital Marketing Specialist',
      description: 'Develop and implement digital marketing strategies to promote products and services. Use various online channels to reach target audiences.',
      imageUrl: 'assets/images/careers/digital_marketing.jpg',
      skills: [
        'Social Media Marketing',
        'SEO/SEM',
        'Content Marketing',
        'Email Marketing',
        'Analytics',
        'Campaign Management',
      ],
      responsibilities: [
        'Create marketing campaigns',
        'Manage social media accounts',
        'Optimize website content',
        'Analyze marketing metrics',
        'Create engaging content',
        'Monitor campaign performance',
      ],
      requirements: [
        'Bachelor\'s degree in Marketing or related field',
        'Experience with marketing tools',
        'Analytical skills',
        'Content creation abilities',
        'Knowledge of digital platforms',
      ],
      averageSalary: 65000,
      salaryCurrency: 'USD',
      relatedCareers: [
        'Content Strategist',
        'Social Media Manager',
        'SEO Specialist',
        'Marketing Manager',
        'Brand Manager',
      ],
      growthRate: 10,
      educationLevel: 'Bachelor\'s Degree',
      yearsOfExperience: 1,
    ),
    CareerProfile(
      id: '5',
      title: 'Project Manager',
      description: 'Lead and coordinate project teams to deliver successful outcomes. Manage resources, timelines, and stakeholder expectations.',
      imageUrl: 'assets/images/careers/project_manager.jpg',
      skills: [
        'Leadership',
        'Communication',
        'Risk Management',
        'Budgeting',
        'Agile/Scrum',
        'Team Management',
      ],
      responsibilities: [
        'Define project scope and goals',
        'Create project plans',
        'Manage team resources',
        'Monitor project progress',
        'Handle stakeholder communication',
        'Mitigate risks',
      ],
      requirements: [
        'Bachelor\'s degree in Business or related field',
        'Project management certification',
        'Leadership experience',
        'Communication skills',
        'Problem-solving abilities',
      ],
      averageSalary: 90000,
      salaryCurrency: 'USD',
      relatedCareers: [
        'Program Manager',
        'Product Manager',
        'Scrum Master',
        'Business Analyst',
        'Operations Manager',
      ],
      growthRate: 7,
      educationLevel: 'Bachelor\'s Degree',
      yearsOfExperience: 5,
    ),
  ];

  List<CareerProfile> get careerProfiles => _careerProfiles;

  CareerProfile? getCareerProfileById(String id) {
    try {
      return _careerProfiles.firstWhere((profile) => profile.id == id);
    } catch (e) {
      return null;
    }
  }

  List<CareerProfile> getRelatedCareers(CareerProfile profile) {
    return _careerProfiles
        .where((career) =>
            career.id != profile.id &&
            (profile.relatedCareers.contains(career.title) ||
                career.relatedCareers.contains(profile.title)))
        .toList();
  }

  List<CareerProfile> searchCareers(String query) {
    final lowercaseQuery = query.toLowerCase();
    return _careerProfiles.where((profile) {
      return profile.title.toLowerCase().contains(lowercaseQuery) ||
          profile.description.toLowerCase().contains(lowercaseQuery) ||
          profile.skills.any((skill) => skill.toLowerCase().contains(lowercaseQuery));
    }).toList();
  }

  Future<void> toggleSaveCareer(String careerId) async {
    final index = _careerProfiles.indexWhere((profile) => profile.id == careerId);
    if (index != -1) {
      final career = _careerProfiles[index];
      _careerProfiles[index] = career.copyWith(isSaved: !career.isSaved);
      notifyListeners();
      
      // Save to SharedPreferences
      final prefs = await SharedPreferences.getInstance();
      final savedCareers = prefs.getStringList('saved_careers') ?? [];
      if (career.isSaved) {
        savedCareers.add(careerId);
      } else {
        savedCareers.remove(careerId);
      }
      await prefs.setStringList('saved_careers', savedCareers);
    }
  }

  Future<void> loadSavedCareers() async {
    final prefs = await SharedPreferences.getInstance();
    final savedCareers = prefs.getStringList('saved_careers') ?? [];
    
    for (var i = 0; i < _careerProfiles.length; i++) {
      final career = _careerProfiles[i];
      _careerProfiles[i] = career.copyWith(
        isSaved: savedCareers.contains(career.id),
      );
    }
    notifyListeners();
  }
} 
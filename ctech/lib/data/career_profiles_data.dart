import '../models/career_profile.dart';

class CareerProfilesData {
  static final List<CareerProfile> profiles = [
    CareerProfile(
      id: '1',
      title: 'Software Developer',
      description: 'Software developers create applications and systems that run on computers and other devices. They write code, test software, and fix bugs.',
      skills: [
        'Programming languages (Python, Java, JavaScript)',
        'Problem-solving',
        'Team collaboration',
        'Version control (Git)',
        'Software testing',
      ],
      applications: [
        'Mobile apps',
        'Web applications',
        'Desktop software',
        'Game development',
        'Enterprise systems',
      ],
      imagePath: 'assets/images/software_dev.jpg',
    ),
    CareerProfile(
      id: '2',
      title: 'Data Scientist',
      description: 'Data scientists analyze and interpret complex data to help organizations make better decisions. They use statistics, machine learning, and programming to extract insights from data.',
      skills: [
        'Statistics and mathematics',
        'Python/R programming',
        'Machine learning',
        'Data visualization',
        'SQL and databases',
      ],
      applications: [
        'Predictive analytics',
        'Business intelligence',
        'Machine learning models',
        'Data visualization',
        'Research and development',
      ],
      imagePath: 'assets/images/data_scientist.jpg',
    ),
    CareerProfile(
      id: '3',
      title: 'UI/UX Designer',
      description: 'UI/UX designers create user interfaces and experiences for digital products. They focus on making products easy to use and visually appealing.',
      skills: [
        'User research',
        'Wireframing and prototyping',
        'Visual design',
        'Interaction design',
        'User testing',
      ],
      applications: [
        'Web design',
        'Mobile app design',
        'Product design',
        'User research',
        'Design systems',
      ],
      imagePath: 'assets/images/ui_ux.jpg',
    ),
    CareerProfile(
      id: '4',
      title: 'Network Engineer',
      description: 'Network engineers design, implement, and maintain computer networks. They ensure that networks are secure, reliable, and efficient.',
      skills: [
        'Network protocols',
        'Network security',
        'Troubleshooting',
        'Cisco/Juniper systems',
        'Cloud networking',
      ],
      applications: [
        'Network infrastructure',
        'Cloud computing',
        'Cybersecurity',
        'Telecommunications',
        'Enterprise networking',
      ],
      imagePath: 'assets/images/network_engineer.jpg',
    ),
  ];
} 
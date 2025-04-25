import '../models/inspiring_story.dart';

class InspiringStoriesData {
  static final List<InspiringStory> stories = [
    InspiringStory(
      id: '1',
      name: 'Ama Ofori',
      role: 'Senior Software Engineer',
      company: 'Google',
      imagePath: 'assets/images/ama_ofori.jpg',
      shortQuote: 'From Accra to Silicon Valley - my journey in tech',
      fullStory: '''
I started my journey in tech at the University of Ghana, where I studied Computer Science. 
After graduation, I worked at a local startup in Accra, where I learned the ropes of software development.

My big break came when I was selected for the Google Africa Developer Scholarship program. 
This opportunity helped me refine my skills and connect with mentors in the industry.

Today, I work at Google\'s headquarters in Mountain View, California, leading a team that develops 
innovative solutions for emerging markets. My advice to aspiring tech professionals in Ghana is to 
never stop learning and to take advantage of every opportunity to grow your skills.

Remember, your background and experiences are valuable assets in the tech industry. 
Embrace your unique perspective and use it to create solutions that make a difference.
''',
      audioPath: 'assets/audio/ama_ofori.mp3',
      relatedCareers: ['1', '3'], // Software Developer, UI/UX Designer
    ),
    InspiringStory(
      id: '2',
      name: 'Kwame Mensah',
      role: 'Data Science Lead',
      company: 'MTN Ghana',
      imagePath: 'assets/images/kwame_mensah.jpg',
      shortQuote: 'Using data to drive positive change in Africa',
      fullStory: '''
My passion for data science began during my undergraduate studies at KNUST, where I was fascinated 
by how data could be used to solve real-world problems. After completing my master\'s degree in 
Data Science, I joined MTN Ghana\'s analytics team.

In my role, I lead a team that uses data to improve customer experience and drive business decisions. 
We\'ve developed innovative solutions that have helped MTN better serve its customers and contribute 
to Ghana\'s digital transformation.

One of my proudest achievements is developing a predictive model that helps identify areas where 
network infrastructure is most needed, ensuring better connectivity for rural communities.

To those interested in data science, I recommend starting with the fundamentals of statistics and 
programming. The field is constantly evolving, so continuous learning is key to success.
''',
      audioPath: 'assets/audio/kwame_mensah.mp3',
      relatedCareers: ['2'], // Data Scientist
    ),
    InspiringStory(
      id: '3',
      name: 'Esi Bonsu',
      role: 'Network Security Specialist',
      company: 'Ghana National Security',
      imagePath: 'assets/images/esi_bonsu.jpg',
      shortQuote: 'Protecting Ghana\'s digital infrastructure',
      fullStory: '''
As a Network Security Specialist, I play a crucial role in protecting Ghana\'s critical digital 
infrastructure. My journey began with a degree in Computer Engineering from the University of Ghana, 
followed by specialized training in cybersecurity.

I\'ve worked on several high-profile projects, including securing government networks and developing 
cybersecurity protocols for financial institutions. My work has helped strengthen Ghana\'s digital 
defenses and protect against cyber threats.

What I love most about my job is the constant challenge and the opportunity to make a real impact 
on national security. Every day brings new problems to solve and new technologies to master.

For those interested in cybersecurity, I recommend starting with networking fundamentals and 
obtaining relevant certifications. The field requires both technical skills and a strong ethical 
foundation.
''',
      audioPath: 'assets/audio/esi_bonsu.mp3',
      relatedCareers: ['4'], // Network Engineer
    ),
  ];
} 
import '../models/career_profile.dart';
import '../models/tech_word.dart';
import '../services/api_service.dart';

class CareerRepository {
  final ApiService _apiService;

  CareerRepository({ApiService? apiService})
      : _apiService = apiService ?? ApiService();

  // Career Profiles
  Future<List<CareerProfile>> getCareers({
    String? search,
    double? minSalary,
    String? educationLevel,
  }) async {
    return _apiService.getCareers(
      search: search,
      minSalary: minSalary,
      educationLevel: educationLevel,
    );
  }

  Future<CareerProfile> createCareer(CareerProfile career) async {
    return _apiService.createCareer(career);
  }

  Future<CareerProfile> updateCareer(CareerProfile career) async {
    return _apiService.updateCareer(career);
  }

  Future<bool> deleteCareer(int id) async {
    return _apiService.deleteCareer(id);
  }

  // Tech Words
  Future<List<TechWord>> getTechWords({int? careerId}) async {
    if (careerId != null) {
      return _apiService.getTechWordsByCareer(careerId);
    } else {
      return _apiService.getTechWords();
    }
  }

  Future<TechWord> createTechWord(TechWord word) async {
    return _apiService.createTechWord(word);
  }

  Future<TechWord> updateTechWord(TechWord word) async {
    return _apiService.updateTechWord(word);
  }

  Future<bool> deleteTechWord(int id) async {
    return _apiService.deleteTechWord(id);
  }
} 
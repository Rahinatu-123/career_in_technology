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

  Future<void> updateCareer(CareerProfile career) async {
    await _apiService.updateCareer(career);
  }

  Future<void> deleteCareer(int id) async {
    await _apiService.deleteCareer(id);
  }

  // Tech Words
  Future<List<TechWord>> getTechWords({int? careerId}) async {
    return _apiService.getTechWords(careerId: careerId);
  }

  Future<TechWord> createTechWord(TechWord word) async {
    return _apiService.createTechWord(word);
  }

  Future<void> updateTechWord(TechWord word) async {
    await _apiService.updateTechWord(word);
  }

  Future<void> deleteTechWord(int id) async {
    await _apiService.deleteTechWord(id);
  }
} 
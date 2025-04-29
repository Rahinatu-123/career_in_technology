import 'package:flutter/material.dart';
import '../models/career_profile.dart';
import '../repositories/career_repository.dart';
import 'career_details_screen.dart';

class CareerListScreen extends StatefulWidget {
  const CareerListScreen({super.key});

  @override
  State<CareerListScreen> createState() => _CareerListScreenState();
}

class _CareerListScreenState extends State<CareerListScreen> {
  final _repository = CareerRepository();
  final _searchController = TextEditingController();
  List<CareerProfile> _careers = [];
  bool _isLoading = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadCareers();
  }

  Future<void> _loadCareers({String? search}) async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final careers = await _repository.getCareers(search: search);
      setState(() {
        _careers = careers;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Tech Careers'),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search careers...',
                suffixIcon: IconButton(
                  icon: const Icon(Icons.search),
                  onPressed: () => _loadCareers(search: _searchController.text),
                ),
              ),
              onSubmitted: (value) => _loadCareers(search: value),
            ),
          ),
          if (_isLoading)
            const Center(child: CircularProgressIndicator())
          else if (_error != null)
            Center(
              child: Text(
                'Error: $_error',
                style: const TextStyle(color: Colors.red),
              ),
            )
          else
            Expanded(
              child: ListView.builder(
                itemCount: _careers.length,
                itemBuilder: (context, index) {
                  final career = _careers[index];
                  return Card(
                    margin: const EdgeInsets.symmetric(
                      horizontal: 16.0,
                      vertical: 8.0,
                    ),
                    child: ListTile(
                      title: Text(career.title),
                      subtitle: Text(
                        career.description,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      trailing: Text(career.salaryRange),
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => CareerDetailsScreen(
                              career: career,
                            ),
                          ),
                        );
                      },
                    ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }
} 
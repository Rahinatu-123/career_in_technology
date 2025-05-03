import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';
import 'package:chewie/chewie.dart';
import 'package:just_audio/just_audio.dart';
import '../models/career_profile.dart';
import '../services/api_service.dart';

class CareerDetailPage extends StatefulWidget {
  final CareerProfile career;

  const CareerDetailPage({
    super.key,
    required this.career,
  });

  @override
  State<CareerDetailPage> createState() => _CareerDetailPageState();
}

class _CareerDetailPageState extends State<CareerDetailPage> {
  VideoPlayerController? _videoController;
  ChewieController? _chewieController;
  AudioPlayer? _audioPlayer;
  bool _isVideoInitialized = false;
  bool _isAudioInitialized = false;

  @override
  void initState() {
    super.initState();
    _initializeMedia();
  }

  Future<void> _initializeMedia() async {
    if (widget.career.videoPath != null) {
      _videoController = VideoPlayerController.network(widget.career.videoPath!);
      await _videoController!.initialize();
      _chewieController = ChewieController(
        videoPlayerController: _videoController!,
        autoPlay: false,
        looping: false,
        aspectRatio: _videoController!.value.aspectRatio,
      );
      setState(() {
        _isVideoInitialized = true;
      });
    }

    if (widget.career.audioPath != null) {
      _audioPlayer = AudioPlayer();
      try {
        await _audioPlayer!.setUrl(widget.career.audioPath!);
        setState(() {
          _isAudioInitialized = true;
        });
      } catch (e) {
        debugPrint('Error initializing audio: $e');
      }
    }
  }

  @override
  void dispose() {
    _videoController?.dispose();
    _chewieController?.dispose();
    _audioPlayer?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      backgroundColor: theme.scaffoldBackgroundColor,
      appBar: AppBar(
        title: Text(
          widget.career.title,
          style: theme.textTheme.titleLarge?.copyWith(
            color: theme.colorScheme.onPrimary,
            fontWeight: FontWeight.bold,
          ),
        ),
        backgroundColor: theme.primaryColor,
        elevation: 0,
        iconTheme: IconThemeData(color: theme.colorScheme.onPrimary),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              height: 200,
              decoration: BoxDecoration(
                color: theme.cardColor,
                borderRadius: BorderRadius.circular(12),
                image: widget.career.imagePath.isNotEmpty
                    ? DecorationImage(
                        image: NetworkImage(widget.career.imagePath),
                        fit: BoxFit.cover,
                      )
                    : null,
              ),
              child: widget.career.imagePath.isEmpty
                  ? Icon(
                      Icons.work,
                      size: 80,
                      color: theme.colorScheme.onSurface.withOpacity(0.24),
                    )
                  : null,
            ),
            const SizedBox(height: 24),
            Text(
              'Description',
              style: theme.textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              widget.career.description,
              style: theme.textTheme.bodyLarge,
            ),
            const SizedBox(height: 24),
            Text(
              'Required Skills',
              style: theme.textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            ...widget.career.skillsList.map((skill) => Padding(
              padding: const EdgeInsets.only(bottom: 8.0),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: theme.cardColor,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(
                      Icons.check_circle,
                      size: 16,
                      color: theme.colorScheme.primary,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      skill,
                      style: theme.textTheme.bodyLarge,
                    ),
                  ),
                ],
              ),
            )),
            const SizedBox(height: 24),
            Text(
              'Education Requirements',
              style: theme.textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              widget.career.education,
              style: theme.textTheme.bodyLarge,
            ),
            const SizedBox(height: 24),
            Text(
              'Salary Range',
              style: theme.textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              widget.career.formattedSalaryRange,
              style: theme.textTheme.bodyLarge,
            ),
            const SizedBox(height: 24),
            Text(
              'Job Outlook',
              style: theme.textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              widget.career.formattedJobOutlook,
              style: theme.textTheme.bodyLarge,
            ),
            if (widget.career.hasMedia) ...[
              const SizedBox(height: 24),
              Text(
                'Learn More',
                style: theme.textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 16),
              if (widget.career.videoPath != null)
                _isVideoInitialized
                    ? AspectRatio(
                        aspectRatio: _videoController!.value.aspectRatio,
                        child: Chewie(controller: _chewieController!),
                      )
                    : const Center(
                        child: CircularProgressIndicator(),
                      ),
              if (widget.career.audioPath != null) ...[
                const SizedBox(height: 16),
                _isAudioInitialized
                    ? StreamBuilder<PlayerState>(
                        stream: _audioPlayer!.playerStateStream,
                        builder: (context, snapshot) {
                          final playerState = snapshot.data;
                          final processingState = playerState?.processingState;
                          final playing = playerState?.playing;
                          
                          return Row(
                            children: [
                              IconButton(
                                icon: Icon(
                                  playing == true
                                      ? Icons.pause_circle
                                      : Icons.play_circle,
                                  size: 48,
                                ),
                                onPressed: () {
                                  if (playing == true) {
                                    _audioPlayer!.pause();
                                  } else {
                                    _audioPlayer!.play();
                                  }
                                },
                              ),
                              Expanded(
                                child: StreamBuilder<Duration>(
                                  stream: _audioPlayer!.positionStream,
                                  builder: (context, snapshot) {
                                    final position = snapshot.data ?? Duration.zero;
                                    return StreamBuilder<Duration?>(
                                      stream: _audioPlayer!.durationStream,
                                      builder: (context, snapshot) {
                                        final duration = snapshot.data ?? Duration.zero;
                                        return Slider(
                                          value: position.inMilliseconds.toDouble(),
                                          min: 0.0,
                                          max: duration.inMilliseconds.toDouble(),
                                          onChanged: (value) {
                                            _audioPlayer!.seek(
                                              Duration(milliseconds: value.toInt()),
                                            );
                                          },
                                        );
                                      },
                                    );
                                  },
                                ),
                              ),
                            ],
                          );
                        },
                      )
                    : const Center(
                        child: CircularProgressIndicator(),
                      ),
              ],
            ],
          ],
        ),
      ),
    );
  }
} 
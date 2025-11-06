import 'package:flutter/foundation.dart';
import '../../data/models/user.dart';

class UserNotifier extends ChangeNotifier {
  static final UserNotifier _instance = UserNotifier._internal();
  factory UserNotifier() => _instance;
  UserNotifier._internal();

  User? _currentUser;

  User? get currentUser => _currentUser;

  void updateUser(User? user) {
    _currentUser = user;
    notifyListeners();
  }

  void clearUser() {
    _currentUser = null;
    notifyListeners();
  }
}

// Instancia global
final userNotifier = UserNotifier();
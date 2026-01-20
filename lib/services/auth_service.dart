import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import '../models/user_model.dart';

class AuthService {
  final FirebaseAuth _auth = FirebaseAuth.instance;
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;

  User? get currentUser => _auth.currentUser;

  Stream<User?> get authStateChanges => _auth.authStateChanges();

  Future<UserCredential> registerWithEmailPassword({
    required String email,
    required String password,
    required String name,
    String? phone,
  }) async {
    try {
      // Create user
      final UserCredential credential =
          await _auth.createUserWithEmailAndPassword(
        email: email,
        password: password,
      );

      // Create user document in Firestore
      final userModel = UserModel(
        uid: credential.user!.uid,
        email: email,
        name: name,
        phone: phone,
      );

      await _firestore
          .collection('users')
          .doc(credential.user!.uid)
          .set(userModel.toMap());

      return credential;
    } catch (e) {
      rethrow;
    }
  }

  Future<UserCredential> signInWithEmailPassword({
    required String email,
    required String password,
  }) async {
    try {
      return await _auth.signInWithEmailAndPassword(
        email: email,
        password: password,
      );
    } catch (e) {
      rethrow;
    }
  }

  Future<void> signOut() async {
    await _auth.signOut();
  }

  Future<void> resetPassword(String email) async {
    await _auth.sendPasswordResetEmail(email: email);
  }

  Future<UserModel?> getUserData(String uid) async {
    try {
      final doc = await _firestore.collection('users').doc(uid).get();
      if (doc.exists) {
        return UserModel.fromMap(doc.data()!, uid);
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  Future<void> updateUserData(UserModel user) async {
    await _firestore.collection('users').doc(user.uid).update(user.toMap());
  }

  Future<void> addAddress(String uid, Address address) async {
    final userDoc = _firestore.collection('users').doc(uid);
    final userData = await userDoc.get();
    
    if (userData.exists) {
      final user = UserModel.fromMap(userData.data()!, uid);
      final updatedAddresses = [...user.addresses, address];
      await userDoc.update({'addresses': updatedAddresses.map((a) => a.toMap()).toList()});
    }
  }

  Future<void> updateAddress(String uid, Address address) async {
    final userDoc = _firestore.collection('users').doc(uid);
    final userData = await userDoc.get();
    
    if (userData.exists) {
      final user = UserModel.fromMap(userData.data()!, uid);
      final updatedAddresses = user.addresses.map((a) => 
        a.id == address.id ? address : a
      ).toList();
      await userDoc.update({'addresses': updatedAddresses.map((a) => a.toMap()).toList()});
    }
  }

  Future<void> deleteAddress(String uid, String addressId) async {
    final userDoc = _firestore.collection('users').doc(uid);
    final userData = await userDoc.get();
    
    if (userData.exists) {
      final user = UserModel.fromMap(userData.data()!, uid);
      final updatedAddresses = user.addresses.where((a) => a.id != addressId).toList();
      await userDoc.update({'addresses': updatedAddresses.map((a) => a.toMap()).toList()});
    }
  }
}


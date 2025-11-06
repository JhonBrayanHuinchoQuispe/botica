import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:botica_san_antonio/main.dart';

void main() {
  testWidgets('App should start with splash screen', (WidgetTester tester) async {
    // Construir la app
    await tester.pumpWidget(const BoticaSanAntonioApp());

    // Verificar que existe el splash screen
    expect(find.byType(MaterialApp), findsOneWidget);
    
    // Verificar el título de la app
    expect(find.text('Botica San Antonio'), findsOneWidget);
  });
}
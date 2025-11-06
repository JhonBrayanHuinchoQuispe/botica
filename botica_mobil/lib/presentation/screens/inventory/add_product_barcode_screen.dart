import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../../core/config/theme.dart';
import '../../../data/models/product.dart';
import '../../../data/services/barcode_service.dart';
import 'product_form_screen.dart';

class AddProductBarcodeScreen extends StatefulWidget {
  final VoidCallback? onProductAdded;
  
  const AddProductBarcodeScreen({super.key, this.onProductAdded});

  @override
  State<AddProductBarcodeScreen> createState() => _AddProductBarcodeScreenState();
}

class _AddProductBarcodeScreenState extends State<AddProductBarcodeScreen> with SingleTickerProviderStateMixin {
  final BarcodeService _barcodeService = BarcodeService();
  MobileScannerController cameraController = MobileScannerController();
  bool _isProcessing = false;
  bool _flashOn = false;
  String? _lastScannedCode;
  late AnimationController _scanController;
  late Animation<double> _scanProgress;

  @override
  void initState() {
    super.initState();
    _inicializarCache();
    _scanController = AnimationController(
      duration: const Duration(milliseconds: 1600),
      vsync: this,
    );
    _scanProgress = Tween<double>(begin: 0, end: 1).animate(
      CurvedAnimation(parent: _scanController, curve: Curves.easeInOut),
    );
    _scanController.repeat();
  }

  Future<void> _inicializarCache() async {
    // Verificar y actualizar cache en background
    _barcodeService.verificarYActualizarCache();
  }

  @override
  void dispose() {
    cameraController.dispose();
    _scanController.dispose();
    super.dispose();
  }

  Future<void> _onBarcodeDetected(BarcodeCapture capture) async {
    if (_isProcessing) return;

    final List<Barcode> barcodes = capture.barcodes;
    if (barcodes.isEmpty) return;

    final String? code = barcodes.first.rawValue;
    if (code == null || code.isEmpty) return;

    // Evitar procesar el mismo código múltiples veces
    if (_lastScannedCode == code) return;
    _lastScannedCode = code;

    setState(() {
      _isProcessing = true;
    });

    try {
      // Pausar la cámara mientras procesamos
      await cameraController.stop();

      // Buscar el producto
      final resultado = await _barcodeService.buscarProductoPorCodigo(code);

      if (!mounted) return;

      if (resultado.encontrado && resultado.producto != null) {
        // Producto encontrado - mostrar formulario de ajuste de stock
        _mostrarFormularioProductoExistente(resultado.producto!, code);
      } else {
        // Producto no encontrado - mostrar formulario de nuevo producto
        _mostrarFormularioProductoNuevo(code);
      }
    } catch (e) {
      if (mounted) {
        _mostrarError('Error al procesar código: $e');
      }
    } finally {
      if (mounted) {
        setState(() {
          _isProcessing = false;
          _lastScannedCode = null;
        });
      }
    }
  }

  void _mostrarFormularioProductoExistente(Product producto, String codigo) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        height: MediaQuery.of(context).size.height * 0.85,
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        child: ProductFormScreen(
          isNewProduct: false,
          existingProduct: producto,
          scannedBarcode: codigo,
          onProductSaved: () {
            Navigator.of(context).pop();
            _mostrarExito('Stock actualizado correctamente');
            widget.onProductAdded?.call();
            _reiniciarEscaneo();
          },
        ),
      ),
    );
  }

  void _mostrarFormularioProductoNuevo(String codigo) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        height: MediaQuery.of(context).size.height * 0.85,
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        child: ProductFormScreen(
          isNewProduct: true,
          scannedBarcode: codigo,
          onProductSaved: () {
            Navigator.of(context).pop();
            _mostrarExito('Producto agregado correctamente');
            widget.onProductAdded?.call();
            _reiniciarEscaneo();
          },
        ),
      ),
    );
  }

  void _mostrarError(String mensaje) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(mensaje),
        backgroundColor: Colors.red,
        duration: const Duration(seconds: 3),
      ),
    );
    _reiniciarEscaneo();
  }

  void _mostrarExito(String mensaje) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(mensaje),
        backgroundColor: Colors.green,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  Future<void> _reiniciarEscaneo() async {
    await Future.delayed(const Duration(milliseconds: 500));
    if (mounted) {
      await cameraController.start();
    }
  }

  void _toggleFlash() {
    setState(() {
      _flashOn = !_flashOn;
    });
    cameraController.toggleTorch();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: const Text(
          'Escanear Código de Barras',
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: Colors.black,
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          IconButton(
            icon: Icon(
              _flashOn ? Icons.flash_on : Icons.flash_off,
              color: Colors.white,
            ),
            onPressed: _toggleFlash,
          ),
        ],
      ),
      body: LayoutBuilder(
        builder: (context, constraints) {
          final width = constraints.maxWidth;
          final height = constraints.maxHeight;
          // Ventana rectangular y delgada, similar a un visor
          final scanWindow = Rect.fromCenter(
            center: Offset(width / 2, height * 0.45),
            width: width * 0.82,
            height: height * 0.34,
          );

          return Stack(
            children: [
              // Cámara restringida al rectángulo
              MobileScanner(
                controller: cameraController,
                scanWindow: scanWindow,
                onDetect: _onBarcodeDetected,
              ),

              // Overlay fino con línea de escaneo animada
              AnimatedBuilder(
                animation: _scanProgress,
                builder: (context, _) {
                  return CustomPaint(
                    painter: _ThinScannerOverlay(
                      progress: _scanProgress.value,
                      scanArea: scanWindow,
                    ),
                    child: Container(),
                  );
                },
              ),

              // Indicador de procesamiento
              if (_isProcessing)
                Container(
                  color: Colors.black54,
                  child: const Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                        ),
                        SizedBox(height: 16),
                        Text(
                          'Buscando producto...',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
            ],
          );
        },
      ),
      bottomNavigationBar: Container(
        color: Colors.black,
        padding: const EdgeInsets.all(20),
        child: const Text(
          'Apunta la cámara hacia el código de barras del producto',
          textAlign: TextAlign.center,
          style: TextStyle(
            color: Colors.white,
            fontSize: 16,
          ),
        ),
      ),
    );
  }

  // Overlay delgado tipo visor, con esquinas rojas y borde blanco
  // y línea de escaneo animada.
}

class _ThinScannerOverlay extends CustomPainter {
  final double progress;
  final Rect scanArea;
  _ThinScannerOverlay({required this.progress, required this.scanArea});

  @override
  void paint(Canvas canvas, Size size) {
    final dimPaint = Paint()
      ..color = Colors.black.withOpacity(0.55)
      ..style = PaintingStyle.fill;

    canvas.drawPath(
      Path.combine(
        PathOperation.difference,
        Path()..addRect(Rect.fromLTWH(0, 0, size.width, size.height)),
        Path()..addRect(scanArea),
      ),
      dimPaint,
    );

    // Borde blanco delgado
    final borderPaint = Paint()
      ..color = Colors.white.withOpacity(0.8)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.5;
    final rrect = RRect.fromRectAndRadius(scanArea, const Radius.circular(12));
    canvas.drawRRect(rrect, borderPaint);

    // Esquinas rojas finas
    final cornerPaint = Paint()
      ..color = AppTheme.primaryRed
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.5;
    const cornerLength = 22.0;
    for (final corner in [
      (scanArea.topLeft, [const Offset(cornerLength, 0), const Offset(0, cornerLength)]),
      (scanArea.topRight, [const Offset(-cornerLength, 0), const Offset(0, cornerLength)]),
      (scanArea.bottomLeft, [const Offset(cornerLength, 0), const Offset(0, -cornerLength)]),
      (scanArea.bottomRight, [const Offset(-cornerLength, 0), const Offset(0, -cornerLength)]),
    ]) {
      for (final offset in corner.$2) {
        canvas.drawLine(corner.$1, corner.$1 + offset, cornerPaint);
      }
    }

    // Línea de escaneo delgada
    final lineY = scanArea.top + (scanArea.height * progress);
    final lineRect = Rect.fromLTWH(scanArea.left, lineY, scanArea.width, 1.5);
    final gradient = LinearGradient(
      colors: [
        AppTheme.primaryRed.withOpacity(0.0),
        AppTheme.primaryRed.withOpacity(0.9),
        AppTheme.primaryRed.withOpacity(0.0),
      ],
      stops: const [0.0, 0.5, 1.0],
    );
    final linePaint = Paint()..shader = gradient.createShader(lineRect);
    canvas.drawRect(lineRect, linePaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

// Clase para el overlay del escáner
class QrScannerOverlayShape extends ShapeBorder {
  const QrScannerOverlayShape({
    this.borderColor = Colors.red,
    this.borderWidth = 3.0,
    this.overlayColor = const Color.fromRGBO(0, 0, 0, 80),
    this.borderRadius = 0,
    this.borderLength = 40,
    this.cutOutSize = 250,
  });

  final Color borderColor;
  final double borderWidth;
  final Color overlayColor;
  final double borderRadius;
  final double borderLength;
  final double cutOutSize;

  @override
  EdgeInsetsGeometry get dimensions => const EdgeInsets.all(10);

  @override
  Path getInnerPath(Rect rect, {TextDirection? textDirection}) {
    return Path()
      ..fillType = PathFillType.evenOdd
      ..addPath(getOuterPath(rect), Offset.zero);
  }

  @override
  Path getOuterPath(Rect rect, {TextDirection? textDirection}) {
    Path _getLeftTopPath(Rect rect) {
      return Path()
        ..moveTo(rect.left, rect.bottom)
        ..lineTo(rect.left, rect.top + borderRadius)
        ..quadraticBezierTo(rect.left, rect.top, rect.left + borderRadius, rect.top)
        ..lineTo(rect.right, rect.top);
    }

    return _getLeftTopPath(rect)
      ..lineTo(rect.right, rect.bottom)
      ..lineTo(rect.left, rect.bottom)
      ..lineTo(rect.left, rect.top);
  }

  @override
  void paint(Canvas canvas, Rect rect, {TextDirection? textDirection}) {
    final width = rect.width;
    final borderWidthSize = width / 2;
    final height = rect.height;
    final borderOffset = borderWidth / 2;
    final _borderLength = borderLength > cutOutSize / 2 + borderOffset
        ? borderWidthSize / 2
        : borderLength;
    final _cutOutSize = cutOutSize < width ? cutOutSize : width - borderOffset;

    final backgroundPaint = Paint()
      ..color = overlayColor
      ..style = PaintingStyle.fill;

    final borderPaint = Paint()
      ..color = borderColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = borderWidth;

    final boxPaint = Paint()
      ..color = borderColor
      ..style = PaintingStyle.fill
      ..blendMode = BlendMode.dstOut;

    final cutOutRect = Rect.fromLTWH(
      rect.left + width / 2 - _cutOutSize / 2 + borderOffset,
      rect.top + height / 2 - _cutOutSize / 2 + borderOffset,
      _cutOutSize - borderOffset * 2,
      _cutOutSize - borderOffset * 2,
    );

    canvas
      ..saveLayer(
        rect,
        backgroundPaint,
      )
      ..drawRect(rect, backgroundPaint)
      ..drawRRect(
        RRect.fromRectAndRadius(
          cutOutRect,
          Radius.circular(borderRadius),
        ),
        boxPaint,
      )
      ..restore();

    // Dibujar las esquinas del marco
    final path = Path()
      ..moveTo(cutOutRect.left - borderOffset, cutOutRect.top + _borderLength)
      ..lineTo(cutOutRect.left - borderOffset, cutOutRect.top + borderRadius)
      ..quadraticBezierTo(cutOutRect.left - borderOffset, cutOutRect.top - borderOffset,
          cutOutRect.left + borderRadius, cutOutRect.top - borderOffset)
      ..lineTo(cutOutRect.left + _borderLength, cutOutRect.top - borderOffset);

    canvas.drawPath(path, borderPaint);

    // Esquina superior derecha
    final path2 = Path()
      ..moveTo(cutOutRect.right + borderOffset, cutOutRect.top + _borderLength)
      ..lineTo(cutOutRect.right + borderOffset, cutOutRect.top + borderRadius)
      ..quadraticBezierTo(cutOutRect.right + borderOffset, cutOutRect.top - borderOffset,
          cutOutRect.right - borderRadius, cutOutRect.top - borderOffset)
      ..lineTo(cutOutRect.right - _borderLength, cutOutRect.top - borderOffset);

    canvas.drawPath(path2, borderPaint);

    // Esquina inferior izquierda
    final path3 = Path()
      ..moveTo(cutOutRect.left - borderOffset, cutOutRect.bottom - _borderLength)
      ..lineTo(cutOutRect.left - borderOffset, cutOutRect.bottom - borderRadius)
      ..quadraticBezierTo(cutOutRect.left - borderOffset, cutOutRect.bottom + borderOffset,
          cutOutRect.left + borderRadius, cutOutRect.bottom + borderOffset)
      ..lineTo(cutOutRect.left + _borderLength, cutOutRect.bottom + borderOffset);

    canvas.drawPath(path3, borderPaint);

    // Esquina inferior derecha
    final path4 = Path()
      ..moveTo(cutOutRect.right + borderOffset, cutOutRect.bottom - _borderLength)
      ..lineTo(cutOutRect.right + borderOffset, cutOutRect.bottom - borderRadius)
      ..quadraticBezierTo(cutOutRect.right + borderOffset, cutOutRect.bottom + borderOffset,
          cutOutRect.right - borderRadius, cutOutRect.bottom + borderOffset)
      ..lineTo(cutOutRect.right - _borderLength, cutOutRect.bottom + borderOffset);

    canvas.drawPath(path4, borderPaint);
  }

  @override
  ShapeBorder scale(double t) {
    return QrScannerOverlayShape(
      borderColor: borderColor,
      borderWidth: borderWidth,
      overlayColor: overlayColor,
    );
  }
}
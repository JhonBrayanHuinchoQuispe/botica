import 'package:flutter/material.dart';
import '../../../core/config/theme.dart';
import '../../../data/models/product.dart';
import '../../../data/services/product_service.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'add_product_screen.dart';
import 'adjust_stock_screen.dart';

class ScanProductScreen extends StatefulWidget {
  const ScanProductScreen({Key? key}) : super(key: key);

  @override
  State<ScanProductScreen> createState() => _ScanProductScreenState();
}

class _ScanProductScreenState extends State<ScanProductScreen> with SingleTickerProviderStateMixin {
  late MobileScannerController controller;
  late AnimationController _animationController;
  late Animation<double> _scanAnimation;
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _priceController = TextEditingController();
  final _stockController = TextEditingController();
  final _batchController = TextEditingController();
  final _laboratoryController = TextEditingController();
  final _productService = ProductService();
  DateTime? _expiryDate;
  bool _isFlashOn = false;
  bool _isFrontCamera = false;
  bool _isProcessing = false;

  @override
  void initState() {
    super.initState();
    controller = MobileScannerController();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    _scanAnimation = Tween<double>(begin: 0, end: 1).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeInOut),
    );
    _animationController.repeat(period: const Duration(milliseconds: 1800));
  }

  @override
  void dispose() {
    controller.dispose();
    _animationController.dispose();
    _nameController.dispose();
    _priceController.dispose();
    _stockController.dispose();
    _batchController.dispose();
    _laboratoryController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: _buildAppBar(),
      body: LayoutBuilder(
        builder: (context, constraints) {
          final width = constraints.maxWidth;
          final height = constraints.maxHeight;
          // Ventana de escaneo: rectangular, delgada y amplia
          final scanWindow = Rect.fromCenter(
            center: Offset(width / 2, height * 0.45),
            width: width * 0.82,
            height: height * 0.34,
          );

          return Stack(
            children: [
              MobileScanner(
                controller: controller,
                scanWindow: scanWindow,
                onDetect: (capture) {
                  final List<Barcode> barcodes = capture.barcodes;
                  if (barcodes.isNotEmpty && barcodes.first.rawValue != null) {
                    _onBarcodeDetected(context, barcodes.first.rawValue!);
                  }
                },
              ),
              // Overlay con marco y línea de escaneo animada
              AnimatedBuilder(
                animation: _scanAnimation,
                builder: (context, _) {
                  return CustomPaint(
                    painter: ScannerOverlay(
                      progress: _scanAnimation.value,
                      scanArea: scanWindow,
                    ),
                    child: Container(),
                  );
                },
              ),
              Positioned(
                bottom: 40,
                left: 0,
                right: 0,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.center_focus_strong, color: Colors.white.withOpacity(0.85)),
                    const SizedBox(width: 8),
                    Text(
                      'Apunta el código dentro del marco',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.85),
                        fontSize: 16,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      backgroundColor: Colors.transparent,
      elevation: 0,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back, color: Colors.white),
        onPressed: () => Navigator.pop(context),
      ),
      title: const Text(
        'Escanear Producto',
        style: TextStyle(color: Colors.white),
      ),
      actions: [
        IconButton(
          icon: Icon(
            _isFlashOn ? Icons.flash_on : Icons.flash_off,
            color: Colors.white,
          ),
          onPressed: () {
            setState(() {
              _isFlashOn = !_isFlashOn;
              controller.toggleTorch();
            });
          },
        ),
        IconButton(
          icon: Icon(
            _isFrontCamera ? Icons.camera_front : Icons.camera_rear,
            color: Colors.white,
          ),
          onPressed: () {
            setState(() {
              _isFrontCamera = !_isFrontCamera;
              controller.switchCamera();
            });
          },
        ),
      ],
    );
  }

  Future<void> _registerStockEntry(
    BuildContext context,
    String productId,
    int quantity,
    String batchNumber,
    DateTime expiryDate,
  ) async {
    if (!_formKey.currentState!.validate()) return;
    
    setState(() => _isProcessing = true);
    
    try {
      await _productService.registerStockEntry(
        productId: productId,
        quantity: quantity,
        batchNumber: batchNumber,
        expiryDate: expiryDate,
      );

      if (!mounted) return;
      
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Entrada registrada correctamente'),
          backgroundColor: Colors.green,
        ),
      );
      
      Navigator.pop(context);
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error al registrar entrada: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isProcessing = false);
      }
    }
  }

  void _onBarcodeDetected(BuildContext context, String barcode) async {
    if (_isProcessing) return; 
    
    setState(() => _isProcessing = true);
    controller.stop();
    HapticFeedback.mediumImpact();

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(
        child: CircularProgressIndicator(color: Colors.white),
      ),
    );

    try {
      final existingProduct = await _productService.findByBarcode(barcode);
      
      if (!context.mounted) return;
      Navigator.pop(context); // Cerrar loading

      if (existingProduct != null) {
        final result = await Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => AdjustStockScreen(product: existingProduct),
          ),
        );
        if (result == true && context.mounted) {
          Navigator.pop(context, true); 
        } else {
          controller.start();
          setState(() => _isProcessing = false);
        }
      } else {
        final result = await Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => AddProductScreen(barcode: barcode),
          ),
        );
         if (result != null && context.mounted) {
          Navigator.pop(context, result); 
        } else {
          controller.start();
          setState(() => _isProcessing = false);
        }
      }
    } catch (e) {
      if (!context.mounted) return;
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error al buscar producto: $e'),
          backgroundColor: Colors.red,
        ),
      );
      controller.start();
      setState(() => _isProcessing = false);
    }
  }

  void _showNewProductDialog(BuildContext context, String barcode) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => _ProductDialog(
        formKey: _formKey,
        title: 'Nuevo Producto',
        content: [
          _buildBarcodeDisplay(barcode),
          const SizedBox(height: 20),
          _buildInputField(
            controller: _nameController,
            label: 'Nombre del producto',
            icon: Icons.medication_outlined,
            validator: (value) =>
                value?.isEmpty ?? true ? 'Ingrese el nombre del producto' : null,
          ),
          _buildInputField(
            controller: _priceController,
            label: 'Precio (S/.)',
            icon: Icons.attach_money,
            keyboardType: TextInputType.number,
            validator: _validatePrice,
          ),
          _buildInputField(
            controller: _stockController,
            label: 'Stock inicial',
            icon: Icons.inventory_2_outlined,
            keyboardType: TextInputType.number,
            validator: _validateStock,
          ),
          _buildDatePicker(),
          _buildInputField(
            controller: _batchController,
            label: 'Número de lote',
            icon: Icons.numbers_outlined,
          ),
          _buildInputField(
            controller: _laboratoryController,
            label: 'Laboratorio',
            icon: Icons.business_outlined,
          ),
        ],
        onConfirm: () => _saveProduct(context, barcode),
        isLoading: _isProcessing,
      ),
    );
  }

  void _showStockEntryDialog(BuildContext context, String barcode, Product product) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => _ProductDialog(
        formKey: _formKey,
        title: 'Entrada de Stock',
        content: [
          _buildBarcodeDisplay(barcode),
          const SizedBox(height: 16),
          _buildProductInfoCard(product),
          const SizedBox(height: 20),
          _buildInputField(
            controller: _stockController,
            label: 'Cantidad de entrada',
            icon: Icons.inventory_2_outlined,
            keyboardType: TextInputType.number,
            validator: _validateStock,
          ),
          _buildDatePicker(),
          _buildInputField(
            controller: _batchController,
            label: 'Número de lote',
            icon: Icons.numbers_outlined,
          ),
        ],
        onConfirm: () => _registerStockEntry(
          context,
          product.id,
          int.parse(_stockController.text),
          _batchController.text,
          _expiryDate!,
        ),
        isLoading: _isProcessing,
        confirmText: 'Registrar Entrada',
      ),
    );
  }

  Widget _buildProductInfoCard(Product product) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.primaryRed.withOpacity(0.05),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppTheme.primaryRed.withOpacity(0.2)),
      ),
      child: Row(
        children: [
          Icon(Icons.info_outline_rounded, color: AppTheme.primaryRed),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  product.name,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                ),
                const SizedBox(height: 4),
                Text('Stock actual: ${product.stock} unidades'),
                Text('Precio: S/. ${product.price.toStringAsFixed(2)}'),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBarcodeDisplay(String barcode) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.grey.shade100,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.grey.shade300),
      ),
      child: Row(
        children: [
          Icon(Icons.qr_code, color: Colors.grey.shade600, size: 20),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              barcode,
              style: const TextStyle(
                fontFamily: 'monospace',
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInputField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    TextInputType? keyboardType,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
        ),
        filled: true,
        fillColor: Colors.grey.shade50,
      ),
      keyboardType: keyboardType,
      validator: validator,
    );
  }

  Widget _buildDatePicker() {
    return InkWell(
      onTap: () async {
        final date = await showDatePicker(
          context: context,
          initialDate: _expiryDate ?? DateTime.now().add(const Duration(days: 365)),
          firstDate: DateTime.now(),
          lastDate: DateTime.now().add(const Duration(days: 3650)),
        );
        if (date != null) {
          setState(() => _expiryDate = date);
        }
      },
      child: InputDecorator(
        decoration: InputDecoration(
          labelText: 'Fecha de vencimiento',
          prefixIcon: const Icon(Icons.calendar_today_outlined),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          filled: true,
          fillColor: Colors.grey.shade50,
        ),
        child: Text(
          _expiryDate != null 
              ? DateFormat('dd/MM/yyyy').format(_expiryDate!)
              : 'Seleccionar fecha',
          style: TextStyle(
            color: _expiryDate != null ? Colors.black87 : Colors.grey.shade600,
          ),
        ),
      ),
    );
  }

  String? _validatePrice(String? value) {
    if (value?.isEmpty ?? true) return 'Ingrese el precio';
    final price = double.tryParse(value!);
    if (price == null || price <= 0) {
      return 'Ingrese un precio válido';
    }
    return null;
  }

  String? _validateStock(String? value) {
    if (value?.isEmpty ?? true) return 'Ingrese la cantidad';
    final stock = int.tryParse(value!);
    if (stock == null || stock <= 0) {
      return 'Ingrese una cantidad válida';
    }
    return null;
  }

  Future<void> _saveProduct(BuildContext context, String barcode) async {
    if (!_formKey.currentState!.validate()) return;
    if (_expiryDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Por favor seleccione la fecha de vencimiento'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() => _isProcessing = true);

    try {
      // Usar el constructor fromScanner del modelo Product
      final product = Product.fromScanner(
        barcode: barcode,
        name: _nameController.text.trim(),
        price: double.parse(_priceController.text),
        stockEntry: int.parse(_stockController.text),
        expiryDate: _expiryDate!,
        batchNumber: _batchController.text.trim(),
        laboratory: _laboratoryController.text.trim(),
        category: 'General', // Puedes agregar un campo para la categoría si lo necesitas
      );

      await _productService.saveProduct(product);

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Producto registrado correctamente'),
          backgroundColor: Colors.green,
        ),
      );

      Navigator.pop(context);
      Navigator.pop(context, product);
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error al registrar el producto: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isProcessing = false);
      }
    }
  }
}

class _ProductDialog extends StatefulWidget {
  final GlobalKey<FormState> formKey;
  final String title;
  final List<Widget> content;
  final VoidCallback onConfirm;
  final bool isLoading;
  final String confirmText;

  const _ProductDialog({
    required this.formKey,
    required this.title,
    required this.content,
    required this.onConfirm,
    required this.isLoading,
    this.confirmText = 'Guardar',
  });

  @override
  __ProductDialogState createState() => __ProductDialogState();
}

class __ProductDialogState extends State<_ProductDialog> {
  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      titlePadding: const EdgeInsets.fromLTRB(24, 24, 24, 0),
      contentPadding: const EdgeInsets.fromLTRB(24, 20, 24, 0),
      actionsPadding: const EdgeInsets.fromLTRB(24, 20, 24, 24),
      title: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppTheme.primaryRed.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(
              Icons.add_circle_outline_rounded,
              color: AppTheme.primaryRed,
              size: 24,
            ),
          ),
          const SizedBox(width: 12),
          Text(
            widget.title,
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 20),
          ),
        ],
      ),
      content: SingleChildScrollView(
        child: Form(
          key: widget.formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: widget.content,
          ),
        ),
      ),
      actions: [
        TextButton(
          onPressed: () {
            Navigator.pop(context);
          },
          child: Text('Cancelar', style: TextStyle(color: Colors.grey.shade600)),
        ),
        ElevatedButton(
          style: ElevatedButton.styleFrom(
            backgroundColor: AppTheme.primaryRed,
            foregroundColor: Colors.white,
            elevation: 0,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          ),
          onPressed: widget.isLoading ? null : widget.onConfirm,
          child: widget.isLoading
              ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                )
              : Text(widget.confirmText),
        ),
      ],
    );
  }
}

class ScannerOverlay extends CustomPainter {
  final double progress;
  final Rect scanArea;
  ScannerOverlay({required this.progress, required this.scanArea});

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

    // Borde suave y delgado
    final borderPaint = Paint()
      ..color = Colors.white.withOpacity(0.8)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.5;
    final rrect = RRect.fromRectAndRadius(scanArea, const Radius.circular(12));
    canvas.drawRRect(rrect, borderPaint);

    // Esquinas rojas finas (tipo visor)
    final cornerPaint = Paint()
      ..color = AppTheme.primaryRed
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.5;

    const cornerLength = 22.0;

    // Esquinas
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

    // Línea de escaneo animada (delgada)
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
    final linePaint = Paint()
      ..shader = gradient.createShader(lineRect);
    canvas.drawRect(lineRect, linePaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
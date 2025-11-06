import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import '../../../core/config/theme.dart';
import '../../../data/models/product.dart';
import '../../../data/services/product_service.dart';

class AdjustStockScreen extends StatefulWidget {
  final Product product;

  const AdjustStockScreen({
    super.key, 
    required this.product,
  });

  @override
  State<AdjustStockScreen> createState() => _AdjustStockScreenState();
}

class _AdjustStockScreenState extends State<AdjustStockScreen> {
  late TextEditingController _quantityController;
  late TextEditingController _reasonController;
  late TextEditingController _supplierController;
  late TextEditingController _costPriceController;
  late TextEditingController _salePriceController;
  late TextEditingController _batchController;
  DateTime? _expiryDate;
  String _adjustmentType = 'entrada';
  bool _isLoading = false;
  final ProductService _productService = ProductService();

  @override
  void initState() {
    super.initState();
    _quantityController = TextEditingController();
    _reasonController = TextEditingController();
    _supplierController = TextEditingController();
    _costPriceController = TextEditingController();
    _salePriceController = TextEditingController();
    _batchController = TextEditingController();
    
    // Inicializar fecha de vencimiento con un año desde hoy
    _expiryDate = DateTime.now().add(const Duration(days: 365));
  }

  @override
  void dispose() {
    _quantityController.dispose();
    _reasonController.dispose();
    _supplierController.dispose();
    _costPriceController.dispose();
    _salePriceController.dispose();
    _batchController.dispose();
    super.dispose();
  }

  bool _validateFields() {
    if (_quantityController.text.isEmpty) {
      _showError('Ingrese una cantidad');
      return false;
    }

    try {
      final quantity = int.parse(_quantityController.text);
      if (quantity <= 0) {
        _showError('La cantidad debe ser mayor a 0');
        return false;
      }
    } catch (e) {
      _showError('Ingrese una cantidad válida');
      return false;
    }

    if (_supplierController.text.isEmpty) {
      _showError('Ingrese el proveedor');
      return false;
    }

    if (_batchController.text.isEmpty) {
      _showError('Ingrese el número de lote');
      return false;
    }

    if (_expiryDate == null) {
      _showError('Seleccione la fecha de vencimiento');
      return false;
    }

    // Validar precios si se ingresaron
    if (_costPriceController.text.isNotEmpty) {
      try {
        final costPrice = double.parse(_costPriceController.text);
        if (costPrice <= 0) {
          _showError('El precio de compra debe ser mayor a 0');
          return false;
        }
      } catch (e) {
        _showError('Ingrese un precio de compra válido');
        return false;
      }
    }

    if (_salePriceController.text.isNotEmpty) {
      try {
        final salePrice = double.parse(_salePriceController.text);
        if (salePrice <= 0) {
          _showError('El precio de venta debe ser mayor a 0');
          return false;
        }
      } catch (e) {
        _showError('Ingrese un precio de venta válido');
        return false;
      }
    }

    return true;
  }

  void _showError(String message) {
    if (!mounted) return;
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(
              Icons.error_outline,
              color: Colors.white,
              size: 16,
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                message,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 14,
                ),
              ),
            ),
          ],
        ),
        backgroundColor: Colors.red,
        behavior: SnackBarBehavior.floating,
        margin: EdgeInsets.only(
          bottom: MediaQuery.of(context).size.height - 100,
          left: 20,
          right: 20,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
        ),
        duration: const Duration(seconds: 3),
      ),
    );
  }

  Future<void> _handleSave() async {
    if (!_validateFields()) return;

    setState(() => _isLoading = true);

    try {
      final quantity = int.parse(_quantityController.text);
      final supplier = _supplierController.text;
      final batchNumber = _batchController.text;
      final expiryDate = _expiryDate!;
      
      // Calcular nuevo stock
      final newStock = _adjustmentType == 'entrada' 
          ? widget.product.stock + quantity 
          : widget.product.stock - quantity;
      
      if (newStock < 0) {
        _showError('El stock no puede ser negativo');
        return;
      }
      
      // Precios opcionales - usar actuales si no se especifican nuevos
      double costPrice = widget.product.costPrice ?? 0.0;
      double salePrice = widget.product.price;
      
      if (_costPriceController.text.isNotEmpty) {
        costPrice = double.parse(_costPriceController.text);
      }
      
      if (_salePriceController.text.isNotEmpty) {
        salePrice = double.parse(_salePriceController.text);
      }

      // Crear producto actualizado
      final updatedProduct = Product(
        id: widget.product.id,
        name: widget.product.name,
        brand: widget.product.brand,
        description: widget.product.description,
        category: widget.product.category,
        stock: newStock,
        minStock: widget.product.minStock,
        maxStock: widget.product.maxStock,
        price: salePrice,
        costPrice: costPrice,
        barcode: widget.product.barcode,
        batchNumber: batchNumber,
        expiryDate: expiryDate,
        supplier: supplier,
        location: widget.product.location,
        isActive: widget.product.isActive,
        createdAt: widget.product.createdAt,
        updatedAt: DateTime.now(),
      );

      // Actualizar producto usando ProductService
      final success = await _productService.updateProduct(widget.product.id, updatedProduct);
      
      if (success) {
        if (mounted) {
          Navigator.of(context).pop(updatedProduct);
          
          // Mostrar mensaje de éxito
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  const Icon(
                    Icons.check_circle_outline,
                    color: Colors.white,
                    size: 20,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    _adjustmentType == 'entrada'
                        ? 'Se agregaron $quantity unidades al stock'
                        : 'Se retiraron $quantity unidades del stock',
                  ),
                ],
              ),
              backgroundColor: Colors.green,
              behavior: SnackBarBehavior.floating,
              margin: const EdgeInsets.only(
                bottom: 20,
                left: 20,
                right: 20,
              ),
              padding: const EdgeInsets.symmetric(
                horizontal: 20,
                vertical: 15,
              ),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              duration: const Duration(seconds: 3),
            ),
          );
        }
      } else {
        _showError('Error al actualizar el producto');
      }
    } catch (e) {
      _showError('Error al actualizar el stock: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_adjustmentType == 'entrada' ? 'Agregar Stock' : 'Retirar Stock'),
        backgroundColor: AppTheme.primaryRed,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Información del producto
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppTheme.primaryRed.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(
                        Icons.medication_rounded,
                        color: AppTheme.primaryRed,
                        size: 24,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            widget.product.name,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          if (widget.product.concentration != null && widget.product.concentration!.isNotEmpty) ...[
                            const SizedBox(height: 2),
                            Text(
                              widget.product.concentration!,
                              style: TextStyle(
                                color: Colors.grey.shade700,
                                fontSize: 13,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                          const SizedBox(height: 4),
                          Text(
                            'Categoría: ${widget.product.category ?? 'Sin categoría'}',
                            style: TextStyle(
                              color: Colors.grey.shade600,
                              fontSize: 14,
                            ),
                          ),
                          Text(
                            'Precio: S/. ${widget.product.price.toStringAsFixed(2)}',
                            style: TextStyle(
                              color: Colors.grey.shade600,
                              fontSize: 14,
                            ),
                          ),
                          Text(
                            'Stock actual: ${widget.product.stock}',
                            style: TextStyle(
                              color: Colors.grey.shade600,
                              fontSize: 14,
                            ),
                          ),
                          Text(
                            'Vence: ${DateFormat('dd/MM/yyyy').format(widget.product.expiryDate)}',
                            style: TextStyle(
                              color: Colors.grey.shade600,
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Tipo de ajuste
              Text(
                'Tipo de Ajuste',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Colors.grey.shade800,
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _AdjustmentTypeButton(
                      icon: Icons.add_circle_outline,
                      label: 'Entrada',
                      isSelected: _adjustmentType == 'entrada',
                      onTap: () => setState(() => _adjustmentType = 'entrada'),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: _AdjustmentTypeButton(
                      icon: Icons.remove_circle_outline,
                      label: 'Salida',
                      isSelected: _adjustmentType == 'salida',
                      onTap: () => setState(() => _adjustmentType = 'salida'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // Cantidad a agregar
              _buildSectionTitle('Cantidad a Agregar'),
              const SizedBox(height: 12),
              _buildTextField(
                controller: _quantityController,
                hintText: 'Ingrese la cantidad',
                icon: Icons.inventory_2_outlined,
                keyboardType: TextInputType.number,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              ),
              const SizedBox(height: 24),

              // Proveedor
              _buildSectionTitle('Proveedor'),
              const SizedBox(height: 12),
              _buildTextField(
                controller: _supplierController,
                hintText: 'Ingrese el proveedor',
                icon: Icons.business_outlined,
              ),
              const SizedBox(height: 24),

              // Precios
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildSectionTitle('Precio de Compra'),
                        const SizedBox(height: 12),
                        _buildTextField(
                          controller: _costPriceController,
                          hintText: widget.product.costPrice != null 
                              ? 'Actual: S/. ${widget.product.costPrice!.toStringAsFixed(2)}'
                              : 'Precio de compra (opcional)',
                          icon: Icons.attach_money_outlined,
                          keyboardType: TextInputType.numberWithOptions(decimal: true),
                          inputFormatters: [
                            FilteringTextInputFormatter.allow(RegExp(r'^\d*\.?\d{0,2}')),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildSectionTitle('Precio de Venta'),
                        const SizedBox(height: 12),
                        _buildTextField(
                          controller: _salePriceController,
                          hintText: 'Actual: S/. ${widget.product.price.toStringAsFixed(2)}',
                          icon: Icons.sell_outlined,
                          keyboardType: TextInputType.numberWithOptions(decimal: true),
                          inputFormatters: [
                            FilteringTextInputFormatter.allow(RegExp(r'^\d*\.?\d{0,2}')),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // Número de lote
              _buildSectionTitle('Número de Lote'),
              const SizedBox(height: 12),
              _buildTextField(
                controller: _batchController,
                hintText: 'Ingrese el número de lote',
                icon: Icons.numbers_outlined,
              ),
              const SizedBox(height: 24),

              // Fecha de vencimiento
              _buildSectionTitle('Fecha de Vencimiento'),
              const SizedBox(height: 12),
              _buildDatePicker(),
              const SizedBox(height: 24),

              // Motivo (opcional)
              _buildSectionTitle('Motivo (Opcional)'),
              const SizedBox(height: 12),
              _buildTextField(
                controller: _reasonController,
                hintText: 'Ingrese el motivo del ajuste',
                icon: Icons.note_outlined,
                maxLines: 3,
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 10,
              offset: const Offset(0, -5),
            ),
          ],
        ),
        child: ElevatedButton(
          onPressed: _isLoading ? null : _handleSave,
          style: ElevatedButton.styleFrom(
            backgroundColor: AppTheme.primaryRed,
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
          child: _isLoading
              ? const SizedBox(
                  height: 20,
                  width: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                )
              : const Text(
                  'Guardar Ajuste',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
        ),
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: TextStyle(
        fontSize: 16,
        fontWeight: FontWeight.bold,
        color: Colors.grey.shade800,
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String hintText,
    required IconData icon,
    TextInputType? keyboardType,
    List<TextInputFormatter>? inputFormatters,
    int maxLines = 1,
  }) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      inputFormatters: inputFormatters,
      maxLines: maxLines,
      decoration: InputDecoration(
        hintText: hintText,
        prefixIcon: Icon(
          icon,
          color: Colors.grey.shade400,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        filled: true,
        fillColor: Colors.grey.shade50,
      ),
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
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
        decoration: BoxDecoration(
          color: Colors.grey.shade50,
          border: Border.all(color: Colors.grey.shade300),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          children: [
            Icon(
              Icons.calendar_today_outlined,
              color: Colors.grey.shade400,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                _expiryDate != null 
                    ? DateFormat('dd/MM/yyyy').format(_expiryDate!)
                    : 'Seleccionar fecha de vencimiento',
                style: TextStyle(
                  color: _expiryDate != null ? Colors.black87 : Colors.grey.shade600,
                  fontSize: 16,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _AdjustmentTypeButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool isSelected;
  final VoidCallback onTap;

  const _AdjustmentTypeButton({
    required this.icon,
    required this.label,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 16),
          decoration: BoxDecoration(
            color: isSelected ? AppTheme.primaryRed : Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isSelected ? AppTheme.primaryRed : Colors.grey.shade300,
            ),
          ),
          child: Column(
            children: [
              Icon(
                icon,
                color: isSelected ? Colors.white : Colors.grey.shade600,
                size: 24,
              ),
              const SizedBox(height: 8),
              Text(
                label,
                style: TextStyle(
                  color: isSelected ? Colors.white : Colors.grey.shade600,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
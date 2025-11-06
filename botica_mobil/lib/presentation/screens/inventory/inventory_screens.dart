import 'package:flutter/material.dart';
import '../../../core/config/theme.dart';
import '../../../data/models/product.dart';
import '../../../data/services/product_service.dart';
import '../../../data/services/cache_service.dart';
import 'add_product_barcode_screen.dart';
import 'package:flutter/services.dart';
import 'edit_product_screen.dart';
import 'adjust_stock_screen.dart'; 
import '../layout/header.dart'; 

class InventoryScreen extends StatefulWidget {
  const InventoryScreen({super.key});

  @override
  State<InventoryScreen> createState() => _InventoryScreenState();
}

class _InventoryScreenState extends State<InventoryScreen> with TickerProviderStateMixin {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  String _selectedFilter = 'Críticos';
  String _sortBy = 'name'; // Opciones: name, stock, expiryDate
  bool _sortAscending = true;
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  
  // Variables para manejo de datos reales
  List<Product> _products = [];
  List<Product> _allProducts = []; // Cache local de todos los productos
  bool _isLoading = true;
  bool _isLoadingMore = false;
  String? _errorMessage;
  final ProductService _productService = ProductService();
  
  // Variables para paginación
  int _currentPage = 1;
  int _totalPages = 1;
  int _totalProducts = 0;
  final int _perPage = 20;
  bool _hasMoreData = true;
  
  // Variables para búsqueda local
  bool _isSearchingLocally = false;
  List<Product> _localSearchResults = [];

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 1200),
      vsync: this,
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeOut),
    );
    _animationController.forward();
    
    // Cargar productos críticos
    _loadProducts();
  }

  // Método para cargar todos los productos desde la API con cache
  Future<void> _loadProducts({bool forceRefresh = false}) async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      // Cargar TODOS los productos desde la API Laravel (con cache)
      final allProducts = await _productService.getAllProducts(forceRefresh: forceRefresh);
      
      setState(() {
        _allProducts = allProducts;
        _isLoading = false;
      });
      
      // Aplicar filtros actuales
      _applyFilters();
      
      print('✅ Productos cargados: ${allProducts.length} totales');
      
      // Mostrar información del cache
      final cacheInfo = await _productService.getCacheInfo();
      if (cacheInfo['hasCache'] == true) {
        print('📦 Cache: ${cacheInfo['productCount']} productos, ${cacheInfo['cacheAge']?.inMinutes ?? 0} min de antigüedad');
      }
      
    } catch (e) {
      // Si falla la API, usar datos de ejemplo solo para desarrollo
      print('❌ Error cargando desde API: $e');
      print('🔄 Usando datos de ejemplo para desarrollo...');
      final sampleProducts = _getSampleProducts();
      
      setState(() {
        _allProducts = sampleProducts;
        _isLoading = false;
        _errorMessage = 'Modo offline - Datos de ejemplo';
      });
      
      // Aplicar filtros actuales
      _applyFilters();
    }
  }

  // Método para refrescar datos
  Future<void> _refreshProducts() async {
    await _loadProducts(forceRefresh: true);
  }

  // Aplicar filtros a los productos
  void _applyFilters() {
    List<Product> filteredProducts = List.from(_allProducts);
    
    // Aplicar filtro específico seleccionado
    switch (_selectedFilter) {
      case 'Críticos':
        // Filtrar solo productos con problemas críticos
        filteredProducts = filteredProducts.where((product) => 
          product.isLowStock || 
          product.isExpiringSoon || 
          product.isExpired || 
          product.isOutOfStock
        ).toList();
        break;
      case 'Normal':
        // Filtrar solo productos en estado normal (sin problemas críticos)
        filteredProducts = filteredProducts.where((product) => 
          !product.isLowStock && 
          !product.isExpiringSoon && 
          !product.isExpired && 
          !product.isOutOfStock
        ).toList();
        break;
      case 'Stock Bajo':
        filteredProducts = filteredProducts.where((product) => product.isLowStock).toList();
        break;
      case 'Por Vencer':
        filteredProducts = filteredProducts.where((product) => product.isExpiringSoon).toList();
        break;
      case 'Vencidos':
        filteredProducts = filteredProducts.where((product) => product.isExpired).toList();
        break;
      case 'Agotados':
        filteredProducts = filteredProducts.where((product) => product.isOutOfStock).toList();
        break;
    }
    
    // Aplicar búsqueda si hay query
    if (_searchQuery.isNotEmpty) {
      final query = _searchQuery.toLowerCase();
      filteredProducts = filteredProducts.where((product) {
        return product.name.toLowerCase().contains(query) ||
               product.brand.toLowerCase().contains(query) ||
               (product.category?.toLowerCase().contains(query) ?? false) ||
               (product.barcode?.toLowerCase().contains(query) ?? false);
      }).toList();
    }
    
    // Aplicar ordenamiento
    _sortProducts(filteredProducts);
    
    setState(() {
      _products = filteredProducts;
    });
  }

  // Realizar búsqueda local
  Future<void> _performLocalSearch(String query) async {
    if (query.isEmpty) {
      setState(() {
        _isSearchingLocally = false;
        _localSearchResults = [];
      });
      _applyFilters();
      return;
    }
    
    setState(() {
      _isSearchingLocally = true;
    });
    
    try {
      final results = await _productService.searchProductsLocally(query);
      
      // Aplicar filtro específico a los resultados de búsqueda
      List<Product> filteredResults = results;
      switch (_selectedFilter) {
        case 'Críticos':
          filteredResults = results.where((product) => 
            product.isLowStock || 
            product.isExpiringSoon || 
            product.isExpired || 
            product.isOutOfStock
          ).toList();
          break;
        case 'Normal':
          filteredResults = results.where((product) => 
            !product.isLowStock && 
            !product.isExpiringSoon && 
            !product.isExpired && 
            !product.isOutOfStock
          ).toList();
          break;
        case 'Stock Bajo':
          filteredResults = results.where((product) => product.isLowStock).toList();
          break;
        case 'Por Vencer':
          filteredResults = results.where((product) => product.isExpiringSoon).toList();
          break;
        case 'Vencidos':
          filteredResults = results.where((product) => product.isExpired).toList();
          break;
        case 'Agotados':
          filteredResults = results.where((product) => product.isOutOfStock).toList();
          break;
      }
      
      setState(() {
        _localSearchResults = filteredResults;
        _products = filteredResults;
        _isSearchingLocally = false;
      });
    } catch (e) {
      print('Error en búsqueda local: $e');
      setState(() {
        _isSearchingLocally = false;
      });
      _applyFilters(); // Fallback a filtros normales
    }
  }

  // Manejar cambios en la búsqueda
  void _onSearchChanged(String value) {
    setState(() {
      _searchQuery = value;
    });
    
    // Usar búsqueda local si hay productos cacheados
    if (_allProducts.isNotEmpty) {
      _performLocalSearch(value);
    } else {
      _applyFilters();
    }
  }

  // Ordenar productos
  void _sortProducts(List<Product> products) {
    products.sort((a, b) {
      int comparison = 0;
      
      switch (_sortBy) {
        case 'name':
          comparison = a.name.compareTo(b.name);
          break;
        case 'stock':
          comparison = a.stock.compareTo(b.stock);
          break;
        case 'expiryDate':
          if (a.expiryDate == null && b.expiryDate == null) {
            comparison = 0;
          } else if (a.expiryDate == null) {
            comparison = 1;
          } else if (b.expiryDate == null) {
            comparison = -1;
          } else {
            comparison = a.expiryDate!.compareTo(b.expiryDate!);
          }
          break;
        case 'price':
          comparison = a.price.compareTo(b.price);
          break;
      }
      
      return _sortAscending ? comparison : -comparison;
    });
  }

  // Datos de ejemplo (fallback)
  List<Product> _getSampleProducts() {
    return [
      Product(
        id: '1',
        name: 'Paracetamol 500mg',
        brand: 'Genfar',
        stock: 5,
        minStock: 8,
        expiryDate: DateTime(2025, 06, 25),
        price: 15.75,
        costPrice: 10.20,
        barcode: '7501234567893',
        category: 'Gastroenterología',
        createdAt: DateTime.now().subtract(const Duration(days: 15)),
        updatedAt: DateTime.now(),
      ),
      Product(
        id: '2',
        name: 'Ibuprofeno 400mg',
        brand: 'Bayer',
        stock: 0,
        minStock: 8,
        expiryDate: DateTime(2025, 09, 25),
        price: 15.75,
        costPrice: 10.20,
        barcode: '7501234567893',
        category: 'Gastroenterología',
        createdAt: DateTime.now().subtract(const Duration(days: 15)),
        updatedAt: DateTime.now(),
      ),
      Product(
        id: '3',
        name: 'Loratadina 10mg',
        brand: 'AllergyFree',
        stock: 5,
        minStock: 8,
        expiryDate: DateTime(2026, 10, 15),
        price: 15.75,
        costPrice: 10.20,
        barcode: '7501234567893',
        category: 'Gastroenterología',
        createdAt: DateTime.now().subtract(const Duration(days: 15)),
        updatedAt: DateTime.now(),
      ),
      Product(
        id: '4',
        name: 'Omeprazol 20mg',
        brand: 'GenericLab',
        stock: 5,
        minStock: 8,
        expiryDate: DateTime(2026, 10, 15),
        price: 15.75,
        costPrice: 10.20,
        barcode: '7501234567893',
        category: 'Gastroenterología',
        createdAt: DateTime.now().subtract(const Duration(days: 15)),
        updatedAt: DateTime.now(),
      ),
      Product(
        id: '5',
        name: 'Loratadina 10mg',
        brand: 'AllergyFree',
        stock: 65,
        minStock: 15,
        expiryDate: DateTime(2025, 11, 30),
        price: 6.25,
        costPrice: 4.10,
        barcode: '7501234567894',
        category: 'Antihistamínicos',
        createdAt: DateTime.now().subtract(const Duration(days: 10)),
        updatedAt: DateTime.now(),
      ),
      Product(
        id: '6',
        name: 'Acetaminofén Jarabe',
        brand: 'PharmaKids',
        stock: 8,
        minStock: 12,
        expiryDate: DateTime(2025, 6, 10),
        price: 18.50,
        costPrice: 12.30,
        barcode: '7501234567895',
        category: 'Pediátricos',
        createdAt: DateTime.now().subtract(const Duration(days: 5)),
        updatedAt: DateTime.now(),
      ),
      Product(
        id: '7',
        name: 'Vitamina C 500mg',
        brand: 'VitaLab',
        stock: 30,
        minStock: 10,
        // Fecha de vencimiento en el pasado para probar
        expiryDate: DateTime(2024, 1, 1), // Fecha ya vencida
        price: 15.99,
        costPrice: 10.00,
        barcode: '7501234567896',
        category: 'Vitaminas',
        createdAt: DateTime.now().subtract(const Duration(days: 60)),
        updatedAt: DateTime.now(),
      ),
      // Agregar este producto a la lista _products
      Product(
        id: '8',
        name: 'Diclofenaco 50mg',
        brand: 'MediFarma',
        stock: 0,              // Stock en 0 = producto agotado
        minStock: 15,
        expiryDate: DateTime(2025, 12, 30),
        price: 12.99,
        costPrice: 8.50,
        barcode: '7501234567897',
        category: 'Antiinflamatorios',
        createdAt: DateTime.now().subtract(const Duration(days: 15)),
        updatedAt: DateTime.now(),
      ),
    ];
  }

  List<Product> get _filteredProducts {
    List<Product> filtered = List.from(_products);

    // Aplicar filtros localmente
    switch (_selectedFilter) {
      case 'Críticos':
        filtered = filtered.where((p) => p.isLowStock || p.isExpiringSoon || p.isExpired || p.isOutOfStock).toList();
        break;
      case 'Normal':
        filtered = filtered.where((p) => !p.isLowStock && !p.isExpiringSoon && !p.isExpired && !p.isOutOfStock).toList();
        break;
      case 'Stock Bajo':
        filtered = filtered.where((p) => p.isLowStock && !p.isExpired && !p.isOutOfStock).toList();
        break;
      case 'Por Vencer':
        filtered = filtered.where((p) => p.isExpiringSoon && !p.isExpired).toList();
        break;
      case 'Vencidos':
        filtered = filtered.where((p) => p.isExpired).toList();
        break;
      case 'Agotados':
        filtered = filtered.where((p) => p.isOutOfStock).toList();
        break;
      default:
        // No filtrar, mostrar todos
        break;
    }

    // Aplicar filtro de búsqueda
    if (_searchQuery.isNotEmpty) {
      filtered = filtered.where((product) {
        return product.name.toLowerCase().contains(_searchQuery.toLowerCase()) ||
               product.brand.toLowerCase().contains(_searchQuery.toLowerCase()) ||
               (product.barcode?.contains(_searchQuery) ?? false) ||
               (product.category?.toLowerCase().contains(_searchQuery.toLowerCase()) ?? false);
      }).toList();
    }

    // Ordenar productos
    filtered.sort((a, b) {
      int comparison;
      switch (_sortBy) {
        case 'name':
          comparison = a.name.compareTo(b.name);
          break;
        case 'stock':
          comparison = a.stock.compareTo(b.stock);
          break;
        case 'expiryDate':
          comparison = a.expiryDate.compareTo(b.expiryDate);
          break;
        default:
          comparison = a.name.compareTo(b.name);
      }
      return _sortAscending ? comparison : -comparison;
    });

    return filtered;
  }
  @override
  void dispose() {
    _searchController.dispose();
    _animationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey.shade50,
      appBar: const BarraCompartida(
        titulo: 'Inventario',
      ),
      body: FadeTransition(
        opacity: _fadeAnimation,
        child: _isLoading 
          ? _buildLoadingState()
          : _errorMessage != null
            ? _buildErrorState()
            : RefreshIndicator(
                onRefresh: _refreshProducts,
                color: AppTheme.primaryRed,
                child: CustomScrollView(
                  slivers: [
                    SliverToBoxAdapter(child: _buildHeader()),
                    SliverToBoxAdapter(child: _buildFilterChips()),
                    _buildProductList(),
                  ],
                ),
              ),
      ),
      floatingActionButton: _buildFloatingActionButton(),
    );
  }

  Widget _buildLoadingState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          CircularProgressIndicator(
            color: AppTheme.primaryRed,
            strokeWidth: 3,
          ),
          const SizedBox(height: 16),
          Text(
            'Cargando inventario...',
            style: TextStyle(
              fontSize: 16,
              color: Colors.grey.shade600,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Colors.red.withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(
              Icons.error_outline_rounded,
              size: 48,
              color: Colors.red.shade600,
            ),
          ),
          const SizedBox(height: 24),
          Text(
            'Error al cargar productos',
            style: TextStyle(
              fontSize: 20,
              color: Colors.red.shade600,
              fontWeight: FontWeight.w600,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Text(
            _errorMessage ?? 'Error desconocido',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey.shade600,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: _loadProducts,
            icon: const Icon(Icons.refresh_rounded),
            label: const Text('Reintentar'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryRed,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      color: AppTheme.primaryRed,
      child: Padding(
        padding: const EdgeInsets.all(20.0),
        child: Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(30),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.15),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
              BoxShadow(
                color: AppTheme.primaryRed.withOpacity(0.1),
                blurRadius: 40,
                offset: const Offset(0, 16),
              ),
            ],
          ),
          child: TextField(
            controller: _searchController,
            onChanged: _onSearchChanged,
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
            decoration: InputDecoration(
              hintText: 'Buscar medicamentos...',
              hintStyle: TextStyle(
                color: Colors.grey.shade400,
                fontSize: 16,
              ),
              prefixIcon: Container(
                padding: const EdgeInsets.all(12),
                child: Icon(
                  Icons.search_rounded,
                  color: AppTheme.primaryRed,
                  size: 24,
                ),
              ),
              suffixIcon: _searchQuery.isNotEmpty
                  ? IconButton(
                      icon: Icon(
                        Icons.clear_rounded,
                        color: Colors.grey.shade400,
                        size: 20,
                      ),
                      onPressed: () {
                        _searchController.clear();
                        _onSearchChanged('');
                      },
                    )
                  : null,
              border: InputBorder.none,
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 20,
                vertical: 18,
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildVerticalDivider() {
    return Container(
      height: 40,
      width: 1,
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.2),
        borderRadius: BorderRadius.circular(1),
      ),
    );
  }

  Widget _buildFilterChips() {
    // Calcular los contadores basándose en TODOS los productos disponibles
    // Filtrar productos críticos de _allProducts para el contador de "Todos"
    final criticalProducts = _allProducts.where((product) => 
      product.isLowStock || 
      product.isExpiringSoon || 
      product.isExpired || 
      product.isOutOfStock
    ).toList();
    
    final totalProducts = criticalProducts.length;
    final normalProducts = _allProducts.where((product) => 
      !product.isLowStock && 
      !product.isExpiringSoon && 
      !product.isExpired && 
      !product.isOutOfStock
    ).length;
    final lowStockCount = _allProducts.where((p) => p.isLowStock && !p.isExpired && !p.isOutOfStock).length;
    final expiringCount = _allProducts.where((p) => p.isExpiringSoon && !p.isExpired).length;
    final expiredCount = _allProducts.where((p) => p.isExpired).length;
    final outOfStockCount = _allProducts.where((p) => p.isOutOfStock).length;

    final filters = [
      {
        'name': 'Críticos',
        'color': const Color(0xFFFF5722),
        'icon': Icons.warning_rounded,
        'count': totalProducts
      },
      {
        'name': 'Stock Bajo',
        'color': const Color(0xFFFFA000),
        'icon': Icons.trending_down_rounded,
        'count': lowStockCount
      },
      {
        'name': 'Por Vencer',
        'color': const Color(0xFFFF4757),
        'icon': Icons.schedule_rounded,
        'count': expiringCount
      },
      {
        'name': 'Vencidos',
        'color': const Color(0xFFD32F2F),
        'icon': Icons.error_outline_rounded,
        'count': expiredCount
      },
      {
        'name': 'Agotados',
        'color': const Color(0xFF757575),
        'icon': Icons.inventory_2_outlined,
        'count': outOfStockCount
      },
      {
        'name': 'Normal',
        'color': const Color(0xFF4CAF50),
        'icon': Icons.check_circle_rounded,
        'count': normalProducts
      },
      {
        'name': 'Ordenar',
        'color': const Color(0xFF00B8D9),
        'icon': Icons.sort_rounded,
        'count': 0
      },
    ];
    

    // Contenedor de filtros 
    return Container(
      height: 85,
      padding: const EdgeInsets.symmetric(vertical: 16),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 20),
        itemCount: filters.length,
        itemBuilder: (context, index) {
          final filter = filters[index];
          final isSelected = filter['name'] as String == _selectedFilter;
          final color = filter['color'] as Color;
          final icon = filter['icon'] as IconData;
          final count = filter['count'] as int;
          
          return Padding(
            padding: const EdgeInsets.only(right: 12),
            child: Stack(
              clipBehavior: Clip.none,
              children: [
                AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  child: Material(
                    color: Colors.transparent,
                    child: InkWell(
                      borderRadius: BorderRadius.circular(25),
                      onTap: () {
                        final filterName = filter['name'] as String;
                        setState(() => _selectedFilter = filterName);
                        if (filterName == 'Ordenar') {
                          _showSortDialog();
                        } else {
                          // Aplicar filtros localmente
                          _applyFilters();
                        }
                      },
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                        decoration: BoxDecoration(
                          color: isSelected ? color : Colors.white,
                          borderRadius: BorderRadius.circular(25),
                          border: Border.all(
                            color: isSelected ? color : Colors.grey.shade200,
                            width: 2,
                          ),
                          boxShadow: isSelected ? [
                            BoxShadow(
                              color: color.withOpacity(0.3),
                              blurRadius: 15,
                              offset: const Offset(0, 6),
                            ),
                          ] : [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.05),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              icon,
                              color: isSelected ? Colors.white : color,
                              size: 18,
                            ),
                            const SizedBox(width: 8),
                            Text(
                              filter['name'] as String,
                              style: TextStyle(
                                color: isSelected ? Colors.white : color,
                                fontWeight: FontWeight.w600,
                                fontSize: 14,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
                // Badge contador
                if (count > 0)
                  Positioned(
                    top: -5,
                    right: -8,
                    child: Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: isSelected ? Colors.white : color,
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: isSelected ? color : Colors.white,
                          width: 2,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: color.withOpacity(0.3),
                            blurRadius: 8,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Text(
                        count.toString(),
                        style: TextStyle(
                          color: isSelected ? color : Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                        ),
                      ),
                    ),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildProductList() {
    if (_filteredProducts.isEmpty) {
      return SliverToBoxAdapter(child: _buildEmptyState());
    }

    // Usando SliverList para mejor rendimiento
    return SliverPadding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
      sliver: SliverList(
        delegate: SliverChildBuilderDelegate(
          (context, index) {
            final product = _filteredProducts[index];
            return Padding(
              padding: const EdgeInsets.only(bottom: 16),
              child: _ProductCard(
                product: product,
                onTap: () => _showProductDetails(product),
              ),
            );
          },
          childCount: _filteredProducts.length,
        ),
      ),
    );
  }
  Widget _buildEmptyState() {
  IconData icon;
  String message;
  Color color;

  if (_searchQuery.isNotEmpty) {
    icon = Icons.search_off_rounded;
    message = 'No se encontraron productos';
    color = Colors.grey.shade600;
  } else {
    switch (_selectedFilter) {
      case 'Agotados':
        icon = Icons.inventory_2_outlined;
        message = 'No hay productos agotados';
        color = Colors.grey.shade600;
        break;
      case 'Vencidos':
        icon = Icons.error_outline_rounded;
        message = 'No hay productos vencidos';
        color = Colors.red.shade600;
        break;
      case 'Por Vencer':
        icon = Icons.schedule_rounded;
        message = 'No hay productos por vencer';
        color = Colors.amber.shade600;
        break;
      case 'Stock Bajo':
        icon = Icons.trending_down_rounded;
        message = 'No hay productos con stock bajo';
        color = Colors.orange.shade600;
        break;
      default:
        icon = Icons.inventory_2_outlined;
        message = 'No hay productos en el inventario';
        color = Colors.grey.shade600;
    }
  }

  return SizedBox(
    height: 400,
    child: Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(
              icon,
              size: 48,
              color: color,
            ),
          ),
          const SizedBox(height: 24),
          Text(
            message,
            style: TextStyle(
              fontSize: 20,
              color: color,
              fontWeight: FontWeight.w600,
            ),
            textAlign: TextAlign.center,
          ),
          if (_searchQuery.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(
              'Intenta con otro término de búsqueda',
              style: TextStyle(
                fontSize: 16,
                color: Colors.grey.shade400,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ],
      ),
    ),
  );
}

  Widget _buildFloatingActionButton() {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(30),
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppTheme.primaryRed,
            AppTheme.darkRed,
          ],
        ),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primaryRed.withOpacity(0.4),
            blurRadius: 25,
            offset: const Offset(0, 12),
          ),
          BoxShadow(
            color: AppTheme.primaryRed.withOpacity(0.2),
            blurRadius: 40,
            offset: const Offset(0, 20),
          ),
        ],
      ),
      child: FloatingActionButton.extended(
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => AddProductBarcodeScreen(
                onProductAdded: () {
                  // Refrescar la lista de productos después de agregar
                  _refreshProducts();
                },
              ),
            ),
          );
        },
        backgroundColor: Colors.transparent,
        elevation: 0,
        icon: Container(
          padding: const EdgeInsets.all(6),
          decoration: BoxDecoration(
            color: Colors.white.withOpacity(0.2),
            borderRadius: BorderRadius.circular(8),
          ),
          child: const Icon(Icons.qr_code_scanner_rounded, color: Colors.white, size: 20),
        ),
        label: const Padding(
          padding: EdgeInsets.symmetric(horizontal: 8),
          child: Text(
            'Agregar',
            style: TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.bold,
              fontSize: 16,
            ),
          ),
        ),
      ),
    );
  }




  void _showProductDetails(Product product) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => _ProductDetailsSheet(product: product),
    );
  }

  void _showSortDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            Icon(Icons.sort_rounded, color: AppTheme.primaryRed),
            const SizedBox(width: 8),
            const Text('Ordenar por'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildSortOption('Nombre', 'name', Icons.sort_by_alpha_rounded),
            _buildSortOption('Stock', 'stock', Icons.inventory_2_rounded),
            _buildSortOption('Fecha de vencimiento', 'expiryDate', Icons.calendar_today_rounded),
          ],
        ),
      ),
    );
  }

  Widget _buildSortOption(String label, String value, IconData icon) {
    final isSelected = _sortBy == value;
    return ListTile(
      leading: Icon(icon, color: isSelected ? AppTheme.primaryRed : Colors.grey.shade600),
      title: Text(
        label,
        style: TextStyle(
          color: isSelected ? AppTheme.primaryRed : Colors.grey.shade800,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
        ),
      ),
      trailing: isSelected
        ? IconButton(
            icon: Icon(
              _sortAscending ? Icons.arrow_upward : Icons.arrow_downward,
              color: AppTheme.primaryRed,
            ),
            onPressed: () {
              setState(() => _sortAscending = !_sortAscending);
              _applyFilters();
              Navigator.pop(context);
            },
          )
        : null,
      onTap: () {
        setState(() => _sortBy = value);
        _applyFilters();
        Navigator.pop(context);
      },
    );
  }
}

class _StatCard extends StatelessWidget {
  final IconData icon;
  final String value;
  final String label;
  final Color color;
  final List<Color> gradient;

  const _StatCard({
    required this.icon,
    required this.value,
    required this.label,
    required this.color,
    required this.gradient,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 85,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: gradient,
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withOpacity(0.2)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              icon,
              size: 18,
              color: color,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              color: color,
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              color: color.withOpacity(0.8),
              fontSize: 11,
              fontWeight: FontWeight.w500,
            ),
            textAlign: TextAlign.center,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}

class _ProductCard extends StatelessWidget {
  final Product product;
  final VoidCallback onTap;

  const _ProductCard({
    required this.product,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border(
          left: BorderSide(
            color: product.isOutOfStock
                ? Colors.grey
                : product.isExpired
                    ? Colors.red
                    : product.isLowStock
                        ? Colors.orange
                        : product.isExpiringSoon
                            ? Colors.amber
                            : Colors.green,
            width: 4,
          ),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(20),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header con estado
                Row(
                  children: [
                    Expanded(
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: product.statusColor.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(
                                color: product.statusColor.withOpacity(0.3),
                                width: 1,
                              ),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Container(
                                  width: 6,
                                  height: 6,
                                  decoration: BoxDecoration(
                                    color: product.statusColor,
                                    shape: BoxShape.circle,
                                  ),
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  product.isOutOfStock ? "Agotado" :
                                  product.isExpired ? "Vencido" :
                                  product.isExpiringSoon ? "Por Vencer" :
                                  product.isLowStock ? "Stock Bajo" : 
                                  "Normal",
                                  style: TextStyle(
                                    color: product.statusColor,
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          if (!product.isExpired && product.isExpiringSoon && product.isLowStock) ...[
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              decoration: BoxDecoration(
                                color: Colors.orange.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: Colors.orange.withOpacity(0.3),
                                  width: 1,
                                ),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Container(
                                    width: 6,
                                    height: 6,
                                    decoration: BoxDecoration(
                                      color: Colors.orange,
                                      shape: BoxShape.circle,
                                    ),
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    "Stock Bajo",
                                    style: TextStyle(
                                      color: Colors.orange.shade700,
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                // Información principal del producto
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 60,
                      height: 60,
                      decoration: BoxDecoration(
                        color: AppTheme.primaryRed.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: AppTheme.primaryRed.withOpacity(0.2),
                          width: 1,
                        ),
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(15),
                        child: product.imageUrl != null
                            ? Image.network(
                                product.imageUrl!,
                                fit: BoxFit.cover,
                                errorBuilder: (context, error, stackTrace) {
                                  return Icon(
                                    Icons.medication_rounded,
                                    size: 40,
                                    color: AppTheme.primaryRed,
                                  );
                                },
                              )
                            : Icon(
                                Icons.medication_rounded,
                                size: 40,
                                color: AppTheme.primaryRed,
                              ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            product.name,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Colors.black87,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 3),
                          Text(
                            product.brand,
                            style: TextStyle(
                              fontSize: 13,
                              color: Colors.grey.shade600,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 6),
                          if (product.category != null)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.blue.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                product.category!,
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.blue.shade700,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                // Stock y fecha de vencimiento
                Row(
                  children: [
                    // Stock
                    Icon(
                      Icons.inventory_2_outlined,
                      size: 16,
                      color: Colors.grey.shade600,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Stock: ',
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade600,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    Text(
                      '${product.stock}',
                      style: TextStyle(
                        fontSize: 14,
                        color: product.stockProgressColor,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(width: 16),
                    // Fecha de vencimiento
                    Icon(
                      Icons.calendar_today_outlined,
                      size: 16,
                      color: Colors.grey.shade600,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      product.isExpired
                          ? 'Vencido hace '
                          : 'Vence en ',
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade600,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    Text(
                      product.isExpired
                          ? '${DateTime.now().difference(product.expiryDate).inDays} días'
                          : '${product.daysUntilExpiry} días',
                      style: TextStyle(
                        fontSize: 14,
                        color: product.isExpired
                            ? Colors.red.shade600
                            : product.isExpiringSoon
                                ? Colors.orange.shade600
                                : Colors.grey.shade700,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showEditDialog(BuildContext context, Product product) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Icon(Icons.edit_outlined, color: Colors.blue.shade600),
            const SizedBox(width: 8),
            const Text('Editar Producto'),
          ],
        ),
        content: Text('Editando: ${product.name}'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.blue.shade600),
            child: const Text('Editar', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _showDeleteDialog(BuildContext context, Product product) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Icon(Icons.warning_rounded, color: Colors.red.shade600),
            const SizedBox(width: 8),
            const Text('Eliminar Producto'),
          ],
        ),
        content: Text('¿Estás seguro de eliminar "${product.name}"?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red.shade600),
            child: const Text('Eliminar', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _showDuplicateDialog(BuildContext context, Product product) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Icon(Icons.copy_outlined, color: Colors.green.shade600),
            const SizedBox(width: 8),
            const Text('Duplicar Producto'),
          ],
        ),
        content: Text('¿Duplicar "${product.name}"?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.green.shade600),
            child: const Text('Duplicar', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }
}

class _InfoItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final Color color;

  const _InfoItem({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, size: 18, color: color),
        const SizedBox(height: 4),
        Text(
          label,
          style: TextStyle(
            fontSize: 12,
            color: Colors.grey.shade600,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
      ],
    );
  }
}

class _ProductDetailsSheet extends StatelessWidget {
  final Product product;

  const _ProductDetailsSheet({required this.product});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: MediaQuery.of(context).size.height * 0.85,
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(30),
          topRight: Radius.circular(30),
        ),
      ),
      child: Column(
        children: [
          // Handle bar
          Container(
            margin: const EdgeInsets.only(top: 12),
            width: 50,
            height: 5,
            decoration: BoxDecoration(
              color: Colors.grey.shade300,
              borderRadius: BorderRadius.circular(3),
            ),
          ),
          
          // Header
          Padding(
            padding: const EdgeInsets.all(24),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Detalles del Producto',
                        style: TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.darkGray,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        product.name,
                        style: TextStyle(
                          fontSize: 16,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: product.statusColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    Icons.medication_rounded,
                    color: product.statusColor,
                    size: 24,
                  ),
                ),
              ],
            ),
          ),
          
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Imagen del producto
                  Center(
                    child: Container(
                      width: 120,
                      height: 120,
                      decoration: BoxDecoration(
                        color: AppTheme.primaryRed.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                          color: AppTheme.primaryRed.withOpacity(0.2),
                          width: 2,
                        ),
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(18),
                        child: product.imageUrl != null
                            ? Image.network(
                                product.imageUrl!,
                                fit: BoxFit.cover,
                                errorBuilder: (context, error, stackTrace) {
                                  return Icon(
                                    Icons.medication_rounded,
                                    size: 60,
                                    color: AppTheme.primaryRed,
                                  );
                                },
                              )
                            : Icon(
                                Icons.medication_rounded,
                                size: 60,
                                color: AppTheme.primaryRed,
                              ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 32),
                  
                  // Información principal
                  _DetailSection(
                    title: 'Información General',
                    items: [
                      _DetailItem(label: 'Nombre', value: product.name),
                      if (product.concentration != null && product.concentration!.isNotEmpty)
                        _DetailItem(label: 'Concentración', value: product.concentration!),
                      _DetailItem(label: 'Marca', value: product.brand),
                      if (product.laboratory != null && product.laboratory != product.brand)
                        _DetailItem(label: 'Laboratorio', value: product.laboratory!),
                      if (product.category != null)
                        _DetailItem(label: 'Categoría', value: product.category!),
                      if (product.presentation != null && product.presentation!.isNotEmpty)
                        _DetailItem(label: 'Presentación', value: product.presentation!),
                      _DetailItem(
                        label: 'Ubicación', 
                        value: product.location ?? 'Sin ubicar',
                        valueColor: product.location != null ? Colors.blue.shade600 : Colors.grey.shade500,
                      ),
                      if (product.batchNumber != null)
                        _DetailItem(label: 'Lote', value: product.batchNumber!),
                    ],
                  ),
                  
                  // Información de inventario
                  _DetailSection(
                    title: 'Inventario',
                    items: [
                      _DetailItem(
                        label: 'Stock Actual',
                        value: '${product.stock} unidades',
                        valueColor: product.stockProgressColor,
                      ),
                      _DetailItem(
                        label: 'Stock Mínimo',
                        value: '${product.minStock} unidades',
                      ),
                      _DetailItem(
                        label: 'Estado',
                        value: product.statusText,
                        valueColor: product.statusColor,
                      ),
                    ],
                  ),
                  
                  // Información de precios
                  _DetailSection(
                    title: 'Precios',
                    items: [
                      _DetailItem(
                        label: 'Precio de Venta',
                        value: 'S/. ${product.price.toStringAsFixed(2)}',
                        valueColor: Colors.green.shade600,
                      ),
                      if (product.costPrice != null)
                        _DetailItem(
                          label: 'Costo',
                          value: 'S/. ${product.costPrice!.toStringAsFixed(2)}',
                        ),
                    ],
                  ),
                  
                  // Información de fechas
                  _DetailSection(
                    title: 'Fechas',
                    items: [
                      _DetailItem(
                        label: 'Fecha de Vencimiento',
                        value: '${product.expiryDate.day}/${product.expiryDate.month}/${product.expiryDate.year}',
                        valueColor: product.isExpiringSoon || product.isExpired 
                            ? Colors.orange.shade600 
                            : null,
                      ),
                      _DetailItem(
                        label: 'Días hasta vencer',
                        value: '${product.daysUntilExpiry} días',
                        valueColor: product.isExpiringSoon || product.isExpired 
                            ? Colors.orange.shade600 
                            : Colors.green.shade600,
                      ),
                      _DetailItem(
                        label: 'Creado',
                        value: '${product.createdAt.day}/${product.createdAt.month}/${product.createdAt.year}',
                      ),
                      _DetailItem(
                        label: 'Última actualización',
                        value: '${product.updatedAt.day}/${product.updatedAt.month}/${product.updatedAt.year}',
                      ),
                    ],
                  ),
                  
                  // Código de barras
                  if (product.barcode != null)
                    _DetailSection(
                      title: 'Código de Barras',
                      items: [
                        _DetailItem(
                          label: 'Código',
                          value: product.barcode!,
                          isBarcode: true,
                        ),
                      ],
                    ),
                  
                  const SizedBox(height: 32),
                ],
              ),
            ),
          ),
          
          // Botones de acción
          // En la clase _ProductDetailsSheet, modifica los botones de acción así:
          Container(
  padding: const EdgeInsets.all(24),
  decoration: BoxDecoration(
    color: Colors.grey.shade50,
    border: Border(top: BorderSide(color: Colors.grey.shade200)),
  ),
  child: Row(
    children: [
      Expanded(
        child: OutlinedButton.icon(
          onPressed: () {
            Navigator.pop(context);
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => EditProductScreen(product: product),
              ),
            );
          },
          icon: const Icon(Icons.edit_outlined),
          label: const Text('Editar'),
          style: OutlinedButton.styleFrom(
            foregroundColor: Colors.blue.shade600,
            side: BorderSide(color: Colors.blue.shade600),
            padding: const EdgeInsets.symmetric(vertical: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        ),
      ),
      const SizedBox(width: 16),
      Expanded(
        child: ElevatedButton.icon(
          onPressed: () {
            Navigator.pop(context);
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => AdjustStockScreen(product: product),
              ),
            );
          },
          icon: const Icon(Icons.inventory_rounded),
          label: const Text('Ajustar Stock'),
          style: ElevatedButton.styleFrom(
            backgroundColor: AppTheme.primaryRed,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        ),
      ),
    ],
  ),
),
        
        ],
      ),
    );
  }
}

class _DetailSection extends StatelessWidget {
  final String title;
  final List<_DetailItem> items;

  const _DetailSection({
    required this.title,
    required this.items,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: AppTheme.darkGray,
          ),
        ),
        const SizedBox(height: 16),
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: Colors.grey.shade50,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Column(
            children: items.map((item) => Padding(
              padding: EdgeInsets.only(
                bottom: item == items.last ? 0 : 16,
              ),
              child: item,
            )).toList(),
          ),
        ),
        const SizedBox(height: 24),
      ],
    );
  }
}

class _DetailItem extends StatelessWidget {
  final String label;
  final String value;
  final Color? valueColor;
  final bool isBarcode;

  const _DetailItem({
    required this.label,
    required this.value,
    this.valueColor,
    this.isBarcode = false,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: isBarcode
          ? Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(
                horizontal: 16,
                vertical: 12,
              ),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.grey.shade300),
              ),
              child: Text(
                value,
                style: const TextStyle(
                  fontSize: 16,
                  fontFamily: 'monospace',
                  fontWeight: FontWeight.w600,
                  letterSpacing: 1.2,
                ),
                textAlign: TextAlign.center,
              ),
            )
          : Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(
                  width: 120,
                  child: Text(
                    label,
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey.shade600,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
                Expanded(
                  child: Text(
                    value,
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: valueColor ?? Colors.grey.shade800,
                    ),
                  ),
                ),
              ],
            ),
    );
  }
}
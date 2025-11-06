const express = require('express');
const router = express.Router();
const productController = require('../controllers/Product');
const { authenticateToken } = require('../middlewares/auth');

router.post('/', authenticateToken, productController.createProduct);
router.get('/barcode/:barcode', authenticateToken, productController.findByBarcode);
router.put('/:id', authenticateToken, productController.updateProduct);

module.exports = router;
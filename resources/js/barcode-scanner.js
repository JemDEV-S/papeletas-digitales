/**
 * Barcode Scanner Helper for Permission Tracking
 * 
 * This module handles barcode scanning functionality for DNI tracking
 * Supports USB barcode scanners that emit keyboard input
 */

class BarcodeScanner {
    constructor(options = {}) {
        this.inputElement = options.inputElement || document.getElementById('dni-scanner');
        this.onScan = options.onScan || this.defaultScanHandler;
        this.scanTimeout = options.scanTimeout || 100; // ms between characters
        this.minLength = options.minLength || 8; // DNI length
        this.maxLength = options.maxLength || 8;
        
        this.scanBuffer = '';
        this.scanTimer = null;
        this.isScanning = false;
        
        this.init();
    }
    
    init() {
        if (!this.inputElement) {
            console.error('BarcodeScanner: Input element not found');
            return;
        }
        
        // Set up event listeners
        this.inputElement.addEventListener('input', this.handleInput.bind(this));
        this.inputElement.addEventListener('keydown', this.handleKeyDown.bind(this));
        this.inputElement.addEventListener('paste', this.handlePaste.bind(this));
        
        // Focus the input element
        this.inputElement.focus();
        
        console.log('BarcodeScanner: Initialized successfully');
    }
    
    handleInput(event) {
        const value = event.target.value;
        
        // Clear previous timer
        if (this.scanTimer) {
            clearTimeout(this.scanTimer);
        }
        
        // Only process if value looks like a DNI (8 digits)
        if (this.isDNIFormat(value)) {
            this.scanTimer = setTimeout(() => {
                this.processScan(value);
            }, this.scanTimeout);
        }
    }
    
    handleKeyDown(event) {
        // Handle Enter key for manual scanning
        if (event.key === 'Enter') {
            event.preventDefault();
            const value = event.target.value.trim();
            if (this.isDNIFormat(value)) {
                this.processScan(value);
            } else {
                this.showError('Por favor ingrese un DNI válido de 8 dígitos');
            }
        }
        
        // Handle Escape to clear
        if (event.key === 'Escape') {
            this.clearInput();
        }
    }
    
    handlePaste(event) {
        setTimeout(() => {
            const value = event.target.value.trim();
            if (this.isDNIFormat(value)) {
                this.processScan(value);
            }
        }, 10);
    }
    
    isDNIFormat(value) {
        return /^\d{8}$/.test(value);
    }
    
    processScan(dni) {
        if (this.isScanning) return;
        
        this.isScanning = true;
        console.log('BarcodeScanner: Processing DNI scan:', dni);
        
        // Call the scan handler
        this.onScan(dni).finally(() => {
            this.isScanning = false;
        });
    }
    
    defaultScanHandler(dni) {
        console.log('BarcodeScanner: Default handler - DNI scanned:', dni);
        return Promise.resolve();
    }
    
    clearInput() {
        if (this.inputElement) {
            this.inputElement.value = '';
            this.inputElement.focus();
        }
    }
    
    showError(message) {
        // Create a simple error display
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-error bg-red-100 text-red-800 border border-red-200 px-4 py-2 rounded-lg mb-4';
        errorDiv.textContent = message;
        
        // Remove any existing errors
        const existingError = document.querySelector('.alert-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Insert before the input element
        this.inputElement.parentNode.insertBefore(errorDiv, this.inputElement);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 3000);
    }
    
    // Public methods
    enable() {
        if (this.inputElement) {
            this.inputElement.disabled = false;
            this.inputElement.focus();
        }
    }
    
    disable() {
        if (this.inputElement) {
            this.inputElement.disabled = true;
        }
    }
    
    setFocus() {
        if (this.inputElement) {
            this.inputElement.focus();
        }
    }
}

/**
 * Advanced barcode scanner for handling multiple input methods
 */
class AdvancedBarcodeScanner extends BarcodeScanner {
    constructor(options = {}) {
        super(options);
        
        this.keyBuffer = '';
        this.keyTimer = null;
        this.keyThreshold = options.keyThreshold || 50; // ms between keystrokes for scanner detection
        this.preventSubmit = options.preventSubmit !== false;
        
        this.setupAdvancedListeners();
    }
    
    setupAdvancedListeners() {
        // Listen for rapid keystrokes (indicates barcode scanner)
        document.addEventListener('keydown', this.handleGlobalKeyDown.bind(this));
        document.addEventListener('keyup', this.handleGlobalKeyUp.bind(this));
    }
    
    handleGlobalKeyDown(event) {
        // Only process if input is focused or no specific element is focused
        if (document.activeElement === this.inputElement || document.activeElement === document.body) {
            const char = event.key;
            
            // Build key buffer
            if (char.length === 1 && /[0-9]/.test(char)) {
                this.keyBuffer += char;
                
                // Clear previous timer
                if (this.keyTimer) {
                    clearTimeout(this.keyTimer);
                }
                
                // Set new timer
                this.keyTimer = setTimeout(() => {
                    this.processKeyBuffer();
                }, this.keyThreshold);
            }
            
            // Handle Enter (scanner usually sends this at the end)
            if (char === 'Enter' && this.keyBuffer.length >= this.minLength) {
                if (this.preventSubmit) {
                    event.preventDefault();
                }
                this.processKeyBuffer();
            }
        }
    }
    
    handleGlobalKeyUp(event) {
        // Additional processing if needed
    }
    
    processKeyBuffer() {
        if (this.keyBuffer.length >= this.minLength && this.isDNIFormat(this.keyBuffer)) {
            // Update input field
            if (this.inputElement) {
                this.inputElement.value = this.keyBuffer;
                this.inputElement.focus();
            }
            
            // Process the scan
            this.processScan(this.keyBuffer);
        }
        
        // Clear buffer
        this.keyBuffer = '';
    }
}

/**
 * Factory function to create the appropriate scanner
 */
function createBarcodeScanner(options = {}) {
    const advanced = options.advanced !== false;
    
    if (advanced) {
        return new AdvancedBarcodeScanner(options);
    } else {
        return new BarcodeScanner(options);
    }
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { BarcodeScanner, AdvancedBarcodeScanner, createBarcodeScanner };
}

// Global export for browser
if (typeof window !== 'undefined') {
    window.BarcodeScanner = BarcodeScanner;
    window.AdvancedBarcodeScanner = AdvancedBarcodeScanner;
    window.createBarcodeScanner = createBarcodeScanner;
}

/**
 * Configuration Guide:
 * 
 * 1. Basic USB Barcode Scanner Setup:
 *    - Connect USB barcode scanner to computer
 *    - Scanner should be configured to emit keyboard input
 *    - Test by scanning into a text editor - should type the numbers
 * 
 * 2. Scanner Configuration (if programmable):
 *    - Set prefix: None or custom identifier
 *    - Set suffix: Enter key (CR) or custom
 *    - Set data format: Code format appropriate for DNI barcodes
 * 
 * 3. Web Application Setup:
 *    - Include this script in your page
 *    - Initialize with: createBarcodeScanner({ inputElement: yourInputElement })
 *    - Provide onScan callback for processing scanned data
 * 
 * 4. DNI Barcode Format:
 *    - Peruvian DNI cards have Code 39 or Code 128 barcodes
 *    - Contains 8-digit DNI number
 *    - May include additional data - extract first 8 digits
 * 
 * 5. Testing:
 *    - Test with physical DNI cards
 *    - Test with printed barcodes
 *    - Test manual input for fallback
 */
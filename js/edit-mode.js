let isEditMode = false;
let draggedElement = null;
let currentEditElement = null;
let originalLayout = null;
let isTextEditing = false;

// Initialize edit mode functionality
document.addEventListener('DOMContentLoaded', function() {
    saveOriginalLayout();
    initializeDragAndDrop();
    initializeEditPopup();
    
    // Initialize nested editable elements
    document.querySelectorAll('.editable-element').forEach(element => {
        addNestedEditHandles(element);
    });
});

function toggleEditMode() {
    isEditMode = !isEditMode;
    const editBtn = document.querySelector('.edit-mode-btn');
    const editControls = document.getElementById('editControls');
    const body = document.body;
    
    if (isEditMode) {
        // Enter edit mode
        editBtn.classList.add('active');
        editBtn.querySelector('.edit-text').textContent = 'Exit Edit';
        editControls.style.display = 'block';
        body.classList.add('edit-mode');
        showEditHandles();
        initializeTextEditing();
    } else {
        // Exit edit mode
        editBtn.classList.remove('active');
        editBtn.querySelector('.edit-text').textContent = 'Edit';
        editControls.style.display = 'none';
        body.classList.remove('edit-mode');
        hideEditHandles();
        closeEditPopup();
        disableTextEditing();
    }
}

function initializeEditPopup() {
    // Create edit popup if it doesn't exist
    if (!document.getElementById('editPopup')) {
        const popup = document.createElement('div');
        popup.id = 'editPopup';
        popup.className = 'edit-popup';
        popup.style.display = 'none';
        popup.innerHTML = `
            <div class="edit-popup-content">
                <div class="edit-popup-header">
                    <h3 class="edit-popup-title">✏️ Edit Element</h3>
                    <button class="edit-popup-close" onclick="closeEditPopup()">✕</button>
                </div>
                
                <div class="edit-section">
                    <div class="edit-section-title">
                        <span class="edit-section-icon">🎨</span>
                        Colors & Appearance
                    </div>
                    <div class="color-grid">
                        <div class="color-control">
                            <label>Background</label>
                            <input type="color" id="editBgColor" value="#ffffff">
                        </div>
                        <div class="color-control">
                            <label>Border</label>
                            <input type="color" id="editBorderColor" value="#3b82f6">
                        </div>
                        <div class="color-control">
                            <label>Text</label>
                            <input type="color" id="editTextColor" value="#1e293b">
                        </div>
                    </div>
                </div>

                <div class="edit-section">
                    <div class="edit-section-title">
                        <span class="edit-section-icon">📐</span>
                        Spacing & Position
                    </div>
                    <div class="spacing-controls">
                        <div class="range-control">
                            <label>Padding</label>
                            <input type="range" id="editPadding" min="0" max="50" value="25" class="range-input">
                            <div class="range-value" id="paddingValue">25px</div>
                        </div>
                        <div class="range-control">
                            <label>Margin</label>
                            <input type="range" id="editMargin" min="0" max="50" value="10" class="range-input">
                            <div class="range-value" id="marginValue">10px</div>
                        </div>
                        <div class="range-control">
                            <label>Border Radius</label>
                            <input type="range" id="editBorderRadius" min="0" max="30" value="12" class="range-input">
                            <div class="range-value" id="borderRadiusValue">12px</div>
                        </div>
                        <div class="range-control">
                            <label>Border Width</label>
                            <input type="range" id="editBorderWidth" min="0" max="10" value="2" class="range-input">
                            <div class="range-value" id="borderWidthValue">2px</div>
                        </div>
                    </div>
                </div>

                <div class="edit-section">
                    <div class="edit-section-title">
                        <span class="edit-section-icon">📝</span>
                        Text Content
                    </div>
                    <div class="edit-control-group full-width">
                        <div class="edit-control">
                            <label>Title Text</label>
                            <input type="text" id="editTitleText" placeholder="Enter title text">
                        </div>
                        <div class="edit-control">
                            <label>Value Text</label>
                            <input type="text" id="editValueText" placeholder="Enter value text">
                        </div>
                        <div class="edit-control">
                            <label>Description</label>
                            <textarea id="editDescText" placeholder="Enter description"></textarea>
                        </div>
                    </div>
                </div>

                <div class="edit-section">
                    <div class="edit-section-title">
                        <span class="edit-section-icon">🔧</span>
                        Advanced Options
                    </div>
                    <div class="edit-control-group">
                        <div class="edit-control">
                            <label>Width (%)</label>
                            <input type="range" id="editWidth" min="50" max="100" value="100" class="range-input">
                            <div class="range-value" id="widthValue">100%</div>
                        </div>
                        <div class="edit-control">
                            <label>Opacity</label>
                            <input type="range" id="editOpacity" min="10" max="100" value="100" class="range-input">
                            <div class="range-value" id="opacityValue">100%</div>
                        </div>
                        <div class="edit-control">
                            <label>Font Size</label>
                            <select id="editFontSize">
                                <option value="0.8rem">Small</option>
                                <option value="1rem" selected>Normal</option>
                                <option value="1.2rem">Large</option>
                                <option value="1.4rem">X-Large</option>
                            </select>
                        </div>
                        <div class="edit-control">
                            <label>Text Alignment</label>
                            <select id="editTextAlign">
                                <option value="left">Left</option>
                                <option value="center" selected>Center</option>
                                <option value="right">Right</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="edit-popup-actions">
                    <button class="popup-btn danger" onclick="removeCurrentElement()">🗑️ Delete</button>
                    <button class="popup-btn secondary" onclick="resetElementStyles()">🔄 Reset</button>
                    <button class="popup-btn secondary" onclick="closeEditPopup()">Cancel</button>
                    <button class="popup-btn primary" onclick="applyAdvancedStyles()">✅ Apply</button>
                </div>
            </div>
        `;
        document.body.appendChild(popup);
        
        // Add event listeners for range inputs
        setupRangeInputs();
    }
}

function setupRangeInputs() {
    const rangeInputs = [
        { input: 'editPadding', value: 'paddingValue', suffix: 'px' },
        { input: 'editMargin', value: 'marginValue', suffix: 'px' },
        { input: 'editBorderRadius', value: 'borderRadiusValue', suffix: 'px' },
        { input: 'editBorderWidth', value: 'borderWidthValue', suffix: 'px' },
        { input: 'editWidth', value: 'widthValue', suffix: '%' },
        { input: 'editOpacity', value: 'opacityValue', suffix: '%' }
    ];

    rangeInputs.forEach(({ input, value, suffix }) => {
        const inputElement = document.getElementById(input);
        const valueElement = document.getElementById(value);
        
        if (inputElement && valueElement) {
            inputElement.addEventListener('input', function() {
                valueElement.textContent = this.value + suffix;
                if (currentEditElement) {
                    applyLivePreview();
                }
            });
        }
    });
}

function openAdvancedEdit(button, targetElement = null) {
    if (!isEditMode) {
        alert('Please enable edit mode first');
        return;
    }
    
    // Allow editing nested elements or the main container
    if (targetElement) {
        currentEditElement = targetElement;
    } else {
        currentEditElement = button.closest('.editable-element');
    }
    
    if (!currentEditElement) {
        alert('Could not find element to edit');
        return;
    }
    
    // Mark element as selected
    document.querySelectorAll('.editable-element, .nested-editable').forEach(el => el.classList.remove('selected'));
    currentEditElement.classList.add('selected');
    
    // Update popup title based on element type
    const popupTitle = document.querySelector('.edit-popup-title');
    if (popupTitle) {
        if (currentEditElement.classList.contains('nested-editable')) {
            popupTitle.textContent = '✏️ Edit Nested Element';
        } else if (currentEditElement.classList.contains('stat-card')) {
            popupTitle.textContent = '✏️ Edit Stat Card';
        } else if (currentEditElement.classList.contains('chart-section')) {
            popupTitle.textContent = '✏️ Edit Chart Section';
        } else {
            popupTitle.textContent = '✏️ Edit Element';
        }
    }
    
    // Load current styles
    loadCurrentStyles();
    
    // Show popup with smart positioning
    const popup = document.getElementById('editPopup');
    popup.style.display = 'flex';
    
    // Position popup next to the element
    positionPopupNearElement(popup, currentEditElement);
    
    // Remove any existing click listeners to prevent auto-close
    popup.onclick = null;
    
    // Only close on Cancel or Apply button clicks - popup stays open otherwise
    popup.addEventListener('click', function(e) {
        e.stopPropagation();
    });
}

function positionPopupNearElement(popup, element) {
    const elementRect = element.getBoundingClientRect();
    const popupContent = popup.querySelector('.edit-popup-content');
    
    // Set initial position to measure popup dimensions
    popup.classList.add('compact-mode');
    popup.style.top = '0px';
    popup.style.left = '0px';
    
    // Force a reflow to get accurate measurements
    popup.offsetHeight;
    
    const popupRect = popupContent.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    const scrollX = window.pageXOffset;
    const scrollY = window.pageYOffset;
    
    let popupX, popupY;
    
    // Calculate horizontal position
    const preferredX = elementRect.right + 20; // 20px gap to the right
    const fallbackX = elementRect.left - popupRect.width - 20; // Left side fallback
    const centeredX = elementRect.left + (elementRect.width - popupRect.width) / 2; // Centered fallback
    
    if (preferredX + popupRect.width <= viewportWidth - 20) {
        // Fits on the right side
        popupX = preferredX + scrollX;
    } else if (fallbackX >= 20) {
        // Fits on the left side
        popupX = fallbackX + scrollX;
    } else {
        // Center horizontally with viewport constraints
        popupX = Math.max(20, Math.min(centeredX, viewportWidth - popupRect.width - 20)) + scrollX;
    }
    
    // Calculate vertical position
    const preferredY = elementRect.top; // Align with top of element
    const centeredY = elementRect.top + (elementRect.height - popupRect.height) / 2; // Center vertically with element
    const fallbackY = Math.max(20, Math.min(preferredY, viewportHeight - popupRect.height - 20)); // Viewport constrained
    
    if (preferredY + popupRect.height <= viewportHeight - 20 && preferredY >= 20) {
        // Fits at preferred position
        popupY = preferredY + scrollY;
    } else if (centeredY >= 20 && centeredY + popupRect.height <= viewportHeight - 20) {
        // Fits centered with element
        popupY = centeredY + scrollY;
    } else {
        // Use fallback position within viewport
        popupY = fallbackY + scrollY;
    }
    
    // Apply the calculated position
    popup.style.top = `${popupY}px`;
    popup.style.left = `${popupX}px`;
    
    // Add a subtle animation
    popupContent.style.transform = 'scale(0.9) translateY(-10px)';
    popupContent.style.opacity = '0';
    
    requestAnimationFrame(() => {
        popupContent.style.transition = 'all 0.2s ease';
        popupContent.style.transform = 'scale(1) translateY(0)';
        popupContent.style.opacity = '1';
    });
}

function loadCurrentStyles() {
    if (!currentEditElement) return;
    
    const computedStyle = window.getComputedStyle(currentEditElement);
    
    // Load colors
    document.getElementById('editBgColor').value = rgbToHex(computedStyle.backgroundColor);
    document.getElementById('editBorderColor').value = rgbToHex(computedStyle.borderLeftColor || computedStyle.borderColor);
    document.getElementById('editTextColor').value = rgbToHex(computedStyle.color);
    
    // Load spacing
    document.getElementById('editPadding').value = parseInt(computedStyle.padding) || 25;
    document.getElementById('editMargin').value = parseInt(computedStyle.margin) || 10;
    document.getElementById('editBorderRadius').value = parseInt(computedStyle.borderRadius) || 12;
    document.getElementById('editBorderWidth').value = parseInt(computedStyle.borderWidth) || 2;
    
    // Load advanced options
    document.getElementById('editWidth').value = parseInt(computedStyle.width) / currentEditElement.parentElement.offsetWidth * 100 || 100;
    document.getElementById('editOpacity').value = parseFloat(computedStyle.opacity) * 100 || 100;
    document.getElementById('editFontSize').value = computedStyle.fontSize || '1rem';
    document.getElementById('editTextAlign').value = computedStyle.textAlign || 'center';
    
    // Load text content - handle both main elements and nested elements
    let titleElement, valueElement, descElement;
    
    if (currentEditElement.classList.contains('nested-editable')) {
        // For nested elements, use the element itself as the text source
        titleElement = currentEditElement;
        valueElement = currentEditElement;
        descElement = currentEditElement;
    } else {
        // For main containers, look for child elements
        titleElement = currentEditElement.querySelector('.stat-label, .section-title, .env-label, .chart-title');
        valueElement = currentEditElement.querySelector('.stat-value, .env-value');
        descElement = currentEditElement.querySelector('.stat-change, .env-data p, .stat-subtitle');
    }
    
    // Clear previous values
    document.getElementById('editTitleText').value = '';
    document.getElementById('editValueText').value = '';
    document.getElementById('editDescText').value = '';
    
    // Set values based on what we found
    if (titleElement && titleElement === currentEditElement) {
        // Single element editing
        document.getElementById('editTitleText').value = titleElement.textContent;
    } else {
        // Multi-element editing
        if (titleElement) document.getElementById('editTitleText').value = titleElement.textContent;
        if (valueElement) document.getElementById('editValueText').value = valueElement.textContent;
        if (descElement) document.getElementById('editDescText').value = descElement.textContent;
    }
    
    // Update range value displays
    document.querySelectorAll('.range-input').forEach(input => {
        input.dispatchEvent(new Event('input'));
    });
}

function applyLivePreview() {
    if (!currentEditElement) return;
    
    const styles = {
        backgroundColor: document.getElementById('editBgColor').value,
        borderLeftColor: document.getElementById('editBorderColor').value,
        color: document.getElementById('editTextColor').value,
        padding: document.getElementById('editPadding').value + 'px',
        margin: document.getElementById('editMargin').value + 'px',
        borderRadius: document.getElementById('editBorderRadius').value + 'px',
        borderWidth: document.getElementById('editBorderWidth').value + 'px',
        width: document.getElementById('editWidth').value + '%',
        opacity: document.getElementById('editOpacity').value / 100,
        fontSize: document.getElementById('editFontSize').value,
        textAlign: document.getElementById('editTextAlign').value
    };
    
    Object.assign(currentEditElement.style, styles);
}

function applyAdvancedStyles() {
    if (!currentEditElement) {
        alert('No element selected');
        return;
    }
    
    // Apply styles
    applyLivePreview();
    
    // Apply text content
    const titleText = document.getElementById('editTitleText').value;
    const valueText = document.getElementById('editValueText').value;
    const descText = document.getElementById('editDescText').value;
    
    // Handle nested elements differently
    if (currentEditElement.classList.contains('nested-editable')) {
        // For nested elements, update the text content directly
        if (titleText) currentEditElement.textContent = titleText;
    } else {
        // For main containers, find and update child elements
        const titleElement = currentEditElement.querySelector('.stat-label, .section-title, .env-label, .chart-title');
        const valueElement = currentEditElement.querySelector('.stat-value, .env-value');
        const descElement = currentEditElement.querySelector('.stat-change, .env-data p, .stat-subtitle');
        
        if (titleElement && titleText) titleElement.textContent = titleText;
        if (valueElement && valueText) valueElement.textContent = valueText;
        if (descElement && descText) descElement.textContent = descText;
        
        // Apply text color to child elements for better visibility
        const childElements = currentEditElement.querySelectorAll('.stat-value, .section-title, .env-value, .chart-title');
        childElements.forEach(element => {
            element.style.color = document.getElementById('editTextColor').value;
        });
    }
    
    closeEditPopup();
    showNotification('✅ Element updated successfully!', 'success');
}

function resetElementStyles() {
    if (!currentEditElement || !confirm('Reset this element to default styles?')) return;
    
    // Clear all custom styles
    currentEditElement.style.cssText = '';
    
    // Reset form values to defaults
    document.getElementById('editBgColor').value = '#ffffff';
    document.getElementById('editBorderColor').value = '#3b82f6';
    document.getElementById('editTextColor').value = '#1e293b';
    document.getElementById('editPadding').value = 25;
    document.getElementById('editMargin').value = 10;
    document.getElementById('editBorderRadius').value = 12;
    document.getElementById('editBorderWidth').value = 2;
    document.getElementById('editWidth').value = 100;
    document.getElementById('editOpacity').value = 100;
    
    // Update displays
    document.querySelectorAll('.range-input').forEach(input => {
        input.dispatchEvent(new Event('input'));
    });
    
    showNotification('🔄 Element reset to default styles', 'info');
}

function removeCurrentElement() {
    if (!currentEditElement || !confirm('Are you sure you want to delete this element?')) return;
    
    currentEditElement.classList.add('removing');
    setTimeout(() => {
        currentEditElement.remove();
        closeEditPopup();
        showNotification('🗑️ Element deleted', 'warning');
    }, 300);
}

function closeEditPopup() {
    const popup = document.getElementById('editPopup');
    if (popup) {
        const popupContent = popup.querySelector('.edit-popup-content');
        
        // Animate out
        popupContent.style.transition = 'all 0.15s ease';
        popupContent.style.transform = 'scale(0.9) translateY(-10px)';
        popupContent.style.opacity = '0';
        
        setTimeout(() => {
            popup.style.display = 'none';
            popup.classList.remove('compact-mode');
            
            // Reset styles
            popupContent.style.transform = '';
            popupContent.style.opacity = '';
            popupContent.style.transition = '';
        }, 150);
    }
    
    // Remove selected state from all elements
    document.querySelectorAll('.editable-element, .nested-editable').forEach(el => el.classList.remove('selected'));
    currentEditElement = null;
}

function initializeTextEditing() {
    // Make all nested elements within editable containers clickable for editing
    document.querySelectorAll('.editable-element').forEach(element => {
        // Add edit handles to nested elements
        addNestedEditHandles(element);
        
        element.addEventListener('dblclick', function(e) {
            if (!isEditMode || isTextEditing) return;
            
            e.stopPropagation();
            enableTextEditing(this);
        });
    });
}

function addNestedEditHandles(parentElement) {
    // Find all potential nested editable elements
    const nestedElements = parentElement.querySelectorAll(
        '.stat-header, .stat-value, .stat-change, .stat-subtitle, ' +
        '.chart-header, .chart-title, .chart-controls, ' +
        '.section-title, .env-label, .env-value, .env-data, ' +
        '.battery-bar, .sources-item'
    );
    
    nestedElements.forEach(nestedEl => {
        if (!nestedEl.classList.contains('nested-editable')) {
            nestedEl.classList.add('nested-editable');
            nestedEl.style.position = 'relative';
            
            // Add a small edit button for nested elements
            const editButton = document.createElement('button');
            editButton.className = 'nested-edit-btn';
            editButton.innerHTML = '✏️';
            editButton.style.cssText = `
                position: absolute;
                top: -5px;
                right: -5px;
                width: 20px;
                height: 20px;
                background: #3b82f6;
                color: white;
                border: none;
                border-radius: 50%;
                font-size: 10px;
                cursor: pointer;
                display: none;
                z-index: 1000;
                transition: all 0.2s ease;
            `;
            
            editButton.addEventListener('click', function(e) {
                e.stopPropagation();
                openAdvancedEdit(this, nestedEl);
            });
            
            nestedEl.appendChild(editButton);
            
            // Show edit button on hover in edit mode
            nestedEl.addEventListener('mouseenter', function() {
                if (isEditMode) {
                    editButton.style.display = 'block';
                }
            });
            
            nestedEl.addEventListener('mouseleave', function() {
                editButton.style.display = 'none';
            });
        }
    });
}

function enableTextEditing(element) {
    const textElements = element.querySelectorAll('.stat-label, .stat-value, .section-title, .env-label, .env-value');
    
    textElements.forEach(textEl => {
        textEl.addEventListener('click', function(e) {
            e.stopPropagation();
            editText(this);
        });
        textEl.style.cursor = 'text';
        textEl.style.border = '1px dashed #3b82f6';
        textEl.style.padding = '2px';
    });
}

function disableTextEditing() {
    document.querySelectorAll('.stat-label, .stat-value, .section-title, .env-label, .env-value').forEach(textEl => {
        textEl.style.cursor = '';
        textEl.style.border = '';
        textEl.style.padding = '';
    });
}

function editText(textElement) {
    if (isTextEditing) return;
    
    isTextEditing = true;
    const originalText = textElement.textContent;
    const input = document.createElement('input');
    
    input.value = originalText;
    input.className = 'text-edit-input';
    input.style.cssText = `
        background: white;
        border: 2px solid #3b82f6;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: inherit;
        font-weight: inherit;
        color: inherit;
        width: 100%;
        text-align: inherit;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    `;
    
    textElement.style.display = 'none';
    textElement.parentNode.insertBefore(input, textElement);
    
    input.focus();
    input.select();
    
    function finishEditing() {
        const newText = input.value.trim();
        if (newText !== '') {
            textElement.textContent = newText;
        }
        textElement.style.display = '';
        input.remove();
        isTextEditing = false;
    }
    
    input.addEventListener('blur', finishEditing);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            finishEditing();
        } else if (e.key === 'Escape') {
            textElement.style.display = '';
            input.remove();
            isTextEditing = false;
        }
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        z-index: 4000;
        font-weight: 500;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function showEditHandles() {
    const editHandles = document.querySelectorAll('.edit-handle');
    editHandles.forEach(handle => {
        handle.style.display = 'flex';
    });
    
    // Also reinitialize nested edit handles
    document.querySelectorAll('.editable-element').forEach(element => {
        addNestedEditHandles(element);
    });
}

function hideEditHandles() {
    const editHandles = document.querySelectorAll('.edit-handle');
    editHandles.forEach(handle => {
        handle.style.display = 'none';
    });
    
    // Hide nested edit buttons
    const nestedEditBtns = document.querySelectorAll('.nested-edit-btn');
    nestedEditBtns.forEach(btn => {
        btn.style.display = 'none';
    });
}

function saveOriginalLayout() {
    const containers = document.querySelectorAll('.editable-container');
    originalLayout = {};
    
    containers.forEach(container => {
        const containerName = container.dataset.container;
        originalLayout[containerName] = [];
        
        const elements = container.querySelectorAll('.editable-element');
        elements.forEach(element => {
            originalLayout[containerName].push({
                element: element.cloneNode(true),
                id: element.dataset.element
            });
        });
    });
}

function resetLayout() {
    if (confirm('Are you sure you want to reset the layout to default?')) {
        const containers = document.querySelectorAll('.editable-container');
        
        containers.forEach(container => {
            const containerName = container.dataset.container;
            if (originalLayout[containerName]) {
                // Clear container
                container.innerHTML = '';
                
                // Restore original elements
                originalLayout[containerName].forEach(item => {
                    const element = item.element.cloneNode(true);
                    container.appendChild(element);
                });
            }
        });
        
        // Reinitialize drag and drop
        initializeDragAndDrop();
        
        if (isEditMode) {
            showEditHandles();
        }
    }
}

function initializeDragAndDrop() {
    const editableElements = document.querySelectorAll('.editable-element');
    const containers = document.querySelectorAll('.editable-container');
    
    editableElements.forEach(element => {
        element.draggable = true;
        
        element.addEventListener('dragstart', handleDragStart);
        element.addEventListener('dragend', handleDragEnd);
    });
    
    containers.forEach(container => {
        container.addEventListener('dragover', handleDragOver);
        container.addEventListener('drop', handleDrop);
        container.addEventListener('dragenter', handleDragEnter);
        container.addEventListener('dragleave', handleDragLeave);
    });
}

function handleDragStart(e) {
    if (!isEditMode) return;
    
    draggedElement = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.outerHTML);
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
    draggedElement = null;
    
    // Remove drag-over class from all containers
    document.querySelectorAll('.editable-container').forEach(container => {
        container.classList.remove('drag-over');
    });
}

function handleDragOver(e) {
    if (!isEditMode || !draggedElement) return;
    
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
}

function handleDragEnter(e) {
    if (!isEditMode || !draggedElement) return;
    
    this.classList.add('drag-over');
}

function handleDragLeave(e) {
    if (!isEditMode || !draggedElement) return;
    
    // Only remove drag-over if we're actually leaving the container
    if (!this.contains(e.relatedTarget)) {
        this.classList.remove('drag-over');
    }
}

function handleDrop(e) {
    if (!isEditMode || !draggedElement) return;
    
    e.preventDefault();
    this.classList.remove('drag-over');
    
    // Don't drop on itself or its parent
    if (this === draggedElement || this.contains(draggedElement)) {
        return;
    }
    
    // Move the element
    const draggedElementClone = draggedElement.cloneNode(true);
    this.appendChild(draggedElementClone);
    draggedElement.remove();
    
    // Reinitialize drag and drop for the new element
    initializeDragAndDrop();
    
    if (isEditMode) {
        showEditHandles();
    }
}

function removeElement(button) {
    if (confirm('Are you sure you want to remove this element?')) {
        const element = button.closest('.editable-element');
        element.classList.add('removing');
        
        setTimeout(() => {
            element.remove();
        }, 300);
    }
}

// Update the openColorPicker function to use the new advanced edit
function openColorPicker(button) {
    openAdvancedEdit(button);
}

// Utility function to convert RGB to HEX
function rgbToHex(rgb) {
    if (!rgb || rgb === 'rgba(0, 0, 0, 0)' || rgb === 'transparent') return '#ffffff';
    
    // Handle already hex colors
    if (rgb.indexOf('#') === 0) return rgb;
    
    const result = rgb.match(/\d+/g);
    if (!result || result.length < 3) return '#ffffff';
    
    const r = parseInt(result[0]);
    const g = parseInt(result[1]);
    const b = parseInt(result[2]);
    
    return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
}

// Load saved layout on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSavedLayout();
});

// Prevent popup from closing on outside clicks - only close via Cancel/Apply buttons
document.addEventListener('click', function(e) {
    const popup = document.getElementById('editPopup');
    const colorModal = document.getElementById('colorPickerModal');
    
    // Handle color picker modal
    if (e.target === colorModal) {
        closeColorPicker();
    }
    
    // DO NOT close edit popup on outside clicks - it stays open until Cancel/Apply
    // This ensures the popup behavior requested by the user
});

// Prevent escape key from closing popup as well
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const popup = document.getElementById('editPopup');
        if (popup && popup.style.display === 'flex') {
            // Don't close on escape - user must click Cancel or Apply
            e.preventDefault();
        }
    }
});

// Update window resize handler to reposition popup if open
window.addEventListener('resize', function() {
    const popup = document.getElementById('editPopup');
    if (popup && popup.style.display === 'flex' && currentEditElement) {
        // Reposition popup on window resize
        setTimeout(() => {
            positionPopupNearElement(popup, currentEditElement);
        }, 100);
    }
});

// Update scroll handler to keep popup positioned correctly
window.addEventListener('scroll', function() {
    const popup = document.getElementById('editPopup');
    if (popup && popup.style.display === 'flex' && popup.classList.contains('compact-mode') && currentEditElement) {
        // Reposition on scroll for compact mode
        positionPopupNearElement(popup, currentEditElement);
    }
});

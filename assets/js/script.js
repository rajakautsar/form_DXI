/* ============================================
   FORM INTERACTIVITY & VALIDATION - CLEAN VERSION
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('competitionForm');
    const fileInputs = document.querySelectorAll('input[type="file"]');
    const categoryCards = document.querySelectorAll('.category-card');

    // Storage for accumulated files
    const storedFiles = {
        photoFile: [],
        proofFile: [],
        exifFile: []
    };

    // ============================================
    // SETUP FILE INPUT HANDLERS
    // ============================================
    fileInputs.forEach(fileInput => {
        const wrapper = fileInput.parentElement;
        const label = wrapper.querySelector('.file-input-label');
        const isPhotoInput = fileInput.id === 'photoFile';
        
        // Initialize stored files array
        storedFiles[fileInput.id] = [];

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            wrapper.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        // Highlight on drag
        wrapper.addEventListener('dragover', () => {
            label.style.borderColor = 'var(--secondary-color)';
            label.style.background = 'linear-gradient(135deg, rgba(0, 102, 204, 0.15) 0%, rgba(0, 168, 204, 0.15) 100%)';
        }, false);

        wrapper.addEventListener('dragleave', () => {
            label.style.borderColor = 'var(--primary-color)';
            label.style.background = 'linear-gradient(135deg, rgba(0, 102, 204, 0.05) 0%, rgba(0, 168, 204, 0.05) 100%)';
        }, false);

        // Handle drop
        wrapper.addEventListener('drop', (e) => {
            const droppedFiles = e.dataTransfer.files;
            if (droppedFiles && droppedFiles.length > 0) {
                // Accumulate dropped files
                const newFiles = Array.from(droppedFiles);
                storedFiles[fileInput.id] = [...storedFiles[fileInput.id], ...newFiles];
                
                // Check max limits
                const maxFiles = (fileInput.id === 'photoFile') ? 3 : 2;
                if (storedFiles[fileInput.id].length > maxFiles) {
                    showError(fileInput, `Maksimal ${maxFiles} file`);
                    storedFiles[fileInput.id] = storedFiles[fileInput.id].slice(0, maxFiles);
                }
                
                // Update the FileList
                updateAccumulatedFiles(fileInput);
            }
            label.style.borderColor = 'var(--primary-color)';
            label.style.background = 'linear-gradient(135deg, rgba(0, 102, 204, 0.05) 0%, rgba(0, 168, 204, 0.05) 100%)';
        }, false);

        // Click to select
        label.addEventListener('click', (e) => {
            e.preventDefault();
            fileInput.click();
        });

        // File change handler
        fileInput.addEventListener('change', () => {
            if (fileInput.files && fileInput.files.length > 0) {
                // Accumulate files instead of replacing
                const newFiles = Array.from(fileInput.files);
                storedFiles[fileInput.id] = [...storedFiles[fileInput.id], ...newFiles];
                
                // Check max limits based on input type
                const maxFiles = (fileInput.id === 'photoFile') ? 3 : 2;
                if (storedFiles[fileInput.id].length > maxFiles) {
                    showError(fileInput, `Maksimal ${maxFiles} file`);
                    storedFiles[fileInput.id] = storedFiles[fileInput.id].slice(0, maxFiles);
                }
                
                // Update the FileList with accumulated files
                updateAccumulatedFiles(fileInput);
            }
        });
    });

    // ============================================
    // UPDATE ACCUMULATED FILES
    // ============================================
    function updateAccumulatedFiles(fileInput) {
        const files = storedFiles[fileInput.id];
        
        try {
            const dataTransfer = new DataTransfer();
            files.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
            updateFileInput(fileInput);
        } catch(err) {
            console.warn('Error updating files:', err);
        }
    }

    // ============================================
    // UPDATE FILE INPUT DISPLAY
    // ============================================
    function updateFileInput(fileInput) {
        if (!fileInput.files || fileInput.files.length === 0) return;

        const files = fileInput.files;
        const label = fileInput.parentElement.querySelector('.file-input-label');
        const isPhotoFile = fileInput.id === 'photoFile';
        const isProofFile = fileInput.id === 'proofFile';
        const isExifFile = fileInput.id === 'exifFile';

        // Validate file count
        if (isPhotoFile && files.length > 3) {
            showError(fileInput, 'Maksimal 3 file');
            fileInput.value = '';
            return;
        }

        // Validate proof file count (max 2)
        if (isProofFile && files.length > 2) {
            showError(fileInput, 'Maksimal 2 file');
            fileInput.value = '';
            return;
        }

        // Validate exif file count (max 2)
        if (isExifFile && files.length > 2) {
            showError(fileInput, 'Maksimal 2 file');
            fileInput.value = '';
            return;
        }

        // Validate each file
        let valid = true;
        for (let i = 0; i < files.length; i++) {
            if (!validateFileInput(fileInput, files[i])) {
                valid = false;
                break;
            }
        }

        if (!valid) {
            fileInput.value = '';
            return;
        }

        // Display
        if (isPhotoFile) {
            let html = '<div style="text-align: left;">';
            for (let i = 0; i < files.length; i++) {
                html += `<div>📄 ${files[i].name} (${formatSize(files[i].size)})</div>`;
            }
            html += '</div>';
            label.innerHTML = `<i class="icon">🖼️</i>${html}`;
            
            // Update file list
            updatePhotoFileList(files);
        } else if (isProofFile) {
            let html = '<div style="text-align: left;">';
            for (let i = 0; i < files.length; i++) {
                html += `<div>📄 ${files[i].name} (${formatSize(files[i].size)})</div>`;
            }
            html += '</div>';
            label.innerHTML = `<i class="icon">📸</i>${html}`;
            
            // Update proof file list
            updateProofFileList(files);
        } else if (isExifFile) {
            let html = '<div style="text-align: left;">';
            for (let i = 0; i < files.length; i++) {
                html += `<div>📄 ${files[i].name} (${formatSize(files[i].size)})</div>`;
            }
            html += '</div>';
            label.innerHTML = `<i class="icon">📋</i>${html}`;
            
            // Update exif file list
            updateExifFileList(files);
        }

        clearError(fileInput);
    }

    // ============================================
    // UPDATE PHOTO FILE LIST DISPLAY
    // ============================================
    function updatePhotoFileList(files) {
        const listContainer = document.getElementById('photoFileList');
        const listItems = document.getElementById('photoFileItems');

        if (!files || files.length === 0) {
            listContainer.style.display = 'none';
            return;
        }

        listContainer.style.display = 'block';
        listItems.innerHTML = '';

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileItem = document.createElement('div');
            fileItem.className = 'file-list-item';
            fileItem.innerHTML = `
                <div class="file-item-info">
                    <div class="file-item-icon">📸</div>
                    <div class="file-item-details">
                        <div class="file-item-name">${i + 1}. ${file.name}</div>
                        <div class="file-item-size">${formatSize(file.size)}</div>
                    </div>
                </div>
                <button type="button" class="file-item-remove" onclick="removePhotoFile(${i})" title="Hapus file">✕</button>
            `;
            listItems.appendChild(fileItem);
        }
    }

    // ============================================
    // UPDATE PROOF FILE LIST DISPLAY
    // ============================================
    function updateProofFileList(files) {
        const listContainer = document.getElementById('proofFileList');
        const listItems = document.getElementById('proofFileItems');

        if (!files || files.length === 0) {
            listContainer.style.display = 'none';
            return;
        }

        listContainer.style.display = 'block';
        listItems.innerHTML = '';

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileItem = document.createElement('div');
            fileItem.className = 'file-list-item';
            fileItem.innerHTML = `
                <div class="file-item-info">
                    <div class="file-item-icon">📸</div>
                    <div class="file-item-details">
                        <div class="file-item-name">${i + 1}. ${file.name}</div>
                        <div class="file-item-size">${formatSize(file.size)}</div>
                    </div>
                </div>
                <button type="button" class="file-item-remove" onclick="removeProofFile(${i})" title="Hapus file">✕</button>
            `;
            listItems.appendChild(fileItem);
        }
    }

    // ============================================
    // UPDATE EXIF FILE LIST DISPLAY
    // ============================================
    function updateExifFileList(files) {
        const listContainer = document.getElementById('exifFileList');
        const listItems = document.getElementById('exifFileItems');

        if (!files || files.length === 0) {
            listContainer.style.display = 'none';
            return;
        }

        listContainer.style.display = 'block';
        listItems.innerHTML = '';

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileItem = document.createElement('div');
            fileItem.className = 'file-list-item';
            fileItem.innerHTML = `
                <div class="file-item-info">
                    <div class="file-item-icon">📋</div>
                    <div class="file-item-details">
                        <div class="file-item-name">${i + 1}. ${file.name}</div>
                        <div class="file-item-size">${formatSize(file.size)}</div>
                    </div>
                </div>
                <button type="button" class="file-item-remove" onclick="removeExifFile(${i})" title="Hapus file">✕</button>
            `;
            listItems.appendChild(fileItem);
        }
    }

    // ============================================
    // REMOVE PHOTO FILE
    // ============================================
    window.removePhotoFile = function(index) {
        storedFiles.photoFile.splice(index, 1);
        
        if (storedFiles.photoFile.length === 0) {
            document.getElementById('photoFile').value = '';
            document.getElementById('photoFileList').style.display = 'none';
        } else {
            updateAccumulatedFiles(document.getElementById('photoFile'));
        }
    };

    // ============================================
    // REMOVE PROOF FILE
    // ============================================
    window.removeProofFile = function(index) {
        storedFiles.proofFile.splice(index, 1);
        
        if (storedFiles.proofFile.length === 0) {
            document.getElementById('proofFile').value = '';
            document.getElementById('proofFileList').style.display = 'none';
        } else {
            updateAccumulatedFiles(document.getElementById('proofFile'));
        }
    };

    // ============================================
    // REMOVE EXIF FILE
    // ============================================
    window.removeExifFile = function(index) {
        storedFiles.exifFile.splice(index, 1);
        
        if (storedFiles.exifFile.length === 0) {
            document.getElementById('exifFile').value = '';
            document.getElementById('exifFileList').style.display = 'none';
        } else {
            updateAccumulatedFiles(document.getElementById('exifFile'));
        }
    };

    // ============================================
    // VALIDATE FILE
    // ============================================
    function validateFileInput(fileInput, file) {
        const id = fileInput.id;
        let maxSize = 5 * 1024 * 1024;
        let validTypes = [];

        if (id === 'photoFile') {
            maxSize = 20 * 1024 * 1024;
            validTypes = ['image/jpeg', 'image/png', 'image/tiff'];
        } else if (id === 'proofFile') {
            // Proof file: JPG only, max 5MB
            maxSize = 5 * 1024 * 1024;
            validTypes = ['image/jpeg'];
        } else if (id === 'exifFile') {
            maxSize = 5 * 1024 * 1024;
            validTypes = ['text/plain', 'application/pdf', 'application/vnd.ms-excel', 
                         'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                         'image/png', 'image/jpeg'];
        } else {
            validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        }

        if (file.type && !validTypes.includes(file.type)) {
            showError(fileInput, 'Format tidak didukung');
            return false;
        }

        if (file.size > maxSize) {
            const max = maxSize / (1024 * 1024);
            showError(fileInput, `Max ${max}MB`);
            return false;
        }

        clearError(fileInput);
        return true;
    }

    // ============================================
    // FORMAT SIZE
    // ============================================
    function formatSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // ============================================
    // ERROR MESSAGES
    // ============================================
    function showError(input, msg) {
        clearError(input);
        const div = document.createElement('div');
        div.className = 'error-message';
        div.textContent = '⚠️ ' + msg;
        div.style.cssText = `
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 8px;
            padding: 8px;
            background: rgba(255, 107, 107, 0.1);
            border-radius: 4px;
            animation: slideIn 0.3s ease;
        `;
        input.parentElement.insertAdjacentElement('afterend', div);
    }

    function clearError(input) {
        const err = input.parentElement.nextElementSibling;
        if (err && err.classList.contains('error-message')) {
            err.remove();
        }
    }

    function showNotification(msg, type = 'info') {
        const notif = document.createElement('div');
        
        let bgColor = '#d1ecf1';
        let borderColor = '#17a2b8';
        let icon = 'ℹ';

        if (type === 'success') {
            bgColor = '#d4edda';
            borderColor = '#28a745';
            icon = '✓';
        } else if (type === 'warning') {
            bgColor = '#fff3cd';
            borderColor = '#ffc107';
            icon = '⚠';
        }

        notif.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${bgColor};
            border-left: 4px solid ${borderColor};
            padding: 15px 20px;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            min-width: 300px;
            animation: slideInRight 0.3s ease;
            font-weight: 500;
        `;

        notif.innerHTML = `<strong>${icon}</strong> ${msg}`;
        document.body.appendChild(notif);

        setTimeout(() => {
            notif.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notif.remove(), 300);
        }, 4000);
    }

    // ============================================
    // PHONE NUMBER INPUT - FILTER NUMBERS ONLY
    // ============================================
    const phoneInput = document.getElementById('phoneNumber');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Remove all non-digit characters
            this.value = this.value.replace(/[^\d]/g, '');
        });
    }

    // ============================================
    // CATEGORY SELECTION
    // ============================================
    categoryCards.forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        if (radio) {
            radio.addEventListener('change', function() {
                categoryCards.forEach(c => c.style.transform = 'scale(1)');
                if (this.checked) card.style.transform = 'scale(1.02)';
            });
        }
    });

    // ============================================
    // FORM SUBMIT (send to server via AJAX)
    // ============================================
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const agr = document.getElementById('agreement');
        if (!agr.checked) {
            showNotification('Setujui pernyataan peserta', 'warning');
            return;
        }

        const photo = document.getElementById('photoFile');
        const proof = document.getElementById('proofFile');
        const exif = document.getElementById('exifFile');

        if (!photo.files || photo.files.length === 0) {
            showNotification('Upload file karya', 'warning');
            return;
        }

        if (photo.files.length > 3) {
            showNotification('Max 3 file karya', 'warning');
            return;
        }

        if (!proof.files || proof.files.length === 0) {
            showNotification('Upload bukti follow & repost (JPG)', 'warning');
            return;
        }

        if (proof.files.length > 2) {
            showNotification('Max 2 file bukti', 'warning');
            return;
        }

        if (!exif.files || exif.files.length === 0) {
            showNotification('Upload EXIF data', 'warning');
            return;
        }

        if (exif.files.length > 2) {
            showNotification('Max 2 file EXIF', 'warning');
            return;
        }

        // Build FormData including files
        const formData = new FormData();
        
        // Append simple fields
        ['fullName','phoneNumber','instagram','address','photoTitle','agreement'].forEach(k => {
            const el = document.getElementsByName(k)[0];
            if (el) {
                if (el.type === 'checkbox') formData.append(k, el.checked ? '1' : '0');
                else formData.append(k, el.value);
            }
        });
        
        // Handle category radio buttons (must get CHECKED element, not first one)
        const categoryElement = document.querySelector('input[name="category"]:checked');
        if (categoryElement) {
            formData.append('category', categoryElement.value);
        }

        // Append photo files (preserve order)
        Array.from(photo.files).forEach(f => formData.append('photoFile[]', f, f.name));
        
        // Append proof files (now multiple, max 2)
        Array.from(proof.files).forEach(f => formData.append('proofFile[]', f, f.name));
        
        // Append exif files (now multiple, max 2)
        Array.from(exif.files).forEach(f => formData.append('exifFile[]', f, f.name));

        showNotification('Mengirim data...', 'info');

        fetch('process_form.php', {
            method: 'POST',
            body: formData
        }).then(async res => {
            const json = await res.json().catch(() => null);
            if (!res.ok) {
                const msg = (json && json.errors) ? json.errors.join('; ') : (json && json.message) ? json.message : 'Server error';
                showNotification('Gagal: ' + msg, 'error');
                return;
            }

            // Success
            showNotification((json && json.message) ? json.message : 'Berhasil dikirim!', 'success');
            // Reset form after short delay
            setTimeout(() => {
                form.reset();
                resetLabels();
            }, 1200);

        }).catch(err => {
            console.error('Submit error', err);
            showNotification('Terjadi kesalahan saat mengirim', 'error');
        });
    });

    // ============================================
    // RESET LABELS
    // ============================================
    function resetLabels() {
        fileInputs.forEach(input => {
            // Clear stored files
            storedFiles[input.id] = [];
            
            const label = input.parentElement.querySelector('.file-input-label');
            if (!label) return;
            
            if (input.id === 'photoFile') {
                label.innerHTML = `<i class="icon">🖼️</i><span>Pilih File Foto (Maks 3) atau Drag & Drop</span>`;
                const photoFileList = document.getElementById('photoFileList');
                if (photoFileList) {
                    photoFileList.style.display = 'none';
                }
            } else if (input.id === 'proofFile') {
                label.innerHTML = `<i class="icon">📸</i><span>Pilih File Bukti (Maks 2 JPG) atau Drag & Drop</span>`;
                const proofFileList = document.getElementById('proofFileList');
                if (proofFileList) {
                    proofFileList.style.display = 'none';
                }
            } else if (input.id === 'exifFile') {
                label.innerHTML = `<i class="icon">📋</i><span>Pilih File Exif Data (Maks 2) atau Drag & Drop</span>`;
                const exifFileList = document.getElementById('exifFileList');
                if (exifFileList) {
                    exifFileList.style.display = 'none';
                }
            }
        });
    }

    // ============================================
    // FORM RESET CONFIRMATION
    // ============================================
    const resetBtn = form.querySelector('button[type="reset"]');
    if (resetBtn) {
        resetBtn.addEventListener('click', function(e) {
            const inputs = form.querySelectorAll('input[value]:not([value=""])');
            const textarea = form.querySelector('textarea');
            if (inputs.length > 0 || (textarea && textarea.value.trim() !== '')) {
                if (!confirm('Apakah Anda yakin?')) {
                    e.preventDefault();
                    return;
                }
            }
            resetLabels();
        });
    }

    // ============================================
    // SMART FORMATTING
    // ============================================
    const ig = document.getElementById('instagram');
    if (ig) {
        ig.addEventListener('change', function() {
            let val = this.value.trim();
            if (val && !val.startsWith('@')) {
                this.value = '@' + val;
            }
        });
    }

    const phone = document.getElementById('phoneNumber');
    if (phone) {
        phone.addEventListener('input', function() {
            let val = this.value.replace(/\D/g, '');
            if (val.startsWith('62')) {
                this.value = '+' + val;
            } else if (val.startsWith('8')) {
                this.value = '0' + val;
            }
        });
    }

    // ============================================
    // DRAFT SAVING
    // ============================================
    setInterval(saveDraft, 30000);

    function saveDraft() {
        try {
            const data = {};
            const inputs = form.querySelectorAll('input, textarea');
            
            inputs.forEach(input => {
                // SKIP file inputs
                if (input.type === 'file') return;
                
                if (input.type === 'checkbox' || input.type === 'radio') {
                    if (input.checked) data[input.name] = input.value;
                } else if (input.value) {
                    data[input.name] = input.value;
                }
            });

            localStorage.setItem('dxiFormDraft', JSON.stringify(data));
        } catch(e) {
            // Ignore storage errors
        }
    }

    function loadDraft() {
        try {
            const draft = localStorage.getItem('dxiFormDraft');
            if (!draft) return;

            const data = JSON.parse(draft);
            
            for (let key in data) {
                const inputs = form.querySelectorAll(`[name="${key}"]`);
                inputs.forEach(input => {
                    if (input.type === 'file') return; // NEVER set files
                    
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = (data[key] === input.value);
                    } else {
                        input.value = data[key];
                    }
                });
            }
            
            // Clear stored files when loading draft
            storedFiles.photoFile = [];
            storedFiles.proofFile = [];
            storedFiles.exifFile = [];
        } catch(e) {
            // Ignore load errors
        }
    }

    loadDraft();
    console.log('✓ Form ready');
});

// ============================================
// CSS ANIMATIONS
// ============================================
const style = document.createElement('style');
style.innerHTML = `
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideOutRight {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(100px); }
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .error-message {
        color: #ff6b6b !important;
        font-size: 0.85rem !important;
        margin-top: 8px !important;
        padding: 8px !important;
        background: rgba(255, 107, 107, 0.1) !important;
        border-radius: 4px !important;
        animation: slideIn 0.3s ease !important;
    }
`;
document.head.appendChild(style);

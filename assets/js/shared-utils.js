// ===== SHARED UTILITY FUNCTIONS =====

// ===== ALERT FUNCTIONS =====
function hideAlert(element) {
    if (typeof element === 'string') {
        element = document.getElementById(element);
    }
    
    if (element) {
        element.classList.add('fade-out');
        setTimeout(() => {
            if (element.parentNode) {
                element.remove();
            }
        }, 500);
    }
}

function showErrorMessage(message) {
    const existingAlert = document.getElementById('dynamic-error-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-error';
    alertDiv.id = 'dynamic-error-alert';
    alertDiv.innerHTML = `
        <span class="alert-close" onclick="hideAlert('dynamic-error-alert')">&times;</span>
        ${message}
    `;
    
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        navbar.insertAdjacentElement('afterend', alertDiv);
    } else {
        document.body.insertBefore(alertDiv, document.body.firstChild);
    }
    
    setTimeout(function() {
        hideAlert(alertDiv);
    }, 5000);
}

function showSuccessMessage(message) {
    const existingAlert = document.getElementById('dynamic-success-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success';
    alertDiv.id = 'dynamic-success-alert';
    alertDiv.innerHTML = `
        <span class="alert-close" onclick="hideAlert('dynamic-success-alert')">&times;</span>
        ${message}
    `;
    
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        navbar.insertAdjacentElement('afterend', alertDiv);
    } else {
        document.body.insertBefore(alertDiv, document.body.firstChild);
    }
    
    setTimeout(function() {
        hideAlert(alertDiv);
    }, 5000);
}

// ===== AUTO-HIDE SESSION ALERTS =====
function initializeSessionAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert, index) {
        if (!alert.id) {
            alert.id = 'auto-alert-' + index;
        }
        
        if (!alert.querySelector('.alert-close')) {
            const closeBtn = document.createElement('span');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.onclick = function() {
                hideAlert(alert);
            };
            
            alert.style.position = 'relative';
            alert.appendChild(closeBtn);
        }
        
        setTimeout(function() {
            hideAlert(alert);
        }, 5000);
    });
}

// ===== FORM VALIDATION UTILITIES =====
function validateFieldInput(field, newValue, currentValue, cell) {
    switch(field) {
        case 'persentase':
            if (newValue < 1 || newValue > 12 || !Number.isInteger(Number(newValue))) {
                showErrorMessage('Persentase harus berupa bilangan bulat antara 1-12');
                cell.text(getDisplayValue(field, currentValue));
                return false;
            }
            break;
            
        case 'koefisien':
            if (isNaN(newValue) || Number(newValue) <= 0) {
                showErrorMessage('Koefisien harus berupa angka positif');
                cell.text(getDisplayValue(field, currentValue));
                return false;
            }
            break;
            
        case 'tahun':
            if (isNaN(newValue) || Number(newValue) < 1900 || Number(newValue) > 2100) {
                showErrorMessage('Tahun harus berupa angka antara 1900-2100');
                cell.text(getDisplayValue(field, currentValue));
                return false;
            }
            break;
            
        case 'periode':
            const validMonths = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 
                            'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
            const periodeParts = newValue.toLowerCase().split(' - ');
            let isValidPeriode = true;
            
            for (let month of periodeParts) {
                month = month.trim();
                if (!validMonths.includes(month)) {
                    isValidPeriode = false;
                    break;
                }
            }
            
            if (!isValidPeriode || newValue.trim() === '') {
                showErrorMessage('Format periode tidak valid. Gunakan nama bulan atau range bulan (contoh: "April" atau "April - Juni")');
                cell.text(getDisplayValue(field, currentValue));
                return false;
            }
            break;
            
        case 'predikat':
            if (newValue.trim() === '') {
                showErrorMessage('Predikat tidak boleh kosong');
                cell.text(getDisplayValue(field, currentValue));
                return false;
            }
            break;
    }
    return true;
}

function getDisplayValue(field, value) {
    switch(field) {
        case 'persentase':
            return value + '/12';
        case 'koefisien':
            return parseFloat(value).toFixed(2);
        case 'periode':
            return value.split(' ').map(word => 
                word === '-' ? word : word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
            ).join(' ');
        default:
            return value;
    }
}

// ===== PERIOD DUPLICATION CHECK =====
function checkPeriodeDuplikasi(bulanAwal, bulanAkhir, tahun, form) {
    $.ajax({
        url: 'check_periode_duplikasi.php',
        type: 'POST',
        data: {
            bulan_awal: bulanAwal,
            bulan_akhir: bulanAkhir,
            tahun: tahun
        },
        dataType: 'json',
        success: function(response) {
            if (response.exists) {
                showErrorMessage('Data untuk periode ' + bulanAwal + ' - ' + bulanAkhir + ' tahun ' + tahun + ' sudah ada! Silakan pilih periode yang berbeda.');
            } else {
                form.submit();
            }
        },
        error: function() {
            console.log('Error checking periode duplikasi, proceeding with server validation');
            form.submit();
        }
    });
}

// ===== PREDICATE CALCULATION UTILITIES =====
const predikatMultiplier = {
    "sangat baik": 1.5,    // 150%
    "baik": 1.0,           // 100%
    "butuh perbaikan": 0.75, // 75%
    "kurang": 0.5,         // 50%
    "sangat kurang": 0.25  // 25%
};

function hitungAngkaKreditDenganPredikat(predikatSelect, persentaseInput, koefisienInput, angkaKreditInput) {
    const predikatValue = predikatSelect.value;
    const nilaiPersentase = parseFloat(persentaseInput.value);
    const nilaiKoefisien = parseFloat(koefisienInput.value);
    
    if (predikatValue && !isNaN(nilaiPersentase) && !isNaN(nilaiKoefisien)) {
        // Hitung persentase dasar (bulan/12 * 100)
        const persenDasar = (nilaiPersentase / 12) * 100;
        
        // Kalikan dengan multiplier predikat
        const multiplier = predikatMultiplier[predikatValue];
        const persenAkhir = persenDasar * multiplier;
        
        // Hitung angka kredit
        const angkaKredit = persenAkhir * nilaiKoefisien / 100;
        
        angkaKreditInput.value = angkaKredit.toFixed(3);
        
        console.log("Perhitungan Angka Kredit:");
        console.log("Predikat:", predikatValue, "(" + (multiplier * 100) + "%)");
        console.log("Persentase:", nilaiPersentase + "/12 =", persenDasar.toFixed(2) + "%");
        console.log("Persentase x Multiplier:", persenDasar.toFixed(2) + "% x", multiplier, "=", persenAkhir.toFixed(2) + "%");
        console.log("Angka Kredit:", persenAkhir.toFixed(2) + "% x", nilaiKoefisien, "=", angkaKredit.toFixed(3));
    } else {
        angkaKreditInput.value = "";
    }
}

function setupAutomaticPercentageCalculation(bulanAwalSelect, bulanAkhirSelect, persentaseInput, callback) {
    const bulanMap = {
        "januari": 1, "februari": 2, "maret": 3, "april": 4,
        "mei": 5, "juni": 6, "juli": 7, "agustus": 8,
        "september": 9, "oktober": 10, "november": 11, "desember": 12
    };

    function hitungPersentase() {
        let awal = bulanMap[bulanAwalSelect.value.toLowerCase()] || 0;
        let akhir = bulanMap[bulanAkhirSelect.value.toLowerCase()] || 0;

        if (awal > 0 && akhir > 0) {
            let selisih = akhir - awal + 1;
            if (selisih <= 0) selisih += 12; 
            persentaseInput.value = selisih;
            
            // Trigger callback (usually angka kredit calculation)
            if (callback && typeof callback === 'function') {
                callback();
            }
        } else {
            persentaseInput.value = "";
        }
    }

    bulanAwalSelect.addEventListener("change", hitungPersentase);
    bulanAkhirSelect.addEventListener("change", hitungPersentase);
}

// ===== NAVIGATION ACTIVE STATE =====
function setActiveNavLink() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        const linkPath = new URL(link.href).pathname;
        if (currentPath.includes(linkPath.split('/').pop().replace('.php', ''))) {
            link.classList.add('active');
        }
    });
}

// ===== DELETE CONFIRMATION =====
function confirmDelete(message = 'Apakah Anda yakin ingin menghapus data ini?') {
    return confirm(message);
}

// ===== LOADING STATE HELPERS =====
function showLoading(element, text = 'Memuat...') {
    if (typeof element === 'string') {
        element = document.getElementById(element);
    }
    
    if (element) {
        element.innerHTML = `<div class="loading">${text}</div>`;
    }
}

function hideLoading(element, content = '') {
    if (typeof element === 'string') {
        element = document.getElementById(element);
    }
    
    if (element) {
        element.innerHTML = content;
    }
}

// ===== TABLE UTILITIES =====
function addTableRow(tableBodyId, rowHtml) {
    const tableBody = document.getElementById(tableBodyId);
    if (tableBody) {
        tableBody.insertAdjacentHTML('beforeend', rowHtml);
    }
}

function removeTableRow(rowElement) {
    if (rowElement) {
        rowElement.remove();
    }
}

// ===== NUMBER FORMATTING =====
function formatNumber(num, decimals = 2) {
    return parseFloat(num).toFixed(decimals);
}

function formatCurrency(num) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(num);
}

// ===== DATE UTILITIES =====
function getCurrentYear() {
    return new Date().getFullYear();
}

function generateYearOptions(startYear = 2020, endYear = getCurrentYear()) {
    const options = [];
    for (let i = endYear; i >= startYear; i--) {
        options.push(`<option value="${i}">${i}</option>`);
    }
    return options.join('');
}

// ===== AJAX UTILITIES =====
function makeAjaxRequest(config) {
    const defaultConfig = {
        type: 'POST',
        dataType: 'json',
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            showErrorMessage('Terjadi kesalahan saat memproses request');
        }
    };
    
    return $.ajax($.extend(defaultConfig, config));
}

// ===== INITIALIZATION FUNCTION =====
function initializeCommonFeatures() {
    // Initialize session alerts
    initializeSessionAlerts();
    
    // Set active navigation link
    setActiveNavLink();
    
    // Initialize common event listeners
    document.addEventListener('click', function(e) {
        // Handle alert close buttons
        if (e.target.classList.contains('alert-close')) {
            hideAlert(e.target.parentElement);
        }
    });
}

// ===== AUTO-INITIALIZE ON DOM READY =====
document.addEventListener('DOMContentLoaded', function() {
    initializeCommonFeatures();
});

// ===== EXPORT FOR MODULES (if needed) =====
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        hideAlert,
        showErrorMessage,
        showSuccessMessage,
        validateFieldInput,
        getDisplayValue,
        checkPeriodeDuplikasi,
        hitungAngkaKreditDenganPredikat,
        setupAutomaticPercentageCalculation,
        setActiveNavLink,
        confirmDelete,
        showLoading,
        hideLoading,
        formatNumber,
        formatCurrency,
        getCurrentYear,
        generateYearOptions,
        makeAjaxRequest,
        initializeCommonFeatures
    };
}
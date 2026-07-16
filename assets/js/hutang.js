// assets/js/hutang.js

// Copy receipt text to clipboard
function copyReceiptText(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            alert("Resi pembayaran berhasil disalin ke clipboard! Silakan kirimkan ke WhatsApp pelanggan.");
        }).catch(err => {
            console.error("Gagal menyalin text: ", err);
        });
    } else {
        // Fallback
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            alert("Resi pembayaran berhasil disalin ke clipboard! Silakan kirimkan ke WhatsApp pelanggan.");
        } catch (err) {
            console.error("Fallback gagal menyalin: ", err);
        }
        document.body.removeChild(textArea);
    }
}

// Print receipt div
function printReceiptDiv() {
    const printEl = document.getElementById('strukPembayaran');
    if (!printEl) return;
    
    const printContents = printEl.innerHTML;
    const originalContents = document.body.innerHTML;
    
    // Create print window style
    document.body.innerHTML = `
        <div style="font-family: monospace; padding: 20px; max-width: 300px; margin: auto;">
            ${printContents}
        </div>
    `;
    
    window.print();
    
    // Restore
    document.body.innerHTML = originalContents;
    window.location.reload(); // Refresh to restore JS listeners
}

// Prevent dropdown clipping inside scrollable table-responsive containers on all devices (PC, tablet, mobile)
document.addEventListener('DOMContentLoaded', function () {
    const tableContainers = document.querySelectorAll('.table-responsive, .table-responsive-scroll');
    
    tableContainers.forEach(function (container) {
        container.addEventListener('show.bs.dropdown', function (event) {
            const dropdown = event.target;
            const menu = dropdown.querySelector('.dropdown-menu');
            if (menu) {
                // Store parent ID so we can restore it on hide
                if (!dropdown.id) {
                    dropdown.id = 'dropdown-parent-' + Math.floor(Math.random() * 100000);
                }
                menu.dataset.parentDropdown = dropdown.id;
                
                // Append to body
                document.body.appendChild(menu);
            }
        });
        
        container.addEventListener('hide.bs.dropdown', function (event) {
            const dropdown = event.target;
            const menuId = dropdown.id;
            if (menuId) {
                const menu = document.querySelector(`body > .dropdown-menu[data-parent-dropdown="${menuId}"]`);
                if (menu) {
                    // Restore back to the dropdown container
                    dropdown.appendChild(menu);
                }
            }
        });
    });
});

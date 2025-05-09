document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const toggleBtn = document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    }
    
    // Modal functionality
    const customReportModal = document.getElementById('custom-report-modal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.querySelector('.cancel-btn');
    
    // Generate report buttons
    const generateBtns = document.querySelectorAll('.generate-btn');
    generateBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Get the report type from the parent card
            const reportCard = this.closest('.report-template-card');
            const reportTitle = reportCard.querySelector('h3').textContent;
            
            // Create a form dynamically
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'generate_report.php';
            
            // Add the report type as a hidden field
            const hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = 'report_type';
            hiddenField.value = reportTitle;
            
            // Add format as CSV
            const formatField = document.createElement('input');
            formatField.type = 'hidden';
            formatField.name = 'format';
            formatField.value = 'csv';
            
            // Append fields to form
            form.appendChild(hiddenField);
            form.appendChild(formatField);
            
            // Append form to body and submit
            document.body.appendChild(form);
            form.submit();
            
            // Remove form from the DOM
            document.body.removeChild(form);
        });
    });
    
    // Close modal
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            customReportModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    }
    
    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            customReportModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    }
    
    // Form submission for custom report
    const reportForm = document.getElementById('custom-report-form');
    if (reportForm) {
        reportForm.addEventListener('submit', function(e) {
            // Set default format to CSV for custom reports too
            const formatSelect = document.getElementById('report-format');
            if (formatSelect) {
                // Find the CSV option and select it
                for (let i = 0; i < formatSelect.options.length; i++) {
                    if (formatSelect.options[i].value === 'csv') {
                        formatSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const toggleBtn = document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('full-width');
        });
    }
    
    // Modal functionality
    const modal = document.getElementById('investment-modal');
    const addInvestmentBtn = document.getElementById('add-investment-btn');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.querySelector('.cancel-btn');
    
    function openModal() {
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeModal() {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            document.getElementById('investment-form').reset();
        }
    }
    
    if (addInvestmentBtn) {
        addInvestmentBtn.addEventListener('click', openModal);
    }
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    
    // Form submission
    const investmentForm = document.getElementById('investment-form');
    
    if (investmentForm) {
        investmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const projectName = document.getElementById('project-name').value;
            const projectCategory = document.getElementById('project-category').value;
            const projectDepartment = document.getElementById('project-department').value;
            const projectBudget = document.getElementById('project-budget').value;
            const projectStartDate = document.getElementById('project-start-date').value;
            const projectEndDate = document.getElementById('project-end-date').value;
            const projectRoi = document.getElementById('project-roi').value;
            const projectStatus = document.getElementById('project-status').value;
            const projectDescription = document.getElementById('project-description').value;
            
            // In a real application, you would send this data to a server
            console.log({
                name: projectName,
                category: projectCategory,
                department: projectDepartment,
                budget: projectBudget,
                startDate: projectStartDate,
                endDate: projectEndDate,
                roi: projectRoi,
                status: projectStatus,
                description: projectDescription
            });
            
            // For demo purposes, add a new row to the table
            addNewInvestmentRow({
                id: generateProjectId(),
                name: projectName,
                category: projectCategory,
                department: projectDepartment,
                budget: formatCurrency(projectBudget),
                spent: '₹0',
                roi: projectRoi ? projectRoi + '%' : 'N/A',
                status: projectStatus
            });
            
            // Close the modal
            closeModal();
            
            // Show success message (you would need to implement this)
            alert('Investment project added successfully!');
        });
    }
    
    // Action buttons functionality
    const viewButtons = document.querySelectorAll('.action-btn.view');
    const editButtons = document.querySelectorAll('.action-btn.edit');
    const deleteButtons = document.querySelectorAll('.action-btn.delete');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const projectId = row.cells[0].textContent;
            const projectName = row.cells[1].textContent;
            
            // In a real application, you would fetch the project details and show them
            alert(`Viewing details for ${projectName} (${projectId})`);
        });
    });
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const projectId = row.cells[0].textContent;
            const projectName = row.cells[1].textContent;
            
            // In a real application, you would fetch the project details and populate a form
            alert(`Editing details for ${projectName} (${projectId})`);
        });
    });
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const projectId = row.cells[0].textContent;
            const projectName = row.cells[1].textContent;
            
            if (confirm(`Are you sure you want to delete ${projectName} (${projectId})?`)) {
                // In a real application, you would call an API to delete the project
                row.remove();
            }
        });
    });
    
    // Chart period buttons
    const chartPeriodBtns = document.querySelectorAll('.chart-period-btn');
    
    chartPeriodBtns.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            chartPeriodBtns.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Update chart data based on selected period
            const period = this.getAttribute('data-period');
            updateInvestmentTrendsChart(period);
        });
    });
    
    // Chart type buttons
    const chartTypeBtns = document.querySelectorAll('.chart-type-btn');
    
    chartTypeBtns.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            chartTypeBtns.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Update chart data based on selected type
            const type = this.getAttribute('data-type');
            updateInvestmentDistributionChart(type);
        });
    });
    
    // Initialize charts
    initializeCharts();
    
    // Helper functions
    function generateProjectId() {
        const lastRow = document.querySelector('.investments-table tbody tr:last-child');
        if (lastRow) {
            const lastId = lastRow.cells[0].textContent;
            const idNumber = parseInt(lastId.split('-')[1]);
            return `INV-${(idNumber + 1).toString().padStart(3, '0')}`;
        }
        return 'INV-001';
    }
    
    function formatCurrency(amount) {
        // Format number to Indian currency format
        const number = parseFloat(amount);
        if (isNaN(number)) return '₹0';
        
        const formatter = new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        
        return formatter.format(number).replace('₹', '₹');
    }
    
    function addNewInvestmentRow(project) {
        const tbody = document.querySelector('.investments-table tbody');
        const newRow = document.createElement('tr');
        
        newRow.innerHTML = `
            <td>${project.id}</td>
            <td>${project.name}</td>
            <td>${project.category}</td>
            <td>${project.department}</td>
            <td>${project.budget}</td>
            <td>${project.spent}</td>
            <td>${project.roi}</td>
            <td><span class="status-badge ${project.status.toLowerCase().replace(' ', '-')}">${project.status}</span></td>
            <td>
                <button class="action-btn view" title="View Details"><i class="fas fa-eye"></i></button>
                <button class="action-btn edit" title="Edit"><i class="fas fa-edit"></i></button>
                <button class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
            </td>
        `;
        
        tbody.prepend(newRow);
        
        // Add event listeners to new buttons
        const viewBtn = newRow.querySelector('.action-btn.view');
        const editBtn = newRow.querySelector('.action-btn.edit');
        const deleteBtn = newRow.querySelector('.action-btn.delete');
        
        viewBtn.addEventListener('click', function() {
            alert(`Viewing details for ${project.name} (${project.id})`);
        });
        
        editBtn.addEventListener('click', function() {
            alert(`Editing details for ${project.name} (${project.id})`);
        });
        
        deleteBtn.addEventListener('click', function() {
            if (confirm(`Are you sure you want to delete ${project.name} (${project.id})?`)) {
                newRow.remove();
            }
        });
    }
    
    // Chart initialization and data
    let trendsChart;
    let distributionChart;
    
    function initializeCharts() {
        // Investment Trends Chart
        const trendsCtx = document.getElementById('investmentTrendsChart');
        if (trendsCtx) {
            trendsChart = new Chart(trendsCtx, {
                type: 'line',
                data: getInvestmentTrendsData('monthly'),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += '₹' + context.parsed.y.toLocaleString('en-IN');
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString('en-IN');
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Investment Distribution Chart
        const distributionCtx = document.getElementById('investmentDistributionChart');
        if (distributionCtx) {
            distributionChart = new Chart(distributionCtx, {
                type: 'doughnut',
                data: getInvestmentDistributionData('category'),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed !== null) {
                                        label += '₹' + context.parsed.toLocaleString('en-IN');
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    
    function updateInvestmentTrendsChart(period) {
        if (trendsChart) {
            trendsChart.data = getInvestmentTrendsData(period);
            trendsChart.update();
        }
    }
    
    function updateInvestmentDistributionChart(type) {
        if (distributionChart) {
            distributionChart.data = getInvestmentDistributionData(type);
            distributionChart.update();
        }
    }
    
    function getInvestmentTrendsData(period) {
        // Sample data - in a real application, this would come from an API
        let labels, investmentData, roiData;
        
        if (period === 'monthly') {
            labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            investmentData = [1500000, 1200000, 1800000, 2200000, 1900000, 2500000, 2800000, 3100000, 2700000, 3000000, 3500000, 3800000];
            roiData = [150000, 180000, 220000, 250000, 280000, 320000, 350000, 380000, 400000, 450000, 500000, 550000];
        } else if (period === 'quarterly') {
            labels = ['Q1 2023', 'Q2 2023', 'Q3 2023', 'Q4 2023'];
            investmentData = [4500000, 6600000, 8600000, 10300000];
            roiData = [550000, 850000, 1130000, 1500000];
        } else { // yearly
            labels = ['2019', '2020', '2021', '2022', '2023'];
            investmentData = [12000000, 15000000, 18000000, 22000000, 30000000];
            roiData = [1800000, 2200000, 2700000, 3500000, 5000000];
        }
        
        return {
            labels: labels,
            datasets: [
                {
                    label: 'Investments',
                    data: investmentData,
                    borderColor: 'rgba(26, 115, 232, 1)',
                    backgroundColor: 'rgba(26, 115, 232, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Returns',
                    data: roiData,
                    borderColor: 'rgba(76, 175, 80, 1)',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }
            ]
        };
    }
    
    function getInvestmentDistributionData(type) {
        // Sample data - in a real application, this would come from an API
        let labels, data, backgroundColor;
        
        if (type === 'category') {
            labels = ['Hardware', 'Software', 'Cloud Services', 'Network', 'Security', 'Services'];
            data = [3500000, 4200000, 2800000, 1500000, 2200000, 1000000];
            backgroundColor = [
                'rgba(26, 115, 232, 0.8)',
                'rgba(76, 175, 80, 0.8)',
                'rgba(156, 39, 176, 0.8)',
                'rgba(255, 152, 0, 0.8)',
                'rgba(244, 67, 54, 0.8)',
                'rgba(96, 125, 139, 0.8)'
            ];
        } else { // department
            labels = ['IT', 'HR', 'Finance', 'Marketing', 'Operations'];
            data = [6500000, 1200000, 2500000, 3800000, 1200000];
            backgroundColor = [
                'rgba(26, 115, 232, 0.8)',
                'rgba(76, 175, 80, 0.8)',
                'rgba(156, 39, 176, 0.8)',
                'rgba(255, 152, 0, 0.8)',
                'rgba(244, 67, 54, 0.8)'
            ];
        }
        
        return {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColor,
                borderColor: 'rgba(255, 255, 255, 0.8)',
                borderWidth: 2,
                hoverOffset: 15
            }]
        };
    }
    
    // Export functionality
    const exportBtn = document.getElementById('export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            // In a real application, this would generate a CSV or Excel file
            alert('Exporting investment data...');
        });
    }
    
    // Pagination functionality
    const pageButtons = document.querySelectorAll('.page-btn');
    if (pageButtons.length > 0) {
        pageButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                pageButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // In a real application, this would fetch the next page of data
                if (!this.classList.contains('next')) {
                    const page = this.textContent;
                    console.log(`Loading page ${page}`);
                } else {
                    console.log('Loading next page');
                }
            });
        });
    }
}); 
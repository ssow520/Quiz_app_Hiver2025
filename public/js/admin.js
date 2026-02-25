// admin.js
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des formulaires de suppression
    const deleteForms = document.querySelectorAll('form[data-confirm]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Animation des statistiques
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        let currentValue = 0;
        const duration = 1000;
        const increment = finalValue / (duration / 16);

        const animate = () => {
            if (currentValue < finalValue) {
                currentValue += increment;
                stat.textContent = Math.round(currentValue);
                requestAnimationFrame(animate);
            } else {
                stat.textContent = finalValue;
            }
        };
        animate();
    });

    // Gestion des changements de rôle
    const roleSelects = document.querySelectorAll('select[name="role"]');
    roleSelects.forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            const userId = form.querySelector('input[name="user_id"]').value;
            
            fetch('../ajax/update_role.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${userId}&role=${this.value}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Rôle mis à jour avec succès', 'success');
                } else {
                    showNotification('Erreur lors de la mise à jour', 'error');
                }
            });
        });
    });

    // Import de questions CSV
    const importForm = document.getElementById('import-form');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../ajax/import_questions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showNotification(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    setTimeout(() => window.location.reload(), 1500);
                }
            });
        });
    }

    // Système de notifications
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        }, 100);
    }

    // Filtrage et recherche dans les tableaux
    const tableFilter = document.getElementById('table-filter');
    if (tableFilter) {
        tableFilter.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Tri des colonnes
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.sort;
            const tbody = this.closest('table').querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const isAsc = this.classList.contains('asc');

            rows.sort((a, b) => {
                const aValue = a.querySelector(`td[data-${column}]`).dataset[column];
                const bValue = b.querySelector(`td[data-${column}]`).dataset[column];
                return isAsc ? bValue.localeCompare(aValue) : aValue.localeCompare(bValue);
            });

            sortableHeaders.forEach(h => h.classList.remove('asc', 'desc'));
            this.classList.toggle('asc', !isAsc);
            this.classList.toggle('desc', isAsc);

            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        });
    });
});
/**
 * BatiSaaS - Graphiques et visualisations
 * Utilise Chart.js pour afficher des graphiques
 */

// Configuration globale Chart.js
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';
}

/**
 * Graphique de revenus par mois
 */
function createRevenueChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Chiffre d\'affaires',
                data: data.revenue,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }, {
                label: 'Dépenses',
                data: data.expenses,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' +
                                   new Intl.NumberFormat('fr-FR', {
                                       style: 'currency',
                                       currency: 'EUR'
                                   }).format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR', {
                                style: 'currency',
                                currency: 'EUR',
                                minimumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            }
        }
    });
}

/**
 * Graphique circulaire des chantiers par statut
 */
function createStatusPieChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    '#2563eb', // Planifié
                    '#06b6d4', // En cours
                    '#10b981', // Terminé
                    '#f59e0b', // Suspendu
                    '#ef4444'  // Annulé
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Graphique en barres de rentabilité par chantier
 */
function createProfitabilityChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Budget estimé',
                data: data.estimated,
                backgroundColor: 'rgba(37, 99, 235, 0.7)',
                borderColor: '#2563eb',
                borderWidth: 1
            }, {
                label: 'Coût réel',
                data: data.actual,
                backgroundColor: 'rgba(239, 68, 68, 0.7)',
                borderColor: '#ef4444',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' +
                                   new Intl.NumberFormat('fr-FR', {
                                       style: 'currency',
                                       currency: 'EUR'
                                   }).format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR', {
                                style: 'currency',
                                currency: 'EUR',
                                minimumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            }
        }
    });
}

/**
 * Graphique de progression d'un chantier
 */
function createProgressChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.dates,
            datasets: [{
                label: 'Progression (%)',
                data: data.progress,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Progression: ' + context.parsed.y + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialise tous les graphiques de la page
 */
function initCharts() {
    // Recherche les canvas avec attribut data-chart
    document.querySelectorAll('[data-chart]').forEach(canvas => {
        const chartType = canvas.getAttribute('data-chart');
        const chartData = JSON.parse(canvas.getAttribute('data-chart-data'));

        switch (chartType) {
            case 'revenue':
                createRevenueChart(canvas.id, chartData);
                break;
            case 'status-pie':
                createStatusPieChart(canvas.id, chartData);
                break;
            case 'profitability':
                createProfitabilityChart(canvas.id, chartData);
                break;
            case 'progress':
                createProgressChart(canvas.id, chartData);
                break;
        }
    });
}

// Auto-init au chargement
document.addEventListener('DOMContentLoaded', initCharts);

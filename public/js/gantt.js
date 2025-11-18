/**
 * BatiSaaS - Planning Gantt interactif
 * Timeline visuelle des chantiers avec dépendances
 */

class GanttPlanner {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error('Gantt container not found:', containerId);
            return;
        }

        this.options = Object.assign({
            viewMode: 'Month', // Day, Week, Month, Year
            language: 'fr',
            barHeight: 30,
            barCornerRadius: 3,
            paddingX: 50,
            paddingY: 50,
            onTaskClick: null,
            onTaskDblClick: null,
            onDateChange: null,
            onProgressChange: null
        }, options);

        this.tasks = [];
        this.currentView = this.options.viewMode;
        this.init();
    }

    init() {
        this.container.innerHTML = `
            <div class="gantt-wrapper">
                <div class="gantt-controls">
                    <div class="gantt-view-modes">
                        <button class="btn-view ${this.currentView === 'Day' ? 'active' : ''}" data-mode="Day">
                            <i class="fas fa-calendar-day"></i> Jour
                        </button>
                        <button class="btn-view ${this.currentView === 'Week' ? 'active' : ''}" data-mode="Week">
                            <i class="fas fa-calendar-week"></i> Semaine
                        </button>
                        <button class="btn-view ${this.currentView === 'Month' ? 'active' : ''}" data-mode="Month">
                            <i class="fas fa-calendar-alt"></i> Mois
                        </button>
                        <button class="btn-view ${this.currentView === 'Year' ? 'active' : ''}" data-mode="Year">
                            <i class="fas fa-calendar"></i> Année
                        </button>
                    </div>
                    <div class="gantt-actions">
                        <button class="btn btn-sm btn-secondary" id="gantt-today">
                            <i class="fas fa-calendar-check"></i> Aujourd'hui
                        </button>
                        <button class="btn btn-sm btn-secondary" id="gantt-export">
                            <i class="fas fa-download"></i> Exporter PNG
                        </button>
                        <button class="btn btn-sm btn-secondary" id="gantt-print">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    </div>
                </div>
                <div class="gantt-container">
                    <svg id="gantt-svg"></svg>
                </div>
                <div class="gantt-legend">
                    <div class="legend-item">
                        <span class="legend-color" style="background: #2563eb;"></span>
                        <span>Planifié</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #10b981;"></span>
                        <span>En cours</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #64748b;"></span>
                        <span>Terminé</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #ef4444;"></span>
                        <span>En retard</span>
                    </div>
                </div>
            </div>
        `;

        this.svg = document.getElementById('gantt-svg');
        this.setupEventListeners();
    }

    setupEventListeners() {
        // View mode buttons
        const viewButtons = this.container.querySelectorAll('.btn-view');
        viewButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const mode = btn.dataset.mode;
                this.changeView(mode);

                viewButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });

        // Today button
        const todayBtn = this.container.querySelector('#gantt-today');
        if (todayBtn) {
            todayBtn.addEventListener('click', () => this.scrollToToday());
        }

        // Export button
        const exportBtn = this.container.querySelector('#gantt-export');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportToPNG());
        }

        // Print button
        const printBtn = this.container.querySelector('#gantt-print');
        if (printBtn) {
            printBtn.addEventListener('click', () => this.print());
        }
    }

    /**
     * Charge les données du Gantt
     */
    loadTasks(tasks) {
        this.tasks = tasks.map(task => ({
            id: task.id,
            name: task.name,
            start: new Date(task.start_date),
            end: new Date(task.end_date),
            progress: task.progress || 0,
            dependencies: task.dependencies || [],
            status: task.status || 'planifie',
            client: task.client_name || '',
            budget: task.estimated_budget || 0
        }));

        this.render();
    }

    /**
     * Calcule les dimensions du graphique
     */
    calculateDimensions() {
        if (this.tasks.length === 0) return {};

        const allDates = [];
        this.tasks.forEach(task => {
            allDates.push(task.start, task.end);
        });

        const minDate = new Date(Math.min(...allDates));
        const maxDate = new Date(Math.max(...allDates));

        // Ajouter marge
        minDate.setDate(minDate.getDate() - 7);
        maxDate.setDate(maxDate.getDate() + 7);

        const timespan = maxDate - minDate;
        const width = this.container.clientWidth - this.options.paddingX * 2;
        const height = this.tasks.length * (this.options.barHeight + 20) + this.options.paddingY * 2;

        return { minDate, maxDate, timespan, width, height };
    }

    /**
     * Convertit une date en position X
     */
    dateToX(date, dims) {
        const elapsed = date - dims.minDate;
        const ratio = elapsed / dims.timespan;
        return this.options.paddingX + (ratio * dims.width);
    }

    /**
     * Génère les graduations de l'axe du temps
     */
    generateTimeScale(dims) {
        const ticks = [];
        const current = new Date(dims.minDate);

        while (current <= dims.maxDate) {
            ticks.push(new Date(current));

            switch (this.currentView) {
                case 'Day':
                    current.setDate(current.getDate() + 1);
                    break;
                case 'Week':
                    current.setDate(current.getDate() + 7);
                    break;
                case 'Month':
                    current.setMonth(current.getMonth() + 1);
                    break;
                case 'Year':
                    current.setFullYear(current.getFullYear() + 1);
                    break;
            }
        }

        return ticks;
    }

    /**
     * Formate une date pour affichage
     */
    formatDate(date) {
        const options = { day: '2-digit', month: 'short' };
        if (this.currentView === 'Year') {
            return date.getFullYear().toString();
        }
        return date.toLocaleDateString('fr-FR', options);
    }

    /**
     * Rend le diagramme de Gantt
     */
    render() {
        if (this.tasks.length === 0) {
            this.svg.innerHTML = '<text x="50" y="50" fill="#64748b">Aucun chantier à afficher</text>';
            return;
        }

        const dims = this.calculateDimensions();
        const ticks = this.generateTimeScale(dims);

        this.svg.setAttribute('width', dims.width + this.options.paddingX * 2);
        this.svg.setAttribute('height', dims.height);

        let svgContent = '';

        // Grille verticale (dates)
        ticks.forEach(tick => {
            const x = this.dateToX(tick, dims);
            svgContent += `
                <line x1="${x}" y1="${this.options.paddingY}"
                      x2="${x}" y2="${dims.height - this.options.paddingY}"
                      stroke="#e2e8f0" stroke-width="1"/>
                <text x="${x}" y="${this.options.paddingY - 10}"
                      fill="#64748b" font-size="12" text-anchor="middle">
                    ${this.formatDate(tick)}
                </text>
            `;
        });

        // Ligne aujourd'hui
        const today = new Date();
        if (today >= dims.minDate && today <= dims.maxDate) {
            const todayX = this.dateToX(today, dims);
            svgContent += `
                <line x1="${todayX}" y1="${this.options.paddingY}"
                      x2="${todayX}" y2="${dims.height - this.options.paddingY}"
                      stroke="#ef4444" stroke-width="2" stroke-dasharray="5,5"/>
            `;
        }

        // Barres de tâches
        this.tasks.forEach((task, index) => {
            const y = this.options.paddingY + (index * (this.options.barHeight + 20)) + 20;
            const startX = this.dateToX(task.start, dims);
            const endX = this.dateToX(task.end, dims);
            const width = endX - startX;

            // Couleur selon statut
            let color = '#2563eb'; // planifie
            if (task.status === 'en_cours') color = '#10b981';
            if (task.status === 'termine') color = '#64748b';
            if (task.status === 'annule') color = '#ef4444';

            // Vérifier si en retard
            const isOverdue = task.end < today && task.status !== 'termine';
            if (isOverdue) color = '#ef4444';

            // Nom de la tâche
            svgContent += `
                <text x="10" y="${y + this.options.barHeight / 2 + 5}"
                      fill="#1e293b" font-size="14" font-weight="500">
                    ${this.escapeHtml(task.name)}
                </text>
            `;

            // Barre de fond
            svgContent += `
                <rect x="${startX}" y="${y}"
                      width="${width}" height="${this.options.barHeight}"
                      fill="${color}" opacity="0.2"
                      rx="${this.options.barCornerRadius}"/>
            `;

            // Barre de progression
            const progressWidth = width * (task.progress / 100);
            svgContent += `
                <rect x="${startX}" y="${y}"
                      width="${progressWidth}" height="${this.options.barHeight}"
                      fill="${color}"
                      rx="${this.options.barCornerRadius}"
                      class="gantt-bar" data-task-id="${task.id}"/>
            `;

            // Pourcentage
            svgContent += `
                <text x="${startX + width / 2}" y="${y + this.options.barHeight / 2 + 5}"
                      fill="white" font-size="12" text-anchor="middle" font-weight="600">
                    ${task.progress}%
                </text>
            `;

            // Dates
            svgContent += `
                <text x="${startX}" y="${y + this.options.barHeight + 15}"
                      fill="#64748b" font-size="10">
                    ${this.formatDate(task.start)}
                </text>
                <text x="${endX}" y="${y + this.options.barHeight + 15}"
                      fill="#64748b" font-size="10" text-anchor="end">
                    ${this.formatDate(task.end)}
                </text>
            `;
        });

        // Dépendances (flèches)
        this.tasks.forEach((task, index) => {
            if (task.dependencies && task.dependencies.length > 0) {
                task.dependencies.forEach(depId => {
                    const depTask = this.tasks.find(t => t.id === depId);
                    if (depTask) {
                        const depIndex = this.tasks.indexOf(depTask);
                        const fromY = this.options.paddingY + (depIndex * (this.options.barHeight + 20)) + 20 + this.options.barHeight / 2;
                        const toY = this.options.paddingY + (index * (this.options.barHeight + 20)) + 20 + this.options.barHeight / 2;
                        const fromX = this.dateToX(depTask.end, dims);
                        const toX = this.dateToX(task.start, dims);

                        svgContent += `
                            <line x1="${fromX}" y1="${fromY}"
                                  x2="${toX}" y2="${toY}"
                                  stroke="#94a3b8" stroke-width="2"
                                  marker-end="url(#arrowhead)"/>
                        `;
                    }
                });
            }
        });

        // Marker pour les flèches
        svgContent = `
            <defs>
                <marker id="arrowhead" markerWidth="10" markerHeight="10"
                        refX="9" refY="3" orient="auto">
                    <polygon points="0 0, 10 3, 0 6" fill="#94a3b8"/>
                </marker>
            </defs>
        ` + svgContent;

        this.svg.innerHTML = svgContent;

        // Event listeners sur les barres
        const bars = this.svg.querySelectorAll('.gantt-bar');
        bars.forEach(bar => {
            bar.style.cursor = 'pointer';
            bar.addEventListener('click', (e) => {
                const taskId = parseInt(bar.dataset.taskId);
                if (this.options.onTaskClick) {
                    this.options.onTaskClick(taskId);
                }
            });
        });
    }

    /**
     * Change le mode de vue
     */
    changeView(mode) {
        this.currentView = mode;
        this.render();
    }

    /**
     * Scroll vers aujourd'hui
     */
    scrollToToday() {
        // Simple implémentation - à améliorer selon besoin
        this.render();
    }

    /**
     * Exporte le Gantt en PNG
     */
    exportToPNG() {
        const svgData = new XMLSerializer().serializeToString(this.svg);
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();

        img.onload = () => {
            canvas.width = this.svg.getAttribute('width');
            canvas.height = this.svg.getAttribute('height');
            ctx.drawImage(img, 0, 0);

            canvas.toBlob(blob => {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `gantt-${Date.now()}.png`;
                a.click();
            });
        };

        img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
    }

    /**
     * Imprime le Gantt
     */
    print() {
        window.print();
    }

    /**
     * Échappe le HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Export global
window.GanttPlanner = GanttPlanner;

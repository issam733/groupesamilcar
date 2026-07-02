@extends('admin.layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', 'Vue d\'ensemble — ' . date('Y'))

@section('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
@endsection

@section('extra-css')
<style>
    /* ─── KPI GRID ─── */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }

    .kpi-card {
        background: var(--card);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        padding: 22px 22px 20px;
        display: flex;
        align-items: center;
        gap: 18px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
        text-decoration: none;
        color: inherit;
        position: relative;
        overflow: hidden;
    }

    .kpi-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 4px; height: 100%;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow);
    }

    .kpi-card.blue::after   { background: var(--primary); }
    .kpi-card.green::after  { background: var(--success); }
    .kpi-card.orange::after { background: var(--warning); }
    .kpi-card.purple::after { background: #7c5cbf; }
    .kpi-card.teal::after   { background: #0d9488; }
    .kpi-card.red::after    { background: var(--danger); }

    .kpi-icon {
        width: 52px;
        height: 52px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .kpi-card.blue   .kpi-icon { background: #eef3ff; color: var(--primary); }
    .kpi-card.green  .kpi-icon { background: #ecfdf5; color: var(--success); }
    .kpi-card.orange .kpi-icon { background: #fffbeb; color: var(--warning); }
    .kpi-card.purple .kpi-icon { background: #f3eeff; color: #7c5cbf; }
    .kpi-card.teal   .kpi-icon { background: #ecfdfb; color: #0d9488; }
    .kpi-card.red    .kpi-icon { background: #fef2f2; color: var(--danger); }

    .kpi-data { flex: 1; min-width: 0; }

    .kpi-value {
        font-size: 28px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    .kpi-label {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 3px;
        font-weight: 500;
    }

    .kpi-trend {
        font-size: 11px;
        font-weight: 600;
        margin-top: 6px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 7px;
        border-radius: 20px;
    }

    .kpi-trend.up   { color: var(--success); background: #ecfdf5; }
    .kpi-trend.down { color: var(--danger);  background: #fef2f2; }
    .kpi-trend.flat { color: var(--text-muted); background: #f1f5f9; }

    /* ─── CHARTS ROW ─── */
    .charts-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 18px;
        margin-bottom: 28px;
    }

    @media (max-width: 1100px) {
        .charts-row { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 700px) {
        .charts-row { grid-template-columns: 1fr; }
        .kpi-grid   { grid-template-columns: 1fr 1fr; }
    }

    .chart-card {
        background: var(--card);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        padding: 22px;
        box-shadow: var(--shadow-sm);
    }

    .chart-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 4px;
    }

    .chart-sub {
        font-size: 11px;
        color: var(--text-muted);
        margin-bottom: 18px;
    }

    /* ─── BOTTOM ROW ─── */
    .bottom-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    @media (max-width: 800px) {
        .bottom-row { grid-template-columns: 1fr; }
    }

    /* Recent activity */
    .activity-list { list-style: none; }

    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }

    .activity-item:last-child { border-bottom: none; }

    .activity-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-top: 5px;
        flex-shrink: 0;
    }

    .activity-dot.blue   { background: var(--primary); }
    .activity-dot.green  { background: var(--success); }
    .activity-dot.orange { background: var(--warning); }
    .activity-dot.purple { background: #7c5cbf; }

    .activity-text {
        flex: 1;
        font-size: 13px;
        color: var(--text);
        line-height: 1.4;
    }

    .activity-text span { font-weight: 600; }

    .activity-time {
        font-size: 11px;
        color: var(--text-muted);
        white-space: nowrap;
    }

    /* Absences today */
    .absence-table { width: 100%; border-collapse: collapse; }

    .absence-table th {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 12px;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }

    .absence-table td {
        font-size: 13px;
        padding: 10px 12px;
        border-bottom: 1px solid #f0f4fa;
        color: var(--text);
    }

    .absence-table tr:last-child td { border-bottom: none; }

    .badge-niveau {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 20px;
        background: #eef3ff;
        color: var(--primary);
    }

    .badge-justifie {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 20px;
    }

    .badge-justifie.oui { background: #ecfdf5; color: var(--success); }
    .badge-justifie.non { background: #fef2f2; color: var(--danger); }

    /* Quick actions */
    .quick-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 28px;
    }

    .qa-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        color: var(--text);
        font-size: 13px;
        font-weight: 500;
    }

    .qa-btn:hover {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(26,79,160,0.2);
    }

    .qa-btn i {
        font-size: 15px;
        color: var(--primary);
        width: 20px;
        text-align: center;
    }

    .qa-btn:hover i { color: #fff; }
</style>
@endsection

@section('content')

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="{{ route('admin.eleves.create') }}" class="qa-btn">
            <i class="fa-solid fa-user-plus"></i> Ajouter un élève
        </a>
        <a href="{{ route('admin.examens.create') }}" class="qa-btn">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Générer un examen IA
        </a>
        <a href="{{ route('admin.attestations.create') }}" class="qa-btn">
            <i class="fa-solid fa-file-certificate"></i> Créer une attestation
        </a>
        <a href="{{ route('admin.annonces.create') }}" class="qa-btn">
            <i class="fa-solid fa-bullhorn"></i> Publier une annonce
        </a>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">

        <a href="{{ route('admin.eleves.index') }}" class="kpi-card blue">
            <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
            <div class="kpi-data">
                <div class="kpi-value">{{ $stats['eleves'] ?? 0 }}</div>
                <div class="kpi-label">Élèves inscrits</div>
                <div class="kpi-trend up"><i class="fa-solid fa-arrow-trend-up"></i> +12 ce mois</div>
            </div>
        </a>

        <a href="{{ route('admin.enseignants.index') }}" class="kpi-card green">
            <div class="kpi-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
            <div class="kpi-data">
                <div class="kpi-value">{{ $stats['enseignants'] ?? 0 }}</div>
                <div class="kpi-label">Enseignants</div>
                <div class="kpi-trend flat"><i class="fa-solid fa-minus"></i> Stable</div>
            </div>
        </a>

        <a href="{{ route('admin.parents.index') }}" class="kpi-card orange">
            <div class="kpi-icon"><i class="fa-solid fa-people-roof"></i></div>
            <div class="kpi-data">
                <div class="kpi-value">{{ $stats['parents'] ?? 0 }}</div>
                <div class="kpi-label">Parents</div>
                <div class="kpi-trend up"><i class="fa-solid fa-arrow-trend-up"></i> +8 ce mois</div>
            </div>
        </a>

        <a href="{{ route('admin.classes.index') }}" class="kpi-card purple">
            <div class="kpi-icon"><i class="fa-solid fa-door-open"></i></div>
            <div class="kpi-data">
                <div class="kpi-value">{{ $stats['classes'] ?? 0 }}</div>
                <div class="kpi-label">Classes actives</div>
                <div class="kpi-trend flat"><i class="fa-solid fa-minus"></i> Stable</div>
            </div>
        </a>

        <a href="{{ route('admin.examens.index') }}" class="kpi-card teal">
            <div class="kpi-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
            <div class="kpi-data">
                <div class="kpi-value">{{ $stats['examens'] ?? 0 }}</div>
                <div class="kpi-label">Examens générés</div>
                <div class="kpi-trend up"><i class="fa-solid fa-arrow-trend-up"></i> +24 ce mois</div>
            </div>
        </a>

        <div class="kpi-card red">
            <div class="kpi-icon"><i class="fa-solid fa-calendar-xmark"></i></div>
            <div class="kpi-data">
                <div class="kpi-value">{{ $stats['absences_jour'] ?? 0 }}</div>
                <div class="kpi-label">Absences aujourd'hui</div>
                <div class="kpi-trend down"><i class="fa-solid fa-arrow-trend-down"></i> -3 vs hier</div>
            </div>
        </div>

    </div>

    <!-- Charts -->
    <div class="charts-row">

        <div class="chart-card" style="grid-column: span 2;">
            <div class="chart-title">Évolution des inscriptions</div>
            <div class="chart-sub">12 derniers mois</div>
            <canvas id="inscriptionsChart" height="90"></canvas>
        </div>

        <div class="chart-card">
            <div class="chart-title">Répartition par niveau</div>
            <div class="chart-sub">Année scolaire {{ date('Y') }}</div>
            <canvas id="niveauChart" height="200"></canvas>
        </div>

    </div>

    <!-- Bottom row -->
    <div class="bottom-row">

        <!-- Recent activity -->
        <div class="chart-card">
            <div class="chart-title" style="margin-bottom:4px;">
                <i class="fa-solid fa-clock-rotate-left" style="color:var(--primary); margin-right:8px;"></i>
                Journal des actions récentes
            </div>
            <div class="chart-sub">Dernières activités sur la plateforme</div>

            <ul class="activity-list">
                @forelse($journal ?? [] as $log)
                <li class="activity-item">
                    <div class="activity-dot {{ $log['couleur'] ?? 'blue' }}"></div>
                    <div class="activity-text">
                        <span>{{ $log['user'] ?? 'Système' }}</span>
                        {{ $log['action'] ?? '' }}
                    </div>
                    <div class="activity-time">{{ $log['heure'] ?? '' }}</div>
                </li>
                @empty
                <li class="activity-item">
                    <div class="activity-dot blue"></div>
                    <div class="activity-text"><span>Admin</span> a créé la plateforme Amilcar</div>
                    <div class="activity-time">Aujourd'hui</div>
                </li>
                <li class="activity-item">
                    <div class="activity-dot green"></div>
                    <div class="activity-text"><span>Système</span> est opérationnel</div>
                    <div class="activity-time">Aujourd'hui</div>
                </li>
                @endforelse
            </ul>
        </div>

        <!-- Absences today -->
        <div class="chart-card">
            <div class="chart-title" style="margin-bottom:4px;">
                <i class="fa-solid fa-calendar-xmark" style="color:#d63031; margin-right:8px;"></i>
                Absences du jour
            </div>
            <div class="chart-sub">{{ now()->translatedFormat('l d F Y') }}</div>

            <table class="absence-table">
                <thead>
                    <tr>
                        <th>Élève</th>
                        <th>Classe</th>
                        <th>Justifié</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absences_jour ?? [] as $abs)
                    <tr>
                        <td>{{ $abs['nom'] ?? '' }} {{ $abs['prenom'] ?? '' }}</td>
                        <td><span class="badge-niveau">{{ $abs['classe'] ?? '' }}</span></td>
                        <td>
                            <span class="badge-justifie {{ $abs['justifie'] ? 'oui' : 'non' }}">
                                {{ $abs['justifie'] ? 'Oui' : 'Non' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center; color:var(--text-muted); padding:20px;">
                            <i class="fa-solid fa-circle-check" style="color:var(--success); margin-right:6px;"></i>
                            Aucune absence enregistrée aujourd'hui
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
    <div class="card mt-4">
        <div class="card-header-custom">
            <h5><i class="fa-solid fa-ranking-star me-2"></i>Classement des élèves par points</h5>
            <input type="text" id="searchClassement" class="form-control form-control-sm" style="max-width: 260px;"
                   placeholder="Rechercher un élève...">
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0" id="classementTable">
                <thead>
                <tr>
                    <th>Rang</th>
                    <th>Élève</th>
                    <th>Classe</th>
                    <th>Points</th>
                </tr>
                </thead>
                <tbody>
                @forelse($classementPoints as $index => $eleve)
                    <tr>
                        <td>
                            @if($index === 0)
                                <span class="badge bg-warning text-dark">1er</span>
                            @elseif($index === 1)
                                <span class="badge bg-secondary">2e</span>
                            @elseif($index === 2)
                                <span class="badge bg-info text-dark">3e</span>
                            @else
                                {{ $index + 1 }}e
                            @endif
                        </td>
                        <td>
                            <strong>{{ $eleve->prenom }} {{ $eleve->nom }}</strong><br>
                            <small class="text-muted">{{ $eleve->matricule }}</small>
                        </td>
                        <td>{{ $eleve->classe->nom ?? 'Aucune classe' }}</td>
                        <td>
                            <strong>{{ $eleve->total_points ?? 0 }}</strong> points
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Aucun point enregistré pour le moment.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Chart 1: Inscriptions
        const ctx1 = document.getElementById('inscriptionsChart')?.getContext('2d');

        if (ctx1) {
            const gradient = ctx1.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(26,79,160,0.15)');
            gradient.addColorStop(1, 'rgba(26,79,160,0)');

            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: ['Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                    datasets: [{
                        label: 'Inscriptions',
                        data: {!! json_encode($inscriptions_data ?? [210,230,290,305,308,310,315,320,325,330,338,342]) !!},
                        borderColor: '#1a4fa0',
                        backgroundColor: gradient,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#1a4fa0',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1a4fa0',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 10,
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: { color: '#f0f4fa' },
                            ticks: { font: { size: 11 }, color: '#6b7f99' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 }, color: '#6b7f99' }
                        }
                    }
                }
            });
        }

        // Chart 2: Niveaux
        const ctx2 = document.getElementById('niveauChart')?.getContext('2d');

        if (ctx2) {
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Préparatoire', 'Primaire', 'Collège', 'Lycée'],
                    datasets: [{
                        data: {!! json_encode($repartition_niveaux ?? [45, 148, 105, 44]) !!},
                        backgroundColor: ['#4a9de0', '#1a4fa0', '#7c5cbf', '#0d9488'],
                        borderWidth: 0,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 14,
                                font: { size: 11 },
                                usePointStyle: true,
                                pointStyleWidth: 8,
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1e2d42',
                            padding: 10,
                            cornerRadius: 8,
                        }
                    }
                }
            });
        }

        // Recherche classement points
        document.getElementById('searchClassement')?.addEventListener('keyup', function () {
            const search = this.value.toLowerCase();
            const rows = document.querySelectorAll('#classementTable tbody tr');

            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(search) ? '' : 'none';
            });
        });
    </script>
@endsection



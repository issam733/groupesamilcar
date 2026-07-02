@extends('admin.layouts.app')

@section('title', $eleve->prenom.' '.$eleve->nom)
@section('page-title', 'Dossier élève')
@section('page-subtitle', $eleve->matricule)

@section('extra-css')
<style>
    .profile-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 900px) { .profile-grid { grid-template-columns: 1fr; } }

    /* ─── Carte profil gauche ─── */
    .profile-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .profile-header {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-light));
        padding: 28px 20px 20px;
        text-align: center;
        position: relative;
    }
    .profile-avatar {
        width: 88px; height: 88px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid rgba(255,255,255,.3);
        margin-bottom: 12px;
    }
    .profile-avatar-initiales {
        width: 88px; height: 88px;
        border-radius: 50%;
        background: rgba(255,255,255,.2);
        border: 4px solid rgba(255,255,255,.3);
        display: flex; align-items: center; justify-content: center;
        font-size: 30px; font-weight: 800; color: #fff;
        margin: 0 auto 12px;
    }
    .profile-name {
        color: #fff; font-size: 18px; font-weight: 700; margin-bottom: 4px;
    }
    .profile-matricule {
        color: rgba(255,255,255,.7); font-size: 12px;
        background: rgba(255,255,255,.12);
        padding: 3px 10px; border-radius: 20px;
        display: inline-block;
    }
    .profile-body { padding: 20px; }
    .profile-info-row {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #f0f4fa;
        font-size: 13px;
    }
    .profile-info-row:last-child { border-bottom: none; }
    .profile-info-row i { width: 18px; text-align: center; color: var(--primary); font-size: 13px; }
    .profile-info-lbl { color: var(--text-muted); min-width: 90px; font-size: 12px; }
    .profile-info-val { color: var(--text); font-weight: 500; }
    .profile-actions {
        padding: 16px 20px;
        border-top: 1px solid var(--border);
        display: flex; flex-direction: column; gap: 8px;
    }
    .btn-profile {
        display: flex; align-items: center; gap: 9px;
        padding: 10px 14px; border-radius: 9px;
        font-size: 13px; font-weight: 600;
        text-decoration: none; cursor: pointer;
        transition: all .2s; border: none;
        font-family: 'Inter', sans-serif; width: 100%;
    }
    .btn-profile.primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: #fff;
    }
    .btn-profile.primary:hover { box-shadow: 0 4px 15px rgba(26,79,160,.3); }
    .btn-profile.outline {
        background: var(--bg); color: var(--text);
        border: 1.5px solid var(--border);
    }
    .btn-profile.outline:hover { border-color: var(--primary); color: var(--primary); background:#eef3ff; }
    .btn-profile.danger {
        background: #fff8f8; color: var(--danger);
        border: 1.5px solid #ffd0d0;
    }
    .btn-profile.danger:hover { background: var(--danger); color: #fff; }

    /* ─── Contenu droit ─── */
    .tabs-nav {
        display: flex; gap: 4px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 6px;
        margin-bottom: 16px;
        box-shadow: var(--shadow-sm);
    }
    .tab-btn {
        flex: 1; padding: 9px 12px;
        border: none; background: none;
        border-radius: 8px;
        font-size: 13px; font-weight: 600;
        color: var(--text-muted);
        cursor: pointer; transition: all .2s;
        font-family: 'Inter', sans-serif;
        display: flex; align-items: center; justify-content: center; gap: 7px;
    }
    .tab-btn:hover { color: var(--primary); background: #f0f4ff; }
    .tab-btn.active { background: var(--primary); color: #fff; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    /* Info card */
    .info-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 22px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 16px;
    }
    .info-card-title {
        font-size: 14px; font-weight: 700;
        color: var(--text); margin-bottom: 16px;
        display: flex; align-items: center; gap: 9px;
    }
    .info-card-title i { color: var(--primary); }

    /* Moyennes */
    .moyennes-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .moyenne-item {
        background: #f7f9fd; border-radius: 10px;
        padding: 16px; text-align: center;
    }
    .moyenne-val {
        font-size: 28px; font-weight: 800;
        margin-bottom: 4px;
    }
    .moyenne-val.bien    { color: var(--success); }
    .moyenne-val.moyen   { color: var(--warning); }
    .moyenne-val.faible  { color: var(--danger); }
    .moyenne-val.na      { color: var(--text-muted); font-size: 20px; }
    .moyenne-lbl { font-size: 11px; color: var(--text-muted); font-weight: 600; }

    /* Table notes */
    .notes-table { width: 100%; border-collapse: collapse; }
    .notes-table th {
        padding: 9px 12px; font-size: 11px; font-weight: 700;
        color: var(--text-muted); text-transform: uppercase;
        letter-spacing: .5px; background: #f7f9fd;
        border-bottom: 1px solid var(--border); text-align: left;
    }
    .notes-table td {
        padding: 10px 12px; font-size: 13px;
        border-bottom: 1px solid #f0f4fa; color: var(--text);
    }
    .notes-table tr:last-child td { border-bottom: none; }
    .note-badge {
        display: inline-block; font-weight: 700; font-size: 14px;
        padding: 3px 10px; border-radius: 20px;
    }
    .note-badge.bien   { background:#ecfdf5; color:var(--success); }
    .note-badge.moyen  { background:#fffbeb; color:var(--warning); }
    .note-badge.faible { background:#fef2f2; color:var(--danger); }

    /* Absences table */
    .absence-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 0; border-bottom: 1px solid #f0f4fa;
        font-size: 13px;
    }
    .absence-item:last-child { border-bottom: none; }
    .badge-justifie {
        font-size: 10px; font-weight: 700;
        padding: 3px 9px; border-radius: 20px;
    }
    .badge-justifie.oui { background:#ecfdf5; color:var(--success); }
    .badge-justifie.non { background:#fef2f2; color:var(--danger); }

    /* Stat absences */
    .abs-stats { display: flex; gap: 12px; margin-bottom: 16px; }
    .abs-stat {
        flex: 1; background: #f7f9fd; border-radius: 10px;
        padding: 12px; text-align: center;
    }
    .abs-stat-val { font-size: 24px; font-weight: 800; color: var(--text); }
    .abs-stat-lbl { font-size: 11px; color: var(--text-muted); }
</style>
@endsection

@section('content')

@if(session('success'))
<div style="background:#ecfdf5; border:1px solid #a7f3d0; border-left:4px solid var(--success);
            border-radius:10px; padding:12px 16px; margin-bottom:18px; font-size:13px;
            color:#065f46; display:flex; align-items:center; gap:10px;">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

<div class="profile-grid">

    <!-- ═══ COLONNE GAUCHE : Carte profil ═══ -->
    <div>
        <div class="profile-card">
            <!-- Header -->
            <div class="profile-header">
                @if($eleve->photo)
                    <img src="{{ asset('storage/'.$eleve->photo) }}" class="profile-avatar" alt="">
                @else
                    <div class="profile-avatar-initiales">
                        {{ strtoupper(substr($eleve->prenom,0,1).substr($eleve->nom,0,1)) }}
                    </div>
                @endif
                <div class="profile-name">{{ $eleve->prenom }} {{ $eleve->nom }}</div>
                <div class="profile-matricule">{{ $eleve->matricule }}</div>
            </div>

            <!-- Infos -->
            <div class="profile-body">
                <div class="profile-info-row">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span class="profile-info-lbl">Classe</span>
                    <span class="profile-info-val">{{ $eleve->classe?->nom ?? '—' }}</span>
                </div>
                <div class="profile-info-row">
                    <i class="fa-solid fa-layer-group"></i>
                    <span class="profile-info-lbl">Niveau</span>
                    <span class="profile-info-val">{{ $eleve->classe?->niveau ?? '—' }}</span>
                </div>
                <div class="profile-info-row">
                    <i class="fa-solid fa-calendar"></i>
                    <span class="profile-info-lbl">Naissance</span>
                    <span class="profile-info-val">
                        {{ $eleve->date_naissance?->format('d/m/Y') ?? '—' }}
                    </span>
                </div>
                <div class="profile-info-row">
                    <i class="fa-solid fa-{{ $eleve->sexe == 'M' ? 'mars' : 'venus' }}"></i>
                    <span class="profile-info-lbl">Sexe</span>
                    <span class="profile-info-val">
                        {{ $eleve->sexe == 'M' ? 'Masculin' : ($eleve->sexe == 'F' ? 'Féminin' : '—') }}
                    </span>
                </div>
                <div class="profile-info-row">
                    <i class="fa-solid fa-people-roof"></i>
                    <span class="profile-info-lbl">Parent</span>
                    <span class="profile-info-val">
                        {{ $eleve->parent ? $eleve->parent->prenom.' '.$eleve->parent->nom : '—' }}
                    </span>
                </div>
                <div class="profile-info-row">
                    <i class="fa-solid fa-phone"></i>
                    <span class="profile-info-lbl">Téléphone</span>
                    <span class="profile-info-val">{{ $eleve->telephone ?? '—' }}</span>
                </div>
                <div class="profile-info-row">
                    <i class="fa-solid fa-envelope"></i>
                    <span class="profile-info-lbl">Email</span>
                    <span class="profile-info-val" style="word-break:break-all;">
                        {{ $eleve->email ?? '—' }}
                    </span>
                </div>
                <div class="profile-info-row">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span class="profile-info-lbl">Inscrit le</span>
                    <span class="profile-info-val">{{ $eleve->created_at?->format('d/m/Y') ?? '—' }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="profile-actions">
                <a href="{{ route('admin.eleves.edit', $eleve) }}" class="btn-profile primary">
                    <i class="fa-solid fa-pen"></i> Modifier la fiche
                </a>
                <a href="{{ route('admin.attestations.create') }}?eleve_id={{ $eleve->id }}"
                   class="btn-profile outline">
                    <i class="fa-solid fa-file-certificate"></i> Créer attestation
                </a>
                <a href="{{ route('admin.eleves.fiche', $eleve) }}" class="btn-profile outline" target="_blank">
                    <i class="fa-solid fa-print"></i> Imprimer la fiche
                </a>
                <form method="POST" action="{{ route('admin.eleves.destroy', $eleve) }}"
                      onsubmit="return confirm('Désactiver cet élève ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-profile danger">
                        <i class="fa-solid fa-ban"></i> Désactiver
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ═══ COLONNE DROITE : Onglets ═══ -->
    <div>
        <!-- Tabs -->
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="switchTab(this, 'notes')">
                <i class="fa-solid fa-star-half-stroke"></i> Notes
            </button>
            <button class="tab-btn" onclick="switchTab(this, 'absences')">
                <i class="fa-solid fa-calendar-xmark"></i> Absences
                @if($totalAbsences > 0)
                <span style="background:#e74c3c; color:#fff; font-size:10px;
                             padding:1px 6px; border-radius:20px;">{{ $totalAbsences }}</span>
                @endif
            </button>
            <button class="tab-btn" onclick="switchTab(this, 'bulletins')">
                <i class="fa-solid fa-file-lines"></i> Bulletins
            </button>
            <button class="tab-btn" onclick="switchTab(this, 'attestations')">
                <i class="fa-solid fa-file-certificate"></i> Attestations
            </button>
        </div>

        <!-- ── Onglet Notes ── -->
        <div id="tab-notes" class="tab-content active">
            <div class="info-card">
                <div class="info-card-title">
                    <i class="fa-solid fa-chart-bar"></i> Moyennes par trimestre
                </div>
                <div class="moyennes-grid">
                    @foreach([1,2,3] as $trim)
                    <div class="moyenne-item">
                        @php
                            $moy   = $moyennes[$trim] ?? null;
                            $cls   = $moy === null ? 'na' : ($moy >= 14 ? 'bien' : ($moy >= 10 ? 'moyen' : 'faible'));
                        @endphp
                        <div class="moyenne-val {{ $cls }}">
                            {{ $moy !== null ? number_format($moy,2).'/' : '—' }}
                            {{ $moy !== null ? '20' : '' }}
                        </div>
                        <div class="moyenne-lbl">Trimestre {{ $trim }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-title">
                    <i class="fa-solid fa-list"></i> Détail des notes
                </div>
                @if($eleve->notes->count())
                <table class="notes-table">
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Type</th>
                            <th>Trimestre</th>
                            <th>Note</th>
                            <th>Commentaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eleve->notes->sortByDesc('created_at') as $note)
                        <tr>
                            <td>{{ $note->matiere?->nom ?? '—' }}</td>
                            <td style="text-transform:capitalize;">{{ $note->type }}</td>
                            <td>T{{ $note->trimestre }}</td>
                            <td>
                                @php
                                    $nc = $note->valeur >= 14 ? 'bien' : ($note->valeur >= 10 ? 'moyen' : 'faible');
                                @endphp
                                <span class="note-badge {{ $nc }}">
                                    {{ number_format($note->valeur, 2) }}/20
                                </span>
                            </td>
                            <td style="color:var(--text-muted); font-size:12px;">
                                {{ $note->commentaire ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div style="text-align:center; padding:30px; color:var(--text-muted);">
                    <i class="fa-solid fa-inbox" style="font-size:32px; opacity:.3; margin-bottom:10px; display:block;"></i>
                    Aucune note enregistrée pour cet élève.
                </div>
                @endif
            </div>
        </div>

        <!-- ── Onglet Absences ── -->
        <div id="tab-absences" class="tab-content">
            <div class="info-card">
                <div class="info-card-title">
                    <i class="fa-solid fa-chart-pie"></i> Bilan des absences
                </div>
                <div class="abs-stats">
                    <div class="abs-stat">
                        <div class="abs-stat-val">{{ $totalAbsences }}</div>
                        <div class="abs-stat-lbl">Total absences</div>
                    </div>
                    <div class="abs-stat">
                        <div class="abs-stat-val" style="color:var(--danger);">{{ $absNonJustifie }}</div>
                        <div class="abs-stat-lbl">Non justifiées</div>
                    </div>
                    <div class="abs-stat">
                        <div class="abs-stat-val" style="color:var(--success);">{{ $totalAbsences - $absNonJustifie }}</div>
                        <div class="abs-stat-lbl">Justifiées</div>
                    </div>
                </div>
                @if($eleve->absences->count())
                    @foreach($eleve->absences->sortByDesc('date') as $abs)
                    <div class="absence-item">
                        <i class="fa-solid fa-calendar" style="color:var(--text-muted); width:16px;"></i>
                        <span style="font-weight:500;">{{ $abs->date?->format('d/m/Y') ?? '—' }}</span>
                        <span class="badge-justifie {{ $abs->justifie ? 'oui' : 'non' }}">
                            {{ $abs->justifie ? 'Justifiée' : 'Non justifiée' }}
                        </span>
                        @if($abs->motif)
                        <span style="color:var(--text-muted); font-size:12px;">— {{ $abs->motif }}</span>
                        @endif
                    </div>
                    @endforeach
                @else
                <div style="text-align:center; padding:24px; color:var(--text-muted);">
                    <i class="fa-solid fa-circle-check" style="font-size:28px; color:var(--success); opacity:.6; display:block; margin-bottom:8px;"></i>
                    Aucune absence enregistrée.
                </div>
                @endif
            </div>
        </div>

        <!-- ── Onglet Bulletins ── -->
        <div id="tab-bulletins" class="tab-content">
            <div class="info-card">
                <div class="info-card-title">
                    <i class="fa-solid fa-file-lines"></i> Bulletins de notes
                </div>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    @foreach([1,2,3] as $trim)
                    <div style="display:flex; align-items:center; gap:14px; padding:14px 16px;
                                background:#f7f9fd; border-radius:10px;">
                        <div style="width:42px; height:42px; background:var(--primary); border-radius:10px;
                                    display:flex; align-items:center; justify-content:center; color:#fff; font-size:16px;">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="font-weight:600; font-size:14px;">Bulletin Trimestre {{ $trim }}</div>
                            <div style="font-size:12px; color:var(--text-muted);">
                                Année scolaire {{ $eleve->annee_scolaire }}
                            </div>
                        </div>
                        <a href="{{ route('admin.bulletins.pdf', [$eleve, $trim]) }}"
                           class="btn-profile outline"
                           style="width:auto; padding:8px 16px; font-size:12px;"
                           target="_blank">
                            <i class="fa-solid fa-download"></i> PDF
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ── Onglet Attestations ── -->
        <div id="tab-attestations" class="tab-content">
            <div class="info-card">
                <div class="info-card-title">
                    <i class="fa-solid fa-file-certificate"></i> Attestations générées
                </div>

                <div style="display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap;">
                    <a href="{{ route('admin.attestations.create') }}?eleve_id={{ $eleve->id }}&type=inscription"
                       class="btn-profile primary" style="width:auto; font-size:12px; padding:9px 16px;">
                        <i class="fa-solid fa-plus"></i> Attestation d'inscription
                    </a>
                    <a href="{{ route('admin.attestations.create') }}?eleve_id={{ $eleve->id }}&type=presence"
                       class="btn-profile outline" style="width:auto; font-size:12px; padding:9px 16px;">
                        <i class="fa-solid fa-plus"></i> Attestation de présence
                    </a>
                </div>

                @if($eleve->attestations ?? false && $eleve->attestations->count())
                    @foreach($eleve->attestations as $att)
                    <div style="display:flex; align-items:center; gap:12px; padding:10px 0;
                                border-bottom:1px solid #f0f4fa;">
                        <i class="fa-solid fa-file-certificate" style="color:var(--primary);"></i>
                        <div style="flex:1;">
                            <span style="font-weight:600; font-size:13px; text-transform:capitalize;">
                                {{ $att->type }}
                            </span>
                            <span style="font-size:11px; color:var(--text-muted); margin-left:8px;">
                                {{ $att->numero_unique }}
                            </span>
                        </div>
                        <span style="font-size:11px; color:var(--text-muted);">
                            {{ $att->created_at?->format('d/m/Y') ?? '—' }}
                        </span>
                        <a href="{{ route('admin.attestations.pdf', $att) }}"
                           style="font-size:12px; color:var(--primary);" target="_blank">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </div>
                    @endforeach
                @else
                <div style="text-align:center; padding:24px; color:var(--text-muted);">
                    <i class="fa-solid fa-file-circle-plus" style="font-size:28px; opacity:.3; display:block; margin-bottom:8px;"></i>
                    Aucune attestation générée.
                </div>
                @endif
            </div>
        </div>

    </div><!-- fin colonne droite -->
</div><!-- fin profile-grid -->

@endsection

@section('scripts')
<script>
    function switchTab(btn, tabId) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }
</script>
@endsection

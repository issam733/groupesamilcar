@extends('admin.layouts.app')

@section('title', 'Bibliothèque numérique')
@section('page-title', 'Bibliothèque numérique')
@section('page-subtitle', 'Ressources pédagogiques organisées par niveau et matière')

@section('extra-css')
<style>
    .stats-bar { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
    .stat-mini { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:14px; box-shadow:var(--shadow-sm); }
    .stat-mini-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
    .stat-mini-icon.blue   { background:#eef3ff; color:var(--primary); }
    .stat-mini-icon.red    { background:#fef2f2; color:var(--danger); }
    .stat-mini-icon.purple { background:#f3eeff; color:#7c5cbf; }
    .stat-mini-icon.green  { background:#ecfdf5; color:var(--success); }
    .stat-mini-val { font-size:24px; font-weight:800; color:var(--text); line-height:1; }
    .stat-mini-lbl { font-size:11px; color:var(--text-muted); margin-top:3px; }

    .toolbar { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px 20px; margin-bottom:18px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; box-shadow:var(--shadow-sm); }
    .toolbar-search { position:relative; flex:1; min-width:200px; }
    .toolbar-search input { width:100%; padding:9px 14px 9px 38px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; }
    .toolbar-search input:focus { border-color:var(--primary); background:#fff; }
    .toolbar-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:13px; }
    .toolbar-select { padding:9px 12px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; font-family:'Inter',sans-serif; color:var(--text); background:var(--bg); outline:none; cursor:pointer; }

    .btn-am { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:9px; font-size:13px; font-weight:600; text-decoration:none; transition:all .2s; border:none; cursor:pointer; font-family:'Inter',sans-serif; }
    .btn-primary-am { background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; }
    .btn-primary-am:hover { box-shadow:0 6px 20px rgba(26,79,160,.3); transform:translateY(-1px); color:#fff; }

    /* Tree view */
    .niveau-group { margin-bottom:22px; }
    .niveau-header { display:flex; align-items:center; gap:10px; padding:12px 16px; background:#f7f9fd; border-radius:10px; cursor:pointer; margin-bottom:10px; user-select:none; }
    .niveau-header:hover { background:#eef3ff; }
    .niveau-header i.chevron { transition:transform .2s; color:var(--text-muted); font-size:12px; }
    .niveau-header.collapsed i.chevron { transform:rotate(-90deg); }
    .niveau-title { font-size:13.5px; font-weight:700; color:var(--text); flex:1; }
    .niveau-count { font-size:11px; color:var(--text-muted); background:var(--bg); padding:2px 10px; border-radius:20px; }

    .niveau-body { padding-left:14px; }
    .niveau-body.collapsed { display:none; }

    .matiere-group { margin-bottom:16px; }
    .matiere-title { font-size:12px; font-weight:700; color:var(--primary); margin-bottom:8px; display:flex; align-items:center; gap:6px; }

    .ressources-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:10px; }
    .ressource-card { background:var(--card); border:1px solid var(--border); border-radius:10px; padding:12px 14px; display:flex; align-items:center; gap:10px; transition:all .2s; }
    .ressource-card:hover { border-color:var(--primary); box-shadow:var(--shadow-sm); }
    .ressource-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
    .ressource-icon.pdf   { background:#fef2f2; color:var(--danger); }
    .ressource-icon.video { background:#f3eeff; color:#7c5cbf; }
    .ressource-icon.lien  { background:#eef3ff; color:var(--primary); }
    .ressource-icon.autre { background:#fffbeb; color:var(--warning); }
    .ressource-info { flex:1; min-width:0; }
    .ressource-titre { font-size:12.5px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .ressource-meta { font-size:10.5px; color:var(--text-muted); margin-top:2px; }
    .ressource-actions { display:flex; gap:5px; }
    .btn-icon-sm { width:26px; height:26px; border-radius:6px; border:1px solid var(--border); background:var(--bg); display:flex; align-items:center; justify-content:center; font-size:11px; color:var(--text-muted); cursor:pointer; text-decoration:none; transition:all .15s; }
    .btn-icon-sm:hover.open { background:#eef3ff; color:var(--primary); border-color:var(--primary); }
    .btn-icon-sm:hover.del { background:#fef2f2; color:var(--danger); border-color:var(--danger); }

    .empty-state { text-align:center; padding:60px 20px; color:var(--text-muted); }
    .empty-state i { font-size:48px; opacity:.3; display:block; margin-bottom:16px; }

    /* Modal upload */
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; padding:20px; }
    .modal-overlay.show { display:flex; }
    .modal-box { background:#fff; border-radius:16px; max-width:480px; width:100%; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.2); }
    .modal-header { padding:18px 24px; background:#f7f9fd; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
    .modal-header h4 { font-size:15px; font-weight:700; color:var(--text); margin:0; }
    .modal-close { cursor:pointer; color:var(--text-muted); font-size:16px; }
    .modal-body { padding:24px; }
    .modal-field { margin-bottom:16px; }
    .modal-field label { display:block; font-size:12px; font-weight:600; color:var(--text); margin-bottom:7px; }
    .modal-field input, .modal-field select { width:100%; padding:10px 14px; border:1.5px solid var(--border); border-radius:9px; font-size:13.5px; font-family:'Inter',sans-serif; color:var(--text); background:#fafbff; outline:none; }
    .modal-field input:focus, .modal-field select:focus { border-color:var(--primary); background:#fff; }
    .type-selector { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; }
    .type-btn { padding:10px 6px; border-radius:9px; border:1.5px solid var(--border); background:#fafbff; text-align:center; cursor:pointer; transition:all .2s; }
    .type-btn i { display:block; font-size:16px; margin-bottom:4px; color:var(--text-muted); }
    .type-btn span { font-size:10px; font-weight:600; color:var(--text-muted); }
    .type-btn.active { border-color:var(--primary); background:#eef3ff; }
    .type-btn.active i, .type-btn.active span { color:var(--primary); }
    .modal-footer { padding:16px 24px; border-top:1px solid var(--border); background:#f7f9fd; display:flex; justify-content:flex-end; gap:10px; }
</style>
@endsection

@section('content')

@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-left:4px solid var(--success);border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:13px;color:#065f46;">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-mini"><div class="stat-mini-icon blue"><i class="fa-solid fa-book-open"></i></div><div><div class="stat-mini-val">{{ $stats['total'] }}</div><div class="stat-mini-lbl">Total ressources</div></div></div>
    <div class="stat-mini"><div class="stat-mini-icon red"><i class="fa-solid fa-file-pdf"></i></div><div><div class="stat-mini-val">{{ $stats['pdf'] }}</div><div class="stat-mini-lbl">Fichiers PDF</div></div></div>
    <div class="stat-mini"><div class="stat-mini-icon purple"><i class="fa-solid fa-video"></i></div><div><div class="stat-mini-val">{{ $stats['video'] }}</div><div class="stat-mini-lbl">Vidéos</div></div></div>
    <div class="stat-mini"><div class="stat-mini-icon green"><i class="fa-solid fa-link"></i></div><div><div class="stat-mini-val">{{ $stats['lien'] }}</div><div class="stat-mini-lbl">Liens externes</div></div></div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <form method="GET" action="{{ route('admin.bibliotheque.index') }}" style="display:contents;" id="filterForm">
        <div class="toolbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" placeholder="Rechercher une ressource…" value="{{ request('search') }}" oninput="debounceSubmit()">
        </div>
        <select class="toolbar-select" name="niveau" onchange="this.form.submit()">
            <option value="">Tous les niveaux</option>
            @foreach(['Préparatoire','Primaire','Collège','Lycée'] as $n)
            <option value="{{ $n }}" {{ request('niveau')==$n?'selected':'' }}>{{ $n }}</option>
            @endforeach
        </select>
        <select class="toolbar-select" name="type" onchange="this.form.submit()">
            <option value="">Tous les types</option>
            <option value="pdf" {{ request('type')=='pdf'?'selected':'' }}>PDF</option>
            <option value="video" {{ request('type')=='video'?'selected':'' }}>Vidéo</option>
            <option value="lien" {{ request('type')=='lien'?'selected':'' }}>Lien</option>
        </select>
    </form>
    <div style="margin-left:auto;">
        <button type="button" class="btn-am btn-primary-am" onclick="openUploadModal()">
            <i class="fa-solid fa-plus"></i> Ajouter une ressource
        </button>
    </div>
</div>

<!-- Tree -->
@forelse($arbre as $niveau => $matieres)
<div class="niveau-group">
    <div class="niveau-header" onclick="toggleNiveau(this)">
        <i class="fa-solid fa-chevron-down chevron"></i>
        <div class="niveau-title">{{ $niveau }}</div>
        <span class="niveau-count">{{ collect($matieres)->flatten()->count() }} ressource(s)</span>
    </div>
    <div class="niveau-body">
        @foreach($matieres as $matiere => $ressources)
        <div class="matiere-group">
            <div class="matiere-title"><i class="fa-solid fa-bookmark"></i> {{ $matiere }}</div>
            <div class="ressources-grid">
                @foreach($ressources as $r)
                @php
                    $icons = ['pdf'=>'fa-file-pdf','video'=>'fa-video','lien'=>'fa-link','autre'=>'fa-file'];
                @endphp
                <div class="ressource-card">
                    <div class="ressource-icon {{ $r->type }}"><i class="fa-solid {{ $icons[$r->type] ?? 'fa-file' }}"></i></div>
                    <div class="ressource-info">
                        <div class="ressource-titre" title="{{ $r->titre }}">{{ $r->titre }}</div>
                        <div class="ressource-meta">{{ $r->enseignant->prenom ?? '' }} {{ $r->enseignant->nom ?? '' }}</div>
                    </div>
                    <div class="ressource-actions">
                        @if($r->fichier)
                        <a href="{{ asset('storage/'.$r->fichier) }}" target="_blank" class="btn-icon-sm open"><i class="fa-solid fa-up-right-from-square"></i></a>
                        @elseif($r->lien_externe)
                        <a href="{{ $r->lien_externe }}" target="_blank" class="btn-icon-sm open"><i class="fa-solid fa-up-right-from-square"></i></a>
                        @endif
                        <form method="POST" action="{{ route('admin.bibliotheque.destroy', $r) }}" onsubmit="return confirm('Supprimer cette ressource ?')" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-icon-sm del"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@empty
<div class="empty-state">
    <i class="fa-solid fa-book-open"></i>
    <h4 style="color:var(--text);margin-bottom:8px;">La bibliothèque est vide</h4>
    <p>Commencez par ajouter des ressources pédagogiques.</p>
</div>
@endforelse

<!-- Modal upload -->
<div class="modal-overlay" id="uploadModal">
    <div class="modal-box">
        <div class="modal-header">
            <h4>Ajouter une ressource</h4>
            <i class="fa-solid fa-xmark modal-close" onclick="closeUploadModal()"></i>
        </div>
        <form method="POST" action="{{ route('admin.bibliotheque.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="modal-field">
                    <label>Type de ressource</label>
                    <div class="type-selector">
                        <div class="type-btn active" data-type="pdf" onclick="selectType(this)"><i class="fa-solid fa-file-pdf"></i><span>PDF</span></div>
                        <div class="type-btn" data-type="video" onclick="selectType(this)"><i class="fa-solid fa-video"></i><span>Vidéo</span></div>
                        <div class="type-btn" data-type="lien" onclick="selectType(this)"><i class="fa-solid fa-link"></i><span>Lien</span></div>
                        <div class="type-btn" data-type="autre" onclick="selectType(this)"><i class="fa-solid fa-file"></i><span>Autre</span></div>
                    </div>
                    <input type="hidden" name="type" id="typeInput" value="pdf">
                </div>

                <div class="modal-field">
                    <label>Titre <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="titre" placeholder="Chapitre 3 — Les fractions" required>
                </div>

                <div class="modal-field">
                    <label>Classe</label>
                    <select name="classe_id" id="classeUploadSelect" onchange="loadMatieresUpload()">
                        <option value="">— Aucune classe spécifique —</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->nom }} ({{ $c->niveau }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-field">
                    <label>Matière</label>
                    <select name="matiere_id" id="matiereUploadSelect">
                        <option value="">— Aucune —</option>
                    </select>
                </div>

                <div class="modal-field" id="fichierField">
                    <label>Fichier</label>
                    <input type="file" name="fichier">
                </div>

                <div class="modal-field" id="lienField" style="display:none;">
                    <label>Lien (URL)</label>
                    <input type="url" name="lien_externe" placeholder="https://...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-am" style="background:var(--bg);color:var(--text);border:1.5px solid var(--border);" onclick="closeUploadModal()">Annuler</button>
                <button type="submit" class="btn-am btn-primary-am"><i class="fa-solid fa-upload"></i> Ajouter</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
let st;
function debounceSubmit() { clearTimeout(st); st = setTimeout(()=>document.getElementById('filterForm').submit(),500); }

function toggleNiveau(el) {
    el.classList.toggle('collapsed');
    el.nextElementSibling.classList.toggle('collapsed');
}

function openUploadModal() { document.getElementById('uploadModal').classList.add('show'); }
function closeUploadModal() { document.getElementById('uploadModal').classList.remove('show'); }
document.getElementById('uploadModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeUploadModal(); });

function selectType(el) {
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    const type = el.dataset.type;
    document.getElementById('typeInput').value = type;
    document.getElementById('fichierField').style.display = (type === 'pdf' || type === 'autre') ? 'block' : 'none';
    document.getElementById('lienField').style.display = (type === 'video' || type === 'lien') ? 'block' : 'none';
}

@php $classesMatieresData = $classes->mapWithKeys(fn($c) => [$c->id => $c->matieres->map(fn($m) => ['id'=>$m->id,'nom'=>$m->nom])]); @endphp
const classesMatieres = @json($classesMatieresData);

function loadMatieresUpload() {
    const classeId = document.getElementById('classeUploadSelect').value;
    const sel = document.getElementById('matiereUploadSelect');
    sel.innerHTML = '<option value="">— Aucune —</option>';
    if (classeId && classesMatieres[classeId]) {
        classesMatieres[classeId].forEach(m => sel.innerHTML += `<option value="${m.id}">${m.nom}</option>`);
    }
}
</script>
@endsection

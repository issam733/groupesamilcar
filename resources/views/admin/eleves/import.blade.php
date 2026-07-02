@extends('admin.layouts.app')

@section('title', 'Importer des élèves')
@section('page-title', 'Import Excel / CSV')
@section('page-subtitle', 'Ajouter plusieurs élèves en une seule fois')

@section('extra-css')
<style>
    .import-wrapper { max-width: 760px; }
    .import-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 20px;
    }
    .import-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 12px;
    }
    .import-card-header i { font-size: 20px; color: var(--primary); }
    .import-card-header h3 { font-size: 15px; font-weight: 700; margin: 0; }
    .import-card-body { padding: 24px; }

    /* Drop zone */
    .drop-zone {
        border: 2.5px dashed var(--border);
        border-radius: 14px;
        padding: 48px 24px;
        text-align: center;
        cursor: pointer;
        transition: all .2s;
        position: relative;
        background: #fafbff;
    }
    .drop-zone:hover, .drop-zone.dragover {
        border-color: var(--primary);
        background: #f0f4ff;
    }
    .drop-zone input[type="file"] {
        position: absolute; inset: 0;
        opacity: 0; cursor: pointer;
        width: 100%; height: 100%;
    }
    .drop-icon {
        font-size: 48px; color: var(--primary);
        opacity: .35; margin-bottom: 16px;
    }
    .drop-title { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
    .drop-sub   { font-size: 13px; color: var(--text-muted); }
    .drop-formats {
        display: flex; gap: 8px; justify-content: center; margin-top: 16px; flex-wrap: wrap;
    }
    .format-badge {
        padding: 4px 12px; border-radius: 20px;
        font-size: 11px; font-weight: 700;
    }
    .format-badge.xlsx { background: #ecfdf5; color: var(--success); }
    .format-badge.xls  { background: #fffbeb; color: var(--warning); }
    .format-badge.csv  { background: #eef3ff; color: var(--primary); }

    /* File preview */
    .file-selected {
        display: none;
        align-items: center; gap: 14px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 10px;
        padding: 14px 18px;
        margin-top: 14px;
    }
    .file-selected i { font-size: 24px; color: var(--success); }
    .file-name { font-weight: 600; font-size: 14px; }
    .file-size { font-size: 12px; color: var(--text-muted); }

    /* Instructions table */
    .col-table { width: 100%; border-collapse: collapse; }
    .col-table th {
        padding: 9px 12px; font-size: 11px; font-weight: 700;
        color: var(--text-muted); text-transform: uppercase;
        letter-spacing: .5px; background: #f7f9fd;
        border-bottom: 1px solid var(--border); text-align: left;
    }
    .col-table td {
        padding: 10px 12px; font-size: 13px;
        border-bottom: 1px solid #f0f4fa; color: var(--text);
    }
    .col-table tr:last-child td { border-bottom: none; }
    .col-required {
        display: inline-block; font-size: 10px; font-weight: 700;
        padding: 2px 8px; border-radius: 20px;
    }
    .col-required.oui { background: #fef2f2; color: var(--danger); }
    .col-required.non { background: #f7f9fd; color: var(--text-muted); }

    code { background: #f0f4fa; padding: 2px 6px; border-radius: 4px; font-size: 12px; }

    /* Buttons */
    .btn-am {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 11px 22px; border-radius: 9px;
        font-size: 13.5px; font-weight: 600;
        font-family: 'Inter', sans-serif;
        cursor: pointer; border: none; transition: all .2s; text-decoration: none;
    }
    .btn-primary-am { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; }
    .btn-primary-am:hover { box-shadow: 0 6px 20px rgba(26,79,160,.3); transform:translateY(-1px); color:#fff; }
    .btn-light-am { background: var(--bg); color:var(--text); border: 1.5px solid var(--border); }
    .btn-light-am:hover { border-color:var(--primary); color:var(--primary); }
</style>
@endsection

@section('content')

<div class="import-wrapper">

    @if(session('success'))
    <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-left:4px solid var(--success);
                border-radius:10px; padding:12px 16px; margin-bottom:18px; font-size:13px;
                color:#065f46; display:flex; align-items:center; gap:10px;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('import_errors') && count(session('import_errors')))
    <div style="background:#fffbeb; border:1px solid #fde68a; border-left:4px solid var(--warning);
                border-radius:10px; padding:12px 16px; margin-bottom:18px; font-size:13px; color:#92400e;">
        <strong><i class="fa-solid fa-triangle-exclamation"></i> Lignes ignorées :</strong>
        <ul style="margin:8px 0 0 16px;">
            @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Upload form -->
    <div class="import-card">
        <div class="import-card-header">
            <i class="fa-solid fa-file-import"></i>
            <h3>Importer un fichier</h3>
        </div>
        <div class="import-card-body">
            <form method="POST" action="{{ route('admin.eleves.import.post') }}"
                  enctype="multipart/form-data" id="importForm">
                @csrf

                @error('fichier')
                <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:8px;
                            padding:10px 14px; margin-bottom:16px; font-size:13px; color:var(--danger);">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                </div>
                @enderror

                <div class="drop-zone" id="dropZone">
                    <input type="file" name="fichier" id="fileInput"
                           accept=".xlsx,.csv,.txt"
                           onchange="onFileSelected(this)">
                    <div class="drop-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                    <div class="drop-title">Glissez votre fichier ici</div>
                    <div class="drop-sub">ou cliquez pour parcourir</div>
                    <div class="drop-formats">
                        <span class="format-badge xlsx">.XLSX (recommandé)</span>
                        <span class="format-badge csv">.CSV</span>
                    </div>
                </div>

                <div class="file-selected" id="fileSelected">
                    <i class="fa-solid fa-file-excel"></i>
                    <div>
                        <div class="file-name" id="fileName">—</div>
                        <div class="file-size" id="fileSize">—</div>
                    </div>
                    <button type="button" onclick="clearFile()"
                            style="margin-left:auto; background:none; border:none; cursor:pointer;
                                   color:var(--danger); font-size:16px;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div style="display:flex; gap:12px; margin-top:20px;">
                    <button type="submit" class="btn-am btn-primary-am" id="submitBtn" disabled>
                        <i class="fa-solid fa-upload"></i> Lancer l'import
                    </button>
                    <a href="{{ route('admin.eleves.index') }}" class="btn-am btn-light-am">
                        <i class="fa-solid fa-xmark"></i> Annuler
                    </a>
                    <a href="{{ route('admin.eleves.import.modele') }}"
                       class="btn-am btn-light-am" style="margin-left:auto;">
                        <i class="fa-solid fa-file-excel"></i> Télécharger le modèle Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Instructions -->
    <div class="import-card">
        <div class="import-card-header">
            <i class="fa-solid fa-circle-info"></i>
            <h3>Format du fichier</h3>
        </div>
        <div class="import-card-body">
            <p style="font-size:13px; color:var(--text-muted); margin-bottom:16px;">
                Le plus simple : <strong>téléchargez le modèle Excel</strong> ci-dessus, remplissez-le
                à partir de la ligne 2 (sans supprimer les en-têtes), puis importez-le.
                Les fichiers <code>.csv</code> sont aussi acceptés (séparateur <code>,</code> ou <code>;</code>
                détecté automatiquement). Colonnes attendues :
            </p>
            <table class="col-table">
                <thead>
                    <tr>
                        <th>Colonne</th>
                        <th>Description</th>
                        <th>Obligatoire</th>
                        <th>Exemple</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>matricule</code></td>
                        <td>Matricule officiel de l'élève (texte, unique)</td>
                        <td><span class="col-required oui">Oui</span></td>
                        <td>123456789</td>
                    </tr>
                    <tr>
                        <td><code>nom</code></td>
                        <td>Nom de famille</td>
                        <td><span class="col-required oui">Oui</span></td>
                        <td>BEN ALI</td>
                    </tr>
                    <tr>
                        <td><code>prenom</code></td>
                        <td>Prénom</td>
                        <td><span class="col-required oui">Oui</span></td>
                        <td>Mohamed</td>
                    </tr>
                    <tr>
                        <td><code>date_naissance</code></td>
                        <td>Date de naissance (JJ/MM/AAAA)</td>
                        <td><span class="col-required non">Non</span></td>
                        <td>15/03/2014</td>
                    </tr>
                    <tr>
                        <td><code>sexe</code></td>
                        <td>M ou F</td>
                        <td><span class="col-required non">Non</span></td>
                        <td>M</td>
                    </tr>
                    <tr>
                        <td><code>email</code></td>
                        <td>Adresse email</td>
                        <td><span class="col-required non">Non</span></td>
                        <td>eleve@exemple.tn</td>
                    </tr>
                    <tr>
                        <td><code>telephone</code></td>
                        <td>Numéro de téléphone</td>
                        <td><span class="col-required non">Non</span></td>
                        <td>+216 22 333 444</td>
                    </tr>
                    <tr>
                        <td><code>classe</code></td>
                        <td>Nom de la classe (doit exister)</td>
                        <td><span class="col-required non">Non</span></td>
                        <td>9ème Base A</td>
                    </tr>
                </tbody>
            </table>

            <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px;
                        padding:12px 14px; margin-top:16px; font-size:12px; color:#92400e;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <strong>Attention :</strong> le matricule est saisi dans le fichier (colonne
                <code>matricule</code>) et doit être unique. Les classes doivent exister au
                préalable (le nom doit correspondre). Un <strong>compte de connexion</strong>
                est créé pour chaque élève importé (mot de passe par défaut : <code>Amilcar2026!</code>).
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    const dropZone   = document.getElementById('dropZone');
    const fileInput  = document.getElementById('fileInput');
    const submitBtn  = document.getElementById('submitBtn');
    const fileSel    = document.getElementById('fileSelected');

    function onFileSelected(input) {
        if (input.files && input.files[0]) {
            const f = input.files[0];
            document.getElementById('fileName').textContent = f.name;
            document.getElementById('fileSize').textContent = (f.size / 1024).toFixed(1) + ' Ko';
            fileSel.style.display = 'flex';
            submitBtn.disabled = false;
        }
    }

    function clearFile() {
        fileInput.value = '';
        fileSel.style.display = 'none';
        submitBtn.disabled = true;
    }

    // Drag & drop
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            onFileSelected(fileInput);
        }
    });

    // Download template CSV
    function downloadTemplate() {
        const rows = [
            'nom;prenom;date_naissance;sexe;email;telephone;classe',
            'BEN ALI;Mohamed;15/03/2014;M;mohamed@exemple.tn;+216 22 111 222;9ème Base A',
            'GHARBI;Fatima;20/05/2015;F;;;7ème Base B',
        ];
        const csv  = '\uFEFF' + rows.join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href = url; a.download = 'modele_import_eleves.csv';
        a.click(); URL.revokeObjectURL(url);
    }
</script>
@endsection

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\ParentEleve;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EleveController extends Controller
{
    /* ─── INDEX ─────────────────────────────────────────────── */
    public function index(Request $request)
    {
        $query = Eleve::with(['classe', 'parent'])->where('actif', true);

        // Recherche
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom',       'like', "%$search%")
                  ->orWhere('prenom',  'like', "%$search%")
                  ->orWhere('matricule','like', "%$search%")
                  ->orWhere('email',   'like', "%$search%");
            });
        }

        // Filtre niveau
        if ($niveau = $request->get('niveau')) {
            $query->whereHas('classe', fn($q) => $q->where('niveau', $niveau));
        }

        // Filtre classe
        if ($classeId = $request->get('classe_id')) {
            $query->where('classe_id', $classeId);
        }

        // Filtre sexe
        if ($sexe = $request->get('sexe')) {
            $query->where('sexe', $sexe);
        }

        $eleves  = $query->orderBy('nom')->paginate(20)->withQueryString();
        $classes = Classe::where('active', true)->orderBy('nom')->get();
        $stats   = [
            'total'      => Eleve::where('actif', true)->count(),
            'garcons'    => Eleve::where('actif', true)->where('sexe', 'M')->count(),
            'filles'     => Eleve::where('actif', true)->where('sexe', 'F')->count(),
            'new_mois'   => Eleve::where('actif', true)->whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.eleves.index', compact('eleves', 'classes', 'stats'));
    }

    /* ─── CREATE ────────────────────────────────────────────── */
    public function create()
    {
        $classes = Classe::where('active', true)->orderBy('nom')->get();
        $parents = ParentEleve::orderBy('nom')->get();
        return view('admin.eleves.create', compact('classes', 'parents'));
    }

    /* ─── STORE ─────────────────────────────────────────────── */
    public function store(Request $request)
    {
        $data = $request->validate([
            'matricule'      => 'required|string|max:50|unique:eleves,matricule',
            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'date_naissance' => 'nullable|date',
            'sexe'           => 'nullable|in:M,F',
            'adresse'        => 'nullable|string|max:255',
            'telephone'      => 'nullable|string|max:20',
            'email'          => 'nullable|email|unique:eleves,email',
            'classe_id'      => 'nullable|exists:classes,id',
            'parent_id'      => 'nullable|exists:parents,id',
            'photo'          => 'nullable|image|max:2048',
        ], [
            'matricule.required' => 'Le matricule est obligatoire.',
            'matricule.unique'   => 'Ce matricule est déjà attribué à un autre élève.',
            'nom.required'    => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.unique'    => 'Cet email est déjà utilisé.',
            'photo.image'     => 'La photo doit être une image.',
            'photo.max'       => 'La photo ne doit pas dépasser 2 Mo.',
        ]);

        $data['matricule']      = trim($data['matricule']);
        $data['annee_scolaire'] = date('Y') . '-' . (date('Y') + 1);
        $data['actif']          = true;

        // Photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('eleves/photos', 'public');
        }

        $eleve = Eleve::create($data);

        // ─── Création automatique du compte de connexion de l'élève ───
        // Email : celui saisi, sinon généré à partir du matricule.
        // Mot de passe par défaut : 'Amilcar2026!' (même convention que les enseignants).
        $emailLogin = $data['email'] ?? (strtolower($eleve->matricule) . '@eleve.gsa');

        if (!User::where('email', $emailLogin)->exists()) {
            $user = User::create([
                'nom'      => $eleve->nom,
                'prenom'   => $eleve->prenom,
                'email'    => $emailLogin,
                'password' => Hash::make('Amilcar2026!'),
                'role'     => 'eleve',
                'actif'    => true,
            ]);
            $eleve->update(['user_id' => $user->id]);
        }

        Journal::log('creation', "a ajouté l'élève {$eleve->prenom} {$eleve->nom} ({$eleve->matricule})");

        return redirect()->route('admin.eleves.index')
            ->with('success', "L'élève {$eleve->prenom} {$eleve->nom} a été ajouté avec succès.");
    }

    /* ─── SHOW (fiche élève) ────────────────────────────────── */
    public function show(Eleve $eleve)
    {
        $eleve->load(['classe', 'parent', 'absences', 'notes.matiere']);

        // Calcul moyennes par trimestre
        $moyennes = [];
        foreach ([1, 2, 3] as $trim) {
            $notes = $eleve->notes->where('trimestre', $trim);
            if ($notes->count()) {
                $somme = $notes->sum(fn($n) => $n->valeur * ($n->matiere->coefficient ?? 1));
                $coefs = $notes->sum(fn($n) => $n->matiere->coefficient ?? 1);
                $moyennes[$trim] = $coefs > 0 ? round($somme / $coefs, 2) : null;
            }
        }

        $totalAbsences   = $eleve->absences->count();
        $absNonJustifie  = $eleve->absences->where('justifie', false)->count();

        return view('admin.eleves.show', compact('eleve', 'moyennes', 'totalAbsences', 'absNonJustifie'));
    }

    /* ─── EDIT ──────────────────────────────────────────────── */
    public function edit(Eleve $eleve)
    {
        $classes = Classe::where('active', true)->orderBy('nom')->get();
        $parents = ParentEleve::orderBy('nom')->get();
        return view('admin.eleves.edit', compact('eleve', 'classes', 'parents'));
    }

    /* ─── UPDATE ────────────────────────────────────────────── */
    public function update(Request $request, Eleve $eleve)
    {
        $data = $request->validate([
            'matricule'      => "required|string|max:50|unique:eleves,matricule,{$eleve->id}",
            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'date_naissance' => 'nullable|date',
            'sexe'           => 'nullable|in:M,F',
            'adresse'        => 'nullable|string|max:255',
            'telephone'      => 'nullable|string|max:20',
            'email'          => "nullable|email|unique:eleves,email,{$eleve->id}",
            'classe_id'      => 'nullable|exists:classes,id',
            'parent_id'      => 'nullable|exists:parents,id',
            'photo'          => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // Supprimer ancienne photo
            if ($eleve->photo) Storage::disk('public')->delete($eleve->photo);
            $data['photo'] = $request->file('photo')->store('eleves/photos', 'public');
        }

        $eleve->update($data);

        Journal::log('modification', "a modifié la fiche de {$eleve->prenom} {$eleve->nom}");

        return redirect()->route('admin.eleves.show', $eleve)
            ->with('success', 'Fiche élève mise à jour avec succès.');
    }

    /* ─── DESTROY (désactiver) ──────────────────────────────── */
    public function destroy(Eleve $eleve)
    {
        $eleve->update(['actif' => false]);
        Journal::log('suppression', "a désactivé l'élève {$eleve->prenom} {$eleve->nom}");

        return redirect()->route('admin.eleves.index')
            ->with('success', "L'élève {$eleve->prenom} {$eleve->nom} a été désactivé.");
    }

    /* ─── FICHE PDF ─────────────────────────────────────────── */
    public function fiche(Eleve $eleve)
    {
        $eleve->load(['classe', 'parent']);
        // Dans un vrai projet Laravel : return view('admin.eleves.fiche-pdf', ...) via dompdf/snappy
        return view('admin.eleves.fiche', compact('eleve'));
    }

    /* ─── IMPORT FORM ───────────────────────────────────────── */
    public function importForm()
    {
        return view('admin.eleves.import');
    }

    /* ─── TÉLÉCHARGER LE MODÈLE EXCEL (.xlsx) ───────────────── */
    public function importModele()
    {
        $chemin = storage_path('app/templates/modele_import_eleves.xlsx');
        if (!file_exists($chemin)) {
            abort(404, "Le modèle est introuvable.");
        }
        return response()->download($chemin, 'modele_import_eleves.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /* ─── IMPORT POST (xlsx ou csv, sans librairie) ─────────── */
    public function importPost(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|max:5120',
        ], [
            'fichier.required' => 'Veuillez sélectionner un fichier.',
        ]);

        $ext      = strtolower($request->file('fichier')->getClientOriginalExtension());
        $fullPath = $request->file('fichier')->getRealPath();

        if (!in_array($ext, ['xlsx', 'csv', 'txt'])) {
            return back()->withErrors(['fichier' => "Format non pris en charge. Utilisez le modèle Excel (.xlsx) ou un fichier .csv."]);
        }

        try {
            $lignes = ($ext === 'xlsx') ? $this->lireXlsx($fullPath) : $this->lireCsv($fullPath);
        } catch (\Throwable $e) {
            return back()->withErrors(['fichier' => 'Lecture du fichier impossible : ' . $e->getMessage()]);
        }

        [$created, $errors] = $this->traiterLignesImport($lignes);

        Journal::log('creation', "a importé $created élèves via fichier " . strtoupper($ext));

        $message = "$created élève(s) importé(s) avec succès.";
        if ($errors) $message .= ' ' . count($errors) . ' ligne(s) ignorée(s).';

        return redirect()->route('admin.eleves.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    /* ─── Traitement commun des lignes (crée élève + compte) ── */
    private function traiterLignesImport(array $lignes): array
    {
        $annee   = date('Y');
        $created = 0;
        $errors  = [];
        $no      = 0;

        foreach ($lignes as $row) {
            $no++;
            if ($no === 1) continue; // ligne d'en-têtes

            // Ignorer les lignes entièrement vides
            if (count(array_filter($row, fn($v) => trim((string) $v) !== '')) === 0) continue;

            // Colonnes : matricule | nom | prenom | date_naissance | sexe | email | telephone | classe
            $matricule = trim($row[0] ?? '');
            $nom       = trim($row[1] ?? '');
            $prenom    = trim($row[2] ?? '');

            if ($matricule === '') {
                $errors[] = "Ligne $no : matricule manquant.";
                continue;
            }
            if ($nom === '' || $prenom === '') {
                $errors[] = "Ligne $no : nom ou prénom manquant (matricule $matricule).";
                continue;
            }
            if (Eleve::where('matricule', $matricule)->exists()) {
                $errors[] = "Ligne $no : le matricule $matricule existe déjà, ignoré.";
                continue;
            }

            $classeNom = trim($row[7] ?? '');
            $classe    = $classeNom ? Classe::where('nom', 'like', "%{$classeNom}%")->first() : null;
            $email     = trim($row[5] ?? '') ?: null;

            $eleve = Eleve::create([
                'matricule'      => $matricule,
                'nom'            => $nom,
                'prenom'         => $prenom,
                'date_naissance' => $this->parseDateImport($row[3] ?? ''),
                'sexe'           => strtoupper(trim($row[4] ?? '')) ?: null,
                'email'          => $email,
                'telephone'      => trim($row[6] ?? '') ?: null,
                'classe_id'      => $classe?->id,
                'annee_scolaire' => $annee . '-' . ($annee + 1),
                'actif'          => true,
            ]);

            // Compte de connexion (même convention que la création manuelle)
            $emailLogin = $email ?: (strtolower($matricule) . '@eleve.gsa');
            if (!User::where('email', $emailLogin)->exists()) {
                $user = User::create([
                    'nom'      => $eleve->nom,
                    'prenom'   => $eleve->prenom,
                    'email'    => $emailLogin,
                    'password' => Hash::make('Amilcar2026!'),
                    'role'     => 'eleve',
                    'actif'    => true,
                ]);
                $eleve->update(['user_id' => $user->id]);
            }

            $created++;
        }

        return [$created, $errors];
    }

    /* ─── Lecture CSV (séparateur auto, BOM géré) ───────────── */
    private function lireCsv(string $chemin): array
    {
        $contenu = file_get_contents($chemin);
        $contenu = preg_replace('/^\xEF\xBB\xBF/', '', $contenu); // BOM UTF-8

        $premiere = strtok($contenu, "\r\n") ?: '';
        $sep = (substr_count($premiere, ';') >= substr_count($premiere, ',')) ? ';' : ',';

        $lignes = [];
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $contenu);
        rewind($handle);
        while (($row = fgetcsv($handle, 0, $sep)) !== false) {
            $lignes[] = array_map(fn($v) => trim((string) $v), $row);
        }
        fclose($handle);
        return $lignes;
    }

    /* ─── Lecture XLSX sans librairie (ZipArchive + SimpleXML) ─ */
    private function lireXlsx(string $chemin): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException("L'extension PHP zip est requise pour lire les fichiers .xlsx.");
        }

        $zip = new \ZipArchive();
        if ($zip->open($chemin) !== true) {
            throw new \RuntimeException("Fichier .xlsx illisible (archive invalide).");
        }

        // Chaînes partagées
        $shared = [];
        $ss = $zip->getFromName('xl/sharedStrings.xml');
        if ($ss !== false) {
            $sx = @simplexml_load_string($ss);
            if ($sx !== false) {
                foreach ($sx->si as $si) {
                    $shared[] = $this->texteNoeud($si);
                }
            }
        }

        // Première feuille (résolue via workbook + relations, avec repli)
        $sheetPath = 'xl/worksheets/sheet1.xml';
        $wb   = $zip->getFromName('xl/workbook.xml');
        $rels = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($wb !== false && $rels !== false) {
            $wx = @simplexml_load_string($wb);
            $rx = @simplexml_load_string($rels);
            if ($wx !== false && $rx !== false && isset($wx->sheets->sheet[0])) {
                $attrs = $wx->sheets->sheet[0]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                $rid   = isset($attrs['id']) ? (string) $attrs['id'] : '';
                foreach ($rx->Relationship as $rel) {
                    if ((string) $rel['Id'] === $rid) {
                        $target = ltrim((string) $rel['Target'], '/');
                        if (strpos($target, 'xl/') !== 0) $target = 'xl/' . $target;
                        $sheetPath = $target;
                        break;
                    }
                }
            }
        }

        $sheet = $zip->getFromName($sheetPath);
        $zip->close();
        if ($sheet === false) {
            throw new \RuntimeException("Feuille de calcul introuvable dans le fichier.");
        }

        $sx = @simplexml_load_string($sheet);
        if ($sx === false || !isset($sx->sheetData)) {
            throw new \RuntimeException("Contenu du fichier .xlsx illisible.");
        }

        $lignes = [];
        foreach ($sx->sheetData->row as $row) {
            $cells  = [];
            $maxIdx = -1;
            foreach ($row->c as $c) {
                $ref  = (string) $c['r'];
                $col  = preg_replace('/[0-9]+/', '', $ref);
                $idx  = $this->colToIndex($col);
                $type = (string) $c['t'];

                if ($type === 's') {
                    $val = $shared[(int) $c->v] ?? '';
                } elseif ($type === 'inlineStr') {
                    $val = $this->texteNoeud($c->is);
                } else {
                    $val = (string) $c->v;
                }

                $cells[$idx] = trim((string) $val);
                if ($idx > $maxIdx) $maxIdx = $idx;
            }
            $ligne = [];
            for ($i = 0; $i <= $maxIdx; $i++) {
                $ligne[$i] = $cells[$i] ?? '';
            }
            $lignes[] = $ligne;
        }
        return $lignes;
    }

    /* ─── Texte d'un noeud <si>/<is> (gère les runs) ────────── */
    private function texteNoeud($noeud): string
    {
        $txt = '';
        if (isset($noeud->t)) {
            $txt .= (string) $noeud->t;
        }
        if (isset($noeud->r)) {
            foreach ($noeud->r as $r) {
                $txt .= (string) $r->t;
            }
        }
        return $txt;
    }

    /* ─── Lettre(s) de colonne -> index 0-based ─────────────── */
    private function colToIndex(string $lettres): int
    {
        $lettres = strtoupper($lettres);
        $n = 0;
        for ($i = 0, $len = strlen($lettres); $i < $len; $i++) {
            $n = $n * 26 + (ord($lettres[$i]) - 64);
        }
        return max(0, $n - 1);
    }

    /* ─── Parse une date (texte ou n° de série Excel) ───────── */
    private function parseDateImport($val): ?string
    {
        $val = trim((string) $val);
        if ($val === '') return null;

        if (is_numeric($val)) {
            // Numéro de série Excel -> date
            $ts = ((float) $val - 25569) * 86400;
            return date('Y-m-d', (int) $ts);
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'd/m/y'] as $fmt) {
            $d = \DateTime::createFromFormat($fmt, $val);
            if ($d !== false) return $d->format('Y-m-d');
        }

        $ts = strtotime($val);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    /* ─── EXPORT EXCEL (CSV) ────────────────────────────────── */
    public function exportExcel()
    {
        $eleves = Eleve::with(['classe', 'parent'])->where('actif', true)->orderBy('nom')->get();

        $filename = 'eleves_amilcar_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($eleves) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fputs($handle, "\xEF\xBB\xBF");
            // En-têtes
            fputcsv($handle, [
                'Matricule','Nom','Prénom','Date naissance','Sexe',
                'Classe','Niveau','Parent','Téléphone','Email','Année scolaire'
            ], ';');
            foreach ($eleves as $e) {
                fputcsv($handle, [
                    $e->matricule,
                    $e->nom,
                    $e->prenom,
                    $e->date_naissance?->format('d/m/Y') ?? '',
                    $e->sexe === 'M' ? 'Masculin' : ($e->sexe === 'F' ? 'Féminin' : ''),
                    $e->classe?->nom ?? '',
                    $e->classe?->niveau ?? '',
                    $e->parent ? "{$e->parent->prenom} {$e->parent->nom}" : '',
                    $e->telephone ?? '',
                    $e->email ?? '',
                    $e->annee_scolaire,
                ], ';');
            }
            fclose($handle);
        };

        Journal::log('export', 'a exporté la liste des élèves en Excel/CSV');

        return response()->stream($callback, 200, $headers);
    }

    /* ─── EXPORT PDF (HTML print) ───────────────────────────── */
    public function exportPdf()
    {
        $eleves = Eleve::with(['classe'])->where('actif', true)->orderBy('nom')->get();
        return view('admin.eleves.export-pdf', compact('eleves'));
    }
}

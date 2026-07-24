<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\MessagerieController;
use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\CahierTexteController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EleveController;
use App\Http\Controllers\Admin\EnseignantController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\ClasseController;
use App\Http\Controllers\Admin\EmploiController;
use App\Http\Controllers\Admin\NoteController;
use App\Http\Controllers\Admin\ExamenController;
use App\Http\Controllers\Admin\BibliothequeController;
use App\Http\Controllers\Admin\AttestationController;
use App\Http\Controllers\Admin\AnnonceController;
use App\Http\Controllers\Admin\ParametreController;

use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Eleve\DashboardController as EleveDashboardController;

use App\Http\Controllers\Enseignant\DashboardController as EnseignantDashboardController;
use App\Http\Controllers\Enseignant\NoteController as EnseignantNoteController;
use App\Http\Controllers\Enseignant\ExamenController as EnseignantExamenController;

/*
|--------------------------------------------------------------------------
| FICHIER WEB.PHP COMPLET — FUSION DES 8 MODULES AMILCAR
|--------------------------------------------------------------------------
| Ce fichier remplace entièrement routes/web.php
| Ordre de fusion : Module 1 → 2 → 3 → 4 → 5 → 6 → 7 → 8
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES — Authentification (Module 1)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/',       [LoginController::class, 'showLogin'])->name('login');
    Route::get('/login',  [LoginController::class, 'showLogin'])->name('login.form');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| ROUTE PUBLIQUE — Vérification QR Code attestation (Module 6)
|--------------------------------------------------------------------------
| IMPORTANT : reste hors de tout middleware auth, accessible sans connexion
*/
Route::get('/verify/{code}', [VerificationController::class, 'verify'])->name('verify');


/*
|--------------------------------------------------------------------------
| MESSAGERIE — accessible à tout utilisateur connecté (tous rôles)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('messagerie')
    ->name('messagerie.')
    ->group(function () {
        Route::get('/',                       [MessagerieController::class, 'index'])->name('index');
        Route::get('/nouveau',                [MessagerieController::class, 'nouveau'])->name('nouveau');
        Route::post('/envoyer',               [MessagerieController::class, 'envoyer'])->name('envoyer');
        Route::get('/{conversation}',         [MessagerieController::class, 'show'])->name('show');
        Route::post('/{conversation}/repondre',[MessagerieController::class, 'repondre'])->name('repondre');
    });


/*
|--------------------------------------------------------------------------
| ABSENCES — saisie de l'appel (admin + enseignant)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,enseignant'])
    ->prefix('absences')
    ->name('absences.')
    ->group(function () {
        Route::get('/',            [AbsenceController::class, 'index'])->name('index');
        Route::post('/',           [AbsenceController::class, 'enregistrer'])->name('enregistrer');
        Route::get('/historique',  [AbsenceController::class, 'historique'])->name('historique');
    });


/*
|--------------------------------------------------------------------------
| CAHIER DE TEXTE — consultation (tous) + saisie (enseignant)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('cahier-texte')
    ->name('cahier.')
    ->group(function () {
        Route::get('/',                  [CahierTexteController::class, 'index'])->name('index');
        Route::get('/nouveau',           [CahierTexteController::class, 'create'])->name('create');
        Route::post('/',                 [CahierTexteController::class, 'store'])->name('store');
        Route::get('/{cahier}/modifier', [CahierTexteController::class, 'edit'])->name('edit');
        Route::put('/{cahier}',          [CahierTexteController::class, 'update'])->name('update');
        Route::delete('/{cahier}',       [CahierTexteController::class, 'destroy'])->name('destroy');
    });


/*
|--------------------------------------------------------------------------
| ESPACE ADMIN — Modules 1, 2, 3, 4, 5, 6, 8
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /* ───── Module 1 : Dashboard ───── */
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        /* ───── Module 2 : Élèves ───── */
        Route::get('eleves/import',         [EleveController::class, 'importForm'])->name('eleves.import.form');
        Route::get('eleves/import/modele',  [EleveController::class, 'importModele'])->name('eleves.import.modele');
        Route::post('eleves/import',        [EleveController::class, 'importPost'])->name('eleves.import.post');
        Route::get('eleves/export-excel',   [EleveController::class, 'exportExcel'])->name('eleves.export.excel');
        Route::get('eleves/export-pdf',     [EleveController::class, 'exportPdf'])->name('eleves.export.pdf');
        Route::get('eleves/{eleve}/fiche',  [EleveController::class, 'fiche'])->name('eleves.fiche');
        Route::resource('eleves', EleveController::class)->parameters(['eleves' => 'eleve']);

        /* ───── Module 3 : Enseignants ───── */
        Route::resource('enseignants', EnseignantController::class);
        Route::patch('enseignants/{enseignant}/reactiver', [EnseignantController::class, 'reactiver'])->name('enseignants.reactiver');

        /* ───── Module 3 : Parents ───── */
        Route::resource('parents', ParentController::class)->names([
            'index'   => 'parents.index',
            'create'  => 'parents.create',
            'store'   => 'parents.store',
            'show'    => 'parents.show',
            'edit'    => 'parents.edit',
            'update'  => 'parents.update',
            'destroy' => 'parents.destroy',
        ]);

        /* ───── Module 3 : Classes ───── */
        Route::resource('classes', ClasseController::class)->parameters(['classes' => 'classe']);
        Route::post('classes/{classe}/matieres',            [ClasseController::class, 'ajouterMatiere'])->name('classes.matieres.add');
        Route::delete('classes/{classe}/matieres/{matiere}',[ClasseController::class, 'supprimerMatiere'])->name('classes.matieres.delete');

        /* ───── Module 4 : Emplois du temps ───── */
        Route::get('emplois',             [EmploiController::class, 'index'])->name('emplois.index');
        Route::get('emplois/{classe}',    [EmploiController::class, 'show'])->name('emplois.show');
        Route::post('emplois/store',      [EmploiController::class, 'store'])->name('emplois.store');
        Route::delete('emplois/{emploi}', [EmploiController::class, 'destroy'])->name('emplois.destroy');

        /* ───── Module 4 : Notes ───── */
        Route::get('notes',                      [NoteController::class, 'index'])->name('notes.index');
        Route::get('notes/{classe}/{trimestre}',  [NoteController::class, 'saisie'])->name('notes.saisie');
        Route::post('notes/sauvegarder',          [NoteController::class, 'sauvegarder'])->name('notes.sauvegarder');

        /* ───── Module 4 : Bulletins ───── */
        Route::get('bulletins',                [NoteController::class, 'bulletins'])->name('bulletins.index');
        Route::get('bulletins/{eleve}/{trim}', [NoteController::class, 'bulletinPdf'])->name('bulletins.pdf');

        /* ───── Module 5 : Examens IA ───── */
        Route::get('examens',              [ExamenController::class, 'index'])->name('examens.index');
        Route::get('examens/create',       [ExamenController::class, 'create'])->name('examens.create');
        Route::post('examens/generer',     [ExamenController::class, 'generer'])->name('examens.generer');
        Route::get('examens/{examen}',     [ExamenController::class, 'show'])->name('examens.show');
        Route::post('examens/{examen}/questions', [ExamenController::class, 'sauvegarderQuestions'])->name('examens.questions.sauvegarder');
        Route::get('examens/{examen}/pdf', [ExamenController::class, 'pdf'])->name('examens.pdf');
        Route::delete('examens/{examen}',  [ExamenController::class, 'destroy'])->name('examens.destroy');
        Route::get('classes/{classe}/matieres-ajax', [ExamenController::class, 'matieresParClasse'])->name('classes.matieres.ajax');

        /* ───── Module 6 : Bibliothèque ───── */
        Route::get('bibliotheque',                [BibliothequeController::class, 'index'])->name('bibliotheque.index');
        Route::post('bibliotheque/upload',        [BibliothequeController::class, 'upload'])->name('bibliotheque.upload');
        Route::delete('bibliotheque/{ressource}', [BibliothequeController::class, 'destroy'])->name('bibliotheque.destroy');
        Route::get('bibliotheque/classes/{classe}/matieres', [BibliothequeController::class, 'matieresParClasse'])->name('bibliotheque.matieres.ajax');

        /* ───── Module 6 : Attestations ───── */
        Route::get('attestations',                   [AttestationController::class, 'index'])->name('attestations.index');
        Route::get('attestations/create',             [AttestationController::class, 'create'])->name('attestations.create');
        Route::post('attestations/generer',           [AttestationController::class, 'generer'])->name('attestations.generer');
        Route::get('attestations/{attestation}/pdf',  [AttestationController::class, 'pdf'])->name('attestations.pdf');
        Route::delete('attestations/{attestation}',   [AttestationController::class, 'destroy'])->name('attestations.destroy');

        /* ───── Module 8 : Annonces (CRUD complet) ───── */
        Route::resource('annonces', AnnonceController::class)->except(['show']);
        Route::post('annonces/{annonce}/renvoyer', [AnnonceController::class, 'renvoyerEmail'])->name('annonces.renvoyer');

        /* ───── Module 1 : Paramètres ───── */
        Route::get('parametres',                      [ParametreController::class, 'index'])->name('parametres');
        Route::post('parametres/ia',                   [ParametreController::class, 'updateIA'])->name('parametres.ia.update');
        Route::post('parametres/ia/tester',             [ParametreController::class, 'testerIA'])->name('parametres.ia.tester');
        Route::post('parametres/admins',                [ParametreController::class, 'storeAdmin'])->name('parametres.admins.store');
        Route::patch('parametres/admins/{admin}/toggle', [ParametreController::class, 'toggleAdmin'])->name('parametres.admins.toggle');
        Route::get('systeme/reparer-migrations', [ParametreController::class, 'repererMigrations'])->name('systeme.reparer-migrations');

        Route::get('journal', function () {
            $logs = \App\Models\Journal::with('user')->latest()->paginate(30);
            return view('admin.journal.index', compact('logs'));
        })->name('journal');

    });


/*
|--------------------------------------------------------------------------
| ESPACE ENSEIGNANT — Module 7 (ajouté)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:enseignant'])
    ->prefix('enseignant')
    ->name('enseignant.')
    ->group(function () {

        /* ───── Accueil & consultation ───── */
        Route::get('/dashboard', [EnseignantDashboardController::class, 'index'])->name('dashboard');
        Route::get('/classes',   [EnseignantDashboardController::class, 'classes'])->name('classes');
        Route::get('/emploi',    [EnseignantDashboardController::class, 'emploi'])->name('emploi');
        Route::get('/annonces',  [EnseignantDashboardController::class, 'annonces'])->name('annonces');

        /* ───── Saisie des notes ───── */
        Route::get('/notes',                                  [EnseignantNoteController::class, 'index'])->name('notes.index');
        Route::get('/notes/{classe}/{matiere}/{trimestre}',   [EnseignantNoteController::class, 'saisie'])->name('notes.saisie');
        Route::post('/notes/sauvegarder',                     [EnseignantNoteController::class, 'sauvegarder'])->name('notes.sauvegarder');

        /* ───── Examens IA ───── */
        Route::get('/examens',                 [EnseignantExamenController::class, 'index'])->name('examens.index');
        Route::get('/examens/create',          [EnseignantExamenController::class, 'create'])->name('examens.create');
        Route::post('/examens/generer',        [EnseignantExamenController::class, 'generer'])->name('examens.generer');
        Route::get('/examens/{examen}',        [EnseignantExamenController::class, 'show'])->name('examens.show');
        Route::post('/examens/{examen}/questions', [EnseignantExamenController::class, 'sauvegarderQuestions'])->name('examens.questions.sauvegarder');
        Route::get('/examens/{examen}/pdf',    [EnseignantExamenController::class, 'pdf'])->name('examens.pdf');
        Route::post('/examens/{examen}/envoyer',[EnseignantExamenController::class, 'envoyer'])->name('examens.envoyer');
        Route::post('/examens/{examen}/retirer',[EnseignantExamenController::class, 'retirer'])->name('examens.retirer');
        Route::get('/examens/{examen}/copies',  [EnseignantExamenController::class, 'copies'])->name('examens.copies');
        Route::post('/examens/{examen}/copies/{copie}/noter', [EnseignantExamenController::class, 'noterCopie'])->name('examens.copies.noter');
        Route::post('/examens/{examen}/copies/{copie}/rapport', [EnseignantExamenController::class, 'genererRapport'])->name('examens.copies.rapport');
        Route::post('/examens/{examen}/copies/{copie}/rapport/parent', [EnseignantExamenController::class, 'basculerRapportParent'])->name('examens.copies.rapport.parent');
        Route::post('/examens/{examen}/rapport-classe', [EnseignantExamenController::class, 'genererRapportClasse'])->name('examens.rapport.classe');
        Route::delete('/examens/{examen}',     [EnseignantExamenController::class, 'destroy'])->name('examens.destroy');
        Route::get('/classes/{classe}/matieres-ajax', [EnseignantExamenController::class, 'matieresParClasse'])->name('classes.matieres.ajax');

    });


/*
|--------------------------------------------------------------------------
| ESPACE PARENT — Module 7
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:parent'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {

        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/notifications', [ParentDashboardController::class, 'notifications'])->name('notifications');
        Route::get('/enfant/{eleve}',               [ParentDashboardController::class, 'enfant'])->name('enfant.show');
        Route::get('/enfant/{eleve}/attestations',  [ParentDashboardController::class, 'attestations'])->name('enfant.attestations');
        Route::get('/enfant/{eleve}/rapports',       [ParentDashboardController::class, 'rapports'])->name('enfant.rapports');
        Route::get('/enfant/{eleve}/emploi',        [ParentDashboardController::class, 'emploi'])->name('enfant.emploi');
        Route::get('/annonces', [ParentDashboardController::class, 'annonces'])->name('annonces');
    });


/*
|--------------------------------------------------------------------------
| ESPACE ÉLÈVE — Module 7
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:eleve'])
    ->prefix('eleve')
    ->name('eleve.')
    ->group(function () {

        Route::get('/dashboard',    [EleveDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profil', [EleveDashboardController::class, 'profil'])->name('profil');
        Route::get('/cours',        [EleveDashboardController::class, 'cours'])->name('cours');
        Route::get('/resultats',    [EleveDashboardController::class, 'resultats'])->name('resultats');
        Route::get('/bibliotheque', [EleveDashboardController::class, 'bibliotheque'])->name('bibliotheque');
        Route::get('/examens',          [EleveDashboardController::class, 'examens'])->name('examens');
        Route::get('/examens/{examen}', [EleveDashboardController::class, 'examenShow'])->name('examens.show');
        Route::post('/examens/{examen}/soumettre', [EleveDashboardController::class, 'examenSoumettre'])->name('examens.soumettre');

    });

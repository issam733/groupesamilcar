<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Note;
use App\Models\Absence;
use App\Observers\NoteObserver;
use App\Observers\AbsenceObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * C'est ici qu'on enregistre les Observers.
     * À chaque création/modification d'une Note ou d'une Absence,
     * Laravel déclenchera automatiquement les méthodes correspondantes
     * dans NoteObserver et AbsenceObserver.
     *
     * Si vous avez déjà un AppServiceProvider, ajoutez UNIQUEMENT
     * les lignes Observer::observe() dans votre méthode boot() existante.
     */
    public function boot(): void
    {
        Note::observe(NoteObserver::class);
        Absence::observe(AbsenceObserver::class);
    }
}

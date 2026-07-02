@extends('eleve.layouts.app')

@section('title', 'Mon profil')
@section('page-title', 'Mon profil')
@section('page-subtitle', $eleve->matricule ?? '')

@section('extra-css')
    <style>
        .profile-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            max-width: 760px;
        }
        .profile-header {
            background: linear-gradient(135deg, #0d9488, #14b8a6);
            padding: 28px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .profile-photo, .profile-initials {
            width: 86px;
            height: 86px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,.35);
            flex-shrink: 0;
        }
        .profile-photo { object-fit: cover; }
        .profile-initials {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.18);
            font-size: 28px;
            font-weight: 800;
        }
        .profile-name { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
        .profile-meta { font-size: 13px; opacity: .85; }
        .profile-body { padding: 24px; }
        <div class="info-grid" style="margin-bottom: 18px;">
        <div class="info-item">
        <div class="info-label">Mes points</div>
        <div class="info-value">{{ $totalPoints }} points</div>
        </div>

        <div class="info-item">
        <div class="info-label">Mon classement général</div>
        <div class="info-value">
                               @if($rang)
                {{ $rang }}e / {{ $totalEleves }} élèves
                                                              @else
                Non classé
        @endif
</div>
        </div>
        </div>
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        @media(max-width: 700px) {
            .info-grid { grid-template-columns: 1fr; }
            .profile-header { align-items: flex-start; flex-direction: column; }
        }
        .info-item {
            background: #f7fbfb;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 16px;
        }
        .info-label {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 14px;
            color: var(--text);
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="profile-card">
        <div class="profile-header">
            @if($eleve->photo)
                <img src="{{ asset('storage/'.$eleve->photo) }}" class="profile-photo" alt="Photo élève">
            @else
                <div class="profile-initials">
                    {{ strtoupper(substr($eleve->prenom,0,1).substr($eleve->nom,0,1)) }}
                </div>
            @endif

            <div>
                <div class="profile-name">{{ $eleve->prenom }} {{ $eleve->nom }}</div>
                <div class="profile-meta">
                    {{ $eleve->matricule }} · {{ $eleve->classe->nom ?? 'Aucune classe' }}
                </div>
            </div>
        </div>

        <div class="profile-body">
            <div class="info-grid" style="margin-bottom: 18px;">
                <div class="info-item">
                    <div class="info-label">Mes points</div>
                    <div class="info-value">{{ $totalPoints ?? 0 }} points</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Mon classement général</div>
                    <div class="info-value">
                        @if(!empty($rang))
                            {{ $rang }}e / {{ $totalEleves }} élèves
                        @else
                            Non classé
                        @endif
                    </div>
                </div>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nom</div>
                    <div class="info-value">{{ $eleve->nom }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Prénom</div>
                    <div class="info-value">{{ $eleve->prenom }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Date de naissance</div>
                    <div class="info-value">{{ $eleve->date_naissance?->format('d/m/Y') ?? 'Non renseignée' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Sexe</div>
                    <div class="info-value">
                        {{ $eleve->sexe === 'M' ? 'Masculin' : ($eleve->sexe === 'F' ? 'Féminin' : 'Non renseigné') }}
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $eleve->email ?: auth()->user()->email }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Téléphone</div>
                    <div class="info-value">{{ $eleve->telephone ?: 'Non renseigné' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Classe</div>
                    <div class="info-value">{{ $eleve->classe->nom ?? 'Aucune classe' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Année scolaire</div>
                    <div class="info-value">{{ $eleve->annee_scolaire ?? 'Non renseignée' }}</div>
                </div>

                <div class="info-item" style="grid-column:1 / -1;">
                    <div class="info-label">Adresse</div>
                    <div class="info-value">{{ $eleve->adresse ?: 'Non renseignée' }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection

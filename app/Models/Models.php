<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model {
    protected $fillable = ['user_id','nom','prenom','matiere','telephone','email','photo','diplome','actif'];
    protected $casts    = ['actif' => 'boolean'];
    public function user()    { return $this->belongsTo(User::class); }
    public function classes() { return $this->hasMany(Classe::class); }
    public function examens() { return $this->hasMany(Examen::class); }
    public function nomComplet() { return "{$this->prenom} {$this->nom}"; }
}

class ParentEleve extends Model {
    protected $table    = 'parents';
    protected $fillable = ['user_id','nom','prenom','telephone','email','profession','actif'];
    protected $casts    = ['actif' => 'boolean'];
    public function user()   { return $this->belongsTo(User::class); }
    public function eleves() { return $this->hasMany(Eleve::class, 'parent_id'); }
    public function nomComplet() { return "{$this->prenom} {$this->nom}"; }
}

class Classe extends Model {
    protected $fillable = ['nom','niveau','enseignant_id','annee_scolaire','effectif_max','active'];
    protected $casts    = ['active' => 'boolean'];
    public function enseignant() { return $this->belongsTo(Enseignant::class); }
    public function eleves()     { return $this->hasMany(Eleve::class); }
    public function matieres()   { return $this->hasMany(Matiere::class); }
    public function emplois()    { return $this->hasMany(EmploiDuTemps::class); }
    public function effectif()   { return $this->eleves()->count(); }
}

class Matiere extends Model {
    protected $fillable = ['classe_id','enseignant_id','nom','coefficient','heures_semaine'];
    public function classe()     { return $this->belongsTo(Classe::class); }
    public function enseignant() { return $this->belongsTo(Enseignant::class); }
    public function notes()      { return $this->hasMany(Note::class); }
}

class Absence extends Model {
    protected $fillable = ['eleve_id','date','justifie','motif'];
    protected $casts    = ['justifie' => 'boolean', 'date' => 'date'];
    public function eleve() { return $this->belongsTo(Eleve::class); }
}

class Note extends Model {
    protected $fillable = ['eleve_id','matiere_id','classe_id','type','valeur','trimestre','commentaire'];
    public function eleve()   { return $this->belongsTo(Eleve::class); }
    public function matiere() { return $this->belongsTo(Matiere::class); }
}

class Examen extends Model {
    protected $fillable = ['enseignant_id','classe_id','matiere_id','langue','niveau','difficulte','nb_questions','contenu','statut'];
    public function enseignant() { return $this->belongsTo(Enseignant::class); }
    public function classe()     { return $this->belongsTo(Classe::class); }
}

class Attestation extends Model {
    protected $fillable = ['eleve_id','type','langue','numero_unique','qr_code','genere_par','annee_scolaire'];
    public function eleve() { return $this->belongsTo(Eleve::class); }
}

class EmploiDuTemps extends Model {
    protected $table    = 'emplois_du_temps';
    protected $fillable = ['classe_id','matiere_id','enseignant_id','jour','heure_debut','heure_fin'];
    public function classe()     { return $this->belongsTo(Classe::class); }
    public function matiere()    { return $this->belongsTo(Matiere::class); }
    public function enseignant() { return $this->belongsTo(Enseignant::class); }
}

class Annonce extends Model {
    protected $fillable = ['titre','contenu','cible','created_by','publie'];
    protected $casts    = ['publie' => 'boolean'];
    public function auteur() { return $this->belongsTo(User::class, 'created_by'); }
}

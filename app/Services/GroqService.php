<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GroqService
{
    private string $apiKey;
    private string $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key', env('GROQ_API_KEY', ''));
        $this->model  = config('services.groq.model', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));
    }

    /**
     * Vérifie si la clé API est configurée
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Génère un examen complet à partir d'un contenu de cours
     *
     * @param string $contenuCours   Texte extrait du PDF ou saisi librement
     * @param array  $options        ['langue', 'niveau', 'matiere', 'difficulte', 'nb_questions', 'nb_questions_ouvertes']
     * @return array                 Structure JSON de l'examen généré
     * @throws Exception
     */
    public function genererExamen(string $contenuCours, array $options): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('La clé API Groq n\'est pas configurée. Contactez l\'administrateur système.');
        }

        $langue            = $options['langue']             ?? 'fr';
        $niveau             = $options['niveau']             ?? '9ème année';
        $matiere            = $options['matiere']            ?? 'Général';
        $difficulte         = $options['difficulte']         ?? 'moyen';
        $nbQcm              = (int) ($options['nb_qcm']      ?? 10);
        $nbOuvertes         = (int) ($options['nb_ouvertes']  ?? 5);

        $prompt = $this->construirePrompt($contenuCours, $langue, $niveau, $matiere, $difficulte, $nbQcm, $nbOuvertes);

        $response = $this->appelerApi($prompt);

        return $this->parserReponse($response);
    }

    /**
     * Génère un résumé de cours avec points clés et questions de révision
     */
    public function genererResume(string $contenuCours, string $langue = 'fr'): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('La clé API Groq n\'est pas configurée.');
        }

        $langueTexte = $this->langueTexte($langue);

        $systemPrompt = <<<PROMPT
Tu es un professeur expérimenté qui prépare des fiches de révision pour des élèves.
Analyse le cours fourni et produis UNIQUEMENT un objet JSON valide (sans markdown, sans backticks, sans texte avant/après) avec cette structure exacte :

{
  "resume": "Un résumé clair et structuré du cours en {$langueTexte}, 200-400 mots",
  "points_cles": ["Point clé 1", "Point clé 2", "..."],
  "questions_revision": ["Question 1 ?", "Question 2 ?", "..."]
}

Réponds en {$langueTexte}. Le résumé doit être pédagogique et adapté à des élèves.
PROMPT;

        $response = $this->appelerApi($systemPrompt, $contenuCours);
        return $this->parserReponse($response);
    }

    /**
     * Génère un devoir/exercice sur un sujet précis (sans PDF)
     */
    public function genererDevoir(string $sujet, array $options): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('La clé API Groq n\'est pas configurée.');
        }

        $langue     = $options['langue']  ?? 'fr';
        $niveau     = $options['niveau']  ?? '9ème année';
        $langueTexte = $this->langueTexte($langue);

        $systemPrompt = <<<PROMPT
Tu es un professeur expérimenté. Crée un devoir/exercice pédagogique complet sur le sujet demandé, adapté au niveau {$niveau}.

Réponds UNIQUEMENT avec un objet JSON valide (sans markdown, sans backticks) avec cette structure exacte :

{
  "titre": "Titre du devoir",
  "consignes": "Instructions générales pour l'élève",
  "exercices": [
    {"numero": 1, "enonce": "Énoncé de l'exercice", "points": 5}
  ],
  "corrige": [
    {"numero": 1, "solution": "Solution détaillée de l'exercice"}
  ],
  "bareme_total": 20
}

Réponds en {$langueTexte}.
PROMPT;

        $response = $this->appelerApi($systemPrompt, $sujet);
        return $this->parserReponse($response);
    }

    /**
     * Génère un rapport pédagogique détaillé sur les lacunes/difficultés d'un
     * élève à partir de sa performance à un examen.
     *
     * @param string $performance  Texte décrivant les réponses de l'élève et leur exactitude
     * @param array  $meta         ['langue', 'eleve', 'matiere', 'niveau']
     * @return array               { appreciation, points_forts[], lacunes[], difficultes[], recommandations[], message_parent }
     */
    public function genererRapportEleve(string $performance, array $meta): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('La clé API Groq n\'est pas configurée. Contactez l\'administrateur système.');
        }

        $langueTexte = $this->langueTexte($meta['langue'] ?? 'fr');
        $eleve   = $meta['eleve']   ?? 'l\'élève';
        $matiere = $meta['matiere'] ?? 'la matière';
        $niveau  = $meta['niveau']  ?? '';

        $systemPrompt = <<<PROMPT
Tu es un enseignant bienveillant et expérimenté en {$matiere}. À partir des résultats détaillés d'un élève ({$eleve}, niveau {$niveau}) à un examen, rédige un rapport pédagogique précis et constructif sur ses lacunes et ses difficultés.

Réponds UNIQUEMENT avec un objet JSON valide (sans markdown, sans backticks, sans texte avant ou après) respectant EXACTEMENT cette structure :

{
  "appreciation": "Appréciation générale du niveau de l'élève en 2-3 phrases",
  "points_forts": ["Point fort observé 1", "Point fort 2"],
  "lacunes": ["Lacune précise constatée 1", "Lacune 2"],
  "difficultes": ["Difficulté identifiée 1", "Difficulté 2"],
  "recommandations": ["Conseil concret et actionnable 1", "Conseil 2"],
  "message_parent": "Un court paragraphe bienveillant adressé aux parents, avec des pistes concrètes pour aider l'enfant à la maison"
}

Appuie-toi UNIQUEMENT sur les questions réellement réussies ou ratées fournies. Sois précis, factuel, encourageant et bienveillant. Si l'élève a bien réussi, le rapport doit le refléter honnêtement. Réponds entièrement en {$langueTexte}.
PROMPT;

        $response = $this->appelerApi($systemPrompt, $performance);
        return $this->parserReponse($response);
    }

    /**
     * Génère un rapport de synthèse pour toute une classe à partir des résultats
     * agrégés à un examen (taux de réussite par question, moyenne, etc.).
     *
     * @param string $statistiques  Texte décrivant les statistiques agrégées de la classe
     * @param array  $meta          ['langue', 'matiere', 'niveau', 'classe']
     * @return array  { synthese, lacunes_recurrentes[], questions_problematiques[], recommandations_pedagogiques[], suivi_eleves }
     */
    public function genererRapportClasse(string $statistiques, array $meta): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('La clé API Groq n\'est pas configurée. Contactez l\'administrateur système.');
        }

        $langueTexte = $this->langueTexte($meta['langue'] ?? 'fr');
        $matiere = $meta['matiere'] ?? 'la matière';
        $niveau  = $meta['niveau']  ?? '';
        $classe  = $meta['classe']  ?? 'la classe';

        $systemPrompt = <<<PROMPT
Tu es un conseiller pédagogique expérimenté en {$matiere}. À partir des statistiques agrégées d'un examen passé par toute la classe {$classe} (niveau {$niveau}), rédige une synthèse pédagogique pour aider l'enseignant à ajuster son enseignement.

Réponds UNIQUEMENT avec un objet JSON valide (sans markdown, sans backticks, sans texte avant ou après) respectant EXACTEMENT cette structure :

{
  "synthese": "Synthèse générale du niveau de la classe en 2-4 phrases",
  "lacunes_recurrentes": ["Lacune partagée par beaucoup d'élèves 1", "Lacune 2"],
  "questions_problematiques": ["Question/notion la moins réussie et pourquoi 1", "..."],
  "recommandations_pedagogiques": ["Action concrète pour l'enseignant 1", "Action 2"],
  "suivi_eleves": "Conseil sur le suivi des élèves les plus en difficulté"
}

Appuie-toi sur les taux de réussite fournis (les questions à faible taux révèlent les notions à retravailler). Sois précis, factuel et orienté action. Réponds entièrement en {$langueTexte}.
PROMPT;

        $response = $this->appelerApi($systemPrompt, $statistiques);
        return $this->parserReponse($response);
    }

    /* ─────────────────────────────────────────────────────── */
    /* MÉTHODES PRIVÉES                                         */
    /* ─────────────────────────────────────────────────────── */

    private function construirePrompt(
        string $contenuCours, string $langue, string $niveau,
        string $matiere, string $difficulte, int $nbQcm, int $nbOuvertes
    ): string {
        $langueTexte    = $this->langueTexte($langue);
        $difficulteTexte = match($difficulte) {
            'facile'    => 'facile, questions de compréhension de base',
            'difficile' => 'difficile, questions d\'analyse et de synthèse approfondies',
            default     => 'moyen, équilibre entre mémorisation et compréhension',
        };

        return <<<PROMPT
Tu es un professeur expérimenté de {$matiere}, niveau {$niveau}.
Analyse le cours fourni par l'utilisateur et génère un examen complet.

Niveau de difficulté souhaité : {$difficulteTexte}.

Réponds UNIQUEMENT avec un objet JSON valide (sans markdown, sans backticks, sans texte avant ou après) respectant EXACTEMENT cette structure :

{
  "titre": "Titre de l'examen",
  "matiere": "{$matiere}",
  "niveau": "{$niveau}",
  "duree_minutes": 60,
  "bareme_total": 20,
  "qcm": [
    {
      "numero": 1,
      "question": "Texte de la question",
      "choix": ["Choix A", "Choix B", "Choix C", "Choix D"],
      "bonne_reponse": 0,
      "points": 1,
      "explication": "Explication courte de la bonne réponse"
    }
  ],
  "questions_ouvertes": [
    {
      "numero": 1,
      "question": "Texte de la question ouverte",
      "reponse_attendue": "Éléments de réponse attendus / corrigé",
      "points": 2
    }
  ]
}

Génère exactement {$nbQcm} questions QCM (4 choix chacune, une seule bonne réponse, index 0-3) et {$nbOuvertes} questions ouvertes.
Le barème total doit être cohérent et sommer à 20.
Réponds entièrement en {$langueTexte}, y compris les questions, choix et explications.
PROMPT . "\n\nContenu du cours à analyser :\n" . $contenuCours;
    }

    private function langueTexte(string $code): string
    {
        return match($code) {
            'ar' => 'arabe (استخدم العربية الفصحى)',
            'en' => 'anglais',
            default => 'français',
        };
    }

    /**
     * Appelle l'API Groq (chat completions, compatible OpenAI)
     */
    private function appelerApi(string $systemPrompt, string $userContent = ''): string
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        if ($userContent !== '') {
            $messages[] = ['role' => 'user', 'content' => $userContent];
        } else {
            $messages[] = ['role' => 'user', 'content' => 'Génère le contenu demandé.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->timeout(90)
            ->post($this->apiUrl, [
                'model'           => $this->model,
                'messages'        => $messages,
                'temperature'     => 0.7,
                'max_tokens'      => 4096,
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->failed()) {
                Log::error('Groq API error', ['status' => $response->status(), 'body' => $response->body()]);
                throw new Exception('Erreur lors de la communication avec l\'IA (code ' . $response->status() . '). Vérifiez la clé API.');
            }

            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? '';

        } catch (Exception $e) {
            Log::error('Groq Service Exception', ['message' => $e->getMessage()]);
            throw new Exception('Impossible de contacter le service IA : ' . $e->getMessage());
        }
    }

    /**
     * Parse la réponse JSON de l'IA en tableau PHP
     */
    private function parserReponse(string $jsonResponse): array
    {
        // Nettoyage au cas où l'IA ajoute des balises markdown malgré la consigne
        $clean = trim($jsonResponse);
        $clean = preg_replace('/^```json\s*/i', '', $clean);
        $clean = preg_replace('/^```\s*/i', '', $clean);
        $clean = preg_replace('/```\s*$/i', '', $clean);
        $clean = trim($clean);

        $data = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            Log::error('Groq JSON parse error', ['raw' => $jsonResponse, 'error' => json_last_error_msg()]);
            throw new Exception('La réponse de l\'IA n\'a pas pu être interprétée. Veuillez réessayer.');
        }

        return $data;
    }
}

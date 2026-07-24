<?php

namespace App\Services;

use App\Models\AiSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service IA unifié : bascule entre Groq et Anthropic selon la configuration
 * enregistrée dans la table ia_settings (modifiable depuis Admin > Paramètres).
 *
 * L'interface publique est identique à l'ancien GroqService : les contrôleurs
 * n'ont besoin de connaître ni le fournisseur actif, ni les détails d'appel API.
 */
class AIService
{
    private string $provider;
    private ?string $apiKey;
    private string $model;

    public function __construct()
    {
        $settings = AiSetting::current();

        $this->provider = $settings->provider ?: 'groq';

        if ($this->provider === 'anthropic') {
            $this->apiKey = $settings->anthropic_api_key ?: env('ANTHROPIC_API_KEY');
            $this->model  = $settings->anthropic_model ?: env('ANTHROPIC_MODEL', 'claude-sonnet-5');
        } else {
            $this->apiKey = $settings->groq_api_key ?: env('GROQ_API_KEY');
            $this->model  = $settings->groq_model ?: env('GROQ_MODEL', 'llama-3.3-70b-versatile');
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function providerActuel(): string
    {
        return $this->provider;
    }

    /**
     * Génère un examen complet à partir d'un contenu de cours
     *
     * @param string $contenuCours   Texte extrait du PDF ou saisi librement
     * @param array  $options        ['langue', 'niveau', 'matiere', 'difficulte', 'nb_qcm', 'nb_ouvertes']
     * @return array                 Structure JSON de l'examen généré
     * @throws Exception
     */
    public function genererExamen(string $contenuCours, array $options): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Aucune clé API IA n\'est configurée pour le fournisseur actif (' . $this->provider . '). Contactez l\'administrateur système.');
        }

        $langue     = $options['langue']      ?? 'fr';
        $niveau     = $options['niveau']      ?? '9ème année';
        $matiere    = $options['matiere']     ?? 'Général';
        $difficulte = $options['difficulte']  ?? 'moyen';
        $nbQcm      = (int) ($options['nb_qcm']     ?? 10);
        $nbOuvertes = (int) ($options['nb_ouvertes'] ?? 5);

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
            throw new Exception('Aucune clé API IA n\'est configurée pour le fournisseur actif.');
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
            throw new Exception('Aucune clé API IA n\'est configurée pour le fournisseur actif.');
        }

        $langue      = $options['langue'] ?? 'fr';
        $niveau      = $options['niveau'] ?? '9ème année';
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
     */
    public function genererRapportEleve(string $performance, array $meta): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Aucune clé API IA n\'est configurée pour le fournisseur actif.');
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
     */
    public function genererRapportClasse(string $statistiques, array $meta): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Aucune clé API IA n\'est configurée pour le fournisseur actif.');
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

    /**
     * Petit appel de test pour valider qu'une clé API fonctionne réellement
     * (utilisé par le bouton "Tester la connexion" dans Paramètres).
     */
    public function testerConnexion(): void
    {
        if (!$this->isConfigured()) {
            throw new Exception('Aucune clé n\'est renseignée pour ce fournisseur.');
        }
        // Appel minimal, ne coûte presque rien, sert juste à valider la clé/le réseau.
        $this->appelerApi('Réponds uniquement par le mot "ok" en JSON : {"status":"ok"}', 'Test de connexion.');
    }

    /* ─────────────────────────────────────────────────────── */
    /* MÉTHODES PRIVÉES                                         */
    /* ─────────────────────────────────────────────────────── */

    private function construirePrompt(
        string $contenuCours, string $langue, string $niveau,
        string $matiere, string $difficulte, int $nbQcm, int $nbOuvertes
    ): string {
        $langueTexte = $this->langueTexte($langue);

        $difficulteTexte = match($difficulte) {
            'facile' => <<<TXT
FACILE : questions de compréhension et de restitution directe. Les réponses se trouvent explicitement et littéralement dans le cours. Vocabulaire simple, une seule idée par question.
TXT,
            'difficile' => <<<TXT
DIFFICILE — exigence stricte, à respecter impérativement :
- INTERDIT de poser des questions dont la réponse est une simple phrase recopiable telle quelle du cours.
- Chaque question QCM doit exiger une analyse, une déduction, une comparaison entre plusieurs passages, ou l'application d'une notion à un cas nouveau non traité explicitement dans le cours.
- Les distracteurs (mauvaises réponses du QCM) doivent être plausibles et proches de la bonne réponse — pas des choix absurdes faciles à écarter par élimination.
- Les questions ouvertes doivent demander une argumentation, une justification, une mise en relation de plusieurs éléments, ou un jugement critique — jamais une simple définition.
- Exemple de question INTERDITE (trop facile même en mode difficile) : "Quel est le personnage principal du texte ?"
- Exemple de question ATTENDUE en mode difficile : "En quoi le comportement du personnage à la ligne X contredit-il ce qu'il affirme au début du texte ? Justifie."
TXT,
            default => <<<TXT
MOYEN : équilibre entre restitution directe et compréhension. Certaines questions demandent de reformuler ou relier deux idées du cours, sans exiger une analyse poussée.
TXT,
        };

        return <<<PROMPT
Tu es un professeur expérimenté de {$matiere}, niveau {$niveau}.
Analyse le cours/texte fourni par l'utilisateur et génère un examen complet.

Niveau de difficulté souhaité :
{$difficulteTexte}

IMPORTANT — texte de support :
Si le contenu fourni est un texte à étudier (étude de texte, texte littéraire, article, poème, extrait à analyser, compréhension de l'écrit), tu DOIS reproduire ce texte intégralement et fidèlement dans le champ "texte_support", afin que l'élève puisse le lire pendant l'examen. Ne le résume pas, ne le raccourcis pas. S'il n'y a pas de texte à étudier (cours théorique, notions de maths/sciences, etc.), laisse "texte_support" à une chaîne vide "".

Réponds UNIQUEMENT avec un objet JSON valide (sans markdown, sans backticks, sans texte avant ou après) respectant EXACTEMENT cette structure :

{
  "titre": "Titre de l'examen",
  "matiere": "{$matiere}",
  "niveau": "{$niveau}",
  "duree_minutes": 60,
  "bareme_total": 20,
  "texte_support": "Texte intégral à étudier si applicable, sinon chaîne vide",
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
Réponds entièrement en {$langueTexte}, y compris les questions, choix et explications (le texte_support reste dans sa langue d'origine s'il t'est fourni tel quel).
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
     * Appelle l'API du fournisseur actif (Groq ou Anthropic) et retourne le texte brut de la réponse.
     */
    private function appelerApi(string $systemPrompt, string $userContent = ''): string
    {
        $contenuUtilisateur = $userContent !== '' ? $userContent : 'Génère le contenu demandé.';

        try {
            if ($this->provider === 'anthropic') {
                $response = Http::withHeaders([
                    'x-api-key'         => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->timeout(90)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => $this->model,
                    'max_tokens' => 4096,
                    'system'     => $systemPrompt,
                    'messages'   => [
                        ['role' => 'user', 'content' => $contenuUtilisateur],
                    ],
                ]);

                if ($response->failed()) {
                    Log::error('Anthropic API error', ['status' => $response->status(), 'body' => $response->body()]);
                    throw new Exception('Erreur lors de la communication avec Anthropic (code ' . $response->status() . '). Vérifiez la clé API.');
                }

                $data = $response->json();
                return $data['content'][0]['text'] ?? '';
            }

            // Groq (compatible OpenAI chat completions)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->timeout(90)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'           => $this->model,
                'messages'        => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $contenuUtilisateur],
                ],
                'temperature'     => 0.7,
                'max_tokens'      => 4096,
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->failed()) {
                Log::error('Groq API error', ['status' => $response->status(), 'body' => $response->body()]);
                throw new Exception('Erreur lors de la communication avec Groq (code ' . $response->status() . '). Vérifiez la clé API.');
            }

            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? '';

        } catch (Exception $e) {
            Log::error('AIService Exception', ['provider' => $this->provider, 'message' => $e->getMessage()]);
            throw new Exception('Impossible de contacter le service IA (' . $this->provider . ') : ' . $e->getMessage());
        }
    }

    /**
     * Parse la réponse JSON de l'IA en tableau PHP
     */
    private function parserReponse(string $jsonResponse): array
    {
        $clean = trim($jsonResponse);
        $clean = preg_replace('/^```json\s*/i', '', $clean);
        $clean = preg_replace('/^```\s*/i', '', $clean);
        $clean = preg_replace('/```\s*$/i', '', $clean);
        $clean = trim($clean);

        $data = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            Log::error('AIService JSON parse error', ['raw' => $jsonResponse, 'error' => json_last_error_msg()]);
            throw new Exception('La réponse de l\'IA n\'a pas pu être interprétée. Veuillez réessayer.');
        }

        return $data;
    }
}

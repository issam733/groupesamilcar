<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attestation;
use App\Models\Eleve;
use App\Models\Journal;

class AttestationController extends Controller
{
    /* ─── INDEX : historique ─────────────────────────────────── */
    public function index(Request $request)
    {
        $query = Attestation::with(['eleve.classe']);

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($search = $request->get('search')) {
            $query->whereHas('eleve', fn($q) => $q->where('nom', 'like', "%$search%")->orWhere('prenom', 'like', "%$search%"))
                  ->orWhere('numero_unique', 'like', "%$search%");
        }

        $attestations = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'        => Attestation::count(),
            'inscription'  => Attestation::where('type', 'inscription')->count(),
            'presence'     => Attestation::where('type', 'presence')->count(),
            'reussite'     => Attestation::where('type', 'reussite')->count(),
        ];

        return view('admin.attestations.index', compact('attestations', 'stats'));
    }

    /* ─── CREATE : formulaire de génération ─────────────────── */
    public function create()
    {
        $eleves = Eleve::with('classe')->where('actif', true)->orderBy('nom')->get();
        return view('admin.attestations.create', compact('eleves'));
    }

    /* ─── GENERER : crée l'attestation + QR code ─────────────── */
    public function generer(Request $request)
    {
        $data = $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'type'     => 'required|in:inscription,presence,reussite',
            'langue'   => 'required|in:fr,ar',
        ]);

        $numero = Attestation::genererNumero();

        $attestation = Attestation::create([
            'eleve_id'       => $data['eleve_id'],
            'type'           => $data['type'],
            'langue'         => $data['langue'],
            'numero_unique'  => $numero,
            'annee_scolaire' => date('Y') . '-' . (date('Y') + 1),
            'genere_par'     => auth()->id(),
        ]);

        $eleve = Eleve::find($data['eleve_id']);

        Journal::log('creation', "a généré une attestation ({$data['type']}) pour {$eleve->prenom} {$eleve->nom}");

        return redirect()->route('admin.attestations.pdf', $attestation);
    }

    /* ─── PDF : document imprimable avec QR code ─────────────── */
    public function pdf(Attestation $attestation)
    {
        $attestation->load('eleve.classe');

        $urlVerification = route('verify', $attestation->numero_unique);
        $qrCodeSvg = $this->genererQrCodeSvg($urlVerification);

        Journal::log('export', "a imprimé l'attestation {$attestation->numero_unique}");

        return view('admin.attestations.pdf', compact('attestation', 'urlVerification', 'qrCodeSvg'));
    }

    /* ─── DESTROY ────────────────────────────────────────────── */
    public function destroy(Attestation $attestation)
    {
        $numero = $attestation->numero_unique;
        $attestation->delete();

        Journal::log('suppression', "a supprimé l'attestation {$numero}");

        return redirect()->route('admin.attestations.index')
            ->with('success', "L'attestation {$numero} a été supprimée.");
    }

    /**
     * Génère un QR code en SVG pur PHP (sans dépendance externe).
     * Pour une vraie génération QR code en production, utiliser
     * `simplesoftwareio/simple-qrcode` (voir notes d'intégration dans le README).
     * Ce fallback affiche un placeholder visuel + le lien en texte.
     */
    private function genererQrCodeSvg(string $url): string
    {
        // Placeholder simple : un cadre avec motif générique.
        // À remplacer par une vraie librairie QR en production.
        return '<svg width="140" height="140" viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg">
            <rect width="140" height="140" fill="#fff" stroke="#1a4fa0" stroke-width="2"/>
            <text x="70" y="65" font-size="9" text-anchor="middle" fill="#1a4fa0" font-family="monospace">QR CODE</text>
            <text x="70" y="80" font-size="7" text-anchor="middle" fill="#6b7f99" font-family="monospace">(voir README)</text>
        </svg>';
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attestation;
use App\Models\Eleve;
use App\Models\Journal;
use App\Services\QrCodeService;

class AttestationController extends Controller
{
    private QrCodeService $qrCode;

    public function __construct(QrCodeService $qrCode)
    {
        $this->qrCode = $qrCode;
    }
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
        $qrCodeSvg = $this->qrCode->genererSvg($urlVerification);

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
}

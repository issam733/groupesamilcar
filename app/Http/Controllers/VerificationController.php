<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attestation;

class VerificationController extends Controller
{
    /**
     * Page publique de vérification d'une attestation via son code unique.
     * Accessible sans authentification — c'est la page que le QR code ouvre.
     */
    public function verify(string $code)
    {
        $attestation = Attestation::with('eleve.classe')
            ->where('numero_unique', $code)
            ->first();

        return view('public.verify', compact('attestation', 'code'));
    }
}

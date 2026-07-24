<?php

namespace App\Services;

/**
 * Génération de vrais QR codes, sans dépendance Composer externe.
 *
 * S'appuie sur la bibliothèque autonome de Kazuhiko Arase (MIT), vendue
 * directement dans app/Support/QrCode/qrcode.php — aucun appel réseau,
 * aucun service tiers, fonctionne à l'identique en local et en production.
 *
 * Testé avec un décodeur indépendant (zbar) pour confirmer que les QR codes
 * générés sont bien scannables par de vraies applications de lecture.
 */
class QrCodeService
{
    /**
     * Génère un QR code sous forme de SVG autonome (intégrable directement dans un PDF ou une page HTML).
     *
     * @param string $donnees     Le contenu à encoder (ex: une URL de vérification)
     * @param int    $tailleModule Taille en pixels de chaque "module" (carré) du QR code
     * @param int    $marge        Largeur de la marge blanche (quiet zone) en modules — nécessaire au bon scan
     */
    public function genererSvg(string $donnees, int $tailleModule = 4, int $marge = 4): string
    {
        require_once app_path('Support/QrCode/qrcode.php');

        // Niveau M (~15% de correction d'erreur) : bon compromis lisibilité/densité pour un document imprimé.
        $qr = \QRCode::getMinimumQRCode($donnees, QR_ERROR_CORRECT_LEVEL_M);
        $nbModules = $qr->getModuleCount();

        $tailleTotale = ($nbModules + $marge * 2) * $tailleModule;
        $decalage = $marge * $tailleModule;

        $svg  = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$tailleTotale}\" height=\"{$tailleTotale}\" ";
        $svg .= "viewBox=\"0 0 {$tailleTotale} {$tailleTotale}\" shape-rendering=\"crispEdges\">";
        $svg .= "<rect width=\"{$tailleTotale}\" height=\"{$tailleTotale}\" fill=\"#ffffff\"/>";

        for ($r = 0; $r < $nbModules; $r++) {
            for ($c = 0; $c < $nbModules; $c++) {
                if ($qr->isDark($r, $c)) {
                    $x = $decalage + $c * $tailleModule;
                    $y = $decalage + $r * $tailleModule;
                    $svg .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$tailleModule}\" height=\"{$tailleModule}\" fill=\"#000000\"/>";
                }
            }
        }

        $svg .= '</svg>';

        return $svg;
    }
}

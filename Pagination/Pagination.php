<?php

/**
 * Class Pagination | /core/Pagination/Pagination.php
 *
 * @package     EsayD
 * @subpackage  Core
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.3 (10/03/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Pagination;

/**
 * Classe de gestion de la pagination
 */
class Pagination {

    public $nbByPages = 25;
    public $nbPages = 5;
    public $adresse = NULL;
    public $rajout = NULL;
    public $limite = NULL;
    public $nbTotal = 0;
    public $accesDirect = 'non';

    public function __construct() {
        
    }

    public function __destruct() {
        
    }

    /**
     * Création de la barre de navigation
     *
     * @return string
     */
    public function BarreNav() {

        $this->rajout = (strpos($this->adresse, '?') === false) ? '?limite=' : '&limite=';

        $barre = '<div class="num">';

        $lien_on = ' <a href="{cible}" title="{title}">{lien}</a> ';
        $lien_off = ' {lien} ';

        // Première page
        if ($this->limite >= $this->nbByPages) {
            $cible = $this->adresse;
            $lien = ' <a href="' . $cible . '" title="Première page" class="back2"><i class="fa fa-angle-double-left fa-lg"></i></a> ';
        } else {
            $lien = '';
        }

        $barre .= $lien;

        // Page précédente
        if ($this->limite >= $this->nbByPages) {

            if ($this->limite - $this->nbByPages == 0) {
                $cible = $this->adresse;
            } else {
                $cible = $this->adresse . $this->rajout . ($this->limite - $this->nbByPages);
            }

            $lien = ' <a href="' . $cible . '" title="Page précédente" class="back1"><i class="fa fa-angle-left fa-lg"></i></a> ';
        } else {
            $lien = '';
        }

        $barre .= $lien . ' ';

        // N° pages
        if ($this->limite >= ($this->nbPages * $this->nbByPages)) {

            $cpt_fin = ($this->limite / $this->nbByPages) + 1;
            $cpt_deb = $cpt_fin - $this->nbPages + 1;
        } else {

            $cpt_deb = 1;
            $cpt_fin = (int) ($this->nbTotal / $this->nbByPages);

            if (($this->nbTotal % $this->nbByPages) != 0) {
                $cpt_fin++;
            }

            if ($cpt_fin > $this->nbPages) {
                $cpt_fin = $this->nbPages;
            }
        }

        for ($cpt = $cpt_deb; $cpt <= $cpt_fin; $cpt++) {

            if ($cpt == ($this->limite / $this->nbByPages) + 1) {

                $barre .= '<span>' . $cpt . '</span>';
            } else {

                if ((($cpt - 1) * $this->nbByPages) == 0) {

                    $cible = $this->adresse;
                    $barre .= ' <a href="' . $cible . '" title="Page n°' . $cpt . '"><b>' . $cpt . '</b></a>  ';
                } else {

                    $cible = $this->adresse . $this->rajout . (($cpt - 1) * $this->nbByPages);
                    $barre .= ' <a href="' . $cible . '" title="Page n°' . $cpt . '"><b>' . $cpt . '</b></a> ';
                }
            }
        }

        // Page suivante
        if (($this->limite + $this->nbByPages) < $this->nbTotal) {
            $cible = $this->adresse . $this->rajout . ($this->limite + $this->nbByPages);
            $lien = ' <a href="' . $cible . '" title="Page suivante" class="next1"><i class="fa fa-angle-right fa-lg"></i></a> ';
        } else {
            $lien = '';
        }

        $barre .= ' ' . $lien;

        // Dernière page
        $fin = ($this->nbTotal - ($this->nbTotal % $this->nbByPages));

        if (($this->nbTotal % $this->nbByPages) == 0) {
            $fin = $fin - $this->nbByPages;
        }

        if ($fin != $this->limite) {
            $cible = $this->adresse . $this->rajout . $fin;
            $lien = ' <a href="' . $cible . '" title="Dernière page" class="next2"><i class="fa fa-angle-double-right fa-lg"></i></a> ';
        } else {
            $lien = '';
        }

        $barre .= $lien;

        $barre .= '</div>';

        // Accès direct
        if ($this->accesDirect == 'oui') {

            $lien = '<form method="POST" action="' . $this->adresse . '">'
                    . '<input type="text" size="3" name="page" />'
                    . '<input type="submit" value="Go" />'
                    . '</form>';

            $barre .= '<div class="direct">' . $lien . '</div>';
        }

        return $barre;
    }

    /**
     * Controle le nombre de resultat pour affichage ou non de la barre
     *
     * @return string
     */
    public function BarreNavigation() {

        if ($this->nbTotal > $this->nbByPages) {

            return $this->BarreNav();
        }
    }

}

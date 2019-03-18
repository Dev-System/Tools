<?php

/**
 * Class FormBootstrap | /core/FormBootstrap/FormBootstrap.php
 *
 * @package     EsayD
 * @subpackage  Core
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (15/02/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\FormBootstrap;

/**
 * Classe de génération des formulaires html, utilisant Bootstrap 4
 */
class FormBootstrap {

    /**
     * Renvoi l'élément html d'un formulaire à l'intérieur d'une div bootstrap
     * 
     * @param string $html
     * @return string
     */
    private static function surround($html) {
        return '<div class="row mb-sm-1 form-group">' . $html . '</div>';
    }

    /**
     * Renvoi un label
     * 
     * @param string $label
     * @param array $options
     */
    private static function label($label, $options = []) {

        if (!$label) {
            return null;
        }
        if (isset($options['labelClass'])) {
            $labelClass = $options['labelClass'];
        } else {
            $labelClass = 'col-sm-3 col-form-label col-form-label-sm text-sm-right';
        }

        $label = trim($label) ? $label . ' : ' : '';

        return '<label class="' . $labelClass . '">' . $label . '</label>';
    }

    /**
     * Renvoi un input du type définit dans $options (default = text)
     * 
     * @param string $name
     * @param string $label
     * @param string $value
     * @param array $options
     * @return sring
     */
    private static function input($type = 'text', $name, $value, $options = []) {

        $value = html_entity_decode($value);
        
        /** Class */
        if (isset($options['inputClass'])) {
            $inputClass = $options['inputClass'];
        } elseif ($type == 'date') {
            $inputClass = 'col-lg-2 col-md-3 col-sm-4 align-self-center';
        } elseif ($type == 'time') {
            $inputClass = 'col-auto align-self-center';
        } elseif ($type == 'number') {
            $inputClass = 'col-lg-2 col-md-3 col-sm-4 align-self-center';
        } elseif ($type == 'textarea') {
            $inputClass = 'col-sm-9 align-self-center';
        } else {
            $inputClass = 'col-md-6 col-sm-9 align-self-center';
        }

        $input = '<div class="' . $inputClass . '">';


        if ($type === 'infos') {
            $input .= $value;
        } elseif ($type === 'infosBoolean') {
            if (!$value) {
                $input .= '<i class="fas fa-times fa-lg text-danger" title="non"></i>';
            } else {
                $input .= '<i class="fas fa-check fa-lg text-success" title="oui"></i>';
            }
        } elseif ($type === 'textarea') {
            $input .= '<textarea name="' . $name . '" class="form-control"  id="' . $name . '" rows="5">' . $value . '</textarea>';
        } elseif ($type === 'password') {
            $input .= '<input type="' . $type . '" name="' . $name . '" value="" class="form-control form-control-sm" id="' . $name . '" autocomplete="off">';
        } else {
            $input .= '<input type="' . $type . '" name="' . $name . '" value="' . $value . '" class="form-control form-control-sm" id="' . $name . '">';
        }
        $input .= '</div>';

        return $input;
    }

    /**
     * Renvoi un checkbox de type switch (pour une valeur boolean)
     * 
     * @param string $name
     * @param string $label
     * @param booleen $active
     * @param array $options
     * @return string
     */
    private static function switchBoolean($name, $value, $options = []) {

        /** Class */
        if (isset($options['inputClass'])) {
            $inputClass = $options['inputClass'];
        } else {
            $inputClass = 'col-sm-9';
        }

        $checked = $value ? 'checked' : '';

        $input = '<div class="' . $inputClass . '">'
                . '<span class="switch switch-' . $name . '">'
                . '   <input type="checkbox" name="' . $name . '" class="switch" id="switch-' . $name . '" value="1" ' . $checked . '>'
                . ' <label for="switch-' . $name . '">&nbsp;</label>  '
                . '</span>'
                . '</div>';

        return $input;
    }

    /**
     * Renvoi un select
     * 
     * @param string $value
     * @param array $settings
     * @param array $options
     * @return string
     */
    private static function select($name, $value, $options = []) {

        $selectOptions = [];
        if (isset($options['selectOptions'])) {
            $selectOptions = $options['selectOptions'];
        }

        /** Class */
        if (isset($options['inputClass'])) {
            $inputClass = $options['inputClass'];
        } else {
            $inputClass = 'col-md-6 col-sm-9';
        }

        $input = '<div class="' . $inputClass . '">';
        $input .= '<select class="form-control form-control-sm" name="' . $name . '" id="' . $name . '">';
        $input .= '<option value="">Choisir...</option>';
        foreach ($selectOptions as $option) {
            $attributes = '';
            if ($option[0] == $value) {
                $attributes = 'selected';
            }
            $input .= '<option value="' . $option[0] . '" ' . $attributes . '>' . $option[1] . '</option>';
        }
        $input .= '</select>'
                . '</div>';

        return $input;
    }

    /**
     * Renvoi l'élément html corespondant à $type
     * 
     * @param string $type
     * @param string $value
     * @param array $settings
     * @param array $options
     * @return string
     */
    public static function getElem($type, $name, $label, $value, $options = []) {
        if ($type == 'input-text') {
            $input = self::input('text', $name, $value, $options);
        } else if ($type == 'input-password') {
            $input = self::input('password', $name, $value, $options);
        } else if ($type == 'input-email') {
            $input = self::input('email', $name, $value, $options);
        } else if ($type == 'infos') {
            $input = self::input('infos', $name, $value, $options);
        } else if ($type == 'infosBoolean') {
            $input = self::input('infosBoolean', $name, $value, $options);
        } else if ($type == 'switchBoolean') {
            $input = self::switchBoolean($name, $value, $options);
        } else if ($type == 'select') {
            $input = self::select($name, $value, $options);
        } else if ($type == 'input-date') {
            $input = self::input('date', $name, $value, $options);
        } else if ($type == 'input-dateTime') {
            $input = self::input('datetime', $name, $value, $options);
        } else if ($type == 'input-time') {
            $input = self::input('time', $name, $value, $options);
        } else if ($type == 'input-number') {
            $input = self::input('number', $name, $value, $options);
        } else if ($type == 'input-file') {
            $input = self::input('file', $name, $value, $options);
        } else if ($type == 'textarea') {
            $input = self::input('textarea', $name, $value, $options);
        }

        $html = self::label($label, $options) . $input;
        return self::surround($html);
    }

    public static function getTitleAndReturn($title, $link) {

        $html = '<div class="form-group row">'
                . '<div class="col-sm-6 pb-2">'
                . '<span class="h1">' . $title . '</span>'
                . '</div>'
                . '<div class="col-sm-6 pb-1 text-center text-sm-right">'
                . '<a href="' . $link . '" class="btn btn-outline-secondary">'
                . '<i class="fas fa-chevron-left"></i> Retour à la liste'
                . '</a>'
                . '</div>'
                . '</div>';

        return $html;
    }

    /**
     * Renvoi un submit de valeur "save"
     * 
     * @return string
     */
    public static function getSaveButton() {

        $html = '<button type="submit" name="submit" value="save" class="btn btn-success" onclick="topWindow();">'
                . '<i class="far fa-save"></i> Enregistrer'
                . '</button>';

        return $html;
    }

    /**
     * Renvoi un submit de valeur "delete"
     * 
     * @return string
     */
    public static function getDeleteButton($txtConfirmDelete = null) {

        $html = '<button type="button" class="btn btn-danger" data-confirm="' . $txtConfirmDelete . '" data-value="delete">'
                . '<i class="fas fa-trash-alt"></i> Supprimer'
                . '</button>';

        return $html;
    }
    
    /**
     * Renvoi un submit de valeur "delete"
     * 
     * @return string
     */
    public static function getConfirmDial(string $txtConfirmDelete = '', string $value = '') {

        $html = '<button type="button" class="btn btn-danger" data-confirm="' . $txtConfirmDelete . '" data-value="delete">'
                . '<i class="fas fa-trash-alt"></i> Supprimer'
                . '</button>';

        return $html;
    }

    /**
     * Renvoi un bloc commentaire avec toggle button
     * 
     * @param string $name
     * @param array $value
     * @param array $options
     * @return string
     */
    public static function getCommentBlock($name, $h2 = '', $value, $options = []) {

        $txt = $value ? $value : 'Aucun commentaire...';

        $html = '<h2>' . $h2 . '</h2>
                <div id="com">
                    <div class="collapse multi-collapse show" id="txt1">
                        <button class="btn btn-outline-secondary float-right" type="button" data-toggle="collapse" 
                                data-target=".multi-collapse" aria-expanded="false" aria-controls="txt1 txt2">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        ' . $txt . '
                    </div>
                    <div class="collapse multi-collapse" id="txt2">
                        <textarea name="' . $name . '" class="form-control" rows="5">' . $value . '</textarea>
                    </div>
                </div>';

        return $html;
    }

    /**
     * Renvoi un bloc "Infos données
     *
     * @param object $infos
     * @return string
     */
    public static function getRegistrationInfo($infos) {

        $html = '<h2>Infos données</h2>';

        /** Création de l'enregistrement */
        $add = $infos->date_add ? date('d/m/Y H:i:s', strtotime($infos->date_add)) : '';
        $add .= $infos->user_add ? ' - ' . $infos->user_add : '';
        $html .= self::getElem('infos', '', 'Création', $add);

        /** Dernière modification de l'enregistrement */
        $upd = $infos->date_upd ? date('d/m/Y H:i:s', strtotime($infos->date_upd)) : '';
        $upd .= $infos->user_upd ? ' - ' . $infos->user_upd : '';
        $html .= self::getElem('infos', '', 'Dernière modification', $upd);


        return $html;
    }

}

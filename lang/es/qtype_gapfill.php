<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
/**
 * The language strings for component 'qtype_gapfill', language 'en' 
 *    
 * @copyright &copy; 2012 Marcus Green
 * @author marcusavgreen@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package qtype
 * @subpackage gapfill
 */

$string['addinggapfill']='Agregando espacios para completar';
$string['casesensitive']='entre mayúsculas y minúsculas';
$string['casesensitive_help']='Cuando esta opción está activada, si la respuesta correcta es la del Gato, se marcará como una respuesta incorrecta ';
$string['noduplicates']='Sin duplicados';
$string['noduplicates_help']='Cuando se activa, cada respuesta debe ser útil único, donde cada campo tiene un operador |, es decir, cuáles son los colores de las medallas olímpicas y cada campo tiene [oro | plata | bronce], sólo si el estudiante entra en oro en todos los campos de la primera recibirá una marca (los otros seguirán recibiendo una garrapata sin embargo). En realidad es más bien descartar duplicar respuestas para fines de marcado ';

$string['delimitchars']='Delimitar signos o caracteres';
$string['pluginnameediting'] = 'Editando espacios para completar';
$string['pluginnameadding'] = 'Agregando una pregunta con espacios para completar';

$string['gapfill'] = 'Gapfill tipo de pregunta';

$string['displaygapfill'] = 'gapfill';
$string['displaydropdown'] = 'dropdown';
$string['displaydragdrop'] = 'dragdrop';

$string['pluginname']="Pregunta de tipo con espacios para completar";
$string['pluginname_help'] = 'Coloque las palabras para ser completado dentro de paréntesis cuadrados, por ejemplo El [cat] se sentó en el [MAT]';

$string['pluginname_link']='/question/type/gapfill';
$string['pluginnamesummary'] = 'Una pregunta del tipo complete los espacios. Menos dispositivos que el tipo Cloze estándar pero con sintaxis mas simple.';
$string['delimitchars_help']='Cambio de los caracteres que delimitan un campo de la omisión [], útil para la programación de las cuestiones lingüísticas';
$string['answerdisplay']='Mostrar respuestas';
$string['answerdisplay_help'] = 'Si se marca esto, cada campo será cambiado en un desplegable que contiene todas las respuestas para todos los campos';
$string['pleaseenterananswer']='Por favor ponga una respuesta';
$string['wronganswers']='respuestas incorrectas.';
$string['wronganswers_help']='Lista de palabras incorrectas separados por comas, se aplica únicamente en dragdrop / modo de menús desplegables';



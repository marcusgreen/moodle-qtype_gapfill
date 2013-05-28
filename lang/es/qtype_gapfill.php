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

$string['addinggapfill']='Agregar rellenar espacios en blanco';
$string['casesensitive']='Mayúscula y minúscula';
$string['casesensitive_help']='Cuando esto está seleccionado, si la respuesta correcta es “GATO”, entonces “gato” se marcará como respuesta incorrecta.';
$string['noduplicates']='Sin duplicados';
$string['noduplicates_help']='Cuando esto está seleccionado, cada respuesta debe ser única. Útil donde cada campo tenga un operador | , por ejemplo: “Cuáles son los colores de la medallas olímpicas” y cada campo tiene [oro] [plata] [bronce], si un estudiante entra oro en cada campo solamente el primero obtendrá una nota (aunque los otros también obtendrán una palomita). Es más bien para descartar las respuestas duplicadas al calificar';

$string['delimitchars']='Delimita los caracteres ';
$string['pluginnameediting'] = 'Editar rellenar espacio en blanco';
$string['pluginnameadding'] = 'Agregar una pregunta de rellenar espacio en blanco';

$string['gapfill'] = 'Relleno espacio tipo Cloze';

$string['displaygapfill'] = 'Rellenar espacio';
$string['displaydropdown'] = 'desplegable';
$string['displaydragdrop'] = 'Arrastrar/ soltar';

$string['pluginname']="Pregunta de rellenar espacio en blanco";
$string['pluginname_help'] = 'Ponga las palabras que necesitan ser completadas entre corchetes; por ejemplo: El [gato] se sentó sobre [la alfombra].  Los modos desplegables y arrastrar/soltar permiten  mostrar una lista de respuestas mezcladas que también pueden incluir opcionales respuestas incorrectas o de distracción.';

$string['pluginname_link']='/question/type/gapfill';
$string['pluginnamesummary'] = 'Pregunta del tipo de rellenar espacio en blanco.  Menos funciones que las clásicas de tipo Cloze, pero con una sintaxis más simple. ';
$string['delimitchars_help']='Cambia los caracteres que delimitan un campo del predeterminado [ ], útil para las preguntas de lenguaje de programación';
$string['questionsmissing']='Usted no se ha incluido ningún campo en el texto de la pregunta';

$string['answerdisplay']='Muestra las respuestas';
$string['answerdisplay_help'] = 'Si está seleccionado, esto convertirá cada campo en un menú de desplegable que contiene todas las respuestas para cada campo.';
$string['pleaseenterananswer']='Por favor introduzca una respuesta';
$string['wronganswers']='Respuestas incorrectas.';
$string['wronganswers_help']='Lista de la palabras incorrectas separadas por comas, solamente es relevante en los  modos de arrastrar/soltar y desplegables';



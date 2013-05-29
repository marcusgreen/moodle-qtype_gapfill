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
 * @copyright &copy; 2013 Marcus Green
 * @author marcusavgreen@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package qtype
 * @subpackage gapfill
 */

$string['addinggapfill']='Ajoutez une question de Gap Fill';
$string['casesensitive']='Sensible à la casse';
$string['casesensitive_help']='Quand cette option est cochée, si la bonne réponse est CAT, cat sera considérée comme une mauvaise réponse';
$string['noduplicates']='Sans copies';
$string['noduplicates_help']="Quand elle est cochée, chaque réponse doit être unique, utile, où chaque champ a un opérateur |, c'est à dire quelles sont les couleurs des médailles olympiques et chaque champ est [d'or | argent | bronze], si l'élève entre l'or dans tous les champs, seulement la première obtenir une marque (les autres obteniront toujours une coche cependant). Dans le but de faire le marquage, il est vraiment plus comme 'rejeter les copies de réponses'.";

$string['delimitchars']='Délimiter les caractères ';
$string['pluginnameediting'] = 'Modification de Gap Fill. ';
$string['pluginnameadding'] = "Ajout d'une question Gap Fill";

$string['gapfill'] = 'Gapfill Cloze. .';

$string['displaygapfill'] = 'Gapfill';
$string['displaydropdown'] = 'Déroulant';
$string['displaydragdrop'] = 'glisser-déposer';

$string['pluginname']="Gapfill question type";
$string['pluginname_help'] = "Placez les mots pour être achevé dans les crochets, par exemple Le [cat] s'assit sur le [mat]. Modes de liste déroulante et glisser-déposer permettent à exposer une liste de réponses mélangées qui peut inclure en option de mauvaises réponses / réponses distracteur.";

$string['pluginname_link']='/question/type/gapfill';
$string['pluginnamesummary'] = "Un question de style 'remplir lacunes'. Moins de fonctionnalités que le type standard Cloze, mais aveec syntaxe plus simple.";
$string['questionsmissing']='You have not included any fields in your question text';

$string['delimitchars_help']="Changez les caractères qui délimitent un champ de la valeur par défaut [ ]. Il est utile pour des questions de langage de programmation d'ordinateur.";
$string['answerdisplay']='Exposez Réponses ';
$string['answerdisplay_help'] = 'Dragdrop shows a list of words that can be dragged into the gaps, gapfill shows gaps with no word options, dropdown shows the same list of correct (and possibly incorrect) answers for each field';
$string['moreoptions']='More Options.';
$string['pleaseenterananswer']="S'il vous plaît entrer une réponse";
$string['wronganswers']='Les mauvaises réponses.';
$string['wronganswers_help']="Liste des mots incorrects séparés par des virgules, ne s'applique que dans les modes de glisser-déposer / de listes déroulantes.";
$string['yougotnrightcount'] = 'Your number of correct answers is {$a->num}.';



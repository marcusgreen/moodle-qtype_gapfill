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

$string['addinggapfill']='Adicionar preenchimento de espaço em branco';
$string['casesensitive']='Maiúsculas e Minúsculas';
$string['casesensitive_help']='Quando esta opção está activada, se a resposta correta for CAT, cat será marcado como uma resposta incorrecta';
$string['noduplicates']='Sem Duplicados';
$string['noduplicates_help']='Quando esta opção está activada, cada resposta deve ser única, isto poderá ser útil onde um campo tenha um operador |, por exemplo: "Quais são as cores da medalhas Olímpicas" e cada campo tem [ouro | prata | bronze], se o aluno inserir ouro em todos os campos apenas o primeiro será avaliado (os outros ainda terão um visto no entanto). Na realidade trata-se de descartar respostas duplicadas para fins de avaliação.';


$string['delimitchars']='Demarcar caracteres';
$string['pluginnameediting'] = 'Editar preenchimento de espaço em branco';
$string['pluginnameadding'] = 'Adicionar uma pergunta com espaço em branco';

$string['gapfill'] = 'Preenchimento de espaço em branco Cloze';

$string['displaygapfill'] = 'gapfill';
$string['displaydropdown'] = 'escolha multipla';
$string['displaydragdrop'] = 'arrastar e largar';

$string['pluginname']="Tipo de pergunta de preenchimento de espaço em branco";
$string['pluginname_help'] = 'Coloque as palavras para serem finalizadas dentro de parentises retos, por exemplo O [gato] sentou-se no [tapete]. Modos de escolha multipla e arrastar e largar permite uma lista de respostas embaralhadas a ser exibido o que pode incluir opcionais respostas erradas / de distracção.';

$string['pluginname_link']='/question/type/gapfill';
$string['pluginnamesummary'] = 'Um tipo de pergunta de preenchimento de espaço em branco. Menos capacidades que o tipo Cloze normal, mas com um sintaxe mais simples.';
$string['questionsmissing']='Você não incluiu todos os campos em seu texto da pergunta
';

$string['delimitchars_help']='Altera os caracteres que demarcam um campo dos pre-definidos [], útil para perguntas sobre linguagens de programação';
$string['answerdisplay']='Mostrar Repostas';
$string['answerdisplay_help'] = 'Dragdrop shows a list of words that can be dragged into the gaps, gapfill shows gaps with no word options, dropdown shows the same list of correct (and possibly incorrect) answers for each field';
$string['moreoptions']='More Options.';
$string['pleaseenterananswer']='Por favor introduza a resposta';
$string['wronganswers']='Respostas erradas.';
$string['wronganswers_help']='Lista de palavras incorretas separadas por virgulas, é aplicado apenas no modo de arrastar e largar';
$string['yougotnrightcount'] = 'Your number of correct answers is {$a->num}.';



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
 * Create training form class
 *
 * @copyright 2024 IFRASS
 * @author    2024 Esteban BIRET-TOSCANO <esteban.biret@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   training_architecture
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/config.php');
require_once(dirname(__FILE__) . '/../functions/training_functions.php');

class create_training extends moodleform {

    function definition () {

        $mform =$this->_form;

        $mform->addElement('header', 'createTraining', get_string('createTraining', 'local_training_architecture'));

        $mform->addElement('text','trainingFullName', get_string('fullName', 'local_training_architecture'),'maxlength="255" size="50"');
        $mform->addRule('trainingFullName', get_string('required'), 'required');
        $mform->setType('trainingFullName', PARAM_TEXT);

        $mform->addElement('text','trainingShortName', get_string('shortName', 'local_training_architecture'), 'maxlength="50" size="20"');
        $mform->addRule('trainingShortName', get_string('required'), 'required');
        $mform->setType('trainingShortName', PARAM_TEXT);

        $mform->addElement('text', 'trainingIDNumber', get_string('IDNumber', 'local_training_architecture'), 'maxlength="15" size="15"');
        $mform->addHelpButton('trainingIDNumber', 'IDNumber', 'local_training_architecture');
        $mform->setType('trainingIDNumber', PARAM_TEXT);

        $mform->addElement('editor','trainingDescription', get_string('description', 'local_training_architecture'));
        $mform->setType('trainingDescription', PARAM_RAW);

        $levels = ['' => get_string('chooseOption', 'local_training_architecture'), '1' => '1', '2' => '2'];
        $mform->addElement('select', 'trainingLevel', get_string('numberOfLevel', 'local_training_architecture'), $levels);
        $mform->addHelpButton('trainingLevel', 'createTrainingGranularityLevel', 'local_training_architecture');
        $mform->addRule('trainingLevel', get_string('required'), 'required');

        $semesterChoice = [
            '' => get_string('chooseOption', 'local_training_architecture'),
            'yes' => get_string('yes', 'local_training_architecture'),
            'no' => get_string('no', 'local_training_architecture')
        ];
        
        $mform->addElement('select', 'trainingSemester', get_string('semesterChoice', 'local_training_architecture'), $semesterChoice);
        $mform->addRule('trainingSemester', get_string('required'), 'required');
        $mform->setType('trainingSemester', PARAM_ALPHA);

        $tableId = 'training-table-container';

        $mform->addElement('html', '<div class="custom-collapsible">
            <span class="show-hide-table-title">' . get_string('collapse', 'local_training_architecture') . '</span>
            <input type="text" class="search-input-training-architecture" id="search-input-training" data-table-id="' . $tableId . '" placeholder="' . get_string('search') . '">
            <div id="'. $tableId .'" class="table-container-training-architecture" style="display: block;"></div>
        </div>');

        $this->add_action_buttons();
        
    }

    /**
     * Form validation
     *
     * @param array $data
     * @param array $files
     * @return array $errors An array of errors
     */
    function validation($data, $files) {

        global $DB;

        $trainingFunctions = new training_functions();
        
        $errors = parent::validation($data, $files);

        // Empty required fields
        if (empty($data['trainingFullName'])) {
            $errors['trainingFullName'] = get_string('required');
        }
        if (empty($data['trainingShortName'])) {
            $errors['trainingShortName'] = get_string('required');
        }
        if (empty($data['trainingLevel'])) {
            $errors['trainingLevel'] = get_string('required');
        }
        if (empty($data['trainingSemester'])) {
            $errors['trainingSemester'] = get_string('required');
        }

        // Handle duplicates values
        if (!empty($data['trainingIDNumber'])) {
            if ($DB->record_exists_select('local_training_architecture_training', 'idnumber = ?', [str_replace(' ', '', $data['trainingIDNumber'])])) {
                $errors['trainingIDNumber'] = get_string('IDNumberAlreadyExists', 'local_training_architecture');
            }        
        }

        if(empty($errors)) {

            /*if ($DB->record_exists_select('local_training_architecture_training', 'LOWER(fullname) = LOWER(?)', [trim($data['trainingFullName'])])) {
                $errors['trainingFullName'] = get_string('nameAlreadyExists', 'local_training_architecture');
            }*/
            if ($DB->record_exists_select('local_training_architecture_training', 'LOWER(shortname) = LOWER(?)', [trim($data['trainingShortName'])])) {
                $errors['trainingShortName'] = get_string('shortNameAlreadyExists', 'local_training_architecture');
            }
            if($errors) {
                return $errors;
            }

            $trainingFunctions->create($data);
        }
        return $errors;
    }

}
<?php

namespace mod_coursework\forms;
use mod_coursework\models\coursework;

/**
 * Class deadline_extension_form is responsible for new and edit actions related to the
 * deadline_extensions.
 *
 */
class deadline_extension_form extends \moodleform {

    /**
     * Form definition.
     */
    protected function definition() {

        $this->_form->addElement('hidden', 'allocatabletype');
        $this->_form->settype('allocatabletype', PARAM_ALPHANUMEXT);
        $this->_form->addElement('hidden', 'allocatableid');
        $this->_form->settype('allocatableid', PARAM_INT);
        $this->_form->addElement('hidden', 'courseworkid');
        $this->_form->settype('courseworkid', PARAM_INT);
        $this->_form->addElement('hidden', 'id');
        $this->_form->settype('id', PARAM_INT);


        // Current deadline for comparison
        $this->_form->addElement('html', '<div class="alert">Default deadline: '. userdate($this->get_coursework()->deadline).'</div>');

        // Date and time picker
        $this->_form->addElement('date_time_selector', 'extended_deadline', get_string('extended_deadline',
                                                                                       'mod_coursework'));

        $extension_reasons = coursework::extension_reasons();
        if (!empty($extension_reasons)) {
            $this->_form->addElement('select',
                                     'pre_defined_reason',
                                     get_string('extension_reason',
                                                'mod_coursework'),
                $extension_reasons);
        }

        $this->_form->addElement('editor', 'extra_information', get_string('extra_information', 'mod_coursework'));
        $this->_form->setType('extra_information', PARAM_RAW);

        // Submit button
        $this->add_action_buttons();
    }

    private function get_coursework() {
        return $this->_customdata['coursework'];
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $CFG;
        $max_deadline = $CFG->coursework_max_extension_deadline;

        $errors = array();
        if ($data['extended_deadline'] <= $this->get_coursework()->deadline) {
            $errors['extended_deadline'] = 'The new deadline must be later than the current deadline';
        }
        if ($data['extended_deadline'] >= strtotime("+$max_deadline months", $this->get_coursework()->deadline)) {
            $errors['extended_deadline'] = "The new deadline must not be later than $max_deadline months after the current deadline";
        }
        return $errors;
    }


}
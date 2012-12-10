<?php

// 20111121 by CITE - migrate to Moodle 2.0

class block_poll_edit_form extends block_edit_form {

  protected function specific_definition($mform) {
    global $CFG, $DB, $USER, $COURSE, $PAGE;

    $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

    // -------------

    $mform->addElement('text', 'config_customtitle', get_string('customtitle', 'block_poll'), array('size' => 50));
    $mform->setType('config_customtitle', PARAM_MULTILANG);

    // -------------

    $availpolls = $DB->get_records('block_poll', array('courseid'=>$COURSE->id));

    $pollmenu = array();
    if ($availpolls) {
      $pollmenu[0] = get_string('choose', 'block_poll');
      foreach ($availpolls as $poll) {
        $pollmenu[$poll->id] = $poll->name;
      }
    }

    if ($pollmenu) {
      $select = $mform->addElement('select', 'config_pollid', get_string('editpollname', 'block_poll'), $pollmenu);
      $select->setMultiple(false);

    } else {
      $mform->addElement('static', 'config_pollid', get_string('editpollname', 'block_poll'), get_string('nopolllabel', 'block_poll'));
    }

    // -------------

    $urlparams = array();

    $urlparams['courseid'] = $this->page->course->id;
    $urlparams['instanceid'] = optional_param('bui_editid', 0, PARAM_INTEGER);
    $urlparams['sesskey'] = $USER->sesskey;
    $urlparams['blockaction'] = 'config';
    $urlparams['manageaction'] = '';

    $url = new moodle_url('/blocks/poll/managepolls.php', $urlparams);

    $mform->addElement('static', 'pollsaddedit', '',
        '<a href="' . $url . '">' . get_string('pollsaddedit', 'block_poll') . '</a>');

    }
}

<?php

  // 20111121 by CITE - migrate to Moodle 2.0

  global $CFG, $DB, $PAGE, $USER, $COURSE, $OUTPUT;
  require_once(dirname(__FILE__) . '/../../config.php');
  require_once($CFG->libdir . '/tablelib.php');
  include_once("$CFG->dirroot/blocks/poll/lib.php");

  require_login();

  $courseid = optional_param('courseid', 0, PARAM_INTEGER);
  $instanceid = optional_param('instanceid', 0, PARAM_INTEGER);
  $manageaction = optional_param('manageaction', 'editpoll', PARAM_ALPHA);

  if ($courseid == SITEID) {
      $courseid = 0;
  }
  if ($courseid) {
      $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
      $PAGE->set_course($course);
      $context = $PAGE->context;
  } else {
      $context = get_context_instance(CONTEXT_SYSTEM);
      $PAGE->set_context($context);
  }

  if (!has_capability('block/poll:editpoll', $context)) {
      require_capability('block/poll:editpoll', $context);
  }

  // ------------ page url ------------

  $urlparams = array();

  $urlparams['courseid'] = $courseid;
  $urlparams['instanceid'] = $instanceid;
  $urlparams['sesskey'] = $USER->sesskey;
  $urlparams['blockaction'] = 'config';
  $urlparams['manageaction'] = '';

  $url = new moodle_url('/blocks/poll/managepolls.php', $urlparams);
  $PAGE->set_url($url);

  // ------------ nav url ------------

  $navurlparams = array();

  $navurlparams['courseid'] = $courseid;
  $navurlparams['instanceid'] = $instanceid;
  $navurlparams['sesskey'] = $USER->sesskey;
  $navurlparams['blockaction'] = 'config';
  $navurlparams['manageaction'] = '';

  $managepolls = new moodle_url('/blocks/poll/managepolls.php', $navurlparams);

  // ----------------------------------

  $strmanage = get_string('managepolls', 'block_poll');

  $PAGE->set_pagelayout('standard');
  $PAGE->set_title($strmanage);
  $PAGE->set_heading($strmanage);

  $PAGE->navbar->add(get_string('blocks'));
  $PAGE->navbar->add($strmanage, $managepolls);

  echo $OUTPUT->header();

  // ----------------------------------

  $blockurlparams = array();

  $blockurlparams['id'] = $courseid;
  $blockurlparams['sesskey'] = $USER->sesskey;
  $blockurlparams['bui_editid'] = $instanceid;

  $blockurl = new moodle_url('/course/view.php', $blockurlparams);


  echo '<p align=RIGHT >'.html_writer::tag('a', get_string('backtoconfig', 'block_poll') , array('href' => $blockurl)).'</p>';

  //$manageaction = 'responses'; // testing used - ** 'editpoll', 'managepolls', 'responses'

  // ------------ tab url ------------
  $tabsurlparams = array();
  $tabsurlparams['courseid'] = $courseid;
  $tabsurlparams['sesskey'] = $USER->sesskey;
  $tabsurlparams['instanceid'] = $instanceid;
  $tabsurlparams['blockaction'] = 'config';
  $tabsurlparams['manageaction'] = '';

  $tabs = array();
  $tabnames = array('editpoll', 'managepolls', 'responses');

  foreach ($tabnames as $tabname) {
    $tabsurlparams['manageaction'] = $tabname;
    $tabs[] = new tabObject($tabname, new moodle_url('/blocks/poll/managepolls.php', $tabsurlparams), get_string('tab'.$tabname, 'block_poll'));
  }

  if (!in_array($manageaction, $tabnames)) {
    $manageaction = 'editpoll';
  }

  print_tabs(array($tabs), $manageaction);

  include("tab_$manageaction.php");

  echo $OUTPUT->footer();

<?php
  // 20111121 by CITE - migrate to Moodle 2.0
  // Paul Holden 24th July, 2007
  // contains the code that controls polls and responses

  require_once('../../config.php');

  require_login();
  
  $manageaction = required_param('manageaction', PARAM_ALPHA);
  $pid = optional_param('pid', 0, PARAM_INTEGER);
  $courseid = required_param('courseid', PARAM_INTEGER);
  if ($courseid == 0) $courseid = 1;
  $instanceid = optional_param('instanceid', 0, PARAM_INTEGER);

  $sesskey = $USER->sesskey;
  
  if ($courseid) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
  } else {
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
  }
  
  function test_allowed_to_update($context) {
    // TODO: Proper roles & capabilities
    if (!has_capability('block/poll:editpoll', $context)) {
      print_error(get_string('pollwarning', 'block_poll'));
    }
  }
  
  $urlparams = array();
  $urlbase='/blocks/poll/managepolls.php';

  switch ($manageaction) {
    case 'create':
    test_allowed_to_update($context);
    $poll = new Object();
    $poll->name = required_param('name', PARAM_TEXT);
    $poll->courseid = $courseid;
    $poll->questiontext = required_param('questiontext', PARAM_TEXT);
    $poll->eligible = required_param('eligible', PARAM_TEXT);
    $poll->created = time();
    $newid = $DB->insert_record('block_poll', $poll, true);
    $optioncount = optional_param('optioncount', 0, PARAM_INTEGER);
    for ($i = 0; $i < $optioncount; $i++) {
      $pollopt = new Object();
      $pollopt->pollid = $newid;
      $pollopt->optiontext = '';
      $DB->insert_record('block_poll_option', $pollopt);
    }
    $urlparams['courseid'] = $courseid;
    $urlparams['instanceid'] = $instanceid;
    $urlparams['sesskey'] = $sesskey;
    $urlparams['blockaction'] = 'config';
    $urlparams['manageaction'] = 'editpoll';
    $urlparams['pid'] = $newid;
    break;

    case 'edit':
    test_allowed_to_update($context);
    $poll = $DB->get_record('block_poll', array('id'=>$pid));
    $poll->name = required_param('name', PARAM_TEXT);
    $poll->questiontext = required_param('questiontext', PARAM_TEXT);
    $poll->eligible = required_param('eligible', PARAM_TEXT);
    $DB->update_record('block_poll', $poll);
    $options = optional_param_array('options', array(), PARAM_RAW);
    foreach (array_keys($options) as $option) {
      $pollopt = $DB->get_record('block_poll_option', array('id'=>$option));
      $pollopt->optiontext = $options[$option];
      $DB->update_record('block_poll_option', $pollopt);
    }
    $optioncount = optional_param('optioncount', 0, PARAM_INTEGER);
    if (count($options) > $optioncount) {
      $temp = 1;
      foreach ($options as $optid => $optname) {
        if ($temp++ > $optioncount) break;
        $safe[] = $optid;
      }
      $DB->delete_records_select('block_poll_option', "pollid = $pid AND id NOT IN (" . implode($safe, ',') . ")");
    }
    for ($i = count($options); $i < $optioncount; $i++) {
      $pollopt = new Object();
      $pollopt->pollid = $pid;
      $pollopt->optiontext = '';
      $DB->insert_record('block_poll_option', $pollopt);
    }
    $urlparams['courseid'] = $courseid;
    $urlparams['instanceid'] = $instanceid;
    $urlparams['sesskey'] = $sesskey;
    $urlparams['blockaction'] = 'config';
    $urlparams['manageaction'] = 'editpoll';
    $urlparams['pid'] = $pid;
    break;

    case 'delete':
    test_allowed_to_update($context);
    $step = optional_param('step', 'first', PARAM_TEXT);
    
    $urlnoparams = array();
    $urlnoparams['courseid'] = $courseid;
    $urlnoparams['instanceid'] = $instanceid;
    $urlnoparams['sesskey'] = $sesskey;
    $urlnoparams['blockaction'] = 'config';
    $urlnoparams['manageaction'] = 'managepolls';

    if ($step == 'confirm') {
      $DB->delete_records('block_poll_response',array('pollid'=>$pid));
      $DB->delete_records('block_poll_option', array('pollid'=>$pid));
      $DB->delete_records('block_poll', array('id'=>$pid));
      $urlparams = $urlnoparams;
    } else {
      $poll = $DB->get_record('block_poll', array('id'=>$pid));
      $urlyesparams = array();
      $urlyesparams['courseid'] = $courseid;
      $urlyesparams['instanceid'] = $instanceid;
      $urlyesparams['sesskey'] = $sesskey;
      $urlyesparams['manageaction'] = 'delete';
      $urlyesparams['step'] = 'confirm';
      $urlyesparams['pid'] = $pid;

      echo $OUTPUT->confirm(get_string('pollconfirmdelete', 'block_poll'),
        new moodle_url('/blocks/poll/poll_action.php', $urlyesparams),
        new moodle_url('/blocks/poll/managepolls.php', $urlnoparams));

      die();
    }
    break;

    case 'respond':
    if (!$DB->get_record('block_poll_response', array('pollid'=>$pid, 'userid'=>$USER->id))) {
      $response = new Object();
      $response->pollid = $pid;
      $response->optionid = required_param('rid', PARAM_INTEGER);
      $response->userid = $USER->id;
      $response->submitted = time();
      $DB->insert_record('block_poll_response', $response);
      $urlparams['id'] = $courseid;
      $urlbase = '/course/view.php';
    }
    break;

  }

  $url = new moodle_url($urlbase, $urlparams);

  redirect($url);
?>

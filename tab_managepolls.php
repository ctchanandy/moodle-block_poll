<?php
  // 20111121 by CITE - migrate to Moodle 2.0
  // Paul Holden 24th July, 2007
  // poll block; poll management tab

  global $DB, $PAGE, $OUTPUT;

  // --------- functions ------------------

  function print_action($action, $url) {
    global $OUTPUT;
    return "<a href=\"$url\"><img src=\"".$OUTPUT->pix_url('t/'.$action)."\" alt=\"\" /></a> ";
  }

  // --------------------------------------


  $polls = $DB->get_records('block_poll', array('courseid'=>$PAGE->course->id));

  $table = new html_table();
  $table->head = array(get_string('editpollname', 'block_poll'),
    get_string('editpolloptions', 'block_poll'),
    get_string('responses', 'block_poll'),
    get_string('action'));
  $table->align = array('left', 'right', 'right', 'left');
  $table->tablealign = 'left';
  $table->width = '*';

  // ----- url params for table content --

  $preurlparams = $urlparams;
  $preurlparams['action'] = 'responses';

  $ediurlparams = $urlparams;
  $ediurlparams['action'] = 'editpoll';

  $delurlparams = array();
  $delurlparams['courseid'] = $COURSE->id;
  $delurlparams['instanceid'] = optional_param('instanceid', 0, PARAM_INTEGER);
  $delurlparams['action'] = 'delete';

  // --------------------------------------

  if ($polls) {
    foreach ($polls as $poll) {

      $options = $DB->get_records('block_poll_option', array('pollid'=>$poll->id));
      $responses = $DB->get_records('block_poll_response', array('pollid'=>$poll->id));

      $preurlparams['pid'] = $poll->id;
      $ediurlparams['pid'] = $poll->id;
      $delurlparams['pid'] = $poll->id;

      $action = print_action('preview', new moodle_url('/blocks/poll/managepolls.php', $preurlparams)) .
        print_action('edit', new moodle_url('/blocks/poll/managepolls.php', $ediurlparams)) .
        print_action('delete', new moodle_url('/blocks/poll/poll_action.php', $delurlparams));

      $table->data[] = array($poll->name, (!$options ? '0' : count($options)), (!$responses ? '0' : count($responses)), $action);
    }
  }

  echo html_writer::table($table);

?>

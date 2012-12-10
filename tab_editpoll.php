<script language="javascript" type="text/javascript">
 function show_poll_results(id) {
  window.location.href="<?php echo(str_replace('&amp;', '&', $url) . 'editpoll&pid='); ?>" + id;
 }
</script>

<?php
  // 20111121 by CITE - migrate to Moodle 2.0
  // Paul Holden 24th July, 2007
  // poll block; poll creating/editing tab
  global $DB, $PAGE, $OUTPUT;

  $courseid = optional_param('courseid', 0, PARAM_INTEGER);
  $pid = optional_param('pid', 0, PARAM_INTEGER);
  $instanceid = optional_param('instanceid', 0, PARAM_INTEGER);

  $polls = $DB->get_records('block_poll', array('courseid'=>$COURSE->id));
  
  $menu = array();

  if ($polls) {
    foreach ($polls as $poll) {
      $menu[$poll->id] = $poll->name;
    }
  }


  print $OUTPUT->box_start('generalbox boxaligncenter');

  echo(get_string('editpollname', 'block_poll') . ': ');
  echo html_writer::select($menu, 'pid', $pid, array('0'=>get_string('choose', 'block_poll')), array('onchange'=>'show_poll_results(this.options[this.selectedIndex].value);'));

  print $OUTPUT->box_end();

  $poll = $DB->get_record('block_poll', array('id'=>$pid));
  $poll_options = $DB->get_records('block_poll_option', array('pollid'=>$pid));
  $poll_option_count = (!$poll_options ? 0 : count($poll_options));

?>

<form method="post" action="<?php echo($CFG->wwwroot); ?>/blocks/poll/poll_action.php">
<input type="hidden" name="pid" value="<?php echo($pid); ?>" />
<input type="hidden" name="manageaction" value="<?php echo($pid == 0 ? 'create' : 'edit'); ?>" />
<input type="hidden" name="instanceid" value="<?php echo($instanceid); ?>" />
<input type="hidden" name="sesskey" value="<?php echo($USER->sesskey) ;?>" />
<input type="hidden" name="blockaction" value="config" />
<input type="hidden" name="courseid" value="<?php echo($COURSE->id); ?>" />

<?php

  $eligible = array('all' => get_string('all'), 'students' => get_string('students'), 'teachers' => get_string('teachers'));
  for ($i = 1; $i <= 20; $options[$i++] = ($i - 1)) {}

  $table = new html_table();
  $table->head = array(get_string('config_param', 'block_poll'), get_string('config_value', 'block_poll'));
  $table->tablealign = 'left';
  $table->width = '*';

  $table->data[] = array(get_string('editpollname', 'block_poll'), '<input type="text" name="name" value="' . (!$poll ? '' : $poll->name) . '" />');
  $table->data[] = array(get_string('editpollquestion', 'block_poll'), '<input type="text" name="questiontext" value="' . (!$poll ? '' : $poll->questiontext) . '" />');
  $table->data[] = array(get_string('editpolleligible', 'block_poll'), html_writer::select($eligible, 'eligible', $poll ? $poll->eligible:'', array('0'=>get_string('choose', 'block_poll'))));
  $table->data[] = array(get_string('editpolloptions', 'block_poll'), html_writer::select($options, 'optioncount', $poll ? $poll_option_count:'', array('0'=>get_string('choose', 'block_poll'))));

  $option_count = 0;

  if ($poll_options) {
     foreach ($poll_options as $option) {
        $option_count++;
        $table->data[] = array(get_string('option', 'block_poll') . " $option_count", "<input type=\"text\" name=\"options[$option->id]\" value=\"$option->optiontext\" />");
     }
  }

  for ($i = $option_count + 1; $i <= $poll_option_count; $i++) {
    $table->data[] = array(get_string('option', 'block_poll') . " $i", '<input type="text" name="newoptions[]" />');
  }

  $table->data[] = array('&nbsp;', '<input type="submit" value="' . get_string('savechanges') . '" />');

  echo html_writer::table($table);

?>
</form>
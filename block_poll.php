<?php
// 20111121 by CITE - migrate to Moodle 2.0
// Paul Holden 24th July, 2007
// this file contains the poll block class

include_once("$CFG->dirroot/blocks/poll/lib.php");

class block_poll extends block_base {

  var $poll, $options;

  function init() {
    $this->title = get_string('formaltitle', 'block_poll');
  }

  function instance_allow_multiple() {
      return true;
  }

  function instance_allow_config() {
    return true;
  }

  function specialization() {
    if (!empty($this->config) && !empty($this->config->customtitle)) {
      $this->title = $this->config->customtitle;
    } else {
      $this->title = get_string('formaltitle', 'block_poll');
    }
  }

  function preferred_width() {
    return 140;
  }

  function poll_can_edit() {
    // TODO: Proper roles & capabilities
    return has_capability('block/poll:editpoll', $this->context);
  }

  function poll_user_eligible() {
    // TODO: Proper roles & capabilities
    if ($this->poll->eligible == 'all') {
      return has_capability('block/poll:polleligibleforall', $this->context);
    } else if($this->poll->eligible == 'teachers') {
      return has_capability('block/poll:polleligibleforteachers', $this->context);
    }else if($this->poll->eligible == 'students') {
      return has_capability('block/poll:polleligibleforstudents', $this->context);
    }

    return false;
  }

  function poll_results_link() {
    global $USER;

    $urlparams = array();
    $urlparams['courseid'] = $this->page->course->id;
    $urlparams['instanceid'] = $this->instance->id;
    $urlparams['sesskey'] = $USER->sesskey;
    $urlparams['blockaction'] = 'config';
    $urlparams['manageaction'] = 'responses';
    $urlparams['pid'] = empty($this->poll) ? '' : $this->poll->id;

    $url = new moodle_url('/blocks/poll/managepolls.php', $urlparams);

    return "<hr />(<a href=\"$url\">" . get_string('responses', 'block_poll') . '</a>)';
  }

  function poll_print_options() {

    global $CFG;

    $this->content->text .= '<form method="get" action="' . $CFG->wwwroot . '/blocks/poll/poll_action.php">
      <input type="hidden" name="manageaction" value="respond" />
      <input type="hidden" name="pid" value="' . $this->poll->id . '" />
      <input type="hidden" name="courseid" value="' . $this->page->course->id . '" />';

    foreach ($this->options as $option) {
      $this->content->text .= "<tr><td><input type=\"radio\" id=\"r_$option->id\" name=\"rid\" value=\"$option->id\" />
        <label for=\"r_$option->id\">$option->optiontext</label></td></tr>";
    }

    $this->content->text .= '<tr><td><input type="submit" value="' . get_string('submit', 'block_poll') . '" /></td></tr></form>';
  }


  function poll_get_results(&$results, $sort = true) {

    global $DB;

    foreach ($this->options as $option) {
      $responses = $DB->get_records('block_poll_response', array('optionid'=>$option->id));
      $results[$option->optiontext] = (!$responses ? '0' : count($responses));
    }
    if ($sort) { poll_sort_results($results); }
  }


  function poll_print_results() {

    $this->poll_get_results($results);
    $img = 0;

    foreach ($results as $option => $count) {
      $img = ($img == 0 ? 1 : 0);
      $highest = (!isset($highest) ? $count : $highest);
         //// CITE fix;
         if ($highest * $count == 0) $imgwidth = 0;
         else $imgwidth = round($this->preferred_width() / $highest * $count);
      $imgwidth = ($imgwidth == 0 ? 1 : $imgwidth);
      $this->content->text .= "<tr><td>$option ($count)<br />" . poll_get_graphbar($img, $imgwidth) . '</td></tr>';
    }
  }


  function get_content() {
    global $USER, $DB;

    if ($this->content !== null) {
      return $this->content;
    }

    $this->content = new stdClass;
    $this->content->text   = '';
    $this->content->footer = '';

    if (empty($this->instance)) {
        return $this->content;
    }

    ///// CITE fix: if no poll is config, don't retrieve record
    if (isset($this->config)) {

      $this->poll = $DB->get_record('block_poll', array('id'=>$this->config->pollid));

      if(!empty($this->poll)) {

        $this->options = $DB->get_records('block_poll_option', array('pollid'=>$this->poll->id));

        $this->content->text .= '<table cellspacing="2" cellpadding="2">';
        $this->content->text .= '<tr><th>' . $this->poll->questiontext . '</th></tr>';

        $response = $DB->get_record('block_poll_response', array('pollid'=>$this->poll->id, 'userid'=>$USER->id));
        $func = 'poll_print_' . (!$response && $this->poll_user_eligible() ? 'options' : 'results');
        $this->$func();

        $this->content->text .= '</table>';
        $this->content->footer = ($this->poll_can_edit() ? $this->poll_results_link() : '');
      }
      else {
        $this->content->text = 'No poll is available';
      }

    }

    return $this->content;
  }

}

?>
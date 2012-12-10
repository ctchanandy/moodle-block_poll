<script language="javascript" type="text/javascript">
 function show_poll_results(id) {
  window.location.href="<?php echo(str_replace('&amp;', '&', $url) . 'responses&pid='); ?>" + id;
 }
</script>

<?php
    // 20111121 by CITE - migrate to Moodle 2.0
    // Paul Holden 24th July, 2007
    // poll block; view poll responses tab

    global $DB, $OUTPUT;

    $pid = optional_param('pid', 0, PARAM_INTEGER);

    // --------- functions ------------------

    function poll_custom_callback($a, $b) {
        $counta = $a->responsecount;
        $countb = $b->responsecount;
        return ($counta == $countb ? 0 : ($counta > $countb ? -1 : 1));
    }

    function get_response_checks($options, $selected) {
        foreach ($options as $option) {
            $arr[] = '<input type="checkbox" onclick="this.checked=' . ($option->id == $selected ? 'true" checked' : 'false"') . ' />';
        }
        return $arr;
    }

    // --------------------------------------

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

    if (($poll = $DB->get_record('block_poll', array('id'=>$pid))) && ($options = $DB->get_records('block_poll_option', array('pollid'=>$poll->id)))) {
        foreach ($options as $option) {
            $option->responses = $DB->get_records('block_poll_response', array('optionid'=>$option->id));
            $option->responsecount = (!$option->responses ? 0 : count($option->responses));
        }
        poll_sort_results($options, 'poll_custom_callback');

        print $OUTPUT->box_start('generalbox boxaligncenter');

        echo("<strong>$poll->questiontext</strong><ol>");
        foreach ($options as $option) {
            echo("<li>$option->optiontext ($option->responsecount)</li>");
        }
        echo('</ol>');

        print $OUTPUT->box_end();

        if ($responses = $DB->get_records('block_poll_response', array('pollid'=>$poll->id), 'submitted ASC')) {

            $responsecount = count($responses);
            $optioncount = count($options);

            $table = new html_table();
            $table->head = array('&nbsp;', get_string('user'), get_string('date'));
            for ($i = 1; $i <= $optioncount; $i++) {
                $table->head[] = $i;
            }
            $table->tablealign = 'left';
            $table->width = '*';

            foreach ($responses as $response) {
                $user = $DB->get_record('user', array('id'=>$response->userid), 'id, firstname, lastname, picture, email, imagealt');
                $table->data[] = array_merge(array($OUTPUT->user_picture($user, array('courseid'=>$COURSE->id)),
                fullname($user),
                userdate($response->submitted)),
                get_response_checks($options, $response->optionid));
            }

            echo html_writer::table($table);
        }
    }
?>

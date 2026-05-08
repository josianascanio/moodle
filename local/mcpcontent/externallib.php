<?php
// This file is part of Moodle - http://moodle.org/.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External functions for MCP course content creation.
 *
 * @package local_mcpcontent
 */
class local_mcpcontent_external extends external_api {
    /**
     * Parameters for create_label.
     *
     * @return external_function_parameters
     */
    public static function create_label_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'sectionid' => new external_value(PARAM_INT, 'Course section ID'),
            'content' => new external_value(PARAM_RAW, 'Label HTML content'),
            'name' => new external_value(PARAM_TEXT, 'Optional label name', VALUE_DEFAULT, ''),
            'visible' => new external_value(PARAM_BOOL, 'Whether the activity is visible', VALUE_DEFAULT, true),
        ]);
    }

    /**
     * Create a label activity.
     *
     * @param int $courseid Course ID.
     * @param int $sectionid Course section ID.
     * @param string $content Label HTML content.
     * @param string $name Optional label name.
     * @param bool $visible Whether the activity is visible.
     * @return array Created activity data.
     */
    public static function create_label(
        int $courseid,
        int $sectionid,
        string $content,
        string $name = '',
        bool $visible = true
    ): array {
        $params = self::validate_parameters(self::create_label_parameters(), [
            'courseid' => $courseid,
            'sectionid' => $sectionid,
            'content' => $content,
            'name' => $name,
            'visible' => $visible,
        ]);

        $moduleinfo = self::base_moduleinfo($params['courseid'], $params['sectionid'], 'label', $params['visible']);
        $moduleinfo->name = $params['name'];
        $moduleinfo->intro = clean_text($params['content'], FORMAT_HTML);
        $moduleinfo->introformat = FORMAT_HTML;

        return self::add_activity($moduleinfo);
    }

    /**
     * Return description for create_label.
     *
     * @return external_single_structure
     */
    public static function create_label_returns(): external_single_structure {
        return self::activity_return_structure();
    }

    /**
     * Parameters for create_page.
     *
     * @return external_function_parameters
     */
    public static function create_page_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'sectionid' => new external_value(PARAM_INT, 'Course section ID'),
            'name' => new external_value(PARAM_TEXT, 'Page name'),
            'content' => new external_value(PARAM_RAW, 'Page HTML content'),
            'intro' => new external_value(PARAM_RAW, 'Page introduction HTML', VALUE_DEFAULT, ''),
            'visible' => new external_value(PARAM_BOOL, 'Whether the activity is visible', VALUE_DEFAULT, true),
        ]);
    }

    /**
     * Create a page resource.
     *
     * @param int $courseid Course ID.
     * @param int $sectionid Course section ID.
     * @param string $name Page name.
     * @param string $content Page HTML content.
     * @param string $intro Page introduction HTML.
     * @param bool $visible Whether the activity is visible.
     * @return array Created activity data.
     */
    public static function create_page(
        int $courseid,
        int $sectionid,
        string $name,
        string $content,
        string $intro = '',
        bool $visible = true
    ): array {
        $params = self::validate_parameters(self::create_page_parameters(), [
            'courseid' => $courseid,
            'sectionid' => $sectionid,
            'name' => $name,
            'content' => $content,
            'intro' => $intro,
            'visible' => $visible,
        ]);

        $config = get_config('page');
        $moduleinfo = self::base_moduleinfo($params['courseid'], $params['sectionid'], 'page', $params['visible']);
        $moduleinfo->name = $params['name'];
        $moduleinfo->intro = clean_text($params['intro'], FORMAT_HTML);
        $moduleinfo->introformat = FORMAT_HTML;
        $moduleinfo->content = clean_text($params['content'], FORMAT_HTML);
        $moduleinfo->contentformat = FORMAT_HTML;
        $moduleinfo->display = $config->display ?? RESOURCELIB_DISPLAY_OPEN;
        $moduleinfo->popupheight = $config->popupheight ?? 450;
        $moduleinfo->popupwidth = $config->popupwidth ?? 620;
        $moduleinfo->printintro = $config->printintro ?? 0;
        $moduleinfo->printlastmodified = $config->printlastmodified ?? 1;
        $moduleinfo->legacyfiles = RESOURCELIB_LEGACYFILES_NO;
        $moduleinfo->legacyfileslast = null;
        $moduleinfo->revision = 1;

        return self::add_activity($moduleinfo);
    }

    /**
     * Return description for create_page.
     *
     * @return external_single_structure
     */
    public static function create_page_returns(): external_single_structure {
        return self::activity_return_structure();
    }

    /**
     * Parameters for create_url.
     *
     * @return external_function_parameters
     */
    public static function create_url_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'sectionid' => new external_value(PARAM_INT, 'Course section ID'),
            'name' => new external_value(PARAM_TEXT, 'URL resource name'),
            'externalurl' => new external_value(PARAM_URL, 'External URL'),
            'intro' => new external_value(PARAM_RAW, 'URL introduction HTML', VALUE_DEFAULT, ''),
            'visible' => new external_value(PARAM_BOOL, 'Whether the activity is visible', VALUE_DEFAULT, true),
        ]);
    }

    /**
     * Create a URL resource.
     *
     * @param int $courseid Course ID.
     * @param int $sectionid Course section ID.
     * @param string $name URL resource name.
     * @param string $externalurl External URL.
     * @param string $intro URL introduction HTML.
     * @param bool $visible Whether the activity is visible.
     * @return array Created activity data.
     */
    public static function create_url(
        int $courseid,
        int $sectionid,
        string $name,
        string $externalurl,
        string $intro = '',
        bool $visible = true
    ): array {
        $params = self::validate_parameters(self::create_url_parameters(), [
            'courseid' => $courseid,
            'sectionid' => $sectionid,
            'name' => $name,
            'externalurl' => $externalurl,
            'intro' => $intro,
            'visible' => $visible,
        ]);

        $config = get_config('url');
        $moduleinfo = self::base_moduleinfo($params['courseid'], $params['sectionid'], 'url', $params['visible']);
        $moduleinfo->name = $params['name'];
        $moduleinfo->intro = clean_text($params['intro'], FORMAT_HTML);
        $moduleinfo->introformat = FORMAT_HTML;
        $moduleinfo->externalurl = $params['externalurl'];
        $moduleinfo->display = $config->display ?? RESOURCELIB_DISPLAY_AUTO;
        $moduleinfo->popupheight = $config->popupheight ?? 450;
        $moduleinfo->popupwidth = $config->popupwidth ?? 620;
        $moduleinfo->printintro = $config->printintro ?? 0;

        return self::add_activity($moduleinfo);
    }

    /**
     * Return description for create_url.
     *
     * @return external_single_structure
     */
    public static function create_url_returns(): external_single_structure {
        return self::activity_return_structure();
    }

    /**
     * Parameters for create_quiz.
     *
     * @return external_function_parameters
     */
    public static function create_quiz_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'sectionid' => new external_value(PARAM_INT, 'Course section ID'),
            'name' => new external_value(PARAM_TEXT, 'Quiz name'),
            'intro' => new external_value(PARAM_RAW, 'Quiz description HTML', VALUE_DEFAULT, ''),
            'timelimit' => new external_value(PARAM_INT, 'Time limit in seconds, 0 for none', VALUE_DEFAULT, 0),
            'attempts' => new external_value(PARAM_INT, 'Allowed attempts, 0 for unlimited', VALUE_DEFAULT, 0),
            'grade' => new external_value(PARAM_FLOAT, 'Maximum grade', VALUE_DEFAULT, 100.0),
            'gradepass' => new external_value(PARAM_FLOAT, 'Grade to pass', VALUE_DEFAULT, 0.0),
            'questionsperpage' => new external_value(PARAM_INT, 'Questions per page', VALUE_DEFAULT, 1),
            'shuffleanswers' => new external_value(PARAM_BOOL, 'Shuffle answers', VALUE_DEFAULT, true),
            'visible' => new external_value(PARAM_BOOL, 'Whether the activity is visible', VALUE_DEFAULT, true),
        ]);
    }

    /**
     * Create a quiz activity.
     *
     * This creates the Moodle quiz shell and its grading/timing settings. Add
     * questions with the local question functions in this plugin.
     *
     * @param int $courseid Course ID.
     * @param int $sectionid Course section ID.
     * @param string $name Quiz name.
     * @param string $intro Quiz description HTML.
     * @param int $timelimit Time limit in seconds.
     * @param int $attempts Allowed attempts.
     * @param float $grade Maximum grade.
     * @param float $gradepass Grade to pass.
     * @param int $questionsperpage Questions per page.
     * @param bool $shuffleanswers Shuffle answers.
     * @param bool $visible Whether the activity is visible.
     * @return array Created activity data.
     */
    public static function create_quiz(
        int $courseid,
        int $sectionid,
        string $name,
        string $intro = '',
        int $timelimit = 0,
        int $attempts = 0,
        float $grade = 100.0,
        float $gradepass = 0.0,
        int $questionsperpage = 1,
        bool $shuffleanswers = true,
        bool $visible = true
    ): array {
        $params = self::validate_parameters(self::create_quiz_parameters(), [
            'courseid' => $courseid,
            'sectionid' => $sectionid,
            'name' => $name,
            'intro' => $intro,
            'timelimit' => $timelimit,
            'attempts' => $attempts,
            'grade' => $grade,
            'gradepass' => $gradepass,
            'questionsperpage' => $questionsperpage,
            'shuffleanswers' => $shuffleanswers,
            'visible' => $visible,
        ]);

        $moduleinfo = self::base_moduleinfo($params['courseid'], $params['sectionid'], 'quiz', $params['visible']);
        $moduleinfo->name = $params['name'];
        $moduleinfo->intro = clean_text($params['intro'], FORMAT_HTML);
        $moduleinfo->introformat = FORMAT_HTML;
        self::apply_quiz_settings($moduleinfo, $params);

        $created = self::add_activity($moduleinfo);
        self::update_grade_item($created['courseid'], $created['instanceid'], $params['name'], $params['grade'], $params['gradepass']);

        return $created;
    }

    /**
     * Return description for create_quiz.
     *
     * @return external_single_structure
     */
    public static function create_quiz_returns(): external_single_structure {
        return self::activity_return_structure();
    }

    /**
     * Parameters for update_quiz_settings.
     *
     * @return external_function_parameters
     */
    public static function update_quiz_settings_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Quiz course module ID'),
            'name' => new external_value(PARAM_TEXT, 'Quiz name', VALUE_OPTIONAL),
            'intro' => new external_value(PARAM_RAW, 'Quiz description HTML', VALUE_OPTIONAL),
            'timelimit' => new external_value(PARAM_INT, 'Time limit in seconds, 0 for none', VALUE_OPTIONAL),
            'attempts' => new external_value(PARAM_INT, 'Allowed attempts, 0 for unlimited', VALUE_OPTIONAL),
            'grade' => new external_value(PARAM_FLOAT, 'Maximum grade', VALUE_OPTIONAL),
            'gradepass' => new external_value(PARAM_FLOAT, 'Grade to pass', VALUE_OPTIONAL),
            'questionsperpage' => new external_value(PARAM_INT, 'Questions per page', VALUE_OPTIONAL),
            'shuffleanswers' => new external_value(PARAM_BOOL, 'Shuffle answers', VALUE_OPTIONAL),
            'visible' => new external_value(PARAM_BOOL, 'Whether the activity is visible', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Update quiz shell settings.
     *
     * @param int $cmid Quiz course module ID.
     * @param string|null $name Quiz name.
     * @param string|null $intro Quiz description HTML.
     * @param int|null $timelimit Time limit in seconds.
     * @param int|null $attempts Allowed attempts.
     * @param float|null $grade Maximum grade.
     * @param float|null $gradepass Grade to pass.
     * @param int|null $questionsperpage Questions per page.
     * @param bool|null $shuffleanswers Shuffle answers.
     * @param bool|null $visible Whether the activity is visible.
     * @return array Updated activity data.
     */
    public static function update_quiz_settings(
        int $cmid,
        ?string $name = null,
        ?string $intro = null,
        ?int $timelimit = null,
        ?int $attempts = null,
        ?float $grade = null,
        ?float $gradepass = null,
        ?int $questionsperpage = null,
        ?bool $shuffleanswers = null,
        ?bool $visible = null
    ): array {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->dirroot . '/course/modlib.php');

        $rawparams = [
            'cmid' => $cmid,
        ];
        foreach (['name', 'intro', 'timelimit', 'attempts', 'grade', 'gradepass', 'questionsperpage', 'shuffleanswers', 'visible'] as $key) {
            if ($$key !== null) {
                $rawparams[$key] = $$key;
            }
        }
        $params = self::validate_parameters(self::update_quiz_settings_parameters(), $rawparams);

        $cm = get_coursemodule_from_id('quiz', $params['cmid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $context = context_course::instance($course->id);

        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);
        require_capability('mod/quiz:addinstance', $context);
        require_capability('local/mcpcontent:createcontent', $context);

        $quiz = $DB->get_record('quiz', ['id' => $cm->instance], '*', MUST_EXIST);

        if (array_key_exists('name', $params)) {
            $quiz->name = $params['name'];
        }
        if (array_key_exists('intro', $params)) {
            $quiz->intro = clean_text($params['intro'], FORMAT_HTML);
            $quiz->introformat = FORMAT_HTML;
        }
        foreach (['timelimit', 'attempts', 'questionsperpage'] as $field) {
            if (array_key_exists($field, $params)) {
                $quiz->$field = max(0, (int) $params[$field]);
            }
        }
        if (array_key_exists('shuffleanswers', $params)) {
            $quiz->shuffleanswers = $params['shuffleanswers'] ? 1 : 0;
        }
        if (array_key_exists('grade', $params)) {
            $quiz->grade = max(0, (float) $params['grade']);
        }

        $quiz->timemodified = time();
        $DB->update_record('quiz', $quiz);

        if (array_key_exists('visible', $params)) {
            set_coursemodule_visible($cm->id, $params['visible'] ? 1 : 0);
        }

        $gradepassvalue = array_key_exists('gradepass', $params) ? (float) $params['gradepass'] : self::get_grade_pass($course->id, $quiz->id);
        self::update_grade_item($course->id, $quiz->id, $quiz->name, (float) $quiz->grade, $gradepassvalue);
        rebuild_course_cache($course->id, true);

        return self::activity_result_from_cmid($cm->id);
    }

    /**
     * Return description for update_quiz_settings.
     *
     * @return external_single_structure
     */
    public static function update_quiz_settings_returns(): external_single_structure {
        return self::activity_return_structure();
    }

    /**
     * Parameters for create_question_category.
     *
     * @return external_function_parameters
     */
    public static function create_question_category_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'name' => new external_value(PARAM_TEXT, 'Question category name'),
            'info' => new external_value(PARAM_RAW, 'Question category description HTML', VALUE_DEFAULT, ''),
            'parentid' => new external_value(PARAM_INT, 'Parent question category ID, 0 for course top category', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Create a question category in the course question bank.
     *
     * @param int $courseid Course ID.
     * @param string $name Category name.
     * @param string $info Category description HTML.
     * @param int $parentid Parent question category ID.
     * @return array Category data.
     */
    public static function create_question_category(int $courseid, string $name, string $info = '', int $parentid = 0): array {
        global $CFG, $DB;

        require_once($CFG->libdir . '/questionlib.php');

        $params = self::validate_parameters(self::create_question_category_parameters(), [
            'courseid' => $courseid,
            'name' => $name,
            'info' => $info,
            'parentid' => $parentid,
        ]);

        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
        $context = context_course::instance($course->id);
        self::validate_context($context);
        self::require_question_capabilities($context, ['add']);

        if ($params['parentid'] > 0) {
            $parent = $DB->get_record('question_categories', [
                'id' => $params['parentid'],
                'contextid' => $context->id,
            ], '*', MUST_EXIST);
        } else {
            $parent = question_get_top_category($context->id, true);
        }

        $category = new stdClass();
        $category->name = $params['name'];
        $category->contextid = $context->id;
        $category->parent = $parent->id;
        $category->info = clean_text($params['info'], FORMAT_HTML);
        $category->infoformat = FORMAT_HTML;
        $category->sortorder = 999;
        $category->stamp = make_unique_id_code();
        $category->id = $DB->insert_record('question_categories', $category);

        return self::question_category_result($category, $course->id);
    }

    /**
     * Return description for create_question_category.
     *
     * @return external_single_structure
     */
    public static function create_question_category_returns(): external_single_structure {
        return self::question_category_return_structure();
    }

    /**
     * Parameters for create_multichoice_question.
     *
     * @return external_function_parameters
     */
    public static function create_multichoice_question_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'categoryid' => new external_value(PARAM_INT, 'Question category ID'),
            'name' => new external_value(PARAM_TEXT, 'Question name'),
            'questiontext' => new external_value(PARAM_RAW, 'Question text HTML'),
            'answers' => new external_multiple_structure(
                new external_single_structure([
                    'text' => new external_value(PARAM_RAW, 'Answer text HTML'),
                    'fraction' => new external_value(PARAM_FLOAT, 'Grade fraction from 0 to 1'),
                    'feedback' => new external_value(PARAM_RAW, 'Answer feedback HTML', VALUE_DEFAULT, ''),
                ]),
                'Answer options'
            ),
            'generalfeedback' => new external_value(PARAM_RAW, 'General feedback HTML', VALUE_DEFAULT, ''),
            'defaultmark' => new external_value(PARAM_FLOAT, 'Default mark', VALUE_DEFAULT, 1.0),
            'single' => new external_value(PARAM_BOOL, 'Single answer question', VALUE_DEFAULT, true),
            'shuffleanswers' => new external_value(PARAM_BOOL, 'Shuffle answers', VALUE_DEFAULT, true),
            'addtoquizcmid' => new external_value(PARAM_INT, 'Quiz course module ID to add this question to, 0 to skip', VALUE_DEFAULT, 0),
            'maxmark' => new external_value(PARAM_FLOAT, 'Quiz slot maximum mark', VALUE_DEFAULT, 1.0),
        ]);
    }

    /**
     * Create a multiple-choice question, optionally adding it to a quiz.
     *
     * @param int $courseid Course ID.
     * @param int $categoryid Question category ID.
     * @param string $name Question name.
     * @param string $questiontext Question text HTML.
     * @param array $answers Answer options.
     * @param string $generalfeedback General feedback HTML.
     * @param float $defaultmark Default mark.
     * @param bool $single Single answer question.
     * @param bool $shuffleanswers Shuffle answers.
     * @param int $addtoquizcmid Quiz course module ID.
     * @param float $maxmark Quiz slot maximum mark.
     * @return array Created question data.
     */
    public static function create_multichoice_question(
        int $courseid,
        int $categoryid,
        string $name,
        string $questiontext,
        array $answers,
        string $generalfeedback = '',
        float $defaultmark = 1.0,
        bool $single = true,
        bool $shuffleanswers = true,
        int $addtoquizcmid = 0,
        float $maxmark = 1.0
    ): array {
        $params = self::validate_parameters(self::create_multichoice_question_parameters(), [
            'courseid' => $courseid,
            'categoryid' => $categoryid,
            'name' => $name,
            'questiontext' => $questiontext,
            'answers' => $answers,
            'generalfeedback' => $generalfeedback,
            'defaultmark' => $defaultmark,
            'single' => $single,
            'shuffleanswers' => $shuffleanswers,
            'addtoquizcmid' => $addtoquizcmid,
            'maxmark' => $maxmark,
        ]);

        $form = self::base_question_form($params['courseid'], $params['categoryid'], 'multichoice', $params['name'],
            $params['questiontext'], $params['generalfeedback'], $params['defaultmark']);
        $form->single = $params['single'] ? 1 : 0;
        $form->shuffleanswers = $params['shuffleanswers'] ? 1 : 0;
        $form->answernumbering = 'abc';
        $form->showstandardinstruction = 0;
        $form->correctfeedback = self::editor_field('Correcto.');
        $form->partiallycorrectfeedback = self::editor_field('Parcialmente correcto.');
        $form->incorrectfeedback = self::editor_field('Incorrecto.');
        $form->shownumcorrect = 1;
        $form->answer = [];
        $form->fraction = [];
        $form->feedback = [];

        foreach ($params['answers'] as $answer) {
            $form->answer[] = self::editor_field($answer['text']);
            $form->fraction[] = max(0, min(1, (float) $answer['fraction']));
            $form->feedback[] = self::editor_field($answer['feedback'] ?? '');
        }

        return self::save_question_and_optionally_add_to_quiz($form, $params['addtoquizcmid'], $params['maxmark']);
    }

    /**
     * Return description for create_multichoice_question.
     *
     * @return external_single_structure
     */
    public static function create_multichoice_question_returns(): external_single_structure {
        return self::question_return_structure();
    }

    /**
     * Parameters for create_truefalse_question.
     *
     * @return external_function_parameters
     */
    public static function create_truefalse_question_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'categoryid' => new external_value(PARAM_INT, 'Question category ID'),
            'name' => new external_value(PARAM_TEXT, 'Question name'),
            'questiontext' => new external_value(PARAM_RAW, 'Question text HTML'),
            'correctanswer' => new external_value(PARAM_BOOL, 'True if the correct answer is true'),
            'feedbacktrue' => new external_value(PARAM_RAW, 'Feedback for true answer HTML', VALUE_DEFAULT, ''),
            'feedbackfalse' => new external_value(PARAM_RAW, 'Feedback for false answer HTML', VALUE_DEFAULT, ''),
            'generalfeedback' => new external_value(PARAM_RAW, 'General feedback HTML', VALUE_DEFAULT, ''),
            'defaultmark' => new external_value(PARAM_FLOAT, 'Default mark', VALUE_DEFAULT, 1.0),
            'addtoquizcmid' => new external_value(PARAM_INT, 'Quiz course module ID to add this question to, 0 to skip', VALUE_DEFAULT, 0),
            'maxmark' => new external_value(PARAM_FLOAT, 'Quiz slot maximum mark', VALUE_DEFAULT, 1.0),
        ]);
    }

    /**
     * Create a true/false question, optionally adding it to a quiz.
     *
     * @return array Created question data.
     */
    public static function create_truefalse_question(
        int $courseid,
        int $categoryid,
        string $name,
        string $questiontext,
        bool $correctanswer,
        string $feedbacktrue = '',
        string $feedbackfalse = '',
        string $generalfeedback = '',
        float $defaultmark = 1.0,
        int $addtoquizcmid = 0,
        float $maxmark = 1.0
    ): array {
        $params = self::validate_parameters(self::create_truefalse_question_parameters(), [
            'courseid' => $courseid,
            'categoryid' => $categoryid,
            'name' => $name,
            'questiontext' => $questiontext,
            'correctanswer' => $correctanswer,
            'feedbacktrue' => $feedbacktrue,
            'feedbackfalse' => $feedbackfalse,
            'generalfeedback' => $generalfeedback,
            'defaultmark' => $defaultmark,
            'addtoquizcmid' => $addtoquizcmid,
            'maxmark' => $maxmark,
        ]);

        $form = self::base_question_form($params['courseid'], $params['categoryid'], 'truefalse', $params['name'],
            $params['questiontext'], $params['generalfeedback'], $params['defaultmark']);
        $form->correctanswer = $params['correctanswer'] ? 1 : 0;
        $form->feedbacktrue = self::editor_field($params['feedbacktrue']);
        $form->feedbackfalse = self::editor_field($params['feedbackfalse']);
        $form->showstandardinstruction = 0;

        return self::save_question_and_optionally_add_to_quiz($form, $params['addtoquizcmid'], $params['maxmark']);
    }

    /**
     * Return description for create_truefalse_question.
     *
     * @return external_single_structure
     */
    public static function create_truefalse_question_returns(): external_single_structure {
        return self::question_return_structure();
    }

    /**
     * Parameters for create_shortanswer_question.
     *
     * @return external_function_parameters
     */
    public static function create_shortanswer_question_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'categoryid' => new external_value(PARAM_INT, 'Question category ID'),
            'name' => new external_value(PARAM_TEXT, 'Question name'),
            'questiontext' => new external_value(PARAM_RAW, 'Question text HTML'),
            'answers' => new external_multiple_structure(
                new external_single_structure([
                    'text' => new external_value(PARAM_TEXT, 'Accepted answer text'),
                    'fraction' => new external_value(PARAM_FLOAT, 'Grade fraction from 0 to 1'),
                    'feedback' => new external_value(PARAM_RAW, 'Answer feedback HTML', VALUE_DEFAULT, ''),
                ]),
                'Accepted answers'
            ),
            'generalfeedback' => new external_value(PARAM_RAW, 'General feedback HTML', VALUE_DEFAULT, ''),
            'defaultmark' => new external_value(PARAM_FLOAT, 'Default mark', VALUE_DEFAULT, 1.0),
            'usecase' => new external_value(PARAM_BOOL, 'Case-sensitive matching', VALUE_DEFAULT, false),
            'addtoquizcmid' => new external_value(PARAM_INT, 'Quiz course module ID to add this question to, 0 to skip', VALUE_DEFAULT, 0),
            'maxmark' => new external_value(PARAM_FLOAT, 'Quiz slot maximum mark', VALUE_DEFAULT, 1.0),
        ]);
    }

    /**
     * Create a short-answer question, optionally adding it to a quiz.
     *
     * @return array Created question data.
     */
    public static function create_shortanswer_question(
        int $courseid,
        int $categoryid,
        string $name,
        string $questiontext,
        array $answers,
        string $generalfeedback = '',
        float $defaultmark = 1.0,
        bool $usecase = false,
        int $addtoquizcmid = 0,
        float $maxmark = 1.0
    ): array {
        $params = self::validate_parameters(self::create_shortanswer_question_parameters(), [
            'courseid' => $courseid,
            'categoryid' => $categoryid,
            'name' => $name,
            'questiontext' => $questiontext,
            'answers' => $answers,
            'generalfeedback' => $generalfeedback,
            'defaultmark' => $defaultmark,
            'usecase' => $usecase,
            'addtoquizcmid' => $addtoquizcmid,
            'maxmark' => $maxmark,
        ]);

        $form = self::base_question_form($params['courseid'], $params['categoryid'], 'shortanswer', $params['name'],
            $params['questiontext'], $params['generalfeedback'], $params['defaultmark']);
        $form->usecase = $params['usecase'] ? 1 : 0;
        $form->answer = [];
        $form->fraction = [];
        $form->feedback = [];

        foreach ($params['answers'] as $answer) {
            $form->answer[] = $answer['text'];
            $form->fraction[] = max(0, min(1, (float) $answer['fraction']));
            $form->feedback[] = self::editor_field($answer['feedback'] ?? '');
        }

        return self::save_question_and_optionally_add_to_quiz($form, $params['addtoquizcmid'], $params['maxmark']);
    }

    /**
     * Return description for create_shortanswer_question.
     *
     * @return external_single_structure
     */
    public static function create_shortanswer_question_returns(): external_single_structure {
        return self::question_return_structure();
    }

    /**
     * Parameters for add_question_to_quiz.
     *
     * @return external_function_parameters
     */
    public static function add_question_to_quiz_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Quiz course module ID'),
            'questionid' => new external_value(PARAM_INT, 'Question ID'),
            'maxmark' => new external_value(PARAM_FLOAT, 'Quiz slot maximum mark', VALUE_DEFAULT, 1.0),
            'page' => new external_value(PARAM_INT, 'Quiz page number, 0 to append using quiz pagination', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Add an existing question to a quiz.
     *
     * @param int $cmid Quiz course module ID.
     * @param int $questionid Question ID.
     * @param float $maxmark Quiz slot maximum mark.
     * @param int $page Quiz page number.
     * @return array Added question data.
     */
    public static function add_question_to_quiz(int $cmid, int $questionid, float $maxmark = 1.0, int $page = 0): array {
        $params = self::validate_parameters(self::add_question_to_quiz_parameters(), [
            'cmid' => $cmid,
            'questionid' => $questionid,
            'maxmark' => $maxmark,
            'page' => $page,
        ]);

        self::add_existing_question_to_quiz($params['cmid'], $params['questionid'], $params['maxmark'], $params['page']);

        return [
            'cmid' => $params['cmid'],
            'questionid' => $params['questionid'],
            'added' => true,
        ];
    }

    /**
     * Return description for add_question_to_quiz.
     *
     * @return external_single_structure
     */
    public static function add_question_to_quiz_returns(): external_single_structure {
        return new external_single_structure([
            'cmid' => new external_value(PARAM_INT, 'Quiz course module ID'),
            'questionid' => new external_value(PARAM_INT, 'Question ID'),
            'added' => new external_value(PARAM_BOOL, 'Whether the question was added'),
        ]);
    }

    /**
     * Parameters for update_sections.
     *
     * @return external_function_parameters
     */
    public static function update_sections_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'sections' => new external_multiple_structure(
                new external_single_structure([
                    'sectionid' => new external_value(PARAM_INT, 'Course section ID'),
                    'name' => new external_value(PARAM_TEXT, 'Section name', VALUE_OPTIONAL),
                    'summary' => new external_value(PARAM_RAW, 'Section summary HTML', VALUE_OPTIONAL),
                    'visible' => new external_value(PARAM_BOOL, 'Whether the section is visible', VALUE_OPTIONAL),
                ]),
                'Sections to update'
            ),
        ]);
    }

    /**
     * Update course sections.
     *
     * @param int $courseid Course ID.
     * @param array $sections Sections to update.
     * @return array Updated section data.
     */
    public static function update_sections(int $courseid, array $sections): array {
        global $CFG, $DB, $PAGE;

        require_once($CFG->dirroot . '/course/lib.php');

        $params = self::validate_parameters(self::update_sections_parameters(), [
            'courseid' => $courseid,
            'sections' => $sections,
        ]);

        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
        $context = context_course::instance($course->id);

        self::validate_context($context);
        require_capability('moodle/course:update', $context);
        require_capability('local/mcpcontent:createcontent', $context);

        $PAGE->set_context($context);

        $updated = [];
        foreach ($params['sections'] as $sectiondata) {
            $section = $DB->get_record('course_sections', [
                'id' => $sectiondata['sectionid'],
                'course' => $course->id,
            ], '*', MUST_EXIST);

            $data = [];
            if (array_key_exists('name', $sectiondata)) {
                $data['name'] = $sectiondata['name'];
            }
            if (array_key_exists('summary', $sectiondata)) {
                $data['summary'] = clean_text($sectiondata['summary'], FORMAT_HTML);
                $data['summaryformat'] = FORMAT_HTML;
            }
            if (array_key_exists('visible', $sectiondata)) {
                $data['visible'] = $sectiondata['visible'] ? 1 : 0;
            }

            if (!empty($data)) {
                course_update_section($course, $section, $data);
            }

            $section = $DB->get_record('course_sections', ['id' => $section->id], '*', MUST_EXIST);
            $updated[] = [
                'courseid' => (int) $course->id,
                'sectionid' => (int) $section->id,
                'sectionnum' => (int) $section->section,
                'name' => $section->name ?? '',
                'summary' => $section->summary ?? '',
                'visible' => (bool) $section->visible,
            ];
        }

        return ['sections' => $updated];
    }

    /**
     * Return description for update_sections.
     *
     * @return external_single_structure
     */
    public static function update_sections_returns(): external_single_structure {
        return new external_single_structure([
            'sections' => new external_multiple_structure(
                new external_single_structure([
                    'courseid' => new external_value(PARAM_INT, 'Course ID'),
                    'sectionid' => new external_value(PARAM_INT, 'Course section ID'),
                    'sectionnum' => new external_value(PARAM_INT, 'Course section number'),
                    'name' => new external_value(PARAM_TEXT, 'Section name'),
                    'summary' => new external_value(PARAM_RAW, 'Section summary HTML'),
                    'visible' => new external_value(PARAM_BOOL, 'Whether the section is visible'),
                ])
            ),
        ]);
    }

    /**
     * Build common editor field data.
     *
     * @param string $text HTML/plain text value.
     * @return array Editor-compatible field value.
     */
    private static function editor_field(string $text): array {
        return [
            'text' => clean_text($text, FORMAT_HTML),
            'format' => FORMAT_HTML,
            'itemid' => 0,
        ];
    }

    /**
     * Build base data for question creation and validate question-bank permissions.
     *
     * @param int $courseid Course ID.
     * @param int $categoryid Question category ID.
     * @param string $qtype Question type.
     * @param string $name Question name.
     * @param string $questiontext Question text HTML.
     * @param string $generalfeedback General feedback HTML.
     * @param float $defaultmark Default mark.
     * @return stdClass Question form data for question_type::save_question.
     */
    private static function base_question_form(
        int $courseid,
        int $categoryid,
        string $qtype,
        string $name,
        string $questiontext,
        string $generalfeedback,
        float $defaultmark
    ): stdClass {
        global $CFG, $DB;

        require_once($CFG->libdir . '/questionlib.php');

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $context = context_course::instance($course->id);
        $category = $DB->get_record('question_categories', [
            'id' => $categoryid,
            'contextid' => $context->id,
        ], '*', MUST_EXIST);

        self::validate_context($context);
        self::require_question_capabilities($context, ['add']);

        $form = new stdClass();
        $form->category = $category->id . ',' . $context->id;
        $form->name = $name;
        $form->questiontext = self::editor_field($questiontext);
        $form->generalfeedback = self::editor_field($generalfeedback);
        $form->defaultmark = max(0, $defaultmark);
        $form->penalty = 0.3333333;
        $form->qtype = $qtype;
        $form->status = self::ready_question_status();
        $form->idnumber = '';
        $form->hint = [];
        $form->tags = [];

        return $form;
    }

    /**
     * Save a question using Moodle question APIs and optionally add it to a quiz.
     *
     * @param stdClass $form Question form data.
     * @param int $quizcmid Optional quiz course module ID.
     * @param float $maxmark Quiz slot maximum mark.
     * @return array Created question data.
     */
    private static function save_question_and_optionally_add_to_quiz(stdClass $form, int $quizcmid, float $maxmark): array {
        global $DB;

        $question = question_bank::get_qtype($form->qtype)->save_question(new stdClass(), $form);
        if (!empty($question->errors)) {
            throw new moodle_exception('Could not save question: ' . json_encode($question->errors));
        }

        $added = false;
        if ($quizcmid > 0) {
            self::add_existing_question_to_quiz($quizcmid, $question->id, $maxmark, 0);
            $added = true;
        }

        $categoryid = (int) explode(',', $form->category)[0];
        $entry = get_question_bank_entry($question->id);

        return [
            'courseid' => (int) $DB->get_field('context', 'instanceid', ['id' => self::question_category_contextid($categoryid)]),
            'categoryid' => $categoryid,
            'questionid' => (int) $question->id,
            'questionbankentryid' => (int) $entry->id,
            'qtype' => $form->qtype,
            'name' => $question->name,
            'addedtoquiz' => $added,
            'quizcmid' => $quizcmid,
        ];
    }

    /**
     * Add an existing question to a quiz using Moodle's quiz API.
     *
     * @param int $cmid Quiz course module ID.
     * @param int $questionid Question ID.
     * @param float $maxmark Quiz slot maximum mark.
     * @param int $page Quiz page number, 0 to append.
     */
    private static function add_existing_question_to_quiz(int $cmid, int $questionid, float $maxmark, int $page): void {
        global $CFG, $DB;

        require_once($CFG->libdir . '/questionlib.php');
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
        $coursecontext = context_course::instance($cm->course);
        $modulecontext = context_module::instance($cm->id);

        self::validate_context($coursecontext);
        require_capability('mod/quiz:manage', $modulecontext);
        self::require_question_capabilities($coursecontext, ['useall', 'usemine']);

        $question = $DB->get_record('question', ['id' => $questionid], '*', MUST_EXIST);
        $quiz = $DB->get_record('quiz', ['id' => $cm->instance], '*', MUST_EXIST);
        $quiz->cmid = $cm->id;

        quiz_add_quiz_question($question->id, $quiz, $page, max(0, $maxmark));
        $quiz->sumgrades = (float) $DB->get_field_sql('SELECT COALESCE(SUM(maxmark), 0) FROM {quiz_slots} WHERE quizid = ?', [$quiz->id]);
        $DB->set_field('quiz', 'sumgrades', $quiz->sumgrades, ['id' => $quiz->id]);
        rebuild_course_cache($cm->course, true);
    }

    /**
     * Return the ready status constant for question versions.
     *
     * @return string Ready status.
     */
    private static function ready_question_status(): string {
        if (class_exists('\core_question\local\bank\question_version_status')) {
            return \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        }

        return 'ready';
    }

    /**
     * Require question-bank permissions, allowing any listed capability suffix.
     *
     * @param context $context Moodle context.
     * @param array $actions Capability suffixes without moodle/question: prefix.
     */
    private static function require_question_capabilities(context $context, array $actions): void {
        require_capability('local/mcpcontent:createcontent', $context);

        $capabilities = array_map(static function(string $action): string {
            return 'moodle/question:' . $action;
        }, $actions);

        if (!has_any_capability($capabilities, $context)) {
            throw new required_capability_exception($context, implode(' or ', $capabilities), 'nopermissions', '');
        }
    }

    /**
     * Get context ID for a question category.
     *
     * @param int $categoryid Question category ID.
     * @return int Context ID.
     */
    private static function question_category_contextid(int $categoryid): int {
        global $DB;

        return (int) $DB->get_field('question_categories', 'contextid', ['id' => $categoryid], MUST_EXIST);
    }

    /**
     * Format a question category result.
     *
     * @param stdClass $category Question category record.
     * @param int $courseid Course ID.
     * @return array Category data.
     */
    private static function question_category_result(stdClass $category, int $courseid): array {
        return [
            'courseid' => $courseid,
            'categoryid' => (int) $category->id,
            'contextid' => (int) $category->contextid,
            'parentid' => (int) $category->parent,
            'name' => $category->name,
        ];
    }

    /**
     * Shared return schema for question categories.
     *
     * @return external_single_structure
     */
    private static function question_category_return_structure(): external_single_structure {
        return new external_single_structure([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'categoryid' => new external_value(PARAM_INT, 'Question category ID'),
            'contextid' => new external_value(PARAM_INT, 'Context ID'),
            'parentid' => new external_value(PARAM_INT, 'Parent category ID'),
            'name' => new external_value(PARAM_TEXT, 'Question category name'),
        ]);
    }

    /**
     * Shared return schema for created questions.
     *
     * @return external_single_structure
     */
    private static function question_return_structure(): external_single_structure {
        return new external_single_structure([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'categoryid' => new external_value(PARAM_INT, 'Question category ID'),
            'questionid' => new external_value(PARAM_INT, 'Question ID'),
            'questionbankentryid' => new external_value(PARAM_INT, 'Question bank entry ID'),
            'qtype' => new external_value(PARAM_PLUGIN, 'Question type'),
            'name' => new external_value(PARAM_TEXT, 'Question name'),
            'addedtoquiz' => new external_value(PARAM_BOOL, 'Whether the question was added to a quiz'),
            'quizcmid' => new external_value(PARAM_INT, 'Quiz course module ID, 0 if not added'),
        ]);
    }

    /**
     * Build common module data and validate permissions.
     *
     * @param int $courseid Course ID.
     * @param int $sectionid Course section ID.
     * @param string $modulename Module name.
     * @param bool $visible Whether the activity is visible.
     * @return stdClass Module info ready for add_moduleinfo.
     */
    private static function base_moduleinfo(int $courseid, int $sectionid, string $modulename, bool $visible): stdClass {
        global $CFG, $DB, $PAGE;

        require_once($CFG->dirroot . '/course/modlib.php');
        require_once($CFG->libdir . '/resourcelib.php');

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $section = $DB->get_record('course_sections', ['id' => $sectionid, 'course' => $courseid], '*', MUST_EXIST);
        $module = $DB->get_record('modules', ['name' => $modulename], '*', MUST_EXIST);
        $context = context_course::instance($courseid);

        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);
        if ($modulename === 'quiz') {
            require_capability('mod/quiz:addinstance', $context);
        }
        require_capability('local/mcpcontent:createcontent', $context);

        $PAGE->set_context($context);

        $moduleinfo = new stdClass();
        $moduleinfo->course = $course->id;
        $moduleinfo->module = $module->id;
        $moduleinfo->modulename = $module->name;
        $moduleinfo->section = $section->section;
        $moduleinfo->visible = $visible ? 1 : 0;
        $moduleinfo->visibleoncoursepage = $visible ? 1 : 0;
        $moduleinfo->showdescription = 0;
        $moduleinfo->groupmode = $course->groupmode;
        $moduleinfo->groupingid = $course->defaultgroupingid;
        $moduleinfo->completion = COMPLETION_DISABLED;
        $moduleinfo->completionview = COMPLETION_VIEW_NOT_REQUIRED;
        $moduleinfo->completionexpected = 0;
        $moduleinfo->downloadcontent = DOWNLOAD_COURSE_CONTENT_ENABLED;
        $moduleinfo->lang = null;

        return $moduleinfo;
    }

    /**
     * Apply default and requested quiz settings to module info.
     *
     * @param stdClass $moduleinfo Module info ready for add_moduleinfo.
     * @param array $params Validated quiz parameters.
     */
    private static function apply_quiz_settings(stdClass $moduleinfo, array $params): void {
        $moduleinfo->timeopen = 0;
        $moduleinfo->timeclose = 0;
        $moduleinfo->timelimit = max(0, (int) $params['timelimit']);
        $moduleinfo->overduehandling = 'autosubmit';
        $moduleinfo->graceperiod = 0;
        $moduleinfo->preferredbehaviour = 'deferredfeedback';
        $moduleinfo->canredoquestions = 0;
        $moduleinfo->attempts = max(0, (int) $params['attempts']);
        $moduleinfo->attemptonlast = 0;
        $moduleinfo->grademethod = 1;
        $moduleinfo->decimalpoints = 2;
        $moduleinfo->questiondecimalpoints = -1;
        $moduleinfo->reviewattempt = 0x10DFFF;
        $moduleinfo->reviewcorrectness = 0x10DFFF;
        $moduleinfo->reviewmarks = 0x10DFFF;
        $moduleinfo->reviewspecificfeedback = 0x10DFFF;
        $moduleinfo->reviewgeneralfeedback = 0x10DFFF;
        $moduleinfo->reviewrightanswer = 0x10DFFF;
        $moduleinfo->reviewoverallfeedback = 0x10DFFF;
        $moduleinfo->questionsperpage = max(0, (int) $params['questionsperpage']);
        $moduleinfo->navmethod = 'free';
        $moduleinfo->shuffleanswers = $params['shuffleanswers'] ? 1 : 0;
        $moduleinfo->sumgrades = 0;
        $moduleinfo->grade = max(0, (float) $params['grade']);
        $moduleinfo->gradepass = max(0, (float) $params['gradepass']);
        $moduleinfo->browsersecurity = '-';
        $moduleinfo->delay1 = 0;
        $moduleinfo->delay2 = 0;
        $moduleinfo->password = '';
        $moduleinfo->subnet = '';
    }

    /**
     * Add the module and format the result.
     *
     * @param stdClass $moduleinfo Module info ready for add_moduleinfo.
     * @return array Created activity data.
     */
    private static function add_activity(stdClass $moduleinfo): array {
        global $CFG, $DB;

        $course = $DB->get_record('course', ['id' => $moduleinfo->course], '*', MUST_EXIST);
        $created = add_moduleinfo($moduleinfo, $course, null);
        $section = $DB->get_record('course_sections', [
            'course' => $course->id,
            'section' => $created->section,
        ], '*', MUST_EXIST);
        $instance = $DB->get_record($created->modulename, ['id' => $created->instance], '*', MUST_EXIST);
        $name = $instance->name ?? $created->name;
        $url = $CFG->wwwroot . '/mod/' . $created->modulename . '/view.php?id=' . $created->coursemodule;

        if ($created->modulename === 'label') {
            $url = $CFG->wwwroot . '/course/view.php?id=' . $course->id . '#module-' . $created->coursemodule;
        }

        return [
            'courseid' => (int) $course->id,
            'sectionid' => (int) $section->id,
            'sectionnum' => (int) $created->section,
            'cmid' => (int) $created->coursemodule,
            'instanceid' => (int) $created->instance,
            'modname' => $created->modulename,
            'name' => $name,
            'url' => $url,
        ];
    }

    /**
     * Build the standard activity result from an existing course module ID.
     *
     * @param int $cmid Course module ID.
     * @return array Activity data.
     */
    private static function activity_result_from_cmid(int $cmid): array {
        global $CFG, $DB;

        $cm = $DB->get_record('course_modules', ['id' => $cmid], '*', MUST_EXIST);
        $module = $DB->get_record('modules', ['id' => $cm->module], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $section = $DB->get_record('course_sections', ['id' => $cm->section], '*', MUST_EXIST);
        $instance = $DB->get_record($module->name, ['id' => $cm->instance], '*', MUST_EXIST);
        $url = $CFG->wwwroot . '/mod/' . $module->name . '/view.php?id=' . $cm->id;

        if ($module->name === 'label') {
            $url = $CFG->wwwroot . '/course/view.php?id=' . $course->id . '#module-' . $cm->id;
        }

        return [
            'courseid' => (int) $course->id,
            'sectionid' => (int) $section->id,
            'sectionnum' => (int) $section->section,
            'cmid' => (int) $cm->id,
            'instanceid' => (int) $cm->instance,
            'modname' => $module->name,
            'name' => $instance->name ?? '',
            'url' => $url,
        ];
    }

    /**
     * Update the quiz grade item, including grade-to-pass.
     *
     * @param int $courseid Course ID.
     * @param int $quizid Quiz instance ID.
     * @param string $name Quiz name.
     * @param float $grade Maximum grade.
     * @param float $gradepass Grade to pass.
     */
    private static function update_grade_item(int $courseid, int $quizid, string $name, float $grade, float $gradepass): void {
        global $CFG;

        require_once($CFG->libdir . '/gradelib.php');

        grade_update('mod/quiz', $courseid, 'mod', 'quiz', $quizid, 0, null, [
            'itemname' => $name,
            'gradetype' => GRADE_TYPE_VALUE,
            'grademax' => max(0, $grade),
            'gradepass' => max(0, $gradepass),
        ]);
    }

    /**
     * Get the current grade-to-pass value for a quiz grade item.
     *
     * @param int $courseid Course ID.
     * @param int $quizid Quiz instance ID.
     * @return float Grade to pass.
     */
    private static function get_grade_pass(int $courseid, int $quizid): float {
        global $DB;

        $item = $DB->get_record('grade_items', [
            'courseid' => $courseid,
            'itemtype' => 'mod',
            'itemmodule' => 'quiz',
            'iteminstance' => $quizid,
            'itemnumber' => 0,
        ]);

        return $item ? (float) $item->gradepass : 0.0;
    }

    /**
     * Shared return schema for created activities.
     *
     * @return external_single_structure
     */
    private static function activity_return_structure(): external_single_structure {
        return new external_single_structure([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'sectionid' => new external_value(PARAM_INT, 'Course section ID'),
            'sectionnum' => new external_value(PARAM_INT, 'Course section number'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'instanceid' => new external_value(PARAM_INT, 'Activity instance ID'),
            'modname' => new external_value(PARAM_PLUGIN, 'Module name'),
            'name' => new external_value(PARAM_TEXT, 'Activity name'),
            'url' => new external_value(PARAM_URL, 'Activity URL'),
        ]);
    }
}

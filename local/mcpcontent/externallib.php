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

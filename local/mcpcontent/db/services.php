<?php
// This file is part of Moodle - http://moodle.org/.

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_mcpcontent_create_label' => [
        'classname' => 'local_mcpcontent_external',
        'methodname' => 'create_label',
        'classpath' => 'local/mcpcontent/externallib.php',
        'description' => 'Create a label activity in a course section.',
        'type' => 'write',
        'capabilities' => 'local/mcpcontent:createcontent, moodle/course:manageactivities',
    ],
    'local_mcpcontent_create_page' => [
        'classname' => 'local_mcpcontent_external',
        'methodname' => 'create_page',
        'classpath' => 'local/mcpcontent/externallib.php',
        'description' => 'Create a page resource in a course section.',
        'type' => 'write',
        'capabilities' => 'local/mcpcontent:createcontent, moodle/course:manageactivities',
    ],
    'local_mcpcontent_create_url' => [
        'classname' => 'local_mcpcontent_external',
        'methodname' => 'create_url',
        'classpath' => 'local/mcpcontent/externallib.php',
        'description' => 'Create a URL resource in a course section.',
        'type' => 'write',
        'capabilities' => 'local/mcpcontent:createcontent, moodle/course:manageactivities',
    ],
    'local_mcpcontent_update_sections' => [
        'classname' => 'local_mcpcontent_external',
        'methodname' => 'update_sections',
        'classpath' => 'local/mcpcontent/externallib.php',
        'description' => 'Update course section names and summaries.',
        'type' => 'write',
        'capabilities' => 'local/mcpcontent:createcontent, moodle/course:update',
    ],
];

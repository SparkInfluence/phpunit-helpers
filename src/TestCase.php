<?php

namespace WSD\Spark\PhpUnitHelpers;

use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\when;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as BaseTestCase;
use function Brain\Monkey\tearDown as tearDownBrainMonkey;
use function Brain\Monkey\setUp as setUpBrainMonkey;
use WSD\Spark\PhpUnitHelpers\Constraints\ExpectationsMet;

abstract class TestCase extends BaseTestCase
{

    use MockeryPHPUnitIntegration;

    protected static $filesToLoad = null;
    protected static $globalsFromLoadedFiles = null;
    protected static $basePath = false;

    /**
     * @beforeClass
     */
    public static function maybeSetUpFileBasePath()
    {
        if (self::$basePath) {
            return;
        }
        if (static::$basePath) {
            self::$basePath = static::$basePath;
            return;
        }
        $ds = DIRECTORY_SEPARATOR;
        $root = explode($ds . 'wsd-', preg_replace('@[/\\\\]+@', $ds, __DIR__))[0];
        $trailing = trim(str_replace($root, '', __DIR__), $ds);
        self::$basePath = $root . $ds . explode($ds, $trailing)[0] . $ds;
    }

    /**
     * @before
     */
    public static function includeFilesToLoad()
    {
        if (!static::$filesToLoad) {
            return;
        }
        $__existing_context = self::formatContext(get_defined_vars());
        foreach (static::$filesToLoad as $file) {
            require_once rtrim(self::$basePath, '/') . '/' . ltrim($file, '/');
        }
        $new_vars = array_keys(array_diff_assoc(self::formatContext(get_defined_vars()), $__existing_context));
        foreach ($new_vars as $var) {
            if (in_array($var, ['_', '__existing_context'])) {
                continue;
            }
            static::$globalsFromLoadedFiles[$var] = $$var;
        }
    }

    /**
     * @before
     */
    public static function setUpBrainMonkey()
    {
        setUpBrainMonkey();
        when('_e')->echoArg();
        when('_n')->alias(function ($s, $p, $n) {
            return $n === 1 ? $s : $p;
        });
        stubs([
            '__',
            '_x',
            '_ex' => '_e',
            '_nx' => '_n',
            'esc_html__',
            'esc_attr__',
            'esc_html_x',
            'esc_attr_x',
            'esc_html_e' => '_e',
            'esc_attr_e' => '_e',
            'esc_attr',
            'esc_html',
            'esc_url',
            'esc_url_raw',
            'esc_textarea',
        ]);
    }

    /**
     * @after
     */
    public static function tearDownBrainMonkey()
    {
        tearDownBrainMonkey();
    }

    private static function formatContext(array $context)
    {
        return array_map(function ($item) {
            return md5(serialize($item));
        }, $context);
    }

    public function assertBrainMonkeyConditionsMet($message = '')
    {
        $this->assertThat(null, (new ExpectationsMet)->constraint(), $message);
    }

    private static function realCleanGlobalScope()
    {
        unset($GLOBALS['wpdb'], $GLOBALS['post']);
        $_GET = $_POST = $_REQUEST = [];
    }

    /**
     * @beforeClass
     */
    public static function cleanGlobalScopeBeforeClass()
    {
        self::realCleanGlobalScope();
    }

    /**
     * Keep this function as the last "after" function to avoid unexpected state
     *
     * @after
     */
    public static function cleanGlobalScopeAfterTests()
    {
        self::realCleanGlobalScope();
    }

    public function mockWpPost(array $data = []): \WP_Post
    {
        $data += [
            'ID' => mt_rand(1, 999),
            'post_author' => 0,
            'post_name' => '',
            'post_type' => '',
            'post_title' => '',
            'post_date' => '',
            'post_date_gmt' => '',
            'post_content' => '',
            'post_excerpt' => '',
            'post_status' => '',
            'comment_status' => '',
            'ping_status' => '',
            'post_password' => '',
            'post_parent' => 0,
            'post_modified' => '',
            'post_modified_gmt' => '',
            'comment_count' => 0,
            'menu_order' => 0,
        ];
        $post = \Mockery::mock('WP_Post');
        foreach ($data as $key => $value) {
            $post->{$key} = $value;
        }
        return $post;
    }

    public function mockWpUser(array $data = []): \WP_User
    {
        $data += [
            'ID' => mt_rand(1, 999),
            'caps' => [],
            'cap_key' => '',
            'roles' => [],
            'allcaps' => [],
            'first_name' => '',
            'last_name' => '',
            'user_login' => '',
            'user_pass' => '',
            'user_nicename' => '',
            'user_email' => '',
            'user_url' => '',
            'user_registered' => '',
            'user_activation_key' => '',
            'user_status' => '',
            'display_name' => '',
        ];
        $user = \Mockery::mock('WP_User');
        foreach ($data as $key => $value) {
            $user->{$key} = $value;
        }
        return $user;
    }

}

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
        $root = explode($ds . 'wsd-', preg_replace('@[/\\]+@', $ds, __DIR__))[0];
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

}

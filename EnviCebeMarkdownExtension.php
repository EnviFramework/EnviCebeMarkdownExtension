<?php
/**
 * MarkdownExtraを使用するためのエクステンションクラス
 *
 *
 * Markdown拡張である、MarkdownExtra形式で記述されたテキストを、HTMLにコンパイルするエクステンションです。
 *
 * 詳細は、

 * https://michelf.ca/projects/php-markdown/extra/
 *
 * を参照して下さい。
 *
 * インストール・設定
 * --------------------------------------------------
 * envi install-extension {app_key} {DI設定ファイル} markdown
 *
 * コマンドでインストール出来ます。
 *
 *
 * PHP versions 5
 *
 *
 * @category   EnviMVC拡張
 * @package    EnviPHPが用意するエクステンション
 * @subpackage MarkdownExtension
 * @author     Akito <akito-artisan@five-foxes.com>
 * @copyright  2011-2013 Artisan Project
 * @license    http://opensource.org/licenses/BSD-2-Clause The BSD 2-Clause License
 * @version    GIT: $Id$
 * @link       https://github.com/EnviMVC/EnviMVC3PHP
 * @see        https://github.com/EnviMVC/EnviMVC3PHP/wiki
 * @since      File available since Release 1.0.0
*/




/**
 *  MarkdownExtraを使用するためのエクステンション
 *
 * @category   EnviMVC拡張
 * @package    EnviPHPが用意するエクステンション
 * @subpackage MarkdownExtension
 * @author     Akito <akito-artisan@five-foxes.com>
 * @copyright  2011-2013 Artisan Project
 * @license    http://opensource.org/licenses/BSD-2-Clause The BSD 2-Clause License
 * @version    Release: @package_version@
 * @link       https://github.com/EnviMVC/EnviMVC3PHP
 * @see        https://github.com/EnviMVC/EnviMVC3PHP/wiki
 * @since      Class available since Release 1.0.0
 */
class EnviCebeMarkdownExtension
{
    private $system_conf;
    private static $dir_name;


    /**
     * +-- コンストラクタ
     *
     * @access      public
     * @param       array $system_conf コンフィグ
     * @return      void
     */
    public function __construct(array $system_conf)
    {
        $this->system_conf = $system_conf;
        self::$dir_name = __DIR__.DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR;
        spl_autoload_register(array('EnviCebeMarkdownExtension', 'autoload'));
    }
    /* ----------------------------------------- */

    /**
     * +-- オートローダー
     *
     * @access      public
     * @static
     * @param       var_text $class_name
     * @return      void
     */
    public static function autoload($class_name)
    {
        $dir_name = self::$dir_name;
        // psr-0
        $class_name = ltrim($class_name, '\\');
        $file_name  = '';
        $namespace = '';
        if ($last_ns_pos = strripos($class_name, '\\')) {
            $namespace  = substr($class_name, 0, $last_ns_pos);
            $class_name = substr($class_name, $last_ns_pos + 1);
            $file_name  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $file_name .= str_replace('_', DIRECTORY_SEPARATOR, $class_name);
        if (is_file($dir_name.DIRECTORY_SEPARATOR.$file_name.'.php')) {
            include $dir_name.DIRECTORY_SEPARATOR.$file_name.'.php';
            return;
        }


        if (is_file($dir_name.DIRECTORY_SEPARATOR.$file_name.'.class.php')) {
            include $dir_name.DIRECTORY_SEPARATOR.$file_name.'.class.php';
            return;
        }
    }
    /* ----------------------------------------- */


    /**
     * +-- ファイルを指定してコンパイルする
     *
     * @access      public
     * @param       string $file_path ファイルパス
     * @param       string $compile_id コンパイルID OPTIONAL:NULL
     * @return      string
     */
    public function compileFile($file_path, $compile_id = NULL)
    {
        if (!is_file($file_path)) {
            throw exception('not file : '.$file_path);
        }
        // cpu負荷節約
        $system_conf = $this->system_conf;
        if ($compile_id === NULL) {
            $compile_id = basename($file_path);
        }

        $compile_id .= '_'.$system_conf['file_version'];
        $cache_path = $system_conf['is_compile_cache'] ? $this->makeCachePath($compile_id) : NULL;
        $is_compile = $this->isCompile($cache_path);
        if (!$is_compile) {
            return file_get_contents($cache_path);
        }
        $out = $this->transformFile($file_path);
        $this->saveCache($cache_path, $out);
        return $out;
    }
    /* ----------------------------------------- */

    /**
     * +-- 文字列を指定してコンパイルする
     *
     * @access      public
     * @param       string $string コンパイルする文字列
     * @param       string $compile_id コンパイルID OPTIONAL:NULL
     * @return      string
     */
    public function compile($string, $compile_id)
    {
        // cpu負荷節約
        $system_conf = $this->system_conf;

        $compile_id .= '_'.$system_conf['file_version'];
        $cache_path = $system_conf['is_compile_cache'] ? $this->makeCachePath($compile_id) : NULL;
        $is_compile = $this->isCompile($cache_path);
        if (!$is_compile) {
            return file_get_contents($cache_path);
        }
        $out = $this->transform($string);
        $this->saveCache($cache_path, $out);
        return $out;
    }
    /* ----------------------------------------- */


    /**
     * +-- transformを実行する
     *
     * @access      public
     * @param       string $text 変換する文字列
     * @return      string
     */
    public function transform($text)
    {
        // cpu負荷節約
        $system_conf = $this->system_conf;
        switch ($system_conf['compile_engine']) {
        case 'Markdown':
            $text = $this->getMarkDown()->parse($text);
            break;
        case 'MarkdownExtra':
            $text = $this->getMarkdownExtra()->parse($text);
            break;
        case 'GithubMarkdown':
            $text = $this->getGithubMarkdown()->parse($text);

            break;
        }

        return $text;
    }
    /* ----------------------------------------- */

    /**
     * +-- ファイルからtransformを実行する
     *
     * @access      public
     * @param       string $file_path 変換するファイルのパス
     * @return      string
     */
    public function transformFile($file_path)
    {
        if (!is_file($file_path)) {
            throw exception('not file : '.$file_path);
        }
        return $this->transform(file_get_contents($file_path));
    }
    /* ----------------------------------------- */




    public function getMarkdownExtra()
    {
        static $parser;
        if ($parser) {
            return $parser;
        }
        $parser = new cebe\markdown\MarkdownExtra;
        $this->settingParser($parser, true);
        return $parser;
    }

    public function getGithubMarkdown()
    {
        static $parser;
        if ($parser) {
            return $parser;
        }
        $parser = new EnviCebeMarkdown\GithubMarkdown;
        $this->settingParser($parser, true);
        return $parser;
    }

    public function getMarkdown()
    {
        static $parser;
        if ($parser) {
            return $parser;
        }
        $parser = new cebe\markdown\Markdown;
        $this->settingParser($parser, true);
        return $parser;
    }


    private function settingParser(&$parser, $use_extra = false)
    {
        // cpu負荷節約
        $system_conf = $this->system_conf;
        $parser->maximumNestingLevel = $system_conf['maximumNestingLevel'];
    }


    private function makeCachePath($compile_id)
    {
        return $this->system_conf['cache_path'].DIRECTORY_SEPARATOR.'mark_down_cache_'.$compile_id.'_'.ENVI_ENV.'.html.envicc';
    }

    private function isCompile($cache_path)
    {
        // cpu負荷節約
        $system_conf = $this->system_conf;
        $is_compile = false;
        if (!$system_conf['is_compile_cache'] || $system_conf['is_force_compile']) {
            $is_compile = true;
        } elseif ($cache_path !== NULL && !is_file($cache_path)) {
            $is_compile = true;
        }
        return $is_compile;
    }
    private function saveCache($cache_path, $out)
    {
        if ($cache_path !== NULL) {
            file_put_contents($cache_path, $out);
        }
    }
}


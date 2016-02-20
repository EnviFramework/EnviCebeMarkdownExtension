<?php
/**
 * @package    %%project_name%%
 * @subpackage %%subpackage_name%%
 * @author     Suzunone <suzunone.eleven@gmail.com>
 * @copyright  %%your_project%%
 * @license    %%your_license%%
 * @link       %%your_link%%
 * @see        %%your_see%%
 * @sinse Class available since Release 1.0.0
 */

namespace EnviCebeMarkdown;



class GithubMarkdown extends \cebe\markdown\GithubMarkdown
{
    protected function renderAbsy($blocks)
    {
        $output = parent::renderAbsy($blocks);

        if (strpos($output, '[ ]') === 0) {
            $output = '<input type="checkbox" name="markdown[]">'.substr($output, 3);
        } elseif (stripos($output, '[x]') === 0) {
            $output = '<input type="checkbox" name="markdown[]" checked=checked>'.substr($output, 3);
        }
        return $output;
    }
}

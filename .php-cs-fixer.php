<?php

use PhpCsFixer\Config;
use PhpCsFixerCustomFixers\Fixer\NoUselessCommentFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessParenthesisFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessStrlenFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocParamTypeFixer;
use PhpCsFixerCustomFixers\Fixer\SingleSpaceAfterStatementFixer;
use PhpCsFixerCustomFixers\Fixer\SingleSpaceBeforeStatementFixer;
use PhpCsFixerCustomFixers\Fixer\NoSuperfluousConcatenationFixer;
use PhpCsFixerCustomFixers\Fixers;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/engine/Shopware')
    ->in(__DIR__ . '/tests')
    ->in(__DIR__ . '/recovery')
    ->exclude('Plugins/Community')
    ->exclude('Plugins/Local')
    ->exclude('install/templates')
    ->exclude('update/templates')
    ->notPath('LegacyPhpDumper.php')
    ->notPath('MemoryLimitTest.php');

$header = <<<EOF
Shopware 5
Copyright (c) shopware AG

According to our dual licensing model, this program can be used either
under the terms of the GNU Affero General Public License, version 3,
or under a proprietary license.

The texts of the GNU Affero General Public License with an additional
permission and of our proprietary license can be found at and
in the LICENSE file you have received along with this program.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Affero General Public License for more details.

"Shopware" is a registered trademark of shopware AG.
The licensing of the program under the AGPLv3 does not imply a
trademark license. Therefore any rights, title and interest in
our trademarks remain entirely with us.
EOF;

return (new Config())
    ->registerCustomFixers(new Fixers())
    ->setRiskyAllowed(true)
    ->setCacheFile('var/cache/php-cs-fixer')
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,

        'class_attributes_separation' => ['elements' => ['method' => 'one', 'property' => 'one']],
        'concat_space' => ['spacing' => 'one'],
        'doctrine_annotation_indentation' => true,
        'doctrine_annotation_spaces' => true,
        'general_phpdoc_annotation_remove' => [
             'annotations' => ['copyright', 'category'],
        ],
        'header_comment' => ['header' => $header, 'separate' => 'bottom', 'comment_type' => 'PHPDoc'],
        'no_useless_else' => true,
        'no_useless_return' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_order' => true,
        'phpdoc_summary' => false,
        'phpdoc_var_annotation_correct_order' => true,
        'php_unit_test_case_static_method_calls' => true,
        'single_line_throw' => false,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'operator_linebreak' => ['only_booleans' => true],
        'native_function_invocation' => true,

        NoUselessCommentFixer::name() => true,
        SingleSpaceAfterStatementFixer::name() => true,
        SingleSpaceBeforeStatementFixer::name() => true,
        PhpdocParamTypeFixer::name() => true,
        NoSuperfluousConcatenationFixer::name() => true,
        NoUselessStrlenFixer::name() => true,
        NoUselessParenthesisFixer::name() => true,
    ])
    ->setFinder($finder);

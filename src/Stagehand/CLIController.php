<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_CLIController
 * @copyright  2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'Console/Getopt.php';
require_once 'PEAR.php';

// {{{ Stagehand_CLIController

/**
 * @package    Stagehand_CLIController
 * @copyright  2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
abstract class Stagehand_CLIController
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $exceptionClass = 'Exception';
    protected $shortOptions;
    protected $longOptions = array();

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ run()

    /**
     * @return integer
     * @throws Exception
     */
    public function run()
    {
        if (!array_key_exists('argv', $_SERVER)) {
            echo "ERROR: either use the CLI php executable, or set register_argc_argv=On in php.ini.\n";;
            return 1;
        }

        try {
            list($options, $args) = $this->_parseOptions();
            $this->_configure($options, $args);
            $this->doRun();
        } catch (Exception $e) {
            if (!$e instanceof $this->exceptionClass) {
                throw $e;
            }

            echo 'ERROR: ' . $e->getMessage() . "\n";
            return 1;
        }

        return 0;
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ doConfigureByOption()

    /**
     * @param string $option
     * @param string $value
     * @return boolean
     */
    abstract protected function doConfigureByOption($option, $value);

    // }}}
    // {{{ doConfigureByArg()

    /**
     * @param string $arg
     * @return boolean
     */
    abstract protected function doConfigureByArg($arg);

    // }}}
    // {{{ doRun()

    /**
     */
    abstract protected function doRun();

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _parseOptions()

    /**
     * Parses the command line options.
     *
     * @return array
     */
    private function _parseOptions()
    {
        $oldErrorReportingLevel = error_reporting(error_reporting() & ~E_STRICT);

        PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
        $argv = Console_Getopt::readPHPArgv();
        PEAR::staticPopErrorHandling();
        if (PEAR::isError($argv)) {
            error_reporting($oldErrorReportingLevel);
            throw new $this->exceptionClass(preg_replace('/^Console_Getopt: /', '', $argv->getMessage()));
        }

        array_shift($argv);
        PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
        $parsedOptions = Console_Getopt::getopt2($argv,
                                                 $this->shortOptions,
                                                 $this->longOptions
                                                 );
        PEAR::staticPopErrorHandling();
        if (PEAR::isError($parsedOptions)) {
            error_reporting($oldErrorReportingLevel);
            throw new $this->exceptionClass(preg_replace('/^Console_Getopt: /', '', $parsedOptions->getMessage()));
        }

        error_reporting($oldErrorReportingLevel);

        return $parsedOptions;
    }

    // }}}
    // {{{ _configure()

    /**
     * Configures the current process by the command line options and arguments.
     *
     * @param array $options
     * @param array $args
     */
    private function _configure($options, $args)
    {
        foreach ($options as $option) {
            $doContinue = $this->doConfigureByOption($option[0], @$option[1]);
            if (!$doContinue) {
                return;
            }
        }

        foreach ($args as $arg) {
            $doContinue = $this->doConfigureByArg($arg);
            if (!$doContinue) {
                return;
            }
        }
    }

    /**#@-*/

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
